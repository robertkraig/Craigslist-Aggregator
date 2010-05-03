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

error_reporting(E_ALL);

require 'lib/CraigListScraper.class.php';

$cl_scraper = new CraigListScraper('cljobs/locations.xml');

if(isset($_POST['s']) && strlen($_POST['s']))
{
	$include = $_POST['include'];
	$include = implode('|', $include);
	$include = str_replace('.', '\\.', $include);
	$cl_scraper->initialize($_POST['s'],$include);
	echo $cl_scraper;
}
else
{
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
		<title>Job Search via Craigslist</title>
		<script type="text/javascript" src="/js/jquery-1.4.2.min.js"></script>
		<script type="text/javascript">
			var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
			document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
		</script>
		<script type="text/javascript">
			try {
				var pageTracker = _gat._getTracker("UA-12896175-2");
				pageTracker._trackPageview();
			} catch(err) {}
		</script>
		<link rel="stylesheet" type="text/css" href="/css/body.css" />
	</head>
	<body>
		<form action="" method="post" id="find_jobs">
			<div><a id="change_size" href="#">[-]</a></div>
			<div id="change_size_container">
				<div style="font-size: 24px;">Find yourself a Job</div>
				<cite>I wrote this App because I found myself going back and forth between different areas of Craigslist so I could look up jobs. This is an aggregate of all the sites that you select in <strong>Areas</strong></cite>
				<input type="text" id="search_term" name="s" value="" />
				<cite>Ex: php, C#, .NET, ASP.NET, Linux</cite>
				Region:<br />
				<?php echo implode("\n\t", $cl_scraper->getRegions()); ?>
				Areas: <br /><?php echo implode("\n\t", $cl_scraper->getAreas()); ?>
				<a href="#submit" id="search_btn">Search</a>
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
?>