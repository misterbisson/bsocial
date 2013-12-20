<?php

class bSocialLinkedIn_Test extends bSocial
{
	public function __construct()
	{
		add_action( 'wp_ajax_bsocial-test-linkedin-api', array( $this, 'bsocial_test_linkedin_api_ajax' ) );
	}//END __construct

	public function bsocial_test_linkedin_api_ajax()
	{
		// permissions check
		if( ! current_user_can( 'activate_plugins' ))
		{
			wp_die( "please don't even try to be sneaky!" );
		}
?>

		<h2>Getting user info by url for <a href="http://www.linkedin.com/pub/christopher-stream/b/139/282">Christopher Stream</a></h2>
		(The user url used is from a public/anonymous search result.)<br/>
		<pre>print_r( bsocial()->linkedin_user_info()->get( 'http://www.linkedin.com/pub/christopher-stream/b/139/282', 'url', '(id,first-name,last-name,headline,industry)' ) );</pre>
		<pre><?php print_r( bsocial()->linkedin_user_info()->get( 'http://www.linkedin.com/pub/christopher-stream/b/139/282', 'url', '(id,first-name,last-name,headline,industry,siteStandardProfileRequest)' ) ); ?></pre>


		<h2>Getting user info by member id/token for <a href="http://www.linkedin.com/in/jeremyjbornstein">Jeremy Bornstein</a></h2>
		<pre>print_r( bsocial()->linkedin_user_info()->get( 'cKzpmcXNzb', 'token', '(id,first-name,last-name,headline,industry,siteStandardProfileRequest)' ) );</pre>
		<pre><?php print_r( bsocial()->linkedin_user_info()->get( 'cKzpmcXNzb', 'token', '(id,first-name,last-name,headline,industry,siteStandardProfileRequest)' ) ); ?></pre>


		<h2>Getting application user's own info</h2>
		<pre>print_r( bsocial()->linkedin_user_info()->get_own_profile() );</pre>
		<pre><?php print_r( bsocial()->linkedin_user_info()->get_own_profile( '(id,first-name,last-name,headline,industry,siteStandardProfileRequest)' ) ); ?></pre>


		<h2>Getting a member's updates (feed/stream) that's not shared</h2>
		<pre>print_r( bsocial()->linkedin_user_stream()->get_updates( 'cKzpmcXNzb', 'token' ) );</pre>
		<pre><?php print_r( bsocial()->linkedin_user_stream()->get_updates( 'cKzpmcXNzb', 'token', 2 ) ); ?></pre>


		<h2>Getting a member's updates (feed/stream)</h2>
		<pre>print_r( bsocial()->linkedin_user_stream()->get_updates( 'http://www.linkedin.com/in/razazaidi', 'url' ) );</pre>
		<pre><?php print_r( bsocial()->linkedin_user_stream()->get_updates( 'http://www.linkedin.com/in/razazaidi', 'url', 2 ) ); ?></pre>


		<h3>(LinkedIn has different search api calls for jobs, companies and people. We may implement some of them once we know what we want to do with LinkedIn search.)</h3>
<?php
		die;
	}//END bsocial_test_linkedin_api_ajax
}//END class