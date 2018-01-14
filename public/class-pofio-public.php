<?php

/**
 * The public-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-specific stylesheet and JavaScript.
 *
 * @package    		Pofio
 * @subpackage 		Pofio/includes
 * @link       		https://github.com/mypreview/pofio
 * @author     		Mahdi Yazdani (Github: @mahdiyazdani, @mypreview)
 * @since      		1.0.0
 */
class Pofio_Public {

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
        add_shortcode( 'pofio', array( $this, 'portfolio_shortcode' ), 10 );
    }

    /**
     * [pofio] shortcode.
     *
     * @see 	 https://codex.wordpress.org/Function_Reference/shortcode_atts
     * @since    1.0.0
     * @access   public
     * @param    $atts   User defined attributes in shortcode tag.
     * @return   portfolio_shortcode_html
     */
    public static function portfolio_shortcode( $atts ) {
        // Default attributes
        $atts = shortcode_atts( apply_filters( 'pofio_shortcode_atts', array( 
            'display_types' => TRUE,
            'display_tags' => TRUE,
            'display_content' => TRUE,
            'display_author' => FALSE,
            'show_filter' => FALSE,
            'include_type' => FALSE,
            'include_tag' => FALSE,
            'columns' => 2,
            'order' => 'asc',
            'orderby' => 'date',
                 ) ), $atts, 'portfolio' );

        if ( $atts['display_types'] && 'TRUE' != $atts['display_types'] ):
            $atts['display_types'] = FALSE;
        endif;
        if ( $atts['display_tags'] && 'TRUE' != $atts['display_tags'] ):
            $atts['display_tags'] = FALSE;
        endif;
        if ( $atts['display_author'] && 'TRUE' != $atts['display_author'] ):
            $atts['display_author'] = FALSE;
        endif;
        if ( $atts['display_content'] && 'TRUE' != $atts['display_content'] && 'full' != $atts['display_content'] ):
            $atts['display_content'] = FALSE;
        endif;
        if ( $atts['include_type'] ):
            $atts['include_type'] = explode( ',', str_replace( ' ', '', $atts['include_type'] ) );
        endif;
        if ( $atts['include_tag'] ):
            $atts['include_tag'] = explode( ',', str_replace( ' ', '', $atts['include_tag'] ) );
        endif;
        $atts['columns'] = absint( $atts['columns'] );
        if ( $atts['order'] ):
            $atts['order'] = urldecode( $atts['order'] );
            $atts['order'] = strtoupper( $atts['order'] );
            if ( 'DESC' != $atts['order'] ):
                $atts['order'] = 'ASC';
            endif;
        endif;
        if ( $atts['orderby'] ):
            $atts['orderby'] = urldecode( $atts['orderby'] );
            $atts['orderby'] = strtolower( $atts['orderby'] );
            $allowed_keys = array( 'author', 'date', 'title', 'rand' );
            $parsed = array();
            foreach ( explode( ',', $atts['orderby'] ) as $portfolio_index_number => $orderby ):
                if ( !in_array( $orderby, $allowed_keys ) ):
                    continue;
                endif;
                $parsed[] = $orderby;
            endforeach;
            if ( empty( $parsed ) ):
                unset( $atts['orderby'] );
            else:
                $atts['orderby'] = implode( ' ', $parsed );
            endif;
        endif;
        return self::portfolio_shortcode_html( $atts );
    }

    /**
     * Query to retrieve entries from the Portfolio post_type.
     *
     * @see 	 https://codex.wordpress.org/Class_Reference/WP_Query
     * @since    1.0.0
     * @access   public
     * @param    $atts   User defined attributes in shortcode tag.
     * @return   object
     */
    public static function portfolio_query( $atts ) {
        // Default query arguments
        $default = array( 
            'order' => $atts['order'],
            'orderby' => $atts['orderby'],
            'posts_per_page' => intval( get_option( 'pofio_portfolio_per_page', '10' ) )
         );
        $args = wp_parse_args( $atts, $default );
        // Force this post type
        $args['post_type'] = POFIO_POST_TYPE;
        if ( FALSE != $atts['include_type'] || FALSE != $atts['include_tag'] ):
            $args['tax_query'] = array();
        endif;
        // If 'include_type' has been set use it on the main query
        if ( FALSE != $atts['include_type'] ):
            array_push( $args['tax_query'], array( 
                'taxonomy' => POFIO_TAXONOMY_TYPE,
                'field' => 'slug',
                'terms' => $atts['include_type']
             ) );
        endif;
        // If 'include_tag' has been set use it on the main query
        if ( FALSE != $atts['include_tag'] ):
            array_push( $args['tax_query'], array( 
                'taxonomy' => POFIO_TAXONOMY_TAG,
                'field' => 'slug',
                'terms' => $atts['include_tag']
             ) );
        endif;
        if ( FALSE != $atts['include_type'] && FALSE != $atts['include_tag'] ):
            $args['tax_query']['relation'] = 'AND';
        endif;
        // Run the query and return
        $query = new WP_Query( $args );
        return $query;
    }

    /**
     *  The Portfolio shortcode loop.
     *
     * @see 	 https://developer.wordpress.org/reference/functions/get_the_terms/
     * @since    1.0.0
     * @access   public
     * @return   html
     */
    public static function portfolio_shortcode_html( $atts ) {
        $query = self::portfolio_query( $atts );
        $portfolio_index_number = 0;
        ob_start();
        // If we have posts, create the html with the portfolio markup
        if ( $query->have_posts() ):
            ?>
            <div class="pofio-shortcode column-<?php echo esc_attr( $atts['columns'] ); ?>">
            <?php
            while ( $query->have_posts() ):
                $query->the_post();
                $post_id = get_the_ID();
                ?>
                    <div class="pofio-entry <?php echo esc_attr( self::get_project_class( $portfolio_index_number, $atts['columns'] ) ); ?>">
                        <header class="pofio-entry-header">
                    <?php
                    // Featured image
                    echo self::get_project_thumbnail_link( $post_id );
                    ?>

                            <h2 class="pofio-entry-title"><a href="<?php echo esc_url( get_permalink() ); ?>" title="<?php echo esc_attr( the_title_attribute() ); ?>"><?php the_title(); ?></a></h2>

                            <div class="pofio-entry-meta">
                <?php
                if ( FALSE != $atts['display_types'] ):
                    echo self::get_project_type( $post_id );
                endif;
                if ( FALSE != $atts['display_tags'] ):
                    echo self::get_project_tags( $post_id );
                endif;
                if ( FALSE != $atts['display_author'] ):
                    echo self::get_project_author( $post_id );
                endif;
                ?>
                            </div>

                        </header>

                <?php
                // The content
                if ( FALSE !== $atts['display_content'] ):
                    if ( 'full' === $atts['display_content'] ):
                        ?>
                                <div class="pofio-entry-content"><?php the_content(); ?></div>
                                <?php
                            else:
                                ?>
                                <div class="pofio-entry-content"><?php the_excerpt(); ?></div>
                            <?php
                            endif;
                        endif;
                        ?>
                    </div><!-- close .pofio-entry -->
                        <?php
                        $portfolio_index_number++;
                    endwhile; // end of while loop
                    wp_reset_postdata();
                    ?>
            </div><!-- close .pofio-shortcode -->
            <?php else: ?>
            <p><em><?php _e( 'Your Portfolio Archive currently has no entries. You can start creating them on your dashboard.', 'pofio' ); ?></p></em>
        <?php
        endif;
        $html = ob_get_clean();
        // If there is a [pofio] within a [pofio], remove the shortcode
        if ( has_shortcode( $html, 'pofio' ) ):
            remove_shortcode( 'pofio' );
        endif;
        // Return the HTML block
        return $html;
    }

    /**
     * Individual project class.
     *
     * @see 	 https://codex.wordpress.org/Function_Reference/wp_get_object_terms
     * @since    1.0.0
     * @access   public
     * @return   string
     */
    public static function get_project_class( $portfolio_index_number, $columns ) {
        $project_types = wp_get_object_terms( get_the_ID(), POFIO_TAXONOMY_TYPE, array( 'fields' => 'slugs' ) );
        $class = array();
        $class[] = 'pofio-entry-column-' . $columns;
        // add a type- class for each project type
        foreach ( $project_types as $project_type ):
            $class[] = 'type-' . esc_html( $project_type );
        endforeach;
        if ( $columns > 1 ):
            if ( (  $portfolio_index_number % 2  ) == 0 ):
                $class[] = 'pofio-entry-mobile-first-item-row';
            else:
                $class[] = 'pofio-entry-mobile-last-item-row';
            endif;
        endif;
        // add first and last classes to first and last items in a row
        if ( (  $portfolio_index_number % $columns  ) == 0 ):
            $class[] = 'pofio-entry-first-item-row';
        elseif ( (  $portfolio_index_number % $columns  ) == (  $columns - 1  ) ):
            $class[] = 'pofio-entry-last-item-row';
        endif;

        return apply_filters( 'pofio-project-post-class', implode( " ", $class ), $portfolio_index_number, $columns );
    }

    /**
     * Displays the project type that a project belongs to.
     *
     * @see 	 https://developer.wordpress.org/reference/functions/get_the_terms/
     * @since    1.0.0
     * @access   public
     * @return   html
     */
    public static function get_project_type( $post_id ) {
        $project_types = get_the_terms( $post_id, POFIO_TAXONOMY_TYPE );
        // If no types, return empty string
        if ( empty( $project_types ) || is_wp_error( $project_types ) ) {
            return;
        }
        $html = '<div class="pofio-project-types"><span>' . __( 'Types', 'pofio' ) . ':</span>';
        $types = array();
        // Loop thorugh all the types
        foreach ( $project_types as $project_type ) {
            $project_type_link = get_term_link( $project_type, POFIO_POST_TYPE );
            if ( is_wp_error( $project_type_link ) ) {
                return $project_type_link;
            }
            $types[] = '<a href="' . esc_url( $project_type_link ) . '" rel="tag">' . esc_html( $project_type->name ) . '</a>';
        }
        $html .= ' ' . implode( ', ', $types );
        $html .= '</div>';
        return $html;
    }

    /**
     * Displays the project tags that a project belongs to.
     *
     * @see 	 https://developer.wordpress.org/reference/functions/get_the_terms/
     * @since    1.0.0
     * @access   public
     * @return   html
     */
    public static function get_project_tags( $post_id ) {
        $project_tags = get_the_terms( $post_id, POFIO_TAXONOMY_TAG );
        // If no tags, return empty string
        if ( empty( $project_tags ) || is_wp_error( $project_tags ) ) {
            return FALSE;
        }
        $html = '<div class="pofio-project-tags"><span>' . __( 'Tags', 'pofio' ) . ':</span>';
        $tags = array();
        // Loop thorugh all the tags
        foreach ( $project_tags as $project_tag ):
            $project_tag_link = get_term_link( $project_tag, POFIO_POST_TYPE );
            if ( is_wp_error( $project_tag_link ) ):
                return $project_tag_link;
            endif;
            $tags[] = '<a href="' . esc_url( $project_tag_link ) . '" rel="tag">' . esc_html( $project_tag->name ) . '</a>';
        endforeach;
        $html .= ' ' . implode( ', ', $tags );
        $html .= '</div>';
        return $html;
    }

    /**
     * Displays the author of the current portfolio project.
     *
     * @see 	 https://codex.wordpress.org/Function_Reference/get_the_author
     * @since    1.0.0
     * @access   public
     * @return   html
     */
    public static function get_project_author() {
        $html = '<div class="pofio-project-author">';
        /* translators: %1$s is link to author posts, %2$s is author display name */
        $html .= sprintf( __( '<span>Author:</span> <a href="%1$s">%2$s</a>', 'pofio' ), esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ), esc_html( get_the_author() )
         );
        $html .= '</div>';
        return $html;
    }

    /**
     * Display the featured image if it's available.
     *
     * @see 	 https://developer.wordpress.org/reference/functions/has_post_thumbnail/
     * @since    1.0.0
     * @access   public
     * @return   html
     */
    public static function get_project_thumbnail_link( $post_id ) {
        return '<a class="pofio-featured-image" href="' . esc_url( get_permalink( $post_id ) ) . '">' . get_the_post_thumbnail( $post_id, apply_filters( 'pofio_thumbnail_size', 'large' ) ) . '</a>';
    }

    /**
     * Retrieve post meta featured gallery field for a portfolio project.
     *
     * @see 	 https://developer.wordpress.org/reference/functions/get_post_meta/
     * @since    1.0.0
     * @access   public
     * @return   string    The IDs of selected images ( comma separated ) by featured gallery metabox.
     */
    public static function get_post_gallery_ids( $id, $max_images = - 1, $method = 'array' ) {
        if ( is_preview( $id ) ):
            $gallery_strings = get_post_meta( $id, 'pofio_fg_perm_meta_data', 1 );
        else:
            $gallery_strings = get_post_meta( $id, 'pofio_fg_perm_meta_data', 1 );
        endif;
        if ( 'string' === $method || 'string' === $max_images ):
            return $gallery_strings;
        else:
            if ( !$gallery_strings ):
                return array();
            else:
                if ( $max_images == - 1 ):
                    return explode( ',', $gallery_strings );
                else:
                    return array_slice( explode( ',', $gallery_strings ), 0, $max_images );
                endif;
            endif;
        endif;
    }

}