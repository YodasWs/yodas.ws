angular.module('pageHome')
.config(['$routeProvider', function($routeProvider) {
	$routeProvider.when('/', {
		templateUrl: 'pages/home/home.html',
		controllerAs: '$ctrl',
		controller: 'ctrlPageHome',
	});
}])
.controller('ctrlPageHome', ['$rootScope', function($rootScope) {
	angular.element('[ng-view]').attr('ng-view', 'pageHome');
	console.log('Sam, travels:', $rootScope.json.travels);
}]);
