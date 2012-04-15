<?php

/**
 * Author: Vasken Hauri
 * Prints JS to load Twitter JS SDK in a deferred manner
 */

class bSocial_TwitterApi
{
	function __construct()
	{
		$this->app_id = TWTTR_APP_ID;

		add_action( 'init' , array( $this , 'init' ));
	}

	function init()
	{
		if( is_admin() )
			return;

		add_action( 'print_footer_scripts' , array( $this , 'inject_js' ));
	}

	function new_search()
	{
		require_once( dirname( __FILE__ ) .'/twitter-streams.php' );

		return new Twitter_Search;
	}

	function new_user_stream()
	{
		require_once( dirname( __FILE__ ) .'/twitter-streams.php' );

		return new Twitter_User_Stream;
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