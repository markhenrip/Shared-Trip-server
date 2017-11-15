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

?>