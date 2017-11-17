<?php
/**
 * Created by PhpStorm.
 * User: Mark
 * Date: 17.11.2017
 * Time: 19:44
 */

//function ERR_TEMPLATE($arg) { throw new Exception(''.$arg); }

/* ROUTER ERRORS */
function ERR_HTTP_METHOD($method) { throw new Exception('Undefined HTTP method: '.$method); }
function ERR_CONTROLLER_NAME($name) { throw new Exception('Undefined controller: '.$name); }
