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
            "singular" => esc_html__( "PopupBox", "ays-popup-box" ), //singular name of the listed records
            "plural" => esc_html__( "PopupBoxes", "ays-popup-box" ), //plural name of the listed records
            "ajax" => false //does this table support ajax?
        ));

        add_action( "admin_notices", array($this, "popupbox_notices") );
    }

    public function popupbox_notices() {
        $status = isset($_REQUEST["status"]) ? sanitize_text_field($_REQUEST["status"]) : "";
        $type = isset($_REQUEST["type"]) ? sanitize_text_field($_REQUEST["type"]) : "";

        if (empty($status)) return;

        if ("created" == $status)
            $updated_message = esc_html( esc_html__("PopupBox created.", "ays-popup-box") );
        elseif ("updated" == $status)
            $updated_message = esc_html( esc_html__("PopupBox saved.", "ays-popup-box") );
        elseif ("deleted" == $status)
            $updated_message = esc_html( esc_html__("PopupBox deleted.", "ays-popup-box") );
        elseif ("duplicated" == $status)
            $updated_message = esc_html( esc_html__("PopupBox duplicated.", "ays-popup-box") );
        elseif ("published" == $status)
            $updated_message = esc_html( esc_html__("PopupBox published.", "ays-popup-box") );
        elseif ("unpublished" == $status)
            $updated_message = esc_html( esc_html__("PopupBox unpublished.", "ays-popup-box") );
        elseif ("error" == $status)
            $updated_message = esc_html__( "You're not allowed to add popupbox for more popupboxes please checkout to ", "ays-popup-box")."<a href='https://ays-pro.com/wordpress/popup-box' target='_blank'>PRO ".esc_html__("version", "ays-popup-box")."</a>.";

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
            "all" => "<a " . $selected_all . " href='" . $href . "'>" . esc_html__('All', "ays-popup-box") . " (" . $all_count . ")</a>",
            "published" => "<a " . $selected_on . " href='" . $href . "&fstatus=published'>" . esc_html__('Published', "ays-popup-box") . " (" . $published_count . ")</a>",
            "unpublished" => "<a " . $selected_off . " href='" . $href . "&fstatus=unpublished'>" . esc_html__('Unpublished', "ays-popup-box") . " (" . $unpublished_count . ")</a>"
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

        if (isset($_REQUEST['s']) && $_REQUEST['s'] != '') {
            $search = esc_sql(sanitize_text_field($_REQUEST['s']));
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
        $do_search = $search ? sprintf( " title LIKE '%%%s%%' ", esc_sql($wpdb->esc_like($search)) ) : '';

        $this->items = self::get_ays_popupboxes($per_page, $current_page, $do_search);
    }

    /** Text displayed when no customer data is available */
    public function no_items() {
        echo esc_html__("There are no popupboxes yet.", "ays-popup-box");
    }

    /**
     *  Associative array of columns
     *
     * @return array
     */
    function get_columns() {
        $columns = array(
            "cb" => "<input type='checkbox' />",
            "title" => esc_html__("Title", "ays-popup-box"),
            "popup_image" => esc_html__("Image", "ays-popup-box"),
            'category_id' => esc_html__('Category', "ays-popup-box"),
            "onoffswitch" => esc_html__("Status", "ays-popup-box"),
            "modal_content" => esc_html__("Type", "ays-popup-box"),
            "view_type" => esc_html__("Template", "ays-popup-box"),
            "create_date" => esc_html__("Created", "ays-popup-box"),
            "views" => esc_html__("Views", "ays-popup-box"),
            "conversions" => esc_html__("Conversions", "ays-popup-box"),
            "id" => esc_html__("ID", "ays-popup-box"),
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
                return print_r($item, true); // Show the whole array for troubleshooting purposes
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
            'edit' => sprintf( "<a href='?page=%s&action=%s&popupbox=%d'>" . esc_html__('Edit', "ays-popup-box") . "</a>", esc_attr($_REQUEST["page"]), "edit", absint($item["id"]) ),
            'duplicate' => sprintf( "<a href='?page=%s&action=%s&popupbox=%d'>" . esc_html__('Duplicate', "ays-popup-box") . '</a>', esc_attr($_REQUEST['page']), 'duplicate', absint($item['id']) ),
            'delete' => sprintf( "<a class='ays_pb_confirm_del' data-message='%s' href='?page=%s&action=%s&popupbox=%d&_wpnonce=%s'>" . esc_html__('Delete', "ays-popup-box") . '</a>', $restitle, esc_attr($_REQUEST['page']), 'delete', absint($item['id']), $delete_nonce )
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
                $modal_content = esc_html__('Custom Content', "ays-popup-box");
                break;
            case 'shortcode':
                $modal_content = esc_html__('Shortcode', "ays-popup-box");
                break;
            case 'video_type':
                $modal_content = esc_html__('Video', "ays-popup-box");
                break;
            case 'image_type':
                $modal_content = esc_html__('Image', "ays-popup-box");
                break;
            case 'facebook_type':
                $modal_content = esc_html__('Facebook', "ays-popup-box");
                break;
            case 'notification_type':
                $modal_content = esc_html__('Notification', "ays-popup-box");
                break;
            default:
                $modal_content = esc_html__('Custom Content', "ays-popup-box");
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
                $view_type = esc_html__('Default', "ays-popup-box");
                break;
            case 'lil':
                $view_type = esc_html__('Red', "ays-popup-box");
                break;
            case 'image':
                $view_type = esc_html__('Modern', "ays-popup-box");
                break;
            case 'minimal':
                $view_type = esc_html__('Minimal', "ays-popup-box");
                break;
            case 'template':
                $view_type = esc_html__('Sale', "ays-popup-box");
                break;
            case 'mac':
                $view_type = esc_html__('MacOs window', "ays-popup-box");
                break;
            case 'ubuntu':
                $view_type = esc_html__('Ubuntu', "ays-popup-box");
                break;
            case 'winXP':
                $view_type = esc_html__('Windows XP', "ays-popup-box");
                break;
            case 'win98':
                $view_type = esc_html__('Windows 98', "ays-popup-box");
                break;
            case 'cmd':
                $view_type = esc_html__('Command Prompt', "ays-popup-box");
                break;
            default:
                $view_type = esc_html__('Default', "ays-popup-box");
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
                die("Go get a life script kiddies");
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
            $unpublished_ids = ( isset($_POST['bulk-delete']) && !empty($_POST['bulk-delete']) ) ? esc_sql($_POST['bulk-delete']) : array();
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
            $order_by .= ' ';
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
            "bulk-published" =>  esc_html__('Publish', "ays-popup-box"),
            "bulk-unpublished" =>  esc_html__('Unpublish', "ays-popup-box"),
            "bulk-delete" =>  esc_html__('Delete', "ays-popup-box"),
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
                <option value=""><?php echo esc_html__('Select Category', "ays-popup-box")?></option>
                <?php
                    foreach ($categories_select as $cat_title) {
                        echo "<option " . $cat_title['selected'] . " value='" . $cat_title['id'] . "'>" . $cat_title['title'] . "</option>";
                    }
                ?>
            </select>
            <select name="filterbyAuthor-<?php echo esc_attr($which); ?>" id="bulk-action-selector-<?php echo esc_attr($which); ?>">
                <option value=""><?php echo esc_html__('Select Author', "ays-popup-box")?></option>
                <?php
                    foreach ($authors_select as $author) {
                        echo "<option " . $author['selected'] . " value='" . $author['id'] . "'>" . $author['display_name'] . "</option>";
                    }
                ?>
            </select>
            <select name="filterbyType-<?php echo esc_attr($which); ?>" id="bulk-action-selector-<?php echo esc_attr($which); ?>">
                <option value=""><?php echo esc_html__('Select Type', "ays-popup-box")?></option>
                <?php
                    foreach ($types_select as $type) {
                        echo "<option " . $type['selected'] . " value='" . $type['value'] . "'>" . $type['title'] . "</option>";
                    }
                ?>
            </select>
            <input type="button" id="doaction-<?php echo esc_attr($which); ?>" class="ays-popup-question-tab-all-filter-button-<?php echo esc_attr($which); ?> button" value="<?php echo esc_html__("Filter", "ays-popup-box"); ?>">
        </div>
        <a href="?page=<?php echo esc_attr($_REQUEST['page']); ?>" class="button ays-pb-clear-filters"><?php echo esc_html__("Clear filters", "ays-popup-box"); ?></a>
        <?php
    }

    public function duplicate_popupbox($id) {
        global $wpdb;

        $pb_table = $wpdb->prefix . "ays_pb";
        $popup = $this->get_popupbox_by_id($id);

        // Popup Name
        $popup_name = (isset($popup['popup_name']) && $popup['popup_name'] != '') ? 'Copy - ' . sanitize_text_field($popup['popup_name']) : '';

        // Popup description
        if (is_multisite()) {
            if (is_super_admin()) {
                $description = (isset($popup['description']) && $popup['description'] != '') ? stripslashes($popup['description'] ) : '';
            } else {
                $description = (isset($popup['description']) && $popup['description'] != '') ? stripslashes(wp_kses_post($popup['description'])) : '';
            }
        } else {
            if (current_user_can('unfiltered_html')) {
                $description = (isset($popup['description']) && $popup['description'] != '') ? stripslashes($popup['description']) : '';
            } else {
                $description = (isset($popup['description']) && $popup['description'] != '') ? stripslashes( wp_kses_post($popup['description']) ) : '';
            }
        }

        // Custom content
        if (is_multisite()) {
            if (is_super_admin()) {
                $popup_custom_html = (isset($popup['custom_html']) && $popup['custom_html'] != '') ? stripslashes($popup['custom_html']) : '';
            } else {
                $popup_custom_html = (isset($popup['custom_html']) && $popup['custom_html'] != '') ? stripslashes( wp_kses_post($popup['custom_html']) ) : '';
            }
        } else {
            if (current_user_can('unfiltered_html')) {
                $popup_custom_html = (isset($popup['custom_html']) && $popup['custom_html'] != '') ? stripslashes($popup['custom_html']) : '';
            } else {
                $popup_custom_html = (isset($popup['custom_html']) && $popup['custom_html'] != '') ? stripslashes( wp_kses_post($popup['custom_html']) ) : '';
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

        $options['create_date'] = gmdate("Y-m-d H:i:s");
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
                "active_time_check" => stripslashes( sanitize_text_field($popup['active_time_check']) ),
                "active_time_start" => stripslashes( sanitize_text_field($popup['active_time_start']) ),
                "active_time_end" => stripslashes( sanitize_text_field($popup['active_time_end']) ),
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
                '%s', // active_time_check
                '%s', // active_time_start
                '%s', // active_time_end
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

    public function get_popup_categories() {
        global $wpdb;

        $sql = "SELECT * FROM {$wpdb->prefix}ays_pb_categories";

        $result = $wpdb->get_results($sql, 'ARRAY_A');

        return $result;
    }

    public function add_or_edit_popupbox() {
		global $wpdb;
		$pb_table = $wpdb->prefix . 'ays_pb';

        $check_nonce = isset($_POST['pb_action']) && wp_verify_nonce(sanitize_text_field($_POST['pb_action']), 'pb_action');

        if (!$check_nonce) {
            return;
        }

        $social_links_default = array(
            'linkedin_link' => '',
            'facebook_link' => '',
            'twitter_link' => '',
            'vkontakte_link' => '',
            'youtube_link' => '',
            'instagram_link' => '',
            'behance_link' => '',
        );

        // Id
		$id = (isset($_POST['id']) && $_POST['id'] != '') ? absint( intval($_POST['id']) ) : null;

        // Tab
        $ays_pb_tab = (isset($_POST['ays_pb_tab']) && $_POST['ays_pb_tab'] != '') ? sanitize_text_field($_POST['ays_pb_tab']) : 'tab1';

        // Author
        $author = (isset($_POST['ays_pb_author']) && $_POST['ays_pb_author'] != '') ? stripcslashes( sanitize_text_field($_POST['ays_pb_author']) ) : '';

        // Popup type
		$modal_content = (isset($_POST['ays-pb']['modal_content']) && $_POST['ays-pb']['modal_content'] != '') ? wp_unslash( sanitize_text_field($_POST['ays-pb']['modal_content']) ) : '';

        // Popup title
		$title = (isset($_POST['ays-pb']['popup_title']) && $_POST['ays-pb']['popup_title'] != '') ? wp_unslash( sanitize_text_field($_POST['ays-pb']['popup_title']) ) : 'Demo Title';

        // Enable popup
        $switch = (isset($_POST['ays-pb']['onoffswitch']) && $_POST['ays-pb']['onoffswitch'] == 'on') ? 'On' : 'Off';

        // Shortcode
		$shortcode = (isset($_POST['ays-pb']['shortcode']) && $_POST['ays-pb']['shortcode'] != '') ? wp_unslash( sanitize_text_field($_POST['ays-pb']["shortcode"]) ) : '';

        // Custom content
        if (is_multisite()) {
            if (is_super_admin()) {
                $custom_html = (isset($_POST['ays-pb']['custom_html']) && $_POST['ays-pb']['custom_html'] != '') ? stripslashes($_POST['ays-pb']['custom_html']) : '';
            } else {
                $custom_html = (isset($_POST['ays-pb']['custom_html']) && $_POST['ays-pb']['custom_html'] != '') ? wp_kses_post($_POST['ays-pb']['custom_html']) : '';
            }
        } else {
            if (current_user_can('unfiltered_html')) {
                $custom_html = (isset($_POST['ays-pb']['custom_html']) && $_POST['ays-pb']['custom_html'] != '') ? stripslashes($_POST['ays-pb']['custom_html']) : '';
            } else {
                $custom_html = (isset($_POST['ays-pb']['custom_html']) && $_POST['ays-pb']['custom_html'] != '') ? stripslashes( wp_kses_post($_POST['ays-pb']['custom_html']) ) : '';
            }
        }

        // Video
        $video_theme_url = (isset($_POST['ays_video_theme_url']) && !empty($_POST['ays_video_theme_url'])) ? wp_http_validate_url($_POST['ays_video_theme_url']) : '';

        // Image type | Main image
        $image_type_img_src = (isset($_POST['ays_pb_image_type_img_src']) && $_POST['ays_pb_image_type_img_src'] != '') ? sanitize_url($_POST['ays_pb_image_type_img_src']) : '';

        // Image type | Redirect URL
        $image_type_img_redirect_url = (isset($_POST['ays_pb_image_type_img_redirect_url']) && $_POST['ays_pb_image_type_img_redirect_url'] != '') ? sanitize_url($_POST['ays_pb_image_type_img_redirect_url']) : '';

        // Image type | Redirect to the new tab
        $image_type_img_redirect_to_new_tab = (isset($_POST['ays_pb_image_type_img_redirect_to_new_tab']) && $_POST['ays_pb_image_type_img_redirect_to_new_tab'] == 'on') ? 'on' : 'off';

        // Facebook type | Facebook page URL
        $facebook_page_url = (isset($_POST['ays_pb_facebook_page_url']) && $_POST['ays_pb_facebook_page_url'] != '') ? sanitize_url($_POST['ays_pb_facebook_page_url']) : '';

        // Facebook type | Hide FB page cover photo
        $hide_fb_page_cover_photo = (isset($_POST['ays_pb_hide_fb_page_cover_photo']) && $_POST['ays_pb_hide_fb_page_cover_photo'] == 'on') ? 'on' : 'off';

        // Facebook type | Use small FB header
        $use_small_fb_header = (isset($_POST['ays_pb_use_small_fb_header']) && $_POST['ays_pb_use_small_fb_header'] == 'on') ? 'on' : 'off';

        // Notification type active columns
        $notification_type_components = (isset($_POST['ays_notification_type_components']) && !empty($_POST['ays_notification_type_components'])) ? array_map('sanitize_text_field', $_POST['ays_notification_type_components']) : array();

        // Notification type columns order
        $notification_type_components_order = (isset($_POST['ays_notification_type_components_order']) && !empty($_POST['ays_notification_type_components_order'])) ? array_map('sanitize_text_field', $_POST['ays_notification_type_components_order']) : array();

        // Notification type | Logo image
        $notification_logo_image = (isset($_POST['ays_pb_notification_logo_image']) && $_POST['ays_pb_notification_logo_image'] != '') ? sanitize_url($_POST['ays_pb_notification_logo_image']) : '';

        // Notification type | Logo redirect URL
        $notification_logo_redirect_url = (isset($_POST['ays_pb_notification_logo_redirect_url']) && $_POST['ays_pb_notification_logo_redirect_url'] != '') ? sanitize_url($_POST['ays_pb_notification_logo_redirect_url']) : '';

        // Notification type | Logo redirect to the new tab
        $notification_logo_redirect_to_new_tab = (isset($_POST['ays_pb_notification_logo_redirect_to_new_tab']) && $_POST['ays_pb_notification_logo_redirect_to_new_tab'] == 'on') ? 'on' : 'off';

        // Notification type | Logo width | On dektop
        $notification_logo_width = (isset($_POST['ays_pb_notification_logo_width']) && $_POST['ays_pb_notification_logo_width'] != '') ? absint( intval($_POST['ays_pb_notification_logo_width']) ) : 100;

        // Notification type | Logo width | Measurement unit | On dektop
        $notification_logo_width_measurement_unit = (isset($_POST['ays_pb_notification_logo_width_measurement_unit']) && $_POST['ays_pb_notification_logo_width_measurement_unit'] != '') ? stripslashes( sanitize_text_field($_POST['ays_pb_notification_logo_width_measurement_unit']) ) : 'percentage';

        // Notification type | Logo width | On mobile
        $notification_logo_width_mobile = (isset($_POST['ays_pb_notification_logo_width_mobile']) && $_POST['ays_pb_notification_logo_width_mobile'] != '') ? absint( intval($_POST['ays_pb_notification_logo_width_mobile']) ) : 100;

        // Notification type | Logo width | Measurement unit | On mobile
        $notification_logo_width_measurement_unit_mobile = (isset($_POST['ays_pb_notification_logo_width_measurement_unit_mobile']) && $_POST['ays_pb_notification_logo_width_measurement_unit_mobile'] != '') ? stripslashes( sanitize_text_field($_POST['ays_pb_notification_logo_width_measurement_unit_mobile']) ) : 'percentage';

        // Notification type | Logo max-width | On dektop
        $notification_logo_max_width = (isset($_POST['ays_pb_notification_logo_max_width']) && $_POST['ays_pb_notification_logo_max_width'] != '') ? absint( intval($_POST['ays_pb_notification_logo_max_width']) ) : 100;

        // Notification type | Logo max-width | Measurement unit | On dektop
        $notification_logo_max_width_measurement_unit = (isset($_POST['ays_pb_notification_logo_max_width_measurement_unit']) && $_POST['ays_pb_notification_logo_max_width_measurement_unit'] != '') ? stripslashes( sanitize_text_field($_POST['ays_pb_notification_logo_max_width_measurement_unit']) ) : 'pixels';

        // Notification type | Logo max-width | On mobile
        $notification_logo_max_width_mobile = (isset($_POST['ays_pb_notification_logo_max_width_mobile']) && $_POST['ays_pb_notification_logo_max_width_mobile'] != '') ? absint( intval($_POST['ays_pb_notification_logo_max_width_mobile']) ) : 100;

        // Notification type | Logo max-width | Measurement unit | On mobile
        $notification_logo_max_width_measurement_unit_mobile = (isset($_POST['ays_pb_notification_logo_max_width_measurement_unit_mobile']) && $_POST['ays_pb_notification_logo_max_width_measurement_unit_mobile'] != '') ? stripslashes( sanitize_text_field($_POST['ays_pb_notification_logo_max_width_measurement_unit_mobile']) ) : 'pixels';

        // Notification type | Logo min-width | On dektop
        $notification_logo_min_width = (isset($_POST['ays_pb_notification_logo_min_width']) && $_POST['ays_pb_notification_logo_min_width'] != '') ? absint( intval($_POST['ays_pb_notification_logo_min_width']) ) : 50;

        // Notification type | Logo min-width | Measurement unit | On dektop
        $notification_logo_min_width_measurement_unit = (isset($_POST['ays_pb_notification_logo_min_width_measurement_unit']) && $_POST['ays_pb_notification_logo_min_width_measurement_unit'] != '') ? stripslashes( sanitize_text_field($_POST['ays_pb_notification_logo_min_width_measurement_unit']) ) : 'pixels';

        // Notification type | Logo min-width | On mobile
        $notification_logo_min_width_mobile = (isset($_POST['ays_pb_notification_logo_min_width_mobile']) && $_POST['ays_pb_notification_logo_min_width_mobile'] != '') ? absint( intval($_POST['ays_pb_notification_logo_min_width_mobile']) ) : 50;

        // Notification type | Logo min-width | Measurement unit | On mobile
        $notification_logo_min_width_measurement_unit_mobile = (isset($_POST['ays_pb_notification_logo_min_width_measurement_unit_mobile']) && $_POST['ays_pb_notification_logo_min_width_measurement_unit_mobile'] != '') ? stripslashes( sanitize_text_field($_POST['ays_pb_notification_logo_min_width_measurement_unit_mobile']) ) : 'pixels';

        // Notification type | Logo max-height
        $notification_logo_max_height = (isset($_POST['ays_pb_notification_logo_max_height']) && $_POST['ays_pb_notification_logo_max_height'] != '') ? absint( intval($_POST['ays_pb_notification_logo_max_height']) ) : '';

        // Notification type | Logo min-height
        $notification_logo_min_height = (isset($_POST['ays_pb_notification_logo_min_height']) && $_POST['ays_pb_notification_logo_min_height'] != '') ? absint( intval($_POST['ays_pb_notification_logo_min_height']) ) : '';

        // Notification type | Logo image sizing
        $notification_logo_image_sizing = (isset($_POST['ays_pb_notification_logo_image_sizing']) && $_POST['ays_pb_notification_logo_image_sizing'] != '') ? stripslashes( sanitize_text_field($_POST['ays_pb_notification_logo_image_sizing']) ) : 'cover';

        // Notification type | Logo image shape
        $notification_logo_image_shape = (isset($_POST['ays_pb_notification_logo_image_shape']) && $_POST['ays_pb_notification_logo_image_shape'] != '') ? stripslashes( sanitize_text_field($_POST['ays_pb_notification_logo_image_shape']) ) : 'rectangle';

        // Notification type | Main content
        $notification_main_content = (isset($_POST['ays_pb_notification_main_content']) && $_POST['ays_pb_notification_main_content'] != '') ? wp_kses_post($_POST['ays_pb_notification_main_content']) : '';

        // Notification type | Button 1 text
        $notification_button_1_text = (isset($_POST['ays_pb_notification_button_1_text']) && $_POST['ays_pb_notification_button_1_text'] != '') ? stripslashes( sanitize_text_field($_POST['ays_pb_notification_button_1_text']) ) : '';

        // Notification type | Button 1 hover text
        $notification_button_1_hover_text = (isset($_POST['ays_pb_notification_button_1_hover_text']) && $_POST['ays_pb_notification_button_1_hover_text'] != '') ? stripslashes( sanitize_text_field($_POST['ays_pb_notification_button_1_hover_text']) ) : '';

        // Notification type | Button 1 redirect URL
        $notification_button_1_redirect_url = (isset($_POST['ays_pb_notification_button_1_redirect_url']) && $_POST['ays_pb_notification_button_1_redirect_url'] != '') ? sanitize_url($_POST['ays_pb_notification_button_1_redirect_url']) : '';

        // Notification type | Button 1 redirect to the new tab
        $notification_button_1_redirect_to_new_tab = (isset($_POST['ays_pb_notification_button_1_redirect_to_new_tab']) && $_POST['ays_pb_notification_button_1_redirect_to_new_tab'] == 'on') ? 'on' : 'off';

        // Notification type | Button 1 background color
        $notification_button_1_bg_color = (isset($_POST['ays_pb_notification_button_1_bg_color']) && $_POST['ays_pb_notification_button_1_bg_color'] != '') ? stripslashes( sanitize_text_field($_POST['ays_pb_notification_button_1_bg_color']) ) : '#F66123';

        // Notification type | Button 1 background hover color
        $notification_button_1_bg_hover_color = (isset($_POST['ays_pb_notification_button_1_bg_hover_color']) && $_POST['ays_pb_notification_button_1_bg_hover_color'] != '') ? stripslashes( sanitize_text_field($_POST['ays_pb_notification_button_1_bg_hover_color']) ) : '#F66123';

        // Notification type | Button 1 text color
        $notification_button_1_text_color = (isset($_POST['ays_pb_notification_button_1_text_color']) && $_POST['ays_pb_notification_button_1_text_color'] != '') ? stripslashes( sanitize_text_field($_POST['ays_pb_notification_button_1_text_color']) ) : '#FFFFFF';

        // Notification type | Button 1 text hover color
        $notification_button_1_text_hover_color = (isset($_POST['ays_pb_notification_button_1_text_hover_color']) && $_POST['ays_pb_notification_button_1_text_hover_color'] != '') ? stripslashes( sanitize_text_field($_POST['ays_pb_notification_button_1_text_hover_color']) ) : '#FFFFFF';

        // Notification type | Button 1 text transformation
        $notification_button_1_text_transformation = (isset($_POST['ays_pb_notification_button_1_text_transformation']) && $_POST['ays_pb_notification_button_1_text_transformation'] != '') ? stripslashes( sanitize_text_field($_POST['ays_pb_notification_button_1_text_transformation']) ) : 'none';

        // Notification type | Button 1 text decoration
        $notification_button_1_text_decoration = (isset($_POST['ays_pb_notification_button_1_text_decoration']) && $_POST['ays_pb_notification_button_1_text_decoration'] != '') ? stripslashes( sanitize_text_field($_POST['ays_pb_notification_button_1_text_decoration']) ) : 'none';

        // Notification type | Button 1 letter spacing
        $notification_button_1_letter_spacing = (isset($_POST['ays_pb_notification_button_1_letter_spacing']) && $_POST['ays_pb_notification_button_1_letter_spacing'] != '') ? absint( intval($_POST['ays_pb_notification_button_1_letter_spacing']) ) : 0;

        // Notification type | Button 1 letter spacing / On mobile
        $notification_button_1_letter_spacing_mobile = (isset($_POST['ays_pb_notification_button_1_letter_spacing_mobile']) && $_POST['ays_pb_notification_button_1_letter_spacing_mobile'] != '') ? absint( intval($_POST['ays_pb_notification_button_1_letter_spacing_mobile']) ) : 0;

        // Notification type | Button 1 font size | On desktop
        $notification_button_1_font_size = (isset($_POST['ays_pb_notification_button_1_font_size']) && $_POST['ays_pb_notification_button_1_font_size'] != '') ? absint( intval($_POST['ays_pb_notification_button_1_font_size']) ) : 15;

        // Notification type | Button 1 font size | On mobile
        $notification_button_1_font_size_mobile = (isset($_POST['ays_pb_notification_button_1_font_size_mobile']) && $_POST['ays_pb_notification_button_1_font_size_mobile'] != '') ? absint( intval($_POST['ays_pb_notification_button_1_font_size_mobile']) ) : 15;

        // Notification type | Button 1 font weight | On desktop
        $notification_button_1_font_weight = (isset($_POST['ays_pb_notification_button_1_font_weight']) && $_POST['ays_pb_notification_button_1_font_weight'] != '') ? stripslashes( sanitize_text_field($_POST['ays_pb_notification_button_1_font_weight']) ) : 'normal';

        // Notification type | Button 1 font weight | On mobile
        $notification_button_1_font_weight_mobile = (isset($_POST['ays_pb_notification_button_1_font_weight_mobile']) && $_POST['ays_pb_notification_button_1_font_weight_mobile'] != '') ? stripslashes( sanitize_text_field($_POST['ays_pb_notification_button_1_font_weight_mobile']) ) : 'normal';

        // Notification type | Button 1 border radius
        $notification_button_1_border_radius = (isset($_POST['ays_pb_notification_button_1_border_radius']) && $_POST['ays_pb_notification_button_1_border_radius'] != '') ? absint( intval($_POST['ays_pb_notification_button_1_border_radius']) ) : 6;

        // Notification type | Button 1 border width
        $notification_button_1_border_width = (isset($_POST['ays_pb_notification_button_1_border_width']) && $_POST['ays_pb_notification_button_1_border_width'] != '') ? absint( intval($_POST['ays_pb_notification_button_1_border_width']) ) : 0;

        // Notification type | Button 1 border color
        $notification_button_1_border_color = (isset($_POST['ays_pb_notification_button_1_border_color']) && $_POST['ays_pb_notification_button_1_border_color'] != '') ? stripslashes( sanitize_text_field($_POST['ays_pb_notification_button_1_border_color']) ) : '#FFFFFF';

        // Notification type | Button 1 border style
        $notification_button_1_border_style = (isset($_POST['ays_pb_notification_button_1_border_style']) && $_POST['ays_pb_notification_button_1_border_style'] != '') ? stripslashes( sanitize_text_field($_POST['ays_pb_notification_button_1_border_style']) ) : 'solid';

        // Notification type | Button 1 padding left/right
        $notification_button_1_padding_left_right = (isset($_POST['ays_pb_notification_button_1_padding_left_right']) && $_POST['ays_pb_notification_button_1_padding_left_right'] !== '') ? absint( intval($_POST['ays_pb_notification_button_1_padding_left_right']) ) : 32;

        // Notification type | Button 1 padding top/bottom
        $notification_button_1_padding_top_bottom = (isset($_POST['ays_pb_notification_button_1_padding_top_bottom']) && $_POST['ays_pb_notification_button_1_padding_top_bottom'] !== '') ? absint( intval($_POST['ays_pb_notification_button_1_padding_top_bottom']) ) : 12;

        // Notification type | Button 1 padding transition
        $notification_button_1_transition = (isset($_POST['ays_pb_notification_button_1_transition']) && $_POST['ays_pb_notification_button_1_transition'] !== '') ? stripslashes( sanitize_text_field($_POST['ays_pb_notification_button_1_transition']) ) : '0.3';

        // Notification type | Button 1 box shadow
        $notification_button_1_enable_box_shadow = (isset($_POST['ays_pb_notification_button_1_enable_box_shadow']) && $_POST['ays_pb_notification_button_1_enable_box_shadow'] == 'on') ? 'on' : 'off';

        // Notification type | Button 1 box shadow color
        $notification_button_1_box_shadow_color = (isset($_POST['ays_pb_notification_button_1_box_shadow_color']) && $_POST['ays_pb_notification_button_1_box_shadow_color'] != '') ? sanitize_text_field($_POST['ays_pb_notification_button_1_box_shadow_color']) : '#FF8319';

        // Notification type | Button 1 box shadow X offset
        $notification_button_1_box_shadow_x_offset = (isset($_POST['ays_pb_notification_button_1_box_shadow_x_offset']) && $_POST['ays_pb_notification_button_1_box_shadow_x_offset'] != '') ? intval($_POST['ays_pb_notification_button_1_box_shadow_x_offset']) : 0;

        // Notification type | Button 1 box shadow Y offset
        $notification_button_1_box_shadow_y_offset = (isset($_POST['ays_pb_notification_button_1_box_shadow_y_offset']) && $_POST['ays_pb_notification_button_1_box_shadow_y_offset'] != '') ? intval($_POST['ays_pb_notification_button_1_box_shadow_y_offset']) : 0;

        // Notification type | Button 1 box shadow Z offset
        $notification_button_1_box_shadow_z_offset = (isset($_POST['ays_pb_notification_button_1_box_shadow_z_offset']) && $_POST['ays_pb_notification_button_1_box_shadow_z_offset'] != '') ? intval($_POST['ays_pb_notification_button_1_box_shadow_z_offset']) : 10;
 
        // Popup description
        if (is_multisite()) {
            if (is_super_admin()) {
                $description = (isset($_POST['ays-pb']['popup_description']) && $_POST['ays-pb']['popup_description'] != '') ? stripslashes($_POST['ays-pb']['popup_description']) : '';
            } else {
                $description = (isset($_POST['ays-pb']['popup_description']) && $_POST['ays-pb']['popup_description'] != '') ? wp_kses_post($_POST['ays-pb']['popup_description']) : '';
            }
        } else {
            if (current_user_can('unfiltered_html')) {
                $description = (isset($_POST['ays-pb']['popup_description']) && $_POST['ays-pb']['popup_description'] != '') ? stripslashes($_POST['ays-pb']['popup_description']) : '';
            } else {
                $description = (isset($_POST['ays-pb']['popup_description']) && $_POST['ays-pb']['popup_description'] != '') ? wp_kses_post($_POST['ays-pb']['popup_description']) : '';
            }
        }

        // Show popup only for author
        $show_only_for_author = (isset($_POST['ays_pb_show_popup_only_for_author']) && $_POST['ays_pb_show_popup_only_for_author'] != '') ? 'on' : 'off';

        // Display
		$show_all = (isset($_POST['ays-pb']['show_all'] ) && $_POST['ays-pb']['show_all'] != '') ? wp_unslash( sanitize_text_field($_POST['ays-pb']['show_all']) ) : 'all';

        // Post type
        $except_types = isset($_POST['ays_pb_except_post_types']) ? $_POST['ays_pb_except_post_types'] : array();

        // Posts
        $except_posts = isset($_POST['ays_pb_except_posts']) ? $_POST['ays_pb_except_posts'] : array();

        // Show on Home page
        $show_on_home_page = (isset($_POST['ays_pb_show_on_home_page']) && $_POST['ays_pb_show_on_home_page'] == 'on') ? 'on' : 'off';

        // Popup trigger
		$action_button_type = (isset($_POST['ays-pb']['action_button_type']) && $_POST['ays-pb']['action_button_type'] != '') ? wp_unslash( sanitize_text_field($_POST['ays-pb']['action_button_type']) ) : 'both';

        // CSS selector(s) for trigger click
		$action_button = (isset($_POST['ays-pb']['action_button']) && $_POST['ays-pb']['action_button'] != '') ? wp_unslash( sanitize_text_field($_POST['ays-pb']['action_button']) ) : '';

        // Popup position
        $pb_position = (isset($_POST['ays-pb']['pb_position']) && $_POST['ays-pb']['pb_position'] != '') ? wp_unslash( sanitize_text_field($_POST['ays-pb']['pb_position']) ) : 'center-center';

        // Enable different popup position for mobile
        $enable_pb_position_mobile = (isset($_POST['ays_pb_enable_popup_position_mobile']) && $_POST['ays_pb_enable_popup_position_mobile'] == 'on') ? 'on' : 'off';

        // Popup position mobile
        $pb_position_mobile = (isset($_POST['ays_pb_position_mobile']) && $_POST['ays_pb_position_mobile'] != '') ? wp_unslash( sanitize_text_field($_POST['ays_pb_position_mobile']) ) : 'center-center';

        // Popup margin (px)
        $pb_margin = (isset($_POST['ays-pb']['pb_margin']) && $_POST['ays-pb']['pb_margin'] != '') ? wp_unslash( sanitize_text_field( intval($_POST['ays-pb']['pb_margin']) ) ) : 0;

        // Open delay
		$delay = (isset($_POST['ays-pb']['delay']) && $_POST['ays-pb']['delay'] != '') ? wp_unslash( sanitize_text_field($_POST['ays-pb']['delay']) ) : 0;

        // Enable different open delay for mobile
        $enable_open_delay_mobile = (isset($_POST['ays_pb_enable_open_delay_mobile']) && $_POST['ays_pb_enable_open_delay_mobile'] == 'on') ? 'on' : 'off';

        // Open delay mobile
        $pb_open_delay_mobile = (isset($_POST['ays_pb_open_delay_mobile']) && $_POST['ays_pb_open_delay_mobile'] != '') ? wp_unslash( sanitize_text_field($_POST['ays_pb_open_delay_mobile']) ) : 0;

        // Open by scrolling down
		$scroll_top = (isset($_POST['ays-pb']['scroll_top']) && $_POST['ays-pb']['scroll_top'] != '') ? wp_unslash( sanitize_text_field( intval( round($_POST['ays-pb']['scroll_top']) ) ) ) : 0;

        // Enable different open by scrolling down for mobile
        $enable_scroll_top_mobile = (isset($_POST['ays_pb_enable_scroll_top_mobile']) && $_POST['ays_pb_enable_scroll_top_mobile'] == 'on') ? 'on' : 'off';

        // Open by scrolling down mobile
        $pb_scroll_top_mobile = (isset($_POST['ays_pb_scroll_top_mobile']) && $_POST['ays_pb_scroll_top_mobile'] != '') ? wp_unslash( sanitize_text_field($_POST['ays_pb_scroll_top_mobile']) ) : 0;

        // Close by pressing ESC
        $close_popup_esc = (isset($_POST['close_popup_esc']) && $_POST['close_popup_esc'] == 'on') ? 'on' : 'off';

        // Close by clicking outside the box
        $close_popup_overlay = (isset($_POST['close_popup_overlay']) && $_POST['close_popup_overlay'] == 'on') ? stripslashes( sanitize_text_field($_POST['close_popup_overlay']) ) : 'off';

        // Close by clicking outside the box mobile
        $close_popup_overlay_mobile = (isset($_POST['close_popup_overlay_mobile']) && $_POST['close_popup_overlay_mobile'] == 'on') ? stripslashes( sanitize_text_field($_POST['close_popup_overlay_mobile']) ) : 'off';

        // Hide close button
        $closeButton = (isset($_POST['ays-pb']['close_button']) && $_POST['ays-pb']['close_button'] == 'on') ? 'on' : 'off';

        // Activate close button while hovering on popup
        $ays_pb_hover_show_close_btn = (isset($_POST['ays_pb_show_close_btn_hover_container']) && $_POST['ays_pb_show_close_btn_hover_container'] == 'on') ? 'on' : 'off';

        // Close button position
        $close_button_position = (isset($_POST['ays_pb_close_button_position']) && $_POST['ays_pb_close_button_position'] != '') ? stripslashes( sanitize_text_field($_POST['ays_pb_close_button_position']) ) : 'right-top';

        // Enable different close button position for mobile
        $enable_close_button_position_mobile = (isset($_POST['ays_pb_enable_close_button_position_mobile']) && $_POST['ays_pb_enable_close_button_position_mobile'] == 'on') ? 'on' : 'off';

        // Close button position mobile
        $close_button_position_mobile = (isset($_POST['ays_pb_close_button_position_mobile']) && $_POST['ays_pb_close_button_position_mobile'] != '') ? stripslashes( sanitize_text_field($_POST['ays_pb_close_button_position_mobile']) ) : 'right-top';

        // Close button text
        $close_button_text = (isset($_POST['ays_pb_close_button_text']) && $_POST['ays_pb_close_button_text'] != '') ? sanitize_text_field($_POST['ays_pb_close_button_text']) : '✕';

        // Enable different close button text for mobile
        $enable_close_button_text_mobile = (isset($_POST['ays_pb_enable_close_button_text_mobile']) && $_POST['ays_pb_enable_close_button_text_mobile'] == 'on') ? 'on' : 'off';

        // Close button text mobile
        $close_button_text_mobile = (isset($_POST['ays_pb_close_button_text_mobile']) && $_POST['ays_pb_close_button_text_mobile'] != '') ? sanitize_text_field($_POST['ays_pb_close_button_text_mobile']) : '✕';

        // Close button hover text
        $close_button_hover_text = (isset($_POST['ays_pb_close_button_hover_text']) && $_POST['ays_pb_close_button_hover_text'] != '') ? sanitize_text_field($_POST['ays_pb_close_button_hover_text']) : '';

        // Autoclose delay (in seconds)
		$autoclose = (isset($_POST['ays-pb']['autoclose']) && $_POST['ays-pb']['autoclose'] != '') ? absint( intval($_POST['ays-pb']['autoclose']) ) : '';

        // Enable different autoclose delay mobile
        $enable_autoclose_delay_text_mobile = (isset($_POST['ays_pb_enable_autoclose_delay_text_mobile']) && $_POST['ays_pb_enable_autoclose_delay_text_mobile'] == 'on') ? 'on' : 'off';

        // Autoclose delay mobile (in seconds)
		$autoclose_mobile = (isset($_POST['ays_pb_autoclose_mobile']) && $_POST['ays_pb_autoclose_mobile'] != '') ? sanitize_text_field($_POST['ays_pb_autoclose_mobile'])  : '';

        // Hide timer
        $enable_hide_timer = (isset($_POST['ays_pb_hide_timer']) && $_POST['ays_pb_hide_timer'] == 'on') ? 'on' : 'off';

        // Hide timer mobile
        $enable_hide_timer_mobile = (isset($_POST['ays_pb_hide_timer_mobile']) && $_POST['ays_pb_hide_timer_mobile'] == 'on') ? 'on' : 'off';

        // Autoclose on video completion
        $enable_autoclose_on_completion = (isset($_POST['ays_pb_autoclose_on_completion']) && $_POST['ays_pb_autoclose_on_completion'] == 'on') ? 'on' : 'off';

        // Close button delay
        $close_button_delay = (isset($_POST['ays_pb_close_button_delay']) && $_POST['ays_pb_close_button_delay'] != '') ? abs( intval($_POST['ays_pb_close_button_delay']) ) : '';

        // Enable different close button delay mobile
        $enable_close_button_delay_for_mobile = (isset($_POST['ays_pb_enable_close_button_delay_for_mobile']) && $_POST['ays_pb_enable_close_button_delay_for_mobile'] == 'on') ? 'on' : 'off';

        // Close button delay mobile
        $close_button_delay_for_mobile = (isset($_POST['ays_pb_close_button_delay_for_mobile']) && $_POST['ays_pb_close_button_delay_for_mobile'] != '') ? abs( intval($_POST['ays_pb_close_button_delay_for_mobile']) ) : '';

        // Popup name
        $popup_name = (isset($_POST['ays_pb_popup_name']) && $_POST['ays_pb_popup_name'] != '') ? sanitize_text_field($_POST['ays_pb_popup_name']) : '';

        // Popup category
        $popup_category_id = (isset($_POST['ays_popup_category']) && $_POST['ays_popup_category'] != '') ? absint( sanitize_text_field($_POST['ays_popup_category']) ) : null;

        // Enable overlay
        $switchoverlay = (isset($_POST['ays-pb']['onoffoverlay']) && $_POST['ays-pb']['onoffoverlay'] == 'on') ? 'On' : 'Off';

        // Enable overlay | Opacity
        $overlay_opacity = ($switchoverlay == 'On') && isset($_POST['ays-pb']['overlay_opacity']) ? stripslashes( sanitize_text_field($_POST['ays-pb']['overlay_opacity']) ) : '0.5'; 

        // Enable overlay | Enable different opacity for mobile
        $enable_overlay_text_mobile = (isset($_POST['ays_pb_enable_overlay_text_mobile']) && $_POST['ays_pb_enable_overlay_text_mobile'] == 'on') ? 'on' : 'off';

        // Enable overlay | Opacity mobile
        $overlay_mobile_opacity = ($switchoverlay == 'On') && isset($_POST['ays_pb_overlay_mobile_opacity']) ? stripslashes( sanitize_text_field($_POST['ays_pb_overlay_mobile_opacity']) ) : '0.5';

        // Blured overlay
        $blured_overlay = (isset($_POST['ays_pb_blured_overlay']) && $_POST['ays_pb_blured_overlay'] != '') ? 'on' : 'off';

        // Blured overlay mobile
        $blured_overlay_mobile = (isset($_POST['ays_pb_blured_overlay_mobile']) && $_POST['ays_pb_blured_overlay_mobile'] != '') ? 'on' : 'off';

        // Enable popup sound
        $enable_pb_sound = (isset($_POST['ays_pb_enable_sounds']) && $_POST['ays_pb_enable_sounds'] == 'on') ? 'on' : 'off';

        // Enable social media links
        $enable_social_links = (isset($_POST['ays_pb_enable_social_links']) && $_POST['ays_pb_enable_social_links'] == 'on') ? 'on' : 'off';

        // Enable social media links | Heading for share buttons
        $social_buttons_heading = (isset($_POST['ays_pb_social_buttons_heading']) && $_POST['ays_pb_social_buttons_heading'] != '') ? stripslashes($_POST['ays_pb_social_buttons_heading']) : '';

        // Enable social media links | Social media link buttons
        $ays_social_links = (isset($_POST['ays_social_links'])) ? array_map( 'sanitize_text_field', $_POST['ays_social_links'] ) : $social_links_default;

        // Enable social media links | LinkedIn link
        $linkedin_link = (isset($ays_social_links['ays_pb_linkedin_link']) && $ays_social_links['ays_pb_linkedin_link'] != '') ? sanitize_text_field($ays_social_links['ays_pb_linkedin_link']) : '';

        // Enable social media links | Facebook link
        $facebook_link = (isset($ays_social_links['ays_pb_facebook_link']) && $ays_social_links['ays_pb_facebook_link'] != '') ? sanitize_text_field($ays_social_links['ays_pb_facebook_link']) : '';

        // Enable social media links | X link
        $twitter_link = (isset($ays_social_links['ays_pb_twitter_link']) && $ays_social_links['ays_pb_twitter_link'] != '') ? sanitize_text_field($ays_social_links['ays_pb_twitter_link']) : '';

        // Enable social media links | VKontakte link
        $vkontakte_link = (isset($ays_social_links['ays_pb_vkontakte_link']) && $ays_social_links['ays_pb_vkontakte_link'] != '') ? sanitize_text_field($ays_social_links['ays_pb_vkontakte_link']) : '';

        // Enable social media links | Youtube link
        $youtube_link = (isset($ays_social_links['ays_pb_youtube_link']) && $ays_social_links['ays_pb_youtube_link'] != '') ? sanitize_text_field($ays_social_links['ays_pb_youtube_link']) : '';

        // Enable social media links | Instagram link
        $instagram_link = (isset($ays_social_links['ays_pb_instagram_link']) && $ays_social_links['ays_pb_instagram_link'] != '') ? sanitize_text_field($ays_social_links['ays_pb_instagram_link']) : '';

        // Enable social media links | Behance link
        $behance_link = (isset($ays_social_links['ays_pb_behance_link']) && $ays_social_links['ays_pb_behance_link'] != '') ? sanitize_text_field($ays_social_links['ays_pb_behance_link']) : '';

        $social_links = array(
            'linkedin_link' => $linkedin_link,
            'facebook_link' => $facebook_link,
            'twitter_link' => $twitter_link,
            'vkontakte_link' => $vkontakte_link,
            'youtube_link' => $youtube_link,
            'instagram_link' => $instagram_link,
            'behance_link' => $behance_link,
        );

        // Schedule the popup
        $active_date_check = (isset($_POST['active_date_check']) && $_POST['active_date_check'] == 'on') ? 'on' : 'off';

        // Schedule the popup | Start date
        $activeInterval = (isset($_POST['ays-active']) && $_POST['ays-active'] != '') ? sanitize_text_field($_POST['ays-active']) : '';

        // Schedule the popup | End date
        $deactiveInterval = (isset($_POST['ays-deactive']) && $_POST['ays-deactive'] != '') ? sanitize_text_field($_POST['ays-deactive']) : '';
        
        //Schedule the popup by time
        $active_time_check = (isset($_POST['active_time_check']) && $_POST['active_time_check'] == 'on') ? 'on' : 'off';

        // Schedule the popup | Start time
        $active_time_start = (isset($_POST['ays-active-time']) && $_POST['ays-active-time'] != '') ? sanitize_text_field($_POST['ays-active-time']) : '';

        // Schedule the popup | End time
        $active_time_end = (isset($_POST['ays-deactive-time']) && $_POST['ays-deactive-time'] != '') ? sanitize_text_field($_POST['ays-deactive-time']) : '';

        // Change the popup creation date
        $pb_create_date = (isset($_POST['ays_pb_change_creation_date']) && $_POST['ays_pb_change_creation_date'] != '') ? sanitize_text_field($_POST['ays_pb_change_creation_date']) : current_time('mysql');

        // Change the popup author
        $pb_create_author = (isset($_POST['ays_pb_create_author']) && $_POST['ays_pb_create_author'] != '') ? absint( sanitize_text_field($_POST['ays_pb_create_author']) ) : '';
        if ($pb_create_author != '' && $pb_create_author > 0) {
            $user = get_userdata($pb_create_author);

            if (!is_null($user) && $user) {
                $pb_author = array(
                    'id' => $user->ID."",
                    'name' => $user->data->display_name
                );

                $author = json_encode($pb_author, JSON_UNESCAPED_SLASHES);
            } else {
                $author_data = json_decode($author, true);
                $pb_create_author = (isset($author_data['id']) && $author_data['id'] != '') ? absint( sanitize_text_field($author_data['id']) ) : get_current_user_id();
            }
        }

        // Enable dismiss ad
        $enable_dismiss = (isset($_POST['ays_pb_enable_dismiss']) && $_POST['ays_pb_enable_dismiss'] != '') ? 'on' : 'off';

        // Enable dismiss ad | Dismiss ad text
        $enable_dismiss_text = (isset($_POST['ays_pb_enable_dismiss_text']) && $_POST['ays_pb_enable_dismiss_text'] != '') ? stripslashes( sanitize_text_field($_POST['ays_pb_enable_dismiss_text']) ) : 'Dismiss ad';

        // Enable dismiss ad | Enable different dismiss ad text mobile
        $enable_dismiss_mobile = (isset($_POST['ays_pb_enable_dismiss_mobile']) && $_POST['ays_pb_enable_dismiss_mobile'] != '') ? 'on' : 'off';

        // Enable dismiss ad | Dismiss ad text mobile
        $enable_dismiss_text_mobile = (isset($_POST['ays_pb_enable_dismiss_text_mobile']) && $_POST['ays_pb_enable_dismiss_text_mobile'] != '') ? stripslashes( sanitize_text_field($_POST['ays_pb_enable_dismiss_text_mobile']) ) : 'Dismiss ad';

        // Disable page scrolling
        $disable_scroll = (isset($_POST['disable_scroll']) && $_POST['disable_scroll'] == 'on') ? 'on' : 'off';

        // Disable page scrolling mobile
        $disable_scroll_mobile = (isset($_POST['disable_scroll_mobile']) && $_POST['disable_scroll_mobile'] == 'on') ? 'on' : 'off';

        // Disable popup scrolling
        $disable_scroll_on_popup = (isset($_POST['ays_pb_disable_scroll_on_popup']) && $_POST['ays_pb_disable_scroll_on_popup'] != '') ? 'on' : 'off';

        // Disable popup scrolling mobile
        $disable_scroll_on_popup_mobile = (isset($_POST['ays_pb_disable_scroll_on_popup_mobile']) && $_POST['ays_pb_disable_scroll_on_popup_mobile'] != '') ? 'on' : 'off';

        // Show scrollbar
        $show_scrollbar = (isset($_POST['ays_pb_show_scrollbar']) && $_POST['ays_pb_show_scrollbar'] != '') ? 'on' : 'off';

        // Template
		$view_type = (isset($_POST['ays-pb']['view_type']) && $_POST['ays-pb']['view_type'] != '') ? wp_unslash( sanitize_text_field($_POST['ays-pb']['view_type']) ) : '';

        // Display Content | Show title
        $showPopupTitle = (isset($_POST['show_popup_title']) && $_POST['show_popup_title'] == 'on') ? 'On' : 'Off';

        // Display Content | Show desctiption
        $showPopupDesc = (isset($_POST['show_popup_desc']) && $_POST['show_popup_desc'] == 'on') ? 'On' : 'Off';

        // Enable different display content mobile
        $enable_display_content_mobile = (isset($_POST['ays_pb_enable_display_content_mobile']) && $_POST['ays_pb_enable_display_content_mobile'] == 'on') ? 'on' : 'off';

        // Display Content | Show title mobile
        $show_popup_title_mobile = (isset($_POST['show_popup_title_mobile']) && $_POST['show_popup_title_mobile'] == 'on') ? 'On' : 'Off';

        // Display Content | Show description mobile
        $show_popup_desc_mobile = (isset($_POST['show_popup_desc_mobile']) && $_POST['show_popup_desc_mobile'] == 'on') ? 'On' : 'Off';

        // Width | On desktop
		$width = (isset($_POST['ays-pb']['width']) && $_POST['ays-pb']['width'] != '') ? absint( intval($_POST['ays-pb']['width']) ) : '';

        // Width | On desktop | Measurement unit
        $popup_width_by_percentage_px = (isset($_POST['ays_popup_width_by_percentage_px']) && $_POST['ays_popup_width_by_percentage_px'] != '') ? stripslashes( sanitize_text_field($_POST['ays_popup_width_by_percentage_px']) ) : 'pixels';

        // Width | On mobile
        $mobile_width = (isset($_POST['ays_pb_mobile_width']) && $_POST['ays_pb_mobile_width'] != '') ? abs( intval($_POST['ays_pb_mobile_width']) )  : '';

        // Width | On mobile | Measurement unit
        $popup_width_by_percentage_px_mobile = (isset($_POST['ays_popup_width_by_percentage_px_mobile']) && $_POST['ays_popup_width_by_percentage_px_mobile'] != '') ? stripslashes( sanitize_text_field($_POST['ays_popup_width_by_percentage_px_mobile']) ) : 'percentage';

        // Max-width for mobile
        $mobile_max_width = (isset($_POST['ays_pb_mobile_max_width']) && $_POST['ays_pb_mobile_max_width'] != '') ? abs(intval($_POST['ays_pb_mobile_max_width'])) : '';

        // Height | On desktop
        $default_height = $view_type == 'notification' ? 100 : 500;
		$height = (isset($_POST['ays-pb']['height']) && $_POST['ays-pb']['height']) ? absint( intval($_POST['ays-pb']['height']) ) : $default_height;

        // Height | On mobile
        $mobile_height = (isset($_POST['ays_pb_mobile_height']) && $_POST['ays_pb_mobile_height'] != '') ? abs( intval($_POST['ays_pb_mobile_height']) ) : '';

        // Popup max-height | On desktop
        $pb_max_height = (isset($_POST['ays_pb_max_height']) && $_POST['ays_pb_max_height'] != '') ? absint( intval($_POST['ays_pb_max_height']) ) : '';

        // Popup max-height | On desktop | Measurement unit
        $popup_max_height_by_percentage_px = (isset($_POST['ays_popup_max_height_by_percentage_px']) && $_POST['ays_popup_max_height_by_percentage_px'] != '') ? stripslashes( sanitize_text_field($_POST['ays_popup_max_height_by_percentage_px']) ) : 'pixels';

        // Popup max-height | On mobile
        $pb_max_height_mobile = (isset($_POST['ays_pb_max_height_mobile']) && $_POST['ays_pb_max_height_mobile'] != '') ? absint( intval($_POST['ays_pb_max_height_mobile']) ) : '';

        // Popup max-height | On mobile | Measurement unit
        $popup_max_height_by_percentage_px_mobile = (isset($_POST['ays_popup_max_height_by_percentage_px_mobile']) && $_POST['ays_popup_max_height_by_percentage_px_mobile'] != '') ? stripslashes( sanitize_text_field($_POST['ays_popup_max_height_by_percentage_px_mobile']) ) : 'pixels';

        // Popup min-height
        $pb_min_height = (isset($_POST['ays_pb_min_height']) && $_POST['ays_pb_min_height'] != '') ? absint( intval($_POST['ays_pb_min_height']) ) : '';

        // Full-screen mode
        $enable_pb_fullscreen = (isset($_POST['enable_pb_fullscreen']) && $_POST['enable_pb_fullscreen'] == 'on') ? 'on' : 'off';

        // Content padding
        $default_padding = ($view_type == "minimal" || $modal_content == 'image_type') ? 0 : 20;
        $padding = (isset($_POST['ays_popup_content_padding']) && $_POST['ays_popup_content_padding'] != '') ? absint( intval($_POST['ays_popup_content_padding']) ) : $default_padding;
        $padding_mobile = (isset($_POST['ays_popup_content_padding_mobile']) && $_POST['ays_popup_content_padding_mobile'] != '') ? absint( intval($_POST['ays_popup_content_padding_mobile']) ) : $default_padding;

        // Content padding | Measurement unit
        $popup_padding_by_percentage_px = (isset($_POST['ays_popup_padding_by_percentage_px']) && $_POST['ays_popup_padding_by_percentage_px'] != '') ? stripslashes( sanitize_text_field($_POST['ays_popup_padding_by_percentage_px']) ) : 'pixels';
        // Content padding | Measurement unit mobile
        $popup_padding_by_percentage_px_mobile = (isset($_POST['ays_popup_padding_by_percentage_px_mobile']) && $_POST['ays_popup_padding_by_percentage_px_mobile'] != '') ? stripslashes( sanitize_text_field($_POST['ays_popup_padding_by_percentage_px_mobile']) ) : 'pixels';
        //Enable Padding mobile
        $enable_padding_mobile = ( isset($_POST['ays_pb_enable_padding_mobile']) && $_POST['ays_pb_enable_padding_mobile'] == 'on' ) ? 'on' : 'off';

        // Text color
		$textcolor = (isset($_POST['ays-pb']['ays_pb_textcolor']) && $_POST['ays-pb']['ays_pb_textcolor'] != '') ? wp_unslash( sanitize_text_field($_POST['ays-pb']['ays_pb_textcolor']) ) : '#000000';

        // Font family
        $pb_font_family = (isset($_POST['ays_pb_font_family']) && $_POST['ays_pb_font_family'] != '') ? stripslashes( sanitize_text_field($_POST['ays_pb_font_family']) ) : 'inherit';

        // Description font size | On desktop
        $pb_font_size = (isset($_POST['ays_pb_font_size']) && $_POST['ays_pb_font_size'] != '') ? absint($_POST['ays_pb_font_size']) : 16;

        // Description font size | On mobile
        $pb_font_size_for_mobile = (isset($_POST['ays_pb_font_size_for_mobile']) && $_POST['ays_pb_font_size_for_mobile'] != '') ? absint($_POST['ays_pb_font_size_for_mobile']) : 16;

        // Title text shadow | On desktop
        $enable_pb_title_text_shadow = (isset($_POST['ays_enable_title_text_shadow']) && $_POST['ays_enable_title_text_shadow'] != '') ? 'on' : 'off';

        // Title text shadow | On desktop | Color
        $pb_title_text_shadow = (isset($_POST['ays_title_text_shadow_color']) && $_POST['ays_title_text_shadow_color'] != '') ? sanitize_text_field($_POST['ays_title_text_shadow_color']) : 'rgba(255,255,255,0)';

        // Title text shadow | On desktop | X
        $pb_title_text_shadow_x_offset = (isset($_POST['ays_pb_title_text_shadow_x_offset']) && $_POST['ays_pb_title_text_shadow_x_offset'] != '') ? intval($_POST['ays_pb_title_text_shadow_x_offset']) : 2;

        // Title text shadow | On desktop | Y
        $pb_title_text_shadow_y_offset = (isset($_POST['ays_pb_title_text_shadow_y_offset']) && $_POST['ays_pb_title_text_shadow_y_offset'] != '') ? intval($_POST['ays_pb_title_text_shadow_y_offset']) : 2;

        // Title text shadow | On desktop | Z
        $pb_title_text_shadow_z_offset = (isset($_POST['ays_pb_title_text_shadow_z_offset']) && $_POST['ays_pb_title_text_shadow_z_offset'] != '') ? intval($_POST['ays_pb_title_text_shadow_z_offset']) : 0;

        // Title text shadow | On mobile
        $enable_pb_title_text_shadow_mobile = (isset($_POST['ays_enable_title_text_shadow_mobile']) && $_POST['ays_enable_title_text_shadow_mobile'] != '') ? 'on' : 'off';

        // Title text shadow | On mobile | Color
        $pb_title_text_shadow_mobile = (isset($_POST['ays_title_text_shadow_color_mobile']) && $_POST['ays_title_text_shadow_color_mobile'] != '') ? sanitize_text_field($_POST['ays_title_text_shadow_color_mobile']) : 'rgba(255,255,255,0)';
        
        // Title text shadow | On mobile | X
        $pb_title_text_shadow_x_offset_mobile = (isset($_POST['ays_pb_title_text_shadow_x_offset_mobile']) && $_POST['ays_pb_title_text_shadow_x_offset_mobile'] != '') ? intval($_POST['ays_pb_title_text_shadow_x_offset_mobile']) : 2;

        // Title text shadow | On mobile | Y
        $pb_title_text_shadow_y_offset_mobile = (isset($_POST['ays_pb_title_text_shadow_y_offset_mobile']) && $_POST['ays_pb_title_text_shadow_y_offset_mobile'] != '') ? intval($_POST['ays_pb_title_text_shadow_y_offset_mobile']) : 2;

        // Title text shadow | On mobile | Z
        $pb_title_text_shadow_z_offset_mobile = (isset($_POST['ays_pb_title_text_shadow_z_offset_mobile']) && $_POST['ays_pb_title_text_shadow_z_offset_mobile'] != '') ? intval($_POST['ays_pb_title_text_shadow_z_offset_mobile']) : 0;
        
        //Show once per session
		$cookie = ( isset( $_POST['ays-pb']["cookie"] ) && $_POST['ays-pb']["cookie"] != '' ) ? absint( intval( $_POST['ays-pb']["cookie"] ) ) : 0;

        //Background Color
		$bgcolor = ( isset( $_POST['ays-pb']["bgcolor"] ) && $_POST['ays-pb']["bgcolor"] != '' ) ? wp_unslash(sanitize_text_field( $_POST['ays-pb']["bgcolor"] )) : '#FFFFFF';

        //Enable Background Color Mobile
        $enable_bgcolor_mobile = ( isset($_POST['ays_pb_enable_bgcolor_mobile']) && $_POST['ays_pb_enable_bgcolor_mobile'] == 'on' ) ? 'on' : 'off';

        //Background Color Mobile
        $bgcolor_mobile = ( isset($_POST['ays_pb_bgcolor_mobile']) && $_POST['ays_pb_bgcolor_mobile'] != '' ) ? wp_unslash( sanitize_text_field($_POST['ays_pb_bgcolor_mobile']) ) : '#FFFFFF';

        //Border Size
        $default_bordersize = $view_type == 'notification' ? 0 : 1;
		$bordersize = ( isset( $_POST['ays-pb']["ays_pb_bordersize"] ) && $_POST['ays-pb']["ays_pb_bordersize"] != '' ) ? wp_unslash(sanitize_text_field(intval(round( $_POST['ays-pb']["ays_pb_bordersize"] )))) : $default_bordersize;

        //Enable Border Size Mobile
        $enable_bordersize_mobile = ( isset($_POST['ays_pb_enable_bordersize_mobile']) && $_POST['ays_pb_enable_bordersize_mobile'] == 'on' ) ? 'on' : 'off';

        //Border Size Mobile
        $bordersize_mobile = ( isset($_POST['ays_pb_bordersize_mobile']) && $_POST['ays_pb_bordersize_mobile'] != '' ) ? wp_unslash(sanitize_text_field(intval(round( $_POST['ays_pb_bordersize_mobile'] )))) : $default_bordersize;

        //Border Color
		$bordercolor = ( isset( $_POST['ays-pb']["ays_pb_bordercolor"] ) && $_POST['ays-pb']["ays_pb_bordercolor"] != '' ) ? wp_unslash(sanitize_text_field( $_POST['ays-pb']["ays_pb_bordercolor"] )) : '#ffffff';

        //Enable Border Color Mobile
        $enable_bordercolor_mobile = ( isset($_POST['ays_pb_enable_bordercolor_mobile']) && $_POST['ays_pb_enable_bordercolor_mobile'] == 'on' ) ? 'on' : 'off';

        //Border Color Mobile
        $bordercolor_mobile = ( isset($_POST['ays_pb_bordercolor_mobile']) && $_POST['ays_pb_bordercolor_mobile'] != '' ) ? wp_unslash( sanitize_text_field($_POST['ays_pb_bordercolor_mobile']) ) : '#ffffff';

        //Border Radius
        $default_border_radius = $view_type == 'notification' ? 0 : 4;
		$border_radius = ( isset( $_POST['ays-pb']["ays_pb_border_radius"] ) && $_POST['ays-pb']["ays_pb_border_radius"] != '' ) ? wp_unslash(sanitize_text_field(intval(round( $_POST['ays-pb']["ays_pb_border_radius"] )))) : $default_border_radius;

        //Enable Border Radius Mobile
        $enable_border_radius_mobile = ( isset($_POST['ays_pb_enable_border_radius_mobile']) && $_POST['ays_pb_enable_border_radius_mobile'] == 'on' ) ? 'on' : 'off';

        //Border Radius Mobile
        $border_radius_mobile = ( isset($_POST['ays_pb_border_radius_mobile']) && $_POST['ays_pb_border_radius_mobile'] != '' ) ? wp_unslash( sanitize_text_field(intval(round( $_POST['ays_pb_border_radius_mobile'] )))) : $border_radius;

        //Custom Class
		$custom_css = ( isset( $_POST['ays-pb']["custom-css"] ) && $_POST['ays-pb']["custom-css"] != '' ) ? wp_unslash(stripslashes( esc_attr( $_POST['ays-pb']["custom-css"] ) ) ) : '';

        //Animate In
		$animate_in = ( isset( $_POST['ays-pb']["animate_in"] ) && $_POST['ays-pb']["animate_in"] != '' ) ? wp_unslash(sanitize_text_field( $_POST['ays-pb']["animate_in"] )) : '';

        //Enable Different Opening Animation Mobile
        $enable_animate_in_mobile = ( isset($_POST['ays_pb_enable_animate_in_mobile']) && $_POST['ays_pb_enable_animate_in_mobile'] == 'on' ) ? 'on' : 'off';

        //Animate In Mobile
        $animate_in_mobile = ( isset($_POST['ays_pb_animate_in_mobile']) && $_POST['ays_pb_animate_in_mobile'] != '' ) ? wp_unslash( sanitize_text_field($_POST['ays_pb_animate_in_mobile']) ) : 0;

        //Animate Out
		$animate_out = ( isset( $_POST['ays-pb']["animate_out"] ) && $_POST['ays-pb']["animate_out"] != '' ) ? wp_unslash(sanitize_text_field( $_POST['ays-pb']["animate_out"] )) : '';

        //Enable Different Closing Animation Mobile
        $enable_animate_out_mobile = ( isset($_POST['ays_pb_enable_animate_out_mobile']) && $_POST['ays_pb_enable_animate_out_mobile'] == 'on' ) ? 'on' : 'off';

        //Animate Out Mobile
        $animate_out_mobile = ( isset($_POST['ays_pb_animate_out_mobile']) && $_POST['ays_pb_animate_out_mobile'] != '' ) ? wp_unslash( sanitize_text_field($_POST['ays_pb_animate_out_mobile']) ) : 0;

        //Header BgColor
        $header_bgcolor = ( isset( $_POST['ays-pb']["header_bgcolor"] ) && $_POST['ays-pb']["header_bgcolor"] != '' ) ? wp_unslash(sanitize_text_field( $_POST['ays-pb']["header_bgcolor"] )) : '#ffffff';
        //Header BgColor mobile
        $header_bgcolor_mobile = ( isset( $_POST['ays-pb']["header_bgcolor_mobile"] ) && $_POST['ays-pb']["header_bgcolor_mobile"] != '' ) ? wp_unslash(sanitize_text_field( $_POST['ays-pb']["header_bgcolor_mobile"] )) : '#ffffff';

        // Background Image
        $bg_image = ( isset( $_POST['ays_pb_bg_image'] ) && $_POST['ays_pb_bg_image'] != '' ) ? sanitize_url( $_POST['ays_pb_bg_image'] ) : '';

        // Enable Different Background Image Mobile
        $enable_bg_image_mobile = ( isset($_POST['ays_pb_enable_bg_image_mobile']) && $_POST['ays_pb_enable_bg_image_mobile'] == 'on' ) ? 'on' : 'off';

        // Background Image Mobile
        $bg_image_mobile = ( isset( $_POST['ays_pb_bg_image_mobile'] ) && $_POST['ays_pb_bg_image_mobile'] != '' ) ? sanitize_url( $_POST['ays_pb_bg_image_mobile'] ) : '';

        // Background Image Position
        $pb_bg_image_position = (isset($_POST['ays_pb_bg_image_position']) && $_POST['ays_pb_bg_image_position'] != "") ? stripslashes( sanitize_text_field($_POST['ays_pb_bg_image_position']) ) : 'center center';

        // Enable Different Background Image Position Mobile
        $enable_pb_bg_image_position_mobile = ( isset($_POST['ays_pb_enable_bg_image_position_mobile']) && $_POST['ays_pb_enable_bg_image_position_mobile'] == 'on' ) ? 'on' : 'off';

        // Background Image Position Mobile
        $pb_bg_image_position_mobile = (isset($_POST['ays_pb_bg_image_position_mobile']) && $_POST['ays_pb_bg_image_position_mobile'] != "") ? stripslashes( sanitize_text_field($_POST['ays_pb_bg_image_position_mobile']) ) : 'center center';

        // Background Image Sizing
        $pb_bg_image_sizing = (isset($_POST['ays_pb_bg_image_sizing']) && $_POST['ays_pb_bg_image_sizing'] != "") ? stripslashes( sanitize_text_field($_POST['ays_pb_bg_image_sizing']) ) : 'cover';

        // Enable Different Background Image Sizing Mobile
        $enable_pb_bg_image_sizing_mobile = ( isset($_POST['ays_pb_enable_bg_image_sizing_mobile']) && $_POST['ays_pb_enable_bg_image_sizing_mobile'] == 'on' ) ? 'on' : 'off';

        // Background Image Sizing Mobile
        $pb_bg_image_sizing_mobile = (isset($_POST['ays_pb_bg_image_sizing_mobile']) && $_POST['ays_pb_bg_image_sizing_mobile'] != "") ? stripslashes( sanitize_text_field($_POST['ays_pb_bg_image_sizing_mobile']) ) : 'cover';

        // Custom class for quiz container
        $custom_class = (isset($_POST['ays-pb']["custom-class"]) && $_POST['ays-pb']["custom-class"] != "") ? stripslashes( sanitize_text_field($_POST['ays-pb']["custom-class"]) ) : '';
        $users_role = (isset($_POST['ays-pb']["ays_users_roles"]) && !empty($_POST['ays-pb']["ays_users_roles"])) ? $_POST['ays-pb']["ays_users_roles"] : array();

        // Background gradient
        $enable_background_gradient = ( isset( $_POST['ays_enable_background_gradient'] ) && $_POST['ays_enable_background_gradient'] == 'on' ) ? 'on' : 'off';
        $pb_background_gradient_color_1 = !isset($_POST['ays_background_gradient_color_1']) ? '' : stripslashes(sanitize_text_field($_POST['ays_background_gradient_color_1'] ));
        $pb_background_gradient_color_2 = !isset($_POST['ays_background_gradient_color_2']) ? '' : stripslashes(sanitize_text_field( $_POST['ays_background_gradient_color_2'] ));
        $pb_gradient_direction = !isset($_POST['ays_pb_gradient_direction']) ? '' : stripslashes( sanitize_text_field($_POST['ays_pb_gradient_direction']) );

        // Background gradient mobile
        $enable_background_gradient_mobile = ( isset( $_POST['ays_enable_background_gradient_mobile'] ) && $_POST['ays_enable_background_gradient_mobile'] == 'on' ) ? 'on' : 'off';
        $pb_background_gradient_color_1_mobile = !isset($_POST['ays_background_gradient_color_1_mobile']) ? '' : stripslashes(sanitize_text_field($_POST['ays_background_gradient_color_1_mobile'] ));
        $pb_background_gradient_color_2_mobile = !isset($_POST['ays_background_gradient_color_2_mobile']) ? '' : stripslashes(sanitize_text_field( $_POST['ays_background_gradient_color_2_mobile'] ));
        $pb_gradient_direction_mobile = !isset($_POST['ays_pb_gradient_direction_mobile']) ? '' : stripslashes( sanitize_text_field($_POST['ays_pb_gradient_direction_mobile']) );

        //Overlay Color
        $overlay_color = (isset($_POST['ays_pb_overlay_color']) && $_POST['ays_pb_overlay_color'] != '') ? stripslashes(sanitize_text_field( $_POST['ays_pb_overlay_color'] )) : '#000';

        //Enable Overlay Color mobile
        $enable_overlay_color_mobile =  ( isset($_POST['ays_pb_enable_overlay_color_mobile']) && $_POST['ays_pb_enable_overlay_color_mobile'] == "on" ) ? 'on' : 'off';

        //Overlay Color mobile
        $overlay_color_mobile = ( isset($_POST['ays_pb_overlay_color_mobile']) && $_POST['ays_pb_overlay_color_mobile'] !== '' ) ? stripslashes( sanitize_text_field($_POST['ays_pb_overlay_color_mobile']) ) : '#000';

        //Animation speed
        $animation_speed = (isset($_POST['ays_pb_animation_speed']) && $_POST['ays_pb_animation_speed'] !== '') ? abs($_POST['ays_pb_animation_speed']) : 1;

        //Enable animation speed mobile
        $enable_animation_speed_mobile =  ( isset($_POST['ays_pb_enable_animation_speed_mobile']) && $_POST['ays_pb_enable_animation_speed_mobile'] == "on" ) ? 'on' : 'off';

        //Animation speed mobile
        $animation_speed_mobile = ( isset($_POST['ays_pb_animation_speed_mobile']) && $_POST['ays_pb_animation_speed_mobile'] !== '' ) ? abs($_POST['ays_pb_animation_speed_mobile']) : 1;
        
        // Close Animation speed
        $close_animation_speed = (isset($_POST['ays_pb_close_animation_speed']) && $_POST['ays_pb_close_animation_speed'] !== '') ? abs($_POST['ays_pb_close_animation_speed']) : 1;

        //Enable close animation speed mobile
        $enable_close_animation_speed_mobile =  ( isset($_POST['ays_pb_enable_close_animation_speed_mobile']) && $_POST['ays_pb_enable_close_animation_speed_mobile'] == "on" ) ? 'on' : 'off';

        //Close animation speed mobile
        $close_animation_speed_mobile = ( isset($_POST['ays_pb_close_animation_speed_mobile']) && $_POST['ays_pb_close_animation_speed_mobile'] !== '' ) ? abs($_POST['ays_pb_close_animation_speed_mobile']) : 1;

        //Hide popup on mobile
        $pb_mobile = (isset($_POST['ays_pb_mobile']) && $_POST['ays_pb_mobile'] == 'on') ? 'on' : 'off';

        //Show PopupBox only once
        $show_only_once = (isset($_POST['ays_pb_show_only_once']) && $_POST['ays_pb_show_only_once'] == 'on') ? 'on' : 'off';

        //close button_size
        $close_button_size = (isset($_POST['ays_pb_close_button_size']) && $_POST['ays_pb_close_button_size'] != '' ) ? abs(sanitize_text_field($_POST['ays_pb_close_button_size'])) : '';
       
        //close button image
        $close_button_image = (isset($_POST['ays_pb_close_btn_bg_img']) && $_POST['ays_pb_close_btn_bg_img'] != '' ) ? sanitize_url($_POST['ays_pb_close_btn_bg_img']) : '';

        //border style
        $border_style = (isset($_POST['ays_pb_border_style']) && $_POST['ays_pb_border_style'] != '' ) ? stripslashes( sanitize_text_field($_POST['ays_pb_border_style']) ) : '';
        
        //Enable border style mobile
        $enable_border_style_mobile =  ( isset($_POST['ays_pb_enable_border_style_mobile']) && $_POST['ays_pb_enable_border_style_mobile'] == "on" ) ? 'on' : 'off';

        //Border style mobile
        $border_style_mobile = ( isset($_POST['ays_pb_border_style_mobile']) && $_POST['ays_pb_border_style_mobile'] !== '' ) ? stripslashes( sanitize_text_field($_POST['ays_pb_border_style_mobile']) ) : '';

       // --------- Check & get post type-----------         
            $post_type_for_allfeld = array();
            if (isset($_POST['ays_pb_except_post_types'])) {
                $all_post_types = $_POST['ays_pb_except_post_types'];              
                if (isset($_POST["ays_pb_except_posts"])) {
                    foreach ($all_post_types as $post_type) {
                        $all_posts = get_posts( array(
                        'numberposts' => -1,            
                        'post_type'   => $post_type,
                        'suppress_filters' => true,
                        ));

                        if (!empty($all_posts)) {
                            foreach ($all_posts as $posts_value) {
                                if (in_array($posts_value->ID, $_POST["ays_pb_except_posts"])) {
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
     
        $log_user = (isset($_POST['ays-pb']["log_user"]) &&  $_POST['ays-pb']["log_user"] == 'on') ? 'On' : 'Off';
        $guest = (isset($_POST['ays-pb']["guest"]) &&  $_POST['ays-pb']["guest"] == 'on') ? 'On' : 'Off';

        if($show_all == 'yes'){
            $view_place = '';
        }else{
            $view_place = isset($_POST['ays-pb']["ays_pb_view_place"]) ? sanitize_text_field( implode( "***", $_POST['ays-pb']["ays_pb_view_place"] ) ) : '';
        }
        $JSON_user_role = json_encode($users_role);

        //Enabel Box Shadow
        $enable_box_shadow = ( isset( $_POST['ays_pb_enable_box_shadow'] ) && $_POST['ays_pb_enable_box_shadow'] == 'on' ) ? 'on' : 'off';

        //Enabel Box Shadow Mobile
        $enable_box_shadow_mobile = ( isset( $_POST['ays_pb_enable_box_shadow_mobile'] ) && $_POST['ays_pb_enable_box_shadow_mobile'] == 'on' ) ? 'on' : 'off';

        //Enabel Box Shadow Color
        $box_shadow_color = (!isset($_POST['ays_pb_box_shadow_color'])) ? '#000' : sanitize_text_field( stripslashes($_POST['ays_pb_box_shadow_color']) );

        //Enabel Box Shadow Color Mobile
        $box_shadow_color_mobile = ( isset($_POST['ays_pb_box_shadow_color_mobile']) && $_POST['ays_pb_box_shadow_color_mobile'] != '' ) ? sanitize_text_field( stripslashes($_POST['ays_pb_box_shadow_color_mobile']) ) : '#000';

        //Box Shadow X offset
        $pb_box_shadow_x_offset = (isset($_POST['ays_pb_box_shadow_x_offset']) && $_POST['ays_pb_box_shadow_x_offset'] != '' && intval( $_POST['ays_pb_box_shadow_x_offset'] ) != 0) ? intval( $_POST['ays_pb_box_shadow_x_offset'] ) : 0;

        //Box Shadow X offset Mobile
        $pb_box_shadow_x_offset_mobile = (isset($_POST['ays_pb_box_shadow_x_offset_mobile']) && $_POST['ays_pb_box_shadow_x_offset_mobile'] != '' && intval( $_POST['ays_pb_box_shadow_x_offset_mobile'] ) != 0) ? intval( $_POST['ays_pb_box_shadow_x_offset_mobile'] ) : 0;

        //Box Shadow Y offset
        $pb_box_shadow_y_offset = (isset($_POST['ays_pb_box_shadow_y_offset']) && $_POST['ays_pb_box_shadow_y_offset'] != '' && intval( $_POST['ays_pb_box_shadow_y_offset'] ) != 0) ? intval( $_POST['ays_pb_box_shadow_y_offset'] ) : 0;

        //Box Shadow Y offset Mobile
        $pb_box_shadow_y_offset_mobile = (isset($_POST['ays_pb_box_shadow_y_offset_mobile']) && $_POST['ays_pb_box_shadow_y_offset_mobile'] != '' && intval( $_POST['ays_pb_box_shadow_y_offset_mobile'] ) != 0) ? intval( $_POST['ays_pb_box_shadow_y_offset_mobile'] ) : 0;

        //Box Shadow Z offset
        $pb_box_shadow_z_offset = (isset($_POST['ays_pb_box_shadow_z_offset']) && $_POST['ays_pb_box_shadow_z_offset'] != '' && intval( $_POST['ays_pb_box_shadow_z_offset'] ) != 0) ? intval( $_POST['ays_pb_box_shadow_z_offset'] ) : 15;

        //Box Shadow Z offset Mobile
        $pb_box_shadow_z_offset_mobile = (isset($_POST['ays_pb_box_shadow_z_offset_mobile']) && $_POST['ays_pb_box_shadow_z_offset_mobile'] != '' && intval( $_POST['ays_pb_box_shadow_z_offset_mobile'] ) != 0) ? intval( $_POST['ays_pb_box_shadow_z_offset_mobile'] ) : 15;

        //Hide on desktop
        $hide_on_pc = ( isset( $_POST['ays_pb_hide_on_pc'] ) && $_POST['ays_pb_hide_on_pc'] == 'on' ) ? 'on' : 'off';

        //Hide on tablets
        $hide_on_tablets = ( isset( $_POST['ays_pb_hide_on_tablets'] ) && $_POST['ays_pb_hide_on_tablets'] == 'on' ) ? 'on' : 'off';

        //Background image position for mobile
        $pb_bg_image_direction_on_mobile = ( isset( $_POST['ays_pb_bg_image_direction_on_mobile'] ) && $_POST['ays_pb_bg_image_direction_on_mobile'] == 'on' ) ? 'on' : 'off';

        // Close button color
        $close_button_color = ( isset($_POST['ays_pb_close_button_color']) && $_POST['ays_pb_close_button_color'] != "" ) ? sanitize_text_field( $_POST['ays_pb_close_button_color'] ) : '#000000';

        // Close button hover color
        $close_button_hover_color = ( isset($_POST['ays_pb_close_button_hover_color']) && $_POST['ays_pb_close_button_hover_color'] != "" ) ? sanitize_text_field( $_POST['ays_pb_close_button_hover_color'] ) : '#000000';

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
            'popup_content_padding_mobile' => $padding_mobile,
            'popup_padding_by_percentage_px' => $popup_padding_by_percentage_px,
            'popup_padding_by_percentage_px_mobile' => $popup_padding_by_percentage_px_mobile,
            'enable_padding_mobile' => $enable_padding_mobile,
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
            'notification_logo_image' => $notification_logo_image,
            'notification_logo_redirect_url' => $notification_logo_redirect_url,
            'notification_logo_redirect_to_new_tab' => $notification_logo_redirect_to_new_tab,
            'notification_logo_width' => $notification_logo_width,
            'notification_logo_width_measurement_unit' => $notification_logo_width_measurement_unit,
            'notification_logo_width_mobile' => $notification_logo_width_mobile,
            'notification_logo_width_measurement_unit_mobile' => $notification_logo_width_measurement_unit_mobile,
            'notification_logo_max_width' => $notification_logo_max_width,
            'notification_logo_max_width_measurement_unit' => $notification_logo_max_width_measurement_unit,
            'notification_logo_max_width_mobile' => $notification_logo_max_width_mobile,
            'notification_logo_max_width_measurement_unit_mobile' => $notification_logo_max_width_measurement_unit_mobile,
            'notification_logo_min_width' => $notification_logo_min_width,
            'notification_logo_min_width_measurement_unit' => $notification_logo_min_width_measurement_unit,
            'notification_logo_min_width_mobile' => $notification_logo_min_width_mobile,
            'notification_logo_min_width_measurement_unit_mobile' => $notification_logo_min_width_measurement_unit_mobile,
            'notification_logo_max_height' => $notification_logo_max_height,
            'notification_logo_min_height' => $notification_logo_min_height,
            'notification_logo_image_sizing' => $notification_logo_image_sizing,
            'notification_logo_image_shape' => $notification_logo_image_shape,
            'notification_main_content' => $notification_main_content,
            'notification_button_1_text' => $notification_button_1_text,
            'notification_button_1_hover_text' => $notification_button_1_hover_text,
            'notification_button_1_redirect_url' => $notification_button_1_redirect_url,
            'notification_button_1_redirect_to_new_tab' => $notification_button_1_redirect_to_new_tab,
            'notification_button_1_bg_color' => $notification_button_1_bg_color,
            'notification_button_1_bg_hover_color' => $notification_button_1_bg_hover_color,
            'notification_button_1_text_color' => $notification_button_1_text_color,
            'notification_button_1_text_hover_color' => $notification_button_1_text_hover_color,
            'notification_button_1_text_transformation' => $notification_button_1_text_transformation,
            'notification_button_1_text_decoration' => $notification_button_1_text_decoration,
            'notification_button_1_letter_spacing' => $notification_button_1_letter_spacing,
            'notification_button_1_letter_spacing_mobile' => $notification_button_1_letter_spacing_mobile,
            'notification_button_1_font_size' => $notification_button_1_font_size,
            'notification_button_1_font_size_mobile' => $notification_button_1_font_size_mobile,
            'notification_button_1_font_weight' => $notification_button_1_font_weight,
            'notification_button_1_font_weight_mobile' => $notification_button_1_font_weight_mobile,
            'notification_button_1_border_radius' => $notification_button_1_border_radius,
            'notification_button_1_border_width' => $notification_button_1_border_width,
            'notification_button_1_border_color' => $notification_button_1_border_color,
            'notification_button_1_border_style' => $notification_button_1_border_style,
            'notification_button_1_padding_left_right' => $notification_button_1_padding_left_right,
            'notification_button_1_padding_top_bottom' => $notification_button_1_padding_top_bottom,
            'notification_button_1_transition' => $notification_button_1_transition,
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
            'header_bgcolor_mobile' => $header_bgcolor_mobile,
        );

        $submit_type = (isset($_POST['submit_type'])) ?  $_POST['submit_type'] : '';

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
                    'active_time_check'             => $active_time_check,
                    'active_time_start'             => $active_time_start,
                    'active_time_end'               => $active_time_end,
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
                '%s',   // active_time_check
                '%s',   // active_time_start
                '%s',   // active_time_end
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
                    'active_time_check'             => $active_time_check,
                    'active_time_start'             => $active_time_start,
                    'active_time_end'               => $active_time_end,
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
                '%s',   // active_time_check
                '%s',   // active_time_start
                '%s',   // active_time_end
                '%s',   // pb_position
                '%d',   // pb_margin
                '%s',   // users_roles
                '%s',   // options
            ),
				array( "%d" )
			);
			$message = "updated";
		}

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
}
