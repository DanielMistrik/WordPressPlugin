<?php
/* This file is responsible for creating all the databases used by the plugin.
ALSreceipts - Database that keeps data on all data access requests. 
*/

global $ALS_db_version;
$ALS_db_version = '1.0';
function DTB_tb_create()
{
	global $wpdb;
	global $ALS_db_version;
	$table_name = $wpdb->prefix . 'ALSreceipts';	
	$charset_collate = $wpdb->get_charset_collate();
	if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) 
	{
		$sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		username varchar(55) DEFAULT '' NOT NULL,
		dataused varchar(150) DEFAULT '' NOT NULL,
		reason varchar(55) DEFAULT '' NOT NULL,
		hash varchar(55) DEFAULT '' NOT NULL,
		PRIMARY KEY  (id)
		) $charset_collate;";
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
		add_option( 'jal_db_version', $ALS_db_version );
	}	
	$wpdb->query("ALTER TABLE wp_alsreceipts MODIFY dataused VARCHAR(250)");
	$wpdb->query("ALTER TABLE wp_alsreceipts MODIFY hash VARCHAR(250)");
}