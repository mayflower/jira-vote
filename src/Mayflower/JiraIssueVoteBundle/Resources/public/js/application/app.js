(function () {

    'use strict';

    angular.module('jira-vote', []).config(
        function ($interpolateProvider, $httpProvider) {
            $interpolateProvider.startSymbol('<%').endSymbol('%>');
            $httpProvider.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
            $httpProvider.defaults.headers.common['Content-Type'] = 'application/json';
        }
    );

})();
