<?php
/****************************************************
paypal.config.php

This is the configuration file for the samples.This file
defines the parameters needed to make an API call.

PayPal includes the following API Signature for making API
calls to the PayPal sandbox:

API Username 	sdk-three_api1.sdk.com
API Password 	QFZCWN5HZM8VBG7Q
API Signature 	A.d9eRKfd1yVkRrtmMfCFLTqa6M9AyodL0SJkhYztxUi8W9pCXF6.4NI

Called by CallerService.php.
 ****************************************************/
/**
# API user: The user that is identified as making the call. you can
# also use your own API username that you created on PayPal�s sandbox
# or the PayPal live site
 */
//for 3-token -> API_USERNAME,API_PASSWORD,API_SIGNATURE  are needed

//Sandbox
//define('API_USERNAME', 'lix059-facilitator2_api1.163.com');
define('API_USERNAME', 'yuzc_api1.turingcat.com');
//Live


/**
# API_password: The password associated with the API user
# If you are using your own API username, enter the API password that
# was generated by PayPal below
# IMPORTANT - HAVING YOUR API PASSWORD INCLUDED IN THE MANNER IS NOT
# SECURE, AND ITS ONLY BEING SHOWN THIS WAY FOR TESTING PURPOSES
 */
//Sandbox
//define('API_PASSWORD', 'EDL3R4RX3K54W8RL');
//paypal
define('API_PASSWORD', 'NVDBS8JV37HG993Q');
//Live

/**
# API_Signature:The Signature associated with the API user. which is generated by paypal.
 */

//sandbox
//define('API_SIGNATURE', 'AzeyvgeaxcFDkj4sSqrcx7kebOLMAjJ0cOassWgfr2XnT2YUcw5HVH9e');
//paypal
define('API_SIGNATURE', 'AUu6I753dM5aZ4noxxMKAKq39ZbKAfu3jDXTBGr2c0fZTfhXieUWnqLn');
//live


/**
# Endpoint: this is the server URL which you have to connect for submitting your API request.
 */
/* Live */
define('API_ENDPOINT', 'https://api-3t.paypal.com/nvp');
//sandbox
//define('API_ENDPOINT', 'https://api-3t.sandbox.paypal.com/nvp');

/*
 # Third party Email address that you granted permission to make api call.
 */
define('SUBJECT','');
/*for permission APIs ->token, signature, timestamp  are needed
define('AUTH_TOKEN',"4oSymRbHLgXZVIvtZuQziRVVxcxaiRpOeOEmQw");
define('AUTH_SIGNATURE',"+q1PggENX0u+6vj+49tLiw9CLpA=");
define('AUTH_TIMESTAMP',"1284959128");
*/
/**
USE_PROXY: Set this variable to TRUE to route all the API requests through proxy.
like define('USE_PROXY',TRUE);
 */
define('USE_PROXY',FALSE);
/**
PROXY_HOST: Set the host name or the IP address of proxy server.
PROXY_PORT: Set proxy port.

PROXY_HOST and PROXY_PORT will be read only if USE_PROXY is set to TRUE
 */
define('PROXY_HOST', '127.0.0.1');
define('PROXY_PORT', '808');

/* Define the PayPal URL. This is the URL that the buyer is
   first sent to to authorize payment with their paypal account
   change the URL depending if you are testing on the sandbox
   or going to the live PayPal site
   For the sandbox, the URL is
   https://www.sandbox.paypal.com/webscr&cmd=_express-checkout&token=
   For the live site, the URL is
   https://www.paypal.com/webscr&cmd=_express-checkout&token=
   */
//live
define('PAYPAL_URL', 'https://www.paypal.com/webscr&cmd=_express-checkout&token=');
//sandbox
//define('PAYPAL_URL', 'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout&useraction=commit&token=');
//define('PAYPAL_URL_Mark', 'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout&useraction=commit&token=');

/**
# Version: this is the API version in the request.
# It is a mandatory parameter for each API request.
# The only supported value at this time is 2.3
 */

define('VERSION', '96.0');

// Ack related constants
define('ACK_SUCCESS', 'SUCCESS');
define('ACK_SUCCESS_WITH_WARNING', 'SUCCESSWITHWARNING');

?>