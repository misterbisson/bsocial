<?php

class bSocialFacebook_Test extends bSocial
{
	public function __construct()
	{
		add_action( 'wp_ajax_bsocial-test-facebook-api', array( $this, 'bsocial_test_facebook_api_ajax' ) );
	}//END __construct

	public function bsocial_test_facebook_api_ajax()
	{
		// permissions check
		if( ! current_user_can( 'activate_plugins' ) )
		{
			wp_die( "please don't even try to be sneaky!" );
		}
?>

		<h2>Getting application user info</h2>
		<pre>print_r( bsocial()->facebook_user_info()->get_own_profile() );</pre>
		<pre><?php print_r( bsocial()->facebook_user_info()->get_own_profile() ); ?></pre>

		<h2>Getting public posts from the application user's wall</h2>
		<pre>print_r( bsocial()->facebook_user_stream()->get_posts( 3 ) );</pre>
		<pre><?php print_r( bsocial()->facebook_user_stream()->get_posts( 3 ) ); ?></pre>


<?php
		die;
	}//END bsocial_test_linkedin_api_ajax
}//END class