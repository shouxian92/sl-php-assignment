# sl-php-assignment

This project houses a basic PHP project that allows for storage and retrieval of a blob value

## Considerations

- Consistency of the data, what should be done when two or more concurrent requests comes in?
    - Simple solution is to use relational DB transactions and let a database handle the ACID stuff, the tradeoff is that a relational DB is heavy for a service with such a simple operation
    - Easy to write integration tests with a well known DB
- The requirement sounds a lot like a transactional log db
- What kind of data model can we use to store and retrieve the data. There are a few important considerations: 
    1. How complex to write/query by the key to the map 
    2. How complex to write/query by timestamp after retrieval by key
    3. How complex to retrieve all data
    4. How often 1,2 occurs as opposed to 3
- Encoding
    - The values to be stored can be JSON/string, for simplicity's sake we can choose to store the content type in the db for each key so that we can send a Content-Type header back to let the caller know that there is a parse-able Content-Type.
- Logging
    - Only if an error occurs. Not much value to log successful response since it literally accepts any kind of value
- Tests
    - Basic sanity check on input
        - If endpoint expects a json input then validate for json
        - If we are using some sort of DB layer then sanitize it such that it doesn't do any sort of funny stuff on the DB layer
        - Reject empty payloads
    - Unit tests
    - Integration between logic layers (if any)
    - Integration tests at CI (can opt to use docker-compose)
    - Integration tests at CD

## API Design

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