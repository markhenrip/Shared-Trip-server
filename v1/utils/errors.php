<?php
/**
 * Created by PhpStorm.
 * User: Mark
 * Date: 17.11.2017
 * Time: 19:44
 */

//function ERR_TEMPLATE($arg) { throw new Exception(''.$arg); }


/* missing function implementation */
function ERR_MISSING_FUNCTION_CREATE($endpoint) { throw new Exception('\'Create\' action is not implemented ['.$endpoint.']'); }
function ERR_MISSING_FUNCTION_READ($endpoint) { throw new Exception('\'Read\' action is not implemented ['.$endpoint.']'); }
function ERR_MISSING_FUNCTION_UPDATE($endpoint) { throw new Exception('\'Update\' action is not implemented ['.$endpoint.']'); }
function ERR_MISSING_FUNCTION_DELETE($endpoint) { throw new Exception('\'Delete\' action is not implemented ['.$endpoint.']'); }

/* routing */
function ERR_HTTP_METHOD($method) { throw new Exception('\'Undefined HTTP method: \' . $method'.$method); }
function ERR_CONTROLLER_NAME($name) { throw new Exception('Undefined controller: '.$name); }

/* MySQL */
function ERR_MYSQLI_CONNECTION($conErr) { throw new Exception('Failed to establish a DB connection: '.$conErr); }
function ERR_STMT_EXEC($stmtErr) { throw new Exception('Could not execute SQL statement: '.$stmtErr); }