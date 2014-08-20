Jira Voter
==========


1) Installation
---------------

There are some steps to install this application:

    git clone -b master https://github.com/Ma27/jira-voter /path/to/your/folder
    cd /path/to/your/folder


Install dependencies

    curl -sS https://getcomposer.org/installer | php
    composer install


Test application [optional]

    php app/check.php
    bin/behat -c app/behat.yml -f progress --no-snippets


2) Configuration
----------------

* You have to [register](https://confluence.atlassian.com/display/JIRA/Allowing+OAuth+Access) this application in JIRA.
* Now you should have a *.pem file with the Jira secret key and the consumer key. Please rename the pem file to jira.pem and
move it to /app/oauth/private.
* Now open the file /app/config/parameters.yml and change the value "jira_secret" to your consumer key and "secret" to another random key
* Open file /app/config/jira.yml and change the value "oauth_host" to the server with jira
    * IMPORTANT: if you don't use a subdomain (like https://jira.example.org) then your URL should look like "http://example.org/path/to/jira"
* Change the value "client_host" in the same file to the host of this application
