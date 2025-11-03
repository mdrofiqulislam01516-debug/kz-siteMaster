<?php
namespace kodezen\siteMaster\Admin;

class kz_siteMaster_site_reset {

    function __construct() {
        add_action( 'admin_post_kz_siteMaster_handle_reset', [ $this, 'handle_reset_form']);
    }

    // Handle reset form
    public function handle_reset_form() {
        if ( ! current_user_can('manage_options') ) {
            wp_die('Permission denied');
        }

        if ( ! isset($_POST['kz_reset_nonce']) || ! wp_verify_nonce($_POST['kz_reset_nonce'], 'kz_reset_action') ) {
            wp_die('Nonce verification failed');
        }

        if ( ! isset($_POST['kz_siteMaster_reset_confirm']) || $_POST['kz_siteMaster_reset_confirm'] !== 'reset' ) {
            wp_die('You must type "reset" to confirm');
        }

        global $wpdb;

       
        $tables = $wpdb->get_col('SHOW TABLES');
        
        foreach( $tables as $table ) {
            $wpdb->query("DROP TABLE IF EXISTS $table");
        }

      
        if ( ! defined('ABSPATH') ) exit;
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        require_once ABSPATH . 'wp-includes/pluggable.php';

        $site_title  = 'wordpress';
        $admin_user  = 'admin';
        $admin_email = 'mdrofiqulislam01516@gmail.com';
        $admin_pass  = 'admin';

        wp_install(
            $site_title,
            $admin_user,
            $admin_email,
            true,
            'wordpress',
            $admin_pass
        );

        $current_theme = wp_get_theme();
        switch_theme( $current_theme->get_stylesheet() );

     
        if ( $active_plugins = get_option('active_plugins', []) ) {
            foreach ( $active_plugins as $plugin ) {
                if ( file_exists(WP_PLUGIN_DIR . '/' . $plugin) ) {
                    activate_plugin( $plugin );
                }
            }
        }

        
        wp_redirect( admin_url( ) );
        exit;
    }
}