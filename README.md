# hook_handler_p

Hook Handler P is a small alternative to the htaccess mod_rewrite option where it is not available. 

(c) Tom Wiesing 2013

## Requirements
* PHP 5.2 or higher
* htaccess support for ErrorDocument, Options, DirectoryIndex

## Install
* copy .htaccess and index.php to your webserver root.

## Config
Configuration is in index.php, at the bottom. The syntax is: 

    @DIRECTIVE ARGUMENTS

Anything which starts with ; is regarded a comment. 
Empty lines are ignored. 
Available directives are: 

* `@self URL` Whenever we are redirected to this file, we handle it as if we were redirected to URL
* `@protocol PROTOCOL` Force to use PROTOCOL for redirects
* `@domain DOMAIN` Force to use DOMAIN for redirects
* `@config PARAM VALUE` Set the configuration setting PARAM to VALUE. 
* `@index FILENAME` Add Filename to the list of index files. 
* `@rewrite PATTERN REPLACEMENT MOD` - Rewrite PATTERN to REPLACEMENT. You can use * as replacement characters in both pattern and replacement. 
* `@rewrite_regexp REGEXP REPLACEMENT MOD` - Rewrite anythign matching REGEXP to REPLACEMENT. 

For more documentation see index.php

## License
WTFPL, see COPYING