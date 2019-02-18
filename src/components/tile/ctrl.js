angular.module('compTile')
	.component('compTile', {
		templateUrl: 'components/tile/tile.html',
		bindings: {
			locale: '<',
		},
		controllerAs: '$ctrl',
		controller: 'ctrlTile',
	})
	.directive('compTile', ['$timeout', ($timeout) => ({
		link(scope, element, attrs, controller, transcludeFn) {
			const randInt = (max) => Math.floor(Math.random() * Math.floor(max));
			let imgs;
			// Set random background image
			const changeBackground = () => {
				if (imgs.length === 0) {
					// No images for background. Exit
					return;
				}

				if (imgs.length === 1) {
					// Only one image. Set and exit
					element.css({
						'background-image': `url('${$(imgs[0]).attr('src')}')`,
					});
					return;
				}

				if (imgs.length === 2 && !element.is('.expanded') || imgs.length > 2) {
					imgs.push(imgs.shift());
				}
				element.css({
					'background-image': `url('${$(imgs[0]).attr('src')}')`,
				});

				if (imgs.length > 1) {
					$timeout(changeBackground, (randInt(16) + 5) * 1000);
				}
			};
			$timeout(() => {
				imgs = [...element.children('img')].shuffle();
				if (
					imgs.length === 0
					&& (
						typeof scope.locale.map === 'string'
						|| (typeof scope.locale.map === 'object' && typeof scope.locale.map.src === 'string')
					)
				) {
					new MutationObserver((mutationList, observer) => {
						const imgMap = element.find('google-maps > img');
						if (imgMap.length > 0) {
							imgs = [...element.find('google-maps > img')].shuffle();
							observer.disconnect();
							changeBackground();
						}
					}).observe(element[0], {
						childList: true,
						subtree: true,
					});
				}
				if (imgs.length > 0) {
					changeBackground();
				}
				element.on('click', () => {
					element.toggleClass('expanded').siblings('.expanded').removeClass('expanded');
					setTimeout(() => {
						$(window).trigger('resize', {
							scrollTo: {
								element,
								block: element[0].classList.contains('expanded') && window.innerHeight - 20 < element[0].offsetHeight ? 'start' : 'center',
							},
						});
					}, 1000);
				});
			}, 0);
		},
	})])
	.controller('ctrlTile', ['$document', '$scope', function($document, $scope) {
		this.$onInit = () => {
			if (this.locale && this.locale.name.includes('Beijing')) {
				console.log('Sam, $ctrl:', this);
			}
		};
	}]);

if (!Array.prototype.shuffle) {
	Array.prototype.shuffle = function shuffleArray() {
		for (let i = this.length - 1; i > 0; i--) {
			const j = Math.floor(Math.random() * (i + 1));
			[this[i], this[j]] = [this[j], this[i]];
		}
		return this;
	}
}
