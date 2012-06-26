<?php
require_once 'Zend/Http/Client.php';
require_once 'Zend/Http/Response.php';
require_once 'Zend/Http/Exception.php';
require_once 'Zend/Http/Client/Exception.php';
require_once 'My/Service/Api.php';

class My_Service_Twitter{
	
	const VERSION = '0.1.0';
	const USER_AGENT = 'Mozilla/4.0 (TwitterConroller/0.1.0;)';
	const CLIENT_NAME = 'TwitterController';
	
	const TWITTER_HOST = 'twitter.com';
	
	const FORMAT_XML = 'xml';
	const FORMAT_JSON = 'json';
	
    /**
     * My_Service_Api
     *
     * @var My_Service_Api
     */
    private $api;
    
	/**
	 * array of request headers
	 * 
	 * @var array
	 */
	protected $config;
	
	/**
	 * error message
	 *
	 * @var string
	 */
	protected $errMsg;
	
	/**
	 * error code
	 *
	 * @var int
	 */
	protected $errCode;
	
	/**
	 * login user id
	 *
	 * @var string
	 */
	protected $username;
	
	/**
	 * login password
	 *
	 * @var string
	 */
	protected $password;
	
	/**
	 * user agent
	 * 
	 * @var string
	 */
	protected $userAgent = TWITTER_CONTROLLER_USERAGENT;
	
	/**
	 * source client name
	 * 
	 * @var string
	 */
	protected $clientName = null;
	
	/**
	 * constructor
	 * コンストラクタ
	 * 
	 * @param string $username ユーザID
	 * @param string $password パスワード
	 */
	public function __construct($username, $password){
		
		// set Default
		$this->api = new My_Service_Api();
		$this->api->setRequestPort(80);
		$this->api->setRequestScheme(My_Service_Api::REQUEST_SCHEME_HTTP);
		$this->api->setRequestHost(self::TWITTER_HOST);
		$this->api->addRequestHeader('User-Agent', self::USER_AGENT);
		
		$this->username = $username;
		$this->password = $password;
	}
	
	/**
	 * set posted at client name
	 * 
	 * @param string $clientName client name
	 */
	public function setClientName($clientName){
	    $this->clientName = $clientName;
	}
	
	/**
	 * get client name
	 * 
	 * @return string client name
	 */
	public function getClientName(){
		return $this->clientName;
	}
	
	/**
	 * set request & response timeout 
	 *
	 * @access protected
	 * @param int $sec timeout time as second
	 */
	public function setTimeout($sec){
		$this->api->setTimeout($sec);
	}
	
	/**
	 * get statuses
	 * 
	 * @return My_Service_Twitter_Statuses
	 */
	public function getStatuses(){
		$status = new My_Service_Twitter_Statuses($this->username, $this->password);
		$status->setTwitterObject($this);
		
		return $status;
	}
	
	/**
	 * get My_Service_Api
	 * 
	 * @return My_Service_Api
	 */
	public function getApi(){
		return $this->api;
	}
	
	
	public function parse($response){
		
		require_once 'My/Service/Twitter/Status.php';
		require_once 'My/Service/Twitter/User.php';
		
		//$parsedData = $this->api->parse($format, $response);
		$statuses = array();
	
		if(isset($response['status'][0]) == FALSE){
			$response['status'] = array($response['status']);
			unset($response['status']['created_at']);
			unset($response['status']['id']);
			unset($response['status']['text']);
			unset($response['status']['source']);
			unset($response['status']['truncated']);
			unset($response['status']['in_reply_to_status_id']);
			unset($response['status']['in_reply_to_user_id']);
			unset($response['status']['favorited']);
			unset($response['status']['in_reply_to_screen_name']);
			unset($response['status']['user']);
		}
		//var_dump($response);
		
		if(isset($response['status'])){
			foreach($response['status'] as $datas){
				$status = new My_Service_Twitter_Status();
				
				if(isset($datas['created_at'])){
					$status->setCreatedAt($datas['created_at']);
				}
				if(isset($datas['id'])){
					$status->setId($datas['id']);
				}
				if(isset($datas['text'])){
					$status->setText($datas['text']);
				}
				if(isset($datas['source'])){
					$status->setSource($datas['source']);
				}
				if(isset($datas['truncated'])){
					$value = ($datas['truncated'] == 'true') ? true : false; 
					$status->setTruncated($value);
				}
				if(isset($datas['in_reply_to_status_id'])){ 
					$status->setInReplyToStatusId($datas['in_reply_to_status_id']);
				}
				if(isset($datas['in_reply_to_user_id'])){ 
					$status->setInReplyToUserId($datas['in_reply_to_user_id']);
				}
				if(isset($datas['in_reply_to_screen_name'])){ 
					$status->setInReplyToScreenName($datas['in_reply_to_screen_name']);
				}
				if(isset($datas['favolited'])){
					$value = ($datas['favolited'] == 'true') ? true : false; 
					$status->setFavorited($value);
				}
				
				
				if(isset($datas['user'])){
					$userData = $datas['user'];
					$user = new My_Service_Twitter_User();
					
					if(isset($userData['id'])){
						$user->setId($userData['id']);
					}
					if(isset($userData['name'])){
						$user->setName($userData['name']);
					}
					if(isset($userData['screen_name'])){
						$user->setScreenName($userData['screen_name']);
					}
					if(isset($userData['location'])){
						$user->setLocation($userData['location']);
					}
					if(isset($userData['description'])){
						$user->setDescription($userData['description']);
					}
					if(isset($userData['profile_image_url'])){
						$user->setProfileImageUrl($userData['profile_image_url']);
					}
					if(isset($userData['url'])){
						$user->setUrl($userData['url']);
					}
					if(isset($userData['protected'])){
						$value = ($userData['protected'] == 'true') ? true : false;
						$user->setProtected($value);
					}
					if(isset($userData['followers_count'])){
						$user->setFollowersCount($userData['followers_count']);
					}
					if(isset($userData['friends_count'])){
						$user->setFriendsCount($userData['friends_count']);
					}
					if(isset($userData['created_at'])){
						$user->setCreatedAt($userData['created_at']);
					}
					if(isset($userData['favolites_count'])){
						$user->setFavoritesCount($userData['favolites_count']);
					}
					if(isset($userData['utc_offset'])){
						$user->setUtcOffset($userData['utc_offset']);
					}
					if(isset($userData['timezone'])){
						$user->setTimeZone($userData['timezone']);
					}
					if(isset($userData['statuses_count'])){
						$user->setProtected($userData['statuses_count']);
					}
					if(isset($userData['notificatons'])){
						$value = ($userData['notifications'] == 'true') ? true : false;
						$user->setNotifications($value);
					}
					if(isset($userData['verified'])){
						$value = ($userData['verified'] == 'true') ? true : false;
						$user->setVerified($value);
					}
					if(isset($userData['following'])){
						$value = ($userData['followeing'] == 'true') ? true : false;
						$user->setFollowing($value);
					}
					
					$status->setUser($user);
				}
				$statuses[] = $status;
			}
		}
		//var_dump($statuses);
		return $statuses;
	}
	
	
}
