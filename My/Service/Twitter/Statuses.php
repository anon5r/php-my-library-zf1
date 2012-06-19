<?php
require_once 'Zend/Http/Client.php';
require_once 'Zend/Http/Response.php';
require_once 'Zend/Http/Exception.php';
require_once 'Zend/Http/Client/Exception.php';
require_once 'My/Service/Api.php';
require_once 'My/Service/Twitter.php';

class My_Service_Twitter_Statuses{
	
	const PARAM_ID = 'id';
	const PARAM_USER_ID = 'user_id';
	const PARAM_SCREEN_NAME = 'screen_name';
	
	/**
	 * @var My_Service_Twitter
	 */
	private $twitter = null;
	
	private $username = '';
	private $password = '';
	
	private $params = array();
	
	private $response = null;
	
	/**
	 * construct
	 * 
	 * @param string $username
	 * @param string $password
	 */
	public function __construct($username, $password){
		$this->username = $username;
		$this->password = $password;
	}
	
	/**
	 * set api object
	 * 
	 * @param My_Service_Api $api
	 */
	public function setTwitterObject(My_Service_Twitter $twitter){
		$this->twitter = $twitter;
	}
	
	/**
	 * add request parameter
	 * 
	 * @param string $name parameter name
	 * @param string $value parameter value
	 * @return My_Service_Twitter_Statuses
	 */
	public function addParameter($name, $value){
		$this->params[$name] = $value;
		return $this;
	}
	
	
	/**
	 * Updates the authenticating user's status. 
	 * Requires the status parameter specified below. 
	 * Request must be a POST. 
	 * A status update with text identical to 
	 * the authenticating user's current status will 
	 * be ignored to prevent duplicates.
	 *
	 * @access public
	 * @param string $message
	 * @return string xml
	 * @throws Zend_Http_Client_Exception
	 * @throws Zend_Http_Exception
	 * @throws Zend_Exception
	 */
	public function update($message){
		$api = $this->twitter->getApi();
	    $api->setRequestPath('/statuses/update.xml');
	    //$api->addRequestQuery('status', rawurlencode($message));
	    $api->addRequestQuery('status', $message);
	    if($this->twitter->getClientName() != null){
	        //$api->addRequestQuery('source', rawurlencode($this->twitter->getClientName()));
	        $api->addRequestQuery('source', $this->twitter->getClientName());
	    }
		
		$api->setRequestMethod(My_Service_Api::REQUEST_METHOD_POST);
		$api->setRequestAuth($this->username, $this->password, My_Service_Api::REQUEST_AUTH_METHOD_BASIC);
		
		if(count($this->params) > 0){
			foreach($this->params as $key => $value){
				if(strlen($key) == 0) continue;
				$api->addRequestQuery($key, $value);
			}
		}
		
		
	    try{
		    if($api->call()){
		    	return $api->parse(My_Service_Api::RESPONSE_DATA_FORMAT_XML);
		    }
		    return '';
	    }catch(Exception $e){
	    	unset($api);
	        throw $e;
	    }
	}
	
	/**
	 * Returns the 20 most recent statuses from 
	 * non-protected users who have set a custom user icon. 
	 * The public timeline is cached for 60 seconds 
	 * so requesting it more often than that is 
	 * a waste of resources.
	 * 
	 * @return string xml
	 * @throws Zend_Http_Client_Exception
	 * @throws Zend_Http_Exception
	 * @throws Zend_Exception
	 */
	public function getPublicTimeline(){
		$api = $this->twitter->getApi();
	    $api->setRequestPath('/statuses/public_timeline.xml');
		$api->setRequestMethod(My_Service_Api::REQUEST_METHOD_GET);
		
		if(count($this->params) > 0){
			foreach($this->params as $key => $value){
				if(strlen($key) == 0) continue;
				$api->addRequestQuery($key, $value);
			}
		}
		
	    try{
		    if($api->call()){
		    	return $api->parse(My_Service_Api::RESPONSE_DATA_FORMAT_XML);
		    }
		    return '';
	    }catch(Exception $e){
	    	unset($api);
	        throw $e;
	    }
	}
	
	
	/**
	 * Returns the 20 most recent statuses posted by 
	 * the authenticating user and that user's friends. 
	 * This is the equivalent of /timeline/home on the Web.
	 * 
	 * @return string xml
	 * @throws Zend_Http_Client_Exception
	 * @throws Zend_Http_Exception
	 * @throws Zend_Exception
	 */
	public function getFriendsTimeline(){
		$api = $this->twitter->getApi();
	    $api->setRequestPath('/statuses/friends_timeline.xml');
		
		$api->setRequestMethod(My_Service_Api::REQUEST_METHOD_GET);
		$api->setRequestAuth($this->username, $this->password, My_Service_Api::REQUEST_AUTH_METHOD_BASIC);
		
		if(count($this->params) > 0){
			foreach($this->params as $key => $value){
				if(strlen($key) == 0) continue;
				$api->addRequestQuery($key, $value);
			}
		}
		
		
	    try{
		    if($api->call()){
		    	return $api->parse(My_Service_Api::RESPONSE_DATA_FORMAT_XML);
		    }
		    return '';
	    }catch(Exception $e){
	    	unset($api);
	        throw $e;
	    }
	}
	

	/**
	 * Returns the 20 most recent statuses posted from 
	 * the authenticating user. It's also possible to 
	 * request another user's timeline via the id parameter. 
	 * This is the equivalent of the Web /<user> page 
	 * for your own user, or the profile page for a third party.
	 * 
	 * @param string|null $param parameter
	 * @param string|null $user user id or user name
	 * @return string xml
	 * @throws Zend_Http_Client_Exception
	 * @throws Zend_Http_Exception
	 * @throws Zend_Exception
	 */
	public function getUserTimeline($param = null, $user = null){
		$api = $this->twitter->getApi();
		
		if($param === self::PARAM_ID){
			$api->setRequestPath('/statuses/user_timeline/' . $user . '.xml');
		}else{
		    $api->setRequestPath('/statuses/user_timeline.xml');
		}
		
		switch($param){
			case self::PARAM_USER_ID:
			case self::PARAM_SCREEN_NAME:
				if($user !== NULL){
					$api->addRequestQuery($param, $user);
				}
				break;
		}
		
		$api->setRequestMethod(My_Service_Api::REQUEST_METHOD_GET);
		$api->setRequestAuth($this->username, $this->password, My_Service_Api::REQUEST_AUTH_METHOD_BASIC);
		
		if(count($this->params) > 0){
			foreach($this->params as $key => $value){
				if(strlen($key) == 0) continue;
				$api->addRequestQuery($key, $value);
			}
		}
		
		
	    try{
		    if($api->call()){
		    	return $api->parse(My_Service_Api::RESPONSE_DATA_FORMAT_XML);
		    }
		    return '';
	    }catch(Exception $e){
	    	unset($api);
	        throw $e;
	    }
	}
	

	/**
	 * Returns a single status, specified by the id parameter below.
	 * The status's author will be returned inline.
	 * 
	 * @param long $id The numerical ID of the status to retrieve.
	 * @return string xml
	 * @throws Zend_Http_Client_Exception
	 * @throws Zend_Http_Exception
	 * @throws Zend_Exception
	 */
	public function show($id){
		$api = $this->twitter->getApi();
		$api->setRequestPath('/statuses/show/' . $id . '.xml');
		
		switch($param){
			case self::PARAM_USER_ID:
			case self::PARAM_SCREEN_NAME:
				if($user !== NULL){
					$api->addRequestQuery($param, $user);
				}
				break;
		}
		
		$api->setRequestMethod(My_Service_Api::REQUEST_METHOD_GET);
		// unless the author of the status is protected
		//$api->setRequestAuth($this->username, $this->password, My_Service_Api::REQUEST_AUTH_METHOD_BASIC);
		
		if(count($this->params) > 0){
			foreach($this->params as $key => $value){
				if(strlen($key) == 0) continue;
				$api->addRequestQuery($key, $value);
			}
		}
		
		
	    try{
		    if($api->call()){
		    	return $api->parse(My_Service_Api::RESPONSE_DATA_FORMAT_XML);
		    }
		    return '';
	    }catch(Exception $e){
	    	unset($api);
	        throw $e;
	    }
	}
	

	/**
	 * Destroys the status specified by the required ID parameter. 
	 * The authenticating user must be the author of the specified status.
	 * 
	 * @param long $id The numerical ID of the status to retrieve.
	 * @return string xml
	 * @throws Zend_Http_Client_Exception
	 * @throws Zend_Http_Exception
	 * @throws Zend_Exception
	 */
	public function destroy($id){
		$api = $this->twitter->getApi();
		$api->setRequestPath('/statuses/destroy/' . $id . '.xml');
		
		switch($param){
			case self::PARAM_USER_ID:
			case self::PARAM_SCREEN_NAME:
				if($user !== NULL){
					$api->addRequestQuery($param, $user);
				}
				break;
		}
		
		$api->setRequestMethod(My_Service_Api::REQUEST_METHOD_POST);
		$api->setRequestAuth($this->username, $this->password, My_Service_Api::REQUEST_AUTH_METHOD_BASIC);
		
		if(count($this->params) > 0){
			foreach($this->params as $key => $value){
				if(strlen($key) == 0) continue;
				$api->addRequestQuery($key, $value);
			}
		}
		
		
	    try{
		    if($api->call()){
		    	return $api->parse(My_Service_Api::RESPONSE_DATA_FORMAT_XML);
		    }
		    return '';
	    }catch(Exception $e){
	    	unset($api);
	        throw $e;
	    }
	}
	
	
	/**
	 * Returns a user's friends, each with current status inline. 
	 * They are ordered by the order in which they were added as friends, 
	 * 100 at a time. (Please note that the result set isn't guaranteed 
	 * to be 100 every time as suspended users will be filtered out.) 
	 * Use the page option to access older friends. With no user specified, 
	 * request defaults to the authenticated user's friends. 
	 * It's also possible to request another user's friends list via the id, 
	 * screen_name or user_id parameter.
	 * 
	 * @param string|null $param parameter
	 * @param string|null $user user id or user name
	 * @return string xml
	 * @throws Zend_Http_Client_Exception
	 * @throws Zend_Http_Exception
	 * @throws Zend_Exception
	 */
	public function getFriends($param = null, $user = null){
	
		$api = $this->twitter->getApi();
		if($param === self::PARAM_ID){
			$api->setRequestPath('/statuses/friends/' . $user . '.xml');
		}else{
	    	$api->setRequestPath('/statuses/friends.xml');
		}
		
		switch($param){
			case self::PARAM_USER_ID:
			case self::PARAM_SCREEN_NAME:
				if($user !== NULL){
					$api->addRequestQuery($param, $user);
				}
				break;
		}
		
		$api->setRequestMethod(My_Service_Api::REQUEST_METHOD_GET);
		
		if(count($this->params) > 0){
			foreach($this->params as $key => $value){
				if(strlen($key) == 0) continue;
				$api->addRequestQuery($key, $value);
			}
		}
		
		
	    try{
		    if($api->call()){
		    	return $api->parse(My_Service_Api::RESPONSE_DATA_FORMAT_XML);
		    }
		    return '';
	    }catch(Exception $e){
	    	unset($api);
	        throw $e;
	    }
	}
	
	
	/**
	 * Returns the authenticating user's followers, each with current status inline. 
	 * They are ordered by the order in which they joined Twitter, 100 at a time. 
	 * (Please note that the result set isn't guaranteed to be 100 every 
	 * time as suspended users will be filtered out.) 
	 * Use the page option to access earlier followers.
	 * 
	 * @param string|null $param parameter
	 * @param string|null $user user id or user name
	 * @return string xml
	 * @throws Zend_Http_Client_Exception
	 * @throws Zend_Http_Exception
	 * @throws Zend_Exception
	 */
	public function getFollowers($param = null, $user = null){
	
		$api = $this->twitter->getApi();
		if($param === self::PARAM_ID){
			$api->setRequestPath('/statuses/followers/' . $user . '.xml');
		}else{
	    	$api->setRequestPath('/statuses/followers.xml');
		}
		
		switch($param){
			case self::PARAM_USER_ID:
			case self::PARAM_SCREEN_NAME:
				if($user !== NULL){
					$api->addRequestQuery($param, $user);
				}
				break;
		}
		
		$api->setRequestMethod(My_Service_Api::REQUEST_METHOD_GET);
		$api->setRequestAuth($this->username, $this->password, My_Service_Api::REQUEST_AUTH_METHOD_BASIC);
		
		if(count($this->params) > 0){
			foreach($this->params as $key => $value){
				if(strlen($key) == 0) continue;
				$api->addRequestQuery($key, $value);
			}
		}
		
	    try{
		    if($api->call()){
		    	return $api->parse(My_Service_Api::RESPONSE_DATA_FORMAT_XML);
		    }
		    return '';
	    }catch(Exception $e){
	    	unset($api);
	        throw $e;
	    }
	}
	
	
	/**
	 * Returns the 20 most recent mentions 
	 * (status containing @username) for 
	 * the authenticating user.
	 * 
	 * @return string xml
	 * @throws Zend_Http_Client_Exception
	 * @throws Zend_Http_Exception
	 * @throws Zend_Exception
	 */
	public function getMentions(){
	
		$api = $this->twitter->getApi();
	    $api->setRequestPath('/statuses/mentions.xml');
		
		$api->setRequestMethod(My_Service_Api::REQUEST_METHOD_GET);
		$api->setRequestAuth($this->username, $this->password, My_Service_Api::REQUEST_AUTH_METHOD_BASIC);
		
		if(count($this->params) > 0){
			foreach($this->params as $key => $value){
				if(strlen($key) == 0) continue;
				$api->addRequestQuery($key, $value);
			}
		}
		
	    try{
		    if($api->call()){
		    	return $api->parse(My_Service_Api::RESPONSE_DATA_FORMAT_XML);
		    }
		    return '';
	    }catch(Exception $e){
	    	unset($api);
	        throw $e;
	    }
	}
}