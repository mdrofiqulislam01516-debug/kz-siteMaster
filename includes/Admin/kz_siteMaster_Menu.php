<?php
namespace kodezen\siteMaster\Admin;

/**
 * kz siteMaster Manu 
 */

class kz_siteMaster_Menu {

    /**
     * admin menu hook
     */
    
    function __construct() {

        add_action( 'admin_menu', [ $this, 'kz_siteMaster_register_admin_menu' ] );
    }

    /**
     * menu register
     */

    public function kz_siteMaster_register_admin_menu() {

        add_menu_page(

            __( 'kz siteMaster', 'kz_siteMaster' ), 
            __( 'KZ siteMaster', 'kz_siteMaster' ),
            'manage_options',
            'kz_siteMaster',
            [ $this, 'kz_siteMaster_render_plugin_page' ],
            'dashicons-update'

        );
    }

    /**
     * kz siteMaster render form 
     */

    public function kz_siteMaster_render_plugin_page() {
          
        include plugin_dir_path(__FILE__) . 'Form/kz_db_reset_form.php'; 
        include plugin_dir_path(__FILE__) . 'Form/kz_wc_reset_form.php'; 
        include plugin_dir_path(__FILE__) . 'Form/kz_reset_form.php'; 
        
    }
}




