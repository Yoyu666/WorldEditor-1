<?php

$opts = getopt('', ['in:', 'out:']);
$subject = $opts['in'] ?? 'in/MOD.js';
$output = $opts['out'] ?? 'out/plugin.php';

@mkdir(dirname($output), 0777, true);

($handle = fopen($subject, 'r')) || exit('Error. You are have\'nt File open permission ');
$count = 0;//Line Count.
$complete = '';

$nextline = function($handle, &$count){//カウント上げて次の行にすすめる
	$count++;
	return fgets($handle);
};

while(!feof($handle)){
	$line = $nextline($handle, $count);
	echo $line;
	if(preg_match('/^function[ \t][A-Za-z1-9_]+([ \t]*)\([A-Za-z1-9_,]*\){/', $line, $match)){
		echo "\n$match[0]";
		$complete .= $match[0]."\n}\n";
	}
}

echo "\n\nWriting $complete to $output";
file_put_contents($output, $complete);
echo PHP_EOL;