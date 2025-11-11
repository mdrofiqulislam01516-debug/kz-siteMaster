<?php
namespace kodezen\siteMaster\Admin;

/**
 * Site Reset class
 */

class kz_siteMaster_site_reset {

    /**
     * register hook
     */

    function __construct( ) {

       /**
        * Enqueue js
        */

        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

        /**
         * Ajax
         */ 

        add_action( 'wp_ajax_kz_siteMaster_handle_reset', [ $this, 'handle_kz_siteMaster_reset_form' ] );
    }

    /**
     * enqueue ajax
     */

    public function enqueue_scripts() {

        wp_enqueue_script(
            'kz-siteMaster-reset',
            KZ_SITEMASTER_ASSETS . '/js/kz_siteMaster.js',
            [ 'jquery' ],
            filemtime( KZ_SITEMASTER_PATH . '/assets/js/kz_siteMaster.js' ),
            true
        );

        wp_localize_script('kz-siteMaster-reset', 'kzsiteMaster', [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'kz_reset_nonce' ),
        ] );
    }

    /**
     * render form
     */

    public function handle_kz_siteMaster_reset_form() {

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => 'Permission denied' ] );
        }

        if ( ! check_ajax_referer( 'kz_reset_nonce', 'nonce' ) ) {
            wp_send_json_error( [ 'message' => 'Nonce verification failed' ] );
        }

        if ( empty( $_POST[ 'kz_reset_confirm' ] ) || $_POST[ 'kz_reset_confirm' ] !== 'reset' ) {
            wp_send_json_error( [ 'message' => 'You must type "reset" to confirm' ] );
        }

        global $wpdb;

        /**
         * Save plugin/theme
         */

        $active_plugins = get_option( 'active_plugins', ['KZ_siteMaster'] );
        $current_theme  = wp_get_theme()->get_stylesheet();

        $reactivate_theme       = ! empty( $_POST[ 'reactivate_theme' ] );
        $reactivate_plugins     = ! empty( $_POST[ 'reactivate_plugins' ] );
        $reactivate_this_plugin = ! empty( $_POST[ 'reactivate_this_plugin' ] );

        /**
         * Reset all tables except users/usermeta
         */
        
        $tables = $wpdb->get_col( "SHOW TABLES LIKE '{$wpdb->prefix}%'" );

        $core_tables = [
            $wpdb->prefix.'commentmeta',
            $wpdb->prefix.'comments',
            $wpdb->prefix.'links',
            $wpdb->prefix.'options',
            $wpdb->prefix.'postmeta',
            $wpdb->prefix.'posts',
            $wpdb->prefix.'termmeta',
            $wpdb->prefix.'terms',
            $wpdb->prefix.'term_relationships',
            $wpdb->prefix.'term_taxonomy'
        ];

        $custom_tables = array_diff($tables, array_merge($core_tables, [$wpdb->prefix.'users', $wpdb->prefix.'usermeta']));

        $wpdb->suppress_errors( true );
        $wpdb->query('SET foreign_key_checks = 0');           
        
        foreach ( $custom_tables as $tb ) {
            $wpdb->query( "DROP TABLE IF EXISTS $tb" );
        }

        foreach ( $core_tables as $table ) {
            $wpdb->query( "TRUNCATE TABLE $table" );
        }
        
        $wpdb->query( 'SET FOREIGN_KEY_CHECKS = 1;' );
        wp_cache_flush();
        $wpdb->queries = []; 

        /**
         * wp_install
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
            md5(wp_rand()),
            $wplang
        );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( [ 'message' => $result->get_error_message() ] );
        }

        $user_id = $result['user_id'] ?? 0;

        delete_user_meta( $user_id, 'default_password_nag' );
        delete_user_meta( $user_id, $wpdb->prefix . 'default_password_nag' );

        $protected_plugin = 'kz-siteMaster/kz-siteMaster.php';
        $plugins_to_restore = array_unique( array_merge( $active_plugins ) );

        if ( $reactivate_plugins ) {
            update_option( 'active_plugins', $plugins_to_restore );
        }

        if ( $reactivate_theme ) {
            switch_theme( $current_theme );
        }

        if ( $reactivate_this_plugin ) {
            activate_plugin( $protected_plugin );
        }

        wp_clear_auth_cookie();
        wp_set_auth_cookie( $user_id );
 
        wp_send_json_success( [ 
            'message' => '✅ Site has been successfully reset!', 
            'redirect_url' => admin_url('admin.php?page=kz_siteMaster')
        ] );            
    }
}
