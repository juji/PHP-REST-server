<?php
	
	Router::add('get','sample',function($v){

		
		Response::ok('ok response');
		

	});

	Router::add('get','sample/:something',function($v){

		$something = $v['vars']['something'];

		/*
	    *
	    * $v = array(
	    *   "method" => "get",
	    *   "path" => "the/path/no/slashes",
	    *   "base" => "/"
	    *   "vars" => array( "something" => "thing" )
	    * )
	    *
	    */

		if($something != 'a thing') Response::error('should be a thing');
		Response::ok(array('it\'s a thing'));

	});

?>