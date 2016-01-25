<div class="vcard">
<img src="<?=$url?>" alt="<?=$venue['name']?>"/>
<span><a class="fn url org" href="<?=$venue['canonicalUrl']?>"><?=$venue['name']?></a></span>
<span class="geo" style="display: none"><span class="latitude"><?=$venue['location']['lat']?></span><span class="longitude"><?=$venue['location']['lng']?></span></span>
<a href="https://foursquare.com/intent/venue.html" class="fourSq-widget" data-variant="wide">Save to foursquare</a>
</div>
