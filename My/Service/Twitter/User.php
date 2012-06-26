<?php
require_once 'My/Service/Twitter/Status.php';

class My_Service_Twitter_User{
	
	/**
	 * user unique id
	 * 
	 * @var long
	 */
	private $id = 0;
	
	/**
	 * user account name
	 * 
	 * @var string
	 */
	private $name = '';
	
	/**
	 * user screen name
	 * 
	 * @var string
	 */
	private $screenName = '';
	
	/**
	 * user location
	 * 
	 * @var string
	 */
	private $location = '';
	
	/**
	 * user profile description
	 * 
	 * @var string
	 */
	private $description = '';
	
	/**
	 * user icon image url
	 * 
	 * @var string
	 */
	private $profileImageUrl = '';
	
	/**
	 * user profile url
	 * 
	 * @var string url
	 */
	private $url = '';
	
	/**
	 * protected user
	 * 
	 * @var bool
	 */
	private $protected = false;
	
	/**
	 * user followed counts
	 * 
	 * @var long
	 */
	private $followersCount = 0;
	
	/**
	 * user friends counts
	 * 
	 * @var long
	 */
	private $friendsCount = 0;
	/**
	 * user created account on date
	 * 
	 * @var string date
	 */
	private $createdAt = '';
	
	/**
	 * user favorites counts
	 * 
	 * @var long
	 */
	private $favoritesCount = 0;
	
	/**
	 * UTC offsets
	 * 
	 * @var long
	 */
	private $utcOffset = 0;
	
	/**
	 * user timezone name
	 * 
	 * @var string
	 */
	private $timeZone = '';
	
	/**
	 * user posts comment count
	 * 
	 * @var long
	 */
	private $statusesCount = 0;
	
	/**
	 * notification future
	 * 
	 * @var bool
	 */
	private $notifications = false;
	
	/**
	 * now following user
	 * 
	 * @var bool
	 */
	private $following = false;
	
	/**
	 * verified famous user account
	 * 
	 * @var bool
	 */
	private $verified = false;
	
	/**
	 * twitter status object
	 * 
	 * @var My_Service_Twitter_Status
	 */
	private $status = null;
	
	/**
	 * @return string
	 */
	public function getCreatedAt(){
		return $this->createdAt;
	}
	
	/**
	 * @param string $createdAt
	 */
	public function setCreatedAt($createdAt){
		$this->createdAt = $createdAt;
	}
	
	/**
	 * @return string
	 */
	public function getDescription(){
		return $this->description;
	}
	
	/**
	 * @param string $description
	 */
	public function setDescription($description){
		$this->description = $description;
	}
	
	/**
	 * @return long
	 */
	public function getFavoritesCount(){
		return $this->favoritesCount;
	}
	
	/**
	 * @param long $favoritesCount
	 */
	public function setFavoritesCount($favoritesCount){
		$this->favoritesCount = $favoritesCount;
	}
	
	/**
	 * @return long
	 */
	public function getFollowersCount(){
		return $this->followersCount;
	}
	
	/**
	 * @param long $followersCount
	 */
	public function setFollowersCount($followersCount){
		$this->followersCount = $followersCount;
	}
	
	/**
	 * @return bool
	 */
	public function getFollowing(){
		return $this->following;
	}
	
	/**
	 * @param bool $following
	 */
	public function setFollowing($following){
		$this->following = $following;
	}
	
	/**
	 * @return long
	 */
	public function getFriendsCount(){
		return $this->friendsCount;
	}
	
	/**
	 * @param long $friendsCount
	 */
	public function setFriendsCount($friendsCount){
		$this->friendsCount = $friendsCount;
	}
	
	/**
	 * @return long
	 */
	public function getId(){
		return $this->id;
	}
	
	/**
	 * @param long $id
	 */
	public function setId($id){
		$this->id = $id;
	}
	
	/**
	 * @return string
	 */
	public function getLocation(){
		return $this->location;
	}
	
	/**
	 * @param string $location
	 */
	public function setLocation($location){
		$this->location = $location;
	}
	
	/**
	 * @return string
	 */
	public function getName(){
		return $this->name;
	}
	
	/**
	 * @param string $name
	 */
	public function setName($name){
		$this->name = $name;
	}
	
	/**
	 * @return bool
	 */
	public function getNotifications(){
		return $this->notifications;
	}
	
	/**
	 * @param bool $notifications
	 */
	public function setNotifications($notifications){
		$this->notifications = $notifications;
	}
	
	/**
	 * @return string
	 */
	public function getProfileImageUrl(){
		return $this->profileImageUrl;
	}
	
	/**
	 * @param string $profileImageUrl
	 */
	public function setProfileImageUrl($profileImageUrl){
		$this->profileImageUrl = $profileImageUrl;
	}
	
	/**
	 * @return bool
	 */
	public function getProtected(){
		return $this->protected;
	}
	
	/**
	 * @param bool $protected
	 */
	public function setProtected($protected){
		$this->protected = $protected;
	}
	
	/**
	 * @return string
	 */
	public function getScreenName(){
		return $this->screenName;
	}
	
	/**
	 * @param string $screenName
	 */
	public function setScreenName($screenName){
		$this->screenName = $screenName;
	}
	
	/**
	 * @return long
	 */
	public function getStatusesCount(){
		return $this->statusesCount;
	}
	
	/**
	 * @param long $statusesCount
	 */
	public function setStatusesCount($statusesCount){
		$this->statusesCount = $statusesCount;
	}
	
	/**
	 * @return string
	 */
	public function getTimeZone(){
		return $this->timeZone;
	}
	
	/**
	 * @param string $timeZone
	 */
	public function setTimeZone($timeZone){
		$this->timeZone = $timeZone;
	}
	
	/**
	 * @return string
	 */
	public function getUrl(){
		return $this->url;
	}
	
	/**
	 * @param string $url
	 */
	public function setUrl($url){
		$this->url = $url;
	}
	
	/**
	 * @return long
	 */
	public function getUtcOffset(){
		return $this->utcOffset;
	}
	
	/**
	 * @param long $utcOffset
	 */
	public function setUtcOffset($utcOffset){
		$this->utcOffset = $utcOffset;
	}
	
	/**
	 * @return bool
	 */
	public function getVerified(){
		return $this->verified;
	}
	
	/**
	 * @param bool $verified
	 */
	public function setVerified($verified){
		$this->verified = $verified;
	}
	
	/**
	 * @return My_Service_Twitter_Status
	 */
	public function getStatus(){
		return $this->status;
	}
	
	/**
	 * @param bool $verified
	 */
	public function setStatus(My_Service_Twitter_Status $status){
		$this->status = $status;
	}
}