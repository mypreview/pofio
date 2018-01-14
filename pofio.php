<?php

/**
 * The `Pofio` bootstrap file.
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://github.com/mypreview/pofio
 * @since             1.0.0
 * @package           Pofio
 * @author     		  Mahdi Yazdani (Github: @mahdiyazdani, @mypreview)
 * @copyright 		  © 2015 - 2018 MyPreview LLC. All Rights Reserved.
 *
 * @wordpress-plugin
 * Plugin Name:       Pofio
 * Plugin URI:        https://github.com/mypreview/pofio
 * Description:       Registers a custom post type along with tags and categories for portfolio projects.
 * Version:           1.0.0
 * Author:            MyPreview
 * Author URI:        https://www.mypreview.one
 * License:           GPL-3.0
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       pofio
 * Domain Path:       /languages
 */
// If this file is called directly, abort.
if ( !defined( 'WPINC' ) ):
    die;
endif;
/**
 * Current plugin version.
 * Started at version 1.0.0 and uses SemVer.
 * @see https://semver.org
 */
define( 'POFIO_VERSION', '1.0.0' );
/**
 * Portfolio CPT(custom post type) named constants.
 * @see http://php.net/manual/en/function.define.php
 */
define( 'POFIO_POST_TYPE', 'pofio-portfolio' );
define( 'POFIO_TAXONOMY_TYPE', 'pofio-portfolio-type' );
define( 'POFIO_TAXONOMY_TAG', 'pofio-portfolio-tag' );
/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-pofio-activator.php
 *
 * @see https://codex.wordpress.org/Function_Reference/register_activation_hook
 */
function activate_pofio() {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-pofio-activator.php';

    Pofio_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-pofio-deactivator.php
 *
 * @see https://codex.wordpress.org/Function_Reference/register_deactivation_hook
 */
function deactivate_pofio() {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-pofio-deactivator.php';

    Pofio_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_pofio' );
register_deactivation_hook( __FILE__, 'deactivate_pofio' );
/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-pofio.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_pofio() {
    $plugin = new Pofio();
    $plugin->run();
}

run_pofio();