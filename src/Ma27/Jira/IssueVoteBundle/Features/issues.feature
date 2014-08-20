Feature: Issue List
    In order to vote issues, a list is required

    Background:
      Given I have an account with the following values:
        | name             |
        | maximilian.bosch |
      Given there are issues:
        | id | filterId | key    | description             | summary   | viewLink                   | reporter         | voteCount | voted  | resolved |
        | 1  | 1        | TEST-1 | some simple description | Heading_1 | http://example.org/browse/ | test.user        | 0         | true   | false    |
        | 2  | 1        | TEST-2 | some simple long test   | Heading_2 | http://example.org/browse/ | maximilian.bosch | 0         | false  | true     |
        | 3  | 1        | TEST-3 | something else          | Heading_3 | http://example.org/browse/ | test.user        | 1         | true   | false    |
        | 4  | 1        | TEST-4 | the whole description   | Heading_4 | http://example.org/browse/ | test.user        | 2         | true   | false    |
        | 5  | 1        | TEST-5 |                         | Heading_5 | http://example.org/browse/ | test.user        | 10        | false  | false    |
        | 6  | 2        | TEST-6 | some simple description | Heading_6 | http://example.org/browse/ | test.user        | 0         | true   | false    |
        | 7  | 3        | TEST-7 | some simple description | Heading_7 | http://example.org/browse/ | test.user        | 0         | true   | false    |
      Given there are filters:
        | filterId | filterName | owner            | viewLink                                                       |
        | 1        | test-1     | maximilian.bosch | http://example.org/secure/IssueNavigator?mode=hide&requestId=1 |
        | 2        | test-2     | test.user        | http://example.org/secure/IssueNavigator?mode=hide&requestId=2 |
        | 3        | test-3     | maximilian.bosch | http://example.org/secure/IssueNavigator?mode=hide&requestId=3 |

    Scenario: view issues
      When I'm logged in
      And I'm on page "/"
      And I have selected the filter "1"
      Then I should see a list of issues from filter 1

    Scenario: view issues when my credentials are invalid
      When I'm on page "/"
      And my credentials are expired
      Then I should be redirected

    Scenario: vote issue
      When I'm on page "/"
      And I didn't vote issue "5"
      And I press "Vote"
      Then I should have voted

    Scenario: unvote issue
      When I'm on page "/"
      And I have voted issue "1"
      And I press "Unvote"
      Then I should have removed by vote
