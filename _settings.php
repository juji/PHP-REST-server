<?php
	if(!defined('__ISINCLUDED')) {die('Direct access not permitted');}
	class Config{

		////////////////////////////
		//SETTINGS


		// use CORS 
		// http://en.wikipedia.org/wiki/Cross-origin_resource_sharing
		static $CORS = true;

		// allowed domain
		// '*' means allow for all
		static $CORS_ALLOW = '*';

		// CORS allowed method
		static $CORS_METHODS = 'GET, POST, PUT, DELETE, OPTIONS, HEAD';

		// CORS allowed header
		static $CORS_HEADERS = 'X-Requested-With, Origin, X-CSRFToken, Content-Type, Accept';

		// Root directory of API endpoint
		// example: 
		// http://www.sample.com/api/v3 -> '/api/v3/'
		// http://api.sample.com/ -> '/'
		static $ROOT_DIR = '/api/';

		// Error handling
		static $ERROR_EMAIL = false;
		static $ERROR_EMAIL_SUBJECT = 'API error';

		// generic error message
		// leave blank to send system error text to client
		static $ERROR_MSSG = '';
		static $BACKTRACE_LIMIT = 5;

		// auto Content-Type:
		// if (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') return 'application/json'
		// else return 'text/html'
		//
		// leave blank for auto Content-Type
		// possible values:
		// - json
		// - html
		static $CONTENT_TYPE = '';

		// encapsulate data for an html response in html tag
		// ISPs just love to inject scripts
		static $HTMLELEMENT_ID = 'data';


	}

?>
