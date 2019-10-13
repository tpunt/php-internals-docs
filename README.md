# Documentation for PHP's internals

tl;dr: To contribute, just submit PRs to this repo.

The original server application can be found at [tpunt/php-internals](https://github.com/tpunt/php-internals). I decided to discard those ~15k LOC of Elixir (backed by Neo4j, Redis, and ETS) because it was convoluted and I forgot how everything worked...

The new server application is built in Node, where it simply pulls data from this repo, and passes it to the old client application (found at [liammann/php-internals-client](https://github.com/liammann/php-internals-client)).

To ensure that proper formatting is used, before committing your change, please run the [linter.php](https://github.com/tpunt/php-internals-docs/blob/master/linter.php) located in the base of this repo. This will tell you if, for example:
 - A symbol or article has been deleted, but its ID is still present in a category
 - A symbol has incorrect formatting (e.g.a variable containing parameters, or a function without a definition)
