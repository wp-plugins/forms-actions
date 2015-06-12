<?php
require_once("../../../../wp-load.php");
if ( current_user_can( 'manage_options' ) && $_POST['object_data'] != NULL ) {
    /* A user with admin privileges */
			require_once plugin_dir_path( __DIR__ ) . '/inc/Spyc.php';
			/* ----------------------------------- */
			/* 1.1. Create database declaration    */
			$object_name = $_POST['object_data']['object_name'];
			$db_array[$object_name] = $_POST;
			//$db_array[$object_name]['object_name'] = $_POST['object_name'];

			/* create posttype declarations array */

			$db_old_array = Spyc::YAMLLoad( plugin_dir_path( __DIR__ ) . '/yaml/database/arguments/database-arguments.yaml');
			
			require_once plugin_dir_path( __DIR__ ) . '/inc/init-yaml-get-merge.php';

		    // update yaml file

		    $db_array = array_merge( $db_old_array , $db_array );

			file_put_contents( plugin_dir_path( __DIR__ ) . 'yaml/database/arguments/database-arguments.yaml' , Spyc::YAMLDump( $db_array ));

			// create new table

			global $wpdb;	

		    //require_once UIGENCLASS_PATH . 'Spyc.php';
		    
			//$debuger_db = Spyc::YAMLLoad( GLOBALDATA_PATH . 'uigen-database/arguments/database-arguments.yaml' );    
			$debuger_db = $db_array;

			foreach ($debuger_db as $db_tb_name => $db_props) {

				$db_create_table_string = '';
				$db_create_table_string .= "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}".$db_tb_name."` ( \n";
				$db_create_table_string .= "`ID` int(5) NOT NULL AUTO_INCREMENT, \n";
				foreach ($db_props["object_data"]['db_table_columns'] as $db_col_props) {
					$db_create_table_string .= "`".$db_col_props['db_column_name']."` ".$db_col_props['db_column_type']." NOT NULL, \n";
					//var_dump('db>' , $db_col_props['db_column_name']);
					//var_dump('db>' , $db_col_props['db_column_type']);
				}

				$db_create_table_string .= " PRIMARY KEY (`ID`) \n";
				//$db_create_table_string .= " CHARACTER SET utf8 COLLATE utf8_general_ci \n";
				$db_create_table_string .= " )  \n";
				$db_tables_array[$db_tb_name] = $db_create_table_string;
			}
			//var_dump($db_tables_array);

			// Create tables
			function print_resoult(){
				echo '<pre style="font-size:9px">';
				foreach ($db_tables_array as $db_tb => $db_sql_synax) {
					echo '<br/>----------------<br/>create '.$db_tb.'<br/>----------------<br/>';
					echo $db_sql_synax;
					$db_msg = $wpdb->query($db_sql_synax);
					
					echo '<br/>----------------return----------------<br/>';
					echo $db_msg;
				}
				echo '</pre>';
			}
}

/* AJAX METHODS */
if($_POST['wpdbselect'] != NULL){
	global $wpdb;
	//global $WP_user;
	

	$sql =
		"
		SELECT * 
		FROM ".$wpdb->prefix.$_POST['from']."
		WHERE id_spotkanie = ".$_POST['where']['id_spotkanie']."
	";

	$resoults = $wpdb->get_results($sql);
	
	
	foreach ($resoults as &$value) {
		$value->avatar = get_user_meta($value->id_user,'avatar',true);
	}

}

echo json_encode($resoults);

?>