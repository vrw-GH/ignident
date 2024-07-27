<?php
ob_start();
class Ays_PopupBox_List_Table extends WP_List_Table {
    private $plugin_name;
    private $title_length;

    /** Class constructor */
    public function __construct($plugin_name) {
        $this->plugin_name = $plugin_name;
        $this->title_length = Ays_Pb_Admin::get_listtables_title_length('popups');

        parent::__construct(array(
            "singular" => __( "PopupBox", "ays-popup-box" ), //singular name of the listed records
            "plural" => __( "PopupBoxes", "ays-popup-box" ), //plural name of the listed records
            "ajax" => false //does this table support ajax?
        ));

        add_action( "admin_notices", array($this, "popupbox_notices") );
    }

    public function popupbox_notices() {
        $status = isset($_REQUEST["status"]) ? sanitize_text_field($_REQUEST["status"]) : "";
        $type = isset($_REQUEST["type"]) ? sanitize_text_field($_REQUEST["type"]) : "";

        if (empty($status)) return;

        if ("created" == $status)
            $updated_message = esc_html( __("PopupBox created.", "ays-popup-box") );
        elseif ("updated" == $status)
            $updated_message = esc_html( __("PopupBox saved.", "ays-popup-box") );
        elseif ("deleted" == $status)
            $updated_message = esc_html( __("PopupBox deleted.", "ays-popup-box") );
        elseif ("duplicated" == $status)
            $updated_message = esc_html( __("PopupBox duplicated.", "ays-popup-box") );
        elseif ("published" == $status)
            $updated_message = esc_html( __("PopupBox published.", "ays-popup-box") );
        elseif ("unpublished" == $status)
            $updated_message = esc_html( __("PopupBox unpublished.", "ays-popup-box") );
        elseif ("error" == $status)
            $updated_message = __( "You're not allowed to add popupbox for more popupboxes please checkout to ", "ays-popup-box")."<a href='https://ays-pro.com/wordpress/popup-box' target='_blank'>PRO ".__("version", "ays-popup-box")."</a>.";

        if (empty($updated_message)) return;

        ?>
        <div class="notice notice-<?php echo esc_attr($type); ?> is-dismissible">
            <p> <?php echo $updated_message; ?> </p>
        </div>
        <?php
    }

    protected function get_views() {
        $published_count = $this->published_popup_count();
        $unpublished_count = $this->unpublished_popup_count();
        $all_count = $this->all_record_count();
        $selected_all = "";
        $selected_off = "";
        $selected_on = "";

        if (isset($_GET['fstatus'])) {
            switch($_GET['fstatus']) {
                case "unpublished":
                    $selected_off = "style='font-weight:bold;'";
                    break;
                case "published":
                    $selected_on = "style='font-weight:bold;'";
                    break;
                default:
                    $selected_all = "style='font-weight:bold;'";
                    break;
            }
        } else {
            $selected_all = "style='font-weight:bold;'";
        }

        $href = "?page=" . esc_attr($_REQUEST['page']);
        $href = $this->ays_pb_add_filters_to_link($href);

        $status_links = array(
            "all" => "<a " . $selected_all . " href='" . $href . "'>" . __('All', "ays-popup-box") . " (" . $all_count . ")</a>",
            "published" => "<a " . $selected_on . " href='" . $href . "&fstatus=published'>" . __('Published', "ays-popup-box") . " (" . $published_count . ")</a>",
            "unpublished" => "<a " . $selected_off . " href='" . $href . "&fstatus=unpublished'>" . __('Unpublished', "ays-popup-box") . " (" . $unpublished_count . ")</a>"
        );

        return $status_links;
    }

    public static function published_popup_count() {
        global $wpdb;

        $base_sql = "SELECT COUNT(*) FROM {$wpdb->prefix}ays_pb WHERE onoffswitch='On'";
        $sql = self::ays_pb_add_filters_to_sql($base_sql);

        return $wpdb->get_var($sql);
    }

    public static function unpublished_popup_count() {
        global $wpdb;

        $base_sql = "SELECT COUNT(*) FROM {$wpdb->prefix}ays_pb WHERE onoffswitch='Off'";
        $sql = self::ays_pb_add_filters_to_sql($base_sql);

        return $wpdb->get_var($sql);
    }

    public static function all_record_count() {
        global $wpdb;

        $base_sql = "SELECT COUNT(*) FROM {$wpdb->prefix}ays_pb WHERE 1=1";
        $sql = self::ays_pb_add_filters_to_sql($base_sql);

        return $wpdb->get_var($sql);
    }

    protected static function ays_pb_add_filters_to_sql($base_sql) {
        $sql = $base_sql;
        $filter_conditions = self::ays_pb_get_filter_conditions();

        if (!empty($filter_conditions)) {
            $sql .= " AND " . implode(" AND ", $filter_conditions);
        }

        return $sql;
    }

    protected static function ays_pb_get_filter_conditions() {
        global $wpdb;
        $conditions = array();

        if (isset($_GET['filterby']) && absint(sanitize_text_field($_GET['filterby'])) > 0) {
            $cat_id = absint(sanitize_text_field($_GET['filterby']));
            $conditions[] = 'category_id = ' . $cat_id;
        }

        if (isset($_GET['filterbyAuthor']) && $_GET['filterbyAuthor'] != '') {
            $ays_pb_author = esc_sql(sanitize_text_field($_GET['filterbyAuthor']));
            $conditions[] = 'JSON_EXTRACT(options, "$.create_author") = ' . $ays_pb_author;
        }

        if (isset($_GET['filterbyType']) && $_GET['filterbyType'] != '') {
            $ays_pb_type = esc_sql(sanitize_text_field($_GET['filterbyType']));
            $conditions[] = 'modal_content = "' . $ays_pb_type . '"';
        }

        if (isset($_GET['s']) && $_GET['s'] != '') {
            $search = esc_sql(sanitize_text_field($_GET['s']));
            $conditions[] = sprintf("title LIKE '%%%s%%' ", esc_sql($wpdb->esc_like($search)));
        }

        return $conditions;
    }

    protected function ays_pb_add_filters_to_link($href) {
        if (isset($_GET['filterby']) && absint(sanitize_text_field($_GET['filterby'])) > 0) {
            $cat_id = absint(sanitize_text_field($_GET['filterby']));
            $href .= '&filterby=' . $cat_id;
        }

        if (isset($_GET['filterbyAuthor']) && $_GET['filterbyAuthor'] != '') {
            $ays_pb_author = esc_sql(sanitize_text_field($_GET['filterbyAuthor']));
            $href .= '&filterbyAuthor=' . $ays_pb_author;
        }

        if (isset($_GET['filterbyType']) && $_GET['filterbyType'] != '') {
            $ays_pb_type = esc_sql(sanitize_text_field($_GET['filterbyType']));
            $href .= '&filterbyType=' . $ays_pb_type;
        }

        if (isset($_REQUEST['s']) && $_REQUEST['s'] != '') {
            $search = esc_sql(sanitize_text_field($_REQUEST['s']));
            $href .= '&s=' . $search;
        }

        return $href;
    }

    /**
     * Handles data query and filter, sorting, and pagination.
     */
    public function prepare_items() {
        global $wpdb;

        $this->_column_headers = $this->get_column_info();

        /** Process bulk action */
        $this->process_bulk_action();

        $per_page = $this->get_items_per_page("popupboxes_per_page", 20);
        $current_page = $this->get_pagenum();
        $total_items = self::record_count();

        $this->set_pagination_args( array(
            "total_items" => $total_items, // WE have to calculate the total number of items
            "per_page" => $per_page // WE have to determine how many items to show on a page
        ) );

        $search = isset($_REQUEST['s']) ? esc_sql( sanitize_text_field($_REQUEST['s']) ) : false;
        $do_search = $search ? sprintf(" title LIKE '%%%s%%' ", esc_sql($wpdb->esc_like($search)) ) : '';

        $this->items = self::get_ays_popupboxes($per_page, $current_page, $do_search);
    }

    /** Text displayed when no customer data is available */
    public function no_items() {
        echo __("There are no popupboxes yet.", "ays-popup-box");
    }

    /**
     *  Associative array of columns
     *
     * @return array
     */
    function get_columns() {
        $columns = array(
            "cb" => "<input type='checkbox' />",
            "title" => __("Title", "ays-popup-box"),
            "popup_image" => __("Image", "ays-popup-box"),
            'category_id' => __('Category', "ays-popup-box"),
            "onoffswitch" => __("Status", "ays-popup-box"),
            "modal_content" => __("Type", "ays-popup-box"),
            "view_type" => __("Template", "ays-popup-box"),
            "create_date" => __("Created", "ays-popup-box"),
            "views" => __("Views", "ays-popup-box"),
            "conversions" => __("Conversions", "ays-popup-box"),
            "id" => __("ID", "ays-popup-box"),
        );

        return $columns;
    }

    /**
     * Columns to make sortable.
     *
     * @return array
     */
    public function get_sortable_columns() {
        $sortable_columns = array(
            "title" => array("title", true),
            "category_id" => array("category_id", true),
            "modal_content" => array("modal_content", true),
            "id" => array("id", true),
        );

        return $sortable_columns;
    }

    /**
     * Render a column when no column specific method exist.
     *
     * @param array $item
     * @param string $column_name
     *
     * @return mixed
     */
    public function column_default($item, $column_name) {
        switch ($column_name) {
            case "title":
            case "popup_image":
            case "onoffswitch":
                return wp_unslash($item[$column_name]);
                break;
            case 'category_id':
            case 'modal_content':
            case 'view_type':
            case "shortcode":
            case "autor":
            case "create_date":
            case "views":
            case "conversions":
            case "id":
                return $item[$column_name];
                break;
            default:
                return print_r($item, true); //Show the whole array for troubleshooting purposes
        }
    }

    /**
     * Render the bulk edit checkbox
     *
     * @param array $item
     *
     * @return string
     */
    function column_cb($item) {
        return sprintf(
            "<input type='checkbox' name='bulk-delete[]' value='%s' />", $item["id"]
        );
    }

    /**
     * Method for name column
     *
     * @param array $item an array of DB data
     *
     * @return string
     */
    function column_title($item) {
        $delete_nonce = wp_create_nonce($this->plugin_name . "-delete-popupbox");

        $popup_name = ( isset($item["popup_name"]) && $item["popup_name"] != "" ) ? stripslashes( sanitize_text_field($item["popup_name"]) ) : stripslashes( sanitize_text_field($item["title"]) );

        $popup_title_length = intval($this->title_length);

        $restitle = Ays_Pb_Admin::ays_pb_restriction_string("word", esc_attr($popup_name), $popup_title_length);

        $title = sprintf("<a href='?page=%s&action=%s&popupbox=%d' title='%s'>%s</a>", esc_attr($_REQUEST["page"]), "edit", absint($item["id"]), esc_attr($popup_name), $restitle);

        $actions = array(
            'edit' => sprintf( "<a href='?page=%s&action=%s&popupbox=%d'>" . __('Edit', "ays-popup-box") . "</a>", esc_attr($_REQUEST["page"]), "edit", absint($item["id"]) ),
            'duplicate' => sprintf( "<a href='?page=%s&action=%s&popupbox=%d'>" . __('Duplicate', "ays-popup-box") . '</a>', esc_attr($_REQUEST['page']), 'duplicate', absint($item['id']) ),
            'delete' => sprintf( "<a class='ays_pb_confirm_del' data-message='%s' href='?page=%s&action=%s&popupbox=%d&_wpnonce=%s'>" . __('Delete', "ays-popup-box") . '</a>', $restitle, esc_attr($_REQUEST['page']), 'delete', absint($item['id']), $delete_nonce )
        );

        return $title . $this->row_actions($actions);
    }

    /**
     * Method for Image column
     *
     * @param array $item an array of DB data
     *
     * @return string
     */
    function column_popup_image($item) {
        global $wpdb;

        $popup_image = ( isset($item['bg_image']) && $item['bg_image'] != '' ) ? esc_url($item['bg_image']) : '';

        $image_html = array();
        $edit_page_url = '';

        if ($popup_image != '') {
            if ( isset($item['id']) && absint($item['id']) > 0 ) {
                $edit_page_url = sprintf( 'href="?page=%s&action=%s&popupbox=%d"', esc_attr($_REQUEST['page']), 'edit', absint($item['id']) );
            }

            $popup_image_url = $popup_image;
            $this_site_path = trim(get_site_url(), "https:");

            if ( strpos( trim($popup_image_url, "https:"), $this_site_path ) !== false ) {
                $query = "SELECT * FROM `" . $wpdb->prefix . "posts` WHERE `post_type` = 'attachment' AND `guid` = '" . $popup_image_url . "'";
                $result_img = $wpdb->get_results($query, "ARRAY_A");

                if (!empty($result_img)) {
                    $url_img = wp_get_attachment_image_src($result_img[0]['ID'], 'thumbnail');

                    if ($url_img !== false) {
                        $popup_image_url = $url_img[0];
                    }
                }
            }

            $image_html[] = '<div class="ays-popup-image-list-table-column">';
                $image_html[] = '<a ' . $edit_page_url . ' class="ays-popup-image-list-table-link-column">';
                    $image_html[] = '<img src="' . $popup_image_url . '" class="ays-popup-image-list-table-img-column">';
                $image_html[] = '</a>';
            $image_html[] = '</div>';
        }

        $image_html = implode('', $image_html);

        return $image_html;
    }

    /**
     * Method for Category column
     *
     * @param array $item an array of DB data
     *
     * @return string
     */
    function column_category_id($item) {
        global $wpdb;

        $sql = "SELECT * FROM {$wpdb->prefix}ays_pb_categories WHERE id=" . absint( sanitize_text_field($item["category_id"]) );
        $category = $wpdb->get_row($sql);

        $category_title = '';
        if ($category !== null) {
            $category_title = ( isset($category->title) && $category->title != "" ) ? sanitize_text_field($category->title) : "";

            if ($category_title != "") {
                $category_title = sprintf('<a href="?page=%s&action=edit&popup_category=%d" target="_blank">%s</a>', esc_attr($_REQUEST['page']) . '-categories', $item["category_id"], $category_title);
            }
        }

        return $category_title;
    }

    /**
     * Method for Status column
     *
     * @param array $item an array of DB data
     *
     * @return string
     */
    function column_onoffswitch($item) {
        $onoffswitch = ( isset($item['onoffswitch']) && $item['onoffswitch'] == 'On' ) ? true : false;

        $nonce = $onoffswitch ? wp_create_nonce($this->plugin_name . "-unpublish-popupbox") : wp_create_nonce($this->plugin_name . "-publish-popupbox");

        $checked = $onoffswitch ? 'checked' : '';
        $href_value = $onoffswitch ? 'unpublish' : 'publish';
        $href = sprintf('?page=%s&action=%s&popupbox=%d&_wpnonce=%s', esc_attr($_REQUEST['page']), $href_value, absint($item['id']), $nonce);

        $href = $this->ays_pb_add_filters_to_link($href);

        if (isset($_GET['fstatus']) && $_GET['fstatus'] != '') {
            $ays_pb_status = esc_sql(sanitize_text_field($_GET['fstatus']));
            $href .= '&fstatus=' . $ays_pb_status;
        }

        $status_html = array();

        $status_html[] = '<label class="ays-pb-enable-switch ays-pb-enable-switch-list-table">';
            $status_html[] = '<input type="checkbox" class="ays-pb-onoffswitch-checkbox"' . $checked . '>';
            $status_html[] = '<a href="' . $href . '" class="ays-pb-enable-switch-slider ays-pb-enable-switch-round"></a>';
        $status_html[] = '</label>';

        $status_html = implode('', $status_html);
        return $status_html;
    }

    /**
     * Method for Type column
     *
     * @param array $item an array of DB data
     *
     * @return string
     */
    function column_modal_content($item) {
        $modal_content = '';

        switch ($item['modal_content']) {
            case 'custom_html':
                $modal_content = __('Custom Content', "ays-popup-box");
                break;
            case 'shortcode':
                $modal_content = __('Shortcode', "ays-popup-box");
                break;
            case 'video_type':
                $modal_content = __('Video', "ays-popup-box");
                break;
            case 'image_type':
                $modal_content = __('Image', "ays-popup-box");
                break;
            case 'facebook_type':
                $modal_content = __('Facebook', "ays-popup-box");
                break;
            case 'notification_type':
                $modal_content = __('Notification', "ays-popup-box");
                break;
            default:
                $modal_content = __('Custom Content', "ays-popup-box");
                break;
        }

        return $modal_content;
    }

    /**
     * Method for Template column
     *
     * @param array $item an array of DB data
     *
     * @return string
     */
    function column_view_type($item) {
        $view_type = '';

        switch ($item['view_type']) {
            case 'default':
                $view_type = __('Default', "ays-popup-box");
                break;
            case 'lil':
                $view_type = __('Red', "ays-popup-box");
                break;
            case 'image':
                $view_type = __('Modern', "ays-popup-box");
                break;
            case 'minimal':
                $view_type = __('Minimal', "ays-popup-box");
                break;
            case 'template':
                $view_type = __('Sale', "ays-popup-box");
                break;
            case 'mac':
                $view_type = __('MacOs window', "ays-popup-box");
                break;
            case 'ubuntu':
                $view_type = __('Ubuntu', "ays-popup-box");
                break;
            case 'winXP':
                $view_type = __('Windows XP', "ays-popup-box");
                break;
            case 'win98':
                $view_type = __('Windows 98', "ays-popup-box");
                break;
            case 'cmd':
                $view_type = __('Command Prompt', "ays-popup-box");
                break;
            default:
                $view_type = __('Default', "ays-popup-box");
                break;
        }

        return $view_type;
    }

    function column_create_date($item) {
        $options = (isset($item['options']) && $item['options'] != '') ? json_decode($item['options'], true) : array();
        $date = isset($options['create_date']) && $options['create_date'] != '' ? $options['create_date'] : "0000-00-00 00:00:00";

        $author = array("name" => "Unknown");
        if ( isset($options['author']) ) {
            if ( is_array($options['author']) ) {
                $author = $options['author'];
            } else {
                $author = json_decode($options['author'], true);
            }
        }

        $text = "";
        if (Ays_Pb_Admin::validateDate($date)) {
            $text .= "<p><b>Date:</b> " . $date . "</p>";
        }

        if ( isset($author['name']) && $author['name'] !== "Unknown" ) {
            $text .= "<p><b>Author:</b> " . $author['name'] . "</p>";
        }

        return $text;
    }

    public function process_bulk_action() {
        //Detect when a bulk action is being triggered...
        $message = "deleted";
        if ( "delete" === $this->current_action() ) {
            // In our file that handles the request, verify the nonce.
            $nonce = esc_attr($_REQUEST["_wpnonce"]);

            if ( !wp_verify_nonce($nonce, $this->plugin_name . "-delete-popupbox") ) {
                die( "Go get a life script kiddies" );
            } else {
                self::delete_popupboxes( absint($_GET["popupbox"]) );

                $url = esc_url_raw( remove_query_arg(array("action", "popupbox", "_wpnonce")) ) . "&status=" . $message . "&type=success";
                wp_redirect($url);
                exit();
            }
        }

        // If the delete bulk action is triggered
        if ( (isset($_POST["action"]) && $_POST["action"] == "bulk-delete") || (isset($_POST["action2"]) && $_POST["action2"] == "bulk-delete") ) {
            $delete_ids = ( isset($_POST['bulk-delete']) && !empty($_POST['bulk-delete']) ) ? esc_sql($_POST['bulk-delete']) : array();

            // loop over the array of record IDs and delete them
            foreach ($delete_ids as $id) {
                self::delete_popupboxes($id);
            }

            $url = esc_url_raw( remove_query_arg(array("action", "popupbox", "_wpnonce")) ) . "&status=" . $message . "&type=success";
            wp_redirect($url);
            exit();
        } elseif ( (isset($_POST['action']) && $_POST['action'] == 'bulk-published') || (isset($_POST['action2']) && $_POST['action2'] == 'bulk-published') ) {
            $published_ids = ( isset($_POST['bulk-delete']) && !empty($_POST['bulk-delete']) ) ? esc_sql($_POST['bulk-delete']) : array();
            $message = 'published';

            // loop over the array of record IDs and publish them
            foreach ($published_ids as $id) {
                self::publish_unpublish_popupbox($id, 'published');
            }

            $url = esc_url_raw( remove_query_arg(array("action", "popupbox", "_wpnonce")) ) . "&status=" . $message . "&type=success";
            wp_redirect($url);
        } elseif ( (isset($_POST['action']) && $_POST['action'] == 'bulk-unpublished') || (isset($_POST['action2']) && $_POST['action2'] == 'bulk-unpublished') ) {
            $unpublished_ids = ( isset($_POST['bulk-delete']) && ! empty($_POST['bulk-delete']) ) ? esc_sql($_POST['bulk-delete']) : array();
            $message = 'unpublished';

            // loop over the array of record IDs and unpublish them
            foreach ($unpublished_ids as $id) {
                self::publish_unpublish_popupbox($id , 'unpublish');
            }

            $url = esc_url_raw( remove_query_arg(array("action", "popupbox", "_wpnonce")) ) . "&status=" . $message . "&type=success";
            wp_redirect($url);
        }
    }

    /**
     * Delete a customer record.
     *
     * @param int $id customer ID
     */
    public static function delete_popupboxes($id) {
        global $wpdb;

        $wpdb->delete(
            "{$wpdb->prefix}ays_pb",
            array("id" => $id),
            array("%d")
        );
    }

    public function publish_unpublish_popupbox($id, $action) {
        global $wpdb;
        $pb_table = $wpdb->prefix . "ays_pb";

        if ($id == null) {
            return false;
        }

        $onoffswitch = ($action == "unpublish") ? "Off" : "On";

        $wpdb->update(
            $pb_table,
            array(
                "onoffswitch" => $onoffswitch
            ),
            array("id" => $id),
            array("%s"),
            array("%d")
        );
    }

    /**
     * Returns the count of records in the database.
     *
     * @return null|string
     */
    public static function record_count() {
        global $wpdb;

        $sql = "SELECT COUNT(*) FROM {$wpdb->prefix}ays_pb";
        $filter_conditions = self::ays_pb_get_filter_conditions();

        if (isset($_GET['fstatus']) && !is_null(sanitize_text_field($_GET['fstatus']))) {
            $fstatus = esc_sql(sanitize_text_field($_GET['fstatus']));
            $status = $fstatus == 'published' ? 'On' : 'Off';
            $filter_conditions[] = "onoffswitch = '" . $status . "'";
        }

        if (!empty($filter_conditions)) {
            $sql .= " WHERE " . implode(" AND ", $filter_conditions);
        }

        return $wpdb->get_var($sql);
    }

    /**
     * Retrieve customers data from the database
     *
     * @param int $per_page
     * @param int $page_number
     *
     * @return mixed
     */
    public static function get_ays_popupboxes($per_page = 20, $page_number = 1 , $search = '') {
        global $wpdb;

        $sql = "SELECT * FROM {$wpdb->prefix}ays_pb";
        $filter_conditions = self::ays_pb_get_filter_conditions();

        if (isset($_GET['fstatus']) && !is_null(sanitize_text_field($_GET['fstatus']))) {
            $fstatus = esc_sql(sanitize_text_field($_GET['fstatus']));
            $status = $fstatus == 'published' ? 'On' : 'Off';
            $filter_conditions[] = "onoffswitch = '" . $status . "'";
        }

        if (!empty($filter_conditions)) {
            $sql .= " WHERE " . implode(" AND ", $filter_conditions);
        }

        if (!empty($_REQUEST['orderby'])) {
            $order_by = ( isset($_REQUEST['orderby']) && sanitize_text_field($_REQUEST['orderby']) != '' ) ? sanitize_text_field($_REQUEST['orderby']) : 'id';
            $order_by .= ( !empty($_REQUEST['order']) && strtolower($_REQUEST['order']) == 'asc' ) ? 'ASC' : 'DESC';

            $sql_orderby = sanitize_sql_orderby($order_by);

            if ($sql_orderby) {
                $sql .= ' ORDER BY ' . $sql_orderby;
            } else {
                $sql .= ' ORDER BY id DESC';
            }
        } else {
            $sql .= ' ORDER BY id DESC';
        }

        $sql .= " LIMIT $per_page";
        $sql .= " OFFSET " . ($page_number - 1) * $per_page;

        $result = $wpdb->get_results($sql, "ARRAY_A");

        return $result;
    }

    public function display_tablenav($which) {
        ?>
        <div class="tablenav <?php echo esc_attr($which); ?>">

            <div class="alignleft actions">
                <?php $this->bulk_actions($which); ?>
            </div>

            <?php
            $this->extra_tablenav($which);
            $this->pagination($which);
            ?>
            <br class="clear" />
        </div>
        <?php
    }

    /**
     * Returns an associative array containing the bulk action
     *
     * @return array
     */
    public function get_bulk_actions() {
        $actions = array(
            "bulk-published" =>  __('Publish', "ays-popup-box"),
            "bulk-unpublished" =>  __('Unpublish', "ays-popup-box"),
            "bulk-delete" =>  __('Delete', "ays-popup-box"),
        );

        return $actions;
    }

    public function extra_tablenav($which) {
        global $wpdb;

        $titles_sql = "SELECT {$wpdb->prefix}ays_pb_categories.title, {$wpdb->prefix}ays_pb_categories.id FROM {$wpdb->prefix}ays_pb_categories";
        $cat_titles = $wpdb->get_results($titles_sql);

        $popup_options_sql = "SELECT `modal_content`, `options` FROM " . $wpdb->prefix . "ays_pb";
        $popup_options = $wpdb->get_results($popup_options_sql, "ARRAY_A");
        $cat_id = null;
        if (isset($_GET['filterby'])) {
            $cat_id = absint( intval($_GET['filterby']) );
        }

        $author_id = null;
        if (isset($_GET['filterbyAuthor'])) {
            $author_id = absint( intval($_GET['filterbyAuthor']) );
        }

        $ays_pb_type = null;
        if (isset($_GET['filterbyType'])) {
            $ays_pb_type = ($_GET['filterbyType']);
        }

        $categories_select = array();
        foreach ($cat_titles as $cat_title) {
            $selected = "";
            if ($cat_id === intval($cat_title->id)) {
                $selected = "selected";
            }

            $categories_select[$cat_title->id]['title'] = $cat_title->title;
            $categories_select[$cat_title->id]['selected'] = $selected;
            $categories_select[$cat_title->id]['id'] = $cat_title->id;
        }
        sort($categories_select);

        $authors_select = array();
        foreach ($popup_options as $options_arr) {
            $options = ( isset($options_arr['options']) && $options_arr['options'] != '' ) ? json_decode($options_arr['options'], 'ARRAY_A') : '';

            $author = array();
            if (isset($options['author'])) {
                if (is_array($options['author'])) {
                    $author = $options['author'];
                } else {
                    $author = json_decode($options['author'], true);
                }
            }

            $selected = "";
            if (!empty($author)) {
                if ($author_id === intval($author["id"])) {
                    $selected = "selected";
                }

                $authors_select[$author["id"]]['display_name'] = $author["name"];
                $authors_select[$author["id"]]['selected'] = $selected;
                $authors_select[$author["id"]]['id'] = $author["id"];
            }
        }
        sort($authors_select);

        $types_select = array();
        foreach ($popup_options as $type_name) {
            $modal_content = ( isset($type_name['modal_content']) && $type_name['modal_content'] != '' ) ? stripslashes( esc_attr($type_name['modal_content']) ) : '';

            $selected = "";
            if ($ays_pb_type === $modal_content) {
                $selected = "selected";
            }

            $types_select[$modal_content]['title'] = $this->column_modal_content($type_name);
            $types_select[$modal_content]['selected'] = $selected;
            $types_select[$modal_content]['value'] = $modal_content;
        }
        sort($types_select);

        ?>
        <div id="popup-filter-div-<?php echo esc_attr($which); ?>" class="alignleft actions bulkactions">
            <select name="filterby-<?php echo esc_attr($which); ?>" id="bulk-action-selector-<?php echo esc_attr($which); ?>">
                <option value=""><?php echo __('Select Category', "ays-popup-box")?></option>
                <?php
                    foreach ($categories_select as $cat_title) {
                        echo "<option " . $cat_title['selected'] . " value='" . $cat_title['id'] . "'>" . $cat_title['title'] . "</option>";
                    }
                ?>
            </select>
            <select name="filterbyAuthor-<?php echo esc_attr($which); ?>" id="bulk-action-selector-<?php echo esc_attr($which); ?>">
                <option value=""><?php echo __('Select Author', "ays-popup-box")?></option>
                <?php
                    foreach ($authors_select as $author) {
                        echo "<option " . $author['selected'] . " value='" . $author['id'] . "'>" . $author['display_name'] . "</option>";
                    }
                ?>
            </select>
            <select name="filterbyType-<?php echo esc_attr($which); ?>" id="bulk-action-selector-<?php echo esc_attr($which); ?>">
                <option value=""><?php echo __('Select Type', "ays-popup-box")?></option>
                <?php
                    foreach ($types_select as $type) {
                        echo "<option " . $type['selected'] . " value='" . $type['value'] . "'>" . $type['title'] . "</option>";
                    }
                ?>
            </select>
            <input type="button" id="doaction-<?php echo esc_attr($which); ?>" class="ays-popup-question-tab-all-filter-button-<?php echo esc_attr($which); ?> button" value="<?php echo __("Filter", "ays-popup-box"); ?>">
        </div>
        <a href="?page=<?php echo esc_attr($_REQUEST['page']); ?>" class="button"><?php echo __("Clear filters", "ays-popup-box"); ?></a>
        <?php
    }

    public function duplicate_popupbox($id) {
        global $wpdb;

        $pb_table = $wpdb->prefix . "ays_pb";
        $popup = $this->get_popupbox_by_id($id);

        // Popup Name
        $popup_name = ( isset($popup['popup_name']) && $popup['popup_name'] != '' ) ? 'Copy - ' . sanitize_text_field($popup['popup_name']) : '';

        // Description
        if (is_multisite()) {
            if (is_super_admin()) {
                $description = ( isset($popup['description']) && $popup['description'] != '' ) ? stripslashes($popup['description'] ) : '';
            } else {
                $description = ( isset($popup['description']) && $popup['description'] != '' ) ? stripslashes( wp_kses_post($popup['description']) ) : '';
            }
        } else {
            if(current_user_can('unfiltered_html')) {
                $description = ( isset($popup['description']) && $popup['description'] != '' ) ? stripslashes($popup['description']) : '';
            } else {
                $description = ( isset($popup['description']) && $popup['description'] != '' ) ? stripslashes( wp_kses_post($popup['description']) ) : '';
            }
        }

        // Custom HTML
        if (is_multisite()) {
            if (is_super_admin()) {
                $popup_custom_html = ( isset($popup['custom_html']) && $popup['custom_html'] != '' ) ? stripslashes($popup['custom_html']) : '';
            } else {
                $popup_custom_html = ( isset($popup['custom_html']) && $popup['custom_html'] != '' ) ? stripslashes( wp_kses_post($popup['custom_html']) ) : '';
            }
        } else {
            if (current_user_can('unfiltered_html')) {
                $popup_custom_html = ( isset($popup['custom_html']) && $popup['custom_html'] != '' ) ? stripslashes($popup['custom_html']) : '';
            } else {
                $popup_custom_html = ( isset($popup['custom_html']) && $popup['custom_html'] != '' ) ? stripslashes( wp_kses_post($popup['custom_html']) ) : '';
            }
        }

        // Update popup author and creation date info
        $user_id = get_current_user_id();
        $user = get_userdata($user_id);
        $author = json_encode(array(
            'id' => $user->ID."",
            'name' => $user->data->display_name
        ), JSON_UNESCAPED_SLASHES);

        $options = json_decode($popup['options'], true);

        $options['create_date'] = date("Y-m-d H:i:s");
        $options['author'] = $author;

        $result = $wpdb->insert(
            $pb_table,
            array(
                "title" => "Copy - " . stripslashes( sanitize_text_field($popup['title']) ),
                "popup_name" => $popup_name,
                "description" => $description,
                "category_id" => absint( intval($popup['category_id']) ),
                "autoclose" => absint( intval($popup['autoclose']) ),
                "cookie" => absint( intval($popup['cookie']) ),
                "width" => absint( intval($popup['width']) ),
                "height" => absint( intval($popup['height']) ),
                "bgcolor" => stripslashes( sanitize_text_field($popup['bgcolor']) ),
                "textcolor" => stripslashes( sanitize_text_field($popup['textcolor']) ),
                "bordersize" => absint( intval($popup['bordersize']) ),
                "bordercolor" => stripslashes( sanitize_text_field($popup['bordercolor']) ),
                "border_radius" => absint( intval($popup['border_radius']) ),
                "shortcode" => wp_unslash( sanitize_text_field($popup['shortcode']) ),
                "users_role" => stripslashes( sanitize_text_field($popup['users_role']) ),
                "custom_class" => stripslashes( sanitize_text_field($popup['custom_class']) ),
                "custom_css" => stripslashes( sanitize_textarea_field($popup['custom_css']) ),
                "custom_html" => $popup_custom_html,
                "onoffswitch" => stripslashes( sanitize_text_field($popup['onoffswitch']) ),
                "show_only_for_author" => stripslashes( sanitize_text_field($popup['show_only_for_author']) ),
                "show_all" => stripslashes( sanitize_text_field($popup['show_all']) ),
                "delay" => absint( intval($popup['delay']) ),
                "scroll_top" => absint( intval($popup['scroll_top']) ),
                "animate_in" => stripslashes( sanitize_text_field($popup['animate_in']) ),
                "animate_out" => stripslashes( sanitize_text_field($popup['animate_out']) ),
                "action_button" => stripslashes( sanitize_text_field($popup['action_button']) ),
                "view_place" => stripslashes( sanitize_text_field($popup['view_place']) ),
                "action_button_type" => stripslashes( sanitize_text_field($popup['action_button_type']) ),
                "modal_content" => stripslashes( sanitize_text_field($popup['modal_content']) ),
                "view_type" => stripslashes( sanitize_text_field($popup['view_type']) ),
                "onoffoverlay" => stripslashes( sanitize_text_field($popup['onoffoverlay']) ),
                "overlay_opacity" => stripslashes( sanitize_text_field($popup['overlay_opacity']) ),
                "show_popup_title" => stripslashes( sanitize_text_field($popup['show_popup_title']) ),
                "show_popup_desc" => stripslashes( sanitize_text_field($popup['show_popup_desc']) ),
                "close_button" => stripslashes( sanitize_text_field($popup['close_button']) ),
                "header_bgcolor" => stripslashes( sanitize_text_field($popup['header_bgcolor']) ),
                "bg_image" => sanitize_url($popup['bg_image']),
                "log_user" => stripslashes( sanitize_text_field($popup['log_user']) ),
                "guest" => stripslashes( sanitize_text_field($popup['guest']) ),
                "active_date_check" => stripslashes( sanitize_text_field($popup['active_date_check']) ),
                "activeInterval" => sanitize_text_field($popup['activeInterval']),
                "deactiveInterval" => sanitize_text_field($popup['deactiveInterval']),
                "pb_position" => stripslashes( sanitize_text_field($popup['pb_position']) ),
                "pb_margin" => absint( intval($popup['pb_margin']) ),
                "options" => json_encode($options)
            ),
            array(
                '%s', // title
                '%s', // popup_name
                '%s', // description
                '%d', // cat_id
                '%d', // autoclose
                '%d', // cookie
                '%d', // width
                '%d', // height
                '%s', // bgcolor
                '%s', // textcolor
                '%d', // bordersize
                '%s', // bordercolor
                '%d', // border_radius
                '%s', // shortcode
                '%s', // users_roles
                '%s', // custom_class
                '%s', // custom_css
                '%s', // custom_html
                '%s', // onoffswitch
                '%s', // show_only_for_author
                '%s', // show_all
                '%d', // delay
                '%d', // scroll_top
                '%s', // animate_in
                '%s', // animate_out
                '%s', // action_button
                '%s', // view_place
                '%s', // action_button_type
                '%s', // modal_content
                '%s', // view_type
                '%s', // onoffoverlay
                '%f', // overlay_opacity
                '%s', // show_popup_title
                '%s', // show_popup_desc
                '%s', // close_button
                '%s', // header_bgcolor
                '%s', // bg_image
                '%s', // log_user
                '%s', // guest
                '%s', // active_date_check
                '%s', // activeInterval
                '%s', // deactiveInterval
                '%s', // pb_position
                '%d', // pb_margin
                '%s', // options
            )
        );

        if ($result >= 0) {
            $message = "duplicated";
            $url = esc_url_raw( remove_query_arg(array('action', 'popupbox')) ) . '&status=' . $message;
            wp_redirect($url);
        }
    }

    public function get_popupbox_by_id($id) {
        global $wpdb;

        $sql = "SELECT * FROM {$wpdb->prefix}ays_pb WHERE id=" . absint( sanitize_text_field($id) ) . " ORDER BY id ASC";
        $result = $wpdb->get_row($sql, "ARRAY_A");

        return $result;
    }

    public function add_or_edit_popupbox($data){

		global $wpdb;
		$pb_table = $wpdb->prefix . "ays_pb";

        $check_nonce = isset($_POST["pb_action"]) && wp_verify_nonce( sanitize_text_field( $_POST["pb_action"] ), 'pb_action' );

        if( !$check_nonce ) {
            return;
        }

        // Id
		$id = ( $data["id"] != NULL ) ? absint( intval( $data["id"] ) ) : null;

        // Width
		$width = ( isset( $data['ays-pb']["width"] ) && $data['ays-pb']["width"] != '' ) ? absint( intval( $data['ays-pb']["width"] ) ) : '';

        //View Type
		$view_type = ( isset( $data['ays-pb']["view_type"] ) && $data['ays-pb']["view_type"] != '' ) ? wp_unslash(sanitize_text_field( $data['ays-pb']["view_type"] )) : "";

        // Height
        $default_height = $view_type == 'notification' ? 100 : 500;
		$height = ( isset( $data['ays-pb']["height"] ) && $data['ays-pb']["height"] ) ? absint( intval( $data['ays-pb']["height"] ) ) : $default_height;

        // Max-Height
        $pb_max_height = ( isset($data['ays_pb_max_height']) && $data['ays_pb_max_height'] != '' ) ? absint( intval($data['ays_pb_max_height']) ) : '';

        // Max-Height Measurement Unit
        $popup_max_height_by_percentage_px = ( isset($data['ays_popup_max_height_by_percentage_px']) && $data['ays_popup_max_height_by_percentage_px'] != '' ) ? stripslashes( sanitize_text_field($data['ays_popup_max_height_by_percentage_px']) ) : 'pixels';

        // Max-Height Mobile
        $pb_max_height_mobile = ( isset($data['ays_pb_max_height_mobile']) && $data['ays_pb_max_height_mobile'] != '' ) ? absint( intval($data['ays_pb_max_height_mobile']) ) : '';

        // Max-Height Measurement Unit Mobile
        $popup_max_height_by_percentage_px_mobile = ( isset($data['ays_popup_max_height_by_percentage_px_mobile']) && $data['ays_popup_max_height_by_percentage_px_mobile'] != '' ) ? stripslashes( sanitize_text_field($data['ays_popup_max_height_by_percentage_px_mobile']) ) : 'pixels';

        //Autoclose
		$autoclose = ( isset( $data['ays-pb']["autoclose"] ) && $data['ays-pb']["autoclose"] != '' ) ? absint( intval( $data['ays-pb']["autoclose"] ) ) : '';

        //Autoclose mobile
		$autoclose_mobile = ( isset( $data["ays_pb_autoclose_mobile"] ) && $data["ays_pb_autoclose_mobile"] != '' ) ? sanitize_text_field( $data["ays_pb_autoclose_mobile"] )  : '';
        
        // Enable different autoclose mobile text for mobile
        $enable_autoclose_delay_text_mobile = ( isset($data['ays_pb_enable_autoclose_delay_text_mobile']) && $data['ays_pb_enable_autoclose_delay_text_mobile'] == 'on' ) ? 'on' : 'off';

        //Show once per session
		$cookie = ( isset( $data['ays-pb']["cookie"] ) && $data['ays-pb']["cookie"] != '' ) ? absint( intval( $data['ays-pb']["cookie"] ) ) : 0;

        //Title
		$title = ( isset( $data['ays-pb']["popup_title"] ) && $data['ays-pb']["popup_title"] != '' ) ? wp_unslash(sanitize_text_field( $data['ays-pb']["popup_title"] )) : 'Demo Title';

        //Shortcode
		$shortcode = ( isset( $data['ays-pb']["shortcode"] ) && $data['ays-pb']["shortcode"] != '' ) ? wp_unslash(sanitize_text_field( $data['ays-pb']["shortcode"] )) : '';

        //Description
        if (is_multisite()) {
            if (is_super_admin()) {
                $description = ( isset( $data['ays-pb']["popup_description"] ) && $data['ays-pb']["popup_description"] != '' ) ? stripslashes( $data['ays-pb']["popup_description"] ) : '';
            } else {
                $description = ( isset( $data['ays-pb']["popup_description"] ) && $data['ays-pb']["popup_description"] != '' ) ? wp_kses_post( $data['ays-pb']["popup_description"] ) : '';
            }
        } else {
            if(current_user_can('unfiltered_html')) {
                $description = ( isset( $data['ays-pb']["popup_description"] ) && $data['ays-pb']["popup_description"] != '' ) ? stripslashes( $data['ays-pb']["popup_description"] ) : '';
            } else {
                $description = ( isset( $data['ays-pb']["popup_description"] ) && $data['ays-pb']["popup_description"] != '' ) ? wp_kses_post( $data['ays-pb']["popup_description"] ) : '';
            }
        }

        //Category Id 
        $popup_category_id = ( isset( $_POST['ays_popup_category'] ) && $_POST['ays_popup_category'] != '' ) ? absint( sanitize_text_field( $_POST['ays_popup_category'] ) ) : null;

        //Background Color
		$bgcolor = ( isset( $data['ays-pb']["bgcolor"] ) && $data['ays-pb']["bgcolor"] != '' ) ? wp_unslash(sanitize_text_field( $data['ays-pb']["bgcolor"] )) : '#FFFFFF';

        //Enable Background Color Mobile
        $enable_bgcolor_mobile = ( isset($data['ays_pb_enable_bgcolor_mobile']) && $data['ays_pb_enable_bgcolor_mobile'] == 'on' ) ? 'on' : 'off';

        //Background Color Mobile
        $bgcolor_mobile = ( isset($data['ays_pb_bgcolor_mobile']) && $data['ays_pb_bgcolor_mobile'] != '' ) ? wp_unslash( sanitize_text_field($data['ays_pb_bgcolor_mobile']) ) : '#FFFFFF';

        //Text Color
		$textcolor = ( isset( $data['ays-pb']["ays_pb_textcolor"] ) && $data['ays-pb']["ays_pb_textcolor"] != '' ) ? wp_unslash(sanitize_text_field( $data['ays-pb']["ays_pb_textcolor"] )) : '#000000';

        //Border Size
        $default_bordersize = $view_type == 'notification' ? 0 : 1;
		$bordersize = ( isset( $data['ays-pb']["ays_pb_bordersize"] ) && $data['ays-pb']["ays_pb_bordersize"] != '' ) ? wp_unslash(sanitize_text_field(intval(round( $data['ays-pb']["ays_pb_bordersize"] )))) : $default_bordersize;

        //Enable Border Size Mobile
        $enable_bordersize_mobile = ( isset($data['ays_pb_enable_bordersize_mobile']) && $data['ays_pb_enable_bordersize_mobile'] == 'on' ) ? 'on' : 'off';

        //Border Size Mobile
        $bordersize_mobile = ( isset($data['ays_pb_bordersize_mobile']) && $data['ays_pb_bordersize_mobile'] != '' ) ? wp_unslash(sanitize_text_field(intval(round( $data['ays_pb_bordersize_mobile'] )))) : $default_bordersize;

        //Border Color
		$bordercolor = ( isset( $data['ays-pb']["ays_pb_bordercolor"] ) && $data['ays-pb']["ays_pb_bordercolor"] != '' ) ? wp_unslash(sanitize_text_field( $data['ays-pb']["ays_pb_bordercolor"] )) : '#ffffff';

        //Enable Border Color Mobile
        $enable_bordercolor_mobile = ( isset($data['ays_pb_enable_bordercolor_mobile']) && $data['ays_pb_enable_bordercolor_mobile'] == 'on' ) ? 'on' : 'off';

        //Border Color Mobile
        $bordercolor_mobile = ( isset($data['ays_pb_bordercolor_mobile']) && $data['ays_pb_bordercolor_mobile'] != '' ) ? wp_unslash( sanitize_text_field($data['ays_pb_bordercolor_mobile']) ) : '#ffffff';

        //Border Radius
        $default_border_radius = $view_type == 'notification' ? 0 : 4;
		$border_radius = ( isset( $data['ays-pb']["ays_pb_border_radius"] ) && $data['ays-pb']["ays_pb_border_radius"] != '' ) ? wp_unslash(sanitize_text_field(intval(round( $data['ays-pb']["ays_pb_border_radius"] )))) : $default_border_radius;

        //Enable Border Radius Mobile
        $enable_border_radius_mobile = ( isset($data['ays_pb_enable_border_radius_mobile']) && $data['ays_pb_enable_border_radius_mobile'] == 'on' ) ? 'on' : 'off';

        //Border Radius Mobile
        $border_radius_mobile = ( isset($data['ays_pb_border_radius_mobile']) && $data['ays_pb_border_radius_mobile'] != '' ) ? wp_unslash( sanitize_text_field(intval(round( $data['ays_pb_border_radius_mobile'] )))) : $border_radius;

        //Custom Class
		$custom_css = ( isset( $data['ays-pb']["custom-css"] ) && $data['ays-pb']["custom-css"] != '' ) ? wp_unslash(stripslashes( esc_attr( $data['ays-pb']["custom-css"] ) ) ) : '';

        //Custom Html
        if (is_multisite()) {
            if (is_super_admin()) {
                $custom_html = ( isset( $data['ays-pb']["custom_html"] ) && $data['ays-pb']["custom_html"] != '' ) ? stripslashes( ($data['ays-pb']["custom_html"]) ) : '';
            } else {
                $custom_html = ( isset( $data['ays-pb']["custom_html"] ) && $data['ays-pb']["custom_html"] != '' ) ? wp_kses_post( $data['ays-pb']["custom_html"] ) : '';
            }
        } else {
            if (current_user_can('unfiltered_html')) {
                $custom_html = ( isset( $data['ays-pb']["custom_html"] ) && $data['ays-pb']["custom_html"] != '' ) ? stripslashes( ($data['ays-pb']["custom_html"]) ) : '';
            } else {
                $custom_html = ( isset( $data['ays-pb']["custom_html"] ) && $data['ays-pb']["custom_html"] != '' ) ? stripslashes( wp_kses_post($data['ays-pb']["custom_html"]) ) : '';
            }
        }

        //Show All
		$show_all = ( isset( $data['ays-pb']["show_all"] ) && $data['ays-pb']["show_all"] != '' ) ? wp_unslash(sanitize_text_field( $data['ays-pb']["show_all"] )) : 'all';

        //Animation Delay
		$delay = ( isset( $data['ays-pb']["delay"] ) && $data['ays-pb']["delay"] != '' ) ? wp_unslash(sanitize_text_field( $data['ays-pb']["delay"] )) : 0;
     
        // Enable different open delay for mobile
        $enable_open_delay_mobile = ( isset($data['ays_pb_enable_open_delay_mobile']) && $data['ays_pb_enable_open_delay_mobile'] == 'on' ) ? 'on' : 'off';

        //Open delay mobile
        $pb_open_delay_mobile = ( isset($data['ays_pb_open_delay_mobile']) && $data['ays_pb_open_delay_mobile'] != '' ) ? wp_unslash( sanitize_text_field($data['ays_pb_open_delay_mobile']) ) : 0;

        //Scroll Top
		$scroll_top = ( isset( $data['ays-pb']["scroll_top"] ) && $data['ays-pb']["scroll_top"] != '' ) ? wp_unslash(sanitize_text_field(intval(round( $data['ays-pb']["scroll_top"] )))) : 0;

        // Enable different scroll top for mobile
        $enable_scroll_top_mobile = ( isset($data['ays_pb_enable_scroll_top_mobile']) && $data['ays_pb_enable_scroll_top_mobile'] == 'on' ) ? 'on' : 'off';

        //Scroll top mobile
        $pb_scroll_top_mobile = ( isset($data['ays_pb_scroll_top_mobile']) && $data['ays_pb_scroll_top_mobile'] != '' ) ? wp_unslash( sanitize_text_field($data['ays_pb_scroll_top_mobile']) ) : 0;

        //Animate In
		$animate_in = ( isset( $data['ays-pb']["animate_in"] ) && $data['ays-pb']["animate_in"] != '' ) ? wp_unslash(sanitize_text_field( $data['ays-pb']["animate_in"] )) : '';

        //Enable Different Opening Animation Mobile
        $enable_animate_in_mobile = ( isset($data['ays_pb_enable_animate_in_mobile']) && $data['ays_pb_enable_animate_in_mobile'] == 'on' ) ? 'on' : 'off';

        //Animate In Mobile
        $animate_in_mobile = ( isset($data['ays_pb_animate_in_mobile']) && $data['ays_pb_animate_in_mobile'] != '' ) ? wp_unslash( sanitize_text_field($data['ays_pb_animate_in_mobile']) ) : 0;

        //Animate Out
		$animate_out = ( isset( $data['ays-pb']["animate_out"] ) && $data['ays-pb']["animate_out"] != '' ) ? wp_unslash(sanitize_text_field( $data['ays-pb']["animate_out"] )) : '';

        //Enable Different Closing Animation Mobile
        $enable_animate_out_mobile = ( isset($data['ays_pb_enable_animate_out_mobile']) && $data['ays_pb_enable_animate_out_mobile'] == 'on' ) ? 'on' : 'off';

        //Animate Out Mobile
        $animate_out_mobile = ( isset($data['ays_pb_animate_out_mobile']) && $data['ays_pb_animate_out_mobile'] != '' ) ? wp_unslash( sanitize_text_field($data['ays_pb_animate_out_mobile']) ) : 0;

        //Action Button
		$action_button = wp_unslash(sanitize_text_field( $data['ays-pb']["action_button"] ));

        //Action Button Type
		$action_button_type  = ( isset( $data['ays-pb']["action_button_type"] ) && $data['ays-pb']["action_button_type"] != '' ) ? wp_unslash(sanitize_text_field( $data['ays-pb']["action_button_type"] )) : 'both';

        //Modal Content
		$modal_content = ( isset( $data['ays-pb']["modal_content"] ) && $data['ays-pb']["modal_content"] != '' ) ? wp_unslash(sanitize_text_field( $data['ays-pb']["modal_content"] )) : '';

        //Header BgColor
        $header_bgcolor = ( isset( $data['ays-pb']["header_bgcolor"] ) && $data['ays-pb']["header_bgcolor"] != '' ) ? wp_unslash(sanitize_text_field( $data['ays-pb']["header_bgcolor"] )) : '#ffffff';

        // Background Image
        $bg_image = ( isset( $data['ays_pb_bg_image'] ) && $data['ays_pb_bg_image'] != '' ) ? sanitize_url( $data['ays_pb_bg_image'] ) : '';

        // Enable Different Background Image Mobile
        $enable_bg_image_mobile = ( isset($data['ays_pb_enable_bg_image_mobile']) && $data['ays_pb_enable_bg_image_mobile'] == 'on' ) ? 'on' : 'off';

        // Background Image Mobile
        $bg_image_mobile = ( isset( $data['ays_pb_bg_image_mobile'] ) && $data['ays_pb_bg_image_mobile'] != '' ) ? sanitize_url( $data['ays_pb_bg_image_mobile'] ) : '';

        // Background Image Position
        $pb_bg_image_position = (isset($data['ays_pb_bg_image_position']) && $data['ays_pb_bg_image_position'] != "") ? stripslashes( sanitize_text_field($data['ays_pb_bg_image_position']) ) : 'center center';

        // Enable Different Background Image Position Mobile
        $enable_pb_bg_image_position_mobile = ( isset($data['ays_pb_enable_bg_image_position_mobile']) && $data['ays_pb_enable_bg_image_position_mobile'] == 'on' ) ? 'on' : 'off';

        // Background Image Position Mobile
        $pb_bg_image_position_mobile = (isset($data['ays_pb_bg_image_position_mobile']) && $data['ays_pb_bg_image_position_mobile'] != "") ? stripslashes( sanitize_text_field($data['ays_pb_bg_image_position_mobile']) ) : 'center center';

        // Background Image Sizing
        $pb_bg_image_sizing = (isset($data['ays_pb_bg_image_sizing']) && $data['ays_pb_bg_image_sizing'] != "") ? stripslashes( sanitize_text_field($data['ays_pb_bg_image_sizing']) ) : 'cover';

        // Enable Different Background Image Sizing Mobile
        $enable_pb_bg_image_sizing_mobile = ( isset($data['ays_pb_enable_bg_image_sizing_mobile']) && $data['ays_pb_enable_bg_image_sizing_mobile'] == 'on' ) ? 'on' : 'off';

        // Background Image Sizing Mobile
        $pb_bg_image_sizing_mobile = (isset($data['ays_pb_bg_image_sizing_mobile']) && $data['ays_pb_bg_image_sizing_mobile'] != "") ? stripslashes( sanitize_text_field($data['ays_pb_bg_image_sizing_mobile']) ) : 'cover';

        //Popup Position
        $pb_position = ( isset( $data['ays-pb']["pb_position"] ) && $data['ays-pb']["pb_position"] != '' ) ? wp_unslash(sanitize_text_field( $data['ays-pb']["pb_position"] )) : 'center-center';

        // Enable different popup position for mobile
        $enable_pb_position_mobile = ( isset($data['ays_pb_enable_popup_position_mobile']) && $data['ays_pb_enable_popup_position_mobile'] == 'on' ) ? 'on' : 'off';

        //Popup Position mobile
        $pb_position_mobile = ( isset($data['ays_pb_position_mobile']) && $data['ays_pb_position_mobile'] != '' ) ? wp_unslash( sanitize_text_field($data['ays_pb_position_mobile']) ) : 'center-center';

        //Popup Margin
        $pb_margin = ( isset( $data['ays-pb']["pb_margin"] ) && $data['ays-pb']["pb_margin"] != '' ) ? wp_unslash(sanitize_text_field( intval( $data['ays-pb']["pb_margin"] ))) : '0';

        // Schedule Popup
        $active_date_check = (isset($data['active_date_check']) && $data['active_date_check'] == "on") ? 'on' : 'off';
        $activeInterval = isset($data['ays-active']) ? sanitize_text_field($data['ays-active']) : "";
        $deactiveInterval = isset($data['ays-deactive']) ? sanitize_text_field($data['ays-deactive']) : "";

        // Custom class for quiz container
        $custom_class = (isset($data['ays-pb']["custom-class"]) && $data['ays-pb']["custom-class"] != "") ? stripslashes( sanitize_text_field($data['ays-pb']["custom-class"]) ) : '';
        $users_role = (isset($data['ays-pb']["ays_users_roles"]) && !empty($data['ays-pb']["ays_users_roles"])) ? $data['ays-pb']["ays_users_roles"] : array();

        // Background gradient
        $enable_background_gradient = ( isset( $data['ays_enable_background_gradient'] ) && $data['ays_enable_background_gradient'] == 'on' ) ? 'on' : 'off';
        $pb_background_gradient_color_1 = !isset($data['ays_background_gradient_color_1']) ? '' : stripslashes(sanitize_text_field($data['ays_background_gradient_color_1'] ));
        $pb_background_gradient_color_2 = !isset($data['ays_background_gradient_color_2']) ? '' : stripslashes(sanitize_text_field( $data['ays_background_gradient_color_2'] ));
        $pb_gradient_direction = !isset($data['ays_pb_gradient_direction']) ? '' : stripslashes( sanitize_text_field($data['ays_pb_gradient_direction']) );

        // Background gradient mobile
        $enable_background_gradient_mobile = ( isset( $data['ays_enable_background_gradient_mobile'] ) && $data['ays_enable_background_gradient_mobile'] == 'on' ) ? 'on' : 'off';
        $pb_background_gradient_color_1_mobile = !isset($data['ays_background_gradient_color_1_mobile']) ? '' : stripslashes(sanitize_text_field($data['ays_background_gradient_color_1_mobile'] ));
        $pb_background_gradient_color_2_mobile = !isset($data['ays_background_gradient_color_2_mobile']) ? '' : stripslashes(sanitize_text_field( $data['ays_background_gradient_color_2_mobile'] ));
        $pb_gradient_direction_mobile = !isset($data['ays_pb_gradient_direction_mobile']) ? '' : stripslashes( sanitize_text_field($data['ays_pb_gradient_direction_mobile']) );

        //Posts
        $except_types = isset($data['ays_pb_except_post_types']) ? $data['ays_pb_except_post_types'] : array();
        $except_posts = isset($data['ays_pb_except_posts']) ? $data['ays_pb_except_posts'] : array();

        //Close button delay
        $close_button_delay = (isset($data['ays_pb_close_button_delay']) && $data['ays_pb_close_button_delay'] != '') ? abs(intval($data['ays_pb_close_button_delay'])) : '';

        //Close button delay
        $close_button_delay_for_mobile = (isset($data['ays_pb_close_button_delay_for_mobile']) && $data['ays_pb_close_button_delay_for_mobile'] != '') ? abs(intval($data['ays_pb_close_button_delay_for_mobile'])) : '';
        
        //Enable different Close button delay mobile text for mobile
        $enable_close_button_delay_for_mobile = ( isset($data['ays_pb_enable_close_button_delay_for_mobile']) && $data['ays_pb_enable_close_button_delay_for_mobile'] == 'on' ) ? 'on' : 'off';

        //Enable PopupBox sound option
        $enable_pb_sound = (isset($data['ays_pb_enable_sounds']) && $data['ays_pb_enable_sounds'] == "on") ? 'on' : 'off';

        //Overlay Color
        $overlay_color = (isset($data['ays_pb_overlay_color']) && $data['ays_pb_overlay_color'] != '') ? stripslashes(sanitize_text_field( $data['ays_pb_overlay_color'] )) : '#000';

        //Enable Overlay Color mobile
        $enable_overlay_color_mobile =  ( isset($data['ays_pb_enable_overlay_color_mobile']) && $data['ays_pb_enable_overlay_color_mobile'] == "on" ) ? 'on' : 'off';

        //Overlay Color mobile
        $overlay_color_mobile = ( isset($data['ays_pb_overlay_color_mobile']) && $data['ays_pb_overlay_color_mobile'] !== '' ) ? stripslashes( sanitize_text_field($data['ays_pb_overlay_color_mobile']) ) : '#000';

        //Animation speed
        $animation_speed = (isset($data['ays_pb_animation_speed']) && $data['ays_pb_animation_speed'] !== '') ? abs($data['ays_pb_animation_speed']) : 1;

        //Enable animation speed mobile
        $enable_animation_speed_mobile =  ( isset($data['ays_pb_enable_animation_speed_mobile']) && $data['ays_pb_enable_animation_speed_mobile'] == "on" ) ? 'on' : 'off';

        //Animation speed mobile
        $animation_speed_mobile = ( isset($data['ays_pb_animation_speed_mobile']) && $data['ays_pb_animation_speed_mobile'] !== '' ) ? abs($data['ays_pb_animation_speed_mobile']) : 1;
        
        // Close Animation speed
        $close_animation_speed = (isset($data['ays_pb_close_animation_speed']) && $data['ays_pb_close_animation_speed'] !== '') ? abs($data['ays_pb_close_animation_speed']) : 1;

        //Enable close animation speed mobile
        $enable_close_animation_speed_mobile =  ( isset($data['ays_pb_enable_close_animation_speed_mobile']) && $data['ays_pb_enable_close_animation_speed_mobile'] == "on" ) ? 'on' : 'off';

        //Close animation speed mobile
        $close_animation_speed_mobile = ( isset($data['ays_pb_close_animation_speed_mobile']) && $data['ays_pb_close_animation_speed_mobile'] !== '' ) ? abs($data['ays_pb_close_animation_speed_mobile']) : 1;

        //Hide popup on mobile
        $pb_mobile = (isset($data['ays_pb_mobile']) && $data['ays_pb_mobile'] == 'on') ? 'on' : 'off';

        //Close button text
        $close_button_text = (isset($data['ays_pb_close_button_text']) && $data['ays_pb_close_button_text'] != '') ? sanitize_text_field($data['ays_pb_close_button_text']) : '✕';

        // Enable different close button text for mobile
        $enable_close_button_text_mobile = ( isset($data['ays_pb_enable_close_button_text_mobile']) && $data['ays_pb_enable_close_button_text_mobile'] == 'on' ) ? 'on' : 'off';

        //Close button text mobile
        $close_button_text_mobile = (isset($data['ays_pb_close_button_text_mobile']) && $data['ays_pb_close_button_text_mobile'] != '') ? sanitize_text_field($data['ays_pb_close_button_text_mobile']) : '✕';

        //Close button hover text
        $close_button_hover_text = (isset($data['ays_pb_close_button_hover_text']) && $data['ays_pb_close_button_hover_text'] != '') ? sanitize_text_field($data['ays_pb_close_button_hover_text']) : '';

        // PopupBox width for mobile option
        $mobile_width = (isset($data['ays_pb_mobile_width']) && $data['ays_pb_mobile_width'] != "") ?abs(intval($data['ays_pb_mobile_width']))  : '';

        // PopupBox max-width for mobile option
        $mobile_max_width = (isset($data['ays_pb_mobile_max_width']) && $data['ays_pb_mobile_max_width'] != "") ? abs(intval($data['ays_pb_mobile_max_width']))  : '';

        // PopupBox height for mobile option
        $mobile_height = (isset($data['ays_pb_mobile_height']) && $data['ays_pb_mobile_height'] != "") ? abs(intval($data['ays_pb_mobile_height']))  : '';

        // Close button position option
        $close_button_position = (isset($data['ays_pb_close_button_position']) && $data['ays_pb_close_button_position'] != '') ? stripslashes( sanitize_text_field($data['ays_pb_close_button_position']) ) : 'right-top';

        // Enable different close button position for mobile
        $enable_close_button_position_mobile = ( isset($data['ays_pb_enable_close_button_position_mobile']) && $data['ays_pb_enable_close_button_position_mobile'] == 'on' ) ? 'on' : 'off';

        //Close button position option mobile
        $close_button_position_mobile = ( isset($data['ays_pb_close_button_position_mobile']) && $data['ays_pb_close_button_position_mobile'] != '' ) ? stripslashes( sanitize_text_field($data['ays_pb_close_button_position_mobile']) ) : 'right-top';

        //Show PopupBox only once
        $show_only_once = (isset($data['ays_pb_show_only_once']) && $data['ays_pb_show_only_once'] == 'on') ? 'on' : 'off';
       
        //Show only on home page
        $show_on_home_page = (isset($data['ays_pb_show_on_home_page']) && $data['ays_pb_show_on_home_page'] == 'on') ? 'on' : 'off';

        //close popup by esc
        $close_popup_esc = (isset($data['close_popup_esc']) && $data['close_popup_esc'] == 'on') ? 'on' : 'off';

        //popup width with percentage
        $popup_width_by_percentage_px = (isset($data['ays_popup_width_by_percentage_px']) && $data['ays_popup_width_by_percentage_px'] != '') ? stripslashes( sanitize_text_field($data['ays_popup_width_by_percentage_px']) ) : 'pixels';

        //popup width with percentage mobile
        $popup_width_by_percentage_px_mobile = (isset($data['ays_popup_width_by_percentage_px_mobile']) && $data['ays_popup_width_by_percentage_px_mobile'] != '') ? stripslashes( sanitize_text_field($data['ays_popup_width_by_percentage_px_mobile']) ) : 'percentage';

        //popup padding with percentage
        $popup_padding_by_percentage_px = (isset($data['ays_popup_padding_by_percentage_px']) && $data['ays_popup_padding_by_percentage_px'] != '') ? stripslashes( sanitize_text_field($data['ays_popup_padding_by_percentage_px']) ) : 'pixels';

        // Padding
        $default_padding = ($view_type == "minimal" || $modal_content == 'image_type') ? 0 : 20;
        $padding = ( isset($data['ays_popup_content_padding']) && $data['ays_popup_content_padding'] != '' ) ? absint( intval( $data['ays_popup_content_padding'] ) ) : $default_padding;

        //font-family
        $pb_font_family = (isset($data['ays_pb_font_family']) && $data['ays_pb_font_family'] != '') ? stripslashes( sanitize_text_field($data['ays_pb_font_family']) ) : 'inherit';
        
        //close popup by clicking overlay
        $close_popup_overlay = (isset($data['close_popup_overlay']) && $data['close_popup_overlay'] == 'on') ? stripslashes( sanitize_text_field($data['close_popup_overlay']) ) : 'off';

        //close popup by clicking overlay mobile
        $close_popup_overlay_mobile = (isset($data['close_popup_overlay_mobile']) && $data['close_popup_overlay_mobile'] == 'on') ? stripslashes( sanitize_text_field($data['close_popup_overlay_mobile']) ) : 'off';

        //open full screen
        $enable_pb_fullscreen = (isset($data['enable_pb_fullscreen']) && $data['enable_pb_fullscreen'] == 'on') ? 'on' : 'off';
       
        //hide timer
        $enable_hide_timer = (isset($data['ays_pb_hide_timer']) && $data['ays_pb_hide_timer'] == 'on') ? 'on' : 'off';

        //hide timer
        $enable_hide_timer_mobile = (isset($data['ays_pb_hide_timer_mobile']) && $data['ays_pb_hide_timer_mobile'] == 'on') ? 'on' : 'off';

        //autoclose on video compltion
        $enable_autoclose_on_completion = (isset($data['ays_pb_autoclose_on_completion']) && $data['ays_pb_autoclose_on_completion'] == 'on') ? 'on' : 'off';

        // Social Media links
        $enable_social_links = (isset($data['ays_pb_enable_social_links']) && $data['ays_pb_enable_social_links'] == "on") ? 'on' : 'off';
        $ays_social_links = (isset($data['ays_social_links'])) ? array_map( 'sanitize_text_field', $data['ays_social_links'] ) : array(
            'linkedin_link'   => '',
            'facebook_link'   => '',
            'twitter_link'    => '',
            'vkontakte_link'  => '',
            'youtube_link'    => '',
            'instagram_link'  => '',
            'behance_link'    => '',
        );
       
        $linkedin_link = isset($ays_social_links['ays_pb_linkedin_link']) && $ays_social_links['ays_pb_linkedin_link'] != '' ? sanitize_text_field($ays_social_links['ays_pb_linkedin_link']) : '';
        $facebook_link = isset($ays_social_links['ays_pb_facebook_link']) && $ays_social_links['ays_pb_facebook_link'] != '' ? sanitize_text_field($ays_social_links['ays_pb_facebook_link']) : '';
        $twitter_link = isset($ays_social_links['ays_pb_twitter_link']) && $ays_social_links['ays_pb_twitter_link'] != '' ? sanitize_text_field($ays_social_links['ays_pb_twitter_link']) : '';
        $vkontakte_link = isset($ays_social_links['ays_pb_vkontakte_link']) && $ays_social_links['ays_pb_vkontakte_link'] != '' ? sanitize_text_field($ays_social_links['ays_pb_vkontakte_link']) : '';
        $youtube_link = isset($ays_social_links['ays_pb_youtube_link']) && $ays_social_links['ays_pb_youtube_link'] != '' ? sanitize_text_field($ays_social_links['ays_pb_youtube_link']) : '';
        $instagram_link = isset($ays_social_links['ays_pb_instagram_link']) && $ays_social_links['ays_pb_instagram_link'] != '' ? sanitize_text_field($ays_social_links['ays_pb_instagram_link']) : '';
        $behance_link = isset($ays_social_links['ays_pb_behance_link']) && $ays_social_links['ays_pb_behance_link'] != '' ? sanitize_text_field($ays_social_links['ays_pb_behance_link']) : '';

        $social_links = array(
            'linkedin_link'   => $linkedin_link,
            'facebook_link'   => $facebook_link,
            'twitter_link'    => $twitter_link,
            'vkontakte_link'  => $vkontakte_link,
            'youtube_link'    => $youtube_link,
            'instagram_link'  => $instagram_link,
            'behance_link'    => $behance_link,
        );
       
        // Heading for social buttons
        $social_buttons_heading = (isset($data['ays_pb_social_buttons_heading']) && $data['ays_pb_social_buttons_heading'] != '') ? stripslashes($data['ays_pb_social_buttons_heading']) : "";
       
        //close button_size
        $close_button_size = (isset($data['ays_pb_close_button_size']) && $data['ays_pb_close_button_size'] != '' ) ? abs(sanitize_text_field($data['ays_pb_close_button_size'])) : '';
       
        //close button image
        $close_button_image = (isset($data['ays_pb_close_btn_bg_img']) && $data['ays_pb_close_btn_bg_img'] != '' ) ? sanitize_url($data['ays_pb_close_btn_bg_img']) : '';

        //border style
        $border_style = (isset($data['ays_pb_border_style']) && $data['ays_pb_border_style'] != '' ) ? stripslashes( sanitize_text_field($data['ays_pb_border_style']) ) : '';
        
        //Enable border style mobile
        $enable_border_style_mobile =  ( isset($data['ays_pb_enable_border_style_mobile']) && $data['ays_pb_enable_border_style_mobile'] == "on" ) ? 'on' : 'off';

        //Border style mobile
        $border_style_mobile = ( isset($data['ays_pb_border_style_mobile']) && $data['ays_pb_border_style_mobile'] !== '' ) ? stripslashes( sanitize_text_field($data['ays_pb_border_style_mobile']) ) : '';
       
        //Show close button by hovering Popup Container
        $ays_pb_hover_show_close_btn = (isset($data['ays_pb_show_close_btn_hover_container']) && $data['ays_pb_show_close_btn_hover_container'] == 'on' ) ? 'on' : 'off';

        // Disable scrolling
        $disable_scroll = (isset($data['disable_scroll']) && $data['disable_scroll'] == 'on') ? 'on' : 'off';
       
        // Disable scrolling mobile
        $disable_scroll_mobile = (isset($data['disable_scroll_mobile']) && $data['disable_scroll_mobile'] == 'on') ? 'on' : 'off';

        //video options
        $video_theme_url = (isset($data['ays_video_theme_url']) && !empty($data['ays_video_theme_url'])) ? wp_http_validate_url($data['ays_video_theme_url']) : "";

        // Image type img src
        $image_type_img_src = (isset($data['ays_pb_image_type_img_src']) && $data['ays_pb_image_type_img_src'] != '') ? sanitize_url($data['ays_pb_image_type_img_src']) : "";

        // Image type img redirect url
        $image_type_img_redirect_url = (isset($data['ays_pb_image_type_img_redirect_url']) && $data['ays_pb_image_type_img_redirect_url'] != '') ? sanitize_url($data['ays_pb_image_type_img_redirect_url']) : "";

        // Image type img redirect to the new tab
        $image_type_img_redirect_to_new_tab = (isset($data['ays_pb_image_type_img_redirect_to_new_tab']) && $data['ays_pb_image_type_img_redirect_to_new_tab'] == 'on') ? 'on' : 'off';

        // Facebook page URL
        $facebook_page_url = (isset($data['ays_pb_facebook_page_url']) && $data['ays_pb_facebook_page_url'] != '') ? sanitize_url($data['ays_pb_facebook_page_url']) : "";

        // Hide FB page cover photo
        $hide_fb_page_cover_photo = (isset($data['ays_pb_hide_fb_page_cover_photo']) && $data['ays_pb_hide_fb_page_cover_photo'] == 'on') ? 'on' : 'off';

        // Use small FB header
        $use_small_fb_header = (isset($data['ays_pb_use_small_fb_header']) && $data['ays_pb_use_small_fb_header'] == 'on') ? 'on' : 'off';

        // Notification type active columns
        $notification_type_components = (isset($data['ays_notification_type_components']) && !empty($data['ays_notification_type_components'])) ? array_map('sanitize_text_field', $data['ays_notification_type_components']) : array();

        // Notification type columns order
        $notification_type_components_order = (isset($data['ays_notification_type_components_order']) && !empty($data['ays_notification_type_components_order'])) ? array_map('sanitize_text_field', $data['ays_notification_type_components_order']) : array();

        // Notification type | Main content
        $notification_main_content = (isset($data['ays_pb_notification_main_content']) && $data['ays_pb_notification_main_content'] != '') ? wp_kses_post($data['ays_pb_notification_main_content']) : '';

        // Notification type | Button 1 text
        $notification_button_1_text = (isset($data['ays_pb_notification_button_1_text']) && $data['ays_pb_notification_button_1_text'] != '') ? stripslashes( sanitize_text_field($data['ays_pb_notification_button_1_text']) ) : '';

        // Notification type | Button 1 redirect URL
        $notification_button_1_redirect_url = (isset($data['ays_pb_notification_button_1_redirect_url']) && $data['ays_pb_notification_button_1_redirect_url'] != '') ? sanitize_url($data['ays_pb_notification_button_1_redirect_url']) : '';

        // Notification type | Button 1 redirect to the new tab
        $notification_button_1_redirect_to_new_tab = (isset($data['ays_pb_notification_button_1_redirect_to_new_tab']) && $data['ays_pb_notification_button_1_redirect_to_new_tab'] == 'on') ? 'on' : 'off';

        // Notification type | Button 1 background color
        $notification_button_1_bg_color = (isset($data['ays_pb_notification_button_1_bg_color']) && $data['ays_pb_notification_button_1_bg_color'] != '') ? stripslashes( sanitize_text_field($data['ays_pb_notification_button_1_bg_color']) ) : '#F66123';

        // Notification type | Button 1 background hover color
        $notification_button_1_bg_hover_color = (isset($data['ays_pb_notification_button_1_bg_hover_color']) && $data['ays_pb_notification_button_1_bg_hover_color'] != '') ? stripslashes( sanitize_text_field($data['ays_pb_notification_button_1_bg_hover_color']) ) : '#F66123';

        // Notification type | Button 1 text color
        $notification_button_1_text_color = (isset($data['ays_pb_notification_button_1_text_color']) && $data['ays_pb_notification_button_1_text_color'] != '') ? stripslashes( sanitize_text_field($data['ays_pb_notification_button_1_text_color']) ) : '#FFFFFF';

        // Notification type | Button 1 text hover color
        $notification_button_1_text_hover_color = (isset($data['ays_pb_notification_button_1_text_hover_color']) && $data['ays_pb_notification_button_1_text_hover_color'] != '') ? stripslashes( sanitize_text_field($data['ays_pb_notification_button_1_text_hover_color']) ) : '#FFFFFF';

        // Notification type | Button 1 letter spacing
        $notification_button_1_letter_spacing = (isset($data['ays_pb_notification_button_1_letter_spacing']) && $data['ays_pb_notification_button_1_letter_spacing'] != '') ? absint( intval($data['ays_pb_notification_button_1_letter_spacing']) ) : 0;

        // Notification type | Button 1 font size
        $notification_button_1_font_size = (isset($data['ays_pb_notification_button_1_font_size']) && $data['ays_pb_notification_button_1_font_size'] != '') ? absint( intval($data['ays_pb_notification_button_1_font_size']) ) : 15;

        // Notification type | Button 1 border radius
        $notification_button_1_border_radius = (isset($data['ays_pb_notification_button_1_border_radius']) && $data['ays_pb_notification_button_1_border_radius'] != '') ? absint( intval($data['ays_pb_notification_button_1_border_radius']) ) : 6;

        // Notification type | Button 1 border width
        $notification_button_1_border_width = (isset($data['ays_pb_notification_button_1_border_width']) && $data['ays_pb_notification_button_1_border_width'] != '') ? absint( intval($data['ays_pb_notification_button_1_border_width']) ) : 0;

        // Notification type | Button 1 border color
        $notification_button_1_border_color = (isset($data['ays_pb_notification_button_1_border_color']) && $data['ays_pb_notification_button_1_border_color'] != '') ? stripslashes( sanitize_text_field($data['ays_pb_notification_button_1_border_color']) ) : '#FFFFFF';

        // Notification type | Button 1 border style
        $notification_button_1_border_style = (isset($data['ays_pb_notification_button_1_border_style']) && $data['ays_pb_notification_button_1_border_style'] != '') ? stripslashes( sanitize_text_field($data['ays_pb_notification_button_1_border_style']) ) : 'solid';

        // Notification type | Button 1 padding left/right
        $notification_button_1_padding_left_right = (isset($data['ays_pb_notification_button_1_padding_left_right']) && $data['ays_pb_notification_button_1_padding_left_right'] !== '') ? absint( intval($data['ays_pb_notification_button_1_padding_left_right']) ) : 32;

        // Notification type | Button 1 padding top/bottom
        $notification_button_1_padding_top_bottom = (isset($data['ays_pb_notification_button_1_padding_top_bottom']) && $data['ays_pb_notification_button_1_padding_top_bottom'] !== '') ? absint( intval($data['ays_pb_notification_button_1_padding_top_bottom']) ) : 12;

        // Notification type | Button 1 box shadow
        $notification_button_1_enable_box_shadow = (isset($data['ays_pb_notification_button_1_enable_box_shadow']) && $data['ays_pb_notification_button_1_enable_box_shadow'] == 'on') ? 'on' : 'off';

        // Notification type | Button 1 box shadow color
        $notification_button_1_box_shadow_color = (isset($data['ays_pb_notification_button_1_box_shadow_color']) && $data['ays_pb_notification_button_1_box_shadow_color'] != '') ? sanitize_text_field($data['ays_pb_notification_button_1_box_shadow_color']) : '#FF8319';

        // Notification type | Button 1 box shadow X offset
        $notification_button_1_box_shadow_x_offset = (isset($data['ays_pb_notification_button_1_box_shadow_x_offset']) && $data['ays_pb_notification_button_1_box_shadow_x_offset'] != '') ? intval($data['ays_pb_notification_button_1_box_shadow_x_offset']) : 0;

        // Notification type | Button 1 box shadow Y offset
        $notification_button_1_box_shadow_y_offset = (isset($data['ays_pb_notification_button_1_box_shadow_y_offset']) && $data['ays_pb_notification_button_1_box_shadow_y_offset'] != '') ? intval($data['ays_pb_notification_button_1_box_shadow_y_offset']) : 0;

        // Notification type | Button 1 box shadow Z offset
        $notification_button_1_box_shadow_z_offset = (isset($data['ays_pb_notification_button_1_box_shadow_z_offset']) && $data['ays_pb_notification_button_1_box_shadow_z_offset'] != '') ? intval($data['ays_pb_notification_button_1_box_shadow_z_offset']) : 10;

        // Min Height
        $pb_min_height = (isset($data['ays_pb_min_height']) && $data['ays_pb_min_height'] != '') ? absint(intval($data['ays_pb_min_height'])) : '';

        //Font size
        $pb_font_size = (isset($data['ays_pb_font_size']) && $data['ays_pb_font_size'] != '') ? absint($data['ays_pb_font_size']) : 16;
        //Font size
        $pb_font_size_for_mobile = (isset($data['ays_pb_font_size_for_mobile']) && $data['ays_pb_font_size_for_mobile'] != '') ? absint($data['ays_pb_font_size_for_mobile']) : 16;

        //Title Text Shadow
        $enable_pb_title_text_shadow = (isset($data['ays_enable_title_text_shadow']) && $data['ays_enable_title_text_shadow'] != '') ? 'on' : 'off';

        //Title Text Shadow Color
        $pb_title_text_shadow = (isset($data['ays_title_text_shadow_color']) && $data['ays_title_text_shadow_color'] != '') ? sanitize_text_field($data['ays_title_text_shadow_color']) : 'rgba(255,255,255,0)';
        
        //Title Text Shadow X Offset
        $pb_title_text_shadow_x_offset = (isset($data['ays_pb_title_text_shadow_x_offset']) && $data['ays_pb_title_text_shadow_x_offset'] != '') ? intval( $data['ays_pb_title_text_shadow_x_offset'] )  : 2;

        //Title Text Shadow Y Offset
        $pb_title_text_shadow_y_offset = (isset($data['ays_pb_title_text_shadow_y_offset']) && $data['ays_pb_title_text_shadow_y_offset'] != '') ? intval( $data['ays_pb_title_text_shadow_y_offset'] ) : 2;

        //Title Text Shadow Z Offset
        $pb_title_text_shadow_z_offset = (isset($data['ays_pb_title_text_shadow_z_offset']) && $data['ays_pb_title_text_shadow_z_offset'] != '') ? intval( $data['ays_pb_title_text_shadow_z_offset'] ) : 0;

        //Title Text Shadow Mobile
        $enable_pb_title_text_shadow_mobile = (isset($data['ays_enable_title_text_shadow_mobile']) && $data['ays_enable_title_text_shadow_mobile'] != '') ? 'on' : 'off';

        //Title Text Shadow Color Mobile
        $pb_title_text_shadow_mobile = (isset($data['ays_title_text_shadow_color_mobile']) && $data['ays_title_text_shadow_color_mobile'] != '') ? sanitize_text_field($data['ays_title_text_shadow_color_mobile']) : 'rgba(255,255,255,0)';
        
        //Title Text Shadow X Offset Mobile
        $pb_title_text_shadow_x_offset_mobile = (isset($data['ays_pb_title_text_shadow_x_offset_mobile']) && $data['ays_pb_title_text_shadow_x_offset_mobile'] != '') ? intval( $data['ays_pb_title_text_shadow_x_offset_mobile'] )  : 2;

        //Title Text Shadow Y Offset Mobile
        $pb_title_text_shadow_y_offset_mobile = (isset($data['ays_pb_title_text_shadow_y_offset_mobile']) && $data['ays_pb_title_text_shadow_y_offset_mobile'] != '') ? intval( $data['ays_pb_title_text_shadow_y_offset_mobile'] ) : 2;

        //Title Text Shadow Z Offset Mobile
        $pb_title_text_shadow_z_offset_mobile = (isset($data['ays_pb_title_text_shadow_z_offset_mobile']) && $data['ays_pb_title_text_shadow_z_offset_mobile'] != '') ? intval( $data['ays_pb_title_text_shadow_z_offset_mobile'] ) : 0;

       // --------- Check & get post type-----------         
            $post_type_for_allfeld = array();
            if (isset($data['ays_pb_except_post_types'])) {
                $all_post_types = $data['ays_pb_except_post_types'];              
                if (isset($data["ays_pb_except_posts"])) {
                    foreach ($all_post_types as $post_type) {
                        $all_posts = get_posts( array(
                        'numberposts' => -1,            
                        'post_type'   => $post_type,
                        'suppress_filters' => true,
                        ));

                        if (!empty($all_posts)) {
                            foreach ($all_posts as $posts_value) {
                                if (in_array($posts_value->ID, $data["ays_pb_except_posts"])) {
                                    $not_post_type = false;
                                    break;
                                }else{
                                    $not_post_type = true;
                                }                   
                            }

                            if ($not_post_type) {
                                $post_type_for_allfeld[] = $post_type;
                            }
                        }else{
                            $post_type_for_allfeld[] = $post_type;
                        }
                        
                    }
                }else{
                    $post_type_for_allfeld = $all_post_types;
                }
                
            }

        // --------- end Check & get post type-----------   
     
        $switch = (isset($data['ays-pb']["onoffswitch"]) &&  $data['ays-pb']["onoffswitch"] == 'on') ? 'On' : 'Off';
        $log_user = (isset($data['ays-pb']["log_user"]) &&  $data['ays-pb']["log_user"] == 'on') ? 'On' : 'Off';
        $guest = (isset($data['ays-pb']["guest"]) &&  $data['ays-pb']["guest"] == 'on') ? 'On' : 'Off';
        $switchoverlay = (isset($data['ays-pb']["onoffoverlay"]) &&  $data['ays-pb']["onoffoverlay"] == 'on') ? 'On' : 'Off';
        $overlay_opacity = ($switchoverlay == 'On') && isset($data['ays-pb']["overlay_opacity"]) ? stripslashes(sanitize_text_field( $data['ays-pb']['overlay_opacity'] )) : '0.5'; 
        $overlay_mobile_opacity = ($switchoverlay == 'On') && isset($data['ays_pb_overlay_mobile_opacity']) ? stripslashes(sanitize_text_field( $data['ays_pb_overlay_mobile_opacity'] )) : '0.5';
        // Enable different overlay mobile text for mobile
        $enable_overlay_text_mobile = ( isset($data['ays_pb_enable_overlay_text_mobile']) && $data['ays_pb_enable_overlay_text_mobile'] == 'on' ) ? 'on' : 'off';

        //Show Popup Title
        $showPopupTitle = ( isset($data["show_popup_title"]) &&  $data["show_popup_title"] == 'on' ) ? 'On' : 'Off';

        //Show Popup Description
        $showPopupDesc = ( isset($data["show_popup_desc"]) &&  $data["show_popup_desc"] == 'on' ) ? 'On' : 'Off';

        //Enable Different Display Content Mobile
        $enable_display_content_mobile = ( isset($data['ays_pb_enable_display_content_mobile']) && $data['ays_pb_enable_display_content_mobile'] == 'on' ) ? 'on' : 'off';

        //Show Popup Title Mobile
        $show_popup_title_mobile = ( isset($data['show_popup_title_mobile']) && $data['show_popup_title_mobile'] == 'on' ) ? 'On' : 'Off';

        //Show Popup Desc Mobile
        $show_popup_desc_mobile = ( isset($data['show_popup_desc_mobile']) && $data['show_popup_desc_mobile'] == 'on' ) ? 'On' : 'Off';
        
		if(isset($data['ays-pb']["close_button"]) && $data['ays-pb']["close_button"] == 'on'){
			$closeButton = 'on';
		}else{ $closeButton = 'off';}

        if($show_all == 'yes'){
            $view_place = '';
        }else{
            $view_place = isset($data['ays-pb']["ays_pb_view_place"]) ? sanitize_text_field( implode( "***", $data['ays-pb']["ays_pb_view_place"] ) ) : '';
        }
        $JSON_user_role = json_encode($users_role);

        $author = ( isset($data['ays_pb_author']) && $data['ays_pb_author'] != "" ) ? stripcslashes( sanitize_text_field( $data['ays_pb_author'] ) ) : '';

        // Change the author of the current pb
        $pb_create_author = ( isset($data['ays_pb_create_author']) && $data['ays_pb_create_author'] != "" ) ? absint( sanitize_text_field( $data['ays_pb_create_author'] ) ) : '';

        //PB creation date
        // $pb_create_date  = !isset($data['ays_pb_create_date']) ? '0000-00-00 00:00:00' : sanitize_text_field( $data['ays_pb_create_date'] );

        $pb_create_date = (isset($data['ays_pb_change_creation_date']) && $data['ays_pb_change_creation_date'] != '') ? sanitize_text_field($data['ays_pb_change_creation_date']) : current_time( 'mysql' ) ;

        // Change the author of the current pb
        $pb_create_author = ( isset($data['ays_pb_create_author']) && $data['ays_pb_create_author'] != "" ) ? absint( sanitize_text_field( $data['ays_pb_create_author'] ) ) : '';

        if ( $pb_create_author != "" && $pb_create_author > 0 ) {
            $user = get_userdata($pb_create_author);
            if ( ! is_null( $user ) && $user ) {
                $pb_author = array(
                    'id' => $user->ID."",
                    'name' => $user->data->display_name
                );

                $author = json_encode($pb_author, JSON_UNESCAPED_SLASHES);
            } else {
                $author_data = json_decode($author, true);
                $pb_create_author = (isset( $author_data['id'] ) && $author_data['id'] != "") ? absint( sanitize_text_field( $author_data['id'] ) ) : get_current_user_id();
            }
        }

        //Enable dismiss
        $enable_dismiss = ( isset($data['ays_pb_enable_dismiss']) && $data['ays_pb_enable_dismiss'] != "" ) ? 'on' : 'off';
        $enable_dismiss_text = ( isset($data['ays_pb_enable_dismiss_text']) && $data['ays_pb_enable_dismiss_text'] != "" ) ? stripslashes( sanitize_text_field($data['ays_pb_enable_dismiss_text']) ) : 'Dismiss ad';

        //Enable dismiss mobile
        $enable_dismiss_mobile = ( isset($data['ays_pb_enable_dismiss_mobile']) && $data['ays_pb_enable_dismiss_mobile'] != "" ) ? 'on' : 'off';
        $enable_dismiss_text_mobile = ( isset($data['ays_pb_enable_dismiss_text_mobile']) && $data['ays_pb_enable_dismiss_text_mobile'] != "" ) ? stripslashes( sanitize_text_field($data['ays_pb_enable_dismiss_text_mobile']) ) : 'Dismiss ad';

        //Enabel Box Shadow
        $enable_box_shadow = ( isset( $data['ays_pb_enable_box_shadow'] ) && $data['ays_pb_enable_box_shadow'] == 'on' ) ? 'on' : 'off';

        //Enabel Box Shadow Mobile
        $enable_box_shadow_mobile = ( isset( $data['ays_pb_enable_box_shadow_mobile'] ) && $data['ays_pb_enable_box_shadow_mobile'] == 'on' ) ? 'on' : 'off';

        //Enabel Box Shadow Color
        $box_shadow_color = (!isset($data['ays_pb_box_shadow_color'])) ? '#000' : sanitize_text_field( stripslashes($data['ays_pb_box_shadow_color']) );

        //Enabel Box Shadow Color Mobile
        $box_shadow_color_mobile = ( isset($data['ays_pb_box_shadow_color_mobile']) && $data['ays_pb_box_shadow_color_mobile'] != '' ) ? sanitize_text_field( stripslashes($data['ays_pb_box_shadow_color_mobile']) ) : '#000';

        //Box Shadow X offset
        $pb_box_shadow_x_offset = (isset($data['ays_pb_box_shadow_x_offset']) && $data['ays_pb_box_shadow_x_offset'] != '' && intval( $data['ays_pb_box_shadow_x_offset'] ) != 0) ? intval( $data['ays_pb_box_shadow_x_offset'] ) : 0;

        //Box Shadow X offset Mobile
        $pb_box_shadow_x_offset_mobile = (isset($data['ays_pb_box_shadow_x_offset_mobile']) && $data['ays_pb_box_shadow_x_offset_mobile'] != '' && intval( $data['ays_pb_box_shadow_x_offset_mobile'] ) != 0) ? intval( $data['ays_pb_box_shadow_x_offset_mobile'] ) : 0;

        //Box Shadow Y offset
        $pb_box_shadow_y_offset = (isset($data['ays_pb_box_shadow_y_offset']) && $data['ays_pb_box_shadow_y_offset'] != '' && intval( $data['ays_pb_box_shadow_y_offset'] ) != 0) ? intval( $data['ays_pb_box_shadow_y_offset'] ) : 0;

        //Box Shadow Y offset Mobile
        $pb_box_shadow_y_offset_mobile = (isset($data['ays_pb_box_shadow_y_offset_mobile']) && $data['ays_pb_box_shadow_y_offset_mobile'] != '' && intval( $data['ays_pb_box_shadow_y_offset_mobile'] ) != 0) ? intval( $data['ays_pb_box_shadow_y_offset_mobile'] ) : 0;

        //Box Shadow Z offset
        $pb_box_shadow_z_offset = (isset($data['ays_pb_box_shadow_z_offset']) && $data['ays_pb_box_shadow_z_offset'] != '' && intval( $data['ays_pb_box_shadow_z_offset'] ) != 0) ? intval( $data['ays_pb_box_shadow_z_offset'] ) : 15;

        //Box Shadow Z offset Mobile
        $pb_box_shadow_z_offset_mobile = (isset($data['ays_pb_box_shadow_z_offset_mobile']) && $data['ays_pb_box_shadow_z_offset_mobile'] != '' && intval( $data['ays_pb_box_shadow_z_offset_mobile'] ) != 0) ? intval( $data['ays_pb_box_shadow_z_offset_mobile'] ) : 15;

        // Popup Name
        $popup_name = ( isset($data['ays_pb_popup_name']) && $data['ays_pb_popup_name'] != "" ) ? sanitize_text_field( $data['ays_pb_popup_name'] ) : '';

        //Disable scroll on popup
        $disable_scroll_on_popup = ( isset( $data['ays_pb_disable_scroll_on_popup'] ) && $data['ays_pb_disable_scroll_on_popup'] != '' ) ? 'on' : 'off';

        //Disable scroll on popup mobile
        $disable_scroll_on_popup_mobile = ( isset( $data['ays_pb_disable_scroll_on_popup_mobile'] ) && $data['ays_pb_disable_scroll_on_popup_mobile'] != '' ) ? 'on' : 'off';

        //Show scrollbar
        $show_scrollbar = ( isset( $data['ays_pb_show_scrollbar'] ) && $data['ays_pb_show_scrollbar'] != '' ) ? 'on' : 'off';

        //Hide on PC
        $hide_on_pc = ( isset( $data['ays_pb_hide_on_pc'] ) && $data['ays_pb_hide_on_pc'] == 'on' ) ? 'on' : 'off';

        //Hide on tablets
        $hide_on_tablets = ( isset( $data['ays_pb_hide_on_tablets'] ) && $data['ays_pb_hide_on_tablets'] == 'on' ) ? 'on' : 'off';

        //Background image position for mobile
        $pb_bg_image_direction_on_mobile = ( isset( $data['ays_pb_bg_image_direction_on_mobile'] ) && $data['ays_pb_bg_image_direction_on_mobile'] == 'on' ) ? 'on' : 'off';

        // Close button color
        $close_button_color = ( isset($data['ays_pb_close_button_color']) && $data['ays_pb_close_button_color'] != "" ) ? sanitize_text_field( $data['ays_pb_close_button_color'] ) : '#000000';

        // Close button hover color
        $close_button_hover_color = ( isset($data['ays_pb_close_button_hover_color']) && $data['ays_pb_close_button_hover_color'] != "" ) ? sanitize_text_field( $data['ays_pb_close_button_hover_color'] ) : '#000000';

        // Show only for author
        $show_only_for_author = ( isset($data['ays_pb_show_popup_only_for_author']) && $data['ays_pb_show_popup_only_for_author'] != "" ) ? 'on' : 'off';

        // Blured Overlay
        $blured_overlay = ( isset($data['ays_pb_blured_overlay']) && $data['ays_pb_blured_overlay'] != "" ) ? 'on' : 'off';

        // Blured Overlay Mobile
        $blured_overlay_mobile = ( isset($data['ays_pb_blured_overlay_mobile']) && $data['ays_pb_blured_overlay_mobile'] != "" ) ? 'on' : 'off';

        $options = array(
            'enable_background_gradient' => $enable_background_gradient,
            'background_gradient_color_1' => $pb_background_gradient_color_1,
            'background_gradient_color_2' => $pb_background_gradient_color_2,
            'pb_gradient_direction' => $pb_gradient_direction,
            'enable_background_gradient_mobile' => $enable_background_gradient_mobile,
            'background_gradient_color_1_mobile' => $pb_background_gradient_color_1_mobile,
            'background_gradient_color_2_mobile' => $pb_background_gradient_color_2_mobile,
            'pb_gradient_direction_mobile' => $pb_gradient_direction_mobile,
            'except_post_types' => $except_types,
            'except_posts' => $except_posts,
            'all_posts' => (empty($post_type_for_allfeld) ? '' : $post_type_for_allfeld),
            'close_button_delay' => $close_button_delay,
            'close_button_delay_for_mobile' => $close_button_delay_for_mobile,
            'enable_close_button_delay_for_mobile' => $enable_close_button_delay_for_mobile,
            'enable_pb_sound' => $enable_pb_sound,
            'overlay_color' => $overlay_color,
            'enable_overlay_color_mobile' => $enable_overlay_color_mobile,
            'overlay_color_mobile' => $overlay_color_mobile,
            'animation_speed' => $animation_speed,
            'enable_animation_speed_mobile' => $enable_animation_speed_mobile,
            'animation_speed_mobile' => $animation_speed_mobile,
            'close_animation_speed' => $close_animation_speed,
            'enable_close_animation_speed_mobile' => $enable_close_animation_speed_mobile,
            'close_animation_speed_mobile' => $close_animation_speed_mobile,
            'pb_mobile' => $pb_mobile,
            'close_button_text' => $close_button_text,
            'enable_close_button_text_mobile' => $enable_close_button_text_mobile,
            'close_button_text_mobile' => $close_button_text_mobile,
            'close_button_hover_text' => $close_button_hover_text,
            'mobile_width' => $mobile_width,
            'mobile_max_width' => $mobile_max_width,
            'mobile_height' => $mobile_height,
            'close_button_position' => $close_button_position,
            'enable_close_button_position_mobile' => $enable_close_button_position_mobile,
            'close_button_position_mobile' => $close_button_position_mobile,
            'show_only_once' => $show_only_once,
            'show_on_home_page' => $show_on_home_page,
            'close_popup_esc' => $close_popup_esc,
            'popup_width_by_percentage_px' => $popup_width_by_percentage_px,
            'popup_width_by_percentage_px_mobile' => $popup_width_by_percentage_px_mobile,
            'popup_content_padding' => $padding,
            'popup_padding_by_percentage_px' => $popup_padding_by_percentage_px,
            'pb_font_family' => $pb_font_family,
            'close_popup_overlay' => $close_popup_overlay,
            'close_popup_overlay_mobile' => $close_popup_overlay_mobile,
            'enable_pb_fullscreen' => $enable_pb_fullscreen,
            'enable_hide_timer' => $enable_hide_timer,
            'enable_hide_timer_mobile' => $enable_hide_timer_mobile,
            'enable_autoclose_on_completion' => $enable_autoclose_on_completion,
            'enable_social_links' => $enable_social_links,
            'social_links' => $social_links,
            'social_buttons_heading' => $social_buttons_heading,
            'close_button_size' => $close_button_size,
            'close_button_image' => $close_button_image,
            'border_style' => $border_style,
            'enable_border_style_mobile' => $enable_border_style_mobile,
            'border_style_mobile' => $border_style_mobile,
            'ays_pb_hover_show_close_btn' => $ays_pb_hover_show_close_btn,
            'disable_scroll' => $disable_scroll,
            'disable_scroll_mobile' => $disable_scroll_mobile,
            "enable_open_delay_mobile" => $enable_open_delay_mobile,
            "open_delay_mobile" => $pb_open_delay_mobile,
            "enable_scroll_top_mobile" => $enable_scroll_top_mobile,
            "scroll_top_mobile" => $pb_scroll_top_mobile,
            "enable_pb_position_mobile" => $enable_pb_position_mobile,
            "pb_position_mobile" => $pb_position_mobile,
            "pb_bg_image_position" => $pb_bg_image_position,
            "enable_pb_bg_image_position_mobile" => $enable_pb_bg_image_position_mobile,
            "pb_bg_image_position_mobile" => $pb_bg_image_position_mobile,
            "pb_bg_image_sizing" => $pb_bg_image_sizing,
            "enable_pb_bg_image_sizing_mobile" => $enable_pb_bg_image_sizing_mobile,
            "pb_bg_image_sizing_mobile" => $pb_bg_image_sizing_mobile,
            'video_theme_url' => $video_theme_url,
            'image_type_img_src' => $image_type_img_src,
            'image_type_img_redirect_url' => $image_type_img_redirect_url,
            'image_type_img_redirect_to_new_tab' => $image_type_img_redirect_to_new_tab,
            'facebook_page_url' => $facebook_page_url,
            'hide_fb_page_cover_photo' => $hide_fb_page_cover_photo,
            'use_small_fb_header' => $use_small_fb_header,
            'notification_type_components' => $notification_type_components,
            'notification_type_components_order' => $notification_type_components_order,
            'notification_main_content' => $notification_main_content,
            'notification_button_1_text' => $notification_button_1_text,
            'notification_button_1_redirect_url' => $notification_button_1_redirect_url,
            'notification_button_1_redirect_to_new_tab' => $notification_button_1_redirect_to_new_tab,
            'notification_button_1_bg_color' => $notification_button_1_bg_color,
            'notification_button_1_bg_hover_color' => $notification_button_1_bg_hover_color,
            'notification_button_1_text_color' => $notification_button_1_text_color,
            'notification_button_1_text_hover_color' => $notification_button_1_text_hover_color,
            'notification_button_1_letter_spacing' => $notification_button_1_letter_spacing,
            'notification_button_1_font_size' => $notification_button_1_font_size,
            'notification_button_1_border_radius' => $notification_button_1_border_radius,
            'notification_button_1_border_width' => $notification_button_1_border_width,
            'notification_button_1_border_color' => $notification_button_1_border_color,
            'notification_button_1_border_style' => $notification_button_1_border_style,
            'notification_button_1_padding_left_right' => $notification_button_1_padding_left_right,
            'notification_button_1_padding_top_bottom' => $notification_button_1_padding_top_bottom,
            'notification_button_1_enable_box_shadow' => $notification_button_1_enable_box_shadow,
            'notification_button_1_box_shadow_color' => $notification_button_1_box_shadow_color,
            'notification_button_1_box_shadow_x_offset' => $notification_button_1_box_shadow_x_offset,
            'notification_button_1_box_shadow_y_offset' => $notification_button_1_box_shadow_y_offset,
            'notification_button_1_box_shadow_z_offset' => $notification_button_1_box_shadow_z_offset,
            'pb_max_height' => $pb_max_height,
            'popup_max_height_by_percentage_px' => $popup_max_height_by_percentage_px,
            'pb_max_height_mobile' => $pb_max_height_mobile,
            'popup_max_height_by_percentage_px_mobile' => $popup_max_height_by_percentage_px_mobile,
            'pb_min_height' => $pb_min_height,
            'pb_font_size' => $pb_font_size,
            'pb_font_size_for_mobile' => $pb_font_size_for_mobile,
            'pb_title_text_shadow' => $pb_title_text_shadow,
            'enable_pb_title_text_shadow' => $enable_pb_title_text_shadow,
            'pb_title_text_shadow_x_offset' => $pb_title_text_shadow_x_offset,
            'pb_title_text_shadow_y_offset' => $pb_title_text_shadow_y_offset,  
            'pb_title_text_shadow_z_offset' => $pb_title_text_shadow_z_offset,
            'pb_title_text_shadow_mobile' => $pb_title_text_shadow_mobile,
            'enable_pb_title_text_shadow_mobile' => $enable_pb_title_text_shadow_mobile,
            'pb_title_text_shadow_x_offset_mobile' => $pb_title_text_shadow_x_offset_mobile,
            'pb_title_text_shadow_y_offset_mobile' => $pb_title_text_shadow_y_offset_mobile,  
            'pb_title_text_shadow_z_offset_mobile' => $pb_title_text_shadow_z_offset_mobile,
            'create_date' => $pb_create_date,
            'create_author' => $pb_create_author,
            'author' => $author,
            'enable_dismiss' => $enable_dismiss,
            'enable_dismiss_text' => $enable_dismiss_text,
            'enable_dismiss_mobile' => $enable_dismiss_mobile,
            'enable_dismiss_text_mobile' => $enable_dismiss_text_mobile,
            'enable_box_shadow' => $enable_box_shadow,
            'enable_box_shadow_mobile' => $enable_box_shadow_mobile,
            'box_shadow_color' => $box_shadow_color,
            'box_shadow_color_mobile' => $box_shadow_color_mobile,
            'pb_box_shadow_x_offset' => $pb_box_shadow_x_offset,
            'pb_box_shadow_x_offset_mobile' => $pb_box_shadow_x_offset_mobile,
            'pb_box_shadow_y_offset' => $pb_box_shadow_y_offset,
            'pb_box_shadow_y_offset_mobile' => $pb_box_shadow_y_offset_mobile,
            'pb_box_shadow_z_offset' => $pb_box_shadow_z_offset,
            'pb_box_shadow_z_offset_mobile' => $pb_box_shadow_z_offset_mobile,
            'disable_scroll_on_popup' => $disable_scroll_on_popup,
            'disable_scroll_on_popup_mobile' => $disable_scroll_on_popup_mobile,
            'show_scrollbar' => $show_scrollbar,
            'hide_on_pc' => $hide_on_pc,
            'hide_on_tablets' => $hide_on_tablets,
            'pb_bg_image_direction_on_mobile' => $pb_bg_image_direction_on_mobile,
            'close_button_color' => $close_button_color,
            'close_button_hover_color' => $close_button_hover_color,
            'blured_overlay' => $blured_overlay,
            'blured_overlay_mobile' => $blured_overlay_mobile,
            'pb_autoclose_mobile' => $autoclose_mobile,
            'enable_autoclose_delay_text_mobile' => $enable_autoclose_delay_text_mobile,
            'enable_overlay_text_mobile' => $enable_overlay_text_mobile,
            'overlay_mobile_opacity' => $overlay_mobile_opacity,
            'show_popup_title_mobile' => $show_popup_title_mobile,
            'show_popup_desc_mobile' => $show_popup_desc_mobile,
            'enable_animate_in_mobile' => $enable_animate_in_mobile,
            'animate_in_mobile' => $animate_in_mobile,
            'enable_animate_out_mobile' => $enable_animate_out_mobile,
            'animate_out_mobile' => $animate_out_mobile,
            'enable_display_content_mobile' => $enable_display_content_mobile,
            'enable_bgcolor_mobile' => $enable_bgcolor_mobile,
            'bgcolor_mobile' => $bgcolor_mobile,
            'enable_bg_image_mobile' => $enable_bg_image_mobile,
            'bg_image_mobile' => $bg_image_mobile,
            'enable_bordercolor_mobile' => $enable_bordercolor_mobile,
            'bordercolor_mobile' => $bordercolor_mobile,
            'enable_bordersize_mobile' => $enable_bordersize_mobile,
            'bordersize_mobile' => $bordersize_mobile,
            'enable_border_radius_mobile' => $enable_border_radius_mobile,
            'border_radius_mobile' => $border_radius_mobile,
        );

        $submit_type = (isset($data['submit_type'])) ?  $data['submit_type'] : '';

		if( $id == null ){
			$pb_result = $wpdb->insert(
				$pb_table,
				array(
					"title"         	            => $title,
                    "popup_name"                    => $popup_name,
					"description"   	            => $description,
                    "category_id"                   => $popup_category_id,
					"autoclose"  		            => $autoclose,
					"cookie"   			            => $cookie,
					"width"         	            => $width,
					"height"        	            => $height,
					"bgcolor"        	            => $bgcolor,
                    "textcolor"        	            => $textcolor,
                    "bordersize"      	            => $bordersize,
                    "bordercolor"     	            => $bordercolor,
                    "border_radius"    	            => $border_radius,
					"shortcode"        	            => $shortcode,
                    "custom_class"                  => $custom_class,
					"custom_css"                    => $custom_css,
					"custom_html"                   => $custom_html,
					"onoffswitch"                   => $switch,
                    "show_only_for_author"          => $show_only_for_author,
					"show_all"                      => $show_all,
                    "delay"                         => $delay,
                    "scroll_top"                    => $scroll_top,
                    "animate_in"                    => $animate_in,
                    "animate_out"                   => $animate_out,
                    "action_button"                 => $action_button,
                    "view_place"                    => $view_place,
                    "action_button_type"            => $action_button_type,
                    "modal_content"                 => $modal_content,
                    "view_type"                     => $view_type,
                    "onoffoverlay"                  => $switchoverlay,
                    "overlay_opacity"               => $overlay_opacity,
                    "show_popup_title"              => $showPopupTitle,
                    "show_popup_desc"               => $showPopupDesc,
                    "close_button"                  => $closeButton,
                    "header_bgcolor"  	            => $header_bgcolor,
                    'bg_image'                      => $bg_image,
                    'log_user'                      => $log_user,
                    'guest'                         => $guest,
                    'active_date_check'             => $active_date_check,
                    'activeInterval'                => $activeInterval,
                    'deactiveInterval'              => $deactiveInterval,
                    "pb_position"                   => $pb_position,
                    "pb_margin"                     => $pb_margin,
                    "users_role"                    => $JSON_user_role,
                    "options"                       => json_encode($options),
				),
				array(
                '%s',   // Title
                '%s',   // Popup Name
                '%s',   // description
                '%d',   // cat_id
                '%d',   //autoclose
                '%d',   // cookie
                '%d',   // width
                '%d',   // height
                '%s',   // bgcolor
                '%s',   // textcolor
                '%d',   // bordersize
                '%s',   // bordercolor
                '%d',   // border_radius
                '%s',   // shortcode
                '%s',   // custom_class
                '%s',   // custom_css
                '%s',   // custom_html
                '%s',   // onoffswitch
                '%s',   // show_only_for_author
                '%s',   // show_all
                '%d',   // delay
                '%d',   // scroll_top
                '%s',   // animate_in
                '%s',   // animate_out
                '%s',   // action_button
                '%s',   // view_place
                '%s',   // action_button_type
                '%s',   // modal_content
                '%s',   // view_type
                '%s',   // onoffoverlay
                '%f',   // overlay_opacity
                '%s',   // show_popup_title
                '%s',   // show_popup_desc
                '%s',   // close_button
                '%s',   // header_bgcolor
                '%s',   // bg_image
                '%s',   // log_user
                '%s',   // guest
                '%s',   // active_date_check
                '%s',   // activeInterval
                '%s',   // deactiveInterval
                '%s',   // pb_position
                '%d',   // pb_margin
                '%s',   // users_roles
                '%s',   // options
            )
			);
			$message = "created";
		}else{
			$pb_result = $wpdb->update(
				$pb_table,
				array(
					"title"         	            => $title,
                    "popup_name"                    => $popup_name,
					"description"   	            => $description,
                    "category_id"                   => $popup_category_id,
					"autoclose"  		            => $autoclose,
					"cookie"   			            => $cookie,
					"width"         	            => $width,
					"height"        	            => $height,
					"bgcolor"        	            => $bgcolor,
                    "textcolor"        	            => $textcolor,
                    "bordersize"      	            => $bordersize,
                    "bordercolor"     	            => $bordercolor,
                    "border_radius"    	            => $border_radius,
					"shortcode"        	            => $shortcode,
                    "custom_class"                  => $custom_class,
					"custom_css"                    => $custom_css,
					"custom_html"                   => $custom_html,
					"onoffswitch"                   => $switch,
                    "show_only_for_author"          => $show_only_for_author,
					"show_all"                      => $show_all,
                    "delay"                         => $delay,
                    "scroll_top"                    => $scroll_top,
                    "animate_in"                    => $animate_in,
                    "animate_out"                   => $animate_out,
                    "action_button"                 => $action_button,
                    "view_place"                    => $view_place,
                    "action_button_type"            => $action_button_type,
                    "modal_content"                 => $modal_content,
                    "view_type"                     => $view_type,
                    "onoffoverlay"                  => $switchoverlay,
                    "overlay_opacity"               => $overlay_opacity,
                    "show_popup_title"              => $showPopupTitle,
                    "show_popup_desc"               => $showPopupDesc,
                    "close_button"                  => $closeButton,
                    "header_bgcolor"                => $header_bgcolor,
                    'bg_image'                      => $bg_image,
                    'log_user'                      => $log_user,
                    'guest'                         => $guest,
                    'active_date_check'             => $active_date_check,
                    'activeInterval'                => $activeInterval,
                    'deactiveInterval'              => $deactiveInterval,
                    "pb_position"                   => $pb_position,
                    "pb_margin"                     => $pb_margin,
                    "users_role"                    => $JSON_user_role,
                    "options"                       => json_encode($options),
				),
				array( "id" => $id ),
				array(
                '%s',   // Title
                '%s',   // Popup Name
                '%s',   // description
                '%d',   // cat_id
                '%d',   //autoclose
                '%d',   // cookie
                '%d',   // width
                '%d',   // height
                '%s',   // bgcolor
                '%s',   // textcolor
                '%d',   // bordersize
                '%s',   // bordercolor
                '%d',   // border_radius
                '%s',   // shortcode
                '%s',   // custom_class
                '%s',   // custom_css
                '%s',   // custom_html
                '%s',   // onoffswitch
                '%s',   // show_only_for_author
                '%s',   // show_all
                '%d',   // delay
                '%d',   // scroll_top
                '%s',   // animate_in
                '%s',   // animate_out
                '%s',   // action_button
                '%s',   // view_place
                '%s',   // action_button_type
                '%s',   // modal_content
                '%s',   // view_type
                '%s',   // onoffoverlay
                '%f',   // overlay_opacity
                '%s',   // show_popup_title
                '%s',   // show_popup_desc
                '%s',   // close_button
                '%s',   // header_bgcolor
                '%s',   // bg_image
                '%s',   // log_user
                '%s',   // guest
                '%s',   // active_date_check
                '%s',   // activeInterval
                '%s',   // deactiveInterval
                '%s',   // pb_position
                '%d',   // pb_margin
                '%s',   // users_roles
                '%s',   // options
            ),
				array( "%d" )
			);
			$message = "updated";
		}

        $ays_pb_tab = isset($data['ays_pb_tab']) ? sanitize_text_field($data['ays_pb_tab']) : 'tab1';
		if( $pb_result >= 0 ){
			if($submit_type != ''){
                if($id == null){
                    $url = esc_url_raw( add_query_arg( array(
                        "action"    => "edit",
                        "popupbox"      => $wpdb->insert_id,
                        "ays_pb_tab"  => $ays_pb_tab,
                        "status"    => $message
                    ) ) );
                }else{
                    $url = esc_url_raw( add_query_arg( array(
                        "ays_pb_tab"  => $ays_pb_tab,
                        "status"    => $message
                    ) ) );
            // $url = esc_url_raw( remove_query_arg(false) ) . 'ays_pb_tab='.$ays_pb_tab."&status=" . $message . "&type=success";
                }
                wp_redirect( $url );
            }else{
                $url = esc_url_raw( remove_query_arg(array("action", "popupbox")  ) ) . "&status=" . $message . "&type=success";
                wp_redirect( $url );
            }
		}
    }

    public function get_popup_categories(){
        global $wpdb;

        $sql = "SELECT * FROM {$wpdb->prefix}ays_pb_categories";

        $result = $wpdb->get_results($sql, 'ARRAY_A');

        return $result;
    }
}
