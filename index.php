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
	require 'templates/index.php';
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
	{
		$app->redirect('/');
	}

	try
	{
		require 'lib/CraigListScraper.class.php';
		$cl_scraper = new CraigListScraper("sites/{$loadConfiguration}");
		$title = $cl_scraper->getInfo()->title;
		require'templates/index.php';
	}
	catch (Exception $e)
	{
		echo $e->getMessage();
	}
});

$app->get('/site/:site/data',function($site) use ($app, $sites)
{

	$init = true;
	$loadConfiguration = "findjobs.locations.xml";

	if(isset($sites[$site]))
	{
		$loadConfiguration = "{$site}.locations.xml";
		$sites[$site] = true;
	}
	else
	{
		$app->redirect('/');
	}

	try
	{
		require 'lib/CraigListScraper.class.php';
		$cl_scraper = new CraigListScraper("sites/{$loadConfiguration}");
		$json_results = array(
			'page_info'		=>$cl_scraper->getInfo(),
			'region_list'	=>$cl_scraper->getRegions(),
			'area_list'		=>$cl_scraper->getAreas(),
			'form_fields'	=>$cl_scraper->getFields()
		);
		header('Content-type: application/json');
		echo json_encode($json_results);
		exit;
	}
	catch (Exception $e)
	{
		echo $e->getMessage();
	}
});

$app->notFound(function () use ($app)
{
    $app->redirect('/');
});

$app->run();