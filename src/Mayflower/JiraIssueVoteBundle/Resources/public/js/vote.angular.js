'use strict';

jira_vote.controller('voteController', ['$scope', '$http', function ($scope, $http) {

    var buttonData = {
        'vote': {
            'class': 'btn-danger',
            'glyphiconid': 'glyphicon-thumbs-down'
        },
        'unvote': {
            'class': 'btn-success',
            'glyphiconid': 'glyphicon-thumbs-up'
        }
    };

    $scope.clickButton = function(issueId, type) {
        if (type !== 'vote' && type !== 'unvote') {
            throw new Error('Invalid tye defined for issue #' + issueId);
        }

        $scope.progressVote = true;
        var url             = '/' + type + '-issue/' + issueId;

        $http({url: url, method: type === 'vote' ? 'POST' : 'DELETE'})
            .success(function () {
                var data = buttonData[type];

                $scope.currentAction = type === 'vote' ? 'unvote' : 'vote';

                $scope.progressVote = false;
                $scope.buttonStyle  = data.class;
                $scope.glyphicon    = data.glyphiconid;
                $scope.error        = null;

                $scope.votes = $scope.increaseIssueCount(type, $scope.votes);

                if (localStorage.getItem('voted') === 'true') {
                    for (var index in $scope.$parent.properties.issues) {
                        if ($scope.$parent.properties.issues[index].issueId === issueId) {
                            $scope.$parent.properties.issues.splice(index, 1);
                        }
                    }
                }
            })
            .error(function () {
                $scope.error = 'Internal error! Please contact an administrator!';
                $scope.progressVote = false;
            })
        ;
    };

    $scope.increaseIssueCount = function (type, count) {
        if (type === 'vote') {
            return count + 1;
        }

        return count - 1;
    };

}])
    .controller('issueController', ['$scope', 'excludeItemFilter', function ($scope, excludeItemFilter) {

        if (typeof initialData === 'undefined') {
            throw new Error('Initial data object of issues cannot be found!');
        }

        var copy    = $.extend({}, initialData);
        copy.issues = excludeItemFilter.filter(initialData.issues);

        $scope.properties = copy;

        $scope.labelPluralization = function (voteCount) {
            voteCount = parseInt(voteCount);

            if (1 === voteCount) {
                return 'Vote'
            }
            return 'Votes';
        };

        $scope.$watchGroup(
            [
                function () {
                    return localStorage.getItem('voted');
                },
                function () {
                    return localStorage.getItem('resolved');
                },
                function () {
                    return localStorage.getItem('reported');
                }
            ],
            function () {
                var propertyCopy = $scope.properties;
                var issues       = excludeItemFilter.filter(initialData.issues); // reload complete issue list

                propertyCopy.issues = issues;
                $scope.properties   = propertyCopy;
            }
        );

    }])
    .filter('sanitize', ['$sce', function($sce) {
        return function(htmlCode){
            return $sce.trustAsHtml(htmlCode);
        }
    }])
    .factory('excludeItemFilter', function () {
        return {
            filter: function (issues) {
                var voted    = localStorage.getItem('voted') === 'true';
                var resolved = localStorage.getItem('resolved') === 'true';
                var reported = localStorage.getItem('reported') === 'true';

                return issues.filter(function (issue) {
                    if (voted && issue.userVoted) {
                        return false;
                    }

                    if (resolved && issue.isResolved) {
                        return false;
                    }

                    if (reported && issue.reporter === initialData.currentUser) {
                        return false;
                    }

                    return true;
                });
            }
        };
    })
;
