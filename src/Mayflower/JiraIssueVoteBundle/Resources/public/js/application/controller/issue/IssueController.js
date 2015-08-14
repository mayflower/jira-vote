(function (ng) {

    'use strict';

    var module = ng.module('jira-vote');

    function IssueController($http, $scope, $window) {
        $scope.issues        = [];
        $scope.ready         = false;
        $scope.loadMoreItems = false;
        $scope.disableMore   = false;

        function filter (issues) {
            var voted    = localStorage.getItem('voted') === 'true';
            var resolved = localStorage.getItem('resolved') === 'true';
            var reported = localStorage.getItem('reported') === 'true';

            return issues.filter(function (issue) {
                if (voted && issue.has_voted) {
                    return false;
                }

                if (resolved && !issue.resolution) {
                    return false;
                }

                if (reported && issue.reporter === $scope.currentUser.name) {
                    return false;
                }

                return true;
            });
        }

        $scope.labelPluralizationFixer = function (voteAmount) {
            return voteAmount === 1 ? 'Vote' : 'Votes';
        };

        $scope.loadMore = function () {
            $scope.receiveIssues($scope.issues.length, true);
        };

        $scope.receiveIssues = function (offset, append) {
            $scope.loadMoreItems = true;

            $http.get('/api/issue-data?issue_offset=' + parseInt(offset))
                .then(
                    function (response) {
                        $scope.loadMoreItems = false;
                        var data             = response.data.issues;
                        var source           = append ? $scope.issues : [];

                        $scope.issues      = filter(source.concat(data));
                        $scope.filterName  = response.data.filterName;
                        $scope.currentUser = response.data.currentUser;
                        $scope.ready       = true;

                        $scope.issues.map(
                            function (issue) {
                                issue.published_date = new Date(issue.created);

                                var converter     = new showdown.Converter();
                                issue.description = converter.makeHtml(issue.description);

                                return issue;
                            }
                        );

                        if (data.length < 50) {
                            $scope.disableMore = true;
                        }

                        if (!append) {
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
                                    var issues    = $scope.issues;
                                    $scope.issues = filter(data);
                                }
                            );
                        }
                    },
                    function (response) {
                        $scope.loadMoreItems = false;
                        var hostname         = window.location.hostname;

                        switch (response.status) {
                            case 401:
                                $window.location.href = '/unauthorized';
                                break;
                            case 403:
                                $window.location.href = '/filter/select';
                                break;
                        }
                    }
                )
            ;
        };

        $scope.receiveIssues(0, false);
    }

    module.controller('IssueController', IssueController);

})(window.angular);