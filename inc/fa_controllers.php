<?php
function fa_targeted_questions($args){

		echo '<h1>Find best questionary target</h1>';

		$sourceArray = get_fields($post->ID);
		$max_points = 0;

		foreach ($args as $key => $value) {
			
			$post_id = $value -> post_type;
			$thisArray = get_fields($post_id);
/*			echo '<pre>';
			var_dump($thisArray);
			echo '</pre>';*/

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

			echo '----------------------<br/>'.$points.' pkt';
			echo '<br/>'.$percent.' %<br/>';

			if( $points > $max_points ){
				$max_points = $points;
				$winner_id = $post_id;
			}

		}

		echo '<h1>The WINNER is: '.get_the_title($winner_id).'</h1>';
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

	$new_post['post_type'] = $args->post_type;
	$this_form_array = get_fields($post->ID);

	foreach ($this_form_array as $key => $value) {
		if($key == 'post_title'){
			$new_post['post_title'] = $value;
		}
		if($key == 'post_content'){
			$new_post['post_content'] = $value;
		}
	}

	$new_post['post_status'] = 'publish';
	wp_insert_post( $new_post );

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
