<?php

class bSocial_Admin
{
	public $tests_loaded = FALSE;
	public $options_key = array();
	public $options_sanitizer = array(
		// social network integrations
		'facebook' => array(
			'enable' => 'absint',
			'meta' => 'absint',
			'js' => 'absint',

			'app_id' => 'wp_kses_data',
			'secret' => 'wp_kses_data',

			'admins' => 'wp_kses_data',
			'page' => 'wp_kses_data',

			'add_button' => 'absint',
			'comments' => 'absint',
		),
		'linkedin' => array(
			'enable' => 'absint',
			'meta' => 'absint',
			'js' => 'absint',

			'consumer_key' => 'wp_kses_data',
			'consumer_secret' => 'wp_kses_data',
			'access_token' => 'wp_kses_data',
			'access_secret' => 'wp_kses_data',
		),
		'twitter' => array(
			'enable' => 'absint',
			'meta' => 'absint',
			'js' => 'absint',

			'consumer_key' => 'wp_kses_data',
			'consumer_secret' => 'wp_kses_data',
			'access_token' => 'wp_kses_data',
			'access_secret' => 'wp_kses_data',

			'username' => 'wp_kses_data',
			'comments' => 'absint',
		),

		// features
		'featuredcomments' => array(
			'enable' => 'absint',

			'use_commentdate' => 'absint',
			'add_to_waterfall' => 'absint',
		),
		'opengraph' => array(
			'enable' => 'absint',
			'type' => 'wp_kses_data',
		),
	);

	public function __construct()
	{
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_filter( 'plugin_action_links', array( $this, 'plugin_action_links' ), 10, 2 );
		add_action( 'admin_init', array( $this, 'admin_init' ) );
	}// END __construct

	public function admin_init()
	{
		register_setting( bsocial()->id_base, bsocial()->id_base, array( $this, 'sanitize_options' ) );

		// load the test suite if the user has permissions
		if( current_user_can( 'manage_options' ) )
		{
			$this->tests_loader();
		}
	}// END admin_init

	public function admin_menu()
	{
		add_submenu_page( 'plugins.php', 'bSocial Configuration', 'bSocial Configuration', 'manage_options', 'bsocial-options', array( $this, 'options_page' ) );
	}// END admin_menu

	public function plugin_action_links( $links, $file )
	{
		if ( $file == plugin_basename( __DIR__ .'/bsocial.php' ) )
		{
			$links[] = '<a href="plugins.php?page=bsocial-options">Settings</a>';
		}

		return $links;
	}// END plugin_action_links

	public function nonce_field()
	{
		wp_nonce_field( plugin_basename( __FILE__ ), bsocial()->id_base .'-nonce' );
	}

	public function verify_nonce()
	{
		return wp_verify_nonce( $_POST[ bsocial()->id_base .'-nonce' ], plugin_basename( __FILE__ ) );
	}// END verify_nonce

	public function get_field_name( $field_name )
	{
		if ( is_array( $field_name ) )
		{
			$field_name = implode( '][', $field_name );
		}

		return bsocial()->id_base . '[' . $field_name . ']';
	}// END get_field_name

	public function get_field_id( $field_name )
	{
		if ( is_array( $field_name ) )
		{
			$field_name = implode( '-', $field_name );
		}

		return bsocial()->id_base . '-' . $field_name;
	}// END get_field_id

	public function sanitize_options( $input )
	{
		$result = $this->_sanitize_options( $input, bsocial()->options(), bsocial()->options_default(), $this->options_sanitizer );

		return $result;
	}// END sanitize_options

	private function _sanitize_options( $new, $old, $default, $sanitizer )
	{

		// if the sanitizer is an array, then recurse into it
		if ( is_array( $sanitizer ) )
		{
			$return = (object) array();

			// objects must be arrays
			$new = (array) $new;
			$old = (array) $old;
			$default = (array) $default;

			foreach ( $sanitizer as $k => $v )
			{
				$return->$k = $this->_sanitize_options( $new[ $k ], $old[ $k ], $default[ $k ], $sanitizer[ $k ] );
			}
		}//END if
		else
		{
			// if the sanitizer is not an array, then we're
			// at the end of the branch and have a sanitizer callback function

			// empty values often represent unselected checkboxes
			// initialize those as an empty string to make the following work
			$new = empty( $new ) ? '' : $new;

			// is there input for this?
			// is the input a string?
			// is the callback callable?
			// use old values or defaults if not
			if (
				! is_string( $new ) ||
				! function_exists( $sanitizer )
			)
			{
				$return = isset( $old ) ? $old : $default;
			}
			else
			{
				// this looks okay, sanitize it
				$return = call_user_func( $sanitizer, $new );
			}
		}//END else

		return $return;
	}// END _sanitize_options

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
			// we have a match at this section, so reset the text for the next section
			if ( isset( $test[ $key ] ) )
			{
				$test = (array) $test[ $key ];
				continue;
			}

			// no match was found, this setting is not suppressed
			return FALSE;
		}//END foreach

		// if the above foreach contues to completion, it means
		// the input field name array was found among the suppressed fields
		return FALSE;

	}// END suppress_option

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

		require_once __DIR__ . '/class-bsocial-reddit-test.php';
		new bSocialReddit_Test();

		$this->tests_loaded = TRUE;
	}//END tests_loader

	public function options_page()
	{

		wp_enqueue_script( 'jquery-ui-accordion' );

		require __DIR__ . '/templates/admin.php';

		// load the links to the test suite if the user has permissions
		if( current_user_can( 'manage_options' ) )
		{
			require __DIR__ . '/templates/tests.php';
		}
	}//END options_page
}//END class