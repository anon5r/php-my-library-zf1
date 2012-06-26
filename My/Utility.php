<?php

/**
 * My Framework
 * 共通関数ライブラリ
 *
 * @access public
 */
class My_Utility{
	
	
	function __construct(){}
	
	
	/**
	 * HTML UnEscape
	 * HTMLタグを除去し、その他のエスケープされた文字を有効にします。
	 *
	 * @author anon <anon@anoncom.net>
	 * @param string $str HTML escaped string
	 * @return string unescaped string
	 */
	public static function htmlunescape($str){
		$str = preg_replace('@<[^>]+>@', '', $str);
		$str = str_replace('&amp;', '&', $str);
		$str = str_replace('&lt;', '<', $str);
		$str = str_replace('&gt;', '>', $str);
		return $str;
	}
	
	
	/**
	 * Make recursive directories
	 *
	 * @author anon <anon@anoncom.net>
	 * @param string $path
	 */
	public static function recursive_mkdir($path){
		$dirs = explode('/', $path);
		$absolute_path = '';
		$i = 0; $max = count($dirs);
		while($i < $max){
			$absolute_path .= '/'.$dirs[$i];
			if(!file_exists($absolute_path)){
				mkdir($absolute_path);
				usleep(100000);	// wait for 0.5 sec (1sec = 1,000,000 microsec)
			}
			$i++;
		}
	}
	
	
	/**
	 * Recursive Convert Encoding for MultiByte String
	 * Replace all string of multibyte string code in arrays by recursive.
	 *
	 * @author anon <anon@anoncom.net>
	 * @param array $param to Exchange target arrays
	 * @param string $to_encoding 変換したい文字コード
	 * @param string $from_encoding 変換元文字コード (default: "auto")
	 * @return array
	 * @link http://kayano.jugem.cc/?eid=431
	 */
	public static function recursive_mb_convert_encoding($param, $to_encoding, $from_encoding = "auto"){
		if(is_array($param)){
			foreach($param as $key => $value){
				$param[$key]=call_user_func(array(__CLASS__, __FUNCTION__), $value, $to_encoding, $from_encoding);
			}
		}else{
			$param = mb_convert_encoding($param, $to_encoding, $from_encoding);
		}
		return $param;
	}
	
	
	
	/**
	 * Parse to ArrayMap from HTTP queriy string.
	 * Warning: duplicated key's value is overwrite by last same name key's value.
	 *
	 * @author anon <anon@anoncom.net>
	 * @param string built query string
	 * @return array $queries ArrayMap Keys and Values
	 */
	public static function parseQuery($queryString){
		$queries = array();	// initialize
		
		$indexOf = strpos($queryString, '?');
		if($indexOf !== FALSE){
			$queryString = substr($queryString, ($indexOf + 1), strlen($queryString));
		}
		
		$dividedQuery = explode('&', $queryString);
		$i = 0;
		$iLimit = count($dividedQuery);
		while($i < $iLimit){
			$tmpQuery = explode('=', $dividedQuery[$i]);
			$queries[$tmpQuery[0]] = isset($tmpQuery[1]) ? urldecode($tmpQuery[1]) : NULL;
			$i++;
		}
		
		return $queries;
	}
	
	/**
	 * Build HTTP request queries by ArrayMap
	 * (Like a simulate from http_build_query() fuinction on php5)
	 *
	 * @author anon <anon@anoncom.net>
	 * @param array $queries ArrayMap Keys and Values
	 * @param string $separator query separate string
	 * @return string built query string
	 */
	public static function buildQuery($queries, $separator = '&'){
		$query = '';	// initialize
		foreach($queries as $key => $val) {
			$query .= urlencode($key);
			if(!is_null($val) && strlen($val) > 0){
				$query .= '=' . urlencode($val);
			}
			if($query != ''){
				$query .= $separator;
			}
		}
		if(strlen($query) > 0){
			$query = substr($query, 0, (strlen($query) - 1));
		}
		return $query;
	}
	
	/**
	 * Extension of print_r()
	 * print_r()の拡張関数です
	 *
	 * @author anon <anon@anoncom.net>
	 * @param array $vars 対象の配列を指定します。
	 * @param boolean $encoding 各配列の文字列エンコーディングを結果と一緒に返すか指定します (default: false)
	 * @param boolean $flg_html ブラウザでデバッグする際に、html形式で見やすい形に整形して出力します (default: false)
	 */
	public static function print_r2($vars, $encoding = false, $flg_html = false, $level = 0){
	
		if(empty($encoding) || is_null($encoding)){ $encoding = false; }
	
		if($flg_html){
	
			if($level == 0){
				print '<div style=\"font-family: "Courier New"; font-size: 8pt;">' . "\r\n";
			}
			if(is_array($vars)){
				print 'Array(' . count($vars) . ')(<br />' . "\r\n";
				foreach($vars as $key => $val){
					for($x = 1; $x <= ($level + 1) * 4; $x++){ print '&nbsp;'; }
					print "[".$key."] => ";
					$level++;
					//print_r2($val, $encoding, $flg_html, $level);
					call_user_func(array(__CLASS__, __FUNCTION__), $val, $encoding, $flg_html, $level);
					$level--;
				}
				for($x = 1; $x <= $level * 4; $x++){ print '&nbsp;'; }
				print ')<br />' . "\r\n";
			}else{
				print '(' . gettype($vars) . ')';
				if($encoding && gettype($vars) == 'string'){
					print '(' . htmlspecialchars(mb_detect_encoding($vars)) . ')';
				}
				print ' ' . $vars . '<br />' . "\r\n";
			}
			if($level == 0){ print '</div>' . "\r\n"; }
	
		}else{
	
			if(is_array($vars)){
				print 'Array(' . count($vars) . ')(' . "\n";
				foreach($vars as $key => $val){
					for($x = 1; $x <= ($level + 1) * 4; $x++){ print " "; }
					print '[' . $key . '] => ';
					$level++;
					//print_r2($val, $encoding, $flg_html, $level);
					call_user_func(array(__CLASS__, __FUNCTION__), $val, $encoding, $flg_html, $level);
					$level--;
				}
				for($x = 1; $x <= $level * 4; $x++){ print ' '; }
				print ')' . "\n";
			}else{
				print '(' . gettype($vars) . ')';
				if($encoding && gettype($vars) == 'string'){
					print '('.mb_detect_encoding($vars) .')';
				}
				print ' ' . $vars . "\n";
			}
	
		}
	}
	
	
	/**
	 * generate unique string
	 * ランダムな文字列を生成します
	 *
	 * @author anon <anon@anoncom.net>
	 * @param int $length 生成する文字数 (default=12)
	 * @param boolean $useNumeric 数値を使用するか
	 * @param boolean $useLCase 小文字アルファベットを使用するか
	 * @param boolean $useUCase 大文字アルファベットを使用するか
	 * @param boolean $useSymbols 記号を使用するか
	 * @return string ランダムに生成された文字列を返します
	 */
	public static function generateUniqueString($length = 12, $useNumeric = true, $useLCase = true, $useUCase = true, $useSymbols = true){
		$charList = '';	// initialize
		
		$numericList   = '0123456789';
		$lowerCaseList = 'abcdefghijklmnopqrstuvwxyz';
		$upperCaseList = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$symbolsList   = '!"#$%&\'()*+,-./:;<=>?@[\]^_`{|}~';
		
		if($useNumeric){
			$charList .= $numericList;
		}
		if($useLCase){
			$charList .= $lowerCaseList;
		}
		if($useUCase){
			$charList .= $upperCaseList;
		}
		if($useSymbols){
			$charList .= $symbolsList;
		}
		
		mt_srand();
		$ret = '';
		$i = 0;
		while($i < $length){
			$ret .= $charList{mt_rand(0, strlen($charList) - 1)};
			$i++;
		}
		return $ret;
	}

	/**
	 * generate unique string
	 * 指定された文字の数で、ランダムな文字列を生成します
	 * 
	 * @author anon <anon@anoncom.net>
	 * @param int $length 生成する文字数 (default=12)
	 * @param int $usesNumeric 使用する数値数
	 * @param int $usesLCase 使用する小文字アルファベット数
	 * @param int $usesUCase 使用する大文字アルファベット数
	 * @param int $usesSymbols 使用する記号数
	 * @return string ランダムに生成された文字列を返します
	 */
	public static function generateUniqueString2($length = 12, $usesNumeric = 3, $usesLCase = 3, $usesUCase = 3, $usesSymbols = 3){
		
		// 使用文字列
		$charsList = array(
			'0123456789',
			'abcdefghijklmnopqrstuvwxyz',
			'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
			'!"#$%&\'()*+,-./:;<=>?@[\]^_`{|}~'
		);
		
		// それぞれの使用する文字数
		$genChars = array(
			$usesNumeric,
			$usesLCase,
			$usesUCase,
			$usesSymbols
		);
		
		mt_srand();
		$ret = '';
		$i = 0;
		while($i < $length){
			$skipchars = true;
			$j = 0;
			while($skipchars && $j < 4){
				// 使用する文字種の決定
				$charsType = rand(0, 3);
				$usesCharsCount = $genChars[$charsType];
				if($usesCharsCount > 0){
					$skipchars = false;
				}
				$j++;
			}
			
			// 使用する文字種のそれぞれの数と
			// 出力したい文字列長が一致しない
			// (文字列長の方が長い)場合
			if($j >= 4){
				// 英数字で無理やり決定させる
				$charsType = rand(0, 2);
			}
			
			$ret .= $charsList[$charsType]{mt_rand(0, strlen($charsList) - 1)};
			$genChars[$charsType]--;	// 使用した文字の数をカウントダウンする
			
			$i++;
		}
		return $ret;
	}
	
	
	/**
	 * $hostname が接続先として接続応答出来る常態かを確認します。
	 *
	 * @access public static
	 * @param string $hostname 接続先ホスト名またはIP
	 * @param int $port 接続先ポート番号
	 * @return boolean
	 */
	public static function isAliveHost($hostname, $port = 80){
		if(strlen($hostname) == 0){
			require_once 'My/Exception.php';
			throw new My_Exception('Hostname is empty.');
		}
		if(self::matchesIn($hostname, ':')){
			$parts = explode(':', $hostname);
			$hostname = $parts[1];
		}
		
		// port番号指定がint型以外、あるいはintの範囲外である場合はfalse
		if(is_int($port) == FALSE && ctype_digit($port) == FALSE){
			require_once 'My/Exception.php';
			throw new My_Exception('Invalid port number: ' . $port);
		}
		
		// port番号が65535を超える場合は不正とみなしfalse
		if($port < 0 || $port > 65535){
			require_once 'My/Exception.php';
			throw new My_Exception('Invalid range of port number: ' . $port);
		}
		
		$fp = @fsockopen($hostname, $port, $errno, $errmsg, 5);
		if(!$fp){
			error_log('[error] [' . __CLASS__ . '::' . __FUNCTION__ . '] could not connect to ' . $hostname . ':' . $port . ', ' . $errmsg);
			return false;
		}
		fclose($fp);
		
		return true;
		
	}
	
	
	/**
	 * 前方一致
	 * $haystackが$needleから始まるか判定します。
	 * 
	 * @author anon <anon@anoncom.net>
	 * @param string $haystack
	 * @param string $needle
	 * @return boolean
	 */
	public static function startsWith($haystack, $needle){
		return strpos($haystack, $needle, 0) === 0;
	}
	
	/**
	 * 後方一致
	 * $haystackが$needleで終わるか判定します。
	 * 
	 * @author anon <anon@anoncom.net>
	 * @param string $haystack
	 * @param string $needle
	 * @return boolean
	 */
	public static function endsWith($haystack, $needle){
		$length = (strlen($haystack) - strlen($needle));
		// 文字列長が足りていない場合はFALSEを返します。
		if($length <0) return FALSE;
		return strpos($haystack, $needle, $length) !== FALSE;
	}
	
	/**
	 * 部分一致
	 * $haystackの中に$needleが含まれているか判定します。
	 * 
	 * @author anon <anon@anoncom.net>
	 * @param string $haystack
	 * @param string $needle
	 * @return boolean
	 */
	public static function matchesIn($haystack, $needle){
		return strpos($haystack, $needle) !== FALSE;
	}
	
	/**
	 * 全角、半角混在の長さ取得
	 * （UTF-8でもEUCでもSJISでも同じ結果がほしい）
	 * 
	 * 
	 * @author anon <anon@anoncom.net>
	 * @param string $str
	 * @param string $encoding
	 * @return int
	 * @link http://shiro.exbridge.jp/?eid=16
	 */
	public static function strlen_byte($str, $encode = 'EUC-JP') {
		$len = 0;
		$strlen = mb_strwidth($str, $encode);
		for ($i = 0; $i < $strlen; $i++) {
			$s = substr($str, $i, 1);
			if (strlen(bin2hex($s)) % 2 == 0) {
				$len += 2;
			}else{
				$len++;
			}
		}
		return $len;
	}
	
	
	/**
	 * 指定の文字数で文字列をまとめますが、省略する位置は文字列の真ん中となります
	 * 
	 * @author anon <anon@anoncom.net>
	 * @param string $str 丸めたい文字列
	 * @param int $width 開始位置のオフセット。文字列の始めからの文字数 (最初の文字は 0) です。 
	 * @param string $trimmarker 丸めた後にその文字列の真ん中に追加される文字列
	 * @param string|null $encoding encoding パラメータには文字エンコーディングを指定します。省略した場合は、内部文字エンコーディングを使用します。
	 * @return string 丸められた文字列を返します。 trimmarker が設定された場合、 trimmarker が丸められた文字列の真ん中に追加されます
	 */
	public static function innerStrimWidth($str, $width, $trimmarker = '...', $encoding = NULL){
		$encoding = mb_detect_encoding($str);
		if(mb_strlen($str, $encoding) <= $width){
			return $str;
		}
		$half = (($width + mb_strlen($trimmaker, $encoding)) / 2);
		$head = mb_substr($str, 0, $half, $encoding);
		$tail = mb_substr($str, mb_strlen($str, $encoding) - $half, $half, $encoding);
		$str = $head . $trimmarker . $tail;
		return $str;
	}
	
	/**
	 * GUIDを生成します
	 *
	 * @author anon <anon@anoncom.net>
	 * @return string
	 */
	public static function createGUID(){
		list($usec, $sec) = explode(' ', microtime());
		$cur_msec_time = $sec . substr($usec, 2, 3);
		
		$addrName = (isset($_ENV['COMPUTERNAME']) && strlen($_ENV['COMPUTERNAME']) > 0) ? $_ENV['COMPUTERNAME'] : 'localhost';
		$addrIP = (isset($_SERVER['SERVER_ADDR']) && strlen($_SERVER['SERVER_ADDR']) > 0) ? $_SERVER["SERVER_ADDR"] : '127.0.0.1';
		$addr = strtolower($addrName . '/' . $addrIP);
		
		$tmp = rand(0,1) ? '-' : '';
		$rnd = $tmp . rand(1000, 9999) . rand(1000, 9999) . rand(1000, 9999) . rand(100, 999) . rand(100, 999);
		
		
		$valueBeforeMD5 = $addr . ':' . $cur_msec_time . ':' . $rnd;
		$valueAfterMD5 = md5($valueBeforeMD5);
		
		$raw = strtoupper($valueAfterMD5);
		return substr($raw, 0, 8) . '-' . substr($raw, 8, 4) . '-' . substr($raw, 12, 4) . '-' . substr($raw, 16, 4) . '-' . substr($raw, 20);
	}
	
	
	/**
	 * $userAgent がモバイル端末であるか判定します
	 * 
	 * @author anon <anon@anoncom.net>
	 * @param string $userAgent User-Agent
	 * @return bool
	 */
	public static function isMobile( $userAgent )
	{
		if ( self::startsWith( $userAgent, 'DoCoMo/' ) ) {
			return true;
		} else if(
			   self::startsWith( $userAgent, 'SoftBank/' )
			|| self::startsWith( $userAgent, 'Vodafone/' )
			|| self::startsWith( $userAgent, 'J-PHONE/' )
			|| self::startsWith( $userAgent, 'MOT-' )
		) {
			return true;
		} else if(
			   self::startsWith( $userAgent, 'KDDI-' ) 
			|| self::matchesIn( $userAgent, 'UP.Browser' )
		) {
			return true;
		}
		return false;
	}
}

