<?php
/**
 * Fired when the plugin is uninstalled.
 *
 *
 * @package    		Pofio
 * @subpackage 		Pofio/includes
 * @link       		https://github.com/mypreview/pofio
 * @author     		Mahdi Yazdani (Github: @mahdiyazdani, @mypreview)
 * @since      		1.0.0
 */
// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}