# sl-php-assignment [![Build Status](https://github.com/shouxian92/sl-php-assignment/actions/workflows/php.yml/badge.svg?branch=main)](https://github.com/shouxian92/sl-php-assignment/actions/workflows/php.yml) [![codecov](https://codecov.io/gh/shouxian92/sl-php-assignment/branch/main/graph/badge.svg?token=KFWLJRW886)](https://codecov.io/gh/shouxian92/sl-php-assignment/)

This project houses a basic PHP project that allows for storage and retrieval of a blob value.

Please take a look at [notes.md](notes.md)

# Pre-requisites

- PHP >= 7.2
- Composer
- MySQL
- Docker (For Development)

# Local Development

For local development you may use docker compose to setup a running environment. The default port runs on `8080`.

```bash
> docker compose up
```

# Unit Tests

To run unit tests, make use of the PHPUnit library that comes together with it

```bash
> ./vendor/bin/phpunit --testsuite unit-test
```

Test coverage results are output in the repository's root location `clover.xml`.

# DB Structure

Do take a look at any files located in the `/mysql` folder.

# API Design

- GET /api/v1/objects
```bash
Examples

-- Empty database --

GET /api/v1/objects
Response - 200 OK 
{}

-- Has some values --

GET /api/v1/objects
Response - 200 OK
{
    "foo": "bar",
    "baz": "fizz"
}
```
- GET /api/v1/objects/{key}
    - Accepted query params: `timestamp=<int>`
```bash
Examples

-- Successful --

GET /api/v1/objects/foo
Response - 200 OK 
bar

GET /api/v1/objects/baz
Response - 200 OK
Header - Content-Type: application/json
{
    "foo": "bar"
}

GET /api/v1/objects/baz?timestamp=1634219586
Response - 200 OK
some earlier val

-- Invalid Timestamp --

GET /api/v1/objects/baz?timestamp=not+integer
Response - 400 Bad Request
{
    "error": "Invalid timestamp given."
}

-- Not Found -- 

GET /api/v1/objects/idontexist
Response - 404 Not Found
{
    "error": "Key was not found."
}
```
- POST /api/v1/objects
    - Accepted headers: `Content-Type: application/json`
```bash
Examples

-- Successful --

POST /api/v1/objects -d '{"key": "foo"}'
Response: 200 OK
{
    "timestamp": 1634219586
}

POST /api/v1/objects -d '{"key": "baz", "another": "bar"}'
Response: 200 OK
{
    "timestamp": 1634219886
}

-- Bad Request --

POST /api/v1/objects -d 'not json'
Response: 400 Bad Request
{
    "error": "Malformed JSON payload received."
}

POST /api/v1/objects -d '{"key":"""multi qu"ote"}'
Response: 400 Bad Request
{
    "error": "Malformed JSON payload received."
}

POST /api/v1/objects -d ''
Response: 400 Bad Request
{
    "error": "Empty payload received."
}
```
