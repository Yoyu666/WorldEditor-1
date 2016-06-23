<?php

$opts = getopt('', ['in:', 'out:']);
$subject = $opts['in'] ?? 'in/MOD.js';
$output = $opts['out'] ?? 'out/plugin.php';

@mkdir(dirname($output), 0777, true);

($handle = fopen($subject, 'r')) || exit('Error. You are have\'nt File open permission ');
$count = 0;//Line Count.
$complete = <<<EOM
<?php

class Edit{


EOM;

$nextline = function($handle, &$count){//カウント上げて次の行にすすめる
	$count++;
	return fgets($handle);
};

while(!feof($handle)){
	$line = $nextline($handle, $count);

	if(preg_match('/^function[ \t]([A-Za-z1-9_]+)[ \t]*\(([A-Za-z1-9_,]*)\){/', trim($line), $match)){
		print_r($match);
		$complete .= "\tpublic function ";
		$argument = '';
		$arguments = explode(',',$match[2]);
		if($match[2] === ""){//引数がない場合
			$complete .= $match[1]."(){\n\t}\n\n";
			continue;
		}

		foreach($arguments as $delimiter => $name){
			$argument .= '$'.$name.', ';
		}
		$argument = rtrim($argument,', ');
		$complete .= $match[1]."(".$argument."){"."\n\t}\n\n";
	}
}

$complete .= "}\n?>";
echo "\n\nWriting $complete to $output";
file_put_contents($output, $complete);
echo PHP_EOL;