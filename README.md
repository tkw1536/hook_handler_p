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
* `@end_config` Ends the config file. Ignores everything after the line it is used on. 

* General behaviour:
    * `@config PARAM VALUE` Set the configuration setting PARAM to VALUE. Later directives will override previous ones. 
    * `@self URL` Whenever we are redirected to this file, we handle it as if we were redirected to URL. 
    * `@protocol PROTOCOL` Force to use PROTOCOL for redirects
    * `@domain DOMAIN` Force to use DOMAIN for redirects
    * `@index FILENAME` Add Filename to the list of index files. 
    * `@empty FILENAME` Include FILENAME when redirecting to itself. 


* Rewrite Rules (Simple Pattern): 
    * `@fix PATTERN` Keeps everything like patterm fixed. Only applied intiially. 
    * `@rewrite PATTERN REPLACEMENT MOD` - Rewrite PATTERN to REPLACEMENT. You can use * as replacement characters in both pattern and replacement. Also use modifiers in MOD. 
    * `@rewrite_once PATTERN REPLACEMENT MOD` - Rewrite PATTERN to REPLACEMENT if nothing else matches. You can use * as replacement characters in both pattern and replacement. Also use modifiers in MOD. 
    * `@noindex FILENAME` Make FILENAME be ignored as an idnex file. 


* Rewrite Rules (Regexp): 
    * `@rewrite_regexp REGEXP REPLACEMENT MOD` - Rewrite anything matching REGEXP to REPLACEMENT. Also use modifiers in MOD. 
    * `@rewrite_regexp_once REGEXP REPLACEMENT MOD` - Rewrite anything matching REGEXP to REPLACEMENT if nothing else matches. Also use modifiers in MOD. 
    * `@ignoreindex REGEXP` Ignore everythign which matches REGEXP as an index file. 
    * `@ignore REGEXP` Same as fix, but for regexp. 

The following  configuration is available: 
    * `max_depth` Maximum recusrion depth. Default: 20
    * `allow_multiple` Allow using the same rule several times. Default: false
    * `add_trailing_slash` Add a trailing slash if it applicable? Default: true. 
    * `use_index_include_hack` Include index files instead of redirecting to them? Ignored if `check_index_start` is false. Default: true. 
    * `check_index_start` Check for index files at the start? Default: true. 
    * `check_index_end` Check for index files at the end? Default: true. 
    * `check_index_rules` Check for index files after applying a rule? Default: true. 
    * `check_real_start` Check for reak files at the start? Default: true. 
    * `check_index_rules` Check for real files after applying a rule? Default: true. 

* `@self` might be ignored it tehre is a rule matching. 
* `@rewrite_once` might be ignored since the target page might redirect you again. 
* index files, real files and external rewrites top the rewrite chain. 
* Modifiers can be any combination of ! @ ? :  and -
    * a ! means to stop every further replace. 
    * a @ means not to match the destination. Not supported for regexp. 
    * a : means not to check for index files
    * a ? means not to check for real files
    * a - marks the destination as external

## License
                DO WHAT THE FUCK YOU WANT TO PUBLIC LICENSE
                        Version 2, December 2004

     Copyright (C) 2004 Sam Hocevar <sam@hocevar.net>

     Everyone is permitted to copy and distribute verbatim or modified
     copies of this license document, and changing it is allowed as long
     as the name is changed.

                DO WHAT THE FUCK YOU WANT TO PUBLIC LICENSE
       TERMS AND CONDITIONS FOR COPYING, DISTRIBUTION AND MODIFICATION

      0. You just DO WHAT THE FUCK YOU WANT TO.