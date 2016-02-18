<?php
if ($delay_load) echo "<template id=\"{$this->fn}\">";
switch ($tag) {
case 'figure':
	echo "<figure>";
	break;
}
$this->html();
echo "</$tag>";
if ($delay_load) echo '</template>';
?>
