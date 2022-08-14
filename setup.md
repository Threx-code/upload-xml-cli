## How to Setup the CLI Application

Following the following steps to setup the application.
- Clone the repository.
- Run composer update
- Setup the database. This application setup postgres database
- Run migrations
- run passport:install
- Generate credentials.json from your google console and store it inside storage repository
- Setup up the below .env configuration
    GOOGLE_SHEET_ID=SpreadSheetId
    GOOGLE_SERVICE_ENABLED=true
    GOOGLE_SERVICE_ACCOUNT_JSON=
    GOOGLE_APP_NAME=YourappName


## You can use either CLI or http requests to load the XML files

## CLI
 run vendor/bin/sail artisan xml:upload

### HTTP Request

- /authenticate/google
- /authenticate/users
- /upload/xml  [Require the authorization bearer token]

