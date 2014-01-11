<?php

class bSocial_Facebook_Meta
{

	public function __construct()
	{
		/*
		* be sure to set the app ID and admins after instantiating the object
		* $this->app_id = FBJS_APP_ID;
		* $this->admins = FBJS_ADMINS;
		*/

		$this->namespace = 'http://www.facebook.com/2008/fbml';

		$this->options->add_like_button = FALSE;

		add_action( 'init', array( $this, 'init' ) );
	} // END __construct

	public function init()
	{
		if ( is_admin() )
		{
			return;
		}

		add_filter( 'opengraph_metadata', array( $this, 'opengraph_metadata' ) );
		add_filter( 'language_attributes', array( $this, 'add_namespace' ) );
		add_action( 'print_footer_scripts', array( $this, 'inject_js' ) );

		if ( $this->options->add_like_button )
		{
			add_filter( 'the_content', array( $this, 'inject_like_button' ) );
		}
	} // END init

	public function opengraph_metadata( $properties )
	{
		$properties['fb:admins'] = $this->admins;
		$properties['fb:app_id'] = $this->app_id;

		return $properties;
	} // END opengraph_metadata

	public function add_namespace( $output )
	{
		$output .= ' xmlns:fb="'. esc_attr( $this->namespace ) .'"';

		return $output;
	} // END add_namespace

	public function inject_js( $output )
	{
		global $post;
?>
		<div id="fb-root"></div>
		<script>
			window.fbAsyncInit = function() {
				FB.init({appId: <?php echo $this->app_id; ?>, status: true, cookie: true, xfbml: true});
			};

			var e = document.createElement('script');
			e.type = 'text/javascript';
			e.src = document.location.protocol + '//connect.facebook.net/en_US/all.js';
			e.async = true;
			document.getElementById('fb-root').appendChild(e);
		</script>
<?php
	} // END inject_js

	public function inject_like_button( $content )
	{
		$button = '<p><fb:like href="'. get_permalink( get_the_ID() ) .'"></fb:like></p>';

		return $button . $content . $button;
	} // END inject_like_button
}