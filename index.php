<?php
	
	define('__ISINCLUDED', true);
	include '_settings.php';
	include '_includes.php';
	include '_classes.php';

	Rest::init();
	include '_init.php';
	Rest::start();

?>