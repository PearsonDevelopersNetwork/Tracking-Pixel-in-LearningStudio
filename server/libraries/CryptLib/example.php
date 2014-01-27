<?php

/** 
 * Using PHPCryptLib to Create CMAC-AES Hashes
 * 
 * The eCollege RESTful APIs leverage OAuth protocols to grant access to user and partner data.
 * Accessing user-specific resources (i.e. student grades) requires OAuth 2.0 protocol. More
 * Information on eCollege Authentication APIs is available on the Pearson Developer Network: 
 * 
 * http://code.pearson.com/pearson-learningstudio/apis/authentication/authentication-overview
 * 
 * eCollege has implemented two authorization grant types: Password Credentials and Assertion. 
 * 
 * In some cases, students and instructors enter LearningStudio via a Single Sign-On 
 * process from another portal. For this use case, the user often dose not know their 
 * LearningStudio credentials, rather their session is established by a third-party 
 * system acting as their proxy. OAuth 2 establishes support for this type of scenario 
 * via an assertion that the third-party system makes regarding their user, that 
 * LearningStudio can validate as coming from a trusted system. This is accomplished via 
 * the OAuth 2.0 Assertion grant type.
 * Full details of this grant type including assertion format is here: 
 * http://code.pearson.com/pearson-learningstudio/apis/authentication/oauth-20-assertion
 * 
 * In order to sign the assertion, eCollege requires a CMAC-AES hash be generated from 
 * the assertion string and then appended to the assertion. This library generates the  
 * necessary hash in PHP. 
 * 
 * This library is a streamlined version of PHPCryptLib, (c) 2011 Anthony Ferrara and 
 * available at https://github.com/ircmaxell/PHP-CryptLib and released under MIT License.
 * Only the code related to generating CMAC hashes has been included. 
 * 
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, 
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR 
 * PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE 
 * FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR 
 * OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER 
 * DEALINGS IN THE SOFTWARE.
 */

// Include the boostrap file from CryptLib.
require_once 'lib/CryptLib/bootstrap.php'; 

//Instantiate the CMAC-generating class
$eCMAC = new CryptLib\MAC\Implementation\ECollegeCMAC; 

//Define your partner-specific secret key somewhere in your code. 
$your_secret_key = '';

//Set up variables for creating the assertion
$client_name   = "";
$key_moniker   = "";
$client_id 	   = "";
$client_string = "";
$username 	   = "";
$timestamp 	   = str_replace('+00:00','Z',gmdate('c')); 

//Set up variables needed to make the request of the API 
$api_url       = "https://m-api.ecollege.com/token";
$grantType 	   = "assertion";
$assertionType = "urn:ecollege:names:moauth:1.0:assertion"; //either urn:ecollege:names:moauth:1.0:assertion  -- if using a username
															//    or urn:ecollege:names:sourcedid:1.0:assertion -- if using a sourced id


//Assemble the Assertion string following this pattern (see documentation for details)
//$assertion = '{client_name}|{key_moniker}|{client_id}|{client_string}|{username}|{timestamp}';
$assertion = "$client_name|$key_moniker|$client_id|$client_string|$username|$timestamp";


try{

	//Pass Assertion and Key to the generateEcollegeCMAC method. 
	//This assumes the Assertion string has NOT been binary encoded. This method will pack() the 
	//string for you, hash it, and then bin2hex it back to a usable string. 
	$cmac = $eCMAC->generateECollegeCMAC($assertion,$your_secret_key); 

	//$cmac is now equal to something like 989d5631fc2a4cd831ba84e6c7d39478

} catch(Exception $e){ 
	exit($e->getMessage());  
} 
	
//Append the signature hash to the Assertion 
$assertion .= '|'.$cmac; 

// 
// You now have a signed assertion string. To get an access token, use 
// code similar to the following. 
// 

//Set up the body of the POST request
$post_fields = "grant_type=".$grantType."&assertion_type=".$assertionType."&assertion=".$assertion;  


//Set up cURL Transaction
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);


//NOTE: 
// If you experience problems related to an SSL Cert, uncomment the next two lines. 
// You'll know there's a problem if the error message comes from the $curlError variable below
// and says something about SSL. You shouldn't have to do this, but if you do, send us the complete
// error message that inspired you to make this change and let us know it happened, and we'll look
// into any SSL cert issues. 

// curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
// curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);


//execute & get server response;
$api_response = curl_exec($ch); 

//capture errors or other status codes
$curl_error = curl_error($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);


// problem with the transport layer (i.e. SSL problems) 
if($curl_error){

	echo "There was a problem making the API Call. cURL problem: $curlError"; 
	

//porblem with the service / host layer (i.e. Error 400) 
} else if(intval($http_code / 100) >=4){
	
	$decoded_response = json_decode($api_response); 
	$msg = (is_object($decoded_response) && isset($decoded_response->error->message))?$decoded_response->error->message:"No message reported."; 
	echo "The API Server responded with ".$http_code.". Message was: $msg";


// success!
} else {

	$decoded_response = json_decode($api_response); 
	$access_token = $decoded_response->access_token; 
	$expires_in = $decoded_response->expires_in; // seconds 

	echo "<b>Access Token:</b> $access_token<br /><br />"; 
	echo "<b>Expires In:</b> $expires_in<br /><br />"; 
	
}
