<?php
/*
Plugin Name: Forms Actions
Plugin URI: https://github.com/dadmor/Forms_actions
Description: WordPress plugin to run actions after form sending.
Author: gdurtan
Author URI: grzegorz.durtan.pl
Version: 1.2.2
License: GPL2
*/
define('Forms_actions','Forms_actions.1.2.2');

function fa_alpaca_lib_init() {

	wp_register_script( 'alpaca-js', plugins_url('/js/alpaca-core.min.js', __FILE__) );
	wp_enqueue_script( 'alpaca-js' );

	wp_register_style( 'alpaca-css', plugins_url('/css/alpaca-wpadmin.css', __FILE__) );
	wp_enqueue_style('alpaca-css');

}
add_action('admin_enqueue_scripts', 'fa_alpaca_lib_init');

require_once( plugin_dir_path( __FILE__ ) . '/inc/fa_controllers.php' );
require_once( plugin_dir_path( __FILE__ ) . '/inc/php_alpaca_api.php' );

/* METABOX start ------------------------------------ */

function fa_add_meta_box() {
	
	/* build post types array to display ACF frontend metabox */
	$post_types = get_post_types( '', 'names' ); 
	
	foreach ( $post_types as $key => $value) {
		$screens[] = $key;
	}

	if( $screen == 'acf' ){
		$title_box = __( 'Forms global ACTIONS', 'forms_actions' );
	}else{
		$title_box = __( 'Forms ACTIONS (ACF extention)', 'forms_actions' );
	}

	foreach ( $screens as $screen ) {
		/* only editors or administrator can display forms */
		if( current_user_can('edit_others_pages') ) {  			
			/* display ACF frontend metabox */
			add_meta_box(
				'form_actions_id',
				$title_box,
				'fa_meta_box_callback',
				$screen,
				'side'
			);
 		} 
	}
}
add_action( 'add_meta_boxes', 'fa_add_meta_box');

function fa_meta_box_callback( $post ) {

	/* create ACF global guardian */
	if( get_post_type( $post->ID ) == 'acf'){
		$gloabal_guardian = false;
		echo '<div style="font-weight:bold; border-bottom:1px solid #eee; margin-bottom:10px; padding-bottom:5px">'.__('Global properties for').' '.$post->post_title.'</div>';
	}
	/* check is globals are defined (in first fieldgroup) */
	
	/* TODO checkit - propably array [0] is error*/
	$fieldsArray = apply_filters('acf/get_field_groups', array());
	$global_form_id = $fieldsArray[0]['id'];
	$global_alpaca = get_post_meta( $global_form_id, '_meta_fa_box_alpaca', true );

	/* -------------------------- */

	/* Add an nonce field so we can check for it later. */
	wp_nonce_field( 'fa_meta_box', 'fa_meta_box_nonce' );
	
	$create_object = get_post_meta( $post->ID, '_meta_fa_create_object', true ); // bolean
	$value_alpaca = get_post_meta( $post->ID, '_meta_fa_box_alpaca', true );

	/* overwrite lolal settings for global ACF settings */
	if($global_alpaca != ''){
		/* insert global data olny with create new post (no edit) */
		if($create_object != 'false'){
			$value_alpaca = $global_alpaca;
		}
	}

	echo '<input type="hidden" id="fa_create_object" name="fa_create_object" value="'.$create_object.'" size="25" />';
	echo '<input type="hidden" id="fa_alpaca_data" name="fa_alpaca_data" value="'.$value_alpaca.'" size="25" />';

	?><div id="fa_options" style=""></div> 
		<script type="text/javascript">
			jQuery(document).ready(function($) {
			    $("#fa_options").alpaca({
			    /* ----------------------------------------------------------------------- */	
			    	<?php if($value_alpaca != ''){ ?>
			    	"data" : <?php echo urldecode ( $value_alpaca );?>,
			    	<?php } ?>
			    	
			    	"options": {
						"fields": {

							"fa_clear_form": {
			    				"rightLabel": "Clear to defaults"
			    			},
			    			"fa_send_new_password": {
			                	"rightLabel":"Send new password",
			                },
							
			    			"fa_send_email": {
		                    	"rightLabel": "Send email"
		               		},			    			
		               		"fa_create_post_dependency": {
		               			"rightLabel": "Create post"
		               		},
		               		"fa_create_post":{
		               			"description": "Select type to created output",
		               			"fields": {	
			    					               		
		               				"redirect_to_id":{
		               					"dataSource":<?php echo render_posttype_to_alpaca_string('page'); ?>,	
										"rightLabel": "Redirect",
										"type":"select" 	  
		               				},
		               				"redirect_to_me": { 
		               					"rightLabel": "Redirect to created element"
		               				}				               			
				               	}

		               			
		               		},
		               		"fa_update_post": {
			    				"rightLabel": "Update post"
			    			},
		               		"fa_login_user_dependency": {
		               			"rightLabel": "Login user"
		               		},

		               		"fa_login_user": {
			    				"fields": {	
			    					"logout_unconfirmed":{

										"rightLabel": "Logout unconfirmed",
	  
		               				},				               		
		               				"redirect_to_id":{
		               					"dataSource":<?php echo render_posttype_to_alpaca_string('page'); ?>,	
										"rightLabel": "Redirect",
										"type":"select" 	  
		               				}				               			
				               	}
			    			},


		               		"fa_register_user_dependency": {
		               			"rightLabel": "Register user"
		               		},
		               		"fa_register_user": {
		               			"fields": {				               		
		               				"confirm_by_email":{
										"rightLabel": "Confirm by email"
		               				},	
		               				"login_as_email": {  
			                        		//"required": false,
			                   				"rightLabel": "Login as email"
				                   		},	               				
		               				"redirect_to_id":{
		               					"dataSource":<?php echo render_posttype_to_alpaca_string('page'); ?>,	
										"rightLabel": "Redirect",
										"type":"select" 	  
		               				}					               			
				               	}
		               		},

							"fa_update_user": {
			    				"rightLabel": "Update user"
			    			},


		               		"fa_targeted_questions_dependency": {
		                    	"rightLabel": "Targeted questions"
		               		},
		               		"fa_targeted_questions": {
		               			"fields": {
				               		"item": {
				   						"fields": {
				   							"post_type": {	 
				   								"type": "select",
			                                	"dataSource": <?php echo render_posttype_to_alpaca_string('page'); ?>,
				   							}
				   						}
				   					}
			   					},
			   					"items": {
									"addItemLabel": "Add target",
									"removeItemLabel": "Remove",
									"showMoveDownItemButton": false,
									"showMoveUpItemButton": false,							                
						        }
		   					},
		   					
			                "fa_dont_display_after_send": {
			                   "rightLabel": "Dont display form after you send"
			                },
			                "fa_redirect_dependency": {
		               			"rightLabel": "Redirect"
		               		},
		   				}   		
			    	},
			    	"schema": {
						//"title": "Form extended options",
						//"description": "Define your special display properties",
						"type": "object",
						"properties": {

							/* -------------------------- */
			                "fa_clear_form": {
			                    "description": "Always clear your form to defaults",
			                    "type": "boolean"
			                },
							/* -------------------------- */
					      	"fa_send_email": {
			                    "description": "Name your form field as: email (addres to send), subject* (email subject), message* (email message), succes (mesage after send)",
			                    "type": "boolean"
			                },
			                /* -------------------------- */			               
			                "fa_create_post_dependency": {
			                    "description": "Use form to create new post or edit exist. Name your form field as: post_title, post_content, post_excerpt, succes (mesage after send)",
			                    "type": "boolean"
			                },
			                 /* -------------------------- */
	                        "fa_create_post": {
	                        	"dependencies": "fa_create_post_dependency",				        		
			                    "type": "object",
	                        		"properties": {
				        				"post_type": {  
			                        		//"required": false,
			                    			"title": "Select post type",		                            
			                   				"enum": [<?php echo render_posttypes_list_to_alpaca_array(); ?>]
				                   		},
				                   		"category":{
				                   			"title": "insert field name to mapped with category",
				                   		},
				                   		"redirect_to_me": {  
			                        		//"required": false,			                    			
			                    			"type": "boolean"	
				                   		},
				                   		"redirect_to_id": {  
			                        		//"required": false,
			                    			"title": "post to redirect",	
				                   		},
				                   		"redirect_param":{
				                   			"title": "add url parameters to redirect",
				                   		},
				                   		"redirect_to_url": {  
			                        		//"required": false,
			                    			"title": "url to redirect",	
				                   		},
				                   		
				                    }
			                },
			                "fa_update_post": {
			    				"description": "Update your post from fields: post_title, post_content etc.",
			                    "type": "boolean"
			    			},
			                /* -------------------------- */			               
			                "fa_register_user_dependency": {
			                    "description": "Use form to register new user or edit exist. Name your form field as: user_name, user_pass, user_email",
			                    "type": "boolean"
			                },
			                 /* -------------------------- */
	                        "fa_register_user": {
	                        	"dependencies": "fa_register_user_dependency",
	 							"type": "object",
	                        		"properties": {
				        				"confirm_by_email": {  
			                        		//"required": false,
			                   				"type": "boolean"
				                   		},
				                   		"login_as_email": {  
			                        		//"required": false,
			                   				"type": "boolean"
				                   		},
				                   		"redirect_to_id": {  
			                        		//"required": false,
			                    			"title": "Redirect after register",
				                   		}
				                    }
			                },
			                "fa_update_user": {
			    				"description": "Update your user from fields: user_email, first_name etc.",
			                    "type": "boolean"
			    			},
			                /* -------------------------- */			               
			                /* -------------------------- */
			                "fa_login_user_dependency": {
			                    "description": "Login user by user_login and user_password",
			                    "type": "boolean"
			                },
			                "fa_login_user": {
			                	"dependencies": "fa_login_user_dependency",
			                    "type": "object",
	                        		"properties": {
				        				"logout_unconfirmed": {  
			                        		//"required": false,
			                   				"type": "boolean"
				                   		},
				                   		"redirect_to_id":{
				                   			"title": "element to redirect",		  
				                   			
				                   		}
				                    }
			                },
			                /* -------------------------- */
			                /* -------------------------- */
			                "fa_send_new_password": {			                	
			                    "description": "Name your form field as user_pass field",
			                    "type": "boolean"
			                },


			                "fa_targeted_questions_dependency": {
			                    "description": "Matching your form results with targeted pages and redirect to best choose.",
			                    "type": "boolean"
			                },
			                /* -------------------------- */
	                        "fa_targeted_questions": {
	                        	"dependencies": "fa_targeted_questions_dependency",
	 							"type": "array",
					      		"items": {  
					      			"type": "object",
	                        		"properties": {
				        				"post_type": {  
			                        		//"dependencies": "fa_targeted_questions_dependency",
			                            	"title": "Add target pages",		                            
			                           		"description": "Add and select target pages to redirect after send form - algorytm choose best choice",  
			                           		"type": "string"
				                   		}
				                    }
				                }
			                },			                
			                
			                "fa_dont_display_after_send": {
			                    "description": "Dont display form again, after you send data",
			                    "type": "boolean"
			                },
			                /* -------------------------- */
			                "fa_redirect_dependency": {
			                    "description": "Set page target to skip after send",
			                    "type": "boolean"
			                },
			                "fa_redirect": {
			                	"dependencies": "fa_redirect_dependency",
			                    "description": "add url to redirect",
			                    
			                }
			            }
		            },	

					/*"view": {
				        //"parent": "bootstrap-edit",
				        "layout": {
				            "template": "threeColumnGridLayout",
				            "bindings": {
				                "fa_redirect_dependency": "column-1",
				                "fa_redirect": "column-1",
				               
				            }
				        },
				        "templates": {
				            "threeColumnGridLayout": '<div class="row">' + '{{#if options.label}}<h2>{{options.label}}</h2><span></span>{{/if}}' + '{{#if options.helper}}<p>{{options.helper}}</p>{{/if}}' + '<div id="column-1" class="col-md-6"> </div>' + '<div id="column-2" class="col-md-6"> </div>' + '<div id="column-3" class="col-md-12"> </div>' + '<div class="clear"></div>' + '</div>'
				        }
				    },*/
				      
			    /* ----------------------------------------------------------------------- */
				    "postRender": function(renderedForm) {          
		              $('#fa_options select, #fa_options input, #fa_options textarea').live('change',function() {   
		              		
		                //if (renderedForm.isValid(true)) {
		                  var val2 = renderedForm.getValue();
		                  $('#fa_alpaca_data').val(encodeURIComponent(JSON.stringify(val2)));
		                //}
		              });
		            } 
	            /* ----------------------------------------------------------------------- */
			  });
			});
		</script><?php
}

/**
 * When the post is saved, saves our custom data.
 *
 * @param int $post_id The ID of the post being saved.
 */
function fa_save_meta_box_data( $post_id ) {
	
	// Check if our nonce is set.
	if ( ! isset( $_POST['fa_meta_box_nonce'] ) ) {
		return;
	}

	// Verify that the nonce is valid.
	if ( ! wp_verify_nonce( $_POST['fa_meta_box_nonce'], 'fa_meta_box' ) ) {
		return;
	}

	// If this is an autosave, our form has not been submitted, so we don't want to do anything.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// Check the user's permissions.
	if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {

		if ( ! current_user_can( 'edit_page', $post_id ) ) {
			return;
		}

	} else {

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
	}

	// Sanitize user input.
	$my_data_fa_alpaca = $_POST['fa_alpaca_data'];
	$my_data_fa_create_object = $_POST['fa_create_object'];
	
	if($my_data_fa_create_object == ''){
		$my_data_fa_create_object = 'false';
	}

	// Update the meta field in the database.
	update_post_meta( $post_id, '_meta_fa_create_object', $my_data_fa_create_object );
	update_post_meta( $post_id, '_meta_fa_box_alpaca', $my_data_fa_alpaca );
	
}
add_action( 'save_post', 'fa_save_meta_box_data' );
/* METABOX end ------------------------------------ */


/* DISPLAY filter ------------------------------------ */
function fa_realize_form_actions() {

	if( isset($_POST['acf_nonce']) && wp_verify_nonce($_POST['acf_nonce'], 'input') )
	{
	
		global $post;


		global $returnObj;
		$returnObj = array(
			'block_redirect' => true,
			'redirect_to_id' => null,
			'redirect_to_url' => null
		);


		// ADD gloal prop
		$get_globals = afd_form_permision();
		foreach ($get_globals as $key => $value) {
			if(get_post_meta( $value, '_meta_fa_box_alpaca', true )!=''){
				$args = json_decode(urldecode(get_post_meta( $value, '_meta_fa_box_alpaca', true )));
				
				process_actions($args);			

			}				
		}
		$args = json_decode(urldecode(get_post_meta( $post->ID, '_meta_fa_box_alpaca', true )));
		process_actions($args);
		
		if($returnObj["redirect_param"] != null){
            $param = $returnObj["redirect_param"];
            $param = explode(',',$returnObj["redirect_param"]);
            foreach ($param as $key) {
            	$key = explode(':',$key);
            	if($key[1] == '{user_id}'){
					$current_user_id = get_current_user_id();
            		$key[1] = $current_user_id;
            	}
            	$param_array[$key[0]] = $key[1];
            	
            }

        }


        if($returnObj["redirect_to_url"] != null){

			if($param_array == null){
				$url = $returnObj["redirect_to_url"];
			}else{
				$url = add_query_arg( $param_array , $returnObj["redirect_to_url"]);
			}
			wp_redirect($url);
           	exit;
        }


        $args_AFD = json_decode( urldecode ( get_post_meta($post->ID,'_meta_afd_form_render_box_alpaca', true )), true );
		if($args_AFD['display_edit']){
			wp_redirect(get_permalink($post->ID));
            exit;
		}

		//echo 'you propably send ACF form';
		return $returnObj;

	}


}

function process_actions($args, $post_id = '', $ajax = false){
	
	global $FA_ajax;
	$FA_ajax = $ajax;
	global $post;

	if($post_id != ''){
		$post = get_post($post_id);
	}


	if($args != NULL){



			// FIRST FIND CLEAT TO DEFAULTS
			
			// SECOND EXECUTE REST
			foreach ($args as $key => $value) {
				if($key != 'fa_clear_form'){
					$swith = substr($key, -10);
					if( $swith != 'dependency'){
						@call_user_func_array($key,array($value));
						//echo $key.'('.array($value).')<br/>';
						//var_dump($key,$value);
					}
				}

			}

			foreach ($args as $key => $value) {
				if($key == 'fa_clear_form'){
					$swith = substr($key, -10);
					if( $swith != 'dependency'){
						@call_user_func_array($key,array($value));
						//echo $key.'('.array($value).')<br/>';
					}
				}

			}

		}
}

add_action('admin_menu', 'FA_menu');
function FA_menu()
{   
  // editor + administrator = moderate_comments;
  add_menu_page('Forms actions', 'Forms actions', 'administrator', 'forms_actions', 'forms_actions_callback');
  // submenu with calbac
  //add_submenu_page('acf', 'UiGEN hierarchy', 'UiGEN hierarchy', 'administrator', 'url_uigen_hierarchy', 'UiGEN_hierarchy_callback');
  // submenu from defined posttype
  //add_submenu_page('url_uigen_core', 'UiGEN hierarchy', 'UiGEN hierarchy', 'manage_options', 'edit.php?post_type=template_hierarchy');  //add_submenu_page('url_uigencore', 'Dodaj', 'Dodaj', 'administrator', 'url_add_mod', 'moderator_ADD');  
  //add_submenu_page('url_uigen_core', 'UiGEN Content parts', 'UiGEN Content parts', 'manage_options', 'edit.php?post_type=content_parts');  //add_submenu_page('url_uigencore', 'Dodaj', 'Dodaj', 'administrator', 'url_add_mod', 'moderator_ADD');  

}

function forms_actions_callback(){
	require_once( plugin_dir_path( __FILE__ ) . '/inc/fa_admin_gui.php' );
}