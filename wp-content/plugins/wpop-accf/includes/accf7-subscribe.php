<?php
if (!defined('ABSPATH'))
    exit;
if(!class_exists('ACCF7_Active_Campaign_Subscribe')){
	class ACCF7_Active_Campaign_Subscribe{
		public function __construct(){
			add_action( 'wpcf7_before_send_mail', array($this, 'wpaccf7_subscribe' ),10,1); 
		}
		public function wpaccf7_subscribe($contact_form ){
			
			$submission = WPCF7_Submission::get_instance();
		    $posted_data = $submission->get_posted_data();
		    $wpcf7 = WPCF7_ContactForm::get_current();
			$form_id = $wpcf7->id;
		    $enable_accf7 = get_post_meta($form_id,'accf7_enable',true);

			if($enable_accf7 == 'yes' && apply_filters( 'accf7_send_data', 'true' ) == 'true' ){

				$fields=get_post_meta($form_id,'accf7_fields',true);
				$emailkey=isset($fields['accf7_email']) ? $fields['accf7_email'] : '';
				$fnamekey=isset($fields['accf7_first_name']) ? $fields['accf7_first_name'] : '';
				$lnamekey=isset($fields['accf7_last_name']) ? $fields['accf7_last_name'] : '';
				$phonekey=isset($fields['accf7_phone']) ? $fields['accf7_phone'] : '';
				$orgkey=isset($fields['accf7_organization']) ? $fields['accf7_organization'] : '';

				$email='';
				if(!empty($emailkey))
				$email=isset($posted_data[$emailkey]) ? $posted_data[$emailkey] : '';

				$fname='';
				if(!empty($fnamekey))
				$fname=isset($posted_data[$fnamekey]) ? $posted_data[$fnamekey] : '';

				$lname='';
				if(!empty($lnamekey))
				$lname=isset($posted_data[$lnamekey]) ? $posted_data[$lnamekey] : '';

				$phone='';
				if(!empty($phonekey))
				$phone=isset($posted_data[$phonekey]) ? $posted_data[$phonekey] : '';

				$organization='';
				if(!empty($orgkey))
				$organization=isset($posted_data[$orgkey]) ? $posted_data[$orgkey] : '';


				//Active Campaign starts
				if(!empty($email)){
					$ac=get_post_meta($form_id,'accf7_credentials',true);
					if(isset($ac['url']) && !empty($ac['url']) && isset($ac['api_key']) && !empty($ac['api_key']) && 
						isset($ac['list_id'])){
						$url = $ac['url'];
						$api_key = $ac['api_key'];
						$list_id = $ac['list_id'];
						$params = array(
							'api_key'      => $ac['api_key'],
							'api_action'   => 'contact_add',
							'api_output'   => 'serialize',
						);
						$body = array(
							'email'                    => $email,
							'p['.$list_id.']'          => $list_id, 
							'status['.$list_id.']'     => 1,
							'instantresponders['.$list_id.']' => 0,
						);
						if(!empty($fname)){
							$body['first_name'] = $fname;
						}
						if(!empty($lname)){
							$body['last_name'] = $lname;
						}
						if(!empty($phone)){
							$body['phone'] = $phone;
						}
						if(!empty($organization)){
							$body['orgname'] = $organization;
						}

						$args = array(
                    		'method' => 'POST',
                    		'timeout'     => 15,
                    		'redirection' => 15,
                    		'headers'     => "Content-Type: application/x-www-form-urlencoded",
                    		'body' => $body,
                    	);
                    	
                    	$api_url = $url . "/admin/api.php?api_action=contact_sync&api_output=json&api_key=".$api_key;
                    	$response = wp_remote_request( $api_url, $args);
                    
                    	if( is_wp_error( $response ) ) {
                    		// do nothing
                    	}
					}
				}
				// Active Campaign ends
			}
		}
	}
	new ACCF7_Active_Campaign_Subscribe();
}