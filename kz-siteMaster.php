<?php 
/*
 * Plugin Name:       KZ SiteMaster
 * Plugin URI:        https://kodezen.com/kz-siteMaster/
 * Description:       Full site reset + backup/snapshot/restore tools for development environments. Use with extreme caution.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * Author:            Kodezen Team
 * Author URI:        https://kodezen.com/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       kz_siteMaster
 * Domain Path:       /languages
 */


if ( ! defined( 'ABSPATH') ) {
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';

/**
 * The main plugin class
 */

final class kz_siteMaster {

    /**
     * plugin version 
     */

    const version = '1.0.0';

    /**
     * The construct 
     */

    private function __construct() {

        $this->define_construct();

        register_activation_hook( __FILE__, [ $this, 'kz_activate' ] );

        add_action( 'plugins_loaded', [ $this, 'kz_siteMaster_init' ] );
    }

    /**
     * The instance 
     * 
     * @return \kz_siteMaster
     */

    public static function init() {

        static $instance = false;

        if ( ! $instance ) {
            $instance = new self();
        }

        return $instance;
    }

    /**
     * Define construct
     */

    public function define_construct() {

        define( 'KZ_SITEMASTER_VERSION', self::version );
        define( 'KZ_SITEMASTER_FILE', __FILE__ );
        define( 'KZ_SITEMASTER_PATH', __DIR__ );
        define( 'KZ_SITEMASTER_URL', plugins_url( '', KZ_SITEMASTER_FILE ) );
        define( 'KZ_SITEMASTER_ASSETS', KZ_SITEMASTER_URL . '/assets' );
       
    }

    /**
     * plugin init
     * 
     * @return void
     */

    public function kz_siteMaster_init() {

            /**
            * Backend 
            */

            new kodezen\siteMaster\Admin\kz_siteMaster_Menu();
            new kodezen\siteMaster\Admin\kz_siteMaster_fullsite_reset();
           new kodezen\siteMaster\Admin\kz_siteMaster_site_reset();
                    
            /**
             * Frontend
             */


    }
    
    /**
     * Install time and update version 
     * 
     * @return void
     */

    public function kz_activate() {

        $installed = get_option( 'kz_siteMaster_installed' );

        if ( ! $installed ) {
            update_option( 'kz_siteMaster_installed', time() );
        }
        update_option( 'kz_siteMaster_version', KZ_SITEMASTER_VERSION );
    }    
}

/**
 * Initialize plugin 
 * 
 * @return \kz_siteMaster
 */

function kz_siteMaster() {
    return kz_siteMaster::init();
}

/**
 * Excess key 
 */
kz_siteMaster();

