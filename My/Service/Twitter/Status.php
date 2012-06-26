<?php

class My_Service_Twitter_Status{
	
	/**
	 * created status on date
	 * 
	 * @var string date
	 */
	private $createdAt = '';
	
	/**
	 * status id
	 * 
	 * @var long
	 */
	private $id = 0;
	
	/**
	 * status updated text
	 * 
	 * @var string
	 */
	private $text = '';
	
	/**
	 * status updated client name
	 * 
	 * @var string
	 */
	private $source = '';
	
	/**
	 * statis truncated
	 * 
	 * @var bool
	 */
	private $truncated = false;
	
	/**
	 * status updated in reply to status id
	 * 
	 * @var long
	 */
	private $inReplyToStatusId = 0;
	
	/**
	 * status updated in reply to user id
	 * 
	 * @var long
	 */
	private $inReplyToUserId = 0;
	
	/**
	 * status updated in reply to user screen name
	 * 
	 * @var string
	 */
	private $inReplyToScreenName = '';
	
	/**
	 * favolited status
	 * 
	 * @var bool
	 */
	private $favorited = false;
	
	/**
	 * user info 
	 * 
	 * @var My_Service_Twitter_User
	 */
	private $user = null;
	
	
	
	
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
	 * @return bool
	 */
	public function getFavorited(){
		return $this->favorited;
	}
	
	/**
	 * @param bool $favorited
	 */
	public function setFavorited($favorited){
		$this->favorited = $favorited;
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
	public function getInReplyToScreenName(){
		return $this->inReplyToScreenName;
	}
	
	/**
	 * @param string $inReplyToScreenName
	 */
	public function setInReplyToScreenName($inReplyToScreenName){
		$this->inReplyToScreenName = $inReplyToScreenName;
	}
	
	/**
	 * @return long
	 */
	public function getInReplyToStatusId(){
		return $this->inReplyToStatusId;
	}
	
	/**
	 * @param int $inReplyToStatusId
	 */
	public function setInReplyToStatusId($inReplyToStatusId){
		$this->inReplyToStatusId = $inReplyToStatusId;
	}
	
	/**
	 * @return long
	 */
	public function getInReplyToUserId(){
		return $this->inReplyToUserId;
	}
	
	/**
	 * @param long $inReplyToUserId
	 */
	public function setInReplyToUserId($inReplyToUserId){
		$this->inReplyToUserId = $inReplyToUserId;
	}
	
	/**
	 * @return string
	 */
	public function getSource(){
		return $this->source;
	}
	
	/**
	 * @param string $source
	 */
	public function setSource($source){
		$this->source = $source;
	}
	
	/**
	 * @return string
	 */
	public function getText(){
		return $this->text;
	}
	
	/**
	 * @param string $text
	 */
	public function setText($text){
		$this->text = $text;
	}
	
	/**
	 * @return bool
	 */
	public function getTruncated(){
		return $this->truncated;
	}
	
	/**
	 * @param bool $truncated
	 */
	public function setTruncated($truncated){
		$this->truncated = $truncated;
	}
	
	/**
	 * @return My_Service_Twitter_User
	 */
	public function getUser(){
		return $this->user;
	}
	
	/**
	 * @param My_Service_Twitter_User $user
	 */
	public function setUser($user){
		$this->user = $user;
	}

}