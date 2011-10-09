<?php
/**
 * @author Robert S Kraig
 * @version 0.7
 *
 * I know that this source code can be a bit messy,
 * but the purpose is far more important than the
 * actual code at this point. If you want to help
 * me improve it make a suggestion to my email or IM me.
 */

set_time_limit(60*3);
error_reporting(E_ALL);
ini_set('error_log', './php_errors.log');
date_default_timezone_set('America/Los_Angeles');

$POST_GET = array_merge($_GET,$_POST);

$init = true;
$find_stuff = $find_jobs = $find_gigs = $find_places = $find_services = false;
$title = 'My KraigsList Search';

if(isset($POST_GET['site']))
{
	$site = $POST_GET['site'];
	$siteList = array(
		'findjobs',
		'findgigs',
		'findplaces',
		'findstuff',
		'findservices'
	);

	if(in_array($site, $siteList))
	{
		$loadConfiguration = "{$site}.locations.xml";
	}
	else
	{
		$loadConfiguration = "findjobs.locations.xml";
	}

	switch($POST_GET['site'])
	{
		case 'findstuff':
			$find_stuff = true;
			break;
		case 'findjobs':
			$find_jobs = true;
			break;
		case 'findgigs':
			$find_gigs = true;
			break;
		case 'findplaces':
			$find_places = true;
			break;
		case 'findservices':
			$find_services = true;
			break;
	}

}
else
{
	$init = false;
}

try
{
	if($init)
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
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
	<head>
		<meta charset="utf-8" />
		<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
		<title><?php echo $title; ?></title>
		<link rel="stylesheet" type="text/css" href="/css/body.css" />
		<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
		<script src="/js/app.js"></script>
		<script type="text/javascript">
		  var _gaq = _gaq || [];
		  _gaq.push(['_setAccount', 'UA-12896175-6']);
		  _gaq.push(['_trackPageview']);

		  (function() {
			var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
			ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
			var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
		  })();
		</script>
	</head>
	<body>
		<div id="header">
			<ul>
				<li><a href="http://www.compubomb.net">Home</a></li>
				<li><a <?php echo ($find_stuff? 'style="color:red;"':'style="color:black;"'); ?> href="http://<?php echo $_SERVER['SERVER_NAME']; ?>?site=findstuff">Stuff</a></li>
				<li><a <?php echo ($find_jobs? 'style="color:red;"':'style="color:black;"'); ?> href="http://<?php echo $_SERVER['SERVER_NAME']; ?>?site=findjobs">Jobs</a></li>
				<li><a <?php echo ($find_gigs? 'style="color:red;"':'style="color:black;"'); ?> href="http://<?php echo $_SERVER['SERVER_NAME']; ?>?site=findgigs">Gigs</a></li>
				<li><a <?php echo ($find_places? 'style="color:red;"':'style="color:black;"'); ?> href="http://<?php echo $_SERVER['SERVER_NAME']; ?>?site=findplaces">Places</a></li>
				<li><a <?php echo ($find_services? 'style="color:red;"':'style="color:black;"'); ?> href="http://<?php echo $_SERVER['SERVER_NAME']; ?>?site=findservices">Services</a></li>
			</ul>
			<div style="clear: both;"></div>
		</div>
<?php
if($init) include_once 'inc/form.php';
?>
	</body>
</html>
<?php
}
catch (Exception $e)
{
	echo $e->getMessage();
}