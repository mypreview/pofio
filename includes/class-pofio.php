<?php

/**
 * The core plugin class.
 * The file that defines the core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @package    		Pofio
 * @subpackage 		Pofio/includes
 * @link       		https://github.com/mypreview/pofio
 * @author     		Mahdi Yazdani (Github: @mahdiyazdani, @mypreview)
 * @since      		1.0.0
 */
class Pofio {

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      $version    The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     * @access   public
     */
    public function __construct() {
        if ( defined( 'POFIO_VERSION' ) ):
            $this->version = POFIO_VERSION;
        else:
            $this->version = '1.0.0';
        endif;
        $this->plugin_name = 'pofio';
        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    /**
     * Log the plugin version number.
     *
     * @since 	 1.0.0
     * @access   public
     */
    public function _log_version_number() {
        update_option( 'pofio-version', POFIO_VERSION );
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - Pofio_Loader. Orchestrates the hooks of the plugin.
     * - Pofio_i18n. Defines internationalization functionality.
     * - Pofio_Admin. Defines all hooks for the admin area.
     * - Pofio_Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() {
        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-pofio-loader.php';

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-pofio-i18n.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-pofio-admin.php';

        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-pofio-public.php';

        $this->loader = new Pofio_Loader();
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the Pofio_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale() {
        $plugin_i18n = new Pofio_i18n();
        $this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks() {
        $plugin_admin = new Pofio_Admin( $this->get_plugin_name(), $this->get_version() );
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
        add_image_size( 'pofio-portfolio-admin-thumb', 90, 90, TRUE );
        $plugin_admin = new Pofio_Admin( $this->get_plugin_name(), $this->get_version() );
        $this->loader->add_action( 'admin_init', $plugin_admin, 'settings_api_init', 10 );
        // Make sure the post types are loaded for imports
        $this->loader->add_action( 'import_start', $plugin_admin, 'portfolio_cpt', 10 );
        $this->loader->add_action( 'init', $plugin_admin, 'portfolio_cpt', 10 );
        // Add to REST API post type whitelist
        $this->loader->add_filter( 'rest_api_allowed_post_types', $plugin_admin, 'allow_portfolio_rest_api_type', 10, 1 );
        $this->loader->add_action( sprintf( 'publish_%s', POFIO_POST_TYPE ), $plugin_admin, 'portfolio_cpt', 10 );
        $this->loader->add_action( 'after_switch_theme', $plugin_admin, 'flush_rules_on_switch', 10 );
        $this->loader->add_filter( 'post_updated_messages', $plugin_admin, 'portfolio_updated_messages', 10, 1 );
        $this->loader->add_filter( sprintf( 'manage_%s_posts_columns', POFIO_POST_TYPE ), $plugin_admin, 'portfolio_edit_admin_columns', 10, 1 );
        $this->loader->add_filter( sprintf( 'manage_%s_posts_custom_column', POFIO_POST_TYPE ), $plugin_admin, 'portfolio_image_column', 10, 2 );
        // Adjust CPT archive and custom taxonomies to obey CPT reading setting
        $this->loader->add_filter( 'pre_get_posts', $plugin_admin, 'portfolio_query_reading_setting', 10, 1 );
        $this->loader->add_action( 'add_meta_boxes', $plugin_admin, 'featured_gallery_metabox', 10 );
        $this->loader->add_action( 'save_post', $plugin_admin, 'save_featured_gallery_items', 10, 2 );
        $this->loader->add_action( 'wp_ajax_pofio_fg_update_temp', $plugin_admin, 'pofio_fg_update_temp', 10 );
        $this->loader->add_filter( 'enter_title_here', $plugin_admin, 'portfolio_title_placeholder', 10, 1 );
        $this->loader->add_action( 'edit_form_after_title', $plugin_admin, 'portfolio_subtitle', 10 );
        $this->loader->add_action( 'save_post', $plugin_admin, 'save_portfolio_subtitle', 10, 2 );
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks() {
        $plugin_public = new Pofio_Public( $this->get_plugin_name(), $this->get_version() );
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     * @access   public
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @access    public
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     * @access    public
     * @return    Pofio_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @access    public
     * @return    string    The version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }

}