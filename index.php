<?php

set_time_limit(60*3);
error_reporting(E_ALL);
ini_set('error_log', './php_errors.log');
date_default_timezone_set('America/Los_Angeles');

require 'lib/Slim/Slim.php';
$app = new Slim();

$sites = array(
	'findstuff'		=>false,
	'findjobs'		=>false,
	'findgigs'		=>false,
	'findplaces'	=>false,
	'findservices'	=>false,
);

$app->get('/',function() use ($app, $sites)
{
	$title = 'My KraigsList Search';
	$init = false;
	include_once 'templates/index.php';
});

$app->post('/',function() use ($app, $sites)
{
	$loadConfiguration = "findjobs.locations.xml";

	$site = isset($_POST['site'])?$_POST['site']:false;

	if(isset($sites[$site]))
	{
		$loadConfiguration = "{$site}.locations.xml";
	}
	else
	{
		echo json_encode(array());
		return;
	}

	try
	{
		require 'lib/CraigListScraper.class.php';
		$cl_scraper = new CraigListScraper("sites/{$loadConfiguration}");
		$search_field = $cl_scraper->getFields();
		$search_field_name = $search_field[0]['argName'];

		if(isset($_POST[$search_field_name]) && strlen($_POST[$search_field_name]))
		{
			header('Content-type: application/json');
			$cl_scraper->initialize($_POST['include']);
			echo $cl_scraper;
			exit;
		}
	}
	catch (Exception $e)
	{
		echo $e->getMessage();
	}
});

$app->get('/site/:site',function($site) use ($app, $sites)
{

	$init = true;
	$loadConfiguration = "findjobs.locations.xml";

	if(isset($sites[$site]))
	{
		$loadConfiguration = "{$site}.locations.xml";
		$sites[$site] = true;
	}
	else
		$app->redirect('/');

	try
	{
		require 'lib/CraigListScraper.class.php';
		$cl_scraper = new CraigListScraper("sites/{$loadConfiguration}");
		$title = $cl_scraper->getInfo()->title;
		$search_field = $cl_scraper->getFields();
		$search_field_name = $search_field[0]['argName'];

		if(isset($_POST[$search_field_name]) && strlen($_POST[$search_field_name]))
		{
			header('Content-type: application/json');
			$cl_scraper->initialize($_POST['include']);
			echo $cl_scraper;
			exit;
		}
	}
	catch (Exception $e)
	{
		echo $e->getMessage();
	}

	include_once'templates/index.php';
});

$app->notFound(function () use ($app)
{
    $app->redirect('/');
});

$app->run();