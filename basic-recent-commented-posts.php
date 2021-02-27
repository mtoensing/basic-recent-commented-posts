<?php
/*
Plugin Name: Basic Recent Commented Posts Widget
Plugin URI: http://www.marc.tv/marctv-wordpress-plugins/
Description: Lists the last commented posts in a widget.
Version: 2.2
Author: Marc Tönsing
Author URI: https://marc.tv
License: GPL2
*/


// Prevent direct file access
if ( ! defined ( 'ABSPATH' ) ) {
	exit;
}

require_once plugin_dir_path( __FILE__ ) . 'includes/class-widget-recent-commented-posts-util.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-widget-recent-commented-posts.php';

class Recent_Commented_Posts_Plugin {

    public function __construct() {

        // Load the text domain - should go on 'plugins_loaded' hook to make sure strings load prior to register_widget call
        add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );

        // Register the widget
        add_action( 'widgets_init', array( $this, 'register_widget' ) );

    }

    public function load_textdomain() {
        load_plugin_textdomain( 'recent-commented-posts', false, dirname( plugin_basename( __FILE__  ) ) . '/languages' );
    }

    public function register_widget() {
        if ( class_exists( 'Widget_Recent_Commented_Posts') ) {
            register_widget( 'Widget_Recent_Commented_Posts' );
        }
    }

} // end class
$Recent_Commented_Posts = new Recent_Commented_Posts_Plugin();
