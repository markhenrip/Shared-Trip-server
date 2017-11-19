<?php

function doSomething() {
	return "hello";
}

function PUTparams() {
    
    parse_str(file_get_contents("php://input"), $put_body);

    $body_master_key = array_shift(array_keys($put_body));
    $put_body_contents = $put_body[$body_master_key];

    $raw_params =
        explode("Content-Disposition: form-data; name=", $put_body_contents);

    $actual_params = array();

    foreach ($raw_params as $raw_pair) {
        $pieces = preg_split('~\"|(\r\n)+(-+\d+)?~',$raw_pair);

        $filtered_pair = array_values(
            array_filter($pieces, function($a) {return $a!="";}));

        $actual_params[$filtered_pair[0]] = tryParseNumber($filtered_pair[1]);
    }

    return $actual_params;
}

function tryParseNumber($value) {
    if (!is_numeric($value))
        return $value;

    $float = floatval($value);
    $int = intval($value);

    if ($float == $int)
        return $int;

    return $float;
}

function parsePathForArgs($path) {

    $pieces = explode('/', $path);
    $controllerName = array_shift($pieces);

    if (is_numeric($controllerName))
        throw new Exception('Invalid controller name: '.$controllerName);

    $pathArgs = array('controller' => $controllerName);

    if (isset($pieces[0])){

        if (is_numeric($pieces[0]))
            $pathArgs['id'] = tryParseNumber(array_shift($pieces));
        else if (!isset($pieces[1])) {
            $pathArgs['verb'] = tryParseNumber(array_shift($pieces));
            return $pathArgs;
        }

        if (isset($pieces[0])) {

            if (is_numeric($pieces[0]))
                throw new Exception('Invalid path parameter: ' . array_shift($pieces));

            $pathArgs = array_merge($pathArgs, joinPathArgs($pieces));
        }
    }
    return $pathArgs;
}

/** Turns strings like 'id/1/sth/2/please' into
 * {
 *   'id' = 1,
 *   'sth' = 2,
 *   'finisher' = 'please'
 * }
 * @param $args - a string
 * @return array
 */
function joinPathArgs($args) {

    $argsObject = array();

    // odd number of path args means there is a finishing verb
    if (count($args) % 2 == 1) {
        $argsObject['verb'] = array_pop($args);
    }

    // even number of path args: key-value pairs
    for ($i=0; $i<count($args); $i += 2) {
        $key = $args[$i];
        $val = $args[$i+1];

        if (!is_numeric($val))
            throw new Exception('Every second path arg must be a numeric id');

        $argsObject[$key] = $val;
    }
    return $argsObject;
}

?>