angular.module('compTile')
.component('compTile', {
	templateUrl: 'components/tile/tile.html',
	bindings: {
		locale: '<',
	},
	controller: 'ctrlTile',
	/*
controller($scope) {
	console.log('Sam, $ctrl:', this);
	const loadImage = () => {
		console.log('Sam, this:', this);
		const $a = $(this);
		const $i = $($a.children('load-img')[0]);
		let img = $i.clone().wrap('<div>').parent().html();
		console.log('Sam, html:', img);
		if (!img) return;
		img = $(img.replace('load-img', 'img'));
		img.appendTo($a);
		img.on('load', () => {
			$i.remove();
			// this.loadImage();
		});
	};
	const changeImage = () => {
		const $a = $(this);
		const $i = $a.children('img');
		const n = $a.is('.expanded') ? Math.min(2, $i.length) : 1;
		let i = Number.parseInt(this.bg_i, 10) + 1 || 0;
		let fadeTime = 0;
		const bg = [];
		let img;
		clearTimeout(this.bgto);
		if (!$i.length) return;
		if ($a.find('.alt').length) {
			fadeTime = 300;
			$a.find('.alt').fadeOut(300, () => {
				$(this).remove();
			});
		}
		if (i >= $i.length) i -= $i.length;
		for (let j=0; j<n; j++) {
			let k = i + j;
			while (k >= $i.length) k -= $i.length;
			img = $($i[k]);
			if (img.attr('alt')) {
				$('<div class="alt">').text(img.attr('alt')).appendTo($a).delay(fadeTime).fadeIn(300);
			}
			bg.push('url(' + img.attr('src') + ')');
		}
		$a.css({
			backgroundImage: bg.join(','),
		});
		this.bg_i = i;
		this.bgto = setTimeout(changeImage, Math.randInt(5, 20) * 1000);
	};
	$(document).ready(function() {
		$('comp-tile').each(function() {
	// Instantiate Tile Objects
			loadImage();
			changeImage();
		}).on('click', function(e) {
			const $t = $(this);
			if ($(e.target).is('a[href]')) {;
	// Clicked a link, don't confuse user with expanding tile
				return true;
			}
// Expand/Collapse Tiles
			if (!$t.is('.expanded')) {
	// Adjust Background Image
				this.bg_i = this.bg_i - 1;
				if (this.bg_i < 0) this.bg_i = $t.children('img').length - 1;
			}
			$t.toggleClass('expanded').siblings('comp-tile.expanded').removeClass('expanded');
			changeImage();
			$(window).trigger('resize', {
				eleScrollTo: $t,
			});
		});
// Close Tiles on Click
		$(document).on('click', function(e) {
			if (!$(e.target).closest('comp-tile').length) {
				const $t = $('comp-tile.expanded');
				$('comp-tile').removeClass('expanded');
				$(window).trigger('resize', {
					eleScrollTo: $t,
				});
			}
		});
	});
},
		/**/
})
	.directive('compTile', ['$timeout', ($timeout) => ({
		link(scope, element, attrs, controller, transcludeFn) {
			const randInt = (max) => Math.floor(Math.random() * Math.floor(max));
			if (scope.locale && scope.locale.name.includes('Beijing')) {
				$timeout(() => {
					const imgs = element.find('img');

					// Set random background image
					element.css({
						'background-image': `url('${$(imgs[randInt(imgs.length)]).attr('src')}')`,
					});
				}, 0);
			}
		},
	})])
	.controller('ctrlTile', ['$document', '$scope', function($document, $scope) {
		this.$onInit = () => {
			if (this.locale && this.locale.name.includes('Beijing')) {
				console.log('Sam, $ctrl:', this);
			}
		};
	}]);
