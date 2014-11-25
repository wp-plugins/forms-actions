<?php
/* acces to wordpress functions with alpacajs forms API */

function render_posttype_to_alpaca_string($args){
  
  /* $args = 'post_type';  */

  $postTitleString = "";
  global $wpdb;   
  $query = "
  SELECT $wpdb->posts.* 
  FROM $wpdb->posts
  WHERE $wpdb->posts.post_status = 'publish' 
  AND $wpdb->posts.post_type = '".$args."'
  ORDER BY $wpdb->posts.post_date DESC
  ";  
  $posts = $wpdb->get_results( $query , object );

    // var_dump( $posts);
  $postTitleString = 'function(field, callback) { callback([';
  foreach ( $posts as $db_post ){       
      $postTitleString .= '{"text": "'. $db_post->post_title.'", "value": "'. $db_post->ID.'"},';
  } 
  $postTitleString = rtrim($postTitleString, ', ');
    // var_dump($postTitleString);
  $postTitleString = $postTitleString.']);}';
  return $postTitleString;

}

function render_users_to_alpaca_string($args){

    $args = array( 'role' => $args);

    // The Query
    $user_query = new WP_User_Query( $args );
    $counter = 0;
    // User Loop
    if ( ! empty( $user_query->results ) ) {

                $postTitleString = 'function(field, callback) { callback([';
                foreach ( $user_query->results as $user ) {
                if($counter == 0){
                  $postTitleString = $postTitleString. '{"text": "'. $user->display_name.'", "value": "'. $user->ID.'"}';
                }else{
                  $postTitleString = $postTitleString. ',{"text": "'. $user->display_name.'", "value": "'. $user->ID.'"}';
                }
                $counter++;
                
      }
      $postTitleString = $postTitleString.']);}';
      return $postTitleString;

  } else {
    $postTitleString = 'function(field, callback) {callback([{"text": "No users found", "value": "0"}]);}';
    return $postTitleString;
  }

}

function render_postmeta_to_alpaca_string($args){
  
  /* $args = array(
    '$post_id' => 1,
    '$meta_name' => 1,
  )
  */

  echo get_post_meta( $args['post_id'] , $args['meta_name'], true);

}

function render_taxonomy_to_alpaca_string($args){
  
  $tx_args = array(
        'hide_empty'    => false, 
        'parent' => 0,
        );
  $terms = get_terms($args, $tx_args );

  $postTitleString = 'function(field, callback) { callback([';
  foreach ($terms as $term) {
    $postTitleString = $postTitleString."{'text':'".$term->name."','value':'".$term->term_id."'},";
  }
  $postTitleString = rtrim($postTitleString, ', ');
  $postTitleString = $postTitleString.']);}';
  return $postTitleString;

}

function render_option_to_alpaca_string($name, $default){

  $options = explode('|', get_option($name, $default));

  $currenciesString = 'function(field, callback) { callback([';
  foreach ($options as $option) {
    $currenciesString = $currenciesString."{'text':'".$option."','value':'".$option."'},";
  }
  $currenciesString = rtrim($currenciesString, ', ');
  $currenciesString = $currenciesString.']);}';
  return $currenciesString;

}

?>