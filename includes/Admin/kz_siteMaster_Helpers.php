<?php
namespace kodezen\siteMaster\Admin;

class kz_siteMaster_Helpers {

    /**
     * Disable foreign key checks
     */

    public static function disable_fk() {
        global $wpdb;
        $wpdb->suppress_errors( true );
        $wpdb->query( 'SET foreign_key_checks = 0' );
    }

    /**
     * Enable foreign key checks
     */

    public static function enable_fk() {
        global $wpdb;
        $wpdb->query( 'SET foreign_key_checks = 1' );
    }

    /**
     * Flush WP cache
     */
    
    public static function flush_cache() {
        wp_cache_flush();
    }

    /**
     * Drop tables if they exist
     */

    public static function drop_tables( $tables ) {
        global $wpdb;
        foreach ( $tables as $table ) {
            if ( $wpdb->get_var( "SHOW TABLES LIKE '$table'" ) === $table ) {
                $wpdb->query( "DROP TABLE `$table`" );
            }
        }
    }

    /**
     * Truncate tables
     */

    public static function truncate_tables( $tables ) {
        global $wpdb;
        foreach ( $tables as $table ) {
            $wpdb->query( "TRUNCATE TABLE `$table`" );
        }
    }

    /**
     * Ignore protected tables 
     */
    
    public static function ignore_tables( $tables ) {
        global $wpdb;
        return array_filter( $tables, function($table) use ($wpdb) {
            return ! in_array( $table, [ $wpdb->prefix . 'users', $wpdb->prefix . 'usermeta' ]);
        });
    }

    /**
     * Reinstall WP 
     */

    public static function reinstall_wp() {
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        
        $current_user = wp_get_current_user();

        $admin_user  = $current_user->user_login;
        $admin_email = $current_user->user_email;
        $site_title  = get_option( 'name' );
        $blog_public = get_option( 'blog_public' );
        $password    = wp_generate_password( 20, true, true );
        $wplang      = get_option( 'WPLANG' );

        return wp_install(
            $site_title,
            $admin_user,
            $admin_email,
            $blog_public,
            $password,
            $wplang
        );
    }
}
