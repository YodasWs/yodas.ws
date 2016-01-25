<?php
require_once("components/component.php");
require_once("components/4sq/secret.php");
class Foursquare implements Component {

	private $venue_url = 'https://api.foursquare.com/v2/venues/';
	private $image = 'http://yodas.ws/api/foursquare.png';
	private $v_query = 'v=20160124&m=foursquare&';

	private function getVenue($venue) {
		global $fsq;
		$venue = @file_get_contents("{$this->venue_url}{$venue}?{$this->v_query}client_id={$fsq['client_id']}&client_secret={$fsq['secret']}");
		if (empty($venue)) return false;
		$venue = json_decode($venue, true);
		if (empty($venue['response']['venue']['location'])) return false;
		return $venue['response']['venue'];
	}

	// Add Map of Foursquare Venue for PicBanner
	public function venueMap($venue, $zoom=11) {
		$venue = $this->getVenue($venue)
		$url = "http://maps.google.com/maps/api/staticmap?";
		$url .= "markers={$venue['location']['lat']},{$venue['location']['lng']}";
		$url .= "&size=150x150&sensor=false&format=png32&zoom=$zoom";
		return <<<venueMapHTML
<div class="vcard">
<img src="{$url}" alt="{$venue['name']}"/>
<span><a class="fn url org" href="{$venue['canonicalUrl']}">{$venue['name']}</a></span>
<span class="geo" style="display: none"><span class="latitude">{$venue['location']['lat']}</span><span class="longitude">{$venue['location']['lng']}</span></span>
<a href="https://foursquare.com/intent/venue.html" class="fourSq-widget" data-variant="wide">Save to foursquare</a>
</div>
venueMapHTML;
	}
}
