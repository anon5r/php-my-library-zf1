<?php
require_once 'Zend/Db.php';
if(!defined('MY_FRAMEWORK_HOME')){
    define('MY_FRAMEWORK_HOME', dirname(__FILE__));
}

/**
 * DriverManager using Zend_Db_Adapter_Abstract ver.
 * 
 * Notice: using PHP version over 5.1.4
 * 
 * Usage sample:
 * 
 * <?php
 * // begin connect
 * $db = My_DriverManager::factory('SAMPLEDB', My_DriverManager::MYSQL);
 * $con = $db->getConnection();	// return Object is PDO
 * 
 * // prepare sql
 * $stmt = $con->prepare($sql);
 * 
 * // binding value
 * $stmt->bindValue(1, strval($keyword), PDO::PARAM_STR);
 * $stmt->bindValue(2, intval($flag), PDO::PARAM_INT);
 * 
 * // execute
 * if(!$stmt->execute()){
 * 		// error: failed to execute sql.
 * }
 * 
 * // fetch rows
 * while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
 * 		$id  = $row['id'];
 * 		$key   = $row['key'];
 * 		$value = $row['value'];
 * 		echo $id . ' => key: ' . $key . ', value: ' . $value . "\n";
 * }
 * 
 * // if you use profile this query
 * $profiler = $db->getProfiler();
 * $totalTime    = $profiler->getTotalElapsedSecs();
 * $queryCount   = $profiler->getTotalNumQueries();
 * $longestTime  = 0;
 * $longestQuery = null;
 * 
 * foreach ($profiler->getQueryProfiles() as $query) {
 *     if ($query->getElapsedSecs() > $longestTime) {
 *         $longestTime  = $query->getElapsedSecs();
 *         $longestQuery = $query->getQuery();
 *     }
 * }
 * 
 * echo 'Executed ' . $queryCount . ' queries in ' . $totalTime . ' seconds' . "\n";
 * echo 'Average query length: ' . ($totalTime / $queryCount) . ' seconds' . "\n";
 * echo 'Queries per second: ' . ($queryCount / $totalTime) . "\n";
 * echo 'Longest query length: ' . $longestTime . "\n";
 * echo 'Longest query: ' . $longestQuery . "\n";
 * 
 * // end of profiler 
 * 
 * // close connection
 * $stmt = null;
 * $con = null;
 * $db->closeConnection();
 * $db = null;
 * 
 * ?>
 *
 * @author anon <anon@anoncom.net>
 * @access Zend_Db
 * @access Zend_Config_Ini
 * @throws My_DriverManager_Exception
 * @throws Zend_Db_Adapter_Exception
 * @throws Zend_Db_Exception
 * @throws Zend_Exception
 */
class My_DriverManager {
    
    /**
     * MySQL (using PDO)
     */
    const MYSQL        = 1;
    /**
     * PostgleSQL (using PDO)
     */
    const PGSQL        = 2;
    /**
     * SQLite (using PDO)
     */
    const SQLITE       = 3;
    /**
     * Microsoft SQL Server (using PDO)
     */
    const MSSQL        = 4;
    /**
     * IBM DB2 and Informix Dynamic Server (IDS)
     * (using PDO)
     */
    const IBM          = 5;
    /**
     * Oracle (using PDO)
     */
    const OCI          = 6;
    /**
     * MySQLi
     */
    const MYSQLI       = 7;
    /**
     * Oracle
     */
    const ORACLE       = 8;
    /**
     * IBM DB2
     */
    const DB2          = 9;
    
    
    /**
     * ドライバIDを設定または取得します
     * 
     * @var int DriverManagerによるドライバID
     */
    private static $driver;
    
    /**
     * データベースとの通信に対するタイムアウト値を秒数で設定または取得します。
     * Default timeout is 2 seconds.
     * 
     * @var int timeout (sec)
     */
    private static $timeout = 2;
    
    /**
     * データベースへ接続を行います。
     * 
     * @param mixed(string|array) 接続情報
     * @param My_DriverManager::const ドライバ定数
     * @return Zend_Db_Adapter_Abstract
     * @throws My_DriverManager_Exception
     * @throws Zend_Db_Adapter_Exception
     * @throws Zend_Db_Exception
     * @throws Zend_Exception
     */
    public static function factory($dsn, $driver = self::MYSQL){
        
        
        self::$driver = $driver;
        
        if(is_array($dsn)){
            // データベースの情報が配列で定義されている場合
			// あいまい性を吸収しつつ再定義
			
            if(isset($dsn['timeout']) && intval($dsn['timeout']) > 0){
                self::setTimeout($dsn['timeout']);
                unset($dsn['timeout']);
            }else{
            	self::setTimeout(2);	// デフォルト2秒
            }
            
            $dsn = array(
                'host'        => isset($dsn['host']) ? $dsn['dsn'] : 'localhost',
                'port'		  => isset($dsn['port']) ? $dsn['port'] : self::getDefaultPort(),
            	'username'    => isset($dsn['username']) ? $dsn['username'] :$dsn['user'],
            	'password'    => isset($dsn['password']) ? $dsn['password'] : $dsn['pass'],
                'dbname'	  => isset($dsn['dbname']) ? $dsn['dbname'] : $dsn['name']
            );
            
            // 不要な情報を破棄
            foreach($dsn as $key => $val){
                if(    $key != 'host'
					&& $key != 'port'
					&& $key != 'username'
					&& $key != 'password'
					&& $key != 'dbname' 
                ){
                    unset($dsn[$key]);
                }
            }
            
        }elseif(strpos($dsn, '://') !== FALSE){
            // URI形式の文字列っぽい場合
            // ex. mysql://user:pass@host:port/dbname
            
            $parts = parse_url($dsn);
            
            self::$driver = $parts['scheme'];
            $parts['path'] = substr($parts['path'], 1);    // 'path'は頭に/を含めてしまうため、ここではそれを除去する
            
            $dsn = array(
                'host'        => $parts['host'],
                'port'		  => isset($parts['port']) ? $parts['port'] : self::getDefaultPort(),
            	'username'    => $parts['user'],
            	'password'    => $parts['pass'],
                'dbname'	  => $parts['path']
            );
            self::setTimeout(2);
            
        }elseif(ctype_alpha(substr($dsn, 0, 1)) && preg_match('/[a-zA-Z0-9_]{0,15}/', substr($dsn, 1))){
            // 接続設定名（URI形式でない文字列）の場合
            // パスの通った場所に設置されている config.db.ini の設定から読み込むものとします。
            // なお、接続設定名は1文字以上の英字で始まり、2文字目以降は英数字と_のみ使用できるものとし、
            // 設定名の長さは最大16文字までとする。
            
            $configName = $dsn;
            
            try{
                require_once('Zend/Config/Ini.php');
                $config = new Zend_Config_Ini('config.db.ini', $configName);
            }catch (Zend_Config_Exception $e){
                require_once My_FRAMEWORK_HOME . '/DriverManager/Exception.php';
                throw new My_DriverManager_Exception(
                	'got Zend_Config_Exception: ' . $e->getMessage()
                );
            }
            
            if(isset($config->db->driver)){
                $driver = self::convertDriverName($config->db->driver);
            }else{
                // 指定がない場合はデフォルトとして、MySQLであるとする
                $driver = self::MYSQL;
            }
            
            // タイムアウト設定
            if(isset($config->db->timeout) && intval($config->db->timeout) > 0){
                self::setTimeout($config->db->timeout);
            }else{
            	self::setTimeout(2);	// デフォルトは2秒
            }
            
            $dsn = array(
                'host'        => $config->db->host,
                'port'		  => isset($config->db->port) ? $config->db->port : self::getDefaultPort(),
            	'username'    => $config->db->user,
            	'password'    => $config->db->pass,
                'dbname'	  => $config->db->name,
            );
            
            // プロファイラの設定
            if(isset($config->db->profiler)){
                $dsn['profiler'] = $config->db->profiler;
            }
            
        }else{
            // 上記に当てはまらないものはすべてエラーとして処理します。
            
        }
        
        if(! self::isAliveHost($dsn['host'], intval($dsn['port']), $errmsg)){
        	// タイムアウト時に例外を発生させます。
	    	require_once MY_FRAMEWORK_HOME . '/DriverManager/Exception.php';
	        throw new My_DriverManager_Exception(
	        	'Cannot get the response from the host destination. The connection has timed out. details as, "' . $errmsg . '"'
	        );
        }
        
        $driverName = '';
        if(
            $driver != self::MYSQLI &&
            $driver != self::ORACLE &&
            $driver != self::DB2
        ){
            $driverName .= 'PDO_';
        }
        $driverName .= strtoupper(self::getDriverName());
        
        // タイムアウトを設定
        $dsn['options'] = array(
        	Zend_Db::ATTR_TIMEOUT	=> self::$timeout,
        );
        
        
        try{
            $db = Zend_Db::factory($driverName, $dsn);
        	
            // 恐らくここには来ないはずだが...
            if(!is_object($db)){
                require_once MY_FRAMEWORK_HOME . '/DriverManager/Exception.php';
                throw new My_DriverManager_Exception(
                	'could not connect to "' . self::buildUri($dsn) . '"'
                );
            }
            
            
            // FetchModeを設定
            $db->setFetchMode(Zend_Db::FETCH_ASSOC);
            
            return $db;
            
            
            
            
            
            
            
            
            
            
            
        } catch (Zend_Db_Adapter_Exception $e) {
            // ID かパスワードが間違っている、あるいは RDBMS が起動していないなど…
            require_once MY_FRAMEWORK_HOME . '/DriverManager/Exception.php';
            throw new My_DriverManager_Exception($e->getMessage(), $dsn, $e->getCode());
            
        } catch (Zend_Exception $e) {
            // factory() が指定したアダプタクラスを読み込めなかったなど…
            throw $e;
        } catch (Exception $e){
            // それ以外の何か
            throw $e;
        }
        
    }
    
    
    
    /**
     * 接続情報をURI情報に組み立てます。
     * 
     * @param array $dsn 接続情報
     * @return string
     */
    private static function buildUri($dsn){
        
        $ret = self::getDriverName() . '://';
        
        if(!empty($dsn['username'])){
            $ret .= $dsn['username'];
        }
        
        if(!empty($dsn['password'])){
            $ret .= ':' . $dsn['password'];
        }
        
        if(!empty($dsn['username']) && !empty($dsn['password'])){
            $ret .= '@';
        }
        
        $ret .= $dsn['host'];
        
        if(!empty($dsn['port'])){
            $ret .= ':' . $dsn['port'];
        }
        
        $ret .= '/' . $dsn['dbname'];
        
        return $ret;
    }
    
    /**
     * タイムアウト値を設定します
     * 
     * @param int $sec タイムアウト値（秒）
     */
    private static function setTimeout($sec){
        if(ctype_digit($sec))
            self::$timeout = intval($sec);
    }
    
    /**
     * ドライバ名を取得します
     * 
     * @return string ドライバ名
     */
    private function getDriverName(){
        switch(self::$driver){
            case self::MYSQL:
                $name = 'mysql'; break;
            case self::PGSQL:
                $name = 'pgsql'; break;
            case self::SQLITE:
                $name = 'sqlite'; break;
            case self::MSSQL:
                $name = 'mssql'; break;
            case self::OCI:
                $name = 'oci'; break;
            case self::IBM:
                $name = 'ibm'; break;
            case self::MYSQLI:
                $name = 'mysqli'; break;
            case self::ORACLE:
                $name = 'oci8'; break;
            case self::DB2:
                $name = 'db2'; break;
            default:
                $name = 'mysql'; break;
        }
        return $name;
    }
    
    /**
     * 各種データベース毎のデフォルトのポート番号を返します
     * 
     * @return int defaultPortNumber
     */
    private static function getDefaultPort(){
        switch(self::$driver){
            case self::MYSQL:
                return 3306;
            case self::PGSQL:
                return 5432;
            case self::SQLITE:
                return null;
            case self::MSSQL:
                return 1433;
            case self::OCI:
                return 1521;
            case self::IBM:
                return 50000;
            case self::MYSQLI:
                return 3306;
            case self::ORACLE:
                return 1521;
            case self::DB2:
                return 50000;
            default:
                return 3306;
        }
        
    }
    
    /**
     * ドライバ名の文字列から各ドライバの定数に変換します
     * 
     * @param string $driverName ドライバ名
     */
    private static function convertDriverName($driverName){
       
        switch(strtolower($driverName)){
            case 'mysql':
                return self::MYSQL;
            case 'postgle':
            case 'pgsql':
                return self::PGSQL;
            case 'sqlite':
                return self::SQLITE;
            case 'mssql':
                return self::MSSQL;
            case 'oracle':
            case 'oci':
                return self::OCI;
            case 'ibm':
                return self::IBM;
            case 'mysqli':
                return self::MYSQLI;
            case 'oci8':
                return self::ORACLE;
            case 'db2':
                return self::DB2;
            default:
                return self::MYSQL;
        }
    }
    
	
	/**
	 * $hostname が接続先として接続応答出来る常態かを確認します。
	 *
	 * @access private static
	 * @param string $hostname 接続先ホスト名
	 * @param int $port 接続先ポート番号
	 * @return boolean
	 */
	private static function isAliveHost($hostname = null, $port = 80, &$errmsg = null){
	    if(empty($hostname)){
	    	$errmsg = 'The host name is not specified.';
	        return false;
	    }
	    
	    // port番号指定がint型以外、あるいは
	    // port番号が65535を超える場合は不正とみなしfalse
	    if(!is_int($port) || ($port < 0 || $port > 65535)){
	    	$errmsg = 'This port number (' . $port . ') is not numeric or out of range in port number format.';
	        return false;
	    }
	    
	    // 接続確認を行います。
	    // 接続タイムアウトの設定時間はDriverManager::setTimeout()で設定した時間です。
	    $fp = @fsockopen($hostname, $port, $errno, $errmsg, self::$timeout);
	    if(!$fp){
	        return false;
	    }
	    fclose($fp);
	    
	    return true;
	    
	}
}

