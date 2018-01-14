<?php

/**
 * Register all actions and filters for the plugin.
 *
 * Maintain a list of all hooks that are registered throughout
 * the plugin, and register them with the WordPress API. Call the
 * run function to execute the list of actions and filters.
 *
 * @package    		Pofio
 * @subpackage 		Pofio/includes
 * @link       		https://github.com/mypreview/pofio
 * @author     		Mahdi Yazdani (Github: @mahdiyazdani, @mypreview)
 * @since      		1.0.0
 */
class Pofio_Loader {

    /**
     * The array of actions registered with WordPress.
     *
     * @since    1.0.0
     * @access   protected
     * @var      array    $actions    The actions registered with WordPress to fire when the plugin loads.
     */
    protected $actions;

    /**
     * The array of filters registered with WordPress.
     *
     * @since    1.0.0
     * @access   protected
     * @var      array    $filters    The filters registered with WordPress to fire when the plugin loads.
     */
    protected $filters;

    /**
     * Initialize the collections used to maintain the actions and filters.
     *
     * @since    1.0.0
     */
    public function __construct() {
        $this->actions = array();
        $this->filters = array();
    }

    /**
     * Add a new action to the collection to be registered with WordPress.
     *
     * @since    1.0.0
     * @param    $hook             The name of the WordPress action that is being registered.
     * @param    $component        A reference to the instance of the object on which the action is defined.
     * @param    $callback         The name of the function definition on the $component.
     * @param    $priority         Optional. The priority at which the function should be fired. Default is 10.
     * @param    $accepted_args    Optional. The number of arguments that should be passed to the $callback. Default is 1.
     */
    public function add_action( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
        $this->actions = $this->add( $this->actions, $hook, $component, $callback, $priority, $accepted_args );
    }

    /**
     * Add a new filter to the collection to be registered with WordPress.
     *
     * @since    1.0.0
     * @param    $hook             The name of the WordPress filter that is being registered.
     * @param    $component        A reference to the instance of the object on which the filter is defined.
     * @param    $callback         The name of the function definition on the $component.
     * @param    $priority         Optional. The priority at which the function should be fired. Default is 10.
     * @param    $accepted_args    Optional. The number of arguments that should be passed to the $callback. Default is 1
     */
    public function add_filter( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
        $this->filters = $this->add( $this->filters, $hook, $component, $callback, $priority, $accepted_args );
    }

    /**
     * A utility function that is used to register the actions and hooks into a single
     * collection.
     *
     * @since    1.0.0
     * @access   private
     * @param    $hooks            The collection of hooks that is being registered ( that is, actions or filters ).
     * @param    $hook             The name of the WordPress filter that is being registered.
     * @param    $component        A reference to the instance of the object on which the filter is defined.
     * @param    $callback         The name of the function definition on the $component.
     * @param    $priority         The priority at which the function should be fired.
     * @param    $accepted_args    The number of arguments that should be passed to the $callback.
     * 
     * @return   The collection of actions and filters registered with WordPress.
     */
    private function add( $hooks, $hook, $component, $callback, $priority, $accepted_args ) {
        $hooks[] = array( 
            'hook' => $hook,
            'component' => $component,
            'callback' => $callback,
            'priority' => $priority,
            'accepted_args' => $accepted_args
         );
        return $hooks;
    }

    /**
     * Register the filters and actions with WordPress.
     *
     * @since    1.0.0
     */
    public function run() {
        foreach ( $this->filters as $hook ):
            add_filter( $hook['hook'], array( 
                $hook['component'],
                $hook['callback']
                     ), $hook['priority'], $hook['accepted_args'] );
        endforeach;
        foreach ( $this->actions as $hook ):
            add_action( $hook['hook'], array( 
                $hook['component'],
                $hook['callback']
                     ), $hook['priority'], $hook['accepted_args'] );
        endforeach;
    }

}