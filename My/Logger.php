<?php
require_once 'Zend/Log.php';
require_once 'Zend/Log/Writer/Stream.php';
require_once 'My/Utility.php';

/**
 * Provides output to a log file.
 * This class based on Zend_Log by ZendFramework.
 * 
 * @copyright anoncom.net
 * @author anon <anon@anoncom.net>
 */
class My_Logger extends Zend_Log
{
	
	
	// Log levels
	
	/**
	 * Emergency: system is unusable
	 * @staticvar int
	 */
	const EMERGENCY				= 0;
	
	/**
	 * Alert: action must be taken immediately
	 * @staticvar int
	 */ 
	const ALERT					= 1;
	
	/**
	 * Critical: critical conditions
	 * @staticvar int
	 */
	const CRITICAL				= 2;
	
	/**
	 * Error: error conditions
	 * @staticvar int
	 */
	const ERROR					= 3;
	
	/**
	 * Warning: warning conditions
	 * @staticvar int
	 */
	const WARNING				= 4;
	
	/**
	 * Notice: normal but significant condition
	 * @staticvar int
	 */
	const NOTICE				= 5;
	
	/**
	 * Informational: informational messages
	 * @staticvar int
	 */
	const INFO					= 6;
	
	/**
	 * Debug: debug messages
	 * @staticvar int
	 */
	const DEBUG					= 7;
	
	
	
	
	// Rotate logs
	
	/**
	 * File rotate by date
	 * @staticvar int
	 */
	const ROTATE_DATE			= 31;
	
	/**
	 * File rotate by time
	 * @staticvar int
	 */ 
	const ROTATE_TIME			= 32;
	
	/**
	 * File rotate by size
	 * @staticvar int
	 */
	const ROTATE_SIZE			= 33; 
	
	
	
	// Rotate logfile name
	
	/**
	 * Numbering filename
	 * @staticvar int
	 */
	const ROTATE_NAME_NUMBER	= 41;
	
	/**
	 * Hourly filename
	 * @staticvar int
	 */
	const ROTATE_NAME_HOURLY	= 42;
	
	/**
	 * Daily filename
	 * @staticvar int
	 */
	const ROTATE_NAME_DAILY		= 43;
	
	/**
	 * Monthly filename
	 * @staticvar int
	 */
	const ROTATE_NAME_MONTHLY	= 44;
	
	/**
	 * Yearly filename
	 * @staticvar int
	 */
	const ROTATE_NAME_YEARLY	= 45;
	
	
	// log filename
	private $logName;
	private $method;
	
	/**
	 * self instance
	 *
	 * @var My_Logger
	 */
	private $instance;
	
	/**
	 * not output log flag
	 *
	 * @var bool
	 */
	//private static $noOutput = false;
	
	
	
	/**
	 * @var array of priorities where the keys are the
	 * priority numbers and the values are the priority names
	 */
	protected $_priorities = array();
	
	
	private $_encoding = 'UTF-8';
	
	
	/**
	 * rotate mode
	 * if set false to disabled.
	 *
	 * @var int|bool
	 */
	private $rotateMode = self::ROTATE_DATE;
	private $rotateAge = true;
	private $rotateName = self::ROTATE_NAME_NUMBER;
	
	
	
	public function __constract($writer){
		parent::__construct($writer);
		
		$this->rotateMode = self::ROTATE_DATE;
		$this->rotateName = self::ROTATE_NAME_NUMBER;
		$this->rotateAge  = 3;
	}
	
	/**
	 * Create logger
	 * 
	 * @param string $logName log 
	 * @param string $logfile output log file path
	 * @param int $logLevel output log level
	 * @return My_Logger
	 * @throws My_Exception
	 */
	public static function factory($logName, $logfile = '', $logLevel = self::DEBUG){
		
		// Set to write to log file path
		if($logfile == ''){
			if(ini_get('error_log') == ''){
				//$path = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'logs';
				$path = '/logs/lang/php/Framework/My';
			}else{
				$path = dirname(ini_get('error_log'));
			}
			if(file_exists($path)){
				$logfile = $path . DIRECTORY_SEPARATOR . $logName . '.log';
			}else{
				if ( PHP_OS == 'WIN32' || PHP_OS == 'WINNT' ) {
					$logfile = 'C:\\';
					if ( file_exists( basename ( $logfile ) ) == FALSE ) {
						mkdir(basename ( $logfile ), 0777, true );
					}
				} else {
					$logfile = '/var/log';
				}
			}
		}else{
			if(is_dir($logfile)){
				$logfile = $logfile . DIRECTORY_SEPARATOR . $logName . '.log';
			} else {
				// on set to write current directory...
				$logfile = dirname(__FILE__) . DIRECTORY_SEPARATOR . $logName . '.log';
			}
		}
		
		if ( self::isMaximumFileSize($logfile) ) {
			//self::$noOutput = true; 
			require_once 'My/Exception.php';
			throw new My_Exception( 'The log file "' . $logfile . '" size over 2GBytes.' );
		}
		
		// Check write permit
		if( !self::isWritable($logfile) ){
			//self::$noOutput = true;
			require_once 'My/Exception.php';
			throw new My_Exception('The log file "' . $logfile . '" cannot write.');
		}
		
		
		// Logging format
		$event = array(
			'method' => __FUNCTION__,
		);
		
		if($instance instanceof My_Logger === FALSE){
			$instance = new My_Logger();
		}
		
		if($instance->rotate !== FALSE  && file_exists($instance->logFile)){
			$isRotate = false;
			switch($instance->rotate){
				case MY_LOGGER_ROTATE_DATE:
					$lastupdate = date('Ymd', filemtime($instance->logFile));
					if($lastupdate < date('Ymd')){ $isRotate = true; }
					break;
				case MY_LOGGER_ROTATE_TIME:
					$lastupdate = date('YmdH', filemtime($instance->logFile));
					if($lastupdate < date('YmdH')){ $isRotate = true; }
					break;
				case MY_LOGGER_ROTATE_SIZE:
					if(filesize($instance->logFile) >= (10 * 1024) * 1024){
						// 10M absolute ( interim )
						$isRotate = true;
					}
					break;
			}
			if($isRotate === TRUE && file_exists($instance->logFile)){
				// rotate log files
				$instance->rotateLogs();
			}
		}
		
		$instance->logName = $logName;
		
		$writer = new Zend_Log_Writer_Stream($logfile);
		$formatter = new Zend_Log_Formatter_Simple(
			'%date% %time% [%priorityName%]: %message%' . PHP_EOL
		);
		$formatter->format($event);
		
		$writer->setFormatter($formatter);
		
		$filter = new Zend_Log_Filter_Priority($logLevel);
		$instance->addFilter($filter);
		
		$instance->addWriter($writer);
		
		return $instance;
	}
	
	
	/**
	 * set saving age for rotated log file 
	 *
	 * @param int|false $age saving age for log file
	 */
	public function setRotateAge($age){
		if(ctype_digit(strval($age)) || $age === FALSE){
			$this->rotateAge = $age;
		}else{
			throw new My_Logger_Exception('Invalid value: ' . $age);
		}
	}
	
	/**
	 * set rotate mode for log file 
	 *
	 * @param int|false $mode false | ROTATE_DATE | ROTATE_TIME | ROTATE_SIZE
	 */
	public function setRotateMode($mode){
		if(ctype_digit(strval($mode)) || $mode === FALSE){
			$this->rotateMode = $mode;
		}else{
			throw new My_Logger_Exception('Invalid value: ' . $mode);
		}
	}
	
	/**
	 * set rotate log file naming style 
	 *
	 * @param int|false $style false | ROTATE_NAME_NUMBER | ROTATE_NAME_HOURY | ROTATE_NAME_DAILY | ROTATE_NAME_MONTHLY | ROTATE_NAME_YEARLY
	 */
	public function setRotateNameStyle($style){
		if(ctype_digit(strval($style)) || $style === FALSE){
			$this->rotateName = $style;
		}else{
			throw new My_Logger_Exception('Invalid value: ' . $style);
		}
	}
	
	/**
	 * set output log string encoding
	 *
	 * @param string $encodingName
	 */
	public function setEncoding( $encodingName ) {
		
		$supported = false;
		
		foreach( mb_list_encodings() as $supportedEncoding ) {
			if ( $supportedEncoding == $encodingName ) {
				$supported = true; break;
			}
		}
		
		if ( $supported == FALSE ) {
			require_once 'My/Exception.php';
			throw new My_Exception ( 'Unsupported character encoding specified.' );
			//trigger_error( 'Unsupported character encoding specified.', E_USER_WARNING );
			$this->_encoding = 'UTF-8';
			return;
		}
		
		$this->_encoding = $encodingName;
	}
	
	/**
	 * Log a message at a priority
	 *
	 * @param  string   $message   Message to log
	 * @param  integer  $priority  Priority of message
	 * @return void
	 * @throws Zend_Log_Exception
	 */
	public function log($message, $priority)
	{
		
		//if ( self::$noOutput == true ) {
		//	// logger was no output logs.
		//	return;
		//}
		
		// sanity checks
		if ( empty( $this->_writers ) ) {
			throw new Zend_Log_Exception( 'No writers were added' );
		}

		if ( isset( $this->_priorities[$priority] ) == FALSE ) {
			throw new Zend_Log_Exception( 'Bad log priority' );
		}
		
		// The unified multi-byte character encoding inside the code when to logging.
		$msgEncoding = mb_detect_encoding( $message, 'UTF-8, eucjp-win, EUC-JP, sjis-win, SJIS, JIS, ASCII' );
		if( ( $msgEncoding != 'ASCII' ) && ( strtolower( $msgEncoding ) != strtolower( $this->_encoding ) ) ){
			$message = mb_convert_encoding( $message, $this->_encoding, $msgEncoding );
		}

		// pack into event required by filters and writers
		$event = array_merge(
			array(
				'timestamp'	   => date('c'),
				'date'		 => date('Y-m-d'),
				'time'		 => date('H:i:s'),
				'message'	  => $message,
				'priority'	 => $priority,
				'priorityName' => self::replaceWrapedLogPriorityName($this->_priorities[$priority]),
			),
			$this->_extras
		);

		// abort if rejected by the global filters
		foreach ( $this->_filters as $filter ) {
			if ( $filter->accept( $event ) == FALSE ) {
				return;
			}
		}

		// send to each writer
		foreach ($this->_writers as $writer) {
			$writer->write($event);
		}
	}
	
	/**
	 * Returns TRUE if the $filename is writable, or FALSE otherwise.
	 * This function uses the PHP include_path, where PHP's is_writable()
	 * does not.
	 *
	 * @param string   $filename
	 * @return boolean
	 */
	public static function isWritable( $filename ){
		
		if (!$fh = @fopen($filename, 'a+', true)) {
			return false;
		}
		@fclose($fh);
		return true;
	}
	
	
	/**
	 * Check the file size under the 2GBytes.
	 * 
	 * @param string $filename Filename
	 * @return bool true: 2GBytes size over file. / false: under the 2GBytes file.
	 */
	public static function isMaximumFileSize ( $filename ) {
		if ( file_exists ( $filename ) == FALSE ){
			return false;
		}
		$bytes = sprintf("%u", filesize ( $filename ) );
		$fileSize = self::formatBytes ($bytes , 2 );
		list ( $size, $unit ) = explode ( ' ', $fileSize );
		if ( $unit == 'GB' && $size >= 1.99 ) {
			return true;
		}
		return false;
	}
	
	
	
	/**
	 * format bytes size
	 *
	 * @param int $bytes
	 * @param int $precision
	 * @return string ex: "2 GB"
	 */
	public static function formatBytes($bytes, $precision = 2) {
		$units = array('B', 'KB', 'MB', 'GB', 'TB');
		$bytes = max($bytes, 0);
		$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
		$pow = min($pow, count($units) - 1);
		$bytes /= pow(1024, $pow);
		return round($bytes, $precision) . ' ' . $units[$pow];
	}
	
	/**
	 * Emergency log writer wrapper
	 */
	public function emergency($value){
		$this->emerg($value);
	}
	/**
	 * Error log writer wrapper
	 */
	public function error($value){
		$this->err($value);
	}
	/**
	 * Warning log writer wrapper
	 */
	public function warning($value){
		$this->warn($value);
	}
	
	/**
	 * Rename log priority name when output log
	 * 
	 * @param string $priorityName
	 */
	private static function replaceWrapedLogPriorityName($priorityName){
		switch(strtoupper($priorityName)){
			case 'EMERG':   return 'EMERGENCY';
			case 'CRIT':	return 'CRITICAL';
			case 'ERR':	 return 'ERROR';
			case 'WARN':	return 'WARNING';
		}
		return $priorityName;
	}
	

	
	/**
	 * log rotation
	 * 
	 * @return void
	 */
	function rotateLogs(){
		
		switch($this->rotate){
			case MY_LOGGER_ROTATE_DATE:
				switch($this->rotateName){
					case MY_LOGGER_ROTATE_NAME_DAILY:
					case MY_LOGGER_ROTATE_NAME_MONTHLY:
					case MY_LOGGER_ROTATE_NAME_YEARLY:
						break;
					default:
						$this->rotateName = MY_LOGGER_ROTATE_NAME_NUMBER;
						break;
				}
				break;
			case MY_LOGGER_ROTATE_TIME:
				if($this->rotateName !== MY_LOGGER_ROTATE_NAME_HOURLY){
					$this->rotaLogger= MY_LOGGER_ROTATE_NAME_NUMBER;
				}
				break;
			case MY_LOGGER_ROTATE_SIZE:
				$this->rotateName = MY_LOGGER_ROTATE_NAME_NUMBER;
				break;
		}
		
		// log file directory path
		$logDir = dirname($this->logFile);
		
		// get file list from log directory
		$d = dir($logDir);
		$logs = array();
		$logFileBase = basename($this->logFile);
		while (false !== ($log = $d->read())) {
			if(My_Utility::startsWith($log, '.')) continue;	// ignore dot files.
			if(preg_match('|^' . $logFileBase . '\.(\d+)$|', $log, $tmp)){
				$ages[] = $tmp[1];
				$logs[$tmp[1]] = $log;
			}
		}
		
		// when not found old generation
		if(count($logs) < 1 && $this->rotateName === MY_LOGGER_ROTATE_NAME_NUMBER){
			rename($this->logFile, $this->logFile . '.1');
			return;
		}
		
		
		
		
		if($this->rotateName !== MY_LOGGER_ROTATE_NAME_NUMBER){
			$maxAge = count($logs) - 1;
			// when not use numbering filename
			switch($this->rotateName){
				case MY_LOGGER_ROTATE_NAME_HOURLY:
					$option = 'Ymd.H';
					break;
				case MY_LOGGER_ROTATE_NAME_DAILY:
					$option = 'Ymdd';
					break;
				case MY_LOGGER_ROTATE_NAME_MONTHLY:
					$option = 'Ym';
					break;
				case MY_LOGGER_ROTATE_NAME_YEARLY:
					$option = 'Y';
					break;
			}
			rename($this->logFile, $this->logFile . '.' . date($option, filetime($this->logFile)));
			
		}else{
			// max generation number from rotate log files
			$maxAge = max($ages);
			// when use numbering filename
			$i = $maxAge + 1;
			foreach($logs as $key => $value){
				// $key = (int)Generation, $value = (string)Filename
				$key = intval($key);
				// shift generation of filename
				rename($logDir . DIRECTORY_SEPARATOR . $value, $logDir . DIRECTORY_SEPARATOR . $logFileBase . '.' . strval($key + 1));
			}
			rename($this->logFile, $this->logFile . '.1');
		}
		
		// using logging generation
		if($this->rotateAge !== FALSE && $maxAge >= $this->rotateAge){
			if($this->rotate !== MY_LOGGER_ROTATE_NAME_NUMBER){
				// when not use numbering filename
				
				$d = dir($logDir);
				$logs = array();
				while (false !== ($log = $d->read())) {
					// timestamp of file in array keys
					if(preg_match('|^' . $logFileBase . '\.\d+$|', $log)){
						$logs[filetime($logDir . DIRECTORY_SEPARATOR . $log)] = $log;
					}
				}
				
				// array key sort
				ksort($logs, SORT_STRING);
				// delete oldest file
				$delLog = array_shift($logs);
				unlink($logDir . DIRECTORY_SEPARATOR . $delLog);
			}else{
				// when use numbering filename
				// delete max generation
				unlink($logFileBase . '.' . ($maxAge + 1));
			}
		}
	}
}



class My_Logger_Exception extends Exception
{
	public function __construct($message, $code = null){
		parent::__construct($message, $code);
	}
}

/*
// unit test
function Logger_UnitTest(){
	$logger = My_Logger::getLog('Logger', '/var/log/hoge.log');
	$logger->info('information message');
	$logger->notice('notice message');
	$logger->error('error message');
	$logger->log('critical message', My_Logger::CRITICAL);
	$logger->log('alert message', My_Logger::ALERT);
	$logger->warn('warning message');
	$logger->emerg('emergency message!');
	
	
}

LogFactory_UnitTest();
*/
