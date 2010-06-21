<?php

/**
 * @author Robert S Kraig
 * @version 0.7
 *
 */
class CraigListScraper {

	static $cl_info = null;

	private $xml_url_token = '{find}';

	private $post_var_array_name = 'include';

	private $xml = null;
	private $locations = null;
	private $areas = null;
	private $regions = null;
	private $record_list = null;

	public function setXMLUrlToken($token)
	{
		$this->xml_url_token = "{{$token}}";
	}

	public function setPostVarArrayName($var)
	{
		$this->post_var_array_name = $var;
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
				'<input class="region '.$type.'" type="checkbox" id="'.$partial.'" name="'.$this->post_var_array_name.'[]" value="'.$partial.'" />'.$name.', '.$state.
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
	private static function replace_query(&$array,$find,$replace_tag='{find}')
	{
		$find = urlencode($find);
		foreach($array as $key=>$val)
		{
			$array[$key]['url'] = str_replace($replace_tag, $find, $array[$key]['url']);
		}
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
			$search_items[$i]['info'] = array(
				'date' => trim($fields[0]),
				'field' =>  $fields[count($fields)-1],
				'from' => $location['partial']
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

	public function initialize(array $include, $find = 'php')
	{

		$include = implode('|', $include);
		$include = str_replace('.', '\\.', $include);

		if(!count($this->locations))
			throw new Exception('Something is wrong');

		$search_items = array();
		self::replace_query($this->locations,$find);
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
