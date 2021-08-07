<?php 
//This file is responsible for managing the entire GUI of the alias plugin. This page is responsible for manual data access requests made by the admin and displaying all previous data access requests. 
?>
<img src="<?php echo plugin_dir_url( __FILE__ ) . 'WhiteonBlueBanner1.jpg'; ?>" style="width:100px;height:35px;padding-left:8px;padding-top:15px;">
<h2 style="color:#40C1E1;">Data Request</h2>
<head>
<link rel="stylesheet" href="http://code.jquery.com/ui/1.9.2/themes/base/jquery-ui.css">
<script src="http://code.jquery.com/jquery-1.8.3.js"></script>
<script src="http://code.jquery.com/ui/1.9.2/jquery-ui.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.4.2/chosen.jquery.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/chosen/1.4.2/chosen.css">
<?php 	
//Lines 14-20 are about collecting all users so they can be chosen from a drop-down menu if the admin wishes to request data access manually
$all_users = get_users();
$usernames = [];
$i = 1;
foreach ($all_users as $user) 
{
	$test = get_userdata($user->ID)->user_login;
	array_push($usernames, $test );
	$i++;
}
?>
<script type="text/javascript">
//Function that populates the table with data access requests and their hashes. From here the admin should get the appropriate hash and use it.
var userArray = <?php echo json_encode($usernames); ?>;
function populate()
{
  for(i=0;i<userArray.length;i++)
    {
      var select = document.getElementById("intended_user");
      select.options[select.options.length] = new Option(userArray[i]);
    }
}
</script>
</head>

<script type="text/javascript">
//Chosen library so there is a useable multi-choice dropdown menu. Is on MIT library so its ok for use.
$(function() {
    $(".chosen-select").chosen();
	 $("#form_field").css('font-family','Sansation');
	$(".chosen-results").css('font-family','Sansation');
});
</script>
<?php 
//Below is the body which sets the UI for manual requests for data access
?>
<div id="rqstfrm">
<body onload="populate();" style="background:white;">
	
<form name = "datarqstform" id = "datarqstform" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF ?page=alias-admin"]);?>">
	
	
  <label for="intended_user" style = "">Username:</label>
  <select id="intended_user" name="intended_user" style="width:350px; margin-left: 69px;">
	  <option value="" disabled selected>Choose a user</option>
  </select><br><br>

	

	
  <label for="intended_data" style=" padding-top: 6px; white-space: nowrap; padding-right: 50px;" >Data you want to request :</label>
	<div class = "pre">	</div> 
  <select  class="chosen-select" multiple="true" name="intended_data[]" id="intended_data" style="width:350px; padding-left: 10px;" data-placeholder="Choose data">
	  <option>Name</option>
      <option>Email</option>
	  <option>Phone Number</option>
	  <option>Username</option>
	  <option>Billing Address</option>
	  <option>Shipping Address</option>
  </select><br><br>
	
  <label for="reason" style = "padding-top: 7px;">Reason for data access:</label> 
  <select name="reason" id="reason" style="width:350px; margin-left: 69px;" >
 <option value="" disabled selected>Choose a reason</option>
    <option>We need it for marketing</option>
    <option>We need it to fill out an order</option>
    <option>We need it to contact you</option>
    <option>We need it for legal purposes</option>
  </select><br><br>

	<input type="hidden" name="dateadded" value="<?php echo time(); ?>">
	
  <input name = "submit" type="submit" style = "margin-left: 8px;" value="Submit">
</form> 
	</body>
</div>
<body>
<h2 style="color:#40C1E1;">Potvrdenky</h2>
<p>After the request is processed it should show up in the table below. If the request is approved you will get the requests hash, which serves as a confirmation ticket. Please use this hash in all your materials, such as emails or orders, which you completed thanks to the data requested.</p>
	
<?php
// Header data to validate API requests. For security reasons the sensitive information was cleared.
$priv_key = '';
$cookie = '';
$business_id = '';
$header_data = array('Content-Type' => 'application/json','X-API-Secret' => $priv_key,'Cookie' => $cookie,'BusinessID' => $business_id);

/*Function that gets called to execute an api call for a data access request
  Paramaters: $databaseId - The id of the user as issued by the server during registration.
              $reason - The reason for the data access request. This will be shown to the user whose data was accessed.
              $accessedData - A list of data that was accessed
  Returns: If the request was approved it will return the data access hash otherwise it will return "ACCESS DENIED"
*/
function DataAccessRequest ($databaseId, $reason, $accessedData)
{
$api_url = 'https://alias2.azurewebsites.net/api/backchannel/AccessEntries';
$body = wp_json_encode( array(    
    "reason" => $reason,
    "accountId" => $databaseid,
    "accessedData" => $accesseddata,
    "accessHash" => "e5f3f94f-d009-4481-82ff-b38892de44b6"
) );

$data = wp_safe_remote_post($api_url, array(
  'method'      => 'POST',
    'headers'     => $header_data,
    'body'        => $body,    
    'data_format' => 'body',
));
if ( is_wp_error( $data ) ) {
    $returnvar = "ACCESS DENIED";
  return $returnvar;
} else {
  return json_decode(wp_remote_retrieve_body($data))->{"accessHash"};
}
} 


//Below are the styles used in the alias page
?>	
<style>
.pre {
    height: 3%; 
    width: 1.28%;
    float: left;
}

	
@font-face {
  font-family: "Sansation";
  src: url("./Sansation_Regular_0.ttf") format("truetype");
}
	h1, h2, select, input {		
		margin-left: 8px;
		font-family: "Sansation", sans-serif;
	}   
	p{
		margin-left: 8px;
		font-family: "Sansation", sans-serif;
		font-size:14px; 
	}
	input{
		font-family: "Sansation", sans-serif;
		background: #40C1E1;
        color: #f1f1f1;
		border: 1px;
		 width: 5em;  height: 2em;
	}
	label {
		font-family: "Sansation", sans-serif;
		font-size:20px; 
		float: left; 
        width:240px;
		margin-left: 8px;
}
.header {
  font-family: "Sansation", sans-serif;
  background: #40C1E1;
  color: #f1f1f1;
  border: 1px;
  margin-right: 37px;
  margin-left: 8px;
}

.sticky {
  position: fixed;
  top: 0;
  width: 100%;
}
.content {
	padding-left: 8px;
	padding-right: 8px;
}
table {
  table-layout:fixed;
  border-collapse: collapse;
  border-spacing: 0;
  width: 100%; 
  border: 1px solid #40C1E1;
}
th {
  font-family: "Sansation", sans-serif;
  text-align: left;
  padding: 8px;
  border: 1px solid #40C1E1;
}
td {
  font-family: "Sansation", sans-serif;
  text-align: left;
  padding: 8px;
  border: 1px solid #40C1E1;
  width: 20%;
}
</style>
<div class="header" id="myHeader">
  <table class = "sturdy">
  	<tr>
      <th style = "width: 158px;font-size:13px">Date (Y-M-D UTC Time)</th>
      <th style = "width: 175px;font-size:13px">Username</th>
      <th style = "width: 500px;font-size:13px">Requested data</th>
	  <th style = "width: 280px;font-size:13px">Reason</th>
      <th>Hash</th>
    </tr>
  </table>
</div>
<body>
<div style="height:243px;overflow:auto;margin-right: 17px; width: 1481px;" class="content" id="rgsttbl" >
  <table class = "sturdy">
<?php 

//Function that reverses the table that lists all the data access requests so the most recent requests are shown first.
function reloadtable()
{
	global $wpdb;
	$prefix = $wpdb->prefix;
	$tablename = $prefix.'ALSreceipts';
	$TBP_results = $wpdb->get_results("SELECT * FROM $tablename");
	foreach (array_reverse($TBP_results) as $TBP_row)
	{
  		$date = $TBP_row->time;
  		$username = $TBP_row->username;
  		$dataused = $TBP_row->dataused;
  		$reason = $TBP_row->reason;
  		$hash = $TBP_row->hash;

  ?>
    <tr>
      <th style = "width: 158px;font-size:13px"><?php echo $date ?></th>
       <th style = "width: 175px;font-size:13px"><?php echo $username ?></th>
       <th style = "width: 500px;font-size:13px"><?php echo $dataused ?></th>
    <th style = "width: 280px;font-size:13px"><?php echo $reason ?></th>
      <th><?php echo $hash ?></th>
    </tr>  
<?php }
} 
reloadtable();    
    ?>
</table>
 </div>
</body>
<?php

/*Function that converts the parts of the manual data access request, as created by the admin through the UI, into a usable string.
  Paramaters: $data - The raw data as outputed by the post
  Returns: Usable string to be used in a data access reuqest api call and be stored on the appropriate database.
*/
function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}

//This handles the response of the manual data access request and cleans up the data and then executes the data access request api call and stores the results in the appropriate database
if ($_SERVER["REQUEST_METHOD"] == "POST") 
{
  //Processing the input data
  $username = test_input($_POST["intended_user"]);
  $dataused = $_POST["intended_data"];
  $reason = test_input($_POST["reason"]);
  // Ignoring an empty request (maybe made by accident)
  if($username == "" || $username == null || $dataused == "" || $dataused == null || $reason == "" || $reason == null ) {}
	else 
  {
	global $wpdb;
    $datachoice = implode(",", $dataused);
    $temptime = date('Y/m/d H:i:s', $_POST['dateadded']);   
    $prefix = $wpdb->prefix;
    $result = get_user_by( 'login', $username )->ID;
    $databaseid = get_user_meta($result,'databaseid',true);
    // API call
    $responseanswer = DataAccessRequest($databaseid,$reason,$datachoice);
    // db insert
    $wpdb->insert( $prefix.'ALSreceipts', array( 'username' => $username, 'hash' => $responseanswer, 'dataused' => $datachoice, 'reason' => $reason, 'time' => $temptime, ) ) ;
    header("Refresh:0");
	}
}


?>
	
