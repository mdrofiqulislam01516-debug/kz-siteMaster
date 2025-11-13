<?php
namespace kodezen\siteMaster\Admin;

class kz_siteMaster_db_reset {

    function __construct() {

        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_script_db_reset' ] );

        add_action('wp_ajax_kz_siteMaster_db_table_reset', [ $this, 'kz_siteMaster_db_table_reset_form' ] );

    }

    public function enqueue_script_db_reset() {

        wp_enqueue_script(
            'kz-siteMaster-db-reset',
            KZ_SITEMASTER_ASSETS . '/js/kz_siteMaster_db.js',
            [ 'jquery' ],
            filemtime( KZ_SITEMASTER_PATH . '/assets/js/kz_siteMaster_db.js' ),
            true
        );

        wp_localize_script( 'kz-siteMaster-db-reset', 'kzsiteMasterdb', [
            'ajax_url'  => admin_url( 'admin-ajax.php' ),
            'nonce'     => wp_create_nonce( 'kz_db_reset_nonce' )
        ] );
    }

    public function kz_siteMaster_db_table_reset_form() {

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => 'Permission denied' ] );   
        }

        if ( ! check_ajax_referer('kz_db_reset_nonce', 'nonce') ) {
            wp_send_json_error( [ 'message' => 'Nonce verification failed' ] );
        }
    

        if ( empty($_POST[ 'kz_db_reset_confirm' ] ) || $_POST[ 'kz_db_reset_confirm' ] ) {
            wp_send_json_error( [ 'message' => 'You must type "reset" to confirm' ] );
        }

    global $wpdb;
    foreach($_POST['tables'] as $table){
        $table = esc_sql($table);
        $wpdb->query("TRUNCATE TABLE `$table`");
    }

    // Theme & plugin reactivate
    if(!empty($_POST['reactivate'])){
        switch_theme(get_option('stylesheet'));
        foreach(get_option('active_plugins') as $plugin){
            activate_plugin($plugin);
        }
    }

    wp_send_json_success(['message' => 'Selected tables reset successfully!']);

    }
}
