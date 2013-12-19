<?php

class bSocial_Test extends bSocial
{
	public $connection = NULL;

	public function __construct()
	{
		add_action( 'wp_ajax_bsocial-test-twitter-api', array( $this, 'twitter_api' ) );

		if ( ! class_exists( 'TwitterOAuth' ) )
		{
			require __DIR__ . '/external/twitteroauth/twitteroauth/twitteroauth.php';
		}

		$this->connection = new TwitterOAuth( GOAUTH_TWITTER_CONSUMER_KEY, GOAUTH_TWITTER_CONSUMER_SECRET, GOAUTH_TWITTER_ACCESS_TOKEN, GOAUTH_TWITTER_ACCESS_TOKEN_SECRET );
	}//END __construct

	public function twitter_api()
	{
		// permissions check
		if( ! current_user_can( 'activate_plugins' ))
		{
			die( 0 );
		}

		?>
		<h2>Getting user info for <a href="https://twitter.com/misterbisson">@misterbisson</a></h2>
		<pre>print_r( bsocial()->twitter_user_info()->get( $this->connection, 'misterbisson' ) );</pre>
		<pre><?php print_r( bsocial()->twitter_user_info()->get( $this->connection, 'misterbisson' ) ); ?></pre>


		<h2>Getting tweet stream for <a href="https://twitter.com/Gigaom">@gigaom</a></h2>
		<pre>
$twitter_feed = bsocial()->new_twitter_user_stream();
$twitter_feed->stream(
	$this->connection,
	array(
		'screen_name' => 'gigaom',
		'count' => 2,
	)
);
print_r( $twitter_feed->tweets() );
		</pre>

		<pre><?php
		$twitter_feed = bsocial()->new_twitter_user_stream();
		$twitter_feed->stream(
			$this->connection,
			array(
				'screen_name' => 'gigaom' , 
				'count' => 2 ,
			)
		);
		print_r( $twitter_feed->tweets() );
		?></pre>

		<h2>Searching for tweets matching <a href="https://twitter.com/search?q=gigaom.com%20-from%3Agigaom">gigaom.com -from:gigaom</a></h2>
		<pre>
$twitter_search = bsocial()->new_twitter_search();
$twitter_search->search(
	$this->connection,
	array(
		'q' => 'gigaom.com%20-from%3Agigaom',
		'count' => 5,
	)
);
print_r( $twitter_search->tweets() );
		</pre>
		<pre><?php
		$twitter_search = bsocial()->new_twitter_search();
		$twitter_search->search(
			$this->connection,
			array(
				'q' => 'gigaom.com%20-from%3Agigaom',
				'count' => 2,
			)
		);
		print_r( $twitter_search->tweets() );
		?></pre>

		<?php
		die;
	}//END twitter_api
}//END class
