<?php
require_once("../site.php");
$blog = new BlogSite();
$blog->javascript = 'gtfs';

$options = array(
	'route_type' => array(
		0 => 'Tram, Streetcar, Light rail',
		1 => 'Subway, Metro',
		2 => 'Rail, Commuter rail, Heavy rail',
		3 => 'Bus',
		4 => 'Ferry',
		5 => 'Cable car (only in San Francisco)',
		6 => 'Gondola, Suspended cable car',
		7 => 'Funicular',
	),
	'direction_id' => array(
		0 => 'Outbound',
		1 => 'Inbound',
	),
	'timepoint' => array(
		0 => 'Approximate times given',
		1 => 'Exact times given',
	),
	'location_type' => array(
		0 => 'Stop',
		1 => 'Station',
	),
);

?>
<h1>GTFS Edit</h1>
<?php

$dir = trim($_SERVER['REQUEST_URI'], '/');
$dir = explode('/', $dir);
chdir("gtfs/{$dir[0]}/{$dir[1]}");
$files = glob("*.txt");
foreach ($files as $file) {
	// Set Header
	$name = explode('.', $file);
	$name = explode('_', $name[0]);
	foreach ($name as &$word) {
		$word = strtoupper($word[0]) . strtolower(substr($word, 1));
	}
	$name = implode(' ', $name);
	echo "<section class=\"wide\">";
	echo "<h2>$name <small>{$file}</small></h2><table>";
	// Show File Contents
	$file = fopen($file, 'r');
	$i = 0;
	while ($line = fgetcsv($file)) {
		if ($i === 0) {
			$head = array();
			foreach ($line as $num => $td) {
				$head[$num] = $td;
				$head[$td] = $num;
			}
		}
		echo "<tr>";
		$d = $i++ ? 'd' : 'h';
		foreach ($line as $num => $td) {
			$class = array();
			$type = 'text';
			$size = 20;
			if ($i != 1) {
				if (substr($head[$num], -4) == '_url') {
					$type = 'url';
				} else if (substr($head[$num], -3) == 'day') {
					$type = 'checkbox';
					if ($td == '1') {
						$type .= '" checked="';
					}
					$td = 1;
				} else if (substr($head[$num], -6) == '_color') {
					$class[] = "right";
					$type = 'color';
					$td = "#$td";
				} else if (in_array($head[$num], array_keys($options))) {
					$type = 'select';
				} else if ($head[$num] == 'route_short_name') {
					$class[] = "right";
				} else if (substr($head[$num], -9) == '_sequence') {
					$class[] = "right";
					$type = 'number';
					$size = 2;
				}
				$class = implode(' ', $class);
				switch ($type) {
				case 'select':
					if (!empty($options[$head[$num]])) {
						$selected = $td;
						$td = "<select>";
						foreach ($options[$head[$num]] as $val => $txt) {
							$td .= "<option value=\"$val\"";
							if ($selected == $val) $td .= ' selected';
							$td .= ">$txt</option>";
						}
						$td .= "</select>";
						break;
					}
				default:
					$td = "<input type=\"$type\" size=\"$size\" value=\"{$td}\"/>";
				}
			}
			echo "<t$d class=\"$class\">{$td}</t$d>";
		}
		echo "</tr>";
	}
	fclose($file);
	echo "</table></section>";
}
?>
