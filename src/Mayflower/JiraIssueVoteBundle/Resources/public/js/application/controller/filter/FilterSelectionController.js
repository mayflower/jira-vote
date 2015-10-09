(function (ng) {

    'use strict';

    function FilterSelectionController($http, $scope, $window) {
        $scope.gotInvalidFilter     = false;
        $scope.selectedVoteStrategy = false;
        $scope.generateSourceList   = false;
        $scope.strategyType         = !localStorage.getItem('source-type') ? 'filters' : localStorage.getItem('source-type');

        $scope.getSourceList = function () {
            $scope.generateSourceList = true;

            localStorage.setItem('source-type', $scope.strategyType);
            $http.get('/api/source/' + $scope.strategyType)
                .then(
                    function (response) {
                        $scope.selectedVoteStrategy = true;
                        $scope.generateSourceList   = false;
                        $scope.filters              = response.data;
                    },
                    function () {
                        $window.location.href = '/unauthorized';
                    }
                )
            ;
        };

        $scope.persistFilter = function (filterId) {
            $scope.gotInvalidFilter = false;

            $http.post('/api/source/' + $scope.strategyType, {filterId: filterId})
                .then(function () {
                    $window.location.href = '/';
                }, function () {
                    $scope.gotInvalidFilter = true;
                });
        };

        $scope.resetSourceConfig = function () {
            $scope.selectedVoteStrategy = false;
            $scope.generateSourceList   = false;
        };
    }

    ng.module('jira-vote').controller('FilterSelectionController', FilterSelectionController)

})(window.angular);
