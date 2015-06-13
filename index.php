<?php
	
	//die('sdfgasdf');
	define('__ISINCLUDED', true);
	define('__INCLUDEDJCMS', true);

	include '_settings.php';
	include '_includes.php';
	include '_classes.php';

	Rest::init();
	include '_init.php';
	Rest::start();

?>