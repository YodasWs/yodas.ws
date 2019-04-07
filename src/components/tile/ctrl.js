angular.module('compTile')
	.component('compTile', {
		templateUrl: 'components/tile/tile.html',
		bindings: {
			locale: '<',
		},
		controllerAs: '$ctrl',
		controller: 'ctrlTile',
	})
	.controller('ctrlTile', ['$document', '$scope', function($document, $scope) {
		this.$onInit = () => {
			if (this.locale && this.locale.name.includes('Beijing')) {
				console.log('Sam, $ctrl:', this);
			}
		};
	}])
	.directive('compTile', [() => ({
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
					// Only one image in tile. Set and exit
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
					setTimeout(changeBackground, (randInt(16) + 5) * 1000);
				}
			};
			setTimeout(() => {
				imgs = [...element.children('img')].shuffle();

				// Add Map to Background Rotation if Tile has few Images
				if (
					imgs.length <= 1
					&& (
						typeof scope.locale.map === 'object' && typeof scope.locale.map.src === 'string'
						|| typeof scope.locale.map === 'string'
					)
				) {
					new MutationObserver((mutationList, observer) => {
						if (element.find('google-maps > img').length > 0) {
							imgs = [...element.find('google-maps > img')]
								.concat([...element.children('img')])
								.shuffle();
							setTimeout(
								changeBackground,
								imgs.length === 1 ? 0 : (randInt(5) + 5) * 1000
							);
							observer.disconnect();
						}
					}).observe(element[0], {
						childList: true,
						subtree: true,
					});
				}
				if (imgs.length > 0) {
					changeBackground();
				}
				const toggleExpand = () => {
					element.toggleClass('expanded').siblings('.expanded').removeClass('expanded');
					setTimeout(() => {
						$(window).trigger('resize', {
							scrollTo: {
								element,
								block: element[0].classList.contains('expanded') && window.innerHeight - 20 < element[0].offsetHeight ? 'start' : 'center',
							},
						});
					}, 1000);
				};
				element.on('click', toggleExpand);
				element.on('keydown', (e) => {
					if ([
						'Spacebar',
						'Enter',
						' ',
					].includes(e.key)) {
						e.preventDefault();
						toggleExpand();
					}
				});
			}, 0);
		},
	})])

if (!Array.prototype.shuffle) {
	Array.prototype.shuffle = function shuffleArray() {
		for (let i = this.length - 1; i > 0; i--) {
			const j = Math.floor(Math.random() * (i + 1));
			[this[i], this[j]] = [this[j], this[i]];
		}
		return this;
	}
}
