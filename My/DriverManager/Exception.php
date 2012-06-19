<?php
require_once 'My/Exception.php';

/**
 * Froute DriverManager Exception
 * 
 * Notice: using PHP version over 5.1.4
 *
 * @package Froute
 * @author anon <anon@anoncom.net>
 * @copyright anoncom.net.
 */
class My_DriverManager_Exception extends My_Exception {
    
    private $conServer;
    private $conPort;
    private $conUser;
    private $conPass;
    private $conDB;
    
    private $errorMessage;
    private $errorCode;
    
    public function __construct($errMsg = NULL, $config = NULL, $errCode = NULL){
        if($config !== NULL){
            $this->conServer = $config['host'];
            $this->conPort = $config['port'];
            $this->conUser = $config['username'];
            $this->conPass = $config['password'];
            $this->conDB = $config['dbname'];
        }
        
        parent::__construct($errMsg, $errCode);
    }
    
    /**
     * 接続先ホスト名を返します。
     */
    public function getConnectionHostName(){
        return $this->conServer;
    }
    
    /**
     * 接続先ポート番号を返します。
     */
    public function getConnectionPort(){
        return $this->conPort;
    }
    
    /**
     * 接続ユーザ名を返します。
     */
    public function getConnectionUserName(){
        return $this->conUser;
    }
    
    /**
     * 接続時に使用したパスワードを返します。
     */
    public function getConnectionPassword(){
        return $this->conPass;
    }

    /**
     * 接続先データベース名を返します。
     */
    public function getConnectionDB(){
        return $this->conDB;
    }
    
}