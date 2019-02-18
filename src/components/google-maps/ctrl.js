angular.module('googleMaps')
	.component('googleMaps', {
		templateUrl: 'components/google-maps/google-maps.html',
		bindings: {
			locale: '<',
		},
		controllerAs: '$ctrl',
		controller: 'ctrlGoogleMaps',
	})
	.directive('googleMaps', ['$timeout', ($timeout) => ({
		link(scope, element, attrs, controller, transcludeFn) {
			if (element instanceof jQuery) element = element[0];

			const locale = scope.$ctrl.locale;
			if (!locale.map && (!Number.isInteger(locale.z) || locale.z < 0 || !Number.isFinite(locale.lat) || !Number.isFinite(locale.lng))) {
				// Can't build a Map Image, so abort
				return;
			}

			// Our Map Image
			const img = new Image(300, 300);

			if (typeof locale.map === 'string') {
				img.src = locale.map;
			} else if (typeof locale.map === 'object' && typeof locale.map.src === 'string') {
				img.src = locale.map.src;
			} else if (Number.isInteger(locale.z) && locale.z >= 0 && Number.isFinite(locale.lat) && Number.isFinite(locale.lng)) {
				// PS: Road Sign warning yellow: #FCD047
				// PS: Road Sign legal yellow: #FFCF48
				// PS: Road Sign route yellow: #F6D223
				// PS: Road Sign black: #262323
				// PS: Road Sign black: #282425
				// PS: Road Sign brown: #7C4A11
				// PS: Road Sign green: #006F54
				// PS: Road Sign blue: #025A9B
				// PS: Road Sign red: #BE303A
				// PS: Toll Sign purple: #6C2B6A
				// More at https://mutcd.fhwa.dot.gov/htm/2009/part2/part2_toc.htm
				const markers = [];
				if (typeof locale.map === 'object' && typeof locale.map.icon === 'string') {
					markers.push('anchor:bottom');
					markers.push(`icon:https://yodasws.github.io/yodas.ws/components/google-maps/icons/${locale.map.icon}.png`);
				} else if (locale.home) {
					markers.push('anchor:bottom');
					markers.push('icon:https://yodasws.github.io/yodas.ws/components/google-maps/icons/home.png');
				} else {
					markers.push('size:small');
				}
				markers.push(`${locale.lat},${locale.lng}`);
				const src = 'https://maps.googleapis.com/maps/api/staticmap?' +
					Object.entries({
						markers: markers.join('|'),
						size: '300x300',
						format: 'png32',
						scale: 2,
						zoom: locale.z,
						key: 'AIzaSyBeRM7BDdB6UzJ-z_IJftYP6lMx3e4u5H4',
						visible: locale.map && Array.isArray(locale.map.visible) ? locale.map.visible.join('|') : '',
					}).map(param => param.join('=')).join('&');
				img.src = src;
			}
			img.decode().then(() => {
				element.appendChild(img);
			}).catch(() => {
				console.log('Failed to load map for', locale.name);
			});
		}
	})])
	.controller('ctrlGoogleMaps', ['$document', '$scope', function($document, $scope) {
		this.$onInit = () => {
			if (this.locale) {
				// console.log('Sam, google-maps $ctrl:', this);
			}
		};
	}]);
