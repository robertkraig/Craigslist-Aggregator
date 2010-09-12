<?php

/**
 * @author Robert S Kraig
 * @version 0.7
 *
 */
class CraigListScraper {

	static $cl_info = null;

	private $xml = null;
	private $locations = null;
	private $areas = null;
	private $regions = null;
	private $record_list = null;

	static function poop()
	{
		$debug = '';
		foreach(func_get_args() as $arg)
		{
			$debug.= is_string($arg)?$arg:print_r($arg,true);
			$debug.="\n".'-------------------------'."\n";
		}
		error_log($debug);
	}

	function  __construct($fileLocation = null)
	{
		if(is_null($fileLocation))
			throw new Exception('Must Enter your XML Configuration file.');

		if(!file_exists($fileLocation))
			throw new Exception('Your XML Configuration must exist');

		$xmlstr = file_get_contents($fileLocation);
		$this->xml = simplexml_load_string($xmlstr, 'SimpleXMLElement', LIBXML_NOCDATA);

		$this->init();
	}

	public function getInfo()
	{
		if(is_null(self::$cl_info))
		{
			self::$cl_info = $this->xml->xpath('/clrepo/info');
		}
		return self::$cl_info[0];
	}

	public function getFields()
	{
		$fields = $this->xml->xpath('/clrepo/info/fields/argField');
		$field_array = array();
		foreach($fields as $field)
		{
			$field_array[] = get_object_vars($field);
		}
		return $field_array;
	}

	public function buildAreas()
	{
		$this->locations = array();
		$this->areas = array();
		foreach($this->xml->xpath('/clrepo/locations/location') as $location)
		{
			$loc = get_object_vars($location);
			$this->locations[] = $loc;
			extract($loc);
			$this->areas[$partial] = ''.
				'<label for="'.$partial.'">'.
				'<input class="region '.$type.'" type="checkbox" id="'.$partial.'" name="include[]" value="'.$partial.'" />'.$name.', '.$state.
				'</label>';
			unset($name);
			unset($url);
			unset($partial);
			unset($type);
		}
	}

	public function buildRegions()
	{
		$this->regions = array();
		foreach($this->xml->xpath('/clrepo/regions/region') as $region)
		{
			$reg = get_object_vars($region);
			extract($reg);
			$this->regions[] = '<label for="'.$type.'"><input class="regions" type="checkbox" id="'.$type.'" name="region[]" value="'.$type.'" />'.$name.'</label>';
			unset($type);
			unset($name);
		}
	}

	private function init()
	{
		$this->buildAreas();
		$this->buildRegions();
	}

	public function getAreas()
	{
		if(is_null($this->areas))
			throw new Exception('init() has not been run');

		return $this->areas;
	}

	public function getLocations()
	{
		if(is_null($this->locations))
			throw new Exception('init() has not been run');

		return $this->locations;
	}

	public function getRegions()
	{
		if(is_null($this->regions))
			throw new Exception('init() has not been run');

		return $this->regions;
	}

	/**
	 * Macro which gets called to loop though locations structure to replace queried search term
	 * @param array $array holds url locations for xml locations
	 * @param string $find inserts the search term being looked up
	 * @param string $replace_tag the token which is to be replaced in the xml document
	 */
	private function replace_query(&$array)
	{
		$fields = $this->getFields();
		$tmp_arr = array();
		foreach($fields as $field)
		{
			if(array_key_exists($field['argName'], $_POST))
			{
				$tmp_arr[$field['argName']] = $_POST[$field['argName']];
			}
		}

		$args = http_build_query($tmp_arr);
		foreach($array as $key=>$val)
		{
			$array[$key]['url'].='&amp;'.$args;
		}
//		$this->poop($array);
	}

	private function stripData(array $str)
	{
		foreach($str as $key=>$seg)
			if(preg_match('/\s+/', $seg) || empty($seg))
				unset($str[$key]);
		return implode(' ',$str);
	}

	/**
	 * Macro which takes a given url location for craigslist search and scrapes useful content off the page
	 * @param array $location
	 * @return array
	 */
	private static function getRecords($location)
	{
		$file = @file_get_contents($location['url']);
		if(!$file) return array();

		$dom = new DOMDocument();
		@$dom->loadHTML($file);

		$xpath = new DOMXPath($dom);
		$p_tags = $xpath->evaluate("/html/body//blockquote//p");
		$a_tags = $xpath->evaluate("/html/body//blockquote//p/a");

		$search_items = array();
		for ($i = 0; $i < $p_tags->length; $i++) {
			$title = $p_tags->item($i);
			$name = $title->textContent;
			$name = str_replace('<<', ' - ', $name);
			$fields = explode('-', $name);
			$search_items[$i]['location'] = $location['partial'];
			$date = self::stripData(explode(' ',$fields[0]));
			$search_items[$i]['info'] = array(
				'date'  => $date,
				'field' => $fields[count($fields)-1],
				'from'  => $location['partial']
			);
		}
		for ($i = 0; $i < $a_tags->length; $i++) {
			$link = $a_tags->item($i);
			$location = $link->getAttribute('href');
			$name = $link->textContent;
			//$name = substr($name, 0, strlen($name)-1);
			$search_items[$i]['info']['url']   = $location;
			$search_items[$i]['info']['title'] = $name;
		}
		return $search_items;
	}

	public function initialize()
	{

		$include = implode('|', $_POST['include']);
		$include = str_replace('.', '\\.', $include);

		if(!count($this->locations))
			throw new Exception('Something is wrong');

		$search_items = array();
		$this->replace_query($this->locations);
		foreach($this->locations as $place)
		{
			if(preg_match("/({$include})/", $place['url']))
			{
				$list = self::getRecords($place);
				$search_items = array_merge($search_items,$list);
			}
		}
		$new_list = array();
		foreach($search_items as $item)
		{
			$date = $item['info']['date'];
			unset($item['info']['date']);
			$uniqu_group_hash = strtotime($date." ". date('Y'));
			$new_list[$uniqu_group_hash]['date'] = $date;
			$new_list[$uniqu_group_hash]['records'][] = $item;
		}
		function mySort($a,$b)
		{
			if($a > $b)
				return 1;
			else
				return -1;
		}
		uksort($new_list, 'mySort');
		$this->record_list = array_reverse($new_list);
	}

	function  __toString()
	{
		if(is_null($this->record_list))
			throw new Exception('Something is wrong');

		return json_encode($this->record_list);
	}
}

?>
