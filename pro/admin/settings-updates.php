<?php 

class acf_settings_updates {
	
	var $view;
	
	
	/*
	*  __construct
	*
	*  Initialize filters, action, variables and includes
	*
	*  @type	function
	*  @date	23/06/12
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function __construct() {
	
	
		// actions
		add_action( 'admin_menu', 				array( $this, 'admin_menu' ), 20 );
	}
	
	
	/*
	*  admin_menu
	*
	*  This function will add the ACF menu item to the WP admin
	*
	*  @type	action (admin_menu)
	*  @date	28/09/13
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function admin_menu() {
		
		// add page
		$page = add_submenu_page('edit.php?post_type=acf-field-group', __('Updates','acf'), __('Updates','acf'), 'manage_options','acf-settings-updates', array($this,'html') );
		
		
		// actions
		add_action('load-' . $page, array($this,'load'));
		
	}
	
	
	/*
	*  load
	*
	*  description
	*
	*  @type	function
	*  @date	7/01/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function load() {
		
		// $_POST
		if( acf_verify_nonce('activate_pro_licence') )
		{
			$this->activate_pro_licence();
		}
		elseif( acf_verify_nonce('deactivate_pro_licence') )
		{
			$this->deactivate_pro_licence();
		}
		
		
		// view
		$this->view = array(
			'license'			=> acf_pro_get_license(),
			'current_version'	=> acf_get_setting('version'),
			'remote_version'	=> '',
			'update_available'	=> false,
			'changelog'			=> ''
		);
		
		
		// force recheck?
		if( !empty($_GET['force-recheck']) )
		{
			delete_transient( 'acf_pro_get_remote_info' );
		}
		
		
		// vars
		$info = acf_pro_get_remote_info();
		
		
		// validate
        if( !$info )
        {
        	acf_add_admin_notice('<b>Error</b>. Could not connect to update server', 'error');
        	return;
        }
        
        
        // add info to view
        $this->view['remote_version'] = $info['version'];
        
        
        // add changelog if the remote version is '>' than the current version
		if( version_compare($this->view['remote_version'], $this->view['current_version'], '>') )
        {
        	 $this->view['update_available'] = true;
        	 $this->view['changelog'] = $info['changelog'];
        }
		
		
	}
	
	
	/*
	*  html
	*
	*  description
	*
	*  @type	function
	*  @date	7/01/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function html() {
		
		// load view
		acf_pro_get_view('settings-updates', $this->view);
		
	}
	
	
	/*
	*  activate_pro_licence
	*
	*  description
	*
	*  @type	function
	*  @date	16/01/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function activate_pro_licence() {
		
		// connect
		$args = array(
			'_nonce'		=> wp_create_nonce('activate_pro_licence'),
			'acf_license'	=> acf_extract_var($_POST, 'acf_pro_licence'),
			'acf_version'	=> acf_get_setting('version'),
			'wp_name'		=> get_bloginfo('name'),
			'wp_url'		=> get_bloginfo('url'),
			'wp_version'	=> get_bloginfo('version'),
			'wp_language'	=> get_bloginfo('language'),
			'wp_timezone'	=> get_option('timezone_string'),
		);
		
		
		// connect
		$response = acf_pro_get_remote_response( 'activate-license', $args );
		
		echo '<pre>';
			print_r( $response );
		echo '</pre>';
		die;
		// validate
		if( empty($response) )
		{
			acf_add_admin_notice('<b>Licence key not found</b>. Make sure you have copied your licence key exactly as it appears in your receipt', 'error');
		}
		
		
		// vars
		$response = json_decode($response, true);
		$class = '';
		
		
		// action
		if( $response['status'] == 1 )
		{
			acf_pro_update_license($response['license']);
		}
		else
		{
			$class = 'error';
		}
		
		
		// show message
		if( $response['message'] )
		{
			acf_add_admin_notice($response['message'], $class);
		}
	}
}


// initialize
new acf_settings_updates();

?>