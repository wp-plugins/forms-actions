<?php
class Multi_relation_widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'Multi_relation_widget', // Base ID
			__( 'FA: Join to post ', 'text_domain' ), // Name
			array( 'description' => __( 'Add multi relations (form actions)', 'text_domain' ), ) // Args
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */

	public function set_dimention($width,$unit){
				

	}

	public function widget( $args, $instance ) {
	
     	echo $args['before_widget'];

		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
		}

		/* create header */
		global $post;
		$action_guardian = true;

		if ( is_user_logged_in() ) {
		
			$curr_user_id = get_current_user_id();
			foreach (get_post_meta($post->ID,'ref_'.$instance['relation_field_name'],false) as $key => $value) {
				if($value == $curr_user_id){
					echo 'You added to this post';
					$action_guardian = false;
					$avatar = get_user_meta($curr_user_id,'avatar',true);
					if( $avatar != ''){

						echo '<div><img style="width:100%; height:auto" src="'.$avatar.'" /></div>';
					};
				}
			}

		} else {
			$action_guardian = false;
			echo 'Login to join';
		}

		if($action_guardian == true){
		?>

		<form action="" method="post" id="commentform" novalidate="">
						
			<p class="form-submit">
				<input type="hidden" name="add_multi_relation" value="<?php echo $post->ID; ?>">
				<input type="hidden" name="multi_relation_field" value="<?php echo $instance['relation_field_name']; ?>">
				<input type="hidden" name="multi_relation_options" value="<?php echo urlencode($instance['adv_options']); ?>">
				<input name="submit" type="submit" id="submit" class="submit" value="Join to this offer"> 
			</p>

		</form>
		<?php	
		}


		// end Widget Body
		echo $args['after_widget'];

		
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
     	        $title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'New title', 'text_domain' );
     	        $relation_field_name = ! empty( $instance['relation_field_name'] ) ? $instance['relation_field_name'] : __( 'Relation field name', 'text_domain' );
     	        $adv_options = ! empty( $instance['adv_options'] ) ? $instance['adv_options'] : '{
"parent_type": "post",
"joined_type": "user_id",
"unique":"true"
}';
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
		<label for="<?php echo $this->get_field_id( 'relation_field_name' ); ?>"><?php _e( 'Post type' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'relation_field_name' ); ?>" name="<?php echo $this->get_field_name( 'relation_field_name' ); ?>" type="text" value="<?php echo esc_attr( $relation_field_name ); ?>">
		</p>
		<p>
		<label for="<?php echo $this->get_field_id( 'adv_options' ); ?>"><?php _e( 'Adv query options:' ); ?></label> 
		<textarea class="widefat" style="height:150px; font-size:11px" id="<?php echo $this->get_field_id( 'adv_options' ); ?>" name="<?php echo $this->get_field_name( 'adv_options' ); ?>" type="text"><?php echo esc_attr( $adv_options ); ?></textarea>
		</p>
		<?php 
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['relation_field_name'] = ( ! empty( $new_instance['relation_field_name'] ) ) ? strip_tags( $new_instance['relation_field_name'] ) : '';
		$instance['adv_options'] = ( ! empty( $new_instance['adv_options'] ) ) ? strip_tags( $new_instance['adv_options'] ) : '';
		return $instance;
	}

} // class Markers_MAP


// register Markers_MAP widget
function register_multi_relation_widget() {
    register_widget( 'Multi_relation_widget' );
}
add_action( 'widgets_init', 'register_multi_relation_widget' );
