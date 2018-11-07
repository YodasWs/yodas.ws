/* app.json */
angular.module('yodas.ws', modules)
.config(['$locationProvider', '$routeProvider', function($locationProvider, $routeProvider) {
	$locationProvider.html5Mode(false);
	$routeProvider.when('/', {
		templateUrl: 'pages/home.html',
		controllerAs: '$ctrl',
		controller() {
			angular.element('[ng-view]').attr('ng-view', 'pageHome');
		},
	})
	.otherwise({redirectTo: '/'});
}])
.controller('app', ['$rootScope', function($rootScope) {
	$rootScope.json = json || {};
}]);
