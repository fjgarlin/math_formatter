# Math field formatter for Drupal 8

Provides a very basic field formatter for math expressions.
 
## Set up

- Enable module

- Create a new field (text - formatted) in a content type or reuse existing one.

- Go to manage display and select *Math formatter*. 

- Save configuration, populate that field with an expression (*ie: 3 * ( 4 + 7 + 6 ) * 6* ) and visit the node

## TODO

- Improve string parsing method as at the moment the tokens need to be separated by space.

- Improve certain hardcoded strings so that they can be reused. 

- Unit Tests.