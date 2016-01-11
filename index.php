<?
/* 
Name:  SMS to Email Newsletter Signup
Version:  1.0

Twilio Fields:
- ToCountry
- ToState
- SmsMessageSid
- NumMedia
- ToCity
- FromZip
- SmsSid
- FromState
- SmsStatus
- Body
- FromCountry
- To
- ToZip
- NumSegments
- MessageSid
- AccountSid
- From
- ApiVersion
*/

header("content-type: text/xml");
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";

/* REQUIRED FUNCTIONS [START] */
// MAILCHIMP SUBMISSION
function mailchimp_submission( $api, $target, $data = false ){
	$ch = curl_init( $api['url'] . $target );
	curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
		'Content-Type: application/json', 
		'Authorization: ' . $api['login'] . ' ' . $api['key'],
	) );
 
	curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, 'POST' );
	curl_setopt( $ch, CURLOPT_TIMEOUT, 5 );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
	curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );

	if( $data )
		curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $data ) );
 
	$response = curl_exec( $ch );
	curl_close( $ch );
 
	return $response;
}

// EXACTTARGET SUBMISSION
function exacttarget_submission($list_id, $mid, $email_address){
	$url = 'http://cl.exct.net/subscribe.aspx?lid=' . $list_id;
	$fields = array(
		'thx' => urlencode($thx),
		'error' => urlencode($error),
		'MID' => urlencode($mid),
		'Email Address' => urlencode($email_address),
		'SubAction' => urlencode("sub_add_update")
	);	

	foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
	rtrim($fields_string, '&');

	$ch = curl_init();
	curl_setopt($ch,CURLOPT_URL, $url);
	curl_setopt($ch,CURLOPT_POST, count($fields));
	curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
	curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
	
	$result = curl_exec($ch);
	curl_close($ch);		

	return $result;
}
/* REQUIRED FUNCTIONS [START] */

/* REQUIRED VARIABLES [START] */
// BASIC SETUP
$enable_mailchimp = "yes";
$enable_exacttarget = "yes";
$enable_response = "yes";

// MAILCHIMP
$mailchimp_api_key = "";
$mailchimp_login = "";
$mailchimp_list_id = "";
$mailchimp_datacenter = "";
$mailchimp_target = "lists/" . $mailchimp_list_id . "/members"; 
$mailchimp_api = array('login' => $mailchimp_login, 'key' => $mailchimp_api_key, 'url' => "https://" . $mailchimp_datacenter . ".api.mailchimp.com/3.0/");

// EXACTTARGET
$exacttarget_mid = "";
$exacttarget_list_id = "";

// TWILIO
$twilio_response = "Thanks For Subscribing!";
/* REQUIRED VARIABLES [END] */

// VALID EMAIL ADDRESS
if (!filter_var($_POST['Body'], FILTER_VALIDATE_EMAIL) === false) {
	if($enable_mailchimp == "yes"){
		$mc = mailchimp_submission( $mailchimp_api, $mailchimp_target, array( 'status' => 'subscribed', 'email_address' => $_POST['Body'] ));
	}

	if($enable_exacttarget == "yes"){
		$et = exacttarget_submission($exacttarget_list_id, $exacttarget_mid, $_POST['Body']);
	}

	if($enable_response == "yes" && $twilio_response != ""){
	?>
	<Response><Message><?= $twilio_response; ?></Message></Response>
	<?
	}

// INVALID EMAIL ADDRESS
}else{
?>
	<Response><Message>Please Provide A Valid Email Address.</Message></Response>
<?	
}