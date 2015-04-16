jira_vote.controller('toggleController', ['$scope', function ($scope) {

    $scope.showDetails = false;
    $scope.arrowClass = 'down';

    $scope.onIssueToggle = function () {
        $scope.showDetails = !$scope.showDetails;
        $scope.arrowClass = $scope.showDetails ? 'up' : 'down';
    };

}]);
