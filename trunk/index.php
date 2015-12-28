<?php

// Bring in all necessary prereqs
require_once ('libs/smarty/Smarty.inc.php');
require_once ('classes/Database.class.php');

try
{
	// Invoke Smarty
	$template = new Smarty ();
	$template->template_dir = 'template/';
	$template->compile_dir = 'template_c/';
	
	// Invoke the Database class
	$db = new Database ();
}
catch (SmartyException $err)
{
	die ($err->getMessage ());
}
catch (DatabaseException $err)
{
	die ($err->getMessage ());
}

?>