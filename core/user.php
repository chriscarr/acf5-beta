<?php 

class acf_controller_user {
	
	/*
	*  Constructor
	*
	*  This function will construct all the neccessary actions and filters
	*
	*  @type	function
	*  @date	23/06/12
	*  @since	3.1.8
	*
	*  @param	N/A
	*  @return	N/A
	*/
	
	function __construct()
	{
		// actions
		add_action( 'admin_enqueue_scripts',		array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'login_form_register', 			array( $this, 'admin_enqueue_scripts' ) );
		
		// render
		add_action( 'show_user_profile', 			array( $this, 'edit_profile' ) );
		add_action( 'edit_user_profile',			array( $this, 'edit_user' ) );
		add_action( 'user_new_form',				array( $this, 'user_new_form' ) );
		add_action( 'register_form',				array( $this, 'register_user' ) );
		
		// save
		//add_action('edit_user_profile_update',	array($this, 'save_user'));
		//add_action('personal_options_update',		array($this, 'save_user'));
		add_action('user_register',					array($this, 'save_user'));
		add_action('profile_update',				array($this, 'save_user'));
		
	}
	
	
	/*
	*  validate_page
	*
	*  This function will check if the current page is for a post/page edit form
	*
	*  @type	function
	*  @date	23/06/12
	*  @since	3.1.8
	*
	*  @param	N/A
	*  @return	(boolean)
	*/
	
	function validate_page()
	{
		// global
		global $pagenow;
		
		
		// vars
		$return = false;
		
		
		// validate page
		if( in_array( $pagenow, array('profile.php', 'user-edit.php', 'user-new.php', 'wp-login.php') ) )
		{
			$return = true;
		}
		
		
		// return
		return $return;
	}
	
	
	/*
	*  admin_enqueue_scripts
	*
	*  This action is run after post query but before any admin script / head actions. 
	*  It is a good place to register all actions.
	*
	*  @type	action (admin_enqueue_scripts)
	*  @date	26/01/13
	*  @since	3.6.0
	*
	*  @param	N/A
	*  @return	N/A
	*/
	
	function admin_enqueue_scripts()
	{
		// validate page
		if( ! $this->validate_page() )
		{
			return;
		}

		
		// load acf scripts
		acf_enqueue_scripts();
	}
	
	
	/*
	*  edit_profile
	*
	*  description
	*
	*  @type	function
	*  @date	8/10/13
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function edit_profile( $profileuser ) {
		
		// render
		$this->render( $profileuser->ID, 'edit', 'tr' );
		
	}
	
	
	/*
	*  register_user
	*
	*  description
	*
	*  @type	function
	*  @date	8/10/13
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function register_user() {
		
		// render
		$this->render( 0, 'register', 'div' );
	}
	
	
	/*
	*  edit_user
	*
	*  description
	*
	*  @type	function
	*  @date	8/10/13
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function edit_user( $profileuser ) {
		
		// render
		$this->render( $profileuser->ID, 'edit', 'tr' );
		
	}
	
	
	/*
	*  user_new_form
	*
	*  description
	*
	*  @type	function
	*  @date	8/10/13
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function user_new_form() {
		
		// render
		$this->render( 0, 'add', 'tr' );
		
	}
	
	
	/*
	*  render
	*
	*  This function will render ACF fields for a given $post_id parameter
	*
	*  @type	function
	*  @date	7/10/13
	*  @since	5.0.0
	*
	*  @param	$user_id (int) this can be set to 0 for a new user
	*  @param	$user_form (string) used for location rule matching. edit | add | register
	*  @param	$el (string)
	*  @return	n/a
	*/
	
	function render( $user_id, $user_form, $el = 'tr' ) {
		
		// vars
		$post_id = "user_{$user_id}";
		$show_title = true;
		
		
		// show title
		if( $user_form == 'register' )
		{
			$show_title = false;
		}
		
		
		// args
		$args = array(
			'user_id'	=> 'new',
			'user_form'	=> $user_form
		);
		
		if( $user_id )
		{
			$args['user_id'] = $user_id;
		}
		
		
		// get field groups
		$field_groups = acf_get_field_groups( $args );
		
		
		// render
		if( !empty($field_groups) ):
			
			acf_form_data(array( 
				'post_id'	=> $post_id, 
				'nonce'		=> 'user' 
			));
			
			foreach( $field_groups as $field_group ): 
				
				$fields = acf_get_fields( $field_group );

				?>
				<?php if( $show_title && $field_group['style'] == 'default' ): ?>
					<h3><?php echo $field_group['title']; ?></h3>
				<?php endif; ?>
				
				<?php if( $el == 'tr' ): ?>
					<table class="form-table">
						<tbody>
				<?php endif; ?>
				
					<?php acf_render_fields( $post_id, $fields, $el, 'field' ); ?>
				
				<?php if( $el == 'tr' ): ?>
						</tbody>
					</table>
				<?php endif; ?>
				<?php 
				
			endforeach; 
		
		endif;
		
	}
	
	
	/*
	*  save_user
	*
	*  description
	*
	*  @type	function
	*  @date	8/10/13
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function save_user( $user_id ) {
		
		// verify and remove nonce
		if( ! acf_verify_nonce('user') )
		{
			return $user_id;
		}
		
	    
	    // save data
	    if( acf_validate_save_post(true) )
		{
			acf_save_post( "user_{$user_id}" );
		}	
	}
			
}

new acf_controller_user();

?>