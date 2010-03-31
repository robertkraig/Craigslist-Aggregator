<?php

$input = $_GET['input']?$_GET['input']:'';

$words  = array(
	'apple', 'pineapple', 'banana', 'orange',
	'radish', 'carrot', 'pea', 'bean', 'potato'
);

$similarity = $sort = array();

foreach ($words as $key=>$word)
	$similarity[] = array('similarity'=>levenshtein($input, $word),'word'=>$word);

foreach($similarity as $key=>$test)
	$sort[$key] = $test['similarity'];

asort($sort,SORT_NUMERIC);

$best_key = key($sort);

if($similarity[$best_key]['similarity'] == 0)
	echo "Exact Match: ".$similarity[$best_key]['word']."\n";
else
	echo "Most Similar Word: ".$similarity[$best_key]['word']."\n";

?>
