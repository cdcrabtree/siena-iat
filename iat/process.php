<?php

$data = json_decode($_POST['matrix']);

$str = "";

foreach ($data as $key => $block) {
	for ($i=0; $i < count($block[0]); $i++) {
		$item = $block[0][$i];
		$responseTime = $block[1][$i];
		$error = $block[2][$i];
		$category = $block[3][$i];

		$str .= $item . " " . $responseTime . " " . $error . " " . $category . "<br>";
	}
	$str .= "<br>";
}
echo $str;
exit;
?>