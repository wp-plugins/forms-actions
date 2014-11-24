<?php
/*
Plugin Name: Forms Actions
Plugin URI: https://github.com/dadmor/Forms_actions
Description: WordPress plugin to run actions after form sending.
Author: gdurtan
Author URI: grzegorz.durtan.pl
Version: 1.0.0
License: GPL2
*/

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
		echo '<div style="font-weight:bold; border-bottom:1px solid #eee; margin-bottom:10px; padding-bottom:5px">Global properties for '.$post->post_title.'</div>';
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
							
			    			"fa_send_email": {
		                    	"rightLabel": "Send email"
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
		   					"fa_clear_form": {
			    				"rightLabel": "Clear to defaults"
			    			},
		   				}   		
			    	},
			    	"schema": {
						//"title": "Form extended options",
						//"description": "Define your special display properties",
						"type": "object",
						"properties": {
							/* -------------------------- */
					      	"fa_send_email": {
			                    "description": "Name your form field as: email (addres to send), subject* (email subject), message* (email message), succes (mesage after send)",
			                    "type": "boolean"
			                },
			                /* -------------------------- */			               
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
			                /* -------------------------- */
			                "fa_clear_form": {
			                    "description": "Always clear your form to defaults",
			                    "type": "boolean"
			                },
			                /* -------------------------- */
			            }
		            },		        
				      
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
		$args = json_decode(urldecode(get_post_meta( $post->ID, '_meta_fa_box_alpaca', true )));
		
		/*echo '<pre>';
		var_dump($args);
		echo '</pre>';*/

		foreach ($args as $key => $value) {

			$swith = substr($key, -10);
			if( $swith != 'dependency'){

				@call_user_func_array($key,array($value));
				//echo $key.'('.array($value).')<br/>';
			}

		}

		$returnObj = array(
			'block_redirect' => true,
			'redirect_to_id' => null
		);

		//echo 'prawdopodobnie wysłałeś formularz';
		return $returnObj;

	}
}