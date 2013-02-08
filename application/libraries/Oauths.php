<?php  !defined('BASEPATH') && exit('No direct script access allowed');
/**
 * Oauth library
 * @author airyland <i@mao.li>
 * @version 0.5
 */
if (!function_exists('curl_init')) {
    throw new Exception('oauth2 needs the CURL PHP extension.');
}
if (!function_exists('json_decode')) {
    throw new Exception('oauth2 needs the JSON PHP extension.');
}

class Oauths {

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

    /**
     * initialize
     *
     * @param array $params
     * @return void
     */
    public function __construct($params=array('type'=>'','clientId'=>'', 'secret'=>'', 'redirectUri'=>'', 'scope' =>'douban_basic_common', 'responseType' => 'code'))
    {
        $this->clientId = $params['clientId'];
        $this->secret = $params['secret'];
        $this->redirectUri = $params['redirectUri'];
        $this->scope = $params['scope'];
        $this->responseType = $params['responseType'];
        $map = array(
            'douban'=>array('https://www.douban.com/service/auth2/auth','https://www.douban.com/service/auth2/token', 'https://api.douban.com'),
            'qq'=>array(),
            'weibo'=>array()
        );
        $this->authorizeUri=$map[$params['type']][0];
        $this->accessUri=$map[$params['type']][1];
        $this->apiUri=$map[$params['type']][2];
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

        $this->tokens = json_decode($result);
        $_SESSION['oauth']=json_decode($result,true);
        print_r($_SESSION['oauth']);
        $_SESSION['user_id']=$this->tokens->douban_user_id;
       // print_r($_SESSION);
        $this->refreshToken = $this->tokens->refresh_token;
        $this->accessToken = $this->tokens->access_token;
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