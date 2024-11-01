<?php 
 /**
 * World_Flags Class
 *
 * @package WorldFlags
 */
class World_Flags extends WP_Widget {
	/** constructor */
	function World_Flags() {
		$widget_ops = array( 'classname' => 'widget_world_flags', 'description' => __( 'Show visitor country based on IP', 'world_flags' ) ); 
		$this->WP_Widget( 'World_Flags', __( 'World Flags', 'bmiic' ), $widget_ops ); //FIXME: does not load the textdomain localized strings
	}

	/** @see WP_Widget::widget */
	function widget( $args, $instance ) {
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );
		$text = apply_filters( 'widget_text', $instance['text'] );
		$size = apply_filters( 'widget_size', $instance['size'] );
		$code = apply_filters( 'widget_code', $instance['code'] );
		?>
			  <?php echo $before_widget; ?>
				  <?php if ( $title )
						echo $before_title . $title . $after_title; 
						
						$ajax = ( $code == 'auto' );
						echo world_flags_build_html( $size, $text, $code, '', '', $ajax, 'widget', false );
					echo $after_widget; ?>
		<?php
	}

	/** @see WP_Widget::update */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['text'] = strip_tags( $new_instance['text'] );
		$instance['size'] = strip_tags( $new_instance['size'] );
		$instance['code'] = strip_tags( $new_instance['code'] );
		return $instance;
	}

	/** @see WP_Widget::form */
	function form( $instance ) {				
		$title = esc_attr( $instance['title'] );
		$text = esc_attr( $instance['text'] );
		$size = esc_attr( $instance['size'] );
		$code = esc_attr( $instance['code'] );
		
		$codes = world_flags_country_codes();
		?>
			<p>
			  <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'world_flags' ); ?> 
			  <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" /></label><br />
			  
			  <label for="<?php echo $this->get_field_id( 'code' ); ?>"><?php _e( 'Country Code:', 'world_flags' ); ?>
			  <select class="widefat" id="<?php echo $this->get_field_id( 'code' ); ?>" name="<?php echo $this->get_field_name( 'code' ); ?>">
			  	<option value="auto" <?php if ( $code == 'auto') echo 'selected="selected"'; ?>><?php _e( 'Automatic, Visitor IP', 'world_flags' ); ?></option>
				<?php 
					$keys = array_keys( $codes );
					foreach ( $keys as $k ) {
						echo "<option value=\"$k\"" .  ( $code == $k ? 'selected="selected"' : '' ) . ">($k) $codes[$k]</option>";
					}
			  	?>
			  </select>
			  </label><br />
			  <label for="<?php echo $this->get_field_id( 'size' ); ?>"><?php _e( 'Size:', 'world_flags' ); ?> 
			  <select class="widefat" id="<?php echo $this->get_field_id( 'size' ); ?>" name="<?php echo $this->get_field_name( 'size' ); ?>">
			  	<option value="16" <?php if ( $size == '16') echo 'selected="selected"'; ?>><?php _e( 'Icon - 16', 'world_flags' ); ?></option>
			  	<option value="24" <?php if ( $size == '24') echo 'selected="selected"'; ?>><?php _e( 'Small - 24', 'world_flags' ); ?></option>
			  	<option value="32" <?php if ( $size == '32') echo 'selected="selected"'; ?>><?php _e( 'Medium - 32', 'world_flags' ); ?></option>
			  	<option value="48" <?php if ( $size == '48') echo 'selected="selected"'; ?>><?php _e( 'Large - 48', 'world_flags' ); ?></option>
			  </select><br />
			  <label for="<?php echo $this->get_field_id( 'text' ); ?>">
			  <input id="<?php echo $this->get_field_id( 'text' ); ?>" name="<?php echo $this->get_field_name( 'text' ); ?>" type="checkbox" value="1" <?php checked( '1', $text ); ?> /> 
			  <?php _e( 'Country Name', 'world_flags' ); ?>
			  </label><br />
			</p>
		<?php 
	}

} // class World_Flags
	
// register widgets
add_action( 'widgets_init', create_function( '', 'return register_widget( "World_Flags" );' ) );
?>