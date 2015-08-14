(function (ng) {

    'use strict';

    var module = ng.module('jira-vote');

    function fetchLocaleStorageBoolSafe(propertyName) {
        return localStorage.getItem(propertyName) === 'true';
    }

    function SettingsController($scope) {
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
    }

    module.controller('settingsController', SettingsController)

})(window.angular);
