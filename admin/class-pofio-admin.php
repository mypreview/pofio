<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    		Pofio
 * @subpackage 		Pofio/includes
 * @link       		https://github.com/mypreview/pofio
 * @author     		Mahdi Yazdani (Github: @mahdiyazdani, @mypreview)
 * @since      		1.0.0
 */
class Pofio_Admin {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param    $plugin_name   The name of this plugin.
     * @param    $version    	The version of this plugin.
     */
    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @link 	 https://codex.wordpress.org/Plugin_API/Action_Reference/admin_enqueue_scripts
     * @since    1.0.0
     * @access   public
     */
    public function enqueue_styles() {
        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Pofio_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Pofio_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/pofio-admin.css', array(), $this->version, 'all' );
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @link 	 https://codex.wordpress.org/Plugin_API/Action_Reference/admin_enqueue_scripts
     * @since    1.0.0
     * @access   public
     */
    public function enqueue_scripts() {
        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Pofio_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Pofio_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        wp_register_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/pofio-admin.js', array( 
            'jquery'
                 ), $this->version, FALSE );
        $pofio_admin_l10n = array( 
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'fg_add_to_gallery' => esc_html__( 'Add to Gallery', 'pofio' ),
            'fg_select_images_lbl' => esc_html__( 'Set featured gallery images', 'pofio' ),
            'fg_edit_images_lbl' => esc_html__( 'Click here to edit or update', 'pofio' ),
            'fg_remove_single_img' => esc_html__( 'Are you sure you want to remove this image?', 'pofio' ),
            'fg_remove_all_imgs' => esc_html__( 'Are you sure you want to remove all images?', 'pofio' ),
            'fg_ajax_error' => esc_html__( 'There was an issue with updating the live preview. Make sure that you click Save to ensure your changes aren\'t lost.', 'pofio' )
         );
        wp_localize_script( $this->plugin_name, 'pofioplusFGVars', $pofio_admin_l10n );
        wp_enqueue_script( $this->plugin_name );
    }

    /**
     * Add a portfolio number of posts field in `Settings` > `Reading`
     *
     * @link  	 https://codex.wordpress.org/Function_Reference/add_settings_section
     * @link  	 https://codex.wordpress.org/Function_Reference/add_settings_field
     * @link 	 https://developer.wordpress.org/reference/functions/register_setting/
     * @since    1.0.0
     * @access   public
     */
    public function settings_api_init() {
        // Bail out if the `portfolio` CPT already NOT registered by Pofio.
        if ( !post_type_exists( POFIO_POST_TYPE ) ):
            return;
        endif;
        add_settings_section( 'pofio_portfolio_cpt_section', '', '', 'reading' );
        add_settings_field( 'pofio_portfolio', __( 'Portfolio pages shows at most', 'pofio' ), array( 
            $this,
            'portfolio_per_page_setting_html'
                 ), 'reading', 'pofio_portfolio_cpt_section' );
        register_setting( 'reading', 'pofio_portfolio_per_page', 'intval' );
    }

    /**
     * Function that fills the portfolio posts per page field with
     * the desired numeric input as part of the larger reading page form.
     *
     * @link 	 https://codex.wordpress.org/Function_Reference/add_settings_field#Parameters
     * @since    1.0.0
     * @access   public
     * @return 	 html
     */
    public function portfolio_per_page_setting_html() {
        // Bail out if the `portfolio` CPT already NOT registered by Pofio.
        if ( !post_type_exists( POFIO_POST_TYPE ) ):
            return;
        endif;
        printf( '<p><label for="%1$s">%2$s</label></p>', esc_attr( 'pofio_portfolio_per_page' ),
                /* translators: %1$s is replaced with an input field for numbers */ sprintf( __( '%1$s projects', 'pofio' ), sprintf( '<input name="%1$s" id="%1$s" type="number" step="1" min="1" value="%2$s" class="small-text" />', esc_attr( 'pofio_portfolio_per_page' ), esc_attr( get_option( 'pofio_portfolio_per_page', '10' ) ) ) ) );
    }

    /**
     * Register portfolio custom post type with defined arguments.
     *
     * @link 	 https://codex.wordpress.org/Function_Reference/register_post_type
     * @since    1.0.0
     * @access   public
     */
    public function portfolio_cpt() {
        // Bail out if the `portfolio` CPT already registered by another plugin or theme.
        if ( post_type_exists( POFIO_POST_TYPE ) ):
            return;
        endif;
        register_post_type( POFIO_POST_TYPE, array( 
            'description' => __( 'Portfolio Items', 'pofio' ),
            'labels' => array( 
                'name' => esc_html__( 'Projects', 'pofio' ),
                'singular_name' => esc_html__( 'Project', 'pofio' ),
                'menu_name' => esc_html__( 'Portfolio', 'pofio' ),
                'all_items' => esc_html__( 'All Projects', 'pofio' ),
                'add_new' => esc_html__( 'Add New', 'pofio' ),
                'add_new_item' => esc_html__( 'Add New Project', 'pofio' ),
                'edit_item' => esc_html__( 'Edit Project', 'pofio' ),
                'new_item' => esc_html__( 'New Project', 'pofio' ),
                'view_item' => esc_html__( 'View Project', 'pofio' ),
                'search_items' => esc_html__( 'Search Projects', 'pofio' ),
                'not_found' => esc_html__( 'No Projects found', 'pofio' ),
                'not_found_in_trash' => esc_html__( 'No Projects found in Trash', 'pofio' ),
                'filter_items_list' => esc_html__( 'Filter projects list', 'pofio' ),
                'items_list_navigation' => esc_html__( 'Project list navigation', 'pofio' ),
                'items_list' => esc_html__( 'Projects list', 'pofio' ),
             ),
            'supports' => array( 
                'title',
                'editor',
                'thumbnail',
                'author',
                'comments',
                'publicize',
                'revisions',
             ),
            'rewrite' => array( 
                'slug' => 'portfolio',
                'with_front' => FALSE,
                'feeds' => TRUE,
                'pages' => TRUE,
             ),
            'public' => TRUE,
            'show_ui' => TRUE,
            'menu_position' => 20, // below Pages
            'menu_icon' => 'dashicons-portfolio', // 3.8+ dashicon option
            'capability_type' => 'page',
            'map_meta_cap' => TRUE,
            'taxonomies' => array( 
                POFIO_TAXONOMY_TYPE,
                POFIO_TAXONOMY_TAG
             ),
            'has_archive' => TRUE,
            'query_var' => 'portfolio',
            'show_in_rest' => TRUE,
         ) );
        register_taxonomy( POFIO_TAXONOMY_TYPE, POFIO_POST_TYPE, array( 
            'hierarchical' => TRUE,
            'labels' => array( 
                'name' => esc_html__( 'Project Types', 'pofio' ),
                'singular_name' => esc_html__( 'Project Type', 'pofio' ),
                'menu_name' => esc_html__( 'Project Types', 'pofio' ),
                'all_items' => esc_html__( 'All Project Types', 'pofio' ),
                'edit_item' => esc_html__( 'Edit Project Type', 'pofio' ),
                'view_item' => esc_html__( 'View Project Type', 'pofio' ),
                'update_item' => esc_html__( 'Update Project Type', 'pofio' ),
                'add_new_item' => esc_html__( 'Add New Project Type', 'pofio' ),
                'new_item_name' => esc_html__( 'New Project Type Name', 'pofio' ),
                'parent_item' => esc_html__( 'Parent Project Type', 'pofio' ),
                'parent_item_colon' => esc_html__( 'Parent Project Type:', 'pofio' ),
                'search_items' => esc_html__( 'Search Project Types', 'pofio' ),
                'items_list_navigation' => esc_html__( 'Project type list navigation', 'pofio' ),
                'items_list' => esc_html__( 'Project type list', 'pofio' ),
             ),
            'public' => TRUE,
            'show_ui' => TRUE,
            'show_in_nav_menus' => TRUE,
            'show_in_rest' => TRUE,
            'show_admin_column' => TRUE,
            'query_var' => TRUE,
            'rewrite' => array( 
                'slug' => 'project-type'
             ),
         ) );
        register_taxonomy( POFIO_TAXONOMY_TAG, POFIO_POST_TYPE, array( 
            'hierarchical' => FALSE,
            'labels' => array( 
                'name' => esc_html__( 'Project Tags', 'pofio' ),
                'singular_name' => esc_html__( 'Project Tag', 'pofio' ),
                'menu_name' => esc_html__( 'Project Tags', 'pofio' ),
                'all_items' => esc_html__( 'All Project Tags', 'pofio' ),
                'edit_item' => esc_html__( 'Edit Project Tag', 'pofio' ),
                'view_item' => esc_html__( 'View Project Tag', 'pofio' ),
                'update_item' => esc_html__( 'Update Project Tag', 'pofio' ),
                'add_new_item' => esc_html__( 'Add New Project Tag', 'pofio' ),
                'new_item_name' => esc_html__( 'New Project Tag Name', 'pofio' ),
                'search_items' => esc_html__( 'Search Project Tags', 'pofio' ),
                'popular_items' => esc_html__( 'Popular Project Tags', 'pofio' ),
                'separate_items_with_commas' => esc_html__( 'Separate tags with commas', 'pofio' ),
                'add_or_remove_items' => esc_html__( 'Add or remove tags', 'pofio' ),
                'choose_from_most_used' => esc_html__( 'Choose from the most used tags', 'pofio' ),
                'not_found' => esc_html__( 'No tags found.', 'pofio' ),
                'items_list_navigation' => esc_html__( 'Project tag list navigation', 'pofio' ),
                'items_list' => esc_html__( 'Project tag list', 'pofio' ),
             ),
            'public' => TRUE,
            'show_ui' => TRUE,
            'show_in_nav_menus' => TRUE,
            'show_in_rest' => TRUE,
            'show_admin_column' => TRUE,
            'query_var' => TRUE,
            'rewrite' => array( 
                'slug' => 'project-tag'
             ),
         ) );
    }

    /**
     * Add to REST API post type whitelist.
     *
     * @link 	 https://developer.jetpack.com/hooks/rest_api_allowed_post_types/
     * @since    1.0.0
     * @access   public
     */
    public function allow_portfolio_rest_api_type( $post_types ) {
        $post_types[] = POFIO_POST_TYPE;
        return $post_types;
    }

    /**
     * Count published projects and flush permalinks when first projects is published.
     *
     * @link 	 https://developer.wordpress.org/reference/functions/flush_rewrite_rules/
     * @since    1.0.0
     * @access   public
     */
    public function flush_rules_on_first_project() {
        $projects = get_transient( 'pofio-portfolio-count-cache' );
        if ( FALSE === $projects ):
            flush_rewrite_rules();
            $projects = ( int ) wp_count_posts( POFIO_POST_TYPE )->publish;
            if ( !empty( $projects ) ):
                set_transient( 'pofio-portfolio-count-cache', $projects, HOUR_IN_SECONDS * 12 );
            endif;
        endif;
    }

    /**
     * Flush permalinks when CPT supported theme is activated.
     *
     * @link 	 https://developer.wordpress.org/reference/functions/flush_rewrite_rules/
     * @since    1.0.0
     * @access   public
     */
    public function flush_rules_on_switch() {
        if ( current_theme_supports( POFIO_POST_TYPE ) ):
            flush_rewrite_rules();
        endif;
    }

    /**
     * Update messages for the Portfolio admin.
     *
     * @link 	 https://developer.wordpress.org/reference/hooks/post_updated_messages/
     * @since    1.0.0
     * @access   public
     * @return   array    List of possible messages while creating or updating existing project.
     */
    public function portfolio_updated_messages( $messages ) {
        global $post;
        $messages[POFIO_POST_TYPE] = array( 
            // Unused. Messages start at index 1.
            0 => '',
            1 => sprintf( __( 'Project updated. <a href="%s">View item</a>', 'pofio' ), esc_url( get_permalink( $post->ID ) ) ),
            2 => esc_html__( 'Custom field updated.', 'pofio' ),
            3 => esc_html__( 'Custom field deleted.', 'pofio' ),
            4 => esc_html__( 'Project updated.', 'pofio' ),
            /* translators: %s: date and time of the revision */
            5 => isset( $_GET['revision'] ) ? sprintf( esc_html__( 'Project restored to revision from %s', 'pofio' ), wp_post_revision_title( ( int ) $_GET['revision'], FALSE ) ) : FALSE,
            6 => sprintf( __( 'Project published. <a href="%s">View project</a>', 'pofio' ), esc_url( get_permalink( $post->ID ) ) ),
            7 => esc_html__( 'Project saved.', 'pofio' ),
            8 => sprintf( __( 'Project submitted. <a target="_blank" href="%s">Preview project</a>', 'pofio' ), esc_url( add_query_arg( 'preview', 'TRUE', get_permalink( $post->ID ) ) ) ),
            9 => sprintf( __( 'Project scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview project</a>', 'pofio' ),
                    /* translators: Publish box date format, @see http://php.net/date  */ date_i18n( __( 'M j, Y @ G:i', 'pofio' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post->ID ) ) ),
            10 => sprintf( __( 'Project item draft updated. <a target="_blank" href="%s">Preview project</a>', 'pofio' ), esc_url( add_query_arg( 'preview', 'TRUE', get_permalink( $post->ID ) ) ) ),
         );
        return $messages;
    }

    /**
     * Update `Title` column label.
     * Add Featured Image column.
     *
     * @link 	 https://codex.wordpress.org/Plugin_API/Filter_Reference/manage_posts_columns
     * @since    1.0.0
     * @access   public
     * @return   array    List of custom post type admin screen columns.
     */
    public function portfolio_edit_admin_columns( $columns ) {
        // change 'Title' to 'Project'
        $columns['title'] = __( 'Project', 'pofio' );
        if ( current_theme_supports( 'post-thumbnails' ) ):
            // Add featured image before 'Project'
            $columns = array_slice( $columns, 0, 1, TRUE ) + array( 
                'thumbnail' => ''
                     ) + array_slice( $columns, 1, NULL, TRUE );
        endif;
        return $columns;
    }

    /**
     * Add featured image to column.
     *
     * @link 	 https://codex.wordpress.org/Plugin_API/Action_Reference/manage_posts_custom_column
     * @since    1.0.0
     * @access   public
     */
    public function portfolio_image_column( $column, $post_id ) {
        global $post;
        switch ( $column ):
            case 'thumbnail':
                echo get_the_post_thumbnail( $post_id, 'pofio-portfolio-admin-thumb' );
                break;
        endswitch;
    }

    /**
     * Follow CPT reading setting on CPT archive and taxonomy pages.
     *
     * @link 	 https://codex.wordpress.org/Function_Reference/register_post_type
     * @since    1.0.0
     * @access   public
     */
    public function portfolio_query_reading_setting( $query ) {
        if ( !current_user_can( 'manage_options' ) && $query->is_main_query() && ( $query->is_post_type_archive( POFIO_POST_TYPE ) || $query->is_tax( POFIO_TAXONOMY_TYPE ) || $query->is_tax( POFIO_TAXONOMY_TAG ) ) ):
            $query->set( 'posts_per_page', get_option( 'pofio_portfolio_per_page', '10' ) );
        endif;
    }

    /**
     * Register featured gallery items metabox.
     *
     * @link 	 https://developer.wordpress.org/reference/functions/add_meta_box/
     * @since    1.0.0
     * @access   public
     */
    public function featured_gallery_metabox() {
        add_meta_box( 'pofio-fg-wrapper', __( 'Featured Gallery', 'pofio' ), array( 
            $this,
            'featured_gallery_html'
                 ), POFIO_POST_TYPE, 'side', 'low' );
    }

    /**
     * Function that fills the portfolio featured gallery metabox with
     * the desired media gallery uploader as part of the larger portfolio edit page.
     *
     * @link 	 https://developer.wordpress.org/reference/functions/add_meta_box/#parameters
     * @since    1.0.0
     * @access   public
     */
    public function featured_gallery_html() {
        global $post;
        $gallery_items = '';
        $gallery_ids = Pofio_Public::get_post_gallery_ids( $post->ID );
        $gallery_strings = Pofio_Public::get_post_gallery_ids( $post->ID, 'string' );
        $select_btn_text = ( $gallery_strings ) ? __( 'Click here to edit or update', 'pofio' ) : __( 'Set featured gallery images', 'pofio' );
        $select_btn_visibility = ( $gallery_strings ) ? 'hidden' : '';
        $remove_btn_visibility = ( $gallery_strings ) ? '' : 'hidden';
        printf( '<input type="hidden" name="pofio_fg_temp_nonce_data" id="pofio_fg_temp_nonce_data" value="%1$s" />', wp_create_nonce( 'fg_temp_noncevalue' ) );
        printf( '<input type="hidden" name="pofio_fg_perm_nonce_data" id="pofio_fg_perm_nonce_data" value="%1$s" />', wp_create_nonce( plugin_basename( __FILE__ ) ) );
        printf( '<input type="hidden" name="pofio_fg_perm_meta_data" id="pofio_fg_perm_meta_data" value="%1$s" data-post_id="%2$s" />', $gallery_strings, $post->ID );
        if ( !empty( $gallery_strings ) ):
            foreach ( $gallery_ids as & $id ):
                $gallery_items .= sprintf( '<li><button class="remove-item">&times;</button><img id="%1$s" src="%2$s"></li>', $id, wp_get_attachment_url( $id ) );
            endforeach;
        endif;
        // Overwrite the temporary featured gallery data with the permanent data.
        update_post_meta( $post->ID, 'pofio_fg_temp_meta_data', $gallery_strings );
        printf( '<ul>%1$s</ul><div class="clearfix"></div>', $gallery_items );
        printf( '<p class="%1$s"><a href="#" id="pofio_fg_select">%2$s</a></p>', $select_btn_visibility, $select_btn_text );
        printf( '<p class="%1$s howto">%2$s</p>', $remove_btn_visibility, __( 'Click the image to edit or update', 'pofio' ) );
        printf( '<p class="%1$s"><a href="#" id="pofio_fg_remove_all">%2$s</a></p>', $remove_btn_visibility, __( 'Remove featured gallery images', 'pofio' ) );
    }

    /**
     * Action which triggers whenever a portfolio project is created or updated.
     *
     * @link 	 https://codex.wordpress.org/Plugin_API/Action_Reference/save_post
     * @since    1.0.0
     * @access   public
     */
    public function save_featured_gallery_items( $post_id, $post ) {
        // Bail out if running an autosave, ajax, cron.
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ):
            return;
        endif;
        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ):
            return;
        endif;
        if ( defined( 'DOING_CRON' ) && DOING_CRON ):
            return;
        endif;
        // Only run the call when updating a Featured Gallery.
        if ( empty( $_POST['pofio_fg_perm_nonce_data'] ) ):
            return;
        endif;
        // Bail out if the the nonce is incorrect or already expired.
        if ( !wp_verify_nonce( $_POST['pofio_fg_perm_nonce_data'], plugin_basename( __FILE__ ) ) ):
            return;
        endif;
        // Check user has enough permission to edit a project( post ).
        if ( !current_user_can( 'edit_post', $post->ID ) ):
            return;
        endif;
        // OK, we're authenticated: we need to find and save the data
        $events_meta['pofio_fg_perm_meta_data'] = $_POST['pofio_fg_perm_meta_data'];
        // Add values of $events_meta as custom fields
        foreach ( $events_meta as $key => $value ):
            if ( $post->post_type == 'revision' ):
                return;
            endif;
            $value = implode( ',', ( array ) $value );
            if ( get_post_meta( $post->ID, $key, FALSE ) ):
                update_post_meta( $post->ID, $key, $value );
            else:
                add_post_meta( $post->ID, $key, $value );
            endif;
            if ( !$value ):
                delete_post_meta( $post->ID, $key );
            endif;
        endforeach;
    }

    /**
     * Allows to handle portfolio featured gallery items using custom AJAX requests.
     *
     * @link 	 https://codex.wordpress.org/Plugin_API/Action_Reference/wp_ajax_( action )
     * @since    1.0.0
     * @access   public
     */
    public function pofio_fg_update_temp() {
        // Bail out if the `portfolio` CPT already NOT registered by Pofio.
        if ( !post_type_exists( POFIO_POST_TYPE ) ):
            return;
        endif;
        // Bail out if the the nonce is incorrect or already expired.
        if ( !wp_verify_nonce( $_REQUEST['pofio_fg_temp_nonce_data'], 'fg_temp_noncevalue' ) ):
            return;
        endif;
        // Bail out if the post id doesn't exists or user doesn't have enough permission to edit the post.
        if ( !current_user_can( 'edit_post', $_REQUEST['fg_post_id'] ) ):
            return;
        endif;
        $newValue = $_REQUEST['pofio_fg_temp_meta_data'];
        $oldValue = get_post_meta( $_REQUEST['fg_post_id'], 'pofio_fg_temp_meta_data', 1 );
        $response = 'success';
        if ( $newValue !== $oldValue ):
            $success = update_post_meta( $_REQUEST['fg_post_id'], 'pofio_fg_temp_meta_data', $newValue );
            if ( $success == FALSE ):
                $response = 'error';
            endif;
        endif;
        echo json_encode( $response );
        die();
    }

    /**
     * Replace the title field placeholder with custom content.
     *
     * @link 	 https://developer.wordpress.org/reference/hooks/enter_title_here/
     * @since    1.0.0
     * @access   public
     * @return   string    The custom content for field placeholder.
     */
    public function portfolio_title_placeholder( $title ) {
        // Bail out if the `portfolio` CPT already NOT registered by Pofio.
        if ( !post_type_exists( POFIO_POST_TYPE ) ):
            return;
        endif;
        // Bail out if the current screen is NOT `portfolio` custom post type.
        if ( POFIO_POST_TYPE !== get_current_screen()->post_type ):
            return;
        endif;
        $title = esc_html__( 'Start by writing a compelling title that describes your project', 'pofio' );
        return $title;
    }

    /**
     * Append the subtitle input field right after title field.
     *
     * @link 	 https://developer.wordpress.org/reference/hooks/edit_form_after_title/
     * @since    1.0.0
     * @access   public
     */
    public function portfolio_subtitle() {
        // Bail out if the `portfolio` CPT already NOT registered by Pofio.
        if ( !post_type_exists( POFIO_POST_TYPE ) ):
            return;
        endif;
        // Bail out if the current screen is NOT `portfolio` custom post type.
        if ( POFIO_POST_TYPE !== get_current_screen()->post_type ):
            return;
        endif;
        global $post;
        // create the meta field ( don't use a metabox, we have our own styling ):
        wp_nonce_field( plugin_basename( __FILE__ ), 'pofio_subtitle_nonce' );
        $get_subtitle = get_post_meta( $post->ID, 'pofio_subtitle', TRUE );
        printf( '<input type="text" class="widefat" name="pofio_subtitle_meta_data" placeholder="%s" value="%s" id="pofio_subtitle_meta_data" />', esc_html__( 'What was your client\'s name on this project?', 'pofio' ), esc_html( $get_subtitle ) );
    }

    /**
     * Action which triggers whenever a portfolio project is created or updated.
     *
     * @link 	 https://developer.wordpress.org/reference/hooks/edit_form_after_title/
     * @since    1.0.0
     * @access   public
     */
    public function save_portfolio_subtitle( $post_id, $post ) {
        // Bail out if running an autosave, ajax, cron.
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ):
            return;
        endif;
        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ):
            return;
        endif;
        if ( defined( 'DOING_CRON' ) && DOING_CRON ):
            return;
        endif;
        // Only run the call when updating a subtitle field.
        if ( empty( $_POST['pofio_subtitle_nonce'] ) ):
            return;
        endif;
        // Bail out if the the nonce is incorrect or already expired.
        if ( !wp_verify_nonce( $_POST['pofio_subtitle_nonce'], plugin_basename( __FILE__ ) ) ):
            return;
        endif;
        // Check user has enough permission to edit a project( post ).
        if ( !current_user_can( 'edit_post', $post->ID ) ):
            return;
        endif;
        // OK, we're authenticated: we need to find and save the data
        if ( in_array( trim( $_POST['pofio_subtitle_meta_data'] ), array( 
                    esc_html__( 'What was your client\'s name on this project?', 'pofio' ),
                    ''
                 ) ) ):
            delete_post_meta( $post_id, 'pofio_subtitle' );
        else:
            update_post_meta( $post_id, 'pofio_subtitle', sanitize_post_field( 'post_title', $_POST['pofio_subtitle_meta_data'], $post_id, 'db' ) );
        endif;
    }

}