'use strict';

jira_vote.controller('settingsController', ['$scope', function ($scope) {

    function fetchLocaleStorageBoolSafe(propertyName) {
        return localStorage.getItem(propertyName) === 'true';
    }

    $scope.data = {
        voted:    fetchLocaleStorageBoolSafe('voted'),
        resolved: fetchLocaleStorageBoolSafe('resolved'),
        reported: fetchLocaleStorageBoolSafe('reported')
    };

    $scope.updateSettings = function () {
        localStorage.setItem('voted', $scope.data.voted);
        localStorage.setItem('reported', $scope.data.reported);
        localStorage.setItem('resolved', $scope.data.resolved);

        $('#settingsDialog').modal('hide');
    };

}]);
