<?php
ob_start();
class Popup_Categories_List_Table extends WP_List_Table {
    private $plugin_name;
    private $title_length;

    /**
     * The wp nonce of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $ays_quiz_nonce
     */
    private $ays_pb_nonce;

    /** Class constructor */
    public function __construct($plugin_name) {
        $this->plugin_name = $plugin_name;
        $this->title_length = Ays_Pb_Admin::get_listtables_title_length('categories');

        parent::__construct( array(
            'singular' => esc_html__( 'Category', "ays-popup-box" ), // singular name of the listed records
            'plural' => esc_html__( 'Categories', "ays-popup-box" ), // plural name of the listed records
            'ajax' => false // does this table support ajax?
        ) );

        add_action( 'admin_notices', array($this, 'popup_category_notices') );

        $this->ays_pb_nonce = wp_create_nonce('ays_pb_admin_popup_categories_list_table_nonce');

        if( empty($this->ays_pb_nonce) ){
            add_action('init', function () {
                $this->ays_pb_nonce = wp_create_nonce('ays_pb_admin_popup_categories_list_table_nonce');
            }, 1);
        }
    }

    protected function get_views() {

        // Run a security check.
        if (empty($this->ays_pb_nonce) || ! wp_verify_nonce( $this->ays_pb_nonce, 'ays_pb_admin_popup_categories_list_table_nonce' ) ) {
            // This nonce is not valid.
            wp_die('Nonce verification failed!');
        }

        if( !is_user_logged_in()){
            wp_die(  esc_html__( 'Something went wrong', 'quiz-maker' ) );
        }

        // Verify unauthorized requests
        if( !current_user_can( 'manage_options' ) ){
            wp_die(  esc_html__( 'Something went wrong', 'quiz-maker' ) );
        }

        $published_count = $this->published_popup_categories_count();
        $unpublished_count = $this->unpublished_popup_categories_count();
        $all_count = $this->all_record_count();
        $selected_all = "";
        $selected_0 = "";
        $selected_1 = "";

        if ( isset($_REQUEST['fstatus']) && is_numeric($_REQUEST['fstatus']) && ! is_null(sanitize_text_field($_REQUEST['fstatus'])) ) {
            switch (sanitize_text_field($_GET['fstatus'])) {
                case 0:
                    $selected_0 = "style='font-weight:bold;'";
                    break;
                case 1:
                    $selected_1 = "style='font-weight:bold;'";
                    break;
                default:
                    $selected_all = "style='font-weight:bold;'";
                    break;
            }
        } else {
            $selected_all = "style='font-weight:bold;'";
        }

        $href = "?page=" . esc_attr($_REQUEST['page']);

        if (isset($_REQUEST['s']) && $_REQUEST['s'] != '') {
            $search = esc_sql(sanitize_text_field($_REQUEST['s']));
            $href .= '&s=' . $search;
        }

        $status_links = array(
            "all" => "<a " . $selected_all . " href='" . $href . "'>" . esc_html__('All', "ays-popup-box") . " (" . $all_count . ")</a>",
            "published" => "<a " . $selected_1 . " href='" . $href . "&fstatus=1'>" . esc_html__('Published', "ays-popup-box") . " (" . $published_count . ")</a>",
            "unpublished" => "<a " . $selected_0 . " href='" . $href . "&fstatus=0'>" . esc_html__('Unpublished', "ays-popup-box") . " (" . $unpublished_count . ")</a>"
        );
        return $status_links;
    }

    public function published_popup_categories_count() {

        // Run a security check.
        if (empty($this->ays_pb_nonce) || ! wp_verify_nonce( $this->ays_pb_nonce, 'ays_pb_admin_popup_categories_list_table_nonce' ) ) {
            // This nonce is not valid.
            wp_die('Nonce verification failed!');
        }

        if( !is_user_logged_in()){
            wp_die(  esc_html__( 'Something went wrong', 'quiz-maker' ) );
        }

        // Verify unauthorized requests
        if( !current_user_can( 'manage_options' ) ){
            wp_die(  esc_html__( 'Something went wrong', 'quiz-maker' ) );
        }

        global $wpdb;
        $conditions = array();

        $sql = "SELECT COUNT(*) FROM {$wpdb->prefix}ays_pb_categories WHERE published=1";

        if (isset($_REQUEST['s']) && $_REQUEST['s'] != '') {
            $search = esc_sql(sanitize_text_field($_REQUEST['s']));
            $conditions[] = sprintf("title LIKE '%%%s%%' ", esc_sql($wpdb->esc_like($search)));
        }

        if (!empty($conditions)) {
            $sql .= " AND " . implode(" AND ", $conditions);
        }

        return $wpdb->get_var($sql);
    }

    public function unpublished_popup_categories_count() {

        // Run a security check.
        if (empty($this->ays_pb_nonce) || ! wp_verify_nonce( $this->ays_pb_nonce, 'ays_pb_admin_popup_categories_list_table_nonce' ) ) {
            // This nonce is not valid.
            wp_die('Nonce verification failed!');
        }

        if( !is_user_logged_in()){
            wp_die(  esc_html__( 'Something went wrong', 'quiz-maker' ) );
        }

        // Verify unauthorized requests
        if( !current_user_can( 'manage_options' ) ){
            wp_die(  esc_html__( 'Something went wrong', 'quiz-maker' ) );
        }

        global $wpdb;
        $conditions = array();

        $sql = "SELECT COUNT(*) FROM {$wpdb->prefix}ays_pb_categories WHERE published=0";

        if (isset($_REQUEST['s']) && $_REQUEST['s'] != '') {
            $search = esc_sql(sanitize_text_field($_REQUEST['s']));
            $conditions[] = sprintf("title LIKE '%%%s%%' ", esc_sql($wpdb->esc_like($search)));
        }

        if (!empty($conditions)) {
            $sql .= " AND " . implode(" AND ", $conditions);
        }

        return $wpdb->get_var($sql);
    }

    public function all_record_count() {

        // Run a security check.
        if (empty($this->ays_pb_nonce) || ! wp_verify_nonce( $this->ays_pb_nonce, 'ays_pb_admin_popup_categories_list_table_nonce' ) ) {
            // This nonce is not valid.
            wp_die('Nonce verification failed!');
        }

        if( !is_user_logged_in()){
            wp_die(  esc_html__( 'Something went wrong', 'quiz-maker' ) );
        }

        // Verify unauthorized requests
        if( !current_user_can( 'manage_options' ) ){
            wp_die(  esc_html__( 'Something went wrong', 'quiz-maker' ) );
        }

        global $wpdb;
        $conditions = array();

        $sql = "SELECT COUNT(*) FROM {$wpdb->prefix}ays_pb_categories WHERE 1=1";

        if (isset($_REQUEST['s']) && $_REQUEST['s'] != '') {
            $search = esc_sql(sanitize_text_field($_REQUEST['s']));
            $conditions[] = sprintf("title LIKE '%%%s%%' ", esc_sql($wpdb->esc_like($search)));
        }

        if (!empty($conditions)) {
            $sql .= " AND " . implode(" AND ", $conditions);
        }

        return $wpdb->get_var($sql);
    }

    /**
     * Handles data query and filter, sorting, and pagination.
     */
    public function prepare_items() {

        // Run a security check.
        if (empty($this->ays_pb_nonce) || ! wp_verify_nonce( $this->ays_pb_nonce, 'ays_pb_admin_popup_categories_list_table_nonce' ) ) {
            // This nonce is not valid.
            wp_die('Nonce verification failed!');
        }

        if( !is_user_logged_in()){
            wp_die(  esc_html__( 'Something went wrong', 'quiz-maker' ) );
        }

        // Verify unauthorized requests
        if( !current_user_can( 'manage_options' ) ){
            wp_die(  esc_html__( 'Something went wrong', 'quiz-maker' ) );
        }

        global $wpdb;

        $this->_column_headers = $this->get_column_info();

        /** Process bulk action */
        $this->process_bulk_action();

        $per_page = $this->get_items_per_page('popup_categories_per_page', 20);
        $current_page = $this->get_pagenum();
        $total_items = $this->record_count();

        $this->set_pagination_args( array(
            "total_items" => $total_items, // WE have to calculate the total number of items
            "per_page" => $per_page // WE have to determine how many items to show on a page
        ) );

        $search = isset($_REQUEST['s']) ? esc_sql( sanitize_text_field($_REQUEST['s']) ) : false;
        $do_search = $search ? sprintf( " title LIKE '%%%s%%' ", esc_sql($wpdb->esc_like($search)) ) : '';

        $this->items = $this->get_popup_categories($per_page, $current_page, $do_search);
    }

    /** Text displayed when no customer data is available */
    public function no_items() {
        echo esc_html__('There are no popup categories yet.', "ays-popup-box");
    }

    /**
     *  Associative array of columns
     *
     * @return array
     */
    function get_columns() {
        $columns = array(
            'cb' => '<input type="checkbox" />',
            'title' => esc_html__('Title', "ays-popup-box"),
            'description' => esc_html__('Description', "ays-popup-box"),
            'items_count' => esc_html__('Popups Count', "ays-popup-box"),
            'published' => esc_html__('Status', "ays-popup-box"),
            'id' => esc_html__('ID', "ays-popup-box"),
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
            'title' => array('title', true),
            'id' => array('id', true),
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
            case 'title':
            case 'description':
            case 'items_count':
            case 'published':
            case 'id':
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
        if (intval($item['id']) === 1) {
            return;
        }

        return sprintf(
            '<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['id']
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

        // Run a security check.
        if (empty($this->ays_pb_nonce) || ! wp_verify_nonce( $this->ays_pb_nonce, 'ays_pb_admin_popup_categories_list_table_nonce' ) ) {
            // This nonce is not valid.
            wp_die('Nonce verification failed!');
        }

        if( !is_user_logged_in()){
            wp_die(  esc_html__( 'Something went wrong', 'quiz-maker' ) );
        }

        // Verify unauthorized requests
        if( !current_user_can( 'manage_options' ) ){
            wp_die(  esc_html__( 'Something went wrong', 'quiz-maker' ) );
        }

        $delete_nonce = wp_create_nonce($this->plugin_name . '-delete-popup-category');

        $categories_title_length = intval($this->title_length);

        $popup_title = (isset($item['title']) && $item['title'] != "") ? esc_attr(stripcslashes($item['title'])) : "";

        $restitle = Ays_Pb_Admin::ays_pb_restriction_string("word", $popup_title, $categories_title_length);

        $title = sprintf('<a href="?page=%s&action=%s&popup_category=%d" title="%s"><strong>%s</strong></a>', esc_attr($_REQUEST['page']), 'edit', absint($item['id']), esc_attr($item['title']) ,$restitle);

        $actions = array(
            'edit' => sprintf( '<a href="?page=%s&action=%s&popup_category=%d">' . esc_html__('Edit', "ays-popup-box") . '</a>', esc_attr($_REQUEST['page']), 'edit', absint($item['id']) ),
        );

        if (intval($item['id']) !== 1) {
            $actions['delete'] = sprintf('<a class="ays_pb_confirm_del" data-message="%s" href="?page=%s&action=%s&popup_category=%s&_wpnonce=%s">' . esc_html__('Delete', "ays-popup-box") . '</a>', $restitle, esc_attr($_REQUEST['page']), 'delete', absint($item['id']), $delete_nonce);
        }

        return $title . $this->row_actions($actions);
    }

    function column_items_count($item) {
        global $wpdb;
        $sql = "SELECT COUNT(*) FROM {$wpdb->prefix}ays_pb WHERE category_id = " .  absint( esc_sql($item['id']) );
        $result = $wpdb->get_var($sql);

        if (isset($result) && $result > 0) {
            $result = sprintf('<a href="?page=%s&filterby=%d" target="_blank">%s</a>', 'ays-pb', $item['id'], $result);
        }

        return "<p style='text-align:left;font-size:14px;'>" . $result . "</p>";
    }

    function column_published($item) {
        $status = (isset($item['published']) && $item['published'] != '') ? absint( sanitize_text_field($item['published']) ) : '';

        $status_html = '';
        switch($status) {
            case 1:
                $status_html = '<span class="ays-pb-publish-status"><img src=' . AYS_PB_ADMIN_URL . "/images/icons/check-square.svg" . '></span>';
                break;
            case 0:
                $status_html = '<span class="ays-pb-publish-status"><img src=' . AYS_PB_ADMIN_URL . "/images/icons/square.svg" . '></span>';
                break;
            default:
                $status_html = '<span class="ays-pb-publish-status"><img src=' . AYS_PB_ADMIN_URL . "/images/icons/square.svg" . '></span>';
                break;
        }

        return $status_html;
    }

    /**
     * Delete a customer record.
     *
     * @param int $id customer ID
     */
    public function delete_popup_categories($id) {

        // Run a security check.
        if (empty($this->ays_pb_nonce) || ! wp_verify_nonce( $this->ays_pb_nonce, 'ays_pb_admin_popup_categories_list_table_nonce' ) ) {
            // This nonce is not valid.
            wp_die('Nonce verification failed!');
        }

        if( !is_user_logged_in()){
            wp_die(  esc_html__( 'Something went wrong', 'quiz-maker' ) );
        }

        // Verify unauthorized requests
        if( !current_user_can( 'manage_options' ) ){
            wp_die(  esc_html__( 'Something went wrong', 'quiz-maker' ) );
        }

        global $wpdb;
        $wpdb->delete(
            "{$wpdb->prefix}ays_pb_categories",
            array('id' => $id),
            array('%d')
        );
    }

    public function ays_pb_published_unpublished_popup_categories($id, $status = 'published') {

        // Run a security check.
        if (empty($this->ays_pb_nonce) || ! wp_verify_nonce( $this->ays_pb_nonce, 'ays_pb_admin_popup_categories_list_table_nonce' ) ) {
            // This nonce is not valid.
            wp_die('Nonce verification failed!');
        }

        if( !is_user_logged_in()){
            wp_die(  esc_html__( 'Something went wrong', 'quiz-maker' ) );
        }

        // Verify unauthorized requests
        if( !current_user_can( 'manage_options' ) ){
            wp_die(  esc_html__( 'Something went wrong', 'quiz-maker' ) );
        }

        global $wpdb;
        $pbcategories_table = esc_sql($wpdb->prefix . "ays_pb_categories");

        if ( is_null($id) || absint(sanitize_text_field($id)) == 0 ) {
            return null;
        }

        $id = absint(sanitize_text_field($id));
        $published = ($status == 'unpublished') ? 0 : 1;

        $wpdb->update(
            $pbcategories_table,
            array(
                'published' => $published,
            ),
            array('id' => $id),
            array('%d'),
            array('%d')
        );
    }

    /**
     * Returns the count of records in the database.
     *
     * @return null|string
     */
    public function record_count() {

        // Run a security check.
        if (empty($this->ays_pb_nonce) || ! wp_verify_nonce( $this->ays_pb_nonce, 'ays_pb_admin_popup_categories_list_table_nonce' ) ) {
            // This nonce is not valid.
            wp_die('Nonce verification failed!');
        }

        if( !is_user_logged_in()){
            wp_die(  esc_html__( 'Something went wrong', 'quiz-maker' ) );
        }

        // Verify unauthorized requests
        if( !current_user_can( 'manage_options' ) ){
            wp_die(  esc_html__( 'Something went wrong', 'quiz-maker' ) );
        }

        global $wpdb;

        $filter = array();
        $sql = "SELECT COUNT(*) FROM {$wpdb->prefix}ays_pb_categories";

        if ( isset($_REQUEST['fstatus']) && is_numeric($_REQUEST['fstatus']) && !is_null(sanitize_text_field($_REQUEST['fstatus'])) && esc_sql($_REQUEST['fstatus']) != '' ) {
            $fstatus = absint(esc_sql($_REQUEST['fstatus']));
            $filter[] = " published = " . $fstatus;
        }

        $search = isset($_REQUEST['s']) ? esc_sql( sanitize_text_field($_REQUEST['s']) ) : false;
        if ($search) {
            $filter[] = sprintf( " title LIKE '%%%s%%' ", esc_sql($wpdb->esc_like($search)) );
        }

        if (count($filter) !== 0) {
            $sql .= " WHERE " . implode(" AND ", $filter);
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
    public function get_popup_categories($per_page = 20, $page_number = 1, $search = '') {

        // Run a security check.
        if (empty($this->ays_pb_nonce) || ! wp_verify_nonce( $this->ays_pb_nonce, 'ays_pb_admin_popup_categories_list_table_nonce' ) ) {
            // This nonce is not valid.
            wp_die('Nonce verification failed!');
        }

        if( !is_user_logged_in()){
            wp_die(  esc_html__( 'Something went wrong', 'quiz-maker' ) );
        }

        // Verify unauthorized requests
        if( !current_user_can( 'manage_options' ) ){
            wp_die(  esc_html__( 'Something went wrong', 'quiz-maker' ) );
        }

        global $wpdb;
        $sql = "SELECT * FROM {$wpdb->prefix}ays_pb_categories";

        $where = array();

        if ($search != '') {
            $where[] = $search;
        }

        if ( isset($_REQUEST['fstatus']) && is_numeric($_REQUEST['fstatus']) && !is_null(sanitize_text_field($_REQUEST['fstatus'])) && esc_sql($_REQUEST['fstatus']) != '' ) {
            $fstatus = absint(esc_sql($_REQUEST['fstatus']));
            $where[] = " published = " . $fstatus;
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
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
        $sql .= ' OFFSET ' . ($page_number - 1) * $per_page;

        $result = $wpdb->get_results($sql, 'ARRAY_A');

        return $result;
    }

    /**
     * Returns an associative array containing the bulk action
     *
     * @return array
     */
    public function get_bulk_actions() {
        $actions = array(
            'bulk-published'    => esc_html__('Publish', "ays-popup-box"),
            'bulk-unpublished'  => esc_html__('Unpublish', "ays-popup-box"),
            'bulk-delete'       => esc_html__('Delete', "ays-popup-box"),
        );

        return $actions;
    }

    public function get_popup_category($id) {
        global $wpdb;

        $sql = "SELECT * FROM {$wpdb->prefix}ays_pb_categories WHERE id=" . absint( sanitize_text_field($id) );

        $result = $wpdb->get_row($sql, 'ARRAY_A');

        return $result;
    }

    public function add_edit_popup_category(){

        // Run a security check.
        if (empty($this->ays_pb_nonce) || ! wp_verify_nonce( $this->ays_pb_nonce, 'ays_pb_admin_popup_categories_list_table_nonce' ) ) {
            // This nonce is not valid.
            wp_die('Nonce verification failed!');
        }

        if( !is_user_logged_in()){
            wp_die(  esc_html__( 'Something went wrong', 'quiz-maker' ) );
        }

        // Verify unauthorized requests
        if( !current_user_can( 'manage_options' ) ){
            wp_die(  esc_html__( 'Something went wrong', 'quiz-maker' ) );
        }

        global $wpdb;
        $popup_category_table = $wpdb->prefix . 'ays_pb_categories';
        $ays_change_type = (isset($_POST['ays_change_type'])) ? sanitize_text_field( $_POST['ays_change_type'] ) : '';

        if( isset($_POST["popup_category_action"]) && wp_verify_nonce( sanitize_text_field( $_POST["popup_category_action"] ), 'popup_category_action' ) ){
            
            $id = absint( sanitize_text_field( $_POST['id'] ) );
            $title = stripslashes( sanitize_text_field( $_POST['ays_title'] ) );
            $description = stripslashes(wpautop( wp_kses_post($_POST['ays_description']) ) );
            $publish = absint( sanitize_text_field( $_POST['ays_publish'] ) );
            $message = '';
            if( $id == 0 ){
                $result = $wpdb->insert(
                    $popup_category_table,
                    array(
                        'title'         =>  $title,
                        'description'   =>  $description,
                        'published'     =>  $publish 
                    ),
                    array( 
                        '%s', //title
                        '%s', //description
                        '%d' //published
                     )
                );
                $message = 'created';
            }else{
                $result = $wpdb->update(
                    $popup_category_table,
                    array(
                        'title'         => $title,
                        'description'   => $description,
                        'published'     => $publish
                    ),
                    array( 'id' => $id ),
                    array( 
                        '%s', //title
                        '%s', //description
                        '%d'  //published
                    ),
                    array( '%d' )
                );
                $message = 'updated';
            }

            if( $result >= 0  ) {
                if($ays_change_type != ''){
                    if($id == null){
                        $url = esc_url_raw( add_query_arg( array(
                            "action"    => "edit",
                            "popup_category"  => $wpdb->insert_id,
                            "status"    => $message
                        ) ) );
                    }else{
                        $url = esc_url_raw( remove_query_arg(false) ) . '&status=' . $message;
                    }
                    wp_safe_redirect( $url );
                    exit;
                }else{
                    $url = esc_url_raw( remove_query_arg(array('action', 'popup_category')  ) ) . '&status=' . $message;
                    wp_safe_redirect( $url );
                    exit;
                }
            }
        }
    }

    public function process_bulk_action() {

        // Detect when a bulk action is being triggered.
        $action = $this->current_action();
        if ( ! $action ) {
            return;
        }

        if( !is_user_logged_in()){
            return;
        }

        // Verify unauthorized requests
        if( !current_user_can( 'manage_options' ) ){
            return;
        }

        if( current_user_can( 'manage_options' ) && is_user_logged_in() ){
            //Detect when a bulk action is being triggered...
            if ( "delete" === $this->current_action() ) {
                // In our file that handles the request, verify the nonce.
                $nonce = esc_attr($_REQUEST["_wpnonce"]);

                if ( !wp_verify_nonce($nonce, $this->plugin_name . "-delete-popup-category") ) {
                    die('Go get a life script kiddies');
                } else {
                    $this->delete_popup_categories( absint($_GET["popup_category"]) );

                    $url = esc_url_raw( remove_query_arg(array("action", "popup_category", "_wpnonce")) ) . "&status=deleted";
                    wp_safe_redirect( $url );
                    exit;
                }
            }

            // If the delete bulk action is triggered
            if ( (isset($_POST["action"]) && $_POST["action"] == "bulk-delete") || (isset($_POST["action2"]) && $_POST["action2"] == "bulk-delete") ) {
                $delete_ids = ( isset($_POST['bulk-delete']) && !empty($_POST['bulk-delete']) ) ? esc_sql($_POST['bulk-delete']) : array();

                // loop over the array of record IDs and delete them
                foreach ($delete_ids as $id) {
                    $this->delete_popup_categories($id);
                }

                $url = esc_url_raw( remove_query_arg(array("action", "popup_category", "_wpnonce")) ) . "&status=deleted";
                wp_safe_redirect( $url );
                exit;
            } elseif ( (isset($_POST['action']) && $_POST['action'] == 'bulk-published') || (isset($_POST['action2']) && $_POST['action2'] == 'bulk-published') ) {
                $published_ids = ( isset($_POST['bulk-delete']) && !empty($_POST['bulk-delete']) ) ? esc_sql($_POST['bulk-delete']) : array();

                // loop over the array of record IDs and mark as read them
                foreach ($published_ids as $id) {
                    $this->ays_pb_published_unpublished_popup_categories($id, 'published');
                }

                $url = esc_url_raw( remove_query_arg(array('action', 'popup_category', '_wpnonce')) ) . '&status=published';
                wp_safe_redirect( $url );
                exit;
            } elseif ( (isset($_POST['action']) && $_POST['action'] == 'bulk-unpublished') || (isset($_POST['action2']) && $_POST['action2'] == 'bulk-unpublished') ) {
                $unpublished_ids = ( isset($_POST['bulk-delete']) && !empty($_POST['bulk-delete']) ) ? esc_sql($_POST['bulk-delete']) : array();

                // loop over the array of record IDs and mark as read them
                foreach ($unpublished_ids as $id) {
                    $this->ays_pb_published_unpublished_popup_categories($id, 'unpublished');
                }

                $url = esc_url_raw( remove_query_arg(array('action', 'popup_category', '_wpnonce')) ) . '&status=unpublished';
                wp_safe_redirect( $url );
                exit;
            }
        }
        else {
            return;
        }
    }

    public function popup_category_notices() {

        // Run a security check.
        if (empty($this->ays_pb_nonce) || ! wp_verify_nonce( $this->ays_pb_nonce, 'ays_pb_admin_popup_categories_list_table_nonce' ) ) {
            // This nonce is not valid.
            wp_die('Nonce verification failed!');
        }

        if( !is_user_logged_in()){
            wp_die(  esc_html__( 'Something went wrong', 'quiz-maker' ) );
        }

        // Verify unauthorized requests
        if( !current_user_can( 'manage_options' ) ){
            wp_die(  esc_html__( 'Something went wrong', 'quiz-maker' ) );
        }

        if( empty($_REQUEST['status']) ){
            return;
        }

        $status = isset($_REQUEST['status']) ? sanitize_text_field($_REQUEST['status']) : '';

        if (empty($status)) return;

        if ('created' == $status)
            $updated_message = esc_html( esc_html__('Popup category created.', "ays-popup-box") );
        elseif ('updated' == $status)
            $updated_message = esc_html( esc_html__('Popup category saved.', "ays-popup-box") );
        elseif ('deleted' == $status)
            $updated_message = esc_html( esc_html__('Popup category deleted.', "ays-popup-box") );
         elseif ('published' == $status)
            $updated_message = esc_html( esc_html__('Popup category(s) published.', "ays-popup-box") );
        elseif ('unpublished' == $status)
            $updated_message = esc_html( esc_html__('Popup category(s) unpublished.', "ays-popup-box") );

        if (empty($updated_message)) return;

        ?>
        <div class="ays-pb-admin-notice notice notice-success is-dismissible">
            <p> <?php echo $updated_message; ?> </p>
        </div>
        <?php
    }
}