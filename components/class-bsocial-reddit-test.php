<?php

class bSocialReddit_Test
{
	public function __construct()
	{
		add_action( 'wp_ajax_bsocial-test-reddit-api', array( $this, 'bsocial_test_reddit_api_ajax' ) );
	}//END __construct

	public function bsocial_test_reddit_api_ajax()
	{
		// permissions check
		if( ! current_user_can( 'activate_plugins' ))
		{
			die( 0 );
		}

		?>
		<h2>Getting links to <a href="http://gigaom.com">gigaom.com</a></h2>
		<pre>print_r( bsocial()->reddit()->get_links_by_domain( 'gigaom.com' ) );</pre>
		<pre><?php print_r( bsocial()->reddit()->get_links_by_domain( 'gigaom.com', 3 ) ); ?></pre>

		<?php
		die;
	}//END bsocial_test_reddit_api_ajax
}//END class