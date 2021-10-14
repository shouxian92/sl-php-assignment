# sl-php-assignment

This project houses a basic PHP project that allows for storage and retrieval of a blob value

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