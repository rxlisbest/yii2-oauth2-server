<?php
/**
 * @link https://github.com/borodulin/yii2-oauth2-server
 * @copyright Copyright (c) 2015 Andrey Borodulin
 * @license https://github.com/borodulin/yii2-oauth2-server/blob/master/LICENSE
 */

namespace conquer\oauth2;
use yii\web\Response;

use yii\base\UserException;
/**
 * @author Andrey Borodulin
 *
 */
class HttpException extends UserException
{
    const ACCESS_DENIED = 'access_denied';
    const INVALID_CLIENT = 'invalid_client';
    const INVALID_GRANT = 'invalid_grant';
    const INVALID_REQUEST = 'invalid_request';
    const INVALID_SCOPE = 'invalid_scope';
    const REDIRECT_URI_MISMATCH = 'redirect_uri_mismatch';
    const SERVER_ERROR = 'server_error';
    const TEMPORARILY_UNAVAILABLE = 'temporarily_unavailable';
    const UNAUTHORIZED_CLIENT = 'unauthorized_client';
    const UNSUPPORTED_GRANT_TYPE = 'unsupported_grant_type';
    const UNSUPPORTED_RESPONSE_TYPE = 'unsupported_response_type';

    const NOT_IMPLEMENTED = 'not_implemented';


    protected $error;

	public $statusCode;


	/**
	 * Constructor.
	 * @param integer $status HTTP status code, such as 404, 500, etc.
	 * @param string $message error message
	 * @param integer $code error code
	 * @param \Exception $previous The previous exception used for the exception chaining.
	 */
	public function __construct($status, $message = null, $code = 0, \Exception $previous = null)
	{
		$this->statusCode = $status;
		parent::__construct($message, $code, $previous);
	}

	/**
	 * @return string the user-friendly name of this exception
	 */
	public function getName()
	{
		if (isset(Response::$httpStatuses[$this->statusCode])) {
			return Response::$httpStatuses[$this->statusCode];
		} else {
			return 'Error';
		}
	}
}
