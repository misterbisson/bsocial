<?php

class bSocial_FacebookApi
{

	function __construct()
	{
		/*
		* be sure to set the app ID and admins after instantiating the object
		* $this->app_id = FBJS_APP_ID;
		* $this->admins = FBJS_ADMINS;
		*/

		$this->namespace = 'http://www.facebook.com/2008/fbml';

		add_action( 'init' , array( $this , 'init' ));
	}

	function init()
	{
		if( is_admin() )
			return;

		add_filter( 'opengraph_metadata' , array( $this , 'opengraph_metadata' ));
		add_filter( 'language_attributes' , array( $this , 'add_namespace' ));
		add_action( 'print_footer_scripts' , array( $this , 'inject_js' ));
		add_filter( 'the_content' , array( $this , 'inject_like_button' ));
	}

	function opengraph_metadata( $properties )
	{
		$properties['fb:admins'] = $this->admins;
		$properties['fb:app_id'] = $this->app_id; 

		return $properties;
	}

	function add_namespace( $output )
	{
		$output .= ' xmlns:fb="'. esc_attr( $this->namespace ) .'"';

		return $output;
	}

	function inject_js( $output )
	{
		global $post;
?>
		<div id="fb-root"></div>
		<script>
			window.fbAsyncInit = function() {
				FB.init({appId: <?php echo FBJS_APP_ID; ?>, status: true, cookie: true, xfbml: true});
			};

			var e = document.createElement('script');
			e.type = 'text/javascript';
			e.src = document.location.protocol + '//connect.facebook.net/en_US/all.js';
			e.async = true;
			document.getElementById('fb-root').appendChild(e);
		</script>
<?php
	}

	function inject_like_button( $content )
	{
		$button = '<p><fb:like href="'. get_permalink( get_the_ID() ) .'"></fb:like></p>';

		return $button . $content . $button;
	}
}