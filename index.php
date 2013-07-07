<?php
/*
	hook_handler_p

	(c) Tom Wiesing 2013
	
	This program is free software. It comes without any warranty, to
	the extent permitted by applicable law. You can redistribute it
	and/or modify it under thfste terms of the Do What The Fuck You Want
	To Public License, Version 2, as published by Sam Hocevar. See
	http://sam.zoy.org/wtfpl/COPYING for more details. 
*/
	if(false){/* Begin Config */
?>
; Script Configuration
; Syntax is @DIRECTIVE ARUMENTS
; Anything with ";" is a comment
; so are empty lines. 
; To check which config is available, please check the README

; To set a hook use

; @rewrite URL REWRITE MODIFIERS

; You can use * as wildcats
; in the replacement they will be used in the same order. (Chanign order coming soon)#

; Example

; @rewrite /about /about/
; @rewrite /about/* /about.html
; @rewrite /* /content/* @

; Redirects the directory about to about.html on root. 

; You can also use Regular expressions
; @rewrite_regexp REGEXP REPLACEMENT MODIFIERS

; Modifiers can be any combination of ! @ and ? and : and -
; a ! means to stop every further replace. 
; a @ means not to match the destination. Not supported for regexp. 
; a : means not to check for index files
; a ? means not to check for real files
; a - marks the destination as external

; You can also set index files: 
; @index FILENAME

; Basic Configuration

@config max_depth 20 ; The maximum depth
@config allow_multiple false ; Allow using a rule several times
@config add_trailing_slash true ; Add a trailing slash if it is missing

; When to check indexes
@config check_index_start true ; check for indexes at the start
@config check_index_end true ; check at the end
@config check_index_rules false ; Do not check with rules. 

; When to check for real files
@config check_real_start true ; Do not check at the start
@config check_real_rules true ; Do not check with rules. 

; Settings for domain and protocol
@domain auto ; or a specific domain without protocol
@protocol auto ; or "http" or "https"

; Set index files
@index index.php
@index index.html

@self / ; Handle self as root
@empty data/404.html; Handle empty things

@rewrite /* /content/* @ ; Redirect everything outside of content to content

@fix /content/*


@end_config ; End Config
<?php
	}

	//startswith, ends with, adapted from: http://stackoverflow.com/questions/834303/php-startswith-and-endswith-functions
	function startsWith($haystack, $needle)
	{
		if($needle == ""){return true; }
	    return strpos($haystack, $needle) === 0;
	}
	function endsWith($haystack, $needle)
	{
		if($needle == ""){return true; }
	    return substr($haystack, -strlen($needle)) == $needle;
	}


	/**
	 * This function is to replace PHP's extremely buggy realpath().
	 * @param string The original path, can be relative etc.
	 * @return string The resolved path, it might not exist.
	 * adapted from http://stackoverflow.com/questions/4049856/replace-phps-realpath
	 */
	function truepath($path){
	    // whether $path is unix or not
	    $unipath=strlen($path)==0 || $path{0}!='/';
	    // attempts to detect if path is relative in which case, add cwd
	    if(strpos($path,':')===false && $unipath)
	        $path=getcwd().DIRECTORY_SEPARATOR.$path;
	    // resolve path parts (single dot, double dot and double delimiters)
	    $path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
	    $parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
	    $absolutes = array();
	    foreach ($parts as $part) {
	        if ('.'  == $part) continue;
	        if ('..' == $part) {
	            array_pop($absolutes);
	        } else {
	            $absolutes[] = $part;
	        }
	    }
	    $path=implode(DIRECTORY_SEPARATOR, $absolutes);
	    // put initial separator that could have been lost
	    $path=!$unipath ? '/'.$path : $path;
	    return $path;
	}

	function get_directives($directive){
		if($directive != "include"){
			$basic = get_directives_from($directive, __FILE__); 
			foreach get_directives("include") as $filename {
				$filename = $filename[0];
				$basic = array_merge($basic, get_directives_from($directive, $filename, true)); 
			}
			return $basic; 
		} else {
			return get_directives_from("include", __FILE__);
		}
	}


	function get_directives_from($directive, $filename, $use = false){
		$directives = array();
		$file_handle = fopen($filename, "r");
		while (!feof($file_handle)) {
			$line = fgets($file_handle);
			if($use){
				if($line != "" and $line != "\n" and !startsWith($line, ";") and startsWith($line, "@" . $directive . " ")){
					array_push($directives, explode(" ", trim(substr($line, strlen("@" . $directive . " "))))); 
				}
			} else {
				$use = startsWith($line, "?>"); 
			}
			if(startsWith($line, "@end_config")){
				break;
			}
		}
		fclose($file_handle);
		return $directives; 
	}

	function get_config(){
		$config = get_directives("config");
		$config_array = array(); 
		foreach($config as $val){
			$config_array[$val[0]] = json_decode($val[1]); 
		}
		return $config_array; 
	}

	function make_regexp_matcher($pattern){
		//turns a simple pattern into a regexpto be used for matching
		$split = explode("*", $pattern);
		foreach($split as &$str){
			$str = preg_quote($str, "/"); 
		}

		return "/^" . implode("(.*)", $split) . "$/s"; 
	}

	function make_regexp_replace($pattern){
		//turns a simple pattern into a regexpto be used for replacing
		if (strpos($pattern, "*") === FALSE){
			return $pattern; 
		}

		$split = explode("*", $pattern);
		foreach($split as &$str){
			$str = str_replace("$", "\\$", $str); 
		}

		$length = count($split); 

		$res = ""; 

		$i = 0;
		while($i < $length - 1){
			$res = $res . $split[$i] . "\${" .  strval( ++$i )  . "}"; 
		}

		if ($i > 0){
			$res = $res . $split[$i];
		}

		return $res; 
	}

	function get_fixes(){
		$fixes = get_directives("fix");
		foreach($fixes as &$fix){
			$fix = $fix[0];
			$fix = make_regexp_matcher($fix); 
		}
		$ignores = get_directives("ignore");
		foreach($ignores as &$ignore){
			$ignore = $ignore[0]; 
		}

		return array_merge($fixes, $ignores); 
	}

	function is_fixed($url){
		$fixes = get_fixes(); 
		foreach ($fixes as $fix){
			if(preg_match($fix, $url)){
				return true; 
			}
		}

		return false; 
	}


	function get_hooks($check_indexes, $check_reals){
		$normal_hooks = get_directives("rewrite");
		foreach($normal_hooks as &$hook){
			$will_stop = false; 
			$will_not_match_dest = false; 
			$will_check_index = $check_indexes; 
			$will_check_real = $check_reals;
			$external = false; 
			if(count($hook) > 2){
				$will_stop = (strpos($hook[2], "!") !== false);  
				$will_not_match_dest = (strpos($hook[2], "@") !== false);

				if(strpos($hook[2], ":") !== false){
					$will_check_index = !$will_check_index; 
				}

				if(strpos($hook[2], "?") !== false){
					$will_check_real = !$will_check_real; 
				}

				if(strpos($hook[2], "-") !== false){
					$external = true; 
				}
			}
			$hook = array(
					make_regexp_matcher($hook[0]),
					make_regexp_replace($hook[1]),
					$will_stop, $will_not_match_dest, make_regexp_matcher($hook[1]), 
					$will_check_index, $will_check_real, $external);
		}
		$regexp_hooks = get_directives("rewrite_regexp");
		foreach($regexp_hooks as &$hook){
			$will_stop = false; 
			$will_check_index = $check_indexes;
			$will_check_real = $check_reals;  
			$external = false; 
			if(count($hook) > 2){
				$will_stop = ($hook[2] == "!");  

				if(strpos($hook[2], ":") !== false){
					$will_check_index = !$will_check_index; 
				}

				if(strpos($hook[2], "?") !== false){
					$will_check_real = !$will_check_real; 
				}

				if(strpos($hook[2], "-") !== false){
					$external = true; 
				}
			}
			$hook = array(
					$hook[0],
					$hook[1],
					$will_stop, false, "", $will_check_index, $will_check_real, $external);
		}
		return array_merge($normal_hooks, $regexp_hooks);
	}

	function hook_match($hook, $url){
		if($hook[3]){
			return (preg_match($hook[0], $url) and !preg_match($hook[4], $url));
		} else {
			return preg_match($hook[0], $url); 
		}
	}

	function hook_replace($hook, $url){
		return preg_replace($hook[0], $hook[1], $url); 
	}
	
	function hook_apply($hook, $url){
		if(hook_match($hook, $url)){
			return hook_replace($hook, $url); 
		} else {
			return $url; 
		}
	}

	function hook_stops($hook){
		return $hook[2]; 
	}

	function is_hook_external($hook){
		return $hook[7];
	}


	$protocol = (!empty($_SERVER['HTTPS']) && ('on' == $_SERVER['HTTPS']))? "https" : "http"; 


	$conf_protocol = get_directives("protocol"); 
	$conf_protocol = $conf_protocol[0][0];


	if($conf_protocol != "auto"){
		$protocol = $conf_protocol;
	}

	$domain = $_SERVER['HTTP_HOST']; 


	$conf_domain = get_directives("domain"); 
	$conf_domain = $conf_domain[0][0];


	if($conf_domain != "auto"){
		$domain = $conf_domain;
	}

	$host = $protocol."://" .$domain;

	function make_hook_url($url, $external = false){
		$root = $url; 
		if(!$external){
			$root = $GLOBALS["host"] . $url; 
		}
		return $root; 
	}

	function hook_check_index($hook){
		return $hook[5]; 
	}

	function hook_check_real($hook){
		return $hook[6]; 
	}

	function get_first_hook_id($url, $hooks){
		foreach ($hooks as $i => $hook){
			if(hook_match($hook, $url)){
				return $i; 
			}
		}
		return -1;
	}

	function get_index_file($indexes, $path){
		$root = $_SERVER["DOCUMENT_ROOT"] . $path; 
		foreach ($indexes as $index){
			$pth = $root . $index[0];
			if(is_file($pth)){
				if(realpath($pth) == realpath(__FILE__)){ //I'm not an index file (I'm invisible)
					return false; 
				}
				return truepath( $path . "/" . $index[0] );
			}
		}

		return false; 
	}

	function get_real_file( $path){
		$root = $_SERVER["DOCUMENT_ROOT"] . $path; 

		if(endsWith($root, "/")){
			$root = substr($root, 0, strlen($root) - 1);
		}
		
		if(is_file($root)){
			if(realpath($root) == realpath(__FILE__)){
				return NULL;
			}
			return $path; 
		}
		return false; 
	}

	function hooks_match($url, $hooks, $indexes, $allow_multiple, $max_count, $add_trailing_slash, $index_init, $index_finish, $real_start, $self_handler){
		$i = 0;
		$path = $url; 
		$applied_hooks = array(); 

		if($real_start){
			$index = get_real_file($path); 
			if($index !== false){
				if($index === NULL){
					$path = $self_handler; 
				} else {
					return $index;
				}
			}
		}

		if($index_init){
			$index = get_index_file($indexes, $path); 
			if($index !== false){
				return $index; 
			}
		}

		while($i < $max_count){

			if($add_trailing_slash){
				if(!endsWith($path, "/")){
					$path = $path . "/"; 
				}
			}


			if(is_fixed($path)){
				return make_hook_url($path); 
			}

			$hook = get_first_hook_id($path, $hooks); 

			if($hook == -1 or (!$allow_multiple and in_array($hook, $applied_hooks))){
				break; 
			}

			array_push($applied_hooks, $hook);
			$path = hook_replace($hooks[$hook], $path); 

			if(is_hook_external($hooks[$hook])){
				return make_hook_url($path, true);
			}

			if(hook_check_real($hooks[$hook])){
				$index = get_real_file($path); 
				if($index !== false){
					if($index === NULL){
						$path = $self_handler; 
					} else {
						return make_hook_url($index);
					}
				}
			}

			if(hook_check_index($hooks[$hook])){
				$index = get_index_file($indexes, $path); 
				if($index !== false){
					return make_hook_url($index); 
				}
			}

			

			if(hook_stops($hooks[$hook])){
				break; 
			}

			$i++;
		}

		if($index_finish){
			$index = get_index_file($indexes, $path); 
			if($index !== false){
				return make_hook_url($index); 
			}
		}


		return make_hook_url($path); 
	}

	function get_current_url(){
		$pageURL = 'http';
		if (@$_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
		$pageURL .= "://";
		if ($_SERVER["SERVER_PORT"] != "80") {
		   $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		}
		else {
		   $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		}
		return $pageURL;
	}

	function get_redirect_dest($url){
		$config =  get_config();
		$self = get_directives("self"); 
		$self = $self[0][0];

		return hooks_match($url, 
			get_hooks($config["check_index_rules"], $config["check_real_rules"]), 
			get_directives("index"), 
			$config["allow_multiple"], 
			$config["max_depth"], 
			$config["add_trailing_slash"],
			$config["check_index_start"], 
			$config["check_index_end"],
			$config["check_real_start"],
			$self
			);
	}

	function redirectto($url){
		if(get_current_url() != $url){
			header( 'Location: ' . $url ) ;
		} else {
			$empty_include = get_directives("empty");
			include $empty_include[0][0]; 
		}
	}

	function apply_redirects(){
		redirectto(get_redirect_dest($_SERVER['REQUEST_URI']));
	}

	apply_redirects(); 
?>