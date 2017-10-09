<?php

namespace AppBundle\Helpers;

use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\Facebook;
use Facebook\Helpers\FacebookRedirectLoginHelper;
use Mockery\Exception;

/**
 * Class FacebookDriver
 * @package AppBundle\Helpers
 */
class FacebookDriver
{
    const FIELDS = ['id','name','email'];

    private $fb;

    /**
     * @param $appId
     * @param $secretId
     */
    public function __construct($appId, $secretId)
    {
        try {
            $this->fb = new Facebook(['app_id' => $appId, 'app_secret' => $secretId]);
        } catch (FacebookSDKException $e)
        {
            var_dump($e->getMessage()   ); die();
        }
    }

    /**
     * @param $helper
     * @return mixed
     */
    private function generateAccessToken(FacebookRedirectLoginHelper $helper)
    {
        try {
            // to fetch access token
            return $helper->getAccessToken();
        } catch (FacebookResponseException $e) {
            // When facebook server returns error
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch (FacebookSDKException $e) {
            // when issue with the fetching access token
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }
    }

    /**
     * @param FacebookRedirectLoginHelper $helper
     */
    private function checkError(FacebookRedirectLoginHelper $helper)
    {
        if ($helper->getError()) {
            header('HTTP / 1.0 401 Unauthorized');
            echo 'Error: ' . $helper->getError() . "\n";
            echo 'Error Code: ' . $helper->getErrorCode() . "\n";
            echo 'Error Reason: ' . $helper->getErrorReason() . "\n";
            echo 'Error Description: ' . $helper->getErrorDescription() . "\n";
        } else {
            header('HTTP / 1.0 400 Bad Request');
            echo 'Bad request';
        }
        exit;
    }

    /**
     * @param $accessToken
     * @return \Facebook\FacebookResponse
     */
    private function getFbResponse($accessToken)
    {
        try {
            $field = implode(',', self::FIELDS);
            $params = '/me?fields='.$field;
            return $this->fb->get($params, $accessToken->getValue());
        } catch (FacebookResponseException $e)// throws an error if invalid fields are specified
        {
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch (FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }
    }

    /**
     * @return \Facebook\FacebookResponse
     */
    public function getResponce()
    {
        $helper = $this->fb->getRedirectLoginHelper();
        $accessToken = self::generateAccessToken($helper);

        if (!isset($accessToken)) {
            $this->checkError($helper);
        }

        return $this->getFbResponse($accessToken);
    }

    /**
     * @param $callbackUrl
     * @return string
     */
    public function generateUrl($callbackUrl)
    {
        // to set redirection url
        $helper       = $this->fb->getRedirectLoginHelper();

        // set required permissions to user details
        $permissions  = ["email"];

        return $helper->getLoginUrl($callbackUrl, $permissions);
    }
}