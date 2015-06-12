<?php
function fa_targeted_questions($args){

		echo '<h1>'.__('Find best questionary target.').'</h1>';

		$sourceArray = get_fields($post->ID);
		$max_points = 0;

		foreach ($args as $key => $value) {

			$post_id = $value -> post_type;
			$thisArray = get_fields($post_id);
			/*	
			echo '<pre>';
			var_dump($thisArray);
			echo '</pre>';
			*/

			/* check arrays */
			echo '<br/>==================<br/>'.get_the_title($post_id).'<br/>----------------------<br/>';
			$counter = 0;
			$points = 0;
			foreach ($sourceArray as $source_key => $source_value) {
				echo $source_key.' = '.$source_value.' >> '.$thisArray[$source_key].'<br/>';
				if($source_value == $thisArray[$source_key]){
					$points ++;
				}
				$counter++;
			}

			$percent =  ( $points / $counter ) * 100;

			echo '----------------------<br/>'.$points.' '.__('points');
			echo '<br/>'.$percent.' %<br/>';

			if( $points > $max_points ){
				$max_points = $points;
				$winner_id = $post_id;
			}

		}

		echo '<h1>'.__('The WINNER is:').' '.get_the_title($winner_id).'</h1>';
}

function fa_send_email($args){

	$this_form_array = get_fields($post->ID);
	// Example using the array form of $headers
	// assumes $to, $subject, $message have already been defined earlier...

	$headers[] = 'From: '.get_bloginfo( 'name' ).' <'.get_option( 'admin_email' ).'>'. "\r\n";
	$headers[] = 'Content-Type: text/html; charset=UTF-8';
	//$headers[] = 'Cc: John Q Codex <jqc@wordpress.org>';
	//$headers[] = 'Cc: iluvwp@wordpress.org'; // note you can just use a simple email address
	$upload_dir = wp_upload_dir();
	$upload_dir = $upload_dir['basedir'];

	$counter = 0;

	foreach ($this_form_array as $key => $value) {

		$pre_kay_name = explode('-',$key);

		/* emails array */
		if($pre_kay_name[0] == 'email'){
			$emails_array[] = $value;
		}
		/* subjects array */
		if($pre_kay_name[0] == 'subject'){
			$subjects_array[$key] = $value;
		}
		/* attachments array */
		if($pre_kay_name[0] == 'attachment'){

			$attachment = $value;

			$pathAtray = explode('/',$attachment);

			$end = end($pathAtray);
			$secondEnd = prev($pathAtray);
			$upload_dir = $upload_dir.'/'.$secondEnd.'/'.$end;

			$attachments_array[$key] = $upload_dir;
		}
		$counter++;
	}

	/* Create Message */
	foreach ( get_field_objects($post->ID) as $key => $value) {
		$pre_kay_name = explode('-',$key);
		if($pre_kay_name[0] == 'message'){
			//echo $key.'-'.$value['order_no'].'<br/>';
			$techArr['label'] = $value['label'];
			$techArr['value'] = $value['value'];
			$messages_array[intval($value['order_no'])] = $techArr;
		}
	}

	ksort($messages_array);

	$message.= '<table border="0" width="100%">';
	foreach ($messages_array as $key => $value) {

		if($value['value'] != ''){

				/* plain text*/
				//$message.= '<p><b>'. $value['label'] .'</b> :'. $value['value'] .'</p>';

				/* table*/
				$message.= '<tr><td style="border-bottom:1px solid #ccc; padding:5px">'. $value['label'] .'</td><td style="border-bottom:1px solid #ccc; padding:5px">'. $value['value'] .'</td></tr>';
		}
	}
	$message.= '</table>';

	/*
	echo '<pre>';
	var_dump($messages_array);
	echo '</pre>';
	*/

	wp_mail( $emails_array , reset($subjects_array) , $message, $headers , $attachments_array );

}

function fa_create_post($args){


	if($args->category != ''){
		$category = $args->category;
	}

	$new_post['post_type'] = $args->post_type;
	$new_post['post_status'] = 'publish';
	$this_form_array = get_fields($post->ID);


	foreach ($this_form_array as $key => $value) {
		
		if($key == 'post_title'){
			$new_post['post_title'] = $value;
		}
		if($key == 'post_content'){
			$new_post['post_content'] = $value;
		}
		if($key == $category){
			$new_post['post_category'] = $value;
		}
		if($key == 'post_status'){
			$new_post['post_status'] = $value;
		}

	}
	
	$post_id = wp_insert_post( $new_post );

	foreach ($this_form_array as $key => $value) {

		$guardian_meta = false;

		if($key == 'post_title'){
			$guardian_meta = true;
		}
		if($key == 'post_content'){
			$guardian_meta = true;
		}


		if($guardian_meta == false){
			update_post_meta($post_id,$key,$value);
			var_dump($key.'_'.$value);
		}
	}

	// Add global AFD props is exist

	$fieldsArray = afd_form_permision(array('post_id'=>$post_id));

	$global_form_id = $fieldsArray[0];

	$global_render = get_post_meta( $global_form_id, '_meta_afd_form_render_box_key', true );
	$global_alpaca = get_post_meta( $global_form_id, '_meta_afd_form_render_box_alpaca', true );

	if($global_render != ''){

			update_post_meta( $post_id, '_meta_afd_form_render_box_key', $global_render );
			update_post_meta( $post_id, '_meta_afd_form_render_box_alpaca', $global_alpaca );

	}

	// -------------------------------
	

	if($args->redirect_to_me != ''){
		global $returnObj;
		$returnObj['redirect_to_url'] = get_permalink($post_id);
    }

	if($args->redirect_to_id != ''){
		global $returnObj;
		$returnObj['redirect_to_url'] = get_permalink($args->redirect_to_id);
    }

    if($args->redirect_param != ''){
		global $returnObj;
		$returnObj['redirect_param'] = $args->redirect_param;
    }
}
function fa_update_post($args){

	$this_form_array = get_fields($post->ID);
	foreach ($this_form_array as $key => $value) {
		if($key == 'post_title'){
			$my_post['post_title'] = $value;
		}
		if($key == 'post_content'){
			$my_post['post_content'] = $value;
		}
		if($key == $category){
			$my_post['post_category'] = $value;
		}
		if($key == 'post_status'){
			$my_post['post_status'] = $value;
		}

	}
	wp_update_post( $my_post );
}


function fa_register_user($args){

	$this_form_array = get_fields($post->ID);

	foreach ($this_form_array as $key => $value) {
		if($key == 'user_pass'){
			$userdata['user_pass'] = $value;
		}
		if($key == 'user_login'){
			$userdata['user_login'] = $value;
		}
		if($key == 'user_nicename'){
			$userdata['user_nicename'] = $value;
		}
		if($key == 'user_url'){
			$userdata['user_url'] = $value;
		}
		if($key == 'user_email'){
			$userdata['user_email'] = $value;
		}
		if($key == 'display_name'){
			$userdata['display_name'] = $value;
		}
		if($key == 'nickname'){
			$userdata['nickname'] = $value;
		}
		if($key == 'first_name'){
			$userdata['first_name'] = $value;
		}
		if($key == 'last_name'){
			$userdata['last_name'] = $value;
		}
		
		if($key == 'description'){
			$userdata['description'] = $value;
		}
		if($key == 'role'){
			$userdata['role'] = $value;
		}
		$userdata['role'] = 'subscriber';

	}
	if($args->login_as_email == true){
		$userdata['user_login'] = $userdata['user_email'];
	}

	$user_id = wp_insert_user($userdata);

	foreach ($this_form_array as $key => $value) {

		$guardian_meta = false;

		if(
			($key == 'user_pass')||
			($key == 'user_login')||
			($key == 'user_nicename')||
			($key == 'user_url')||
			($key == 'user_email')||
			($key == 'display_name')||
			($key == 'nickname')||
			($key == 'first_name')||
			($key == 'lase_name')||
			($key == 'description')||
			($key == 'role')
		){
			$guardian_meta = true;
		}
	

		if($guardian_meta == false){
			update_user_meta($post_id,$key,$value);
		}
	}

	//On success
	if ( is_wp_error($user_id) ){

		$my_message = $user_id->get_error_message();

		echo '<div class="message updated">'.$my_message.'</div>';

	}else{

		if ( $user_id && !is_wp_error( $user_id ) ) {

			$user_data = get_userdata( $user_id );
	        $code = sha1( $user_id . $user_data->user_registered );
	        $id_to_redirect = $args->redirect_to_id;
	        $activation_link = add_query_arg( array( 'key' => $code, 'user' => $user_id, 'redirect_id' => $id_to_redirect ), get_permalink( /* YOUR ACTIVATION PAGE ID HERE */ ));

			echo $activation_link;
			
			global $post;
			$display_args = json_decode( urldecode ( get_post_meta($post->ID,'_meta_afd_form_render_box_alpaca', true )), true );


			function replace_code($string,$codes){
					foreach ($codes as $key => $value) {
						$string = str_replace($key,$value,$string);
					}
					return $string;
				}	

			if($display_args['dependence_three'] == true){
				$code = array(
	        		'{user_login}' => $userdata['user_login'],
					'{user_pass}' => $userdata['user_pass'],
					'{user_email}' => $userdata['user_email'],
					'{active_link}' => '<a href="'.$activation_link.'">'.$activation_link.'</a>',
					'{blog_name}' => get_bloginfo( 'name' )
				);
				$mail_message = replace_code($display_args['display_messages_after_signon_v_email'],$code);
				$mail_title = replace_code($display_args['display_messages_after_signon_mail_title'],$code);
				$mail_content = replace_code($display_args['display_messages_after_signon_mail_content'],$code);
				
			}else{
				$mail_message = 'Mail with activation link was send to: '.$userdata['user_email'];
				$mail_title = get_bloginfo( 'name' ).' : Confirmation email';
				$mail_content = 'Welcome '.$userdata['user_login'].' <br/><br/> Please confirm this email by link: <a href="'.$activation_link.'">'.$activation_link.'</a> <br><br>Best<br>'.get_bloginfo( 'name' );
				
			}					

	       	$message = '';
			$message .= '<div class="message updated">';
			$message .= '<p>'.$mail_message.'</p>';
			$message .= '</div>';
			
			echo $message;

			add_user_meta( $user_id, '_activation_key', $code, true );
			add_filter('wp_mail_content_type',create_function('', 'return "text/html"; '));
	        wp_mail( $userdata['user_email'], $mail_title, '<html><body>'.$mail_content.'</body></html>' );

	       // wp_mail( $userdata['user_email'], 'Potwierdzenie rejestracji w grze', '<html><body><p>Graczu,</p><p>Dziękujmy za rejestrację. Aktywuj swoje konto klikając w poniższy link:</br> ' . $activation_link . '</p><p>Graj, baw się i wygrywaj.</p><p>Pozdrawiamy<br/>Kruger Sp. z.o.o</p></body></html>' );

		}
	}
}

function fa_update_user($args){

	$this_form_array = get_fields($post->ID);
	foreach ($this_form_array as $key => $value) {
		// Zmieniamy dla zalogowanego usera
		$my_user['ID'] = get_current_user_id();
		
		if($key == 'user_email'){
			$my_user['user_email'] = $value;
		}
		if($key == 'first_name'){
			$my_user['first_name'] = $value;
		}		
		if($key == 'last_name'){
			$my_user['last_name'] = $value;
		}

	}
	wp_update_user( $my_user );
}

function fa_login_user($args){

	global $post;
	global $FA_ajax;



	$this_form_array = get_fields($post->ID);

	$creds = array();
	foreach ($this_form_array as $key => $value) {
		if($key == 'user_login'){
			$creds['user_login'] = $value;
		}
		if($key == 'user_pass'){
			$creds['user_password'] = $value;
		}
	}
	$creds['remember'] = true;
	wp_logout();
	$user = wp_signon( $creds, false );

	if ( is_wp_error($user) ){
		$return_msg =  $user->get_error_message();

	}else{


		global $returnObj;
		$returnObj['redirect_to_url'] = get_permalink($args->redirect_to_id);
		/*
		wp_redirect($args->redirect);
		exit;*/
	}

	if($FA_ajax == true){
		if($return_msg != ''){
			echo $return_msg;
		}else{
			echo json_encode($user);
		}
	}else{
		echo $return_msg;
	}
		
	




}


function fa_clear_form($args){

	$fields = get_fields($post->ID);
	foreach ($fields as $key => $value) {
		$field_object  = get_field_object($key);

/*		echo '<pre>';
			var_dump($field_object["default_value"]);
		echo '</pre>';*/

		update_field($key, $field_object["default_value"], $post->ID);
	}
}

function fa_dont_display_after_send($args){

	global $acf;
	$acf -> AFD_block_display = true;
	//var_dump($acf);

}
