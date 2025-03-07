<?php

	error_reporting(E_ALL & ~E_NOTICE);

	$file = $argv[1];
	$id = $argv[2];
	system('cat ' . $file . ' | grep ' . $id . ' > out.tmp');
	$handle = fopen('out.tmp',"r");
	while ($line = fgets($handle)) {
		$line = trim($line);
		$ids = explode(" " . $id, $line);
		for($i=1;$i<count($ids);$i++) {
			$oid = $id . substr($ids[$i],0,strpos($ids[$i]," "));
			$oid = str_replace('"','',$oid);
			$done[$oid] = true;
			$oids[] = $oid;
		}
	}
	fclose($handle);
	unlink('out.tmp');
	$ids = $oids;

	$string = file_get_contents("course/components.json");
	$data = json_decode($string,true);
	for ($i=0;$i<count($data);$i++) {
		$node = $data[$i];
		if ($node["_id"] && substr($node["_id"],0,strlen($id)) == $id) {
			$parts[$node["_id"]] = $node;
		}
	}
	echo '<!DOCTYPE html>' . "\n";
	echo '<html id="adapt" class="js flexbox flexboxlegacy canvas no-touch rgba hsla multiplebgs backgroundsize borderimage borderradius boxshadow textshadow opacity cssanimations csscolumns cssgradients cssreflections csstransforms csstransforms3d csstransitions fontface generatedcontent video audio applicationcache chrome version-57.0 OS-linux pixel-density-low dir-ltr size-large orientation-landscape" lang="en"><!--<![endif]--><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">' . "\n";

	for ($i=0;$i<count($ids);$i++) {
		$component = $parts[$ids[$i]];
		output($component);
	}

	echo '</head></html>';

function output($component) {
	if (!is_array($component)) {
		return;
	}
	if ($component["_component"] == "text" || $component["_component"] == "media" || $component["_component"] == "media-autoplay" || $component["_component"] == "graphic") {
		outputText($component);
	} elseif (@array_key_exists("_canShowFeedback", $component)) {
		outputText($component);
		outputQuestion($component);
	} elseif ($component["_component"] == "accordion") {
		outputText($component);
		outputAccordion($component);
	} elseif ($component["_component"] == "narrative") {
		outputText($component);
		outputNarrative($component);
	} elseif ($component["_component"] == "matching") {
		outputText($component);
		outputMatching($component);
	} elseif ($component["_component"] == "notify-imagegrid") {
		outputImageGrid($component);
	} elseif ($component) {
		print_r($component["_component"]);
		//print_r($component);
		exit(1);
	}
}

function outputText($component) {
	if (trim($component["body"])) {
		echo "<h2>" . outputTitle(strip_tags($component["title"])) . "</h2>";
		echo "<p>" . trim($component["body"]) . "</p>";
	}
}
function outputImageGrid($component) {
	outputText($component);
	$items = $component["_items"];
	for($i=0;$i<count($items);$i++) {
		echo '<h2>' . outputTitle(strip_tags($items[$i]["_graphic"]["title"])) . '</h2>';
		echo "<p>" . trim(strip_tags($items[$i]["_graphic"]["gridmessage"])) . "</p>";
	}
}
function outputQuestion($component) {
	$items = $component["_items"];
	echo '<ul>';
	for($i=0;$i<count($items);$i++) {
		echo '<li>' . $items[$i]["text"] . '</li>';
	}
	echo '</ul>';
	echo $component["_feedback"]["correct"];
	echo $component["_feedback"]["_incorrect"]["final"];
}
function outputMatching($component) {
	$items = $component["_items"];
	echo '<ul>';
	for($i=0;$i<count($items);$i++) {
		echo '<li>' . $items[$i]["text"] . '</li>';
		$options = $items[$i]["_options"];
		echo '<ul>';
		for ($j=0;$j<count($options);$j++) {
			echo '<li>' . $options[$j]["text"] . '</li>';
		}
		echo '</ul>';
	}
	echo '</ul>';
	echo $component["_feedback"]["correct"];
	echo $component["_feedback"]["_incorrect"]["final"];
}
function outputAccordion($component) {
	$items = $component["_items"];
	for($i=0;$i<count($items);$i++) {
		$item = $items[$i];
		echo "<h3>" . strip_tags($item["title"]) . "</h3>";
		echo "<p>" . $item["body"] . "</p>";
	}
}
function outputNarrative($component) {
	$items = $component["_items"];
	for($i=0;$i<count($items);$i++) {
		$item = $items[$i];
		echo "<h3>" . strip_tags($item["title"]) . "</h3>";
		echo "<p>" . $item["body"] . "</p>";
	}
}


function reorder($data) {
	$order = ['title','body','instruction'];
	for($i=0;$i<count($order);$i++) {
		$path = $order[$i];
		for($j=0;$j<count($data);$j++) {
			if ($data[$j]["path"] == $path) $ret[] = $data[$j];
		}
	}
	for($j=0;$j<count($data);$j++) {
		$path = $data[$j]["path"];
		if (!in_array($path, $order)) {
			$ret[] = $data[$j];
		}
	}
	return $ret;
}

function filter($data) {
	for($i=0;$i<count($data);$i++) {
		$current = $data[$i];
		if (trim($current["value"]) == "") {
			continue;
		}
		if (trim($current["value"]) == "Component title") {
			continue;
		}
		if (trim($current["value"]) == "Article title") {
			continue;
		}
		if (trim($current["value"]) == "Page title") {
			continue;
		}
		$ret[] = $data[$i];
	}
	return $ret;
}

function outputTitle($value) {
	if (trim($value) == "") {
		return;
	}
	if (trim($value) == "Component title") {
		return;
	}
	if (trim($value) == "Article title") {
		return;
	}
	if (trim($value) == "Page title") {
		return;
	}
	return $value;
	
}

?>
