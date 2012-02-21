<script>
	window.page_info = <?php echo json_encode($cl_scraper->getInfo()); ?>;
	window.region_list = <?php echo json_encode($cl_scraper->getRegions()); ?>;
	window.area_list = <?php echo json_encode($cl_scraper->getAreas()); ?>;
	window.form_fields = <?php echo json_encode($cl_scraper->getFields()); ?>;
	window.PHP_SELF = "/";
</script>
<form action="/" method="post" id="find_items">
	<input type="hidden" name="site" id="site" value="<?php echo $cl_scraper->getInfo()->pageType; ?>" />
	<div id="change_size_container">
		<div style="font-size: 20px;"><?php echo $cl_scraper->getInfo()->pagetitle; ?></div>
		<?php
		foreach ($cl_scraper->getFields() as $field)
			if (preg_match('/(string|int)/', $field['argType']))
				include "templates/fieldtypes/int_string.php";
			elseif ($field['argType'] == 'radio')
				include "templates/fieldtypes/radio.php";
			elseif ($field['argType'] == 'checkbox')
				include "templates/fieldtypes/checkbox.php";
		?>
		<cite><?php echo $cl_scraper->getInfo()->pagesearchexample; ?></cite>
		<div id="locations_container">
			Region:&nbsp;&nbsp;<a id="region_list_disp">open</a>
			<div id="region_list"></div><br />
			Areas:&nbsp;&nbsp;<a id="areas_list_disp">open</a>
			<div id="areas_list"></div>
		</div>
		<a id="search_btn">Search</a>
		<input type="submit" style="display:none;" />
		<div><a id="donate" href="http://www.compubomb.net/pages/payme" target="_blank">Donate To Author</a></div>
		<img alt="loader" id="loader" style="display:none; position: absolute; bottom: 0; right: 0; margin:10px; margin-bottom: 35px;" src="/img/loading.gif" />
	</div>
</form>
<div id="content-container">
	<div id="buttons">
		<a id="toggle_disp" class="button">Close All</a>
		<a id="show_search" class="button">Show Search</a>
		<span id="open_windows"></span>
		<div style="clear: left; height: 0px;"></div>
	</div>
	<div style="display:none;" id="link_content"></div>
	<div style="display:none;" id="content"></div>
</div>