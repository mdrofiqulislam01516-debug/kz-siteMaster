<?php
namespace kodezen\siteMaster\Admin;

class kz_siteMaster_site_reset {

    private $plugin_file;

    function __construct( $plugin_file = null ) {
       $this->plugin_file = $plugin_file;

        add_action( 'admin_post_kz_siteMaster_handle_reset', [ $this,'handle_reset_form' ] );

    }

    public function handle_reset_form() {

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die('Permission denied');
        }
                       
        if ( ! isset( $_POST[ 'kz_reset_nonce' ] ) || ! wp_verify_nonce( $_POST[ 'kz_reset_nonce' ], 'kz_reset_action' ) ) {
            wp_die( 'Nonce verification failed' );
        }

        if ( ! isset( $_POST[ 'kz_reset_confirm' ] ) || $_POST[ 'kz_reset_confirm' ]  !== 'reset' ) {
            wp_die( 'You must type "reset" to confirm' );
        }

        global $wpdb;

        // Save current state

        $active_plugins = get_option( 'active_plugins', [] );
        $current_theme  = wp_get_theme()->get_stylesheet();

        $reactivate_theme       = ! empty( $_POST[ 'reactivate_theme' ] );
        $reactivate_plugins     = ! empty( $_POST[ 'reactivate_plugins' ] );
        $reactivate_this_plugin = ! empty( $_POST[ 'reactivate_this_plugin' ] );

        // Reset tables (except essential ones)

        $tables = $wpdb->get_col( "SHOW TABLES LIKE '{$wpdb->prefix}%'" );
        
        foreach( $tables as $table ) {
            if ( in_array( $table, [$wpdb->prefix.'users', $wpdb->prefix.'usermeta' ] ) ) {
                continue;
            }
            $wpdb->query( "TRUNCATE TABLE $table" );
        }

        // Reinstall WordPress core

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        require_once ABSPATH . 'wp-includes/pluggable.php';

        $site_title  = get_bloginfo( 'name' );
        $admin_user  = 'admin';
        $admin_email = get_option( 'admin_email' ) ?: 'admin@example.com';
        $admin_pass  = 'admin';
        
        wp_install(
            $site_title,
            $admin_user, 
            $admin_email, 
            true, 
            $admin_pass
        );
        
        // Safely restore plugins

        $protected_plugin   = plugin_basename( $this->plugin_file );
        $plugins_to_restore = array_unique( array_merge( [ $protected_plugin ], $active_plugins ) );

        if ( $reactivate_plugins ) {
            update_option( 'active_plugins', $plugins_to_restore );   
        }

        // Reactivate theme

        if ( $reactivate_theme ) {
            switch_theme( $current_theme );
        } 

        // Optional: reactivate this plugin if checkbox selected

        if ( $reactivate_this_plugin ) {
            activate_plugin( $protected_plugin );
        }

        // Redirect to dashboard
        wp_redirect( admin_url('admin.php?page=kz_siteMaster&sitereset=done') );
        exit;
    }
}
