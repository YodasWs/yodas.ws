</main>
<footer itemscope itemtype="http://schema.org/WPFooter">
	<small>&copy;<span itemprop="copyrightYear">2016</span> Samuel B Grundman</small>
</footer>
<?php
if (!empty($this->gmaps)) {
	echo "<script>$('#google-maps')";
	foreach ($this->gmaps as $class) {
		if (is_string($class)) {
			echo ".addClass('$class')";
		}
	}
	echo "</script>";
}
if (file_exists("google_analytics.php")) {
	include_once("google_analytics.php");
}
?></body></html>
