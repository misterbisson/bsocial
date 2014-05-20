<?php

/**
 * Author: Vasken Hauri, Casey Bisson
 * Prints JS to load Twitter JS SDK in a deferred manner
 * Puts Twitter card meta in the head, https://dev.twitter.com/docs/cards
 */

class bSocial_Twitter_Meta
{
	/**
	 * boolean to determine if the JS should be loaded
	 */
	public $load_js = true;

	public function __construct()
	{
		add_action( 'init', array( $this, 'init' ) );
	} // END __construct

	public function init()
	{
		// don't do anything to admin pages
		if ( is_admin() )
		{
			return;
		}

		// twitter card metadata
		add_action( 'wp_head', array( $this, 'wp_head' ) );

		// defaults
		add_filter( 'twittercard_card', array( $this, 'default_card' ), 1 );
		add_filter( 'twittercard_site', array( $this, 'default_site' ), 1 );

		// optionally load js
		if ( bsocial()->options()->twitter->js )
		{
			add_action( 'print_footer_scripts', array( $this, 'inject_js' ) );
		}// end if
	} // END init

	public function default_card( $type = '' )
	{
		return 'summary';
	} // END default_card

	public function default_site( $twitteruser = '' )
	{
		return bsocial()->options()->twitter->username;
	} // END default_site

	public function wp_head()
	{
		$metadata = $this->metadata();
		foreach ( $metadata as $key => $value )
		{
			if ( empty( $key ) || empty( $value ) )
			{
				continue;
			}

			echo '<meta name="' . esc_attr( $key ) . '" content="' . esc_attr( $value ) . '" />' . "\n";
		}
	} // END wp_head

	public function metadata()
	{
		$metadata = array();

		// definitions: https://dev.twitter.com/docs/cards
		$properties = array(
			// required
			'card',

			// identity (optional)
			'site', 'site:id', 'creator', 'creator:id',

			// content
			//'url', 'title', 'description',

			// image
			//'image', 'image:width', 'image:height',

			// player
			//'player', 'player:width', 'player:height', 'player:stream',
		);

		foreach ( $properties as $property )
		{
			$filter = 'twittercard_' . $property;
			$metadata[ "twitter:$property" ] = apply_filters( $filter, '' );
		}

		return apply_filters( 'twittercard_metadata', $metadata );
	} // END metadata

	public function inject_js()
	{
		?>
		<script type="text/javascript">
			setTimeout(function() {
				var bstwitterb = document.createElement('script'); bstwitterb.type = 'text/javascript'; bstwitterb.async = true;
				bstwitterb.src = 'http://platform.twitter.com/widgets.js';
				var z = document.getElementsByTagName('script')[0]; z.parentNode.insertBefore(bstwitterb, z);
			}, 1);
		</script>
		<?php
	} // END inject_js
}//END class
