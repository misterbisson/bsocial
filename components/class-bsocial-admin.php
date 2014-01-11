<?php

class bSocial_Admin
{
	public $tests_loaded = FALSE;

	public function __construct()
	{
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_filter( 'plugin_action_links', array( $this, 'plugin_action_links' ), 10, 2 );
		add_action( 'admin_init', array( $this, 'admin_init' ) );
	}

	public function admin_init()
	{
		register_setting( bsocial()->id_base, bsocial()->id_base, array( $this, 'sanitize_options' ) );

		// load the test suite if the user has permissions
		if( current_user_can( 'manage_options' ) )
		{
			$this->tests_loader();
		}
	}

	public function admin_menu()
	{
		add_submenu_page( 'plugins.php' , 'bSocial Configuration' , 'bSocial Configuration' , 'manage_options' , 'bsocial-options' , array( $this, 'options' ) );
	}

	public function plugin_action_links( $links, $file )
	{
		if ( $file == plugin_basename( __DIR__ .'/bsocial.php' ) )
		{
			$links[] = '<a href="plugins.php?page=bsocial-options">'. 'Settings' .'</a>';
		}

		return $links;
	}

	public function nonce_field()
	{
		wp_nonce_field( plugin_basename( __FILE__ ) , bsocial()->id_base .'-nonce' );
	}

	public function verify_nonce()
	{
		return wp_verify_nonce( $_POST[ bsocial()->id_base .'-nonce' ] , plugin_basename( __FILE__ ) );
	}

	public function get_field_name( $field_name )
	{
		if ( is_array( $field_name ) )
		{
			$field_name = implode( '][', $field_name );
		}

		return bsocial()->id_base . '[' . $field_name . ']';
	}

	public function get_field_id( $field_name )
	{
		if ( is_array( $field_name ) )
		{
			$field_name = implode( '-', $field_name );
		}

		return bsocial()->id_base . '-' . $field_name;
	}

	public function sanitize_options( $input )
	{

print_r( $input );
die;

		// filter the values so we only store known items
		$input = wp_parse_args( (array) $input , array(
			'open-graph' => 0,
			'featured-comments' => 0,
			'twitter-api' => 0,
			'twitter-comments' => 0,
			'twitter-app_id' => '',
			'twitter-card_site' => '',
			'facebook-api' => 0,
			'facebook-add_button' => 0,
			'facebook-comments' => 0,
			'facebook-admins' => '',
			'facebook-app_id' => '',
			'facebook-secret' => '',
		) );

		// sanitize the integer values
		foreach ( array(
			'open-graph',
			'featured-comments',
			'twitter-api',
			'twitter-comments',
			'facebook-api',
			'facebook-add_button',
			'facebook-comments',
		) as $key )
		{
			$input[ $key ] = absint( $input[ $key ] );
		}

		// sanitize the text values
		foreach ( array(
			'twitter-app_id',
			'twitter-card_site',
			'facebook-admins',
			'facebook-app_id',
			'facebook-secret',
		) as $key )
		{
			$input[ $key ] = wp_kses( $input[ $key ], array() );
		}

		return $input;
	}


	public function suppress_option( $field_name )
	{
		if ( ! $this->suppress )
		{
			$this->suppress = (array) bsocial()->options()->suppress;
		}

		// if nothing is marked for supression, then...
		if ( ! count( $this->suppress ) )
		{
			return FALSE;
		}

		// strings can only refer to top-level settings
		if ( is_string( $field_name ) )
		{
			return isset( $this->suppress[ $field_name ] );
		}

		$test = $this->suppress;
		foreach ( (array) $field_name as $key )
		{
			// we have a match at this section, se reset the text for the next section
			if ( isset( $test[ $key ] ) )
			{
				$test = (array) $test[ $key ];
				continue;
			}

			// no match was found, this setting is not suppressed
			return FALSE;
		}

		// if the above foreach contues to completion, it means
		// the input field name array was found among the suppressed fields
		return FALSE;

	}

	/**
	 * some rudimentary tests for the various social network integrations are included
	 * these are available on the settings page in the admin dashboard
	 */
	public function tests_loader()
	{
		if ( $this->tests_loaded )
		{
			return;
		}

		require_once __DIR__ . '/class-bsocial-twitter-test.php';
		new bSocialTwitter_Test();

		require_once __DIR__ . '/class-bsocial-linkedin-test.php';
		new bSocialLinkedIn_Test();

		require_once __DIR__ . '/class-bsocial-facebook-test.php';
		new bSocialFacebook_Test();

		$this->tests_loaded = TRUE;
	}//END test_loaded

	public function options()
	{

		wp_enqueue_script( 'jquery-ui-accordion' );

		require __DIR__ . '/templates/admin.php';

		// load the links to the test suite if the user has permissions
		if( current_user_can( 'manage_options' ) )
		{
			require __DIR__ . '/templates/test.php';
		}
	}
}
