<?php
/**
 * @link https://github.com/borodulin/yii2-oauth2-server
 * @copyright Copyright (c) 2015 Andrey Borodulin
 * @license https://github.com/borodulin/yii2-oauth2-server/blob/master/LICENSE
 */

namespace conquer\oauth2\granttypes;

use conquer\oauth2\models\User;
use conquer\oauth2\models\AccessToken;
use conquer\oauth2\Exception;
use conquer\oauth2\BaseModel;
use yii\web\HttpException;

/**
 *
 * @author Andrey Borodulin
 */
class UserCredentials extends BaseModel
{
    private $_user;

    /**
     * Value MUST be set to "password".
     * @var string
     */
    public $grant_type;

    /**
     * Value MUST be set to "password".
     * @var string
     */
    public $username;

    /**
     * Value MUST be set to "password".
     * @var string
     */
    public $password;

    /**
     *
     * @var string
     */
    public $client_id;

    public function rules()
    {
        return [
            [['username', 'password', 'grant_type', 'client_id'], 'required']
        ];
    }

    public function getResponseData()
    {
        $user = $this->getUser();
        $acessToken = AccessToken::createAccessToken([
            'client_id' => 'blog',
            'user_id' => $user->user_id,
            'expires' => $this->accessTokenLifetime + time(),
            'scope' => $user->scope,
        ]);

	    $refreshToken = \conquer\oauth2\models\RefreshToken::createRefreshToken([
		    'client_id' => $this->client_id,
		    'user_id' => $user->user_id,
		    'expires' => $this->refreshTokenLifetime + time(),
		    'scope' => $user->scope,
	    ]);

        return [
            'access_token' => $acessToken->access_token,
            'expires_in' => $this->accessTokenLifetime,
            'token_type' => $this->tokenType,
            // 'scope' => $this->scope,
	        'refresh_token' => $refreshToken->refresh_token,
        ];
    }


    /**
     *
     * @return \conquer\oauth2\models\AuthorizationCode
     */
    public function getUser()
    {
        if (is_null($this->_user)) {
            // if (empty($this->code)) {
            //     $this->errorRedirect('Authorization code is missing.', Exception::INVALID_REQUEST);
            // }
            if (!$this->_user = User::findOne(['username' => $this->username])) {
                throw new HttpException(400, 'User do not exist.');
            }
            if ($this->_user->password != md5($this->password . $this->_user->salt)) {
                throw new HttpException(400, 'Incorrect password.');
            }
        }
        return $this->_user;
    }
}
