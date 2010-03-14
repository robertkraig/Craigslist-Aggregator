<?php
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
	$areas[$partial] = '<label for="'.$partial.'"><input class="region '.$type.'" type="checkbox" id="'.$partial.'" name="include[]" value="'.$partial.'" />'.$name.'</label>';
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
	$file = file_get_contents($location['url']);

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
		$jobs[$i]['date'] = trim($fields[0]);
		$jobs[$i]['field'] = $fields[count($fields)-1];
		$jobs[$i]['from'] = $location['partial'];
	}
	for ($i = 0; $i < $a_tags->length; $i++) {
		$link = $a_tags->item($i);
		$location = $link->getAttribute('href');
		$name = $link->textContent;
		$name = substr($name, 0, strlen($name)-1);
		$jobs[$i]['url']   = $location;
		$jobs[$i]['title'] = $name;
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
		$date = $job['date'];
		unset($job['date']);
		$new_list[$date][] = $job;
	}

	return $new_list;
}

if(isset($_POST['s']) && strlen($_POST['s'])):
	$include = $_POST['include'];
	$include = implode('|', $include);
	$include = str_replace('.', '\\.', $include);
	$jobs = jobs($locations,$_POST['s'],$include);
	//echo "<pre>".print_r($jobs,true)."</pre>"; return;
	if(count($jobs)):
			$tmp_key = false;
		foreach($jobs as $key=>$job):
			if(preg_match('/[ ]/', $key)):
				if($tmp_key) echo '</div>';
				echo "<h1>{$key}</h1>";
				echo '<div class="date">';
				$tmp_key = true;
			endif;
	if(count($job)):
		$test_val = '';
		foreach($job as $_key=>$_job):
			$link = strstr($_job['url'], 'http://')?$_job['url']:'http://'.$_job['from'].$_job['url'];
			$not_near = strstr($_job['url'], 'http://')?'<span class="near"></span>':'';
			if($test_val != $_job['from']):
				$test_val =  $_job['from'];
				$from = explode('.',$_job['from']);
				if($_key):?>
</ul>
<?php
				endif;
?>
<h2><?=$from[0];?></h2>
<ul>
<?php endif; ?>
	<li><a href="<?=$link;?>" target="_blank"><span><?=$_job['title'];?> : <span style="color:black;"><?=$_job['field'];?></span></span></a><?=$not_near;?></li>
<?php
		endforeach;
	endif;
?>
</ul>
<?php
		endforeach;
	endif;?>
<script type="text/javascript">
$(function(){
//		$('#content div.date').hide();
//		$('#content table').hide();
	$('#toggle_disp a').live('click',function(){
		if($(this).attr('rel') == 'open')
		{
			$(this).text('Expand All');
			$('#content div.date').hide();
			$('#content div.date ul').css('display','none');
			$(this).attr('rel', 'close');
		}else{
			$('#content div.date').show();
			$('#content div.date ul').css('display','block');
			$(this).attr('rel', 'open');
			$(this).text('Close All');
		}
		return false;
	});
	$('span.near').each(function(){
		var href = $(this).prev('a').attr('href');
		href = href.replace('http://','');
		var name = href.split('.')[0];
		$(this).text(name);
	});
});
</script>
<?php else: ?>
<title>Job Search via Craigslist</title>
<script type="text/javascript" src="http://www.google.com/jsapi"></script>
<script type="text/javascript">
  // You may specify partial version numbers, such as "1" or "1.3",
  //  with the same result. Doing so will automatically load the
  //  latest version matching that partial revision pattern
  //  (i.e. both 1 and 1.3 would load 1.3.2 today).
  google.load("jquery", "1.3.2");

  google.setOnLoadCallback(function() {
    // Place init code here instead of $(document).ready()
  });
</script>
<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
try {
var pageTracker = _gat._getTracker("UA-12896175-1");
pageTracker._trackPageview();
} catch(err) {}</script>
<style type="text/css">
	body {background-color: #F4FDFF; margin: 0px; padding: 0px;}
	* {font-family:Geneva,Arial,Helvetica,serif,sans-serif; outline: none;}
	h1 {font-size: 24px; margin:0px; padding:0px; cursor:pointer;}
	h2 {font-size: 18px; margin:0px; padding:0px; text-transform:uppercase;cursor:pointer; margin-left:20px; width: 200px;}
	h1:hover,h2:hover {color:red;}
	a {text-decoration:none; color:red;}
	a:visited{color:black;text-decoration: line-through;}
	a span {border-bottom:dashed 1px gray;}
	div.date {}
	ul {margin-bottom:10px; margin-left:25px;}
	ul li {list-style-type:none; margin:0px; padding:0px;}
	ul li,ul li a { font-size: 16px; line-height:25px;}
	form label {display:block;}
	form input,
	form button {margin:5px; padding:2px; color:red; border:solid 1px gray; background-color:white;}
	span.near {margin-left:10px; text-transform:uppercase; font-weight:bold;}
	form button, form input[type="text"]{
		-moz-box-shadow: 0px 0px 2px #000;
		-webkit-box-shadow: 0px 0px 2px #000;
	}
	cite {display:block;font-size: 10px; font-style: italic;margin:5px; margin-top: 0px; padding:2px;}
	a#search_btn {
		margin:5px;
		padding:2px;
		color:red;
		border:solid 1px gray;
		background-color:white;
		-moz-box-shadow: 0px 0px 2px #000;
		-webkit-box-shadow: 0px 0px 2px #000;
		text-decoration: none;
		padding:5px;
		display:inline-block;
	}
	form button:focus,
	form input[type="text"]:focus,
	form input[type="password"]:focus,
	form input[type="file"]:focus,
	form select:focus,
	form textarea:focus,
	a#search_btn:focus,
	a#search_btn:hover{
		background-color: #FFF;
		border: 1px solid #508FCF;
		-moz-box-shadow: 0px 0px 2px #999;
		-webkit-box-shadow: 0px 0px 2px #999;
	}
	#content {overflow-y: scroll; width: 100%;}
	#find_jobs {float:left; position: relative; width: 250px; background-color: #fff; border: solid 1px #999; padding:10px; border-left: none; border-top: none;}

	a#donate {text-decoration: none; display:block; border-top: solid 1px #999; margin:-10px; margin-top: 0px; line-height: 30px; text-align: center;}
	a#donate:hover{background-color: #F4FDFF;}
	a#donate:focus{color: red;}
</style>
<form action="" method="post" id="find_jobs">
	<div style="font-size: 24px;">Find yourself a Job</div>
	<cite>I wrote this App because I found myself going back and forth between different areas of Craigslist so I could look up jobs. This is an aggregate of all the sites that you select in <strong>Areas</strong></cite>
	<input type="text" id="search_term" name="s" value="" />
	<cite>Ex: php, C#, .NET, ASP.NET, Linux</cite>
	Region:<br />
	<?php echo implode("\n\t",$regions); ?>
	Areas: <br />
	<?php echo implode("\n\t",$areas); ?>
	<a href="#submit" id="search_btn">Search</a>
	<div><a id="donate" href="http://www.compubomb.net/pages/payme" target="_blank">Donate To Author</a></div>
	<img alt="loader" id="loader" style="display:none; position: absolute; bottom: 0; right: 0; margin:10px; margin-bottom: 35px;" src="/img/loading.gif" />
</form>
<script type="text/javascript">
$(function(){

	$('#search_btn').live('click',function(){
		$('#find_jobs').submit();
		return false;
	});

	$('input[type="checkbox"].regions').live('click',function(){
		var region = $(this).val();
		var str = 'input[name="include[]"].'+region;
		var $regions = $(str);
		if($(this).is(':checked'))
		{
			$regions.attr('checked','checked');
		}
		else
		{
			$regions.removeAttr('checked');
		}
	});
	$('#find_jobs').submit(function(){
		if(!$('input[name="include[]"]:checked').length)
		{
			$('input[name="include[]"].region').attr('checked','checked');
		}
		if($('#search_term').val() == "")
		{
			$('#search_term')
				.css('-moz-box-shadow','0px 0px 2px red')
				.css('-webkit-box-shadow','0px 0px 2px red')
				.css('border','solid 1px red')
			return false;
		}
		$('#loader').show();
		$('#content').show().html('Loading...');
		$('#search_btn').val('searching');
		$('#toggle_disp').hide();
		$.ajax({
			type: "POST",
			url: "<?php echo $_SERVER['PHP_SELF']; ?>",
			data: $('#find_jobs').serialize(),
			success: function(data){
				$('#content').html(data);
				$('#toggle_disp').show();
				$('#search_btn').val('Search Craigslist');
				$('#loader').hide();
			}
		});
		return false;
	});
	$('#content h1').live('click',function(){
		$(this).next('div.date').toggle();
	});
	$('#content h2').live('click',function(){
		$(this).next('ul').toggle();
	});
	content_size();
	$(window).resize(content_size);
});

function content_size()
{
	$('#content')
		.css('height',$(window).height()-50)
		.css('width',$(window).width() - $('#find_jobs').width() - 70);
}
</script>
<div style="float:left; padding: 10px;">
	<div style="display:none;" id="toggle_disp">
		<a style="display:inline-block;" href="#" rel="open">Close All</a>
	</div>
	<div style="display:none;" id="content"></div>
</div>
<?php endif;?>