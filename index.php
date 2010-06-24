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

ini_set('error_log', './php_errors.log');

error_reporting(E_ALL);

require 'lib/CraigListScraper.class.php';
try
{
	$cl_scraper = new CraigListScraper('clrepo/locations.xml');

	$search_field = $cl_scraper->getFields();
	$search_field_name = $search_field[0]['argName'];

	if(isset($_POST[$search_field_name]) && strlen($_POST[$search_field_name]))
	{
		$cl_scraper->initialize($_POST['include']);
		echo $cl_scraper;
	}
	else
	{
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
		<title><?php echo $cl_scraper->getInfo()->title; ?></title>
		<script type="text/javascript" src="/js/jquery-1.4.2.min.js"></script>
		<script type="text/javascript">
		  var _gaq = _gaq || [];
		  _gaq.push(['_setAccount', 'UA-12896175-3']);
		  _gaq.push(['_trackPageview']);

		  (function() {
			var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
			ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
			var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
		  })();
		</script>
		<link rel="stylesheet" type="text/css" href="/css/body.css" />
	</head>
	<body>
		<form action="" method="post" id="find_items">
			<div><a id="change_size" href="#">[-]</a></div>
			<div id="change_size_container">
				<div style="font-size: 24px;"><?php echo $cl_scraper->getInfo()->pagetitle; ?></div>
				<cite><?php echo $cl_scraper->getInfo()->pagedesc; ?></cite>
				<?php foreach($cl_scraper->getFields() as $field): ?>
				<label class="fields" for="<?php echo $field['argId']; ?>"><?php echo $field['argTitle']; ?></label>
				<input class="fields" type="text" name="<?php echo $field['argName']; ?>" id="<?php echo $field['argId']; ?>" />
				<br style="margin:0;padding:0; height:1px; clear: left;" />
				<?php endforeach; ?>
				<cite><?php echo $cl_scraper->getInfo()->pagesearchexample; ?></cite>
				Region:<br />
				<?php echo implode("\n\t", $cl_scraper->getRegions()); ?>
				Areas: <br /><?php echo implode("\n\t", $cl_scraper->getAreas()); ?>
				<a href="#submit" id="search_btn">Search</a>
				<input type="submit" style="display:none;" />
				<div><a id="donate" href="http://www.compubomb.net/pages/payme" target="_blank">Donate To Author</a></div>
				<img alt="loader" id="loader" style="display:none; position: absolute; bottom: 0; right: 0; margin:10px; margin-bottom: 35px;" src="/img/loading.gif" />
			</div>
		</form>
		<script type="text/javascript">
			window.PHP_SELF = "<?php echo $_SERVER['PHP_SELF']; ?>";
		</script>
		<script type="text/javascript" src="js/app.js"></script>
		<div id="content-container">
			<div style="display:none; margin-left: 10px;" id="toggle_disp">
				<a style="display:inline-block; text-decoration: none;" href="#" rel="open">Close All</a>
			</div>
			<div style="display:none;" id="content"></div>
		</div>
		<script type="text/javascript" src="/js/jquery.simplemodal-1.3.4.min.js"></script>
	</body>
</html>
<?php
	}
}
catch (Exception $e)
{
	echo $e->getMessage();
}
?>