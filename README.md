# hook_handler_p

Hook Handler P is a small alternative to the htaccess mod_rewrite option where it is not available. 

(c) Tom Wiesing 2013

## Requirements
* PHP 5.2 or higher
* htaccess support for ErrorDocument, Options, DirectoryIndex

## Install
* copy .htaccess and index.php to your webserver root.

## Config
Configuration is in index.php, at the top. The syntax is: 

    @DIRECTIVE ARGUMENTS

Anything which starts with ; is regarded a comment. 
Empty lines are ignored. 
Available directives are: 

* `@include FILENAME Also read the configuration file FILENAME. Only supported in the main file. `
* `@self URL` Whenever we are redirected to this file, we handle it as if we were redirected to URL
* `@fix PATTERN` Keeps everything like patterm fixed. Only applied intiially. 
* `@ignore REGEXP` Same as fix, but for regexp. 
* `@protocol PROTOCOL` Force to use PROTOCOL for redirects
* `@domain DOMAIN` Force to use DOMAIN for redirects
* `@config PARAM VALUE` Set the configuration setting PARAM to VALUE. 
* `@index FILENAME` Add Filename to the list of index files. 
* `@rewrite PATTERN REPLACEMENT MOD` - Rewrite PATTERN to REPLACEMENT. You can use * as replacement characters in both pattern and replacement. 
* `@rewrite_regexp REGEXP REPLACEMENT MOD` - Rewrite anything matching REGEXP to REPLACEMENT. 
* `@empty FILENAME` Include FILENAME when redirecting to itself. 
* `@end_config` Ends the config file. Ignores everything after the line it is used on. 

For more documentation see index.php

## License
WTFPL, see COPYING