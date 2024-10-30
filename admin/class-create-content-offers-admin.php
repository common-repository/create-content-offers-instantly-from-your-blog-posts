<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://glennsantos.com
 * @since      1.0.0
 *
 * @package    create_content_offers
 * @subpackage create_content_offers/admin
 */

//all the default values
class Create_Content_Offers_Defaults {

	public $header_type = 'h1';

	public $preform_message = 'Click here to get a free checklist of this post!';

	public $emailform_message = 'Enter your email to grab the checklist:';

	public $emailform_button = 'Show Me The Checklist!';

	public $offer_message = "Here's the checklist!";	
}

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    create_content_offers
 * @subpackage create_content_offers/admin
 * @author     Glenn Santos <glenn@memokitchen.com>
 */
class Create_Content_Offers_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */

	public $preform_message = 'Click here to get a free checklist of this post!';

	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		//add the options meta box into the pages and posts
		add_action( 'add_meta_boxes_post', array($this,'show_options') );
		add_action( 'add_meta_boxes_page', array($this,'show_options') );

		//add widget that shows list of signups

		add_action('wp_dashboard_setup', array ($this,'add_dashboard_widgets') );

		//save the values returned
		add_action( 'save_post', array($this,'save_options'), 10, 2 );

		//save values if at menu 
		add_action( 'admin_post_cco_update_menu_options', array($this, 'update_menu_options') );

		add_action( 'admin_menu', array($this,'show_emails_admin_page'), 10, 1 );

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Create_Content_Offers_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Create_Content_Offers_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/create-content-offers-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Create_Content_Offers_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Create_Content_Offers_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/create-content-offers-admin.js', array( 'jquery' ), $this->version, false );

	}

	//add per page/post options meta box so they can toggle views, heading types, etc
	public function show_options($post) {
    add_meta_box('cco_page_options',
				        __( 'Content Offer Options' ),
				        array ($this, 'get_options'),
				        $post->post_type,
				        'side',
				        'low'
    );
  }

  //expanded options form in admin area
  public function get_menu_options() {
  	$page = 'menu_options';

  	?>
  		<form class="cco_menu_options_form" method="post" action="admin-post.php">
  		<h1 class="content_offer_head">Content Offer Options</h1>

  		<?php $this->get_options($page); ?>

  		<h1>MailChimp Integration</h1>
	  		<p>
	  			Automatically add the emails you collect into your MailChimp list.<br/><span style="font-style:italic;">Don't have an account? <a href="http://eepurl.com/dhwXnL" target="_blank">Sign up for free using our referral link</a></span>
	  		</p>
	  		<div class="cco_mailchimp">
	  			<label class="textfield" for="cco_mailchimp_apikey">API Key</label>
	  			<a href="https://kb.mailchimp.com/integrations/api-integrations/about-api-keys">How to get your API key</a>
	  			<input type="text" name="cco_mailchimp_apikey" id="cco_mailchimp_apikey" value="<?php echo get_option('cco_mailchimp_apikey'); ?>" />
	  			
	  			<label class="textfield" for="cco_mailchimp_listid">List ID</label>
	  			<a href="https://kb.mailchimp.com/lists/manage-contacts/find-your-list-id">How to get your List ID</a>
	  			<input type="text" name="cco_mailchimp_listid" id="cco_mailchimp_listid" value="<?php echo get_option('cco_mailchimp_listid'); ?>" 
	  			/>
	  			
	  		</div>

	  		<div>
	  			<input type="submit" name="submit" value="Update Options"  />
	  		</div>

	  		<div style="margin-top:1em;font-style: italic;">
	  			<a href="https://glennsantos.org" target="_blank" style="color:sienna;">Need a WordPress developer? I can help. https://glennsantos.org</a>
	  		</div>

  		</form>

  	<?php
  }

  //base option form included in posts
  public function get_options($post) {


  	$set_default = get_option( 'cco_set_default');
  	$defaults = new Create_Content_Offers_Defaults();

  	//for post options, get meta
  	if (isset($post->ID)) {
  		$show_checklist = get_post_meta( $post->ID, 'cco_show_checklist', true );
  		$header_type = get_post_meta( $post->ID, 'cco_header_type', true );	
  		$preform_message = get_post_meta( $post->ID, 'cco_preform_message', true );
  		$emailform_message = get_post_meta( $post->ID, 'cco_emailform_message', true );
  		$emailform_button = get_post_meta( $post->ID, 'cco_emailform_button', true );
  		$offer_message = get_post_meta( $post->ID, 'cco_offer_message', true );
  	} 

  	$cco_linkback = get_option('cco_linkback');
  	
  	//set alternate values from defaults, if there's none doesn't
  	if (!isset($show_checklist) AND 'yes' == $set_default) {
		  $show_checklist = get_option('cco_show_checklist');
  	}
		
		if (!isset($header_type) AND 'menu_options' == $post) {
  		$header_type = get_option('cco_header_type');
  	}
  	if (!isset($header_type)) {
  		$header_type = $defaults->header_type;
  	}

  	if (!isset($preform_message) AND 'menu_options' == $post) {
  		$preform_message = stripslashes(get_option('cco_preform_message'));
  	} 
  	
  	if (!isset($emailform_message) AND 'menu_options' == $post) {
  		$emailform_message = stripslashes(get_option('cco_emailform_message'));
		} 

		if (!isset($emailform_button) AND 'menu_options' == $post) {
  		$emailform_button = stripslashes(get_option('cco_emailform_button'));
  	}

  	if (!isset($offer_message) AND 'menu_options' == $post) {
  		$offer_message = stripslashes(get_option('cco_offer_message'));
  	} 

		//set up placeholder values for blank fields
		$placeholder_preform_message = $defaults->preform_message;
		if ('menu_options' != $post AND get_option('cco_preform_message')) {
		  $placeholder_preform_message = stripslashes(get_option('cco_preform_message'));
		}

		$placeholder_emailform_message = $defaults->emailform_message;
	  if ('menu_options' != $post AND get_option('cco_emailform_message')) {
	  	$placeholder_emailform_message = stripslashes(get_option('cco_emailform_message'));
	  }
  	
  	$placeholder_emailform_button = $defaults->emailform_button;
		if ('menu_options' != $post AND get_option('cco_emailform_button')) {
			$placeholder_emailform_button = stripslashes(get_option('cco_emailform_button'));
		}

		$placeholder_offer_message = $defaults->offer_message;	
	  if ('menu_options' != $post AND get_option('cco_offer_message')) {
	  	$placeholder_offer_message = stripslashes(get_option('cco_offer_message'));
	  }

  	//generate the form nonce
  	wp_nonce_field( basename( __FILE__ ), 'cco_page_options' );

  	//echo checked=checked and selected=selected 
  	$echo = true;

  	?>

  	<div class="cco_post_options_form">
  		<?php if ('menu_options' == $post): ?>
  			<input type="hidden" name="action" value="cco_update_menu_options" />
				<?php wp_nonce_field( 'cco_update_menu_options_verify' ); ?>
  			<div>
  				<input type="hidden" name="cco_linkback" value="no" />
	  			<input type="checkbox" name="cco_linkback" id="cco_linkback" value="yes" <?php checked($cco_linkback,'yes', $echo); ?> /> <label for="cco_linkback">Show SiteStaffer links below the form (thanks for the support!)</label>
	  		</div>
	  		<div>
  				<input type="hidden" name="cco_set_default" value="no" />
	  			<input type="checkbox" name="cco_set_default" id="cco_set_default" value="yes" <?php checked($set_default,'yes', $echo); ?> /> <label for="cco_set_default">Show Checklist for ALL Posts</label>
  			</div>
  		<?php else: ?>
	  		<div>
	  			<input type="hidden" name="cco_show_checklist" value="no" />
	  			<input type="checkbox" name="cco_show_checklist" id="cco_show_checklist" value="yes" <?php checked($show_checklist,'yes', $echo); ?> /> <label for="cco_show_checklist">Show Checklist in Post</label>
	  		</div>
	  	<?php endif; ?>

	  	<div>
	  		<label class="textfield" for="cco_header_type">Headings to Convert into Checklist</label>
	  		<select name="cco_header_type" id="cco_header_type">
	  			<?php for ($i=1; $i <= 6; $i++): ?>
	  				<option value="<?php echo 'h'.$i; ?>" <?php selected($header_type, 'h'.$i); ?> ><?php echo 'Heading '.$i; ?></option>
	  			<?php endfor; ?>
	  			<option value="none" <?php selected($header_type, 'none'); ?> >None, just collect emails</option>
	  		</select>
	  	</div>

	  	<div>
	  		<label class="textfield" for="cco_preform_message">Call-to-Action Text</label>
	  		<div style="color:#888;font-size:90%;">User clicks on this to show the email form.<br/>We find that this increases the chances of collecting their emails.</div>
	  		<textarea name="cco_preform_message" id="cco_preform_message" rows="6" placeholder="<?php echo $placeholder_preform_message; ?>"><?php echo $preform_message; ?></textarea>
	  	</div>

	  	<div>
	  		<label class="textfield" for="cco_emailform_message">Email Form Message</label>
	  		<div style="color:#888;font-size:90%;">Tell them what they get in exchange for their email.</div>
	  		<textarea name="cco_emailform_message" id="cco_emailform_message" rows="6" placeholder="<?php echo $placeholder_emailform_message; ?>"><?php echo $emailform_message; ?></textarea>
	  	</div>

	  	<div>
	  		<label class="textfield" for="cco_emailform_button">Email Form Button Text</label>
	  		<div style="color:#888;font-size:90%;">Hint: use action verbs for best results</div>
	  		<input type="text" name="cco_emailform_button" id="cco_emailform_button" value="<?php echo $emailform_button; ?>" placeholder="<?php echo $placeholder_emailform_button; ?>"/>
	  	</div>

	  	<div>
	  		<label class="textfield" for="cco_offer_message">Checklist Intro</label>
	  		<div style="color:#888;font-size:90%;">The message shown at the top of the checklist.</div>
	  		<textarea name="cco_offer_message" id="cco_offer_message" rows="9" placeholder="<?php echo $placeholder_offer_message; ?>"><?php echo $offer_message; ?></textarea>
	  	</div>

	  </div>

  	<?php
  }

  //save the data submitted in the metabox
  /* Save the meta box's post metadata. */
	public function save_options( $post_id, $post ) {

		//do some verification.

		//verify for menu options update
		if ('menu_options' == $post) {
			if ( !current_user_can( 'administrator' ) ) {
			  return $post_id;
			}
			// Check that nonce field
			check_admin_referer( 'cco_update_menu_options_verify' );

		//verify for post options update
		} else {
			/* Verify the nonce before proceeding. */
			if ( !isset( $_POST['cco_page_options'] ) OR !wp_verify_nonce( $_POST['cco_page_options'], basename( __FILE__ ) ) ) {
				return $post_id;
			}
			/* Get the post type object and check if the current user has permission to edit the post. */
		  $post_type = get_post_type_object( $post->post_type );
		  if ( !current_user_can( $post_type->cap->edit_post, $post_id ) ) {
		    return $post_id;
		  }
		}

	  //Get the checklist info, sanitize it and only update if the new value is different and exists
	  $show_checklist = sanitize_text_field($_POST['cco_show_checklist']);
	  $old_checklist = get_post_meta( $post_id, 'cco_show_checklist', true );
	  if ( $old_checklist != $show_checklist ) {
	    update_post_meta( $post_id, 'cco_show_checklist', $show_checklist );
	  }

	  //Get the header type, sanitize it and only update if the new value is different and exists
	  $header_type = sanitize_text_field($_POST['cco_header_type']);
	  $old_header = get_post_meta( $post_id, 'cco_header_type', true );
	  if ( $old_header != $header_type ) {
	    update_post_meta( $post_id, 'cco_header_type', $header_type );
	  } 

	  //Get the email message, sanitize it and only update if the new value is different and exists
	  $preform_message = sanitize_textarea_field($_POST['cco_preform_message']);
	  $old_preform = get_post_meta( $post_id, 'cco_preform_message', true );
	  if ( $old_preform != $preform_message ) {
	    update_post_meta( $post_id, 'cco_preform_message', $preform_message );
	  }

	  //Get the email message, sanitize it and only update if the new value is different and exists
	  $emailform_message = sanitize_textarea_field($_POST['cco_emailform_message']);
	  $old_message = get_post_meta( $post_id, 'cco_emailform_message', true );
	  if ( $old_message != $emailform_message ) {
	    update_post_meta( $post_id, 'cco_emailform_message', $emailform_message );
	  }

	  //Get the email button text, sanitize it and only update if the new value is different and exists
	  $emailform_button = sanitize_text_field($_POST['cco_emailform_button']);
	  $old_button = get_post_meta( $post_id, 'cco_emailform_button', true );
	  if ( $old_button != $emailform_button ) {
	    update_post_meta( $post_id, 'cco_emailform_button', $emailform_button );
	  }

	  //Get the email message, sanitize it and only update if the new value is different and exists
	  $offer_message = sanitize_textarea_field($_POST['cco_offer_message']);
	  $old_offer = get_post_meta( $post_id, 'cco_offer_message', true );
	  if ( $old_offer != $offer_message ) {
	    update_post_meta( $post_id, 'cco_offer_message', $offer_message );
	  }

		//only save these if in menu options
		if ('menu_options' == $post) {
			$set_default = sanitize_text_field($_POST['cco_set_default']);
			if ($set_default) {
				update_option('cco_set_default',$set_default);	
			}

			$cco_linkback = sanitize_text_field($_POST['cco_linkback']);
			if ($cco_linkback) {
				update_option('cco_linkback',$cco_linkback);	
			}

		  update_option( 'cco_show_checklist', $show_checklist);
		  update_option( 'cco_offer_message', $offer_message );
	  	update_option( 'cco_header_type', $header_type );
	  	update_option( 'cco_preform_message', $preform_message );
	  	update_option( 'cco_emailform_message', $emailform_message );
	  	update_option( 'cco_emailform_button', $emailform_button );

	  	//save mailchimp info
	  	//API docs: http://developer.mailchimp.com/documentation/mailchimp/
		  $api_key = sanitize_text_field($_POST['cco_mailchimp_apikey']);
		  $old_key = get_option('cco_mailchimp_apikey');
		  if ( $old_key != $api_key ) {
		    update_option('cco_mailchimp_apikey', $api_key );
		  }

		  $list_id = sanitize_text_field($_POST['cco_mailchimp_listid']);
		  $old_id = get_option('cco_mailchimp_listid');
		  if ( $old_id != $list_id ) {
		    update_option('cco_mailchimp_listid', $list_id );
		  }

	  	//back to the rate page
			wp_redirect(admin_url('admin.php?page=checklist-content-offer&updated=yes'));
			exit;
	  }

	}

	//save options	
	function update_menu_options() {
		$page = 'menu_options';
		$this->save_options($page, $page);
	}

	//show the list of emails
	public function show_email_list() {
		$email_list = get_option('cco_email_list');

		if (isset($_GET['updated']) AND 'yes' == $_GET['updated']) {
			echo '<div class="notice notice-success is-dismissible">';
			echo 		'<p>';
			_e( 			'Options Updated!' );
			echo 		'</p>';
			echo '</div>';
		}
				
		if (isset($_GET['page']) AND 'checklist-content-offer' == $_GET['page']) {
			echo '<h1 class="content_offer_head">Content Offer Emails</h1>';
		}

		if ($email_list) {
			foreach ($email_list as $post_id => $data) {
				echo '<p><h3><span style="font-weight:normal;font-size:80%;">via</span>  "<strong><a href="'.get_edit_post_link($post_id,'').'">'.get_the_title( $post_id ).'</a></strong>"</h3>';
				$c = 0;
				foreach ($data as $timestamp => $email) {
					echo '<div style="margin-left:1em;">'.++$c.'. <strong><a href="mailto:'.$email.'">'.$email.'</a></strong> : '.date('M d, Y, g:i A',$timestamp).'</div>';
				}
				echo '</p>';
			}	
		} else {
			echo '<p><h3>No emails yet.</h3></p>';
		}
	}

	//show the options and the email list
	public function show_emails_admin_page() {
		add_menu_page( 'Content Offer Emails', 'Content Offer Emails', 'edit_pages', 'checklist-content-offer', array($this, 'show_menu_options'), 'dashicons-email-alt', 26 );
	}

	public function show_menu_options() {
		$this->show_email_list();
		$this->get_menu_options();
	}

	//add list to dashboard
	public function add_dashboard_widgets() {
		wp_add_dashboard_widget('cco_email_list', 'Content Offer Emails', array($this, 'show_email_list_dashboard') );
	}

	public function show_email_list_dashboard() {
		$this->show_email_list();
	}
	

}
