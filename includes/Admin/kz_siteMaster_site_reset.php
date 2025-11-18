<?php
namespace kodezen\siteMaster\Admin;

require_once __DIR__ . '/kz_siteMaster_Helpers.php';
use kodezen\siteMaster\Admin\kz_siteMaster_Helpers;

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

        wp_localize_script( 'kz-siteMaster-reset', 'kzsiteMaster', [
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

        $current_theme  = wp_get_theme()->get_stylesheet();
        $active_plugin  = get_option( 'active_plugin' );

        if ( ! empty( $active_plugin ) ) {
            deactivate_plugins( $active_plugin );
        }

        $reactivate_theme       = ! empty( $_POST[ 'reactivate_theme' ] );    
        $reactivate_this_plugin = ! empty( $_POST[ 'reactivate_this_plugin' ] );

        /**
         * Reset all tables except users/usermeta
         */
        
        $tables = $wpdb->get_col( "SHOW TABLES LIKE '{$wpdb->prefix}%'" );

        $wp_core_tables = [
            'commentmeta',
            'comments',
            'links',
            'options',
            'postmeta',
            'posts',
            'termmeta',
            'terms',
            'term_relationships',
            'term_taxonomy'
        ];

        $custom_tables = array_diff( $tables, array_merge( $wp_core_tables, [ $wpdb->prefix.'users', $wpdb->prefix.'usermeta' ] ) );

        kz_siteMaster_Helpers::disable_fk();
        kz_siteMaster_Helpers::drop_tables($custom_tables);
        kz_siteMaster_Helpers::truncate_tables($wp_core_tables);
        kz_siteMaster_Helpers::enable_fk();
        kz_siteMaster_Helpers::flush_cache();
        
        /**
         * wp_install
         */

        $result = kz_siteMaster_Helpers::reinstall_wp();

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( [ 'message' => $result->get_error_message() ] );
        }

        $user_id = $result[ 'user_id' ] ?? 0;

        delete_user_meta( $user_id, 'default_password_nag' );
        delete_user_meta( $user_id, $wpdb->prefix . 'default_password_nag' );

        $protected_plugin = 'kz-siteMaster/kz-siteMaster.php';

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
            'redirect_url' => admin_url('admin.php?page=kz_siteMaster' )
        ] );            
    }
}
