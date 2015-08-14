(function (ng) {

    'use strict';

    function ToggleController ($scope) {
        $scope.showDetails = false;
        $scope.arrowClass = 'down';

        $scope.onIssueToggle = function () {
            $scope.showDetails = !$scope.showDetails;
            $scope.arrowClass = $scope.showDetails ? 'up' : 'down';
        };
    }

    ng.module('jira-vote').controller('ToggleController', ToggleController);

})(window.angular);
