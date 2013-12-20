<?php

class bSocial_Admin extends bSocial
{
	public function __construct()
	{
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_filter( 'plugin_action_links', array( $this, 'plugin_action_links' ), 10, 2 );
		add_action( 'admin_init', array( $this, 'admin_init' ) );
	}

	public function admin_init()
	{
		register_setting( 'bsocial-options', 'bsocial-options', array( $this, 'sanitize_options' ) );

		// load the test suite if the user has permissions
		if( current_user_can( 'activate_plugins' ))
		{
			$this->tests();
		}
	}

	public function admin_menu()
	{
		add_submenu_page( 'plugins.php' , __('bSocial Configuration') , __('bSocial Configuration') , 'manage_options' , 'bsocial-options' , array( $this, 'options' ));
	}

	public function plugin_action_links( $links, $file )
	{
		if ( $file == plugin_basename( dirname(__FILE__) .'/bsocial.php' ))
		{
			$links[] = '<a href="plugins.php?page=bsocial-options">'. __('Settings') .'</a>';
		}
	
		return $links;
	}

	public function sanitize_options( $input )
	{
	
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
		));
	
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

	public function options()
	{
		$options = get_option( 'bsocial-options' );
		require __DIR__ . '/templates/admin.php';

		// load the links to the test suite if the user has permissions
		if( current_user_can( 'activate_plugins' ) )
		{
			require __DIR__ . '/templates/test.php';
		}
	}
}
