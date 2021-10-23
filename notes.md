
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
        - Same key shouldn't appear twice in payload
    - Unit tests
    - Integration between logic layers (if any)
    - Integration tests at CI (can opt to use docker-compose)
    - Integration tests at CD

## Database Modeling

- Should have two tables
    - the first stores the data with the latest keys and values, this is needed for endpoint that obtains all objects. or at least all of the keys so that we can retrieve it somewhere else later.
    - the second stores a transactional log related to the key/value and encoding type so that we can retrieve it based on timestamp.
- map_value column
    - For simplicity sake we should not allow too long a string to be stored. retrieval will be an issue later as it takes up memory in the requests that obtains all keys + values
    - Storing `TEXT` vs `VARCHAR`
        - `TEXT` cannot be directly indexed, but we never have to index this column
    - Text with language encoding should have their encoding retained

## Laravel Pitfalls

- Started with `app("db")->insert("INSERT INTO objects (obj_key, obj_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE obj_value=?", [$key, $value, $value]);` and ended up with an upsert query that was very clean and allowed for multiple insert statements to be executed in 1 statement instead