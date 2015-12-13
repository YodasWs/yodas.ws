<div id="worldmap"></div>
<script>
(function(s,a,m,j,x){
	x=s.getElementsByTagName(a)[0];
	m.forEach(function(i){
		j=s.createElement(a),
		j.async=1;
		j.src=m[i];
		x.parentNode.insertBefore(j,x)
	})
})(document,'script',['http://maps.google.com/maps/api/js?v=3&sensor=false&region=US'])
</script>
