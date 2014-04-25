<?php

class bSocial_Facebook_Meta
{
	private $namespace;
	private $options;

	public function __construct()
	{
		$this->namespace = 'http://www.facebook.com/2008/fbml';

		$this->options = (object) array( 'add_like_button' => FALSE );

		add_action( 'init', array( $this, 'init' ) );
	} // END __construct

	public function init()
	{
		if ( is_admin() )
		{
			return;
		}

		// add opengraph filters
		add_filter( 'opengraph_metadata', array( $this, 'opengraph_metadata' ) );
		add_filter( 'language_attributes', array( $this, 'language_attributes' ) );

		// conditionally add js
		if ( bsocial()->options()->facebook->js )
		{
			add_action( 'print_footer_scripts', array( $this, 'inject_js' ) );
		}

		// conditionally insert like buttons everywhere
		if ( bsocial()->options()->facebook->add_button )
		{
			add_filter( 'the_content', array( $this, 'inject_like_button' ) );
		}
	} // END init

	public function opengraph_metadata( $properties )
	{
		$properties['fb:admins'] = bsocial()->options()->facebook->admins;
		$properties['fb:app_id'] = bsocial()->options()->facebook->app_id;

		return $properties;
	} // END opengraph_metadata

	public function language_attributes( $output )
	{
		$output .= ' xmlns:fb="'. esc_attr( $this->namespace ) .'"';

		return $output;
	} // END language_attributes

	public function inject_js( $output )
	{
		global $post;
?>
		<div id="fb-root"></div>
		<script>
			window.fbAsyncInit = function() {
				FB.init({appId: <?php echo esc_js( bsocial()->options()->facebook->app_id ); ?>, status: true, cookie: true, xfbml: true});
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
}//END class