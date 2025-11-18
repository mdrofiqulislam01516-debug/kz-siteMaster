<?php
namespace kodezen\siteMaster\Admin;

require_once __DIR__ . '/kz_siteMaster_Helpers.php';
use kodezen\siteMaster\Admin\kz_siteMaster_Helpers;

/**
 * DB Tables Reset class
 */ 

class kz_siteMaster_db_reset {

    /**
     * register hook
     */

    function __construct() {
        
        /**
         * Enqueue js
         */

        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_script_db_reset' ] );

        /**
         * ajax
         */

        add_action( 'wp_ajax_kz_siteMaster_db_table_reset', [ $this, 'kz_siteMaster_db_table_reset_form' ] );

    }

    /**
     * Enqueue ajax 
     */

    public function enqueue_script_db_reset() {

        wp_enqueue_script(
            'kz-siteMaster-db-reset',
            KZ_SITEMASTER_ASSETS . '/js/kz_siteMaster.js',
            [ 'jquery' ],
            filemtime( KZ_SITEMASTER_PATH . '/assets/js/kz_siteMaster.js' ),
            true
        );

        wp_localize_script( 'kz-siteMaster-db-reset', 'kzsiteMasterdb', [
            'ajax_url'  => admin_url( 'admin-ajax.php' ),
            'nonce'     => wp_create_nonce( 'kz_db_reset_nonce' )
        ] );
    }

    /**
     * render form
     */

    public function kz_siteMaster_db_table_reset_form() {

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => 'Permission denied' ] );   
        }

        if ( ! check_ajax_referer( 'kz_db_reset_nonce', 'nonce' ) ) {
            wp_send_json_error( [ 'message' => 'Nonce verification failed' ] );
        }
    
        if ( empty( $_POST[ 'kz_db_reset_confirm' ] ) || $_POST[ 'kz_db_reset_confirm' ] !== 'reset' ) {
            wp_send_json_error( [ 'message' => 'You must type "reset" to confirm' ] );
        }
       
        $tables = isset( $_POST[ 'tables' ] ) ? (array) $_POST[ 'tables' ] : [];
        if ( empty( $tables ) ) {
            wp_send_json_error( [ 'message' => 'No tables selected' ] );
        }

        global $wpdb;
   
        /**
         * save plugin/theme
         */

        $current_theme  = wp_get_theme()->get_stylesheet();
        $protected_plugin = 'kz-siteMaster/kz-siteMaster.php';
        $active_plugins = get_option( 'active_plugins' );

        if ( ! empty( $active_plugins ) ) {
            deactivate_plugins( $active_plugins );
        }

        $reactivate_plugin_theme = ! empty( $_POST[ 'reactivate' ] );

        kz_siteMaster_Helpers::disable_fk();
        $tables = kz_siteMaster_Helpers::ignore_tables($tables);
        kz_siteMaster_Helpers::truncate_tables($tables);
        kz_siteMaster_Helpers::enable_fk();
        kz_siteMaster_Helpers::flush_cache();


        /**
         * wp-_install
         */
        
        $result = kz_siteMaster_Helpers::reinstall_wp();

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( [ 'message' => $result->get_error_message() ] );
        }

        $user_id = $result[ 'user_id' ] ?? 0;

        delete_user_meta( $user_id, 'default_password_nag' );
        delete_user_meta( $user_id, $wpdb->prefix . 'default_password_nag' );

        if ( $reactivate_plugin_theme ) {
            update_option( 'active_plugins', [ $protected_plugin ] );
            switch_theme( $current_theme );
        }
       
        wp_send_json_success( [
            'message' => '✅ Selected tables have been reset successfully!',
            'redirect_url' => admin_url( 'admin.php?page=kz_siteMaster' )
        ] );

    } 
}
