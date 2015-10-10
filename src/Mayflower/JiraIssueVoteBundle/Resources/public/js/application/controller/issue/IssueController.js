(function (ng) {

    'use strict';

    var module = ng.module('jira-vote');

    function IssueController($http, $scope, $window) {
        $scope.issues        = [];
        $scope.ready         = false;
        $scope.loadMoreItems = false;
        $scope.disableMore   = false;
        $scope.showButton    = false;
        $scope.issueTypes    = [];
        $scope.issueStates   = [];

        if (localStorage.getItem('voted') === null) {
            localStorage.setItem('voted', 'true');
        }

        if (localStorage.getItem('resolved') === null) {
            localStorage.setItem('resolved', 'true');
        }

        if (localStorage.getItem('reported') === null) {
            localStorage.setItem('reported', 'true');
        }

        if (localStorage.getItem('subtask') === null) {
            localStorage.setItem('subtask', 'true');
        }

        $window.onscroll = function () {
            var scrollPosition = document.body.scrollTop || document.documentElement.scrollTop;

            $scope.showButton = scrollPosition > 0;
            $scope.$apply();
        };

        function filter (issues) {
            var voted      = localStorage.getItem('voted') === 'true';
            var resolved   = localStorage.getItem('resolved') === 'true';
            var reported   = localStorage.getItem('reported') === 'true';
            var issueType  = localStorage.getItem('issueType');
            var issueState = localStorage.getItem('issueState');
            var subTask    = localStorage.getItem('subTask') === 'true';

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

                if (subTask && issue.sub_task === true) {
                    return false;
                }

                if (null !== issueType) {
                    if (issue.issue_type !== issueType && 'Everything' !== issueType) {
                        return false;
                    }
                }

                if (null !== issueState) {
                    if (issue.status !== issueState && 'Everything' !== issueState) {
                        return false;
                    }
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

                        $scope.filterName  = response.data.filterName;
                        $scope.currentUser = response.data.currentUser;
                        $scope.issues      = filter(source.concat(data));
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
                                    },
                                    function () {
                                        return localStorage.getItem('issueType');
                                    },
                                    function () {
                                        return localStorage.getItem('issueState');
                                    },
                                    function () {
                                        return localStorage.getItem('subTask');
                                    }
                                ],
                                function () {
                                    var issues    = $scope.issues;
                                    $scope.issues = filter(data);
                                }
                            );
                        }

                        var issueTypes  = response.data.types;
                        var issueStates = response.data.states;

                        typeCheck:
                        for (var i in issueTypes) {
                            var knownTypes  = $scope.issueTypes;

                            for (var x in knownTypes) {
                                if (knownTypes[x] === issueTypes[i]) {
                                    continue typeCheck;
                                }
                            }

                            $scope.issueTypes.push(issueTypes[i]);
                        }

                        stateCheck:
                        for (var i in issueStates) {
                            var knownStates = $scope.issueStates;

                            for (var y in knownStates) {
                                if (knownStates[y] === issueStates[i] || knownStates[y] === undefined) {
                                    continue stateCheck;
                                }
                            }

                            $scope.issueStates.push(issueStates[i]);
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
