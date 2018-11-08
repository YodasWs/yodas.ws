/* app.json */
angular.module('yodas.ws', modules)
.config(['$locationProvider', '$routeProvider', function($locationProvider, $routeProvider) {
	$locationProvider.html5Mode(false);
	$routeProvider.otherwise({redirectTo: '/'});
}])
.controller('app', ['$rootScope', function($rootScope) {
	$rootScope.json = json || {};
}]);
