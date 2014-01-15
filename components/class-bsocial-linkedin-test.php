<?php

class bSocialLinkedIn_Test
{
	public function __construct()
	{
		add_action( 'wp_ajax_bsocial-test-linkedin-api', array( $this, 'bsocial_test_linkedin_api_ajax' ) );
		add_action( 'wp_ajax_bsocial-test-post-linked-api', array( $this, 'bsocial_test_post_linkedin_api_ajax' ) );
	}//END __construct

	public function bsocial_test_linkedin_api_ajax()
	{
		// permissions check
		if ( ! current_user_can( 'activate_plugins' ) )
		{
			wp_die( "please don't even try to be sneaky!" );
		}
?>

		<h2>Getting user info by url for <a href="http://www.linkedin.com/pub/christopher-stream/b/139/282">Christopher Stream</a></h2>
		(The user url used is from a public/anonymous search result.)<br/>
		<pre>print_r( bsocial()->linkedin()->get_user_info( 'http://www.linkedin.com/pub/christopher-stream/b/139/282', 'url', '(id,first-name,last-name,headline,industry)' ) );</pre>
		<pre><?php print_r( bsocial()->linkedin()->get_user_info( 'http://www.linkedin.com/pub/christopher-stream/b/139/282', 'url', '(id,first-name,last-name,headline,industry,siteStandardProfileRequest)' ) ); ?></pre>


		<h2>Getting user info by member id/token for <a href="http://www.linkedin.com/in/jeremyjbornstein">Jeremy Bornstein</a></h2>
		<pre>print_r( bsocial()->linkedin()->get_user_info( 'cKzpmcXNzb', 'token', '(id,first-name,last-name,headline,industry,siteStandardProfileRequest)' ) );</pre>
		<pre><?php print_r( bsocial()->linkedin()->get_user_info( 'cKzpmcXNzb', 'token', '(id,first-name,last-name,headline,industry,siteStandardProfileRequest)' ) ); ?></pre>


		<h2>Getting application user's own info</h2>
		<pre>print_r( bsocial()->linkedin()->get_user_info( NULL, 'self', '(id,first-name,last-name,headline,industry,siteStandardProfileRequest)' ) );</pre>
		<pre><?php print_r( bsocial()->linkedin()->get_user_info( NULL, 'self', '(id,first-name,last-name,headline,industry,siteStandardProfileRequest)' ) ); ?></pre>


		<h2>Getting a member's updates (feed/stream) that's not shared</h2>
		<pre>print_r( bsocial()->linkedin()->get_updates( 'cKzpmcXNzb', 'token' ) );</pre>
		<pre><?php print_r( bsocial()->linkedin()->get_updates( 'cKzpmcXNzb', 'token', 2 ) ); ?></pre>


		<h2>Getting a member's updates (feed/stream)</h2>
		<pre>print_r( bsocial()->linkedin()->->get_updates( 'http://www.linkedin.com/in/razazaidi', 'url' ) );</pre>
		<pre><?php print_r( bsocial()->linkedin()->get_updates( 'http://www.linkedin.com/in/razazaidi', 'url', 2 ) ); ?></pre>


		<h3>(LinkedIn has different search api calls for jobs, companies and people. We may implement some of them once we know what we want to do with LinkedIn search.)</h3>

		<h2>post to a linkedin feed</h2>
		<a href="<?php echo admin_url( 'admin-ajax.php?action=bsocial-test-post-linked-api', 'https' ); ?>">share something in a user's feed</a>
		<p/>
<?php
		die;
	}//END bsocial_test_linkedin_api_ajax


	public function bsocial_test_post_linkedin_api_ajax()
	{
		$params = array(
			'comment' => 'yet another test of linedin\'s "share" api. ' . date( DATE_RFC2822 ),
			'title' => 'camper van beethoven',
			'description' => 'sweethearts',
			'submitted-url' => 'http://tabs.ultimate-guitar.com/c/camper_van_beethoven/sweethearts_tab.htm',
			'submitted-image-url' => 'http://upload.wikimedia.org/wikipedia/en/1/1e/Camper_Van_Beethoven_Key_Lime_Pie.jpg',
			'visibility' => 'anyone',
		);
?>
		<pre>
$params = array(
	'comment' => 'yet another test of linedin\'s "share" api. ' . date( DATE_RFC2822 ),
	'title' => 'camper van beethoven',
	'description' => 'sweethearts',
	'submitted-url' => 'http://tabs.ultimate-guitar.com/c/camper_van_beethoven/sweethearts_tab.htm',
	'submitted-image-url' => 'http://upload.wikimedia.org/wikipedia/en/1/1e/Camper_Van_Beethoven_Key_Lime_Pie.jpg',
	'visibility' => 'anyone',
);

print_r( bsocial()->linkedin()->share( $params ) );</pre>

<pre><?php print_r( bsocial()->linkedin()->share( $params ) ); ?></pre>

<?php
		die;
	}//END bsocial_test_post_linkedin_api_ajax
}//END class