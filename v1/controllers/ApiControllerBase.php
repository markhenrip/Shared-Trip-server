<?php
/**
 * Created by PhpStorm.
 * User: Mark
 * Date: 15.11.2017
 * Time: 18:11
 */

abstract class ApiControllerBase
{
    protected $httpMethod = '';
    protected $endpointName = '';
    protected $verb = '';
    protected $uriArgs = array();
    protected $file = null;
    protected $masterParam = '';

    public function __construct($requestUri)
    {
        header("Access-Control-Allow-Orgin: *");
        header("Access-Control-Allow-Methods: *");
        header("Content-Type: application/json");

        $uriArgs = explode('/', rtrim($requestUri, '/'));

        $this->endpointName = array_shift($uriArgs);

        if (array_key_exists(0, $uriArgs)) {
            if (!is_numeric($uriArgs[0]))
                $this->verb = array_shift($uriArgs);
            else {
                $this->masterParam = array_shift($uriArgs);

            }
        }

        $this->httpMethod = $_SERVER['REQUEST_METHOD'];

        if ($this->httpMethod != "GET" and
            $this->httpMethod != "POST" and
            $this->httpMethod != "DELETE" and
            $this->httpMethod != "PUT")
            throw new Exception("Unexpected Header");
    }
}