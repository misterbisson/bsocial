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
		<h2>Getting 3 top submissions of <a href="http://gigaom.com">gigaom.com</a> links</h2>
		<pre>print_r( bsocial()->reddit()->get_links_by_domain( array( 'domain' => 'gigaom.com', 'type' => 'top', 'limit' => 3 ) ) );</pre>
		<pre><?php print_r( bsocial()->reddit()->get_links_by_domain( array( 'domain' => 'gigaom.com', 'type' => 'top', 'limit' => 3 ) ) ); ?></pre>

		<h2>Getting 3 new submissions of <a href="http://gigaom.com">gigaom.com</a> links with error</h2>
		<pre>print_r( bsocial()->reddit()->get_links_by_domain( array( 'domain' => 'gigaom.com', 'type' => 'newincorrecttype', 'limit' => 3 ) ) );</pre>
		<pre><?php $ret = bsocial()->reddit()->get_links_by_domain( array( 'domain' => 'gigaom.com', 'type' => 'newincorrecttype', 'limit' => 3 ) );
				print_r( bsocial()->reddit()->errors ); ?></pre>

		<h2>Getting 2 new submissions of <a href="http://pro.gigaom.com">pro.gigaom.com</a> links</h2>
		<pre>print_r( bsocial()->reddit()->get_links_by_domain( array( 'domain' => 'pro.gigaom.com', 'type' => 'new', 'limit' => 2 ) ) );</pre>
		<pre><?php print_r( bsocial()->reddit()->get_links_by_domain( array( 'domain' => 'pro.gigaom.com', 'type' => 'new', 'limit' => 2 ) ) ); ?></pre>

		<h2>Getting page 5 (with page size of 2) of top submissions of <a href="http://gigaom.com">gigaom.com</a> links</h2>
		<pre>
$res = bsocial()->reddit()->get_links_by_domain( array( 'domain' => 'gigaom.com', 'type' => 'top', 'limit' => 2 ); // page 1

// skip the next 3 pages
for ( $i = 0; $i < 3; ++$i )
{
	$res = bsocial()->reddit()->next();
}
print_r( bsocial()->reddit()->next() );</pre>

		<pre><?php
			$res = bsocial()->reddit()->get_links_by_domain( array( 'domain' => 'gigaom.com', 'type' => 'top', 'limit' => 2 ) ); // page 1
			// skip the next 3 pages
			for ( $i = 0; $i < 3; ++$i )
			{
				$res = bsocial()->reddit()->next();
			}
			print_r( bsocial()->reddit()->next() ); ?></pre>

		<?php
		die;
	}//END bsocial_test_reddit_api_ajax
}//END class