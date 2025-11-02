<?php
namespace kodezen\siteMaster\Admin;

class kz_siteMaster_fullsite_reset {

    function __construct() {

        add_action( 'admin_init', [ $this, 'kz_siteMaster_handle_reset_form' ] );

    }

    /**
     * Completely reset the WordPress database (like fresh install)
     */
    public function kz_siteMaster_handle_reset_form() {

        if ( isset( $_POST['kz_siteMaster_fullsite_reset'] ) && check_admin_referer( 'kz_reset_action', 'kz_reset_nonce' ) ) {
                
            global $wpdb;

            $tables = $wpdb->get_col('SHOW TABLES');

            foreach( $tables as $table ) {

                $wpdb->query("DROP TABLE IF EXISTS $table");

            }

            if ( ! defined('ABSPATH') ) exit;

            // Reinstall WordPress default tables
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        require_once( ABSPATH . 'wp-admin/includes/schema.php' );
        require_once( ABSPATH . 'wp-includes/pluggable.php' );

        $site_title   = 'wordpress';
        $admin_user   = 'admin';
        $admin_email  = 'mdrofiqulislam01516@gmail.com';
        $admin_pass   = 'admin';

        wp_install(
            $site_title,      // Site title
            $admin_user,      // Admin username
            $admin_email,     // Admin email
            true,             // Public?
            'wordpress',               // Deprecated
            $admin_pass       // Admin password
        );

                wp_redirect( admin_url( 'admin.php?page=kz_siteMaster&reset=done' ) );
                exit;
        }

    }
}