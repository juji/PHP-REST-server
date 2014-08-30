<?php
	if(!defined('__ISINCLUDED')) {die('Direct access not permitted');}
	class Config{

		////////////////////////////
		//SETTINGS


		// use CORS 
		// http://en.wikipedia.org/wiki/Cross-origin_resource_sharing
		static $CORS = false;

		// allowed domain
		// rejected domain
		// * means all, always allow
		// if you wan't to reject all cors request, set $CORS instead
		static $CORS_ALLOW = '*';
		static $CORS_REJECT = '*';

		// CORS allowed method
		static $CORS_METHOD = 'GET, POST, PUT, DELETE';

		// CORS allowed header
		static $CORS_HEADERS = 'X-requested-with';


		//error handling	
		static $LOG_FILE = './api.error.log';				// if falsy, will not write error log
		static $ERROR_EMAIL = '';							// if falsy, will not send error message
		static $ERROR_EMAIL_SUBJECT = 'PHP-API-error';
		static $BACKTRACE_LIMIT = 5;

		// HTML ELEMENT ID
		// when request is sent without 'X-requested-with: XmlHttpRequest' header ( iframe )
		// some ISP messed up contents by injecting scripts,
		// so content-type 'text/plain' or 'application/json' is out of the question.
		// To overcome this, enclose JSON data inside html element with an 'id' attribute,
		// and fetch data inside that ID instead.
		// I.e. JSON.parse( iframeDocument.getElementById('data').innerHTML );
		static $HTMLELEMENT_ID = 'data';

		//Generic error message
		static $ERROR_MSSG = 'Sorry, something went bad..';

	}

?>