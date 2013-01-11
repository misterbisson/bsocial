<?php

/**
 * Author: Vasken Hauri, Casey Bisson
 * Prints JS to load Twitter JS SDK in a deferred manner
 * Puts Twitter card meta in the head, https://dev.twitter.com/docs/cards
 */

class bSocial_TwitterApi
{
	/**
	 * boolean to determine if the JS should be loaded
	 */
	public $load_js = true;

	function __construct()
	{
		/*
		* be sure to set the app ID after instantiating the object
		* $this->app_id = TWTTR_APP_ID;
		*/

		add_action( 'init' , array( $this , 'init' ));
	}

	function init()
	{
		// don't do anything to admin pages
		if( is_admin() )
			return;

		if ( $this->load_js )
		{
			// inject the JS include
			add_action( 'print_footer_scripts' , array( $this , 'inject_js' ));
		}// end if

		// twitter card metadata
		add_action( 'wp_head' , array( $this , 'head' ));

		// defaults
		add_filter( 'twittercard_card', array( $this , 'default_card' ) , 5 );
		add_filter( 'twittercard_site', array( $this , 'default_site' ) , 5 );
	}

	function default_card( $type = '' )
	{
		// default to 'summary', unless something else is set
		if( empty( $type ))
			return 'summary';

		return $type;
	}

	function default_site( $twitteruser = '' )
	{
		// can only move forward if we have a twitter username of our own
		if( empty( $this->card_site ))
			return $twitteruser;

		// default, unless something else is set
		if( empty( $twitteruser ))
			return $this->card_site;

		return $twitteruser;
	}

	function head()
	{
		$metadata = $this->metadata();
		foreach ( $metadata as $key => $value )
		{
			if( empty( $key ) || empty( $value ))
				continue;
			echo '<meta name="'. esc_attr( $key ) .'" value="'. esc_attr($value) .'" />' . "\n";
		}
	}

	function metadata()
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

		foreach ($properties as $property)
		{
			$filter = 'twittercard_' . $property;
			$metadata["twitter:$property"] = apply_filters( $filter, '' );
		}

		return apply_filters( 'twittercard_metadata' , $metadata );
	}

	function inject_js()
	{
?>
		<script type="text/javascript">
<?php
		if( ! empty( $this->app_id ))
		{
?>
			setTimeout(function() {
				var bstwittera = document.createElement('script'); bstwittera.type = 'text/javascript'; bstwittera.async = true;
				bstwittera.src = 'http://platform.twitter.com/anywhere.js?id=<?php echo $this->app_id ; ?>&v=1';
				var z = document.getElementsByTagName('script')[0]; z.parentNode.insertBefore(bstwittera, z);
			}, 1);

<?php
		}
?>
			setTimeout(function() {
				var bstwitterb = document.createElement('script'); bstwitterb.type = 'text/javascript'; bstwitterb.async = true;
				bstwitterb.src = 'http://platform.twitter.com/widgets.js';
				var z = document.getElementsByTagName('script')[0]; z.parentNode.insertBefore(bstwitterb, z);
			}, 1);
		</script>
<?php
	}
}