<?php
	
	class Config{


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


	}

?>