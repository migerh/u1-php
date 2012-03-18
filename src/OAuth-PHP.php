<?php

/**
 * Ubuntu One OAuth PHP
 * 
 * @package U1 PHP
 *
 * From:
 * @copyright Copyright (C) 2010 Rooftop Solutions. All rights reserved.
 * @author Evert Pot (http://www.rooftopsolutions.nl/) 
 * @license http://code.google.com/p/dropbox-php/wiki/License MIT
 */

require_once("OAuth.php");
require_once("Exception.php");

/**
 * This class is used to sign all requests to ubuntu one.
 *
 * This specific class uses the PHP OAuth extension
 */
class U1_OAuth_PHP extends U1_OAuth {

    /**
     * OAuth object
     *
     * @var OAuth
     */
    protected $oAuth;

    /**
     * Constructor
     * 
     * @param string $consumerName
     * @param string $consumerKey 
     * @param string $consumerSecret 
     */
    public function __construct($consumerName, $consumerKey = "ubuntuone", $consumerSecret = "hammertime") {
        if (!class_exists('OAuth')) 
            throw new U1_Exception('The OAuth class could not be found! Did you install and enable the oauth extension?', 0);

        $this->OAuth = new OAuth($consumerKey, $consumerSecret,OAUTH_SIG_METHOD_HMACSHA1,OAUTH_AUTH_TYPE_URI);
        $this->OAuth->enableDebug();
        $this->consumerName = $consumerName;
    }

    /**
     * Sets the request token and secret.
     *
     * The tokens can also be passed as an array into the first argument.
     * The array must have the elements token and token_secret.
     * 
     * @param string|array $token 
     * @param string $token_secret 
     * @return void
     */
    public function setToken($token, $token_secret = null) {
        parent::setToken($token,$token_secret);
        $this->OAuth->setToken($this->oauth_token, $this->oauth_token_secret);

    }

    /**
     * Fetches a secured oauth url and returns the response body. 
     * 
     * @param string $uri 
     * @param mixed $arguments 
     * @param string $method 
     * @param array $httpHeaders 
     * @return string 
     */
    public function fetch($uri, $arguments = array(), $method = 'GET', $httpHeaders = array()) {
        try {
            $this->OAuth->fetch($uri, $arguments, $method, $httpHeaders);
            $result = $this->OAuth->getLastResponse();
            $lastResponseInfo = $this->OAuth->getLastResponseInfo();
            return array(
                'httpStatus' => $lastResponseInfo['http_code'],
                'body'       => $result,
            );
        } catch (OAuthException $e) {

            $lastResponseInfo = $this->OAuth->getLastResponseInfo();
            switch($lastResponseInfo['http_code']) {

                  // Not modified
                case 304 :
                    return array(
                        'httpStatus' => 304,
                        'body'       => null,
                    );
                    break;
                case 401 :
                case 403 :
                    throw new U1_Exception('Forbidden. This could mean a bad OAuth request, or invalid access tokens were used.', 403);
                case 404 : 
                    throw new U1_Exception('Resource at uri: ' . $uri . ' could not be found', 404);
                case 507 : 
                    throw new U1_Exception('Quota exceeded', 509);
                default:
                    // rethrowing
                    throw new U1_Exception('Unknown error: ' . $e->getMessage(), 0, $e);
            }

        }

    }

    /**
     * Requests the OAuth request token.
     *
     * @param string $callback
     *
     * @return void 
     */
    public function getRequestToken($callback) {
        try {
            $tokens = $this->OAuth->getRequestToken(self::URI_REQUEST_TOKEN . "?oauth_callback=" . $callback);
            $this->setToken($tokens['oauth_token'], $tokens['oauth_token_secret']);
            return $this->getToken();
        } catch (OAuthException $e) {
            throw new U1_Exception('We were unable to fetch request tokens. This likely means that your consumer key and/or secret are incorrect.',0,$e);
        }
    }

    /**
     * Requests the OAuth access tokens.
     *
     * This method requires the 'unauthorized' request tokens
     * and, if successful will set the authorized request tokens.
     *
     * @param string $verifier
     * 
     * @return void 
     */
    public function getAccessToken($verifier) {
        try {
            $uri = self::URI_ACCESS_TOKEN . "?oauth_verifier=".$verifier;
            $tokens = $this->OAuth->getAccessToken($uri);
            $this->setToken($tokens['oauth_token'], $tokens['oauth_token_secret']);
            return $this->getToken();
        } catch (OAuthException $e) {
            throw new U1_Exception('We were unable to fetch access tokens. This likely means that the authentication has not been started properly.',0,$e);
        }
    }
}
