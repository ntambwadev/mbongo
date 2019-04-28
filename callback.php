
<?php

	require 'vendor/autoload.php';

	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Methods: GET, POST');
	
	$merchant_uid = 'VuEOhgzgUkbLDV7378AyhFm4sH02';            
	$merchant_public_key = 'pk_test_WaKezJlBISMRtgYa'; 
	$merchant_secret = 'sk_test_p7Aq1TW4oKqJD6zIodsWSoCW2fFOZoNlfNqJM0CuBbLe';    
	$transaction_uid = '';// create an empty transaction_uid
	$transaction_token  = '';// create an empty transaction_token
	$transaction_provider_name  = ''; // create an empty transaction_provider_name
	$transaction_confirmation_code  = ''; // create an empty confirmation code
	if(isset($_POST['transaction_uid'])){
	$transaction_uid = $_POST['transaction_uid']; // Get the transaction_uid posted by the payment box
	}
	if(isset($_POST['transaction_token'])){
	$transaction_token  = $_POST['transaction_token']; // Get the transaction_token posted by the payment box
	}
	if(isset($_POST['transaction_provider_name'])){
	$transaction_provider_name  = $_POST['transaction_provider_name']; // Get the transaction_provider_name posted by the payment box
	}
	if(isset($_POST['transaction_confirmation_code'])){
	$transaction_confirmation_code  = $_POST['transaction_confirmation_code']; // Get the transaction_confirmation_code posted by the payment box
	}
	$url = 'https://www.wecashup.com/api/v2.0/merchants/'.$merchant_uid.'/transactions/'.$transaction_uid.'?merchant_public_key='.$merchant_public_key;
	
	//Steps 7 : You must complete this script at this to save the current transaction in your database.
	/* Provide a table with at least 5 columns in your database capturing the following
	/  transaction_uid | transaction_confirmation_code| transaction_token| transaction_provider_name | transaction_status */
	
	use Parse\ParseQuery;
	use Parse\ParseACL;
	use Parse\ParsePush;
	use Parse\ParseUser;
	use Parse\ParseInstallation;
	use Parse\ParseException;
	use Parse\ParseFile;
	use Parse\ParseClient;
	use Parse\ParseGeoPoint;

	$master_key = "yPc6M834wK7qwaJsMHY8lTx97RhNX2kZeIxhfm4W";
	$app_id = "5QUa5y0lcxNstWnw7onLUaBGHL2uIKW4YzTO2TEJ";
	$rest_key = "hwkUY2rYjfzbeLOVChaBaN42dHF3lxJcnhEyLf9v";
	$server = "https://parseapi.back4app.io";
	$path = "/";

	ParseClient::initialize($app_id, $rest_key, $master_key);  
	ParseClient::setServerURL($server, $path);

	// example query

	$payment = new ParseObject("Payment");

	$payment->set("name", $merchant_uid);
	$payment->set("transaction_provider_name", $transaction_provider_name);
	$payment->set("transaction_confirmation_code", $transaction_confirmation_code);
	$payment->set("transaction_uid", $transaction_uid);
	$payment->set("transaction_status", $transaction_status);
	$payment->set("transaction_token", $transaction_token);
	

	try {
	  $payment->save();
	  echo 'New object created with objectId: ' . $payment->getObjectId();
	} catch (ParseException $ex) {  
	  // Execute any logic that should take place if the save fails.
	  // error is a ParseException object with an error code and message.
	  echo 'Failed to create new object, with error message: ' . $ex->getMessage();
	}

	
	//Step 8 : Sending data to the WeCashUp Server
	
	$fields = array(
	'merchant_secret' => urlencode($merchant_secret),
	'transaction_token' => urlencode($transaction_token),
	'transaction_uid' => urlencode($transaction_uid),
	'transaction_confirmation_code' => urlencode($transaction_confirmation_code),
	'transaction_provider_name' => urlencode($transaction_provider_name),
	'_method' => urlencode('PATCH')
	);
	foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
	rtrim($fields_string, '&');
	$ch = curl_init();
	
	curl_setopt($ch, CURLOPT_URL,$url);
	curl_setopt($ch, CURLOPT_POST, count($fields));
	curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	
	//Step 9  : Retrieving the WeCashUp Response
	
	$server_output = curl_exec ($ch);
	
	echo $server_output;
	
	curl_close ($ch);
	
	$data = json_decode($server_output, true);
	
	if($data['response_status'] =="success"){
	
		//Do wathever you want to tell the user that it's transaction succeed or redirect him/her to a success page
	
		$location = 'https://www.wecashup.cloud/cdn/tests/websites/PHP/responses_pages/success.html';
	
	}else{
	
		//Do wathever you want to tell the user that it's transaction failed or redirect him/her to a failure page
	
		$location = 'https://www.wecashup.cloud/cdn/tests/websites/PHP/responses_pages/failure.html';
	
	}
	
	//redirect to your feedback page
	echo '<script>top.window.location = "'.$location.'"</script>';
?>
        
        