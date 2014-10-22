<?php 
/**
 * SimplyREST is a lightweight php REST routing script 
 *
 * It's ment to be used as simple rest based server. It's 
 * supports all HTTP methods and has helpers for generating
 * JSON and Javascript responses. 
 *
 * It does not support nested resources as of yet.
 *
 * At the moment SimplyREST is use as backend for an EmberJS
 * single page web application and is as such designed with 
 * that usage in mind. It also server as a backend for 
 * a KnockoutJS based application which is loosly based on 
 * RESTfull, something SimplyREST also supports.
 *
 * SimplyREST is Memcache ready which means it will cache
 * route configuration in Memcache so it doesn't have to
 * go through the php tokenizer on each request. To keep
 * the requests as small as possible the responses are 
 * gziped
 *
 */

/**
 * SimplyREST prefers convention over configuration
 * as such very little configuration is nececary.
 * The Configuration that's needed has "sane defaults"
 * and is intentionally left in the routers source file
 * in order to keep things light weight and all in one
 * place
 */ 
define("BASE_PATH", '/'); // Path relative to document root


/* All Logic from this point down ward */

// Composer
require 'vendor/autoload.php';

define("API_BASE", $_SERVER{'DOCUMENT_ROOT'}.BASE_PATH);
$p_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$SEGS = explode('/', substr($p_path, strlen(BASE_PATH), strlen($p_path)));

// Prefix controller functions with the request method verb for requests other then GET
$method = strtolower($_SERVER['REQUEST_METHOD'])."_"; 

// API Routes
if (have_route($SEGS[0])) {

    // Simple root url for resource without url path arguments
    if (!isset($SEGS[1]) && user_fn_exists($SEGS[0], $method.'index')) {
        ob_start("ob_gzhandler");
        include_once($SEGS[0].".php");
        run_filters($method.'index');
        call_user_func_array($method."index", array_slice($SEGS, 2));
        ob_end_flush();

    // Before assuming params check if we have the specified function 
    } else if (isset($SEGS[1]) && user_fn_exists($SEGS[0], $method.$SEGS[1])) {
        ob_start("ob_gzhandler");
        include_once($SEGS[0].".php");
        run_filters($method.$SEGS[1]);
        call_user_func_array($method.$SEGS[1], array_slice($SEGS, 2)); 
        ob_end_flush();

    // If non above match try to call the method_index function with 
    // the rest of the path as arguments to the function 
    } else if (isset($SEGS[1]) && user_fn_exists($SEGS[0], $method.'index')) { 
        ob_start("ob_gzhandler");
        include_once($SEGS[0].".php");
        run_filters($method.'index');
        call_user_func($method.'index', $SEGS[1]); 
        ob_end_flush();
        
    } else {
        exitWithHTTPstatus(404, json_encode(array(
            "error" => "No function [".$method.$SEGS[1]."] or [".$method."index] for ".$SEGS[0])));
    }
} else {
    exitWithHTTPstatus(404, json_encode(array( 
        "error" => "No such API Resource ".$SEGS[0].".php")));
} 

/*
 * Check to see if we have filters
 * defined that need to be run
 * before actions are executed.
 */
function run_filters($action) {
    global $before_filters;
    if ($before_filters) {
        if (isset($before_filters[$action])) {
            foreach ($before_filters[$action] as $fn_filter) {
                call_user_func($fn_filter);
            }
        } 
    }  
}

/*
 * Checks a php include for the exsistance 
 * of a function. 
 */
function user_fn_exists($file, $func) {
    global $memcache;

    if ($memcache) {
        if ($memcache->get(ENV."simplyrest.routing.$file.$func")) {
            return true;
        }
    }
    $php_tokens = token_get_all(
        file_get_contents(API_BASE.$file.".php"));
    for ($i=0; $i<count($php_tokens); $i++) {
        if ($php_tokens[$i][0] == T_FUNCTION) {
            // Check if we have a name with this function 
            // this way we can skip over anonymouse functions
            // in which we're not interested for actions
            if (isset($php_tokens[$i+2][1])) {
                $function_name = $php_tokens[$i+2][1];
                if ($function_name == $func) {
                    if ($memcache) {
                        $memcache->set(ENV."simplyrest.routing.$file.$func", true, false, 0);
                    }
                    return true;
                } else {
                    // Skip over the next two we know what they are
                    $i+=2;
                } 
            }
        } 
    }
}

// Parse POST/PUT body data utility
function decode_json_http_body() {
    return json_decode(file_get_contents("php://input"));
}

function exitWithHTTPstatus($code, $json) {
    header('content-type: application/json');
    header('HTTP/1.1 ' . $code);
    exit($msg =! '' ? $json : $code );
}

// Keep routing simple. If we have a file name related 
// to the specific url segment then load it up. 
// Of course we try to keep things safe.
function have_route($segment) {
    // Don't allow circular include
    if ($segment == "simplyrest") {
        exitWithHTTPstatus(403, json_encode(array(
            "error" => "url forbidden")));
    }
    // No urls that start with dots
    if (substr($segment, 0, 1) == ".") {
      exitWithHTTPstatus(403, json_encode(array(
          "error" => "API root url forbidden")));
    }
    // Api doesn't have a root
    if ($segment == "") {
      exitWithHTTPstatus(403, json_encode(array(
          "error" => "API root url forbidden")));
    }
    return file_exists(API_BASE.$segment.".php");
}

function set_json_response($response="") {
    header('content-type: application/json');
    echo json_encode($response);
}

function set_js_response($response="") {
    header('content-type: application/javascript');
    echo $response;
}
?>
