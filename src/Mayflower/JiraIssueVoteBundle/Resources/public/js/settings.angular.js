'use strict';

jira_vote.controller('settingsController', ['$scope', '$http', function ($scope, $http) {

    $scope.onAddSettings = function (option) {
        /** @ToDo access properties dynamically (didn't work first time) */
        switch (option) {
            case 'voted':
                var type = !$scope.settingVotedType;

                break;
            case 'reported':
                var type = !$scope.settingReportedType;

                break;
            case 'resolved':
                var type = !$scope.settingResolvedType;

                break;
            default:
                throw new Error('Invalid model name');

                break;
        }

        var url = '/settings/' + option + '/' + type;

        $scope.message = 'Settings in progress. Page will be reloaded...';
        $http({method: 'GET', url: url})
            .success(function () {
                $scope.result = true;
                $scope.type = 'success';

                window.setTimeout(
                    function () {
                        location.reload();
                    },
                    1000
                );
            })
            .error(function () {
                $scope.result = true;
                $scope.type = 'danger';
                $scope.message = 'Error occurred. Please try again';
            });
    };

}]);
