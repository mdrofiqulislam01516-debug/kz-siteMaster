<?php
namespace kodezen\siteMaster\Admin;

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

        add_action('wp_ajax_kz_siteMaster_db_table_reset', [ $this, 'kz_siteMaster_db_table_reset_form' ] );

    }

    /**
     * Enqueue ajax 
     */

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

    /**
     * render form
     */

    public function kz_siteMaster_db_table_reset_form() {

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => 'Permission denied' ] );   
        }

        if ( ! check_ajax_referer('kz_db_reset_nonce', 'nonce') ) {
            wp_send_json_error( [ 'message' => 'Nonce verification failed' ] );
        }
    
        if ( empty($_POST[ 'kz_db_reset_confirm' ] ) || $_POST[ 'kz_db_reset_confirm' ] !== 'reset' ) {
            wp_send_json_error( [ 'message' => 'You must type "reset" to confirm' ] );
        }
       
        $tables = isset($_POST[ 'tables' ] ) ? (array) $_POST[ 'tables' ] : [];
        if ( empty( $tables ) ) {
            wp_send_json_error( [ 'message' => 'No tables selected' ] );
        }

        global $wpdb;
   
        /**
         * save plugin/theme
         */

        $current_theme  = wp_get_theme()->get_stylesheet();
        $active_plugin = get_option( 'active_plugin' );

        if ( ! empty( $active_plugin ) ) {
            deactivate_plugins( $active_plugin );
        }

        $reactivate_plugin_theme = ! empty( $_POST[ 'reactivate' ] );

        $tables = $wpdb->get_col( "SHOW TABLES LIKE '{$wpdb->prefix}%'" );

        $wpdb->suppress_errors( true );
        $wpdb->query('SET foreign_key_checks = 0');

        foreach ($tables as $table) {

            $table = sanitize_text_field( $table );

            if ( strpos( $table, $wpdb->prefix ) !== 0 ) {
                continue;
            }
            
            if ( in_array( $table, [ $wpdb->prefix. 'users', $wpdb->prefix. 'usermeta' ] ) ) {
                continue;
            }
            $wpdb->query("TRUNCATE TABLE `$table`");
        }

        $wpdb->query( 'SET foreign_key_checks = 1;' );
        wp_cache_flush();


        /**
         * wp-_install
         */
        
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        
        $current_user = wp_get_current_user();

        $admin_user  = $current_user->user_login;
        $admin_email = $current_user->user_email;
        $site_title  = get_option('name');
        $blog_public = get_option('blog_public');
        $wplang      = get_option('WPLANG');

        $result = wp_install(
            $site_title,
            $admin_user,
            $admin_email,
            $blog_public,
            '',
            wp_generate_password(20, true, true),
            $wplang
        );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( [ 'message' => $result->get_error_message() ] );
        }

        $user_id = $result[ 'user_id' ] ?? 0;

        delete_user_meta( $user_id, 'default_password_nag' );
        delete_user_meta( $user_id, $wpdb->prefix . 'default_password_nag' );
        
        $active_plugins = 'kz-siteMaster/kz-siteMaster.php';
        if ( $reactivate_plugin_theme ) {
            update_option( 'active_plugins', $active_plugins );
            switch_theme( $current_theme );
        }
       
        wp_send_json_success( [
            'message' => '✅ Selected tables have been reset successfully!',
            'redirect_url' => admin_url( 'admin.php?page=kz_siteMaster' )
        ] );

    } 
}
