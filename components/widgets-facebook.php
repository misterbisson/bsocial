<?php

class bSocial_Facebook_Comments_Widget extends WP_Widget
{
	public function __construct()
	{
		$widget_ops = array('classname' => 'widget_fb_comments', 'description' => __( 'Displays Facebook comments') );
		$this->WP_Widget('fb_comments', __('Facebook Comments (bSocial)'), $widget_ops);
	}//END __construct

	public function widget( $args, $instance )
	{
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'] );
		if( is_singular() ) // get clean URLs on single post pages
		{
			wp_reset_query(); // reset the query for good luck
			$url = 'data-href="'. get_permalink( get_queried_object_id() ) .'"'; // get the permalink to the requested page
		}
		else // use the current URL for any other page
		{
			$url = ''; // empty URL
		}

		echo $args['before_widget'] . $args['before_title'] . esc_html( $title ) . $args['after_title'];
		?>
		<div class="fb-comments" <?php echo esc_url( $url ); ?> data-num-posts="<?php echo absint( $instance['comments'] ); ?>" data-width="<?php echo absint( $instance['width'] ); ?>" data-colorscheme="<?php echo esc_attr( $instance['colorscheme'] ); ?>"></div>
		<?php
		echo $args['after_widget'];
	}//END widget

	public function update( $new_instance, $old_instance )
	{
		$instance = $old_instance;
		$instance['title'] = wp_kses( $new_instance['title'], array() );
		$instance['comments'] = absint( $new_instance['comments'] );
		$instance['width'] = absint( $new_instance['width'] );
		$instance['colorscheme'] = in_array( $new_instance['colorscheme'], array( 'light', 'dark' )) ? $new_instance['colorscheme'] : 'dark';

		return $instance;
	}//END update

	public function form( $instance )
	{
		//Defaults
		$instance = wp_parse_args( (array) $instance,
			array(
				'title' => 'Comment Via Facebook',
				'comments' => 5,
				'width' => 300,
				'colorscheme' => 'light',
			)
		);
?>

		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ) ?>" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('comments'); ?>"><?php _e('Number of comments to show:'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('comments'); ?>" name="<?php echo $this->get_field_name('comments'); ?>" type="text" value="<?php echo esc_attr( $instance['comments'] ) ?>" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('width'); ?>"><?php _e('Width of the comment box in pixels:'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('width'); ?>" name="<?php echo $this->get_field_name('width'); ?>" type="text" value="<?php echo esc_attr( $instance['width'] ) ?>" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('colorscheme'); ?>"><?php _e( 'Color scheme:' ); ?></label>
			<select name="<?php echo $this->get_field_name('colorscheme'); ?>" id="<?php echo $this->get_field_id('colorscheme'); ?>" class="widefat">
				<option value="light"<?php selected( $instance['colorscheme'], 'light' ); ?>><?php _e('Light'); ?></option>
				<option value="dark"<?php selected( $instance['colorscheme'], 'dark' ); ?>><?php _e('Dark'); ?></option>
			</select>
		</p>
<?php
	}//END form
}// end Widget_FB_Comments

class bSocial_Facebook_Activity_Widget extends WP_Widget
{

	public function __construct()
	{
		$widget_ops = array('classname' => 'widget_fb_activity', 'description' => __( 'Displays Facebook activity for this domain') );
		$this->WP_Widget('fb_activity', __('Facebook Activity (bSocial)'), $widget_ops);
	}//END __construct

	public function widget( $args, $instance )
	{
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'] );

		echo $args['before_widget'] . $args['before_title'] . esc_html( $title ) . $args['after_title'];
?>
		<fb:activity width="300" height="270" header="false" font="segoe ui" border_color="#fff" recommendations="true"></fb:activity>
<?php
		echo $args['after_widget'];
	}//END widget

	public function update( $new_instance, $old_instance )
	{
		$instance = $old_instance;
		$instance['title'] = wp_kses( $new_instance['title'], array() );

		return $instance;
	}//END update

	public function form( $instance )
	{
		//Defaults
		$instance = wp_parse_args( (array) $instance,
			array(
				'title' => 'Recent Activity',
			)
		);

?>

		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>" />
		</p>
<?php
	}//END form
}// end Widget_FB_Activity


class bSocial_Facebook_Like_Widget extends WP_Widget
{

	public function __construct()
	{
		$widget_ops = array('classname' => 'widget_fb_like', 'description' => __( 'Displays a Facebook like button and facepile') );
		$this->WP_Widget('fb_like', __('Facebook Like (bSocial)'), $widget_ops);
	}//END __construct

	public function widget( $args, $instance )
	{

		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'] );

		switch( $instance['context'] )
		{
			case 'page':
				if( is_singular() ) // get clean URLs on single post pages
				{
					wp_reset_query(); // reset the query for good luck
					$url = get_permalink( get_queried_object_id() ); // get the permalink to the requested page
				}
				else // use the current URL for any other page
				{
					$url = ''; // empty URL
				}
				break;

			case 'site':
			default:
				$url = trailingslashit( home_url() );
		}

		echo $args['before_widget'] . $args['before_title'] . esc_html( $title ) . $args['after_title'];
?>
		<span id="fb_activity_like">
			<fb:like ref="top_activity" width="50" show_faces="false" send="false" layout="box_count" href="<?php echo esc_url( $url ); ?>" font="segoe ui"></fb:like>
			<fb:facepile href="<?php echo esc_url( $url ); ?>" width="225" max_rows="1"  font="segoe ui"></fb:facepile>
		</span>
<?php
		echo $args['after_widget'];
	}//END widget

	public function update( $new_instance, $old_instance )
	{
		$instance = $old_instance;
		$instance['title'] = wp_kses( $new_instance['title'], array() );
		$instance['context'] = in_array( $new_instance['context'], array( 'site', 'page' )) ? $new_instance['context'] : 'site';

		return $instance;
	}//END update

	public function form( $instance )
	{
		//Defaults
		$instance = wp_parse_args( (array) $instance,
			array(
				'title' => 'Find Us On Facebook',
				'context' => 'site',
			)
		);

?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id('context'); ?>"><?php _e( 'Like what:' ); ?></label>
			<select name="<?php echo $this->get_field_name('context'); ?>" id="<?php echo $this->get_field_id('context'); ?>" class="widefat">
				<option value="site"<?php selected( $instance['context'], 'site' ); ?>><?php _e('This site'); ?></option>
				<option value="page"<?php selected( $instance['context'], 'page' ); ?>><?php _e('The current page'); ?></option>
			</select>
		</p>
<?php
	}//END form
}// end Widget_FB_Like



// register these widgets
function fb_widgets_init()
{
	register_widget( 'bSocial_Facebook_Comments_Widget' );
	register_widget( 'bSocial_Facebook_Activity_Widget' );
	register_widget( 'bSocial_Facebook_Like_Widget' );
}//END fb_widgets_init
add_action( 'widgets_init' , 'fb_widgets_init', 1 );
