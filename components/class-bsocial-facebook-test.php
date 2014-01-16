<?php

class bSocialFacebook_Test
{
	public function __construct()
	{
		add_action( 'wp_ajax_bsocial-test-facebook-api', array( $this, 'bsocial_test_facebook_api_ajax' ) );
		add_action( 'wp_ajax_bsocial-test-post-facebook-api', array( $this, 'bsocial_test_post_facebook_api_ajax' ) );
	}//END __construct

	public function bsocial_test_facebook_api_ajax()
	{
		// permissions check
		if( ! current_user_can( 'activate_plugins' ) )
		{
			wp_die( "please don't even try to be sneaky!" );
		}
?>

		<h2>Getting application's own user info</h2>
		<pre>print_r( bsocial()->facebook()->get_profile() );</pre>
<?php
		$result = bsocial()->facebook()->get_profile();
		if ( ! $result )
		{
			$result = bsocial()->facebook()->errors;
		}
?>
		<pre><?php print_r( $result ); ?></pre>

		<h2>Getting profile of a public page</h2>
		<pre>print_r( bsocial()->facebook()->get_profile( '/Gigaom' ) );</pre>
		<pre><?php print_r( bsocial()->facebook()->get_profile( '/Gigaom' ) ); ?></pre>

		<h2>Getting posts from the application user's wall</h2>
		<pre>print_r( bsocial()->facebook()->user_stream()->get_posts( 3 ) );</pre>
<?php
		$result = bsocial()->facebook()->user_stream()->get_posts( 3 );
		if ( ! $result )
		{
			$result = bsocial()->facebook()->errors;
		}
?>
		<pre><?php print_r( $result ); ?></pre>

<p>
<h2>Post a status update to the user's own wall</h2>
<a href="<?php echo admin_url( 'admin-ajax.php?action=bsocial-test-post-facebook-api', 'https' ); ?>">Status Update Test</a>
</p>

<?php
		die;
	}//END bsocial_test_facebook_api_ajax

	public function bsocial_test_post_facebook_api_ajax()
	{
?>
		<h2>Post a status update to the authenticated user's wall</h2>
		<pre>print_r( bsocial()->facebook()->post_status( '"bunny chocolac!" ' . date( DATE_RFC2822 ) ) );
		<?php print_r( bsocial()->facebook()->post_status( '"bunny chocolac!" ' . date( DATE_RFC2822 ) ) ); ?>
</pre>

<?php
		die;
	}//END bsocial_test_post_facebook_api_ajax
}//END class