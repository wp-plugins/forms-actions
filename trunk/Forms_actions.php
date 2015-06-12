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

require_once( plugin_dir_path( __FILE__ ) . '/widgets/join_widget.php' );

/* METABOX start ------------------------------------ */

function fa_add_meta_box() {

	/* build post types array to display ACF frontend metabox */
	

		$title_box = __( 'Forms ACTIONS (ACF extention)', 'forms_actions' );
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


add_action( 'add_meta_boxes', 'fa_add_meta_box');
function fa_meta_box_callback( $post ) {
	global $post;
	if($post->post_type == 'acf'){
		require_once( plugin_dir_path( __FILE__ ) . '/inc/fa_metabox_and_schema.php' );
	}else{
		echo 'to set form actions go to <a href="wp-admin/edit.php?post_type=acf">ACF form</a>';
	}
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
function my_acf_save_post( $post_id ) {
if ( ! is_admin() ) {
		//global $post;

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

        //$args_AFD = json_decode( urldecode ( get_post_meta($post_id,'_meta_afd_form_render_box_alpaca', true )), true );

		wp_redirect(get_permalink($post->ID).'?acf_message='.$GLOBALS['acf_validation_errors']);
		exit;

}	
}
add_action('acf/save_post', 'my_acf_save_post', 30);







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

						/* forced edit post */
						if($_GET['pid']!='' ){
							if($key == 'fa_create_post'){
								$key = 'fa_update_post';
							}
						}

						@call_user_func_array($key,array($value));
						//var_dump('action:'.$key,$value);
					}
				}

			}
			if($_GET['pid']=='' ){
				foreach ($args as $key => $value) {
					if($key == 'fa_clear_form'){
						$swith = substr($key, -10);
						if( $swith != 'dependency'){
							@call_user_func_array($key,array($value));
							//var_dump('action:'.$key,$value);
							
						}
					}

				}
			}

		}
}

add_action('admin_menu', 'FA_menu');
function FA_menu()
{   
  // editor + administrator = moderate_comments;
  //add_menu_page('Forms actions', 'Forms actions', 'administrator', 'forms_actions', 'forms_actions_callback');
  // submenu with calbac
  //add_submenu_page('forms_actions', 'UiGEN hierarchy', 'UiGEN hierarchy', 'administrator', 'url_uigen_hierarchy', 'forms_actions_database_calback');
  // submenu from defined posttype
  //add_submenu_page('url_uigen_core', 'UiGEN hierarchy', 'UiGEN hierarchy', 'manage_options', 'edit.php?post_type=template_hierarchy');  //add_submenu_page('url_uigencore', 'Dodaj', 'Dodaj', 'administrator', 'url_add_mod', 'moderator_ADD');  
  //add_submenu_page('url_uigen_core', 'UiGEN Content parts', 'UiGEN Content parts', 'manage_options', 'edit.php?post_type=content_parts');  //add_submenu_page('url_uigencore', 'Dodaj', 'Dodaj', 'administrator', 'url_add_mod', 'moderator_ADD');  

}

function forms_actions_callback(){
	require_once( plugin_dir_path( __FILE__ ) . '/inc/fa_admin_gui.php' );
}

function forms_actions_database_calback(){
	require_once( plugin_dir_path( __FILE__ ) . '/inc/fa_admin_gui_database.php' );
}






