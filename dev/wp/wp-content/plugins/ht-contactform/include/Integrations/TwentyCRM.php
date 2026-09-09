<?php
/**
 * Twenty CRM Integration Class
 *
 * Handles all Twenty CRM API interactions for form submissions including
 * person creation, company linking, notes and schema (metadata) discovery.
 *
 * Twenty is schema-per-tenant: every workspace exposes its own objects and
 * fields over identical REST endpoints, so field mapping is driven by the
 * Metadata API rather than a hardcoded property list.
 *
 * @package HTContactForm
 * @subpackage Integrations
 */

namespace HTContactForm\Integrations;

use HTContactFormAdmin\Includes\Services\Helper;
use WP_Error;
use WP_REST_Response;

/**
 * Twenty CRM Integration Handler
 *
 * Provides functionality to integrate form submissions with Twenty CRM
 * using their REST API (Core + Metadata).
 */
class TwentyCRM {
    /**
     * Twenty CRM API key
     *
     * @var string
     */
    private $api_key = '';

    /**
     * Raw instance URL as configured by the user
     *
     * @var string
     */
    private $raw_base_url = '';

    /**
     * Normalized instance URL, or WP_Error when invalid
     *
     * @var string|WP_Error|null
     */
    private $base_url = null;

    /**
     * Helper class instance
     *
     * @var Helper|null
     */
    private $helper = null;

    /**
     * Instances storage by credentials
     *
     * @var array
     */
    private static $instances = [];

    /**
     * Metadata cache lifetime in seconds
     */
    const METADATA_TTL = 900;

    /**
     * Composite field types and their writable sub keys
     *
     * @var array
     */
    private static $composite_fields = [
        'FULL_NAME' => ['firstName', 'lastName'],
        'EMAILS'    => ['primaryEmail'],
        'PHONES'    => ['primaryPhoneNumber', 'primaryPhoneCallingCode', 'primaryPhoneCountryCode'],
        'LINKS'     => ['primaryLinkUrl', 'primaryLinkLabel'],
        'ADDRESS'   => ['addressStreet1', 'addressStreet2', 'addressCity', 'addressState', 'addressPostcode', 'addressCountry'],
        'CURRENCY'  => ['amountMicros', 'currencyCode'],
        // Twenty reports rich text as RICH_TEXT on current builds and
        // RICH_TEXT_V2 on others; both take the same composite payload.
        'RICH_TEXT'    => ['markdown'],
        'RICH_TEXT_V2' => ['markdown'],
    ];

    /**
     * Standard CRM objects that must never be filtered out as plumbing
     *
     * @var array
     */
    private static $standard_objects = [
        'person',
        'company',
        'opportunity',
        'note',
        'task',
    ];

    /**
     * Field types that can never be written through a form mapping
     *
     * @var array
     */
    private static $unsupported_types = [
        'RELATION',
        'MORPH_RELATION',
        'ACTOR',
        'TS_VECTOR',
        'POSITION',
        'UUID',
    ];

    /**
     * Human readable labels for composite sub keys
     *
     * @var array
     */
    private static $sub_labels = [
        'firstName'              => 'First Name',
        'lastName'               => 'Last Name',
        'primaryEmail'           => 'Email',
        'primaryPhoneNumber'     => 'Phone Number',
        'primaryPhoneCallingCode'=> 'Calling Code',
        'primaryPhoneCountryCode'=> 'Country Code',
        'primaryLinkUrl'         => 'URL',
        'primaryLinkLabel'       => 'Label',
        'addressStreet1'         => 'Street 1',
        'addressStreet2'         => 'Street 2',
        'addressCity'            => 'City',
        'addressState'           => 'State',
        'addressPostcode'        => 'Postcode',
        'addressCountry'         => 'Country',
        'amountMicros'           => 'Amount',
        'currencyCode'           => 'Currency Code',
        'markdown'               => 'Text',
    ];

    /**
     * Get instance by credentials
     *
     * @param string|null $api_key Twenty CRM API key
     * @param string|null $base_url Twenty CRM instance URL
     * @return self Instance of the TwentyCRM class
     */
    public static function get_instance($api_key = null, $base_url = null) {
        $key = md5(($api_key ?? '') . '|' . ($base_url ?? ''));
        if (!isset(self::$instances[$key])) {
            self::$instances[$key] = new self($api_key, $base_url);
        }
        return self::$instances[$key];
    }

    /**
     * Constructor
     *
     * @param string|null $api_key Twenty CRM API key
     * @param string|null $base_url Twenty CRM instance URL
     */
    public function __construct($api_key = null, $base_url = null) {
        $this->api_key      = is_string($api_key) ? trim($api_key) : '';
        $this->raw_base_url = is_string($base_url) ? trim($base_url) : '';

        if (class_exists(Helper::class)) {
            $this->helper = Helper::get_instance();
        }
    }

    //-------------------------------------------------------------------------
    // TRANSPORT
    //-------------------------------------------------------------------------

    /**
     * Normalize and validate the configured instance URL
     *
     * Rejects anything that is not an http(s) URL with a host, and requires
     * HTTPS unless HTCONTACTFORM_ALLOW_INSECURE_CRM is defined for local
     * development. The result is cached on the instance.
     *
     * @return string|WP_Error Normalized origin without trailing slash, or WP_Error
     */
    private function get_base_url() {
        if ($this->base_url !== null) {
            return $this->base_url;
        }

        $url = $this->raw_base_url;

        if ($url === '') {
            $this->base_url = new WP_Error(
                'base_url_missing',
                __('Twenty CRM instance URL is required', 'ht-contactform')
            );
            return $this->base_url;
        }

        if (!preg_match('#^https?://#i', $url)) {
            $url = 'https://' . $url;
        }

        $url   = esc_url_raw($url, ['http', 'https']);
        $parts = wp_parse_url($url);

        if (empty($parts['host']) || empty($parts['scheme'])) {
            $this->base_url = new WP_Error(
                'base_url_invalid',
                __('Twenty CRM instance URL is not a valid URL', 'ht-contactform')
            );
            return $this->base_url;
        }

        $allow_insecure = defined('HTCONTACTFORM_ALLOW_INSECURE_CRM') && HTCONTACTFORM_ALLOW_INSECURE_CRM;

        if (strtolower($parts['scheme']) !== 'https' && !$allow_insecure) {
            $this->base_url = new WP_Error(
                'base_url_insecure',
                __('Twenty CRM instance URL must use HTTPS', 'ht-contactform')
            );
            return $this->base_url;
        }

        $normalized = strtolower($parts['scheme']) . '://' . $parts['host'];

        if (!empty($parts['port'])) {
            $normalized .= ':' . (int) $parts['port'];
        }

        // Keep any sub path the user configured (some self-hosted setups proxy
        // Twenty under a sub directory), minus a trailing slash.
        if (!empty($parts['path']) && $parts['path'] !== '/') {
            $normalized .= '/' . trim($parts['path'], '/');
        }

        // Blocks private/reserved hosts unless the site explicitly allows them.
        if (!wp_http_validate_url($normalized)) {
            $this->base_url = new WP_Error(
                'base_url_blocked',
                __('Twenty CRM instance URL was rejected. Private or reserved hosts are not allowed.', 'ht-contactform')
            );
            return $this->base_url;
        }

        $this->base_url = $normalized;

        return $this->base_url;
    }

    /**
     * Make a request against the Twenty CRM Core API
     *
     * @param string $endpoint Endpoint below /rest/ (e.g. 'people')
     * @param string $method HTTP method
     * @param array $data Request body for write methods
     * @param array $query Query arguments; the 'filter' value is passed through untouched
     * @return array|WP_Error Unwrapped response data on success, WP_Error on failure
     */
    private function api_request($endpoint, $method = 'GET', $data = [], $query = []) {
        return $this->request('rest/' . ltrim($endpoint, '/'), $method, $data, $query);
    }

    /**
     * Make a request against the Twenty CRM Metadata API
     *
     * @param string $endpoint Endpoint below /rest/metadata/ (e.g. 'objects')
     * @param array $query Query arguments
     * @return array|WP_Error Unwrapped response data on success, WP_Error on failure
     */
    private function metadata_request($endpoint, $query = []) {
        return $this->request('rest/metadata/' . ltrim($endpoint, '/'), 'GET', [], $query);
    }

    /**
     * Perform the HTTP request
     *
     * @param string $path Path below the instance origin
     * @param string $method HTTP method
     * @param array $data Request body
     * @param array $query Query arguments
     * @return array|WP_Error
     */
    private function request($path, $method = 'GET', $data = [], $query = []) {
        if (empty($this->api_key)) {
            return new WP_Error('api_key_missing', __('Twenty CRM API key is required', 'ht-contactform'));
        }

        $base_url = $this->get_base_url();

        if (is_wp_error($base_url)) {
            return $base_url;
        }

        $url = $base_url . '/' . ltrim($path, '/');

        if (!empty($query)) {
            $pairs = [];
            foreach ($query as $key => $value) {
                if ($value === null || $value === '') {
                    continue;
                }
                $pairs[] = rawurlencode($key) . '=' . rawurlencode((string) $value);
            }
            if (!empty($pairs)) {
                $url .= '?' . implode('&', $pairs);
            }
        }

        $args = [
            'method'  => strtoupper($method),
            'timeout' => 30,
            'headers' => [
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
                'Authorization' => 'Bearer ' . $this->api_key,
                'User-Agent'    => 'HT-ContactForm/' . HTCONTACTFORM_VERSION,
            ],
        ];

        if (!empty($data) && in_array(strtoupper($method), ['POST', 'PUT', 'PATCH'], true)) {
            $args['body'] = wp_json_encode($data);
        }

        $response = wp_remote_request($url, $args);

        if (is_wp_error($response)) {
            return new WP_Error(
                'http_request_failed',
                sprintf(
                    /* translators: %s: Error message */
                    __('HTTP request failed: %s', 'ht-contactform'),
                    $response->get_error_message()
                )
            );
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);

        if ($response_code === 401 || $response_code === 403) {
            return new WP_Error(
                'invalid_api_key',
                __('Twenty CRM rejected the API key. It may be expired, revoked, or missing permissions for this record type.', 'ht-contactform'),
                ['status' => $response_code]
            );
        }

        if ($response_code === 429) {
            $retry_after = wp_remote_retrieve_header($response, 'retry-after');
            $retry_after = $retry_after !== '' ? (int) $retry_after : 10;

            return new WP_Error(
                'rate_limited',
                sprintf(
                    /* translators: %d: Number of seconds to wait */
                    __('Twenty CRM API rate limit exceeded. Please retry after %d seconds.', 'ht-contactform'),
                    $retry_after
                ),
                ['status' => 429, 'retry_after' => $retry_after]
            );
        }

        $decoded_data = null;

        if ($response_body !== '') {
            $decoded_data = json_decode($response_body, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return new WP_Error(
                    'json_decode_error',
                    __('Invalid JSON response from Twenty CRM API', 'ht-contactform'),
                    ['response_body' => substr($response_body, 0, 200)]
                );
            }
        }

        if ($response_code < 200 || $response_code >= 300) {
            $error_message = $response_body;

            if (is_array($decoded_data)) {
                if (!empty($decoded_data['messages']) && is_array($decoded_data['messages'])) {
                    $error_message = implode(' ', array_map('strval', $decoded_data['messages']));
                } elseif (!empty($decoded_data['message'])) {
                    $error_message = is_array($decoded_data['message'])
                        ? implode(' ', array_map('strval', $decoded_data['message']))
                        : (string) $decoded_data['message'];
                } elseif (!empty($decoded_data['error'])) {
                    $error_message = is_string($decoded_data['error']) ? $decoded_data['error'] : wp_json_encode($decoded_data['error']);
                }
            }

            return new WP_Error(
                'api_error',
                sprintf(
                    /* translators: %1$d: HTTP status code, %2$s: Error message */
                    __('Twenty CRM API error [%1$d]: %2$s', 'ht-contactform'),
                    $response_code,
                    $error_message
                ),
                ['status' => $response_code, 'response' => $decoded_data]
            );
        }

        if ($response_code === 204 || $response_body === '') {
            return [];
        }

        if (!is_array($decoded_data)) {
            return new WP_Error(
                'unexpected_response',
                __('Unexpected response from Twenty CRM API', 'ht-contactform')
            );
        }

        // Core and Metadata REST both wrap payloads in a top level "data" key.
        if (array_key_exists('data', $decoded_data)) {
            $unwrapped = $decoded_data['data'];

            if (isset($decoded_data['pageInfo']) && is_array($unwrapped)) {
                $unwrapped['__pageInfo'] = $decoded_data['pageInfo'];
            }

            return is_array($unwrapped) ? $unwrapped : ['value' => $unwrapped];
        }

        return $decoded_data;
    }

    /**
     * Build a Twenty REST filter expression
     *
     * The value is quoted so commas and spaces inside it cannot terminate the
     * expression; the whole string is URL encoded by request().
     *
     * @param string $field Field path (e.g. 'emails.primaryEmail')
     * @param string $comparator Comparator (e.g. 'eq', 'ilike')
     * @param string $value Value to compare against
     * @return string Filter expression
     */
    private function build_filter($field, $comparator, $value) {
        $value = str_replace(['"', "\\"], '', (string) $value);

        return $field . '[' . $comparator . ']:"' . $value . '"';
    }

    //-------------------------------------------------------------------------
    // CREDENTIALS & SCHEMA
    //-------------------------------------------------------------------------

    /**
     * Verify API credentials
     *
     * @return array|WP_Error Success payload on success, WP_Error on failure
     */
    public function verify() {
        $response = $this->api_request('people', 'GET', [], ['limit' => 1]);

        if (is_wp_error($response)) {
            return $response;
        }

        return [
            'success' => true,
            'message' => __('Twenty CRM credentials verified successfully', 'ht-contactform'),
        ];
    }

    /**
     * Build the transient key for a metadata request
     *
     * @param string $suffix Cache bucket suffix
     * @return string Transient key
     */
    private function metadata_cache_key($suffix) {
        return 'htcf_twentycrm_meta_' . md5($this->raw_base_url . '|' . $this->api_key . '|' . $suffix);
    }

    /**
     * Delete all cached metadata for the current credentials
     *
     * @return void
     */
    public function flush_metadata_cache() {
        delete_transient($this->metadata_cache_key('objects'));

        $objects = get_transient($this->metadata_cache_key('object_names'));

        if (is_array($objects)) {
            foreach ($objects as $name) {
                delete_transient($this->metadata_cache_key('fields_' . $name));
                delete_transient($this->metadata_cache_key('raw_fields_' . strtolower($name)));
            }
        }

        delete_transient($this->metadata_cache_key('object_names'));
    }

    /**
     * Pull a record list out of a metadata response
     *
     * Twenty has shipped several envelopes for these endpoints across versions:
     * a plain list under the collection key, a GraphQL style
     * {edges: [{node: {...}}]} connection, or the list directly under "data".
     * All three are accepted so self-hosted instances on older builds work.
     *
     * @param array $response Unwrapped response body
     * @param string $collection Expected collection key ('objects' or 'fields')
     * @return array List of records
     */
    private function extract_collection($response, $collection) {
        if (!is_array($response)) {
            return [];
        }

        // Drop the page cursor request() attaches, so a plain list stays a list.
        unset($response['__pageInfo']);

        $candidates = [];

        if (isset($response[$collection])) {
            $candidates[] = $response[$collection];
        }

        // Some builds nest the collection one level deeper, e.g. data.objects.objects.
        if (isset($response[$collection][$collection])) {
            $candidates[] = $response[$collection][$collection];
        }

        // Fall back to the response itself when it is already a plain list.
        $candidates[] = $response;

        foreach ($candidates as $candidate) {
            $records = $this->normalize_record_list($candidate);

            if (!empty($records)) {
                return $records;
            }
        }

        return [];
    }

    /**
     * Normalize a possible record list into a plain array of records
     *
     * @param mixed $value Candidate value
     * @return array List of records
     */
    private function normalize_record_list($value) {
        if (!is_array($value) || empty($value)) {
            return [];
        }

        // GraphQL style connection.
        if (isset($value['edges']) && is_array($value['edges'])) {
            $records = [];

            foreach ($value['edges'] as $edge) {
                if (isset($edge['node']) && is_array($edge['node'])) {
                    $records[] = $edge['node'];
                }
            }

            return $records;
        }

        // Plain list of records.
        if (array_keys($value) === range(0, count($value) - 1)) {
            return array_values(array_filter($value, 'is_array'));
        }

        return [];
    }

    /**
     * Fetch every page of a metadata collection
     *
     * @param string $endpoint Metadata endpoint ('objects' or 'fields')
     * @param string $collection Key holding the records in the response
     * @param array $query Extra query arguments
     * @return array|WP_Error Records on success, WP_Error on failure
     */
    private function fetch_all_metadata($endpoint, $collection, $query = []) {
        $records  = [];
        $cursor   = '';
        $max_page = 20;

        for ($page = 0; $page < $max_page; $page++) {
            $page_query = array_merge($query, ['limit' => 60]);

            if ($cursor !== '') {
                $page_query['starting_after'] = $cursor;
            }

            $response = $this->metadata_request($endpoint, $page_query);

            if (is_wp_error($response)) {
                return $response;
            }

            $page_records = $this->extract_collection($response, $collection);

            if (empty($page_records)) {
                break;
            }

            $records = array_merge($records, $page_records);

            $page_info = $response['__pageInfo'] ?? [];
            $has_next  = !empty($page_info['hasNextPage']);
            $cursor    = (string) ($page_info['endCursor'] ?? '');

            if (!$has_next || $cursor === '') {
                break;
            }
        }

        return $records;
    }

    /**
     * Fetch the workspace schema through the Metadata GraphQL API
     *
     * Used when the Metadata REST API is unavailable or returns nothing. Each
     * returned object carries its own field list under 'fields', so the whole
     * schema arrives in a single request.
     *
     * @return array|WP_Error Object records with nested fields, or WP_Error
     */
    private function fetch_schema_graphql() {
        $query = 'query GetObjects {'
            . ' objects(paging: {first: 200}) { edges { node {'
            . ' id nameSingular namePlural labelSingular labelPlural isActive isSystem isCustom'
            . ' fields(paging: {first: 200}) { edges { node {'
            . ' id name label type isActive isSystem isCustom options'
            . ' } } }'
            . ' } } } }';

        $response = $this->request('metadata', 'POST', ['query' => $query]);

        if (is_wp_error($response)) {
            return $response;
        }

        if (!empty($response['errors'])) {
            $messages = [];

            foreach ((array) $response['errors'] as $error) {
                if (!empty($error['message'])) {
                    $messages[] = (string) $error['message'];
                }
            }

            return new WP_Error(
                'graphql_error',
                sprintf(
                    /* translators: %s: Error message returned by the API */
                    __('Twenty CRM metadata query failed: %s', 'ht-contactform'),
                    implode(' ', $messages)
                )
            );
        }

        $records = $this->extract_collection($response, 'objects');
        $objects = [];

        foreach ($records as $record) {
            if (empty($record['nameSingular'])) {
                continue;
            }

            $record['fields'] = isset($record['fields'])
                ? $this->normalize_record_list($record['fields'])
                : [];

            $objects[] = $record;
        }

        return $objects;
    }

    /**
     * Fetch a metadata endpoint untouched, for diagnostics
     *
     * @param string $endpoint Metadata endpoint ('objects' or 'fields')
     * @return array|WP_Error Raw unwrapped response, or WP_Error
     */
    public function get_raw_metadata($endpoint = 'objects') {
        if ($endpoint === 'graphql') {
            return $this->request('metadata', 'POST', [
                'query' => 'query GetObjects { objects(paging: {first: 200}) { edges { node { id nameSingular namePlural isActive isSystem } } } }',
            ]);
        }

        $endpoint = in_array($endpoint, ['objects', 'fields'], true) ? $endpoint : 'objects';

        return $this->metadata_request($endpoint, ['limit' => 60]);
    }

    /**
     * Get the workspace objects available for mapping
     *
     * @return array|WP_Error List of objects on success, WP_Error on failure
     */
    public function get_objects() {
        $cache_key = $this->metadata_cache_key('objects');
        $cached    = get_transient($cache_key);

        // An empty cached array means a previous lookup failed; ignore it and
        // retry rather than serving the failure for the rest of the TTL.
        if (is_array($cached) && !empty($cached)) {
            return $cached;
        }

        $records = $this->fetch_all_metadata('objects', 'objects');

        if (is_wp_error($records)) {
            return $records;
        }

        // Some instances do not serve the Metadata REST API (or serve it empty)
        // while the Metadata GraphQL API works; fall back to it before giving up.
        if (empty($records)) {
            $records = $this->fetch_schema_graphql();

            if (is_wp_error($records)) {
                return $records;
            }
        }

        $objects = [];
        $names   = [];

        foreach ($records as $object) {
            if (empty($object['nameSingular'])) {
                continue;
            }

            if (isset($object['isActive']) && !$object['isActive']) {
                continue;
            }

            // Twenty marks internal plumbing (views, workspace members) as
            // system objects, but some builds flag standard CRM objects that
            // way too, so keep anything that is not obviously plumbing.
            if (!empty($object['isSystem']) && !in_array((string) $object['nameSingular'], self::$standard_objects, true)) {
                continue;
            }

            // Both the REST and GraphQL object payloads embed each object's own
            // field list. Keep it: the /rest/metadata/fields endpoint ignores
            // the objectMetadataId filter and returns a mixed, paginated set,
            // so the embedded list is the only reliable per-object source.
            if (!empty($object['fields'])) {
                $embedded = $this->normalize_record_list($object['fields']);

                if (!empty($embedded)) {
                    set_transient(
                        $this->metadata_cache_key('raw_fields_' . strtolower((string) $object['nameSingular'])),
                        $embedded,
                        self::METADATA_TTL
                    );
                }
            }

            $objects[] = [
                'nameSingular'  => (string) $object['nameSingular'],
                'namePlural'    => (string) ($object['namePlural'] ?? ''),
                'labelSingular' => (string) ($object['labelSingular'] ?? $object['nameSingular']),
                'labelPlural'   => (string) ($object['labelPlural'] ?? ''),
                'id'            => (string) ($object['id'] ?? ''),
                'isCustom'      => !empty($object['isCustom']),
            ];

            $names[] = (string) $object['nameSingular'];
        }

        // Never cache an empty schema; that would keep a transient failure
        // pinned for the whole TTL.
        if (!empty($objects)) {
            set_transient($cache_key, $objects, self::METADATA_TTL);
            set_transient($this->metadata_cache_key('object_names'), $names, self::METADATA_TTL);
        }

        return $objects;
    }

    /**
     * Resolve an object's metadata id and plural name
     *
     * @param string $name_singular Object singular name (e.g. 'person')
     * @return array|WP_Error ['id' => ..., 'namePlural' => ...] or WP_Error
     */
    private function get_object_descriptor($name_singular) {
        $objects = $this->get_objects();

        if (is_wp_error($objects)) {
            return $objects;
        }

        $needle = strtolower($name_singular);

        foreach ($objects as $object) {
            if (strtolower($object['nameSingular']) === $needle
                || strtolower($object['namePlural']) === $needle) {
                return $object;
            }
        }

        $available = implode(', ', array_map(function ($object) {
            return $object['nameSingular'];
        }, $objects));

        return new WP_Error(
            'object_not_found',
            sprintf(
                /* translators: %1$s: Requested object name, %2$s: Comma separated list of available objects */
                __('Twenty CRM object "%1$s" was not found in this workspace. Objects reported by the API: %2$s', 'ht-contactform'),
                $name_singular,
                $available !== '' ? $available : __('(none)', 'ht-contactform')
            )
        );
    }

    /**
     * Get the mappable fields of an object, with composite fields flattened
     *
     * Each returned row is one mapping target: composite fields such as
     * FULL_NAME produce one row per writable sub key ('name.firstName').
     *
     * @param string $object_name Object singular name (e.g. 'person')
     * @return array|WP_Error Field rows on success, WP_Error on failure
     */
    public function get_fields($object_name = 'person') {
        $object_name = sanitize_key($object_name);
        $cache_key   = $this->metadata_cache_key('fields_' . $object_name);
        $cached      = get_transient($cache_key);

        if (is_array($cached) && !empty($cached)) {
            return $cached;
        }

        $descriptor = $this->get_object_descriptor($object_name);

        if (is_wp_error($descriptor)) {
            return $descriptor;
        }

        // Resolving the object list caches each object's embedded field list;
        // that is the authoritative per-object source.
        $raw_cache_key = $this->metadata_cache_key('raw_fields_' . strtolower($descriptor['nameSingular']));
        $records       = get_transient($raw_cache_key);

        if (!is_array($records) || empty($records)) {
            // Last resort: the standalone fields endpoint. It ignores the
            // objectMetadataId filter on current builds, so the mixed result is
            // filtered by objectMetadataId below.
            $records = $this->fetch_all_metadata('fields', 'fields', [
                'filter' => $this->build_filter('objectMetadataId', 'eq', $descriptor['id']),
            ]);

            if (is_wp_error($records)) {
                return $records;
            }
        }

        if (empty($records)) {
            return new WP_Error(
                'fields_not_found',
                sprintf(
                    /* translators: %s: Object name */
                    __('Twenty CRM returned no fields for "%s". The API key may lack read access to this object.', 'ht-contactform'),
                    $descriptor['nameSingular']
                )
            );
        }

        // Older self-hosted builds may ignore the metadata filter and return
        // every field; drop anything that belongs to another object.
        $records = array_values(array_filter($records, function ($field) use ($descriptor) {
            if (empty($field['objectMetadataId'])) {
                return true;
            }
            return (string) $field['objectMetadataId'] === (string) $descriptor['id'];
        }));

        $fields = [];

        foreach ($records as $field) {
            $name = (string) ($field['name'] ?? '');
            $type = strtoupper((string) ($field['type'] ?? 'TEXT'));

            if ($name === '') {
                continue;
            }

            if (isset($field['isActive']) && !$field['isActive']) {
                continue;
            }

            if (!empty($field['isSystem']) && $name !== 'name') {
                continue;
            }

            if (in_array($type, self::$unsupported_types, true)) {
                continue;
            }

            $label   = (string) ($field['label'] ?? $name);
            $options = $this->extract_field_options($field);

            if (isset(self::$composite_fields[$type])) {
                foreach (self::$composite_fields[$type] as $sub) {
                    $fields[] = [
                        'key'      => $name . '.' . $sub,
                        'field'    => $name,
                        'sub'      => $sub,
                        'label'    => $label . ' → ' . (self::$sub_labels[$sub] ?? $sub),
                        'type'     => $type,
                        'options'  => [],
                        'required' => ($name === 'emails' && $sub === 'primaryEmail'),
                        'isCustom' => !empty($field['isCustom']),
                    ];
                }
                continue;
            }

            $fields[] = [
                'key'      => $name,
                'field'    => $name,
                'sub'      => null,
                'label'    => $label,
                'type'     => $type,
                'options'  => $options,
                'required' => false,
                'isCustom' => !empty($field['isCustom']),
            ];
        }

        if (!empty($fields)) {
            set_transient($cache_key, $fields, self::METADATA_TTL);
        }

        return $fields;
    }

    /**
     * Extract selectable option values from a SELECT/MULTI_SELECT field
     *
     * @param array $field Raw metadata field
     * @return array List of ['value' => ..., 'label' => ...]
     */
    private function extract_field_options($field) {
        if (empty($field['options']) || !is_array($field['options'])) {
            return [];
        }

        $options = [];

        foreach ($field['options'] as $option) {
            if (!is_array($option) || !isset($option['value'])) {
                continue;
            }

            $options[] = [
                'value' => (string) $option['value'],
                'label' => (string) ($option['label'] ?? $option['value']),
            ];
        }

        return $options;
    }

    /**
     * Build a lookup of field name => metadata row
     *
     * @param string $object_name Object singular name
     * @return array|WP_Error Map of field name to descriptor, or WP_Error
     */
    private function get_field_types($object_name) {
        $fields = $this->get_fields($object_name);

        if (is_wp_error($fields)) {
            return $fields;
        }

        $types = [];

        foreach ($fields as $field) {
            $types[$field['field']] = [
                'type'    => $field['type'],
                'options' => $field['options'],
            ];
        }

        return $types;
    }

    //-------------------------------------------------------------------------
    // PAYLOAD BUILDING
    //-------------------------------------------------------------------------

    /**
     * Turn a flat mapping into the nested payload Twenty CRM expects
     *
     * @param array $mapping Mapping of field key (possibly 'field.sub') to smart tag value
     * @param array $form_data Form submission data
     * @param array $form Form configuration
     * @param array $field_types Map of field name to descriptor from get_field_types()
     * @return array Payload ready to send
     */
    private function build_payload($mapping, $form_data, $form, $field_types) {
        $payload = [];

        if (empty($mapping) || !is_array($mapping)) {
            return $payload;
        }

        foreach ($mapping as $key => $raw_value) {
            if ($raw_value === '' || $raw_value === null || is_array($raw_value)) {
                continue;
            }

            $value = $this->process_field_value($raw_value, $form_data, $form);

            if ($value === '' || $value === null) {
                continue;
            }

            $parts = explode('.', (string) $key, 2);
            $field = $parts[0];
            $sub   = $parts[1] ?? null;
            $type  = strtoupper($field_types[$field]['type'] ?? 'TEXT');

            if ($sub === null) {
                // Composite fields must be addressed through a sub key; sending
                // a bare string where Twenty expects an object is rejected.
                if (isset(self::$composite_fields[$type])) {
                    continue;
                }

                $cast = $this->cast_scalar($value, $type);

                if ($cast !== null) {
                    $payload[$field] = $cast;
                }
                continue;
            }

            $cast = $this->cast_sub($value, $type, $sub);

            if ($cast === null) {
                continue;
            }

            if (!isset($payload[$field]) || !is_array($payload[$field])) {
                $payload[$field] = [];
            }

            $payload[$field][$sub] = $cast;
        }

        return $payload;
    }

    /**
     * Cast a mapped value for a scalar field type
     *
     * @param string $value Processed value
     * @param string $type Twenty field type
     * @return mixed|null Cast value, or null to skip the field
     */
    private function cast_scalar($value, $type) {
        switch ($type) {
            case 'NUMBER':
            case 'NUMERIC':
            case 'RATING':
                if (!is_numeric($value)) {
                    return null;
                }
                return 0 + $value;

            case 'BOOLEAN':
                return rest_sanitize_boolean($value);

            case 'DATE':
                $timestamp = strtotime($value);
                return $timestamp ? gmdate('Y-m-d', $timestamp) : null;

            case 'DATE_TIME':
                $timestamp = strtotime($value);
                return $timestamp ? gmdate('c', $timestamp) : null;

            case 'RAW_JSON':
                $decoded = json_decode($value, true);
                return json_last_error() === JSON_ERROR_NONE ? $decoded : null;

            case 'MULTI_SELECT':
                $items = array_filter(array_map('trim', explode(',', $value)));
                return !empty($items) ? array_values(array_map('sanitize_text_field', $items)) : null;

            case 'ARRAY':
                $items = array_filter(array_map('trim', explode(',', $value)));
                return array_values(array_map('sanitize_text_field', $items));

            default:
                return sanitize_text_field($value);
        }
    }

    /**
     * Cast a mapped value for a composite field sub key
     *
     * @param string $value Processed value
     * @param string $type Twenty field type
     * @param string $sub Sub key
     * @return mixed|null Cast value, or null to skip the sub key
     */
    private function cast_sub($value, $type, $sub) {
        if ($type === 'EMAILS' && $sub === 'primaryEmail') {
            $email = sanitize_email($value);
            return is_email($email) ? $email : null;
        }

        if ($type === 'LINKS' && $sub === 'primaryLinkUrl') {
            $url = esc_url_raw($value);
            return $url !== '' ? $url : null;
        }

        if ($type === 'CURRENCY' && $sub === 'amountMicros') {
            if (!is_numeric($value)) {
                return null;
            }
            return (int) round(((float) $value) * 1000000);
        }

        if ($type === 'PHONES' && $sub === 'primaryPhoneCallingCode') {
            $code = preg_replace('/[^0-9]/', '', $value);
            return $code !== '' ? '+' . $code : null;
        }

        if ($type === 'PHONES' && $sub === 'primaryPhoneCountryCode') {
            $code = strtoupper(preg_replace('/[^A-Za-z]/', '', $value));
            return $code !== '' ? substr($code, 0, 2) : null;
        }

        if (($type === 'RICH_TEXT' || $type === 'RICH_TEXT_V2') && $sub === 'markdown') {
            return sanitize_textarea_field($value);
        }

        return sanitize_text_field($value);
    }

    //-------------------------------------------------------------------------
    // RECORD OPERATIONS
    //-------------------------------------------------------------------------

    /**
     * Find a person by primary email
     *
     * @param string $email Email address
     * @return array|WP_Error Person record, or WP_Error when not found
     */
    public function find_person_by_email($email, $include_deleted = false) {
        $filter = $this->build_filter('emails.primaryEmail', 'eq', $email);

        if ($include_deleted) {
            // Twenty hides soft-deleted records by default, but its duplicate
            // detection still matches them, so they have to be searchable here.
            $filter .= ',' . 'deletedAt[is]:NOT_NULL';
        }

        $response = $this->api_request('people', 'GET', [], [
            'filter' => $filter,
            'limit'  => 1,
            'depth'  => 0,
        ]);

        if (is_wp_error($response)) {
            return $response;
        }

        $people = $response['people'] ?? [];

        if (empty($people) || !is_array($people)) {
            return new WP_Error('person_not_found', __('Person not found', 'ht-contactform'));
        }

        return $people[0];
    }

    /**
     * Find a person by email, including soft-deleted records
     *
     * @param string $email Email address
     * @return array|null Person record, or null when nothing matches
     */
    private function find_person_including_deleted($email) {
        foreach ([false, true] as $include_deleted) {
            $found = $this->find_person_by_email($email, $include_deleted);

            if (!is_wp_error($found) && !empty($found['id'])) {
                return $found;
            }
        }

        return null;
    }

    /**
     * Whether an error is Twenty's duplicate rejection
     *
     * @param WP_Error $error Error returned by the API
     * @return bool True when the API rejected the write as a duplicate
     */
    private function is_duplicate_error($error) {
        if (!is_wp_error($error)) {
            return false;
        }

        return stripos($error->get_error_message(), 'duplicate') !== false;
    }

    /**
     * Un-archive a soft-deleted record before writing to it
     *
     * deletedAt is a read-only system field, so the restore is issued as its
     * own request: folding it into the data write would make a rejected
     * restore take the whole submission down with it. A failure here is
     * logged and the write proceeds against the archived record.
     *
     * @param string $collection REST collection ('people' or 'companies')
     * @param array $existing Matched record
     * @return void
     */
    private function restore_if_deleted($collection, $existing) {
        if (empty($existing['deletedAt']) || empty($existing['id'])) {
            return;
        }

        $restored = $this->api_request(
            $collection . '/' . rawurlencode((string) $existing['id']),
            'PATCH',
            ['deletedAt' => null]
        );

        if (is_wp_error($restored)) {
            error_log(sprintf(
                'HT Contact Form Twenty CRM could not restore archived %s record %s: %s',
                $collection,
                $existing['id'],
                $restored->get_error_message()
            ));
        }
    }

    /**
     * Create a person record
     *
     * @param array $payload Person payload
     * @return array|WP_Error Created record on success, WP_Error on failure
     */
    public function create_person($payload) {
        $response = $this->api_request('people', 'POST', $payload);

        if (is_wp_error($response)) {
            return $response;
        }

        return $response['createPerson'] ?? $response['person'] ?? $response;
    }

    /**
     * Update a person record
     *
     * @param string $person_id Person id
     * @param array $payload Person payload
     * @return array|WP_Error Updated record on success, WP_Error on failure
     */
    public function update_person($person_id, $payload) {
        $response = $this->api_request('people/' . rawurlencode($person_id), 'PATCH', $payload);

        if (is_wp_error($response)) {
            return $response;
        }

        return $response['updatePerson'] ?? $response['person'] ?? $response;
    }

    /**
     * Find a company by domain or name, creating it when missing
     *
     * @param array $payload Company payload built from the mapping
     * @return string|WP_Error Company id on success, WP_Error on failure
     */
    public function find_or_create_company($payload) {
        $domain = $payload['domainName']['primaryLinkUrl'] ?? '';
        $name   = $payload['name'] ?? '';

        if ($domain === '' && $name === '') {
            return new WP_Error(
                'company_data_missing',
                __('Company name or domain is required to create a company', 'ht-contactform')
            );
        }

        $existing = $this->find_company_including_deleted($domain, $name);

        if (!empty($existing['id'])) {
            $this->restore_if_deleted('companies', $existing);
            return (string) $existing['id'];
        }

        $response = $this->api_request('companies', 'POST', $payload);

        // Twenty's duplicate detection also matches soft-deleted companies and
        // criteria beyond the ones searched above, so recover from its
        // rejection by reusing whatever it matched.
        if (is_wp_error($response) && $this->is_duplicate_error($response)) {
            $duplicate = $this->find_company_including_deleted($domain, $name);

            if (!empty($duplicate['id'])) {
                $this->restore_if_deleted('companies', $duplicate);
                return (string) $duplicate['id'];
            }

            return new WP_Error(
                'duplicate_entry',
                __('Twenty CRM rejected the company as a duplicate, but no matching company could be found. Check for an archived company with the same name or domain.', 'ht-contactform'),
                ['status' => 400]
            );
        }

        if (is_wp_error($response)) {
            return $response;
        }

        $company = $response['createCompany'] ?? $response['company'] ?? $response;

        if (empty($company['id'])) {
            return new WP_Error('company_create_failed', __('Twenty CRM did not return a company id', 'ht-contactform'));
        }

        return (string) $company['id'];
    }

    /**
     * Find a company by domain then name, including soft-deleted records
     *
     * @param string $domain Domain URL from the mapping
     * @param string $name Company name from the mapping
     * @return array|null Company record, or null when nothing matches
     */
    private function find_company_including_deleted($domain, $name) {
        $filters = [];

        if ($domain !== '') {
            $host      = wp_parse_url($domain, PHP_URL_HOST);
            $host      = $host ? preg_replace('/^www\./i', '', $host) : $domain;
            $filters[] = $this->build_filter('domainName.primaryLinkUrl', 'ilike', '%' . $host . '%');
        }

        if ($name !== '') {
            $filters[] = $this->build_filter('name', 'eq', $name);
        }

        foreach ($filters as $filter) {
            // Live records first, then archived ones, mirroring the person path.
            foreach (['', ',deletedAt[is]:NOT_NULL'] as $deleted_clause) {
                $found = $this->find_company(['filter' => $filter . $deleted_clause]);

                if (!empty($found['id'])) {
                    return $found;
                }
            }
        }

        return null;
    }

    /**
     * Look up a single company
     *
     * @param array $query Query arguments including the filter expression
     * @return array|null Company record, or null when not found or on error
     */
    private function find_company($query) {
        $response = $this->api_request('companies', 'GET', [], array_merge($query, [
            'limit' => 1,
            'depth' => 0,
        ]));

        if (is_wp_error($response)) {
            return null;
        }

        $companies = $response['companies'] ?? [];

        return !empty($companies[0]) ? $companies[0] : null;
    }

    //-------------------------------------------------------------------------
    // SUBMISSION HANDLER
    //-------------------------------------------------------------------------

    /**
     * Create or update a person from a form submission
     *
     * @param object $integration Integration settings
     * @param array $form Form configuration
     * @param array $form_data Form submission data
     * @param array $meta Submission metadata
     * @return WP_Error|WP_REST_Response Response
     */
    public function subscribe($integration, $form, $form_data, $meta) {
        if (!is_object($integration)) {
            return new WP_Error('invalid_integration', __('Invalid integration configuration', 'ht-contactform'));
        }

        if (empty($integration->enabled)) {
            return new WP_Error('integration_disabled', __('Integration is not enabled for this form', 'ht-contactform'));
        }

        $person_types = $this->get_field_types('person');

        if (is_wp_error($person_types)) {
            return $person_types;
        }

        $mapping = isset($integration->field_mapping) && is_array($integration->field_mapping)
            ? $integration->field_mapping
            : [];

        $payload = $this->build_payload($mapping, $form_data, $form, $person_types);

        $email = $payload['emails']['primaryEmail'] ?? '';

        if (empty($email)) {
            return new WP_Error(
                'invalid_email',
                __('A valid email address is required to create a Twenty CRM person', 'ht-contactform')
            );
        }

        // Company handling runs first so the person can be linked on create.
        $company_id = '';

        if (!empty($integration->create_company)) {
            $company_types = $this->get_field_types('company');

            if (!is_wp_error($company_types)) {
                $company_mapping = isset($integration->company_mapping) && is_array($integration->company_mapping)
                    ? $integration->company_mapping
                    : [];

                $company_payload = $this->build_payload($company_mapping, $form_data, $form, $company_types);

                if (!empty($company_payload)) {
                    $company = $this->find_or_create_company($company_payload);

                    if (is_wp_error($company)) {
                        error_log(sprintf(
                            'HT Contact Form Twenty CRM company error (Form ID: %s): %s',
                            $form['id'] ?? 'unknown',
                            $company->get_error_message()
                        ));
                    } else {
                        $company_id            = $company;
                        $payload['companyId']  = $company_id;
                    }
                }
            }
        }

        $update_existing = !isset($integration->update_existing) || !empty($integration->update_existing);
        $existing        = $update_existing ? $this->find_person_including_deleted($email) : null;

        if (!empty($existing['id'])) {
            $this->restore_if_deleted('people', $existing);
            $response = $this->update_person((string) $existing['id'], $payload);
        } else {
            $response = $this->create_person($payload);

            // Twenty's duplicate detection also matches soft-deleted records and
            // criteria beyond email (name, LinkedIn URL). When it rejects the
            // create, fall back to updating whatever it matched.
            if (is_wp_error($response) && $this->is_duplicate_error($response) && $update_existing) {
                $duplicate = $this->find_person_including_deleted($email);

                if (!empty($duplicate['id'])) {
                    $this->restore_if_deleted('people', $duplicate);
                    $response = $this->update_person((string) $duplicate['id'], $payload);
                } else {
                    $response = new WP_Error(
                        'duplicate_entry',
                        __('Twenty CRM rejected this submission as a duplicate, but no matching person could be found by email. Twenty also de-duplicates on name and LinkedIn URL; check for an archived record with the same details.', 'ht-contactform'),
                        ['status' => 400]
                    );
                }
            }
        }

        if (is_wp_error($response)) {
            do_action('ht_form/twentycrm_integration_result', null, 'failed', $response->get_error_message());
            return $response;
        }

        $person_id = isset($response['id']) ? (string) $response['id'] : '';

        do_action(
            'ht_form/twentycrm_integration_result',
            $response,
            'success',
            __('Twenty CRM person created/updated successfully', 'ht-contactform')
        );

        return new WP_REST_Response([
            'message'    => __('Twenty CRM person created/updated successfully', 'ht-contactform'),
            'person_id'  => $person_id,
            'company_id' => $company_id,
        ], 200);
    }

    /**
     * Process field value with smart tags
     *
     * @param string $value The field value or smart tag
     * @param array $form_data Form submission data
     * @param array $form Form configuration
     * @return string Processed value
     */
    private function process_field_value($value, $form_data, $form) {
        if ($this->helper === null) {
            return $value;
        }

        return $this->helper->filter_vars($value, $form_data, $form);
    }
}
