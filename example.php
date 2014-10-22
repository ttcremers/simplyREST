<?php
/**
 * An example REST controller/resource
 * Checkout the comments and the examples
 * they tell you all you need to know
 */

/**
 * SimplyREST also support before filters
 *
 * a before filter is a function that gets 
 * called before the action function is called
 * before filters are setup on a per function 
 * basis and can consist of multiple function 
 * refences. 
 *
 * A good example of a before filter would be
 * an authentication check. This way you can 
 * write the function once and add it as a 
 * reference to every action which needs 
 * authentication. 
 */
$auth_callback = function() {
  if (!isset($_SESSION['valid_user'])) {
    // JS reponse is also possible
    set_js_response("alert('Access Denied')");
    exit();
  }
};
// $before_filters is convention 
$before_filters = array(
    // lets reference a the get_index action
    "get_auth" => array($auth_callback));


/**
 * As expected index is the default
 * action for every controller. 
 *
 * Actions are always prefixed by the
 * HTTP method they should respond to
 *
 * function <METHOD>_name() {}
 *
 * folling that convention this example
 * rest controller will repond to:
 *
 * GET: http://url.com/example
 * 
 * SimplyREST also support aruments in order
 * to parse id's in the url
 *
 * GET: http://url.com/example/1
 */
function get_index($id) {
  set_json_response(
    Array( "message" => "A simplyREST greeting: ".$id) 
  );
}

/**
 * Or what about a custom action for the example resource
 *
 * GET: http://url.com/example/bar
 *
 * As you can see argument are supported here as well
 *
 * GET: http://url.com/example/bar/1
 */
function get_foo($id) {
  set_json_response(
    Array( "message" => "SimplyREST bar: ".$id ) 
  );
}

/**
 * A simple action which has a before filter set on it
 */
function get_auth() {
  set_json_response(
    Array( "message" => "You authenticated" ) 
  );
}

/**
 * SimplyREST can also help you decode a json body
 */
function post_index() {
  // This will dump a php object representation
  // of the JSON that was send in the request 
  // body
  var_dump(decode_json_http_body()); 
}
?>
