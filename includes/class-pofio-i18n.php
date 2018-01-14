<?php

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @package    		Pofio
 * @subpackage 		Pofio/includes
 * @link       		https://github.com/mypreview/pofio
 * @author     		Mahdi Yazdani (Github: @mahdiyazdani, @mypreview)
 * @since      		1.0.0
 */
class Pofio_i18n {

    /**
     * Load the plugin text domain for translation.
     *
     * @since    1.0.0
     */
    public function load_plugin_textdomain() {
        load_plugin_textdomain( 'pofio', FALSE, dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/' );
    }

}
