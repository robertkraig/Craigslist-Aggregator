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

// OR!

$input = $_GET['input']?$_GET['input']:'';

$words  = array(
    'apple', 'pineapple', 'banana', 'orange',
    'radish', 'carrot', 'pea', 'bean', 'potato'
);

$similarity = array();

foreach ($words as $key=>$word)
    $similarity[] = array('similarity'=>levenshtein($input, $word),'word'=>$word);

uasort($similarity,function($a,$b) { if ($a['similarity'] === $b['similarity']) return 0; return ($a['similarity']<$b['similarity'])?-1:1; });
$best_fit = array_shift($similarity);

echo (($best_fit['similarity'] === 0) ? "Exact Match: " : "Most Similar: ") . $best_fit['word']."\n";
