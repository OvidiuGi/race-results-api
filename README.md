# race-results-api

## Description
REST API using API Platform and Symfony.
## Features
- Importing a CSV list of results for a race
- Calculate the average finish time & placement of runners
- Showing the imported results
- Ability to edit a result

## Installation
1. Clone the repository
2. Run `composer install`
3. Run `bin/console doctrine:database:create && bin/console doctrine:migrations:migrate`
3. Run `symfony serve -d`

## Usage
 - Open `http://localhost:8000/api` in your browser to use the API


## Tests
1. Run `./vendor/bin/phpunit tests (GitHub Actions will run the tests automatically)

### Author
- [Ovidiu Gireada]


## License
[MIT](https://choosealicense.com/licenses/mit/)