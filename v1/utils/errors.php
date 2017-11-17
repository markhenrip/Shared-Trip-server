<?php
/**
 * Created by PhpStorm.
 * User: Mark
 * Date: 17.11.2017
 * Time: 19:44
 */

/* missing function implementation */
function ERR_MISSING_FUNCTION_CREATE($endpoint) { throw new Exception('\'Create\' action is not implemented ['.$endpoint.']'); }
function ERR_MISSING_FUNCTION_READ($endpoint) { throw new Exception('\'Read\' action is not implemented ['.$endpoint.']'); }
function ERR_MISSING_FUNCTION_UPDATE($endpoint) { throw new Exception('\'Update\' action is not implemented ['.$endpoint.']'); }
function ERR_MISSING_FUNCTION_DELETE($endpoint) { throw new Exception('\'Delete\' action is not implemented ['.$endpoint.']'); }

