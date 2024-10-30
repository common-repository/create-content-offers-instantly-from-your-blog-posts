<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://glennsantos.com
 * @since      1.0.0
 *
 * @package    create_content_offers
 * @subpackage create_content_offers/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    create_content_offers
 * @subpackage create_content_offers/public
 * @author     Glenn Santos <glenn@memokitchen.com>
 */
class Create_Content_Offers_Public {

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
	 * @since    1.0.1
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */


	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		
		add_action( 'admin_post_nopriv_cco_emailform', array($this, 'process_email') );
		add_action( 'admin_post_cco_emailform', array($this, 'process_email') );

		//add the checklist 
		$email_cookie = $this->get_checklist_cookie();

		if ($email_cookie){
			add_filter( 'the_content', array( $this, 'add_checklist') );	
		} else {
			add_filter( 'the_content', array( $this, 'add_email_form') );
		}

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/create-content-offers-public.css', array(), $this->version, 'all' );
		wp_enqueue_style( 'dashicons' );
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
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
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/create-content-offers-public.js', array( 'jquery' ), $this->version, false );

	}

	/*
	 * Add the email form
	 */

	public function add_email_form($content) {
		$post_id = get_the_ID();

		$set_default = get_option( 'cco_set_default');

  	require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-create-content-offers-admin.php';
		$defaults = new Create_Content_Offers_Defaults();


		//don't show if feed
		if (is_feed()) {
			return $content;
		}

		//NOTE: if the post has its own content, use that instead of the defaults even if $set_default is true. use defaults if no values

		$preform_message = get_post_meta( $post_id, 'cco_preform_message', true );
  	if (!$preform_message AND 'yes' == $set_default) {
		  $preform_message = stripslashes(get_option('cco_preform_message'));
	  } 
	  if (!$preform_message) {
	  	$preform_message = $defaults->preform_message;
	  } 

		$emailform_message = get_post_meta( $post_id, 'cco_emailform_message', true );
		if (!$emailform_message AND 'yes' == $set_default) {
		  $emailform_message = stripslashes(get_option('cco_emailform_message'));
		}
		if (!$emailform_message) {
	  	$emailform_message = $defaults->emailform_message;
	  }

  	$emailform_button = get_post_meta( $post_id, 'cco_emailform_button', true );
  	if (!$emailform_button AND 'yes' == $set_default) {
		  $emailform_button = stripslashes(get_option('cco_emailform_button'));
		 }
		if (!$emailform_button) {
	  	$emailform_button = $defaults->emailform_button;
  	}


		$content .= '<form action="'.admin_url('admin-post.php').'" method="post" class="cco_emailform emailform_for_post-'.$post_id.'">';
		$content .= '<span id="show_content_offer_cco"></span>';
		$content .= '<a href="#show_content_offer_cco">'.nl2br($preform_message).'</a>';
		$content .= '<input type="hidden" name="action" value="cco_emailform">';
		$content .= '<input type="hidden" name="post_id" value="'.$post_id.'">';	
		$content .= '<div class="cco_optin">';
		$content .=		'<div>'.nl2br($emailform_message).'</div>';
		$content .= 	'<input type="email" name="cco_email" />';
		$content .= 	'<input type="submit" name="cco_send_form" value="'.$emailform_button.'" />';
		$content .= '</div>'; 
			
		$content .= '</form>';
		$content .= $this->sitestaffer_footer();

		return $content;
	}

	public function process_email() {

		if (array_key_exists('cco_send_form', $_POST) && $_POST['cco_send_form']) {
			$email = $_POST['cco_email'];
			$post_id = $_POST['post_id'];
			list($local, $domain) = explode('@',$email);

			if (is_email($email)) {
				$this->save_email($email,$post_id);

				//add to mailchimp list
				$this->add_to_mailchimp($email);
			}

		}

		wp_redirect( wp_get_referer().'?subscribed=yes#create_content_offers');
		exit;
	}

	public function save_email($email,$post_id) {
		$post_id = intval($post_id);

		$email_list = get_option('cco_email_list');
		$now = current_time('timestamp',0);

		$email_list[$post_id][$now] = sanitize_email($email);

		update_option('cco_email_list', $email_list);

		$this->set_checklist_cookie();
	}

	//add the email to the owners' mailchimp list
	public function add_to_mailchimp($email) {
		
		// MailChimp API credentials
		// https://developer.mailchimp.com/documentation/mailchimp/
    $apiKey = get_option('cco_mailchimp_apikey');
    if (!$apiKey) {
    	return false;
    }

		$listID = get_option('cco_mailchimp_listid');
		if (!$listID) {
			return false;
		}
    
    // MailChimp API URL
    $memberID = md5(strtolower($email));
    $dataCenter = substr($apiKey,strpos($apiKey,'-')+1);
    $url = 'https://' . $dataCenter . '.api.mailchimp.com/3.0/lists/' . $listID . '/members/' . $memberID;
    
    // member information
    $json = json_encode([
        'email_address' => $email,
        'status'        => 'subscribed',
    ]);
    
    // send a HTTP POST request with curl
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_USERPWD, 'user:' . $apiKey);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

	}

	/*
	 *
	 *	add checklist at the end of the content
	 *
	 */

	public function add_checklist($content) {
		if (!is_single()) {
			return $content;
		}

		$post_id = get_the_ID();
		$set_default = get_option( 'cco_set_default');

		//don't show if post says no (or not yes)
		$show_checklist = get_post_meta( $post_id, 'cco_show_checklist', true );
		if ('yes' != $show_checklist AND 'yes' != $set_default) {
			return $content;
		}

  	require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-create-content-offers-admin.php';
		$defaults = new Create_Content_Offers_Defaults();

		//get the data from meta, or options if none and default, or use defaults

		$offer_message = get_post_meta( $post_id, 'cco_offer_message', true );
  	if (!$offer_message AND 'yes' == $set_default) {
  		$offer_message = stripslashes(get_option('cco_offer_message'));
  	}
  	if (!$offer_message) {
  		$offer_message = $defaults->offer_message;
  	}

		//get header to show
		$header_type = get_post_meta( $post_id, 'cco_header_type', true );
		if (!$header_type AND 'yes' == $set_default) {
		  	$header_type = get_option('cco_header_type');
		}
		if (!$header_type) {
		  $header_type = $defaults->header_type;
  	}

		//only show the thank you message 
		if ('yes' == $set_default AND 'none' == $header_type) {
			if (isset($_GET['subscribed']) AND 'yes' != $_GET['subscribed']) {
				return $content; //only show in first sub, not in subsequent ones
			} 
			$offer_message = '<div style="text-align:center;">'.$offer_message.'</div>'; //center the message	if just for subscriptions
		}

		$dom = new domDocument('1.0', 'utf-8'); 
		// load the html into the object ***/ 
		$dom->loadHTML($content); 
		
		//discard white space 
		$dom->preserveWhiteSpace = false; 

		//get headers by type (TODO: allow other tags later)
		$headers = $dom->getElementsByTagName($header_type);


		//setup checklist
		$checklist = $this->get_checklist_cookie();
		if (!is_array($checklist)) {
			$checklist = [];
			$checklist[$post_id] = array_fill(0,$headers->length, 0);
			$checklist = json_encode($checklist);
			$this->set_checklist_cookie($checklist);
		} 

		//add a message
		$content .= '<div class="cco_offer" data-post_id="'.$post_id.'" id="create_content_offers">';
		$content .= $offer_message;
		$counter = 0;
		$current = 1;
		$echo = false;
		if ($headers->length) {
			foreach ($headers as $header) {
				++$counter;

				$checked = '';
				if (isset($checklist[$post_id][$counter])) {
					$checked = $checklist[$post_id][$counter];
				}
				
				$content .= '<p>';
				$content .= 	'<input class="cco_checkbox" type="checkbox" id="checkbox_id-'.$counter.'" data-checkbox_id="'.$counter.'" '.checked($checked,$current,$echo).' />';
				$content .= 	'<label class="cco_checkmark" for="checkbox_id-'.$counter.'"></label><label class="cco_label" for="checkbox_id-'.$counter.'">'.$header->nodeValue.'</label>';
				$content .= '</p>'; 
			}
		}
		
		$content .= '</div>';
		$content .= $this->sitestaffer_footer();
	
		
		return $content;
	}

	/***************************
	 **
	 **    COOKIE FUNCTIONS
	 **
	 ***************************/

	//set a cookie for 1 year
	protected function set_checklist_cookie($value = 1) {
		if (headers_sent()) {
			return false;
		}
		$now = current_time('timestamp',0);
		$expiry = $now+60*60*24*1000;
		setcookie('cco_checklist', $value, $expiry, '/', COOKIE_DOMAIN, false);
	}

	//get email cookie
	protected function get_checklist_cookie() {
		if (array_key_exists('cco_checklist', $_COOKIE)) {
			return json_decode($_COOKIE['cco_checklist'],true);
		}

		return false;
	}

	//create the footer
	protected function sitestaffer_footer() {
		$cco_linkback = get_option('cco_linkback');
 
		if ('yes' != $cco_linkback) {
			return '';
		}
		
		return '<div style="float:right;font-size:65%;color:#888;margin-right:0.5em;">built by <a href="https://sitestaffer.com" target="_blank" style="color:#888 !important;">SiteStaffer</a></div>';
	}

}
