<?php
	
	Router::add('user',function($data){
		Response::ok('Hello, i am user.');
	});

	Router::add('user/:id',function($data){
		Response::ok('Hello, i am user id '.$data['vars']['id'].'.');
	});

?>