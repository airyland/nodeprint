<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
//douban Oauth config
$config['np.oauth.douban.apikey'] = '';
$config['np.oauth.douban.secret'] = '';
$config['np.oauth.douban.authorizeUri']='https://www.douban.com/service/auth2/auth';
$config['np.oauth.douban.accessUri']='https://www.douban.com/service/auth2/token';
$config['np.oauth.douban.apiUri']='https://api.douban.com';
$config['np.oauth.douban.callbackUri']='oauth/douban/callback';

//QQ Oauth config
$config['np.oauth.qq.apikey'] = '';
$config['np.oauth.qq.secret'] = '';
$config['np.oauth.qq.authorizeUri']='https://graph.qq.com/oauth2.0/authorize';
$config['np.oauth.qq.mobile.authorizeUri']='https://graph.z.qq.com/moc2/authorize ';
$config['np.oauth.qq.accessUri']='https://graph.qq.com/oauth2.0/token';
$config['np.oauth.qq.mobile.accessUri']='https://graph.z.qq.com/moc2/token ';
$config['np.oauth.qq.apiUri']='https://graph.qq.com';
$config['np.oauth.qq.callbackUri']='oauth/qq/callback';

//weibo Oauth config
$config['np.oauth.weibo.apikey'] = '';
$config['np.oauth.weibo.secret'] = '';
$config['np.oauth.weibo.authorizeUri']='https://www.weibo.com/service/auth2/auth';
$config['np.oauth.weibo.accessUri']='https://www.weibo.com/service/auth2/token';
$config['np.oauth.weibo.apiUri']='https://api.weibo.com';
$config['np.oauth.weibo.callbackUri']='oauth/weibo/callback';

/* End of file oauth.php */
/* Location: ./application/config/oauth.php */