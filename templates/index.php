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
				<li><a <?php echo ($sites['findstuff']? 'style="color:red;"':'style="color:black;"'); ?> href="http://<?php echo $_SERVER['SERVER_NAME']; ?>/site/findstuff">Stuff</a></li>
				<li><a <?php echo ($sites['findjobs']? 'style="color:red;"':'style="color:black;"'); ?> href="http://<?php echo $_SERVER['SERVER_NAME']; ?>/site/findjobs">Jobs</a></li>
				<li><a <?php echo ($sites['findgigs']? 'style="color:red;"':'style="color:black;"'); ?> href="http://<?php echo $_SERVER['SERVER_NAME']; ?>/site/findgigs">Gigs</a></li>
				<li><a <?php echo ($sites['findplaces']? 'style="color:red;"':'style="color:black;"'); ?> href="http://<?php echo $_SERVER['SERVER_NAME']; ?>/site/findplaces">Places</a></li>
				<li><a <?php echo ($sites['findservices']? 'style="color:red;"':'style="color:black;"'); ?> href="http://<?php echo $_SERVER['SERVER_NAME']; ?>/site/findservices">Services</a></li>
			</ul>
			<div style="clear: both;"></div>
		</div>
<?php
if($init) include_once 'templates/inc/form.php';
?>
	</body>
</html>