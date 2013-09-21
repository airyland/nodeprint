<?php
require_once __DIR__.'/Google_Client.php';
require_once __DIR__.'/Google_Oauth2Service.php';
//session_start();

class Google {

  /**
  * google clien 
  */
  private $clien;

  /**
  * google oauth service
  */
  private $oauth;

  /**
  * google openid
  */
  public $openid;

  /**
  * user's token info
  */
  public $token_info;

   /**
  * user info
  */
  public $user_info;

	function __construct(){
		$this->client = new Google_Client();
    $this->client->setApplicationName("Google UserInfo PHP Starter Application");
    // Visit https://code.google.com/apis/console?api=plus
    $this->client->setClientId('982792199846.apps.googleusercontent.com');
    $this->client->setClientSecret('mrqb3_erO8bhkCn08Dx4VcPv');
    $this->client->setRedirectUri('http://nodeprint.com/oauth/google/callback');
    $this->client->setDeveloperKey('AIzaSyBvbDUXYQaIqLSbRGXGnvVLM46Vu33guqA');
    $this->oauth = new Google_Oauth2Service($this->client);
	}

	public function requestAuthorizeCode(){
	   $authUrl = $this->client->createAuthUrl();
     header("location:".$authUrl);
	}

  public function setAuthorizeCode($code){
     $this->client->authenticate($code);
     $_SESSION['token_info'] = $this->client->getAccessToken();
  }

  public function requestAccessToken(){
    $token=$this->client->getAccessToken();
    if ($token) {
      $this->token_info = json_decode($token,true);
      $this->user_info = $_SESSION['user_info'] = $user = $this->oauth->userinfo->get();
      $_SESSION['token_info'] = $this->client->getAccessToken();
      $this->openid= $user['id'];
    }
  }


  public function parse_user_info(){
         $user_info = $this->user_info;
          $user_data = array(
              'user_from'=>'google',
              'user_name'=>$user_info['name'],
              'user_email'=>$user_info['email'],
              'user_register_time'=>current_time(),
              'user_profile_info'=>json_encode(array(
                  'google' => $user_info['link'],
                  'github'=>'',
                  'twitter'=>'',
                  'weibo'=>'',
                  'douban'=>'',
                  'location'=>'',
                  'avatar'=>$user_info['picture'],
                  'sign' => '',
                  'intro' => '',
                  'site' => ''
                  ))
              );
          $_SESSION['avatar']=$user_info['picture'];
          $_SESSION['user_data']=$user_data;
          return $user_data;
      }

    public function parse_user_token(){
         $oauth_info = $this->token_info;
         $oauth = array(
            'o_type' => 'google',
            'o_access_token' => $oauth_info['access_token'],
            'o_openid' => $this->openid,
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

}
