<?php

//!defined('BASEPATH') && exit('No direct script access allowed');
/**
 * NodePrint
 *
 * Simple and Elegant Forum Software
 *
 * @package         NodePrint
 * @author          airyland <i@mao.li>
 * @copyright       Copyright (c) 2013, mao.li
 * @license         MIT
 * @link            https://github.com/airyland/nodeprint
 * @version         0.0.5
 */


if (!function_exists('curl_init')) {
    throw new Exception('oauth2 needs the CURL PHP extension.');
}
if (!function_exists('json_decode')) {
    throw new Exception('oauth2 needs the JSON PHP extension.');
}

class Douban {

    /**
     * @brief authorizeCode request uri
     */
    protected $authorizeUri;

    /**
     * @brief accessToken request uri
     */
    protected $accessUri;

    /**
     * @brief api uri
     */
    protected $apiUri;

    /**
     * @brief appkey
     */
    protected $clientId;

    /**
     * @brief app secret
     */
    protected $secret;

    /**
     * @brief callback uri
     */
    protected $redirectUri;

    /**
     * @brief Api scope
     */
    protected $scope;

    /**
     * @brief response type
     */
    protected $responseType;

    /**
     * @brief authorize code
     */
    protected $authorizeCode;

    /**
     * @brief tokens contains accessToken and refreshToken
     */
    protected $tokens;

    /**
     * @brief access token
     */
    protected $accessToken;

    /**
     * @brief refresh token
     */
    protected $refreshToken;

    /**
     * @var default header
     */
    protected $defaultHeader = array(
                'Content_type: application/x-www-form-urlencoded'
                );

    /**
     * @var authorize header
     */
    protected $authorizeHeader;  

    /**
     * @var curl default option
     */
    protected $CURL_OPTS = array(
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_TIMEOUT        => 60,
                CURLOPT_USERAGENT      => 'simple-douban-oauth2-0.4',
                );

    public $openid;

    public $token_info;

    public $user_info;

    /**
     * initialize
     *
     * @param array $params
     * @return void
     */
    public function __construct($apikey,$secret)
    {
        $this->clientId =$apikey;
        $this->secret = $secret;
        $this->redirectUri = 'http://nodeprint.com/oauth/douban/callback';
        $this->scope = 'douban_basic_common';
        $this->responseType = 'code';
        $this->authorizeUri='https://www.douban.com/service/auth2/auth';
        $this->accessUri='https://www.douban.com/service/auth2/token';
        $this->apiUri='https://api.douban.com';
    }

    /**
     * redirect to authorize page
     *
     * @return redirect
     */
    public function requestAuthorizeCode()
    {
        $authorizeUrl = $this->getAuthorizeUrl();
        header('Location:'.$authorizeUrl);
    }
    
    /**
     * set authorize code
     *
     * @param string $authorizeCode
     * @return void
     */
    public function setAuthorizeCode($authorizeCode)
    {
        $this->authorizeCode = $authorizeCode;
    }

    /**
     * get accessToken with AuthorizeCode
     *
     * @return string
     */
    public function requestAccessToken()
    {
        $accessUrl = $this->accessUri;
        $header = $this->defaultHeader;
        $data = array(
                    'client_id' => $this->clientId,
                    'client_secret' => $this->secret,
                    'redirect_uri' => $this->redirectUri,
                    'grant_type' => 'authorization_code',
                    'code' => $this->authorizeCode,
                    );
        $result = $this->curl($accessUrl, 'POST', $header, $data);
        //print_r($result);

        $this->tokens =  json_decode($result);
        $this->token_info =json_decode($result,true);

        $this->openid = $this->tokens->douban_user_id;
       // print_r($_SESSION);
        $this->refreshToken = $this->tokens->refresh_token;
        $this->accessToken = $this->tokens->access_token;
    }

    private function get_user_info(){
        $this->user_info = json_decode($this->makeRequest('/v2/user/' . $this->openid, 'GET'),true);
        return $this->user_info;
    }

    public function parse_user_info(){
        $user_info = $this->get_user_info();
        $user_data = array(
            'user_from'=>'douban',
            'user_name'=>$user_info['name'],
            'user_register_time'=>current_time(),
            'user_profile_info'=>json_encode(array(
                'github'=>'',
                'twitter'=>'',
                'weibo'=>'',
                'douban'=>$user_info['uid'],
                'location'=>$user_info['loc_name'],
                'avatar'=>$user_info['avatar'],
                'sign' => $user_info['signature'],
                'intro' => $user_info['desc'],
                'site' => $user_info['alt']
                ))
            );
        $_SESSION['avatar']=$user_info['avatar'];
        $_SESSION['user_data']=$user_data;
        return $user_data;
    }

    public function parse_user_token(){
        $oauth_info = $this->token_info;
        $oauth = array(
            'o_type' => 'douban',
            'o_access_token' => $oauth_info['access_token'],
            'o_openid' => $oauth_info['douban_user_id'],
            'o_refresh_token' => $oauth_info['refresh_token'],
            'o_time' => time(),
            'o_expire' => $oauth_info['expires_in'],
        );
        $_SESSION['token_data']=$oauth;
        return $oauth;
    }

    public function parse_user_data(){
        $this->parse_user_info();
        $this->parse_user_token();
    }

    
    /**
     * set accessToken
     *
     * @param string $accessToken
     * @return object
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
    }

    /**
     * get accessToken
     *
     * @return string
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }


    /**
     * make request
     *
     * @param string $uri
     * @param string $type 'POST' or 'GET'
     * @param array $data
     * @param boolean if true, accessToken will be sent in header
     * @return object
     */
    public function makeRequest($uri='',$type='GET', $data = array(), $authorization = false)
    {
        $url = $this->apiUri.$uri;
        $header = $authorization ? $this->getAuthorizeHeader() : $this->defaultHeader;
        $type = $type;
        $type='GET';
        return $this->curl($url, $type, $header, $data);
    }

    /**
     * build authorize url
     *
     * @return string
     */
    protected function getAuthorizeUrl()
    {
        $params = array(
                    'client_id' => $this->clientId,
                    'redirect_uri' => $this->redirectUri,
                    'response_type' => $this->responseType,
                    'scope' => $this->scope
                    );

        return $this->authorizeUri.'?'.http_build_query($params);
    }

    /**
     * get Authorization header
     *
     * @return array
     */
    protected function getAuthorizeHeader()
    {
        return $this->authorizeHeader = array('Authorization: Bearer '.$this->accessToken);
    }

    /**
     * use curl to request data
     *
     * @param string $url
     * @param string $type
     * @param array $header
     * @param array $data
     *
     * @return object
     */
    protected function curl($url, $type, $header, $data = array())
    {
        $opts = $this->CURL_OPTS;
        $opts[CURLOPT_URL] = $url;
        $opts[CURLOPT_CUSTOMREQUEST] = $type;
        $header[] = 'Expect:'; 
        $opts[CURLOPT_HTTPHEADER] = $header;
        if ($type == 'POST' || $type =='PUT') {  
            $opts[CURLOPT_POSTFIELDS] = $data;
        }
        $ch = curl_init();
        curl_setopt_array($ch, $opts);
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            die('CURL error: '.curl_error($ch));
        }
        curl_close($ch);  
        return $result;   
    }

}

/* End of file Oauths.php */
/* Location: ./application/libraries.Oauths.php */