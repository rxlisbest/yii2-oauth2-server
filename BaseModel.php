<?php

namespace conquer\oauth2;

use yii\helpers\ArrayHelper;
use conquer\oauth2\models\Client;

abstract class BaseModel extends \yii\base\Model
{
    protected $_client;

    /**
     * @link https://tools.ietf.org/html/rfc6749#section-7.1
     * @var string
     */
    public $tokenType = 'bearer';

    /**
     * Authorization Code lifetime
     * 30 seconds by default
     * @var integer
     */
    public $authCodeLifetime = 30;

    /**
     * Access Token lifetime
     * 1 hour by default
     * @var integer
     */
    public $accessTokenLifetime = 3600;

    /**
     * Refresh Token lifetime
     * 2 weeks by default
     * @var integer
     */
    public $refreshTokenLifetime = 1209600;


    public function init()
    {
        $headers = [
            'client_id' => 'PHP_AUTH_USER',
            'client_secret' => 'PHP_AUTH_PW',
        ];

        foreach ($this->safeAttributes() as $attribute) {
            $this->$attribute = self::getRequestValue($attribute, ArrayHelper::getValue($headers, $attribute));
        }
        // echo json_encode($this->safeAttributes());exit;
    }

    public function addError($attribute, $error = "")
    {
        throw new Exception($error, Exception::INVALID_REQUEST);
    }

    public function errorServer($error, $type = Exception::INVALID_REQUEST)
    {
        throw new Exception($error, Exception::INVALID_REQUEST);
    }

    public function errorRedirect($error, $type = Exception::INVALID_REQUEST)
    {
        $redirectUri = isset($this->redirect_uri) ? $this->redirect_uri : $this->getClient()->redirect_uri;
        if ($redirectUri) {
            throw new RedirectException($redirectUri, $error, $type, isset($this->state) ? $this->state : null);
        } else {
            throw new Exception($error, $type);
        }
    }

    abstract function getResponseData();

    public static function getRequestValue($param, $header = null)
    {
        static $request;
        if (is_null($request)) {
            $request = \Yii::$app->request;
        }
        if ($header && ($result = $request->headers->get($header))) {
            return $result;
        } else {
            return $request->post($param, $request->get($param));
        }
    }

    /**
     *
     * @return \conquer\oauth2\models\Client
     */
    public function getClient()
    {
        if (is_null($this->_client)) {
            if (empty($this->client_id)) {
                $this->errorServer('Unknown client', Exception::INVALID_CLIENT);
            }
            if (!$this->_client = Client::findOne(['client_id' => $this->client_id])) {
                $this->errorServer('Unknown client', Exception::INVALID_CLIENT);
            }
        }
        return $this->_client;
    }

    public function validateClient_id($attribute, $params)
    {
        $this->getClient();
    }

    public function validateClient_secret($attribute, $params)
    {
        if (!\Yii::$app->security->compareString($this->getClient()->client_secret, $this->$attribute)) {
            $this->addError($attribute, 'The client credentials are invalid', Exception::UNAUTHORIZED_CLIENT);
        }
    }

    public function validateRedirect_uri($attribute, $params)
    {
        if (!empty($this->$attribute)) {
            $clientRedirectUri = $this->getClient()->redirect_uri;
            if (strncasecmp($this->$attribute, $clientRedirectUri, strlen($clientRedirectUri)) !== 0) {
                $this->errorServer('The redirect URI provided is missing or does not match', Exception::REDIRECT_URI_MISMATCH);
            }
        }
    }

    public function validateScope($attribute, $params)
    {
        if (!$this->checkSets($this->$attribute, $this->client->scope)) {
            $this->errorRedirect('The requested scope is invalid, unknown, or malformed.', Exception::INVALID_SCOPE);
        }
    }

    /**
     * Checks if everything in required set is contained in available set.
     *
     * @param string|array $requiredSet
     * @param string|array $availableSet
     * @return boolean
     */
    protected function checkSets($requiredSet, $availableSet)
    {
        if (!is_array($requiredSet)) {
            $requiredSet = explode(' ', trim($requiredSet));
        }
        if (!is_array($availableSet)) {
            $availableSet = explode(' ', trim($availableSet));
        }
        return (count(array_diff($requiredSet, $availableSet)) == 0);
    }

}