(function (ng) {

    'use strict';

    var module = ng.module('jira-vote');

    function fetchLocaleStorageBoolSafe(propertyName) {
        return localStorage.getItem(propertyName) === 'true';
    }

    function SettingsController($scope) {
        var types = $scope.$parent.issueTypes;
        types.push('Everything');

        var states = $scope.$parent.issueStates;
        states.push('Everything');

        var currentType  = localStorage.getItem('issueType');
        var currentState = localStorage.getItem('issueState');

        $scope.data = {
            voted:    fetchLocaleStorageBoolSafe('voted'),
            resolved: fetchLocaleStorageBoolSafe('resolved'),
            reported: fetchLocaleStorageBoolSafe('reported'),
            type:     currentType,
            types:    types,
            states:   states,
            state:    currentState
        };

        $scope.updateSettings = function () {
            localStorage.setItem('voted', $scope.data.voted);
            localStorage.setItem('reported', $scope.data.reported);
            localStorage.setItem('resolved', $scope.data.resolved);

            if (false !== $scope.data.type) {
                localStorage.setItem('issueType', $scope.data.type);
            }

            if (false !== $scope.data.state) {
                localStorage.setItem('issueState', $scope.data.state);
            }

            $('#settingsDialog').modal('hide');
        };
    }

    module.controller('settingsController', SettingsController)

})(window.angular);
