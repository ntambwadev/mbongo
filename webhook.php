
<?php
/****************************VERY IMPORTANT TO READ ***************************
                            WECASHUP DEFAULT WEBHOOK                      
                                                                          
                    This piece of code is written to enable               
                merchants to get informed about the status of            
        pending transactions that are being processed asynchronously by   
        WeCashUp. If a transaction was pending and WeCashUp receive a     
        payment confirmation from one of the CASH, TELCO or M-WALLET      
		provider, WeCashUp will inform the merchant by forwarding the 
        transaction status to this Default Webhook script on the merchant's 
		server. As WeCashUp is the only one system knowing the merchant's 
		secret key and that the communication between WeCashUp and the 
		merchant's server is secure (via SSL), WeCashUp will send the 
		merchant_secret with the transaction_uid, transaction_status, 
		transaction_details and transaction_token. 
		 
		To reduce the risk of getting spammed by other individuals willing
		to deceive the merchant's default webhook, merchant should considers 
		that the received data are valid if and only if : 
		
		1. the received merchant_secret match his configuration merchant_secret 
		2. the received transaction_uid match one of the transaction in his database 
		3. the received transaction_token match the exact same transaction's transaction_token 
		 
		If everything match,  merchant should update the current transaction 
		in his database and can take action (cancel transaction if received status 
		is "FAILED" or launch the delivery process if received status is "PAID".
		The possible status are : PAID or FAILED 		   
 																		   
 ***************************************************************************/

	require 'vendor/autoload.php';

	$merchant_secret = 'sk_test_p7Aq1TW4oKqJD6zIodsWSoCW2fFOZoNlfNqJM0CuBbLe';  
			
	// Create and initialize variables to be sent to confirm the that the ongoing transaction is associated with the current merchant
	
	$received_transaction_merchant_secret = null;//create an empty received_transaction_merchant_secret
	$received_transaction_uid = null;//create an empty received_transaction_uid 
	$received_transaction_status  = null;//create an empty received_transaction_status
	$received_transaction_details = null;//create an empty received_transaction_details
	$received_transaction_token = null;//create an empty received_transaction_token
	$authenticated = 'false'; //create an authentication boolean and initialize it at false
	
	//extracting data from the post and filling the variable above
	if(isset($_POST['merchant_secret'])){
		$received_transaction_merchant_secret = $_POST['merchant_secret']; //Get the merchant_secret posted by WeCashUp.
		
	}
	
	if(isset($_POST['transaction_uid'])){
		$received_transaction_uid = $_POST['transaction_uid']; //Get the transaction_uid posted by WeCashUp
	}
	if(isset($_POST['transaction_status'])){
		$received_transaction_status  = $_POST['transaction_status']; //Get the transaction_status posted by WeCashUp
	}
	if(isset($_POST['transaction_amount'])){
		$received_transaction_amount  = $_POST['transaction_amount']; //Get the transaction_amount posted by WeCashUp
	}
	
	if(isset($_POST['transaction_receiver_currency'])){
		$received_transaction_receiver_currency  = $_POST['transaction_receiver_currency']; //Get the transaction_amount posted by WeCashUp
	}
	
	if(isset($_POST['transaction_details'])){
		$received_transaction_details  = $_POST['transaction_details']; //Get the transaction_details posted by WeCashUp
	}
	
	if(isset($_POST['transaction_token'])){
		$received_transaction_token  = $_POST['transaction_token']; //Get the transaction_token posted by WeCashUp
	}
	
	if(isset($_POST['transaction_type'])){
		$received_transaction_type  = $_POST['transaction_type']; //Get the transaction_type posted by WeCashUp
	}	
	
	echo '<br><br> received_transaction_merchant_secret : '.$received_transaction_merchant_secret;
	echo '<br><br> received_transaction_uid : '.$received_transaction_uid;
	echo '<br><br> received_transaction_token : '.$received_transaction_token;
	echo '<br><br> received_transaction_details : '.$received_transaction_details;
	echo '<br><br> received_transaction_amount : '.$received_transaction_amount;
	echo '<br><br> received_transaction_status : '.$received_transaction_status;
	echo '<br><br> received_transaction_type : '.$received_transaction_type;

/***** SAVE THIS IN YOUD DATABASE - start ****************/
	
	$file = $received_transaction_uid.'.txt';
	$txt = "received_transaction_merchant_secret : ".$received_transaction_merchant_secret."\n".
			"received_transaction_uid : ".$received_transaction_uid."\n".
			"received_transaction_token : ".$received_transaction_token."\n".
			"received_transaction_details : ".$received_transaction_details."\n".
			"received_transaction_amount : ".$received_transaction_amount."\n".
			"received_transaction_receiver_currency : ".$received_transaction_receiver_currency."\n".
			"received_transaction_status : ".$received_transaction_status."\n".
			"received_transaction_type : ".$received_transaction_type."\n";

	$myfile = fopen($file, "w") or die("Unable to open file!");
	fwrite($myfile, $txt);
	fclose($myfile);

/***** SAVE THIS IN YOUD DATABASE - end ****************/

// GET TRANSACTION FROM PARSE DB 

	use Parse\ParseObject;
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
	$server = "https://parseapi.back4app.com";
	$path = "/";

	ParseClient::initialize($app_id, $rest_key, $master_key);  
	ParseClient::setServerURL($server, $path);

	try {
		file_put_contents("php://stderr", "MBGONGO: Trying fetch PAYMENT in PARSE with transaction_uid: " . $received_transaction_uid." \n");

		$query = new ParseQuery("Payment");
		$query->equalTo("transaction_uid", $received_transaction_uid);
		$results = $query->find();
		
		echo "Successfully retrieved " . count($results) . " Payments.";
		error_log("MBONGO: Successfully retrieved " . count($results) . " Payments from Parse.");

		$database_transaction_uid = '';//************* LOAD FROM YOUR DATABASE ****************
		$database_transaction_token = '';//************* LOAD FROM YOUR DATABASE ****************

		// Do something with the returned ParseObject values
		for ($i = 0; $i < count($results); $i++) {
		  $object = $results[$i];

		  $database_transaction_uid = $object->get('transaction_uid');
		  $database_transaction_token = $object->get('transaction_token');

		  echo $object->getObjectId() . ' - ' . $object->get('transaction_uid');
		  error_log("MBONGO: Successfully retrieved payment with transaction_uid: " . $object->get('transaction_uid') . " from Parse.");
		}

		//Authentication |We make sure that the received data come from a system that knows our secret key (WeCashUp only)
		if($received_transaction_merchant_secret !=null && $received_transaction_merchant_secret == $merchant_secret){
			//received_transaction_merchant_secret is Valid
			
			echo '<br><br> merchant_secret [MATCH]'; 
			
			//Now check if you have a transaction with the received_transaction_uid and received_transaction_token
			
			// $database_transaction_uid = 'TEST_UID';//************* LOAD FROM YOUR DATABASE ****************
			// $database_transaction_token = 'TEST_TOKEN';//************* LOAD FROM YOUR DATABASE ****************
			
			if($received_transaction_uid != null && $received_transaction_uid == $database_transaction_uid){
				//received_transaction_merchant_secret is Valid
				
				echo '<br><br> transaction_uid [MATCH]'; 
				
				if($received_transaction_token  != null && $received_transaction_token == $database_transaction_token){
					//received_transaction_token is Valid 
					
					echo '<br><br> transaction_token [MATCH]'; 
					
					//All the 3 parameters match, so...
					$authenticated = 'true';
				}
			}
		}

		echo '<br><br>authenticated : '.$authenticated;
		error_log("MBONGO: authenticated " . $authenticated);
			
		if($authenticated == 'true'){
			
			//Update and process your transaction
			
			if($received_transaction_status =="PAID"){
				//Save the transaction status in your database and do whatever you want to tell the user that it's transaction succeed
				echo '<br><br> transaction_status : '.$transaction_status;
				error_log('MBONGO: WECASHUP transaction_status: ' . $transaction_status);
				
			}else{ //Status = FAILED
				
				//Save the transaction status in your database and do whatever you want to tell the user that it's transaction failed
				echo '<br><br> transaction_status : '.$transaction_status;
				error_log('MBONGO: WECASHUP transaction_status: ' . $transaction_status);
			}
			
			/***** SAVE THIS IN YOUD DATABASE - start ****************/
				
			$file = 'transactions.txt';
			$txt = "received_transaction_merchant_secret : ".$received_transaction_merchant_secret."\n".
					"received_transaction_uid : ".$received_transaction_uid."\n".
					"received_transaction_token : ".$received_transaction_token."\n".
					"received_transaction_details : ".$received_transaction_details."\n".
					"received_transaction_status : ".$received_transaction_status."\n".
					"received_transaction_type : ".$received_transaction_type."\n";
			
			$myfile = fopen("newfile.txt", "w") or die("Unable to open file!");
			fwrite($myfile, $txt);
			fclose($myfile);
			/***** SAVE THIS IN YOUD DATABASE - end ****************/
				
				/*
					NOTE : 	You can analyze each variable in order to process further operations like sending 
							an email to the customer to inform him that his transaction failed or launching the 
							delivery process if the transaction succeed.
				*/ 
			
		}
		
		/* If the one of the 3 parameters above doesn't match, this default webhook script will ignore the request. 
		   You can also save them in your logs if you want to keep track of everything that happens here.
		   
		   If you are here it means that you are done with the WeCashUp's integration, we wish you to make lot of money and become billionaire.
		   If so ma broda ma sista, please don't forget to buy me a beer *_*.
		*/


		} catch (\Exception $e){
			echo $e->getMessage();
			error_log('MBONGO: Failed to create fetch Payment, with error message:' . $ex->getMessage());
		}
		
?>
