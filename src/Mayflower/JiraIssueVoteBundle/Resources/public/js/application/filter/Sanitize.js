(function (ng) {

    'use strict';

    ng.module('jira-vote').filter('sanitize', ['$sce', function($sce) {
        return function(htmlCode){
            return $sce.trustAsHtml(htmlCode);
        }
    }]);

})(window.angular);
