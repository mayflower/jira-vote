(function (ng) {

    'use strict';

    function VoterController ($scope, $http) {
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
                        for (var index in $scope.$parent.issues) {
                            if ($scope.$parent.issues[index].issueId === issueId) {
                                $scope.$parent.issues.splice(index, 1);
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
    }

    ng.module('jira-vote').controller('VoterController', VoterController);

})(window.angular);
