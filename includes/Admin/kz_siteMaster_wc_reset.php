<?php
namespace kodezen\siteMaster\Admin;

/**
 * DB Tables Reset class
 */
class kz_siteMaster_wc_reset {

    /**
     * register hook
     */

    function __construct() {

        /**
         * Enqueue js
         */

        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_script_wc_reset' ] );

        /**
         * ajax
         */

        add_action( 'wp_ajax_kz_siteMaster_wc_reset', [ $this, 'kz_siteMaster_wc_table_reset_form' ] );

    }

    /**
     * Enqueue ajax 
     */


    public function enqueue_script_wc_reset() {

        wp_enqueue_script(
            'kz-siteMaster-wc-reset',
            KZ_SITEMASTER_ASSETS . '/js/kz_siteMaster_wc.js',
            [ 'jquery' ],
            filemtime( KZ_SITEMASTER_PATH . '/assets/js/kz_siteMaster_wc.js' ),
            true
        );

        wp_localize_script( 'kz-siteMaster-wc-reset', 'kzsiteMasterwc', [
            'ajax_url'  => admin_url( 'admin-ajax.php' ),
            'nonce'     => wp_create_nonce( 'kz_wc_reset_nonce' )
        ] );
    }

    /**
     * render form
     */

    public function kz_siteMaster_wc_table_reset_form() {

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( [ 'message' => 'Permission denied' ] );   
        }

        if ( ! check_ajax_referer( 'kz_wc_reset_nonce', 'nonce' ) ) {
            wp_send_json_error( [ 'message' => 'Nonce verification failed' ] );
        }
    
        if ( empty( $_POST[ 'kz_wc_reset_confirm' ] ) || $_POST[ 'kz_wc_reset_confirm' ] !== 'reset' ) {
            wp_send_json_error( [ 'message' => 'You must type "reset" to confirm' ] );
        }
        
        global $wpdb;

        if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
            deactivate_plugins( 'woocommerce/woocommerce.php' );
        }


        $wc_tables = [
            'wp_wc_admin_notes', 
            'wp_wc_admin_note_actions', 
            'wp_wc_category_lookup', 
            'wp_wc_customer_lookup', 
            'wp_wc_download_log', 
            'wp_wc_orders_meta', 
            'wp_wc_order_addresses', 
            'wp_wc_order_coupon_lookup', 
            'wp_wc_order_operational_data', 
            'wp_wc_order_product_lookup', 
            'wp_wc_order_stats', 
            'wp_wc_order_tax_lookup', 
            'wp_wc_product_attributes_lookup', 
            'wp_wc_product_download_directories', 
            'wp_wc_product_meta_lookup', 
            'wp_wc_rate_limits', 
            'wp_wc_reserved_stock', 
            'wp_wc_tax_rate_classes', 
            'wp_wc_webhooks', 
            'wp_woocommerce_api_keys', 
            'wp_woocommerce_attribute_taxonomies', 
            'wp_woocommerce_downloadable_product_permissions', 
            'wp_woocommerce_log', 
            'wp_woocommerce_order_itemmeta', 
            'wp_woocommerce_order_items', 
            'wp_woocommerce_payment_tokenmeta', 
            'wp_woocommerce_payment_tokens', 
            'wp_woocommerce_sessions', 
            'wp_woocommerce_shipping_zones', 
            'wp_woocommerce_shipping_zone_locations', 
            'wp_woocommerce_shipping_zone_methods', 
            'wp_woocommerce_tax_rates', 
            'wp_woocommerce_tax_rate_locations'
        ];

        $wpdb->suppress_errors( true );
        $wpdb->query( 'SET foreign_key_checks = 0' ); 

        foreach ( $wc_tables as $table ) {
            if ( $wpdb->get_var( "SHOW TABLES LIKE '$table'" ) === $table ) {
                $wpdb->query( "DROP TABLE `$table`" );
            }
        }

        $wpdb->query( 'SET FOREIGN_KEY_CHECKS = 1;' );
        wp_cache_flush();
        
        wp_send_json_success( [
            'message' => '✅ WooCommerce Plugin have been reset successfully!',
            'redirect_url' => admin_url( 'admin.php?page=kz_siteMaster' )
        ] );

    }
}


