<?php
/*
 * base class for bSocial's facebook-related functions.
 */

if ( ! class_exists( 'Facebook' ) )
{
	require __DIR__ . '/external/facebook-php-sdk/src/facebook.php';
}

/*
 * bSocial_Facebook class
 */
class bSocial_Facebook
{
	public $facebook = NULL;

	public function __construct()
	{
		if ( ! $this->facebook )
		{
			$this->facebook = new Facebook(
				array(
					'appId' => GOAUTH_FACEBOOK_CONSUMER_KEY,
					'secret' => GOAUTH_FACEBOOK_CONSUMER_SECRET,
					'fileUpload' => FALSE,         // optional
					'allowSignedRequest' => FALSE, // optional but should be set to false for non-canvas apps
				)
			);
		}//END if
	}//END __construct

	/**
	 * get the id of the current authenticated user
	 */
	public function get_user_id()
	{
		if ( ! $this->facebook )
		{
			return new WP_Error( 'facebook auth error', 'error instantiating a Facebook instance.');
		}

		$user_id = $this->facebook->getUser();

		if ( ! $user_id )
		{
			$login_url = $this->facebook->getLoginUrl();
			return new WP_Error( 'facebook auth error', 'user not logged in. Please <a href="' . $login_url . '">login</a> and try again.' );
		}

		return $user_id;
	}//END get_user_id
}//END class