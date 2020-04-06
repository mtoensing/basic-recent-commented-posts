<?php

// Prevent direct file access
if ( ! defined ( 'ABSPATH' ) ) {
	exit;
}

class Widget_Recent_Commented_Posts extends WP_Widget {

    // A unique identifier for the widget
    private $widget_slug = 'recent_commented_posts';
            
	public function __construct() {

		parent::__construct(
			$this->widget_slug,
			__( 'Recent Commented Posts', 'recent-commented-posts' ),
			array(
				'classname'  => 'widget_recent_comments',
				'description' => __( 'list of the last commented posts.', 'recent-commented-posts' )
			)
		);

		add_action( 'comment_post', array( $this, 'flush_widget_cache' ) );
		add_action( 'edit_comment', array( $this, 'flush_widget_cache' ) );
		add_action( 'transition_comment_status', array( $this, 'flush_widget_cache' ) );
        
	} // end constructor
    
	public function flush_widget_cache() {
    	wp_cache_delete( $this->widget_slug, 'widget' );
	}
    
	/**
	 * Outputs the content of the widget.
	 *
	 * @param array args  The array of form elements
	 * @param array instance The current instance of the widget
	 */
	public function widget( $args, $instance ) {
		
		// Check if there is a cached output
		$cache = wp_cache_get( $this->widget_slug, 'widget' );

		if ( !is_array( $cache ) ) {
			$cache = array();
        }

		if ( ! isset ( $args['widget_id'] ) ) {
			$args['widget_id'] = $this->id;
        }

		if ( isset ( $cache[ $args['widget_id'] ] ) ) {
			echo $cache[ $args['widget_id'] ];
            return;
        }
        
        $output = $args['before_widget'];

        $title = ( !empty( $instance['title'] ) ) ? $instance['title'] : __( 'Recent Commented Posts', 'recent-commented-posts' );
        // This filter is documented in wp-includes/default-widgets.php
        $title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

        if ( $title ) {
            $output .= $args['before_title'] . $title . $args['after_title'];
        }
        
        $output .= Recent_Commented_Posts_Util::get_recent_commented_posts( $instance );
        
        $output .= $args['after_widget'];
        
		echo $output;
        
        if ( !$this->is_preview() ) {
            $cache[ $args['widget_id'] ] = $output;
            wp_cache_set( $this->widget_slug, $cache, 'widget' );
        }

	} // end widget
    
	/**
	 * Processes the widget's options to be saved.
	 *
	 * @param array new_instance The new instance of values to be generated via the update.
	 * @param array old_instance The previous instance of values before the update.
	 */
	public function update( $new_instance, $old_instance ) {

		$instance = $old_instance;

		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['number'] = absint( $new_instance['number'] );
        
		$this->flush_widget_cache();
		return $instance;

	} // end widget

	/**
	 * Generates the administration form for the widget.
	 *
	 * @param array instance The array of keys and values for the widget.
	 */
	public function form( $instance ) {
		$instance = wp_parse_args( $instance, array( 'title' => '' ) );
        
        $number = isset( $instance['number'] ) ? filter_var( $instance['number'], FILTER_VALIDATE_INT ) : false;
        if ( !$number ) {
            $number = 5;
        }
?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e( 'Title:', 'recent-commented-posts' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('number'); ?>"><?php _e( 'Number of posts to show:', 'recent-commented-posts' ); ?></label>
            <input id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo $number; ?>" size="3" />
        </p>
<?php 
	} // end form
    
} // end class

