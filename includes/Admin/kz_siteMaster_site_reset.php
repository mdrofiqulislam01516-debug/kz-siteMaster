<?php
namespace kodezen\siteMaster\Admin;

/**
 * Site Reset class
 */

class kz_siteMaster_site_reset {

    private $plugin_file;

    /**
     * register hook
     */

    function __construct( $plugin_file = true ) {
        $this->plugin_file = $plugin_file;

       /**
        * Enqueue JS
        */

        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

        /**
         * AJAX
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
        ] );
    }

    /**
     * render form
     */

    public function handle_kz_siteMaster_reset_form() {

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => 'Permission denied' ] );
        }

        if ( ! check_ajax_referer( 'kz_reset_action', 'kz_reset_nonce', false ) ) {
            wp_send_json_error( [ 'message' => 'Nonce verification failed' ] );
        }

        if ( empty( $_POST[ 'kz_reset_confirm' ] ) || $_POST[ 'kz_reset_confirm' ] !== 'reset' ) {
            wp_send_json_error( [ 'message' => 'You must type "reset" to confirm' ] );
        }

        global $wpdb;

        /**
         * Save current state
         */

        $active_plugins = get_option( 'active_plugins', [] );
        $current_theme  = wp_get_theme()->get_stylesheet();

        $reactivate_theme       = ! empty( $_POST[ 'reactivate_theme' ] );
        $reactivate_plugins     = ! empty( $_POST[ 'reactivate_plugins' ] );
        $reactivate_this_plugin = ! empty( $_POST[ 'reactivate_this_plugin' ] );

        /**
         * Reset all tables except users/usermeta
         */

        $tables = $wpdb->get_col( "SHOW TABLES LIKE '{$wpdb->prefix}%'" );
        foreach ( $tables as $table ) {
            if ( in_array( $table, [ $wpdb->prefix.'users', $wpdb->prefix.'usermeta' ] ) ) continue;
            $wpdb->query( "TRUNCATE TABLE $table" );
        }
    
        /**
         * Reinstall
         */

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        require_once ABSPATH . 'wp-includes/pluggable.php';

        $site_title  = get_bloginfo('name');
        $admin_user  = 'admin';
        $admin_email = get_option( 'admin_email' ) ?: 'admin@example.com';
        $admin_pass  = 'admin';

        wp_install(
            $site_title, 
            $admin_user, 
            $admin_email, 
            true, 
            '', 
            $admin_pass
        );

        /**
         * Reactivate plugins
         */

        $protected_plugin = plugin_basename( $this->plugin_file );

// Reactivate all plugins
if ( $reactivate_plugins ) {
    $plugins_to_restore = array_unique( array_merge( [ $protected_plugin ], $active_plugins ) );
    update_option( 'active_plugins', $plugins_to_restore );
}

// Reactivate theme
if ( $reactivate_theme ) {
    switch_theme( $current_theme );
}

// Reactivate this plugin only (if Reactivate all plugins is NOT checked)
if ( $reactivate_this_plugin && ! $reactivate_plugins ) {

    // Plugin must exist
    if ( file_exists( WP_PLUGIN_DIR . '/' . $protected_plugin ) ) {
        update_option( 'active_plugins', [ $protected_plugin ] );
    } else {
        wp_send_json_error( [ 'message' => '❌ Plugin file does not exist: ' . $protected_plugin ] );
    }
}

        wp_send_json_success( [ 'message' => '✅ Site has been successfully reset!' ] );
    }
}
