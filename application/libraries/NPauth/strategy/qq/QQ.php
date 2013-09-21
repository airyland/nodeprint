<?php

!defined('BASEPATH') && exit('No direct script access allowed');
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

class Qq{

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
                CURLOPT_TIMEOUT        => 60//,
                //CURLOPT_USERAGENT      => 'simple-douban-oauth2-0.4',
                );

    protected $ci;

    /**
     * initialize
     *
     * @param array $params
     * @return void
     */
    public function __construct()
    {   
        $this->ci=&get_instance();
        $this->ci->config->load('oauth');
        $this->clientId = $this->ci->config->item('np.oauth.qq.apikey');
        $this->secret = $this->ci->config->item('np.oauth.qq.secret');
        $this->redirectUri = 'http://nodeprint.com/oauth/qq/callback';
        $this->responseType = 'code';
        $this->authorizeUri='https://graph.qq.com/oauth2.0/authorize';
        $this->accessUri='https://graph.qq.com/oauth2.0/token';
        $this->apiUri='https://graph.qq.com';
        $this->scope='get_user_info';
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
        exit;
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
                    'state' => 'raose'
                    );
        $result = $this->curl($accessUrl, 'POST', $header, $data);
        parse_str($result,$token);

        $this->tokens = $token;
        $this->token_info =$token;

        $response=$this->curl('https://graph.qq.com/oauth2.0/me?access_token='.$token['access_token'],'GET');

        $lpos = strpos($response, "(");
        $rpos = strrpos($response, ")");
        $response  = substr($response, $lpos + 1, $rpos - $lpos -1);
        $this->openid=$openid = json_decode($response)->openid;
    }

    private function get_user_info(){
       $this->user_info =json_decode($this->curl('https://graph.qq.com/user/get_user_info?access_token='.$this->tokens['access_token'].'&oauth_consumer_key='.$this->clientId.'&openid='.$this->openid,'GET'),true);
       return $this->user_info;
    }
    
    public function parse_user_info(){
       $user_info = $this->get_user_info();
        $user_data = array(
            'user_from'=>'QQ',
            'user_name'=>$user_info['nickname'],
            'user_register_time'=>current_time(),
            'user_profile_info'=>json_encode(array(
                'github'=>'',
                'twitter'=>'',
                'weibo'=>'',
                'douban'=>'',
                'location'=>'',
                'avatar'=>$user_info['figureurl_2'],
                'sign' => '',
                'intro' => '',
                'site' => ''
                ))
            );
        $_SESSION['avatar']=$user_info['figureurl_2'];
        $_SESSION['user_data']=$user_data;
        return $user_data;
    }

    public function parse_user_token(){
         $oauth_info = $this->token_info;
        $oauth = array(
            'o_type' => 'QQ',
            'o_access_token' => $oauth_info['access_token'],
            'o_openid' => $this->openid,
            'o_refresh_token' => '',
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
                    'scope' => $this->scope,
                    'state' => 'raose'
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
    protected function curl($url, $type, $header=array(), $data = array())
    {
        $opts = $this->CURL_OPTS;
        $opts[CURLOPT_URL] = $url;
        $opts[CURLOPT_CUSTOMREQUEST] = $type;
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