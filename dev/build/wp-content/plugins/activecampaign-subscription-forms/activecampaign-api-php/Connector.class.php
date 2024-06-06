<?php

class AC_ConnectorWordPress
{

    public $url;
    public $api_key;
    public $output = "json";

    function __construct($url, $api_key, $api_user = "", $api_pass = "")
    {
        // $api_pass should be md5() already
        $base = "";
        if (!preg_match("/https:\/\/www.activecampaign.com/", $url)) {
            // not a reseller
            $base = "/admin";
        }
        if (preg_match("/\/$/", $url)) {
            // remove trailing slash
            $url = substr($url, 0, strlen($url) - 1);
        }
        if ($api_user && $api_pass) {
            $this->url = "{$url}{$base}/api.php?api_user={$api_user}&api_pass={$api_pass}";
        } else {
            $this->url = "{$url}{$base}/api.php?";
        }
        $this->api_key = $api_key;
    }

    public function credentials_test()
    {
        $test_url = "{$this->url}&api_action=user_me&api_output={$this->output}";
        $r = $this->curl($test_url);
        if (is_object($r) && (int)$r->result_code) {
            // successful
            $r = true;
        } else {
            // failed
            $r = false;
        }
        return $r;
    }

    public function curl($url, $params_data = array(), $verb = "", $custom_method = "")
    {
        if ($this->version == 1) {
            // find the method from the URL.
            $method = preg_match("/api_action=[^&]*/i", $url, $matches);
            if ($matches) {
                $method = preg_match("/[^=]*$/i", $matches[0], $matches2);
                $method = $matches2[0];
            } elseif ($custom_method) {
                $method = $custom_method;
            }
        } elseif ($this->version == 2) {
            $method = $custom_method;
            $url .= "?";
        }
        if ($params_data && $verb == "GET") {
            if ($this->version == 2) {
                $url .= "&" . $params_data;
            }
        } else {
            if ($params_data && !$verb) {
                // if no verb passed but there IS params data, it's likely POST.
                $verb = "POST";
            } elseif ($params_data && $verb) {
                // $verb is likely "POST" or "PUT".
            } else {
                $verb = "GET";
            }
        }
        if ($verb == "POST" || $verb == "PUT" || $verb == "DELETE") {
            $data = "";
            if (is_array($params_data)) {
                foreach ($params_data as $key => $value) {
                    if (is_array($value)) {
                        if (is_int($key)) {
                            // array two levels deep
                            foreach ($value as $key_ => $value_) {
                                if (is_array($value_)) {
                                    foreach ($value_ as $k => $v) {
                                        $k = urlencode($k);
                                        $data .= "{$key_}[{$key}][{$k}]=" . urlencode($v) . "&";
                                    }
                                } else {
                                    $data .= "{$key_}[{$key}]=" . urlencode($value_) . "&";
                                }
                            }
                        } else {
                            // IE: [group] => array(2 => 2, 3 => 3)
                            // normally we just want the key to be a string, IE: ["group[2]"] => 2
                            // but we want to allow passing both formats
                            foreach ($value as $k => $v) {
                                if (!is_array($v)) {
                                    $k = urlencode($k);
                                    $data .= "{$key}[{$k}]=" . urlencode($v) . "&";
                                }
                            }
                        }
                    } else {
                        $data .= "{$key}=" . urlencode($value) . "&";
                    }
                }
            } else {
                // not an array - perhaps serialized or JSON string?
                // just pass it as data
                $data = "data={$params_data}";
            }

            $data = rtrim($data, "& ");
        }

        // Use native WordPress HTTP method.
        // We only need GET support because our WordPress plugin doesn't currently make any other type of requests.
        $args = array( 'headers' => array(
            'user-agent' => 'ActiveCampaign WordPress Plugin',
            'Api-Token' => $this->api_key ) );
        $response = wp_safe_remote_get($url, $args);

        // If the response code is actually based off WP_ERROR Send the error back instead;
        if (is_object($response) && get_class($response) === 'WP_Error') {
            foreach ($response->get_error_messages() as $error) {
                echo $error . "<br />";
            }
            exit;
        }

        $http_code = $response["response"]["code"];

        $object = json_decode($response["body"]);

        if (!is_object($object) || (!isset($object->result_code) && !isset($object->succeeded) && !isset($object->success))) {
            // add methods that only return a string
            $string_responses = array("tracking_event_remove", "contact_list", "form_html", "tracking_site_status", "tracking_event_status", "tracking_whitelist", "tracking_log", "tracking_site_list", "tracking_event_list");
            if (in_array($method, $string_responses)) {
                return $response;
            }
            // something went wrong
            $response = $response["body"];
            return "An unexpected problem occurred with the API request. Some causes include: invalid JSON or XML returned. Here is the actual response from the server: ---- " . $response;
        }

        //header("HTTP/1.1 " . $http_code);
        $object->http_code = $http_code;

        if (isset($object->result_code)) {
            $object->success = $object->result_code;
            if (!(int)$object->result_code) {
                $object->error = $object->result_message;
            }
        } elseif (isset($object->succeeded)) {
            // some calls return "succeeded" only
            $object->success = $object->succeeded;
            if (!(int)$object->succeeded) {
                $object->error = $object->message;
            }
        }
        return $object;
    }
}
