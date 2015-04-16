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
        var url = '/' + type + '-issue/' + issueId;

        $http.get(url)
            .success(function () {
                var data = buttonData[type];

                $scope.currentAction = type === 'vote' ? 'unvote' : 'vote';

                $scope.progressVote = false;
                $scope.buttonStyle = data.class;
                $scope.glyphicon = data.glyphiconid;
                $scope.error = null;

                $scope.votes = $scope.increaseIssueCount(type, $scope.votes);

                $http.get('/settings/list')
                    .success(function (data) {
                        if (data.hideVoted) {
                            for (var index in initialData.issues) {
                                if (initialData.issues[index].issueId === issueId) {
                                    initialData.issues.splice(index, 1);
                                }
                            }
                        }
                    });
            })
            .error(function () {
                $scope.error = 'Internal error! Please contact an administrator!';
                $scope.progressVote = false;
            });
    };

    $scope.increaseIssueCount = function (type, count) {
        if (type === 'vote') {
            return count + 1;
        }

        return count - 1;
    }

}])
    .controller('issueController', ['$scope', function ($scope) {

        if (typeof initialData === 'undefined') {
            throw new Error('Initial data object of issues cannot be found!');
        }

        $scope.properties = initialData;

        $scope.labelPluralization = function (voteCount) {
            voteCount = parseInt(voteCount);

            if (1 === voteCount) {
                return 'Vote'
            }
            return 'Votes';
        };

    }])
    .filter("sanitize", ['$sce', function($sce) {
        return function(htmlCode){
            return $sce.trustAsHtml(htmlCode);
        }
    }])
;
