<?php
if (!defined('ABSPATH'))
    exit;
if(!class_exists('ACCF7_Active_Campaign_Settings')){
	class ACCF7_Active_Campaign_Settings{
		public function __construct(){
	        add_filter( 'wpcf7_editor_panels', array($this, 'accf7_tab'),10,1);
	        add_action('save_post_wpcf7_contact_form', array($this, 'save_contact_form_seven_ac_settings'));
		}
		public function accf7_tab($panels){
			$panels['activecampaign-panel'] = array( 
	            'title' => __( 'ActiveCampaign', 'wpop-accf' ),
	            'callback' => array($this, 'accf7_tab_callback')
	        );
	        return $panels;
		}
		public function save_contact_form_seven_ac_settings($post_id){
			//fields
			update_post_meta($post_id,'accf7_fields', $_POST['accf7_fields']);
			// enable
			if(isset($_POST['accf7_enable'])){
				update_post_meta($post_id,'accf7_enable','yes');
			}else{
				update_post_meta($post_id,'accf7_enable','no');
			}

			//GDPR compliance
			if(isset($_POST['accf7_gdpr'])){
				update_post_meta($post_id,'accf7_gdpr',trim(sanitize_text_field($_POST['accf7_gdpr'])));
			}
			// ActiveCampaign
			$ac['url']=isset($_POST['accf7_url']) ? trim(sanitize_text_field($_POST['accf7_url'])) : '';
			$ac['api_key']=isset($_POST['accf7_api_key']) ? trim(sanitize_text_field($_POST['accf7_api_key'])) : '';
			$ac['list_id']=isset($_POST['accf7_list_id']) ? trim(absint($_POST['accf7_list_id'])) : '';
			update_post_meta($post_id,'accf7_credentials',$ac);
		}
		public function accf7_tab_callback(){
			global $post;
	        $cf7 = WPCF7_ContactForm::get_instance($_GET['post']);
	        $tags = '';
	        if(!empty($cf7)){
	        	$tags = $cf7->collect_mail_tags();	
	        }
	        
	        $post_id = isset($_GET['post']) ? $_GET['post'] : '';

	        $enable = get_post_meta($post_id,'accf7_enable',true);

	        ?>
	        <div id="accf7-settings">
	        <h2><?php echo esc_html__("ActiveCampaign Setttings","wpop-accf"); ?></h2>

	        <h3><label for="accf7_enable"><input type="checkbox" name="accf7_enable" id="cf7_email_subscription" value="yes" <?php echo (($enable=='yes')?'checked':''); ?> ><?php echo esc_html__("Enable ActiveCampaign for this form.","wpop-accf"); ?></label></h3><hr>
		    <div class="accf7-settings-tab clearfix">
		    	<ul class="tab-wrap clearfix">
		    		<li class="tab active" data-id="general">
		    			<?php echo esc_html__('General Settings','wpop-accf'); ?>
		    		</li>
		    		<li class="tab" data-id="form-fields">
		    		    <?php echo esc_html__('Form Fields','wpop-accf'); ?>
		    		</li>
		    		<li class="tab" data-id="form-pro" style="background:#05c305; color:#fff;" >
		    		    <?php echo esc_html__('Pro Version','wpop-accf'); ?>
		    		</li>
		    	</ul>
		    </div>
	        <div id="accf7_enable">

            <div class="accf7-main-settings tab-pane general general-settings-section">
		        <h1><?php echo __("ActiveCampaign Settings","wpop-accf"); ?></h1>
		        <?php
		        $ac=get_post_meta($post_id,'accf7_credentials',true);
		        ?>
		        <p>
		        	<label for="accf7_url"><?php echo __("ActiveCampaign URL","wpop-accf"); ?></label>
		        	<input type="text" name="accf7_url" class="widefat" id="accf7_url" value="<?php echo (isset($ac['url']) ?  esc_url($ac['url']) : '' ); ?>">
		        	<em><?php echo sprintf(esc_html__( 'You can get url like %s.', 'wpop-accf' ),'<a href="https://help.activecampaign.com/hc/en-us/articles/207317590-Getting-started-with-the-API" target="_blank">this</a>');?></em>
		        </p>
		        <p>
		        	<label for="accf7_api_key"><?php echo __("ActiveCampaign API KEY","wpop-accf"); ?></label>
		        	<input type="text" name="accf7_api_key" class="widefat" id="accf7_api_key" value="<?php echo (isset($ac['api_key']) ?  esc_attr($ac['api_key']) : '' ); ?>">
		        	<em><?php echo sprintf(esc_html__( 'You can get API key like %s.', 'wpop-accf' ),'<a href="https://help.activecampaign.com/hc/en-us/articles/207317590-Getting-started-with-the-API" target="_blank">this</a>');?></em>
		        </p>

	            <p>
	            	<label for="accf7_list_id"><?php echo __("ActiveCampaign Email List ID","wpop-accf"); ?></label>
            		<input type="text" name="accf7_list_id" class="widefat" id="accf7_list_id" value="<?php echo (isset($ac['list_id']) ?  esc_attr($ac['list_id']) : '' ); ?>">
            		<em><?php echo sprintf(esc_html__( 'You Must Add List Id to add contacts in your Active Campiagn Lists. You can get List ID like %s.', 'wpop-accf' ),'<a href="http://support.exitbee.com/email-marketing-crm-integrations/how-to-find-your-activecampaign-list-id" target="_blank">this</a>');?></em>
	            </p>
	            <p>
			    <div class="contacts-meta-section-wrapper">
					<span class="add-button table-contacts"><a href="javascript:void(0)" class="docopy-table-list button"><?php esc_html_e('Add List','wpop-accf'); ?></a></span>
			    </div>
			    <em class="pro"><?php echo __("Available in Premium Version.","wpop-accf"); ?>
			    	<a href="https://wpoperation.com/plugins/active-campaign-contact-form-7-pro" target="_blank"><?php esc_html_e('Get Pro Version','wpop-accf'); ?></a>
			    </em>

			    </p>
            </div><!--general Settings -->
	        <div class="accf7-main-settings tab-pane form-fields clearfix" style="display:none">
	            <?php
		        if(!empty($tags)){
		        	?>
		        	<h1><?php echo __("Select form fields","wpop-accf"); ?></h1>
		            <?php	
		            $fields=get_post_meta($post_id,'accf7_fields',true);
		            // email field
		            ?>
		            <div class="form-fields">
			            <label for="accf7_email" class="fleft"><?php echo __("Email Field* : ","wpop-accf"); ?></label>
			            <select name="accf7_fields[accf7_email]" id="accf7_email" class="fleft">
			            <option value=""><?php echo __("Select field name for email","wpop-accf"); ?></option>
			            <?php
			            foreach ($tags as $key => $tag) {
			                 $selected='';
			                if(isset($fields['accf7_email']) && $fields['accf7_email']==$tag)
			                    $selected='selected';

			                echo '<option value="'.esc_attr($tag).'" '.$selected.'>'.esc_attr($tag).'</option>';
			            }
			            ?>
			            </select>
		            </div>
		            <p><em><?php echo __("Following fields are optional select if available in form, otherwise leave unselected. Only email field is required.","wpop-accf"); ?></em></p>
                    <div class="form-fields">
		            	<label for="accf7_first_name" class="fleft"><?php echo __("First Name Field : ","wpop-accf"); ?></label>
			            <select name="accf7_fields[accf7_first_name]" id="accf7_first_name" class="fleft">
			            <option value=""><?php echo __("Select field name for first name","wpop-accf"); ?></option>
			            <?php
			            foreach ($tags as $key => $tag) {
			                $selected='';
			                if(isset($fields['accf7_first_name']) && $fields['accf7_first_name']==$tag)
			                    $selected='selected';

			                echo '<option value="'.esc_attr($tag).'" '.$selected.'>'.esc_attr($tag).'</option>';
			            }
			            ?>
			            </select>
                    </div>
                    <div class="form-fields">
			            <label for="accf7_last_name" class="fleft"><?php echo __("Last Name Field : ","wpop-accf"); ?></label>
			            <select name="accf7_fields[accf7_last_name]" id="accf7_last_name" class="fleft">
			            <option value=""><?php echo __("Select field name for last name","wpop-accf"); ?></option>
			            <?php
			            foreach ($tags as $key => $tag) {
			                $selected='';
			                if(isset($fields['accf7_last_name']) && $fields['accf7_last_name']==$tag)
			                    $selected='selected';

			                echo '<option value="'.esc_attr($tag).'" '.$selected.'>'.esc_attr($tag).'</option>';
			            }
			            ?>
			           </select>
                   </div>
                   <div class="form-fields">
			            <label for="accf7_phone" class="fleft"><?php echo __("Phone Number Field : ","wpop-accf"); ?></label>
			            <select name="accf7_fields[accf7_phone]" id="accf7_phone" class="fleft">
			            <option value=""><?php echo __("Select field name for Phone","wpop-accf"); ?></option>
			            <?php
			            foreach ($tags as $key => $tag) {
			                $selected='';
			                if(isset($fields['accf7_phone']) && $fields['accf7_phone']==$tag)
			                    $selected='selected';

			                echo '<option value="'.esc_attr($tag).'" '.$selected.'>'.esc_attr($tag).'</option>';
			            }
			            ?>
			            </select>
		            </div>
		            <div class="form-fields">
			            <label for="accf7_organization" class="fleft"><?php echo __("Organization Field : ","wpop-accf"); ?></label>
			            <select name="accf7_fields[accf7_organization]" id="accf7_organization" class="fleft">
			            <option value=""><?php echo __("Select field name for organization","wpop-accf"); ?></option>
			            <?php
			            foreach ($tags as $key => $tag) {
			                $selected='';
			                if(isset($fields['accf7_organization']) && $fields['accf7_organization']==$tag)
			                    $selected='selected';

			                echo '<option value="'.esc_attr($tag).'" '.$selected.'>'.esc_attr($tag).'</option>';
			            }
			            ?>
			            </select>
		            </div>
		            <p><hr></p>
		            <div class="form-fields">
			            <label for="accf7_tags" class="fleft"><?php echo __("Tags : ","wpop-accf"); ?></label>
                        <input type="text" name="" value="" placeholder="tag1,tag2,your-name" readonly="true" />
                        <em class="pro"><?php echo __("Available in Premium Version.","wpop-accf"); ?>
                        	<a href="https://wpoperation.com/plugins/active-campaign-contact-form-7-pro" target="_blank"><?php esc_html_e('Get Pro Version','wpop-accf'); ?></a>
                        </em>
		            </div>
		            <p><hr></p>
		            <div class="form-fields">
			            <label for="accf7_note" class="fleft"><?php echo __("Notes : ","wpop-accf"); ?></label>
                        <input type="text" name="" value="" placeholder="notes..." readonly="true" />
                        <em class="pro"><?php echo __("Available in Premium Version.","wpop-accf"); ?>
                        	<a href="https://wpoperation.com/plugins/active-campaign-contact-form-7-pro" target="_blank"><?php esc_html_e('Get Pro Version','wpop-accf'); ?></a>
                        </em>
		            </div>
		            <p><hr></p>
		            <div class="form-fields">
						<label for="accf7_gdpr" class="fleft"><?php echo __("Acceptance Field for GDPR compliance : ","wpop-accf"); ?></label>
			            <select name="" id="accf7_gdpr" class="fleft">
				            <option value=""><?php echo __("Choose Field","wpop-accf"); ?></option>
			            </select>
					</div>
					<em class="pro"><?php echo __("Available in Premium Version.","wpop-accf"); ?>
			    		<a href="https://wpoperation.com/plugins/active-campaign-contact-form-7-pro" target="_blank"><?php esc_html_e('Get Pro Version','wpop-accf'); ?></a>
			    	</em>
					<p><hr></p>
                    <label><?php echo __("Add Extra Fields.","wpop-accf"); ?></label>
                    <div class="form-fields">
				    <div class="contacts-meta-section-wrapper">
				    	<span class="add-button table-contacts"><a href="javascript:void(0)" class="docopy-table-contact button"><?php esc_html_e('Add Field','wpop-accf'); ?></a></span>
				    	<em class="pro"><?php echo __("Available in Premium Version.","wpop-accf"); ?>
				    		<a href="https://wpoperation.com/plugins/active-campaign-contact-form-7-pro" target="_blank"><?php esc_html_e('Get Pro Version','wpop-accf'); ?></a>
				    	</em>
				    </div>
				    </div>
		           <?php
		        }
		        else{
		            echo __('Please Add Contact Form Tags First!', 'wpop-accf');
		        }
		        ?>
		        <hr>
            </div><!--Form Fields -->
            <div class="accf7-main-settings tab-pane form-pro clearfix" style="display:none">
            	<div class="pro-features">
            		<h2><?php esc_html_e('Pro Features','wpop-accf'); ?></h2>
            		<hr>
            		<ul>
            			<li><?php esc_html_e('Adds “Contact Form 7” data to “ActiveCampaign”.','wpop-accf');?></li>
						<li><?php esc_html_e('Adds Contacts to unlimited List ID\'s.','wpop-accf'); ?></li>
						<li><?php esc_html_e('Option to select “Contact Form 7” fields for “ActiveCampaign” list.','wpop-accf'); ?></li>
						<li><?php esc_html_e('Option to add Unlimited Fields.','wpop-accf'); ?></li>
						<li><?php esc_html_e('Option to add Tags','wpop-accf'); ?></li>
						<li><?php esc_html_e('Supports Contact Form 7 Special Mail Tags','wpop-accf'); ?></li>
						<li><?php esc_html_e('GDPR acceptance field','wpop-accf'); ?></li>
						<li><?php esc_html_e('Life Time Free Updates & Support.'); ?></li>
						
						

            		</ul>
            		<a href="https://wpoperation.com/plugins/active-campaign-contact-form-7-pro" class="button-secondary" target="_blank">
            			<?php esc_html_e('Get Pro Version','wpop-accf'); ?>
            		</a>
            	</div>
            	<hr>
            	<h2><?php esc_html_e('Please Spread Your Love With 5 Star Rating.','wpop-accf'); ?></h2>
            	<span><?php esc_html_e('If you are loving our plugin please give us nice rating.','wpop-accf'); ?></span>
            	<a href="https://wordpress.org/support/plugin/wpop-accf/reviews/#new-post" class="button-primary" target="_blank">
            			<?php esc_html_e('Rate Now','wpop-accf'); ?>
            		</a>
            </div><!-- Premium Version -->
	        <hr>
	        </div>
	        </div>
	        <?php

		}
	}
	new ACCF7_Active_Campaign_Settings();
}