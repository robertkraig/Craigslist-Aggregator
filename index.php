<?php
/**
 * @author Robert S Kraig
 * @version 0.5
 *
 * I know that this source code can be a bit messy,
 * but the purpose is far more important than the
 * actual code at this point. If you want to help
 * me improve it make a suggestion to my email or IM me.
 */

error_reporting(E_ALL);

$xmlstr = file_get_contents('cljobs/locations.xml');
$xml = simplexml_load_string($xmlstr, 'SimpleXMLElement', LIBXML_NOCDATA);

$locations = array();
$areas = array();
foreach($xml->xpath('/cljobs/locations/location') as $location)
{
	$loc = get_object_vars($location);
	$locations[] = $loc;
	extract($loc);
	$areas[$partial] = '<label for="'.$partial.'"><input class="region '.$type.'" type="checkbox" id="'.$partial.'" name="include[]" value="'.$partial.'" />'.$name.', '.$state.'</label>';
	unset($name);
	unset($url);
	unset($partial);
	unset($type);
}

$regions = array();
foreach($xml->xpath('/cljobs/regions/region') as $region)
{
	$reg = get_object_vars($region);
	extract($reg);
	$regions[] = '<label for="'.$type.'"><input class="regions" type="checkbox" id="'.$type.'" name="region[]" value="'.$type.'" />'.$name.'</label>';
	unset($type);
	unset($name);
}

function replace_query(&$array,$find)
{
	$find = urlencode($find);
	foreach($array as $key=>$val)
	{
		$array[$key]['url'] = str_replace('{find}', $find, $array[$key]['url']);
	}
}

function getJobs($location)
{
	$file = @file_get_contents($location['url']);
	if(!$file) return array();

	$dom = new DOMDocument();
	@$dom->loadHTML($file);

	$xpath = new DOMXPath($dom);
	$p_tags = $xpath->evaluate("/html/body//blockquote//p");
	$a_tags = $xpath->evaluate("/html/body//blockquote//p/a");

	$jobs = array();
	for ($i = 0; $i < $p_tags->length; $i++) {
		$title = $p_tags->item($i);
		$name = $title->textContent;
		$name = str_replace('<<', ' - ', $name);
		$fields = explode('-', $name);
		$jobs[$i]['location'] = $location['partial'];
		$jobs[$i]['info'] = array(
			'date' => trim($fields[0]),
			'field' =>  $fields[count($fields)-1],
			'from' => $location['partial']
		);
	}
	for ($i = 0; $i < $a_tags->length; $i++) {
		$link = $a_tags->item($i);
		$location = $link->getAttribute('href');
		$name = $link->textContent;
		$name = substr($name, 0, strlen($name)-1);
		$jobs[$i]['info']['url']   = $location;
		$jobs[$i]['info']['title'] = $name;
	}
	//echo "<pre>".print_r($jobs,true)."</pre>"; return;

	return $jobs;
}

function jobs($locations, $find = 'php', $include = '')
{
	$jobs = array();
	replace_query($locations,$find);
	foreach($locations as $place)
	{
		if(preg_match("/({$include})/", $place['url']))
		{
			$list = getJobs($place);
			$jobs = array_merge($jobs,$list);
		}
	}
	$new_list = array();
	foreach($jobs as $job)
	{
		$date = $job['info']['date'];
		unset($job['info']['date']);
		$uniqu_group_hash = strtotime($date." ". date('Y'));
		$new_list[$uniqu_group_hash]['date'] = $date;
		$new_list[$uniqu_group_hash]['records'][] = $job;
	}
	function mySort($a,$b)
	{
		if($a > $b)
			return 1;
		else
			return -1;
	}
	uksort($new_list, 'mySort');
	return array_reverse($new_list);
}

if(isset($_POST['s']) && strlen($_POST['s']))
{
	$include = $_POST['include'];
	$include = implode('|', $include);
	$include = str_replace('.', '\\.', $include);
	$jobs = jobs($locations,$_POST['s'],$include);
	$json_output = json_encode($jobs);
//	echo '<!--'.memory_get_usage().' -->';
	echo $json_output;

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
				<?php echo implode("\n\t", $regions); ?>
				Areas: <br /><?php echo implode("\n\t", $areas); ?>
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