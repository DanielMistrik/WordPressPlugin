<?php
// This file is the primary file for the entire plugin. It is responsible for tracking data accesses allowing for automatic data access requests reducing the need for the admins to manually add every data access request. This file also redirects to templates/admin.php that serves as the GUI of the plugin.
session_start();
/**
 * @package AliasPlugin
 */
/*
 Plugin Name: Alias Plugin
 Plugin URI: 
 Description: GDPR solved technically
 Version: 0.0.0
 Author: 
 Author URI: 
*/
 //Unauthorized looking at files. Plugin has to shut down
if (! defined('ABSPATH')) 
{
 	die("Access DENIED");
}
//Declaring databases the software uses
include_once("ALS_db_file.php");
register_activation_hook(__FILE__,"DTB_tb_create");

//Action that fires when the user clicks on the alias button to open the graphical version of the alias plugin.
add_action('admin_menu','alias_admin_menu');

//Alias menu declaration
function alias_admin_menu()
{
	add_options_page('Alias Page','Alias','manage_options','alias-admin','alias_execute_function','',4);
}

//File that contains all the graphics and UI, always called
function alias_execute_function()
{
	require_once plugin_dir_path(__FILE__).'templates/admin.php';
}
// Header data to validate API requests. For security reasons the sensitive information was cleared.
$priv_key = '';
$cookie = '';
$business_id = '';
$header_data = array('Content-Type' => 'application/json','X-API-Secret' => $priv_key,'Cookie' => $cookie,'BusinessID' => $business_id);

/*  Unites a number of user metadata into one coherent shipping address.
	Parameters: $userid - Id of user whose shipping address will be returned
	Return: $address - multi-line string that details the shipping address.
*/
function shippingaddress($userid)
{
	$address = '';
	$address .= get_user_meta( $userid, 'shipping_first_name', true );
    $address .= ' ';
    $address .= get_user_meta( $userid, 'shipping_last_name', true );
    $address .= "\n";
    $address .= get_user_meta( $userid, 'shipping_company', true );
    $address .= "\n";
    $address .= get_user_meta( $userid, 'shipping_address_1', true );
    $address .= "\n";
    $address .= get_user_meta( $userid, 'shipping_address_2', true );
    $address .= "\n";
    $address .= get_user_meta( $userid, 'shipping_city', true );
    $address .= "\n";
    $address .= get_user_meta( $userid, 'shipping_state', true );
    $address .= "\n";
    $address .= get_user_meta( $userid, 'shipping_postcode', true );
    $address .= "\n";
    $address .= get_user_meta( $userid, 'shipping_country', true );
	$address .= "\n";
	return $address;
}

/*  Unites a number of user metadata into one coherent billing address.
	Parameters: $userid - Id of user whose billing address will be returned
	Return: $address - multi-line string that details the billing address.
*/
function billingaddress($userid)
{
	$address = '';
	$address .= get_user_meta( $userid, 'billing_first_name', true );
    $address .= ' ';
    $address .= get_user_meta( $userid, 'billing_last_name', true );
    $address .= "\n";
    $address .= get_user_meta( $userid, 'billing_address_1', true );
    $address .= "\n";
    $address .= get_user_meta( $userid, 'billing_address_2', true );
    $address .= "\n";
    $address .= get_user_meta( $userid, 'billing_city', true );
    $address .= "\n";
    $address .= get_user_meta( $userid, 'billing_state', true );
    $address .= "\n";
    $address .= get_user_meta( $userid, 'billing_postcode', true );
    $address .= "\n";
    $address .= get_user_meta( $userid, 'billing_country', true );
	$address .= "\n";
	return $address;
}

/*  API Call to get connection status of user with the alias app. Is he using alias,does he use alias to monitor said business and so on.
	Parameters: $serverid-The user's id as issued by the server.
	Returns: $body - string with information on the user's connection with the alias app.
*/
function getconnectionstatus($serverid)
{
	// Actual API request
	$api_url = 'Alias/Actual/URL/Removed/For/Privacy/Reasons'.$serverid.'/connection';
	$data = wp_safe_remote_get($api_url, array('headers' => $header_data ));
	$body = wp_remote_retrieve_body( $data );
	//Processing the what the api returned
	switch($body)
	{
		case "no user":
			$body = "User doesn't have Alias";
			break;
		case "confirmation pending":
			$body = "User has Alias but isnt connected to you";
			break;
		case "completed":
			$body = "User is connected";
			break;
		default:
			$body = 'Server Error';
			break;
	}	
	return $body;
}

/*  Function that sends a users data to the server. Should be called when someone updates the given user's data.
	Parameters: $serverid - Id of the user as issued by the server during registration.
				$userid - Id of the user as issued by wordpress.
	Returns: $returnvar - returns confirmation of a succesful API call or 'Access Denied' if there was an error
*/
function editAccount($serverid, $userid)
{	
	// Prepares the API url
	$api_url = 'Alias/Actual/URL/Removed/For/Privacy/Reasons';
	$api_url .= $serverid;
	// Because the server was only made to handle one address and wordpress has two the function only sends the larger address in terms of string length	
	$shipping = strlen(preg_replace("/\s+/","",shippingaddress($userid)));
    $billing = strlen(preg_replace("/\s+/","",billingaddress($userid)));
	if($shipping > $billing)
	{
		$adrsdirection = 'shipping';
	}
	else{
		$adrsdirection = 'billing';
	}	
	// json encodes the data to be sent
	$body = wp_json_encode( array(    
    "created" => 1596101134,
    "firstName" => get_user_meta( $userid, 'first_name', true ),
    "lastName" => get_user_meta( $userid, 'last_name', true ),
    "tel" => get_user_meta( $userid, 'billing_phone', true ),
    "email" => get_userdata($userid)->user_email,
    "street1" => get_user_meta( $userid, $adrsdirection.'_address_1', true ),
    "street1Number" => null,
    "street2" => get_user_meta( $userid, $adrsdirection.'_address_2', true ),
    "street2Number" => null,
    "city" => get_user_meta( $userid, $adrsdirection.'_city', true ),
    "zip" => get_user_meta( $userid, $adrsdirection.'_postcode', true ), 
    "country" => get_user_meta( $userid, $adrsdirection.'_country', true ), 
    "imageId" => null,
    "isDefaultAccount" => false
	) );
	// encodes the API call
	$data = wp_safe_remote_post($api_url, array(
	'method'      => 'POST',
    'headers'     => $header_data,
    'body'        => $body,    
    'data_format' => 'body',
	));
	// Error handling and processing of a succesful API call
	if ( is_wp_error( $data ) ) 
	{
    $returnvar = "Access Denied";
	return $returnvar;
	} 
	else 
	{
	$returnvar = json_decode(wp_remote_retrieve_body($data))->{"updated"};
	return $returnvar;
	}
}	

/*  Function that submits a data request to the server through an API call and returns the data access hash if successful	
	Parameters: $serverid - The user's id as issued by the server during registration
				$reason - The reason for the data request. This will be seen by the user
				$accessedData - The list of data that was accessed
	Returns: $returnvar - If the API call was successful the it returns the data access hash otherwise 'ACCESS DENIED'
*/
function dataRequestApiCall($serverid, $reason, $accessedData)
{
	// Constructing the API call
	$api_url = 'Alias/Actual/URL/Removed/For/Privacy/Reasons';
	$body = wp_json_encode( array(    
    	"reason" => $reason,
    	"accountId" => $serverid,
    	"accessedData" => $accessedData,
    	"accessHash" => "e5f3f94f-d009-4481-82ff-b38892de44b6"
	) );

 	$data = wp_safe_remote_post($api_url, array(
		'method'      => 'POST',
    	'headers'     => $header_data,
    	'body'        => $body,    
    	'data_format' => 'body',
	));
 	//Error handling and processing what the API call returned
	if ( is_wp_error( $data ) ) 
	{
    	$returnvar = "ACCESS DENIED";
		return $returnvar;
	} 
	else 
	{
		return json_decode(wp_remote_retrieve_body($data))->{"accessHash"};
	}
}

/*  Function that registers a new user with the business-side of Alias immediately as they are registered through wordpress. Because this is immediately
	after registration only a few pieces of data, like name and email, are available and so only those are sent to the server.
	Parameters: $firstname - First name of the user that has been registered
				$lastname - Last name of the user that has been registered
				$email - Email of the user that has been registered
	Returns: Either a list of the format [userid,last_update_timestamp] or ["Error",""] if the call failed.
*/
function UserRegisterRequest ($firstname, $lastname, $email)
{
	// Preparing the API call
	$body = wp_json_encode( array(    
    	"created" => time(),
	    "firstName" => $firstname,
	    "lastName" => $lastname,
	    "tel" => null,
	     "email" => $email,
	     "street1" => null,
	     "street1Number" => null,
	     "street2" => null,
	     "street2Number" => null,
	     "city" => null,
	     "zip" => null,
	     "country" => null,
	     "imageId" => null,
	     "isDefaultAccount" => false
	));

	$data = wp_safe_remote_post('Alias/Actual/URL/Removed/For/Privacy/Reasons', array(
		'method'      => 'POST',
	    'headers'     => $header_data,
	    'body'        => $body,    
	    'data_format' => 'body',
	));	
	// Error handling and processing data the API call returned.
	try
	{
	return [json_decode(wp_remote_retrieve_body($data))->{"id"},json_decode(wp_remote_retrieve_body($data))->{"updated"}];
	} 
	catch(Exception $e)
	{
	return ["Error",""];
	}
}	

//Session variable added so when the admin opens up a user profile and updates something and the profile reloads the user only gets one data request.	
if (!isset($_SESSION["preseen"])) 
{
    $_SESSION["preseen"] = false;
} 

// Triggers when a user is updated. Changes internal database and sends updates to the server. Used as a backup if edit_user_profile doesnt trigger.
add_action( 'profile_update', 'myprofileupdate', 10, 2 );

/*  Function that is called by the action above and calls the editAccount and updates the last_update_timestamp on its own database.
	Parameters: $user_id - The id of the user, given by wordpress, whose data has been updated.
				$old_user_data - Old user data before updated. Not used in this function but because actions are standardized this function had to take this 
								 parameter as well
	Returns: Null
*/	
function myprofileupdate( $user_id, $old_user_data ) 
{
	    $_SESSION["preseen"] = true;
		$updated = editAccount(get_user_meta($user_id,'databaseid',true), $user_id);	
	    update_user_meta( $user_id, 'updatetime', $updated );
} 

//Action fires when a new order from the woocommerce plugin is created and the called funcion creates a data access request which is sent to the useful database and server.  
add_action('woocommerce_new_order', 'orderReview',10,1);

/*  Function that is called when a new order is created and creates a data access request that reflects the order information. Is trigerred by the action 
	above.
	Parameters: $orderid - Wordpress-issued id of the order. Used to collect information on the order
	Returns: Null
*/
function orderReview($orderid)
{ 
	global $wpdb;
	//Collect information about the order
	$order = wc_get_order( $orderid );
	$user = $order->get_user();
	$user_id = $order->get_user_id();
	//Collect information on the user who made the order
	$username = get_userdata($user_id)->user_login;
	$temptime = date('Y/m/d H:i:s', time());
	//Prepare the data request (reason and list of data used)
	$reason = 'We need it to fill out an order';	
	$dataused = 'Name, Email, Phone Number, Shipping Address, Billing Address'; 
	$databaseid = get_user_meta($user_id,'databaseid',true);
	$prefix = $wpdb->prefix;
	// Execute the data request call to the server and store the results in the appropriate database
	$responseanswer = dataRequestApiCall($databaseid,$reason,$dataused);
	$wpdb->insert( $prefix.'ALSreceipts', array( 'username' => $username, 'hash' => $responseanswer, 'dataused' => $dataused, 'reason' => $reason, 'time' => $temptime ) ) ; 
}

//Admin changes profile. This function registers that he saw the data. The profile_update hook above registers and updates the changes
 add_action('edit_user_profile', 'dataRequest');

/*  Function that prepares the information neccessary for a data access request call that uses all the user's data. Is called by the action above.
	Paramaters: $user - The user object of the user whose data was changed by the admin.
	Returns: Null
*/
function dataRequest($user)
{
	// Checks to prevent double registering a single data access
	if(!$_SESSION["preseen"])
	{   
		global $wpdb;
		// Prepares the data for the data access request
		$userid = $user->ID;
		$username = get_userdata($userid)->user_login;
		$temptime = date('Y/m/d H:i:s', time());
		$reason = 'We are working on your account';
		$dataused = 'Name, Email, Phone Number, Shipping Address, Billing Address'; 	   
		$databaseid = get_user_meta($userid,'databaseid',true);
		// Executing the data access request api call and placing the results into the appropriate database
		$responseanswer = dataRequestApiCall($databaseid,$reason,$dataused);	      
		$prefix = $wpdb->prefix;
		$wpdb->insert( $prefix.'ALSreceipts', array( 'username' => $username, 'hash' => $responseanswer, 'dataused' => $dataused, 'reason' => $reason, 'time' => $temptime ) ) ; 
    
	}
    else
    {
  	   $_SESSION["preseen"] = false;	
    }  
}

/*  Function that takes the new data from the first paramater and updates the user's data with them/
	Paramaters: $updatedBody - The updated body of data whose data will be applied to the user's.
				$user_id - The id of the user assigned by wordpress whose data will be updated 
	Returns: Null
*/
function syncAccount($updatedBody,$user_id)
{
	// Again the two address problem so the updated address will be the one that is longer on record.
	$shipping = strlen(preg_replace("/\s+/","",shippingaddress($userid)));
    $billing = strlen(preg_replace("/\s+/","",billingaddress($userid)));
	if($shipping > $billing){
		$chosenAddress = 'shipping';
	}else{
		$chosenAddress = 'billing';
	}
	// Actual data being updated, a very mechanical process
	update_user_meta( $user_id, 'first_name', $updatedBody->firstName );	
	update_user_meta( $user_id, 'last_name', $updatedBody->lastName );	
	update_user_meta( $user_id, 'billing_phone', $updatedBody->tel );
	update_user_meta( $user_id, $chosenAddress.'_address_1', $updatedBody->street1 );
	update_user_meta( $user_id, $chosenAddress.'_address_2', $updatedBody->street2 );
	update_user_meta( $user_id, $chosenAddress.'_city', $updatedBody->city );
	update_user_meta( $user_id, $chosenAddress.'_postcode', $updatedBody->zip );
	update_user_meta( $user_id, $chosenAddress.'_country', $updatedBody->country );
	wp_update_user( array('ID' => $user_id, 'user_email' => $updatedBody->email ) );
	update_user_meta( $user_id, 'updatetime', $updatedBody->updated);
}

/*  Function that informs the site admins, through an email, that a user has deleted their alias account and they should no longer use their data.
	Paramaters: $deletedUserId - User id of the user assigned by wordpress that has deleted their alias account
	Returns: Null
*/
function informofdeletion($deletedUserId)
{
	// Gets nickname of the deleted user so the admin knows whose account wass deleted
	$deletedUser = get_user_meta($deletedUserId,'nickname',true);
	$admins = get_users('role=Administrator');	
	// Each admin is sent an email informing them of their deletion
    foreach ($admins as $admin) 
    {
		$to = $admin->user_email;
    	$subject = 'User '.$deletedUser.' deleted their account';
		$message = "User ".$deletedUser." deleted their account. You should no longer update or use their data. \n";
		$message .= "Best Regards, \n";
		$message .= "Alias";	
    	wp_mail($to, $subject, $message );
    }  
}


// Filter that sets up a cron schedule,a regularly timed event that triggers a function.
add_filter( 'cron_schedules', 'add_cron_interval' );

/*  Function that adds a cron schedule of one minute and adds it cron schedules
	Paramaters: $schedules - The cron schedules to which the one_minute interval will be added
	Returns: The cron schedules with the one minute schedule added
*/
function add_cron_interval( $schedules ) 
{ 
    $schedules['one_minute'] = array(
    'interval' => 60,
    'display'  => esc_html__( 'Every one minute' ), );
    return $schedules;
}

// Action that triggers every x time units as specified by the cron schedule and the scheduled event
add_action( 'bl_cron_hook', 'bl_cron_exec' );

// Sets up a scheduled event according to the cron schedule that was created above
if ( ! wp_next_scheduled( 'bl_cron_hook' ) ) 
{
    wp_schedule_event( time(), 'one_minute', 'bl_cron_hook' );
}

//Function that maintains that all the accounts on wordpress have up to date user data. Reacts to changes of user data done on the alias app and then
//reported to the server.
function bl_cron_exec()
{
	// Prepares arguements for getting a list of all users from the wordpress
	$args = array(
        'meta_query' => array(
            array(
                'key'     => 'databaseid',
                'value'   => ' ',
                'compare' => 'LIKE',
            )
        ),
    );
    $users = get_users($args);	
    // Foreach user checks with the server whether their data was updated in which case syncAccount, defined above, will be called
    foreach ($users as $user) 
    {
    	// Gets basic info on user
		$dbid = $user->databaseid;  
		$api_url = 'Alias/URL/Hidden/For/Privacy/Reasons'.$dbid;
    	$data = wp_safe_remote_get( $api_url , $header_data );
		$body = json_decode( wp_remote_retrieve_body( $data ));  
		$user_id = $user->ID;
		$last_update_timestamp = get_user_meta($user_id,'updatetime',true);
		$response = getconnectionstatus($dbid);
		// Checks whether the hasn't deleted their account
		if(($body->state) == 2)
		{   		
			if(get_user_meta($user_id,'constatus',true) != "User has deleted their account")
			{
			update_user_meta($user_id, 'constatus', 'User has deleted their account' );
			informofdeletion($user_id);
			}
		}
		else if($body->id != '')
		{
			if($body->updated > $last_update_timestamp)
			{			
				syncAccount($body,$user_id);
			}
		}	
		if(get_user_meta($user_id,'constatus',true) == '')
		{
			add_user_meta($user_id, 'constatus', $response);
		}
		elseif ((get_user_meta($user_id,'constatus',true) == "User has deleted their account")) 
		{
		}
		else
		{
			update_user_meta($user_id, 'constatus', $response );
		}
  	}
} 

//Action that fires when a new user is registered
add_action( 'user_register', 'add_to_db', 10, 1);

/*  Function that is triggered when a new user is registered and saves the data in the appropriate database as well as creating an account for the user with
	alias on the server.
	Paramaters: $user_id - The id of the newly registered user as assigned by wordpress.
	Return Null
*/
function add_to_db( $user_id ) 
{
	// Processing basic data from the user
	$fullname = '';
	$firstname = '';
	$lastname = '';	
	$email = get_userdata($user_id)->user_email;
    if ( isset( $_POST['first_name'] ) )
    {
        $fullname .= $_POST['first_name'];
		$firstname = $_POST['first_name'];
	}
    if ( isset( $_POST['last_name'] ) )
    {
        $fullname .= $_POST['last_name'];
		$lastname = $_POST['last_name'];
	}
	// Call to register the user with Alias
	$databaseidregister = UserRegisterRequest($firstname, $lastname, $email);	
	add_user_meta($user_id, 'databaseid', $databaseidregister[0]);
	add_user_meta($user_id, 'updatetime', $databaseidregister[1]);
}

// Filter that triggers the function that wants to modify the user table
add_filter( 'manage_users_columns', 'new_modify_user_table' );

/*  Adds a connection status column to the wordpress user overview table. Cannot be changed by the admin.
	Parameters: The user overview table to whom the column will be added
	Returns: The modified table with the new column added
*/
function new_modify_user_table( $table ) 
{
	$table['userConnection'] = "User Connection";
    return $table;
}

// Filter that calls a function when the user table is going to be updated.
add_filter( 'manage_users_custom_column', 'new_modify_user_table_row', 10, 3 );

/*  Function that populates and updated the new user connection column. 
	Paramaters: $val - Not relevant to our function but neccessary because of fixed filter structure
				$column_name - The name of the column that is to be updated.
				$user_id - The wordpress-issued id of the user whose row is being updated now.
*/
function new_modify_user_table_row( $val, $column_name, $user_id ) 
{
	// Checks whether the name of the column is userConnection. The function only needs to work with that column.
    switch ($column_name) {
        case 'userConnection' :
            return get_user_meta( $user_id,'constatus',true);
        default:
    }
    return $val;
}


class AliasPlugin
{
	function register()
	{
		
	} 

	// On activation this function runs through all the users to check whether some were added while the function was turned off so they can be registered
	// by Alias.
	function activate()
	{
		flush_rewrite_rules();	
		// Gets all the users from wordpress
		$users = get_users();
		$body = array();
		// Iterates through all the users to check whether some were added while the plugin was offline. If so it adds them to the array body
		foreach($users as $user)
		{
			if(!($user->databaseid))
			{
				array_push($body,array(    
    			"created" => time(),
    			"firstName" => $user->first_name,
    			"lastName" => $user->last_name,
    			"tel" => null,
    			"email" => get_userdata($user->ID)->user_email,
    			"street1" => null,
    			"street1Number" => null,
    			"street2" => null,
     			"street2Number" => null,
     			"city" => null,
     			"zip" => null,
    			"country" => null,
    			"imageId" => null, 
				"isDefaultAccount" => false
				));
			}
		};
		// Batch registers users from the $body array
		$body = wp_json_encode($body);
		$data = wp_safe_remote_post('Alias/URL/Hidden/For/Privacy/Reasons', array(
		'method'      => 'POST',
    	'headers'     => $header_data,
    	'body'        => $body,    
    	'data_format' => 'body',
		));	
		$returneddata = json_decode(wp_remote_retrieve_body($data));
		$i = 0;
		$returnedmatrix = array();
		// Batch adds registration results to the appropriate databases
		foreach($returneddata as $array)
			{
				try
				{
					array_push($returnedmatrix,array($array->{"id"},$array->{"updated"}));
				}
				catch(Exception $e)
				{
					array_push($returnedmatrix,array("Error",""));
				}
			}
		// Batch adds user meta data			
		$i = 0;
		foreach($users as $user)
		{
			if(!($user->databaseid))
			{
				add_user_meta($user->ID, 'databaseid', $returnedmatrix[$i][0]);
				add_user_meta($user->ID, 'updatetime', $returnedmatrix[$i][1]);
				$i++;
			}
		}
	}

	public function admin_index() 
	{
	}

	function deactivate()
	{
		flush_rewrite_rules();
	}

	function custom_post_type()
	{
		register_post_type('test', ['public'=> true, 'label' => 'test']);
	}
}

// Instantiates a new aliasPlugin object and the plugin is started. 
if (class_exists('AliasPlugin'))
{
	$aliasPlugin = new AliasPlugin();
}
$aliasPlugin->register();

register_activation_hook(__FILE__, array( $aliasPlugin, 'activate'));

register_activation_hook(__FILE__, array( $aliasPlugin, 'deactivate'));


?>