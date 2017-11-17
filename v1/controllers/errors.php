<?php
/**
 * Created by PhpStorm.
 * User: Mark
 * Date: 17.11.2017
 * Time: 19:44
 */

//function ERR_TEMPLATE($arg) { throw new Exception(''.$arg); }

/*** CONTROLLER ERRORS ***/

/* missing function implementation */
function ERR_MISSING_FUNCTION_CREATE($endpoint) { throw new Exception('\'Create\' action is not implemented ['.$endpoint.']'); }
function ERR_MISSING_FUNCTION_READ($endpoint) { throw new Exception('\'Read\' action is not implemented ['.$endpoint.']'); }
function ERR_MISSING_FUNCTION_UPDATE($endpoint) { throw new Exception('\'Update\' action is not implemented ['.$endpoint.']'); }
function ERR_MISSING_FUNCTION_DELETE($endpoint) { throw new Exception('\'Delete\' action is not implemented ['.$endpoint.']'); }

/* MySQL */
function ERR_MYSQLI_CONNECTION($conErr) { throw new Exception('Failed to establish a DB connection: '.$conErr); }
function ERR_STMT_EXEC($stmtErr) { throw new Exception('Could not execute SQL statement: '.$stmtErr); }
function MISSING_PARAMS_ERR() { throw new Exception('Missing one or more query parameters'); }