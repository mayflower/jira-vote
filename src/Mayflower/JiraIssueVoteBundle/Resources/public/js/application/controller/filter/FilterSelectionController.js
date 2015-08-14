(function (ng) {

    function FilterSelectionController($http, $scope, $window) {
        $scope.gotInvalidFilter = false;

        $http.get('/api/filters')
            .then(
                function (response) {
                    $scope.filters = response.data;
                },
                function () {
                    $window.location.href = '/unauthorized';
                }
            )
        ;

        $scope.persistFilter = function (filterId) {
            $scope.gotInvalidFilter = false;

            $http.post('/api/filters', {filterId: filterId})
                .then(function () {
                    $window.location.href = '/';
                }, function () {
                    $scope.gotInvalidFilter = true;
                });
        };
    }

    ng.module('jira-vote').controller('FilterSelectionController', FilterSelectionController)

})(window.angular);
