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
		/* messages array */
		if($pre_kay_name[0] == 'message'){
			$messages_array[$key] = $value;
			
			if($value != ''){
				$message.= '<p><b>'. $pre_kay_name[1] .'</b> :'. $value .'</p>';
			}
		
		}
		/* attachments array */
		if($pre_kay_name[0] == 'attachment'){


			$attachment = $value;
			var_dump($attachment);

			$pathAtray = explode('/',$attachment);
	
			$end = end($pathAtray); 
			$secondEnd = prev($pathAtray); 
			$upload_dir = $upload_dir.'/'.$secondEnd.'/'.$end;		
				
			$attachments_array[$key] = $upload_dir;
		}
		$counter++;
	}

/*	echo '<pre>';
	var_dump($messages_array);
	var_dump($subjects_array);
	var_dump($emails_array);
	var_dump($attachments_array);
	echo '</pre>';
	die(); */

	wp_mail( $emails_array , reset($subjects_array) , $message, $headers , $attachments_array );
	

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