<?php
if ($delay_load) echo "<template id=\"{$this->fn}\">";
switch ($tag) {
case 'figure':
	echo "<figure itemscope itemtype=\"http://schema.org/Photograph\">";
	break;
}
$this->html();
echo "</$tag>";
if ($delay_load) echo '</template>';
?>
