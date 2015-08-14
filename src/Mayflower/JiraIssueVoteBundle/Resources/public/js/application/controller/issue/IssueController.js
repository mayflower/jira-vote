(function (ng) {

    'use strict';

    var module = ng.module('jira-vote');

    function IssueController($http, $scope, $window) {
        $scope.issues = [];
        $scope.ready  = false;

        function filter (issues) {
            var voted    = localStorage.getItem('voted') === 'true';
            var resolved = localStorage.getItem('resolved') === 'true';
            var reported = localStorage.getItem('reported') === 'true';

            return issues.filter(function (issue) {
                if (voted && issue.has_voted) {
                    return false;
                }

                if (resolved && issue.resolution) {
                    return false;
                }

                if (reported && issue.reporter === initialData.currentUser) {
                    return false;
                }

                return true;
            });
        }

        $scope.labelPluralizationFixer = function (voteAmount) {
            return voteAmount === 1 ? 'Vote' : 'Votes';
        };

        $http.get('/api/issue-data')
            .then(
                function (response) {
                    var data = response.data.issues;

                    $scope.issues      = filter(data);
                    $scope.filterName  = response.data.filterName;
                    $scope.currentUser = response.data.currentUser;
                    $scope.ready       = true;

                    $scope.issues.map(
                        function (issue) {
                            issue.published_date = new Date(issue.created);

                            return issue;
                        }
                    );

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
                },
                function (response) {
                    var hostname = window.location.hostname;

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
    }

    module.controller('IssueController', IssueController);

})(window.angular);
