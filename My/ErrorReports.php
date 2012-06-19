<?php

// error_log用フラグ
// @link http://jp.php.net/manual/ja/function.error-log.php
define('MY_ERRREPORTS_ERRLOG_SYSTEM', 0);
define('MY_ERRREPORTS_ERRLOG_MAIL',   1);
define('MY_ERRREPORTS_ERRLOG_FILE',   3);

/**
 * エラー通知フラグ
 */
define('MY_ERRREPORTS_NOTIFY_NOTICE',    0);
define('MY_ERRREPORTS_NOTIFY_ALERT',     1);
define('MY_ERRREPORTS_NOTIFY_WARNING',   2);
define('MY_ERRREPORTS_NOTIFY_ERROR',     3);
define('MY_ERRREPORTS_NOTIFY_EMERGENCY', 4);

/**
 * froute Framework エラー通知用ライブラリ
 *
 * @since 2008/08/13
 * @author anon <anon@anoncom.net>
 * @version @package@_version
 */
class My_ErrorReports{
    
    function __construct(){}
    
	
	/**
	 * Report mail
	 * ファイル出力用ログファイルとは別にメールで通知
	 * しておくべきメッセージがある場合に使用します。
	 *
	 * @access public static
	 * @param string $message メッセージ
	 * @param mixed $addrs 通知先アドレス(配列または、","、";"で区切って複数宛先設定可能)
	 * @param int $type 通知種別(0:Notice, 1:Alert, 2:Warning, 3:Error 4:Emergency)
	 * @param array(string $filename) 添付するファイル名(フルパス)を配列で指定します。 
	 * @author anon <anon@anoncom.net>
	 */
	public static function report_mail($message, $addrs, $type = 0, $attache = array()){
	    
	    $alert = array(
	        'Notice',
	        'Alert',
	        'Warning',
	        'Error',
	        'Emergency',
	    );
	    
	    $headers = '';
	    $default_report_to = "sys-alert@froute.co.jp";
	    
	    $subject = "[{$alert[$type]}] System Error Report";
	    
	    if(is_array($addrs)){
	        $to = $addrs;
	    }else{  
            $to = split("[\,;]", $addrs);    // 「,」または「;」で区切る
	    }
	    $i = 0;
	    $max = count($to);
	    $fail = 0;
	    
	    // 本文文字コードを確認
	    $encoding = mb_detect_encoding($message);
	    if($encoding != 'JIS' && $encoding != 'ISO-2022-JP'){    // 文字コードがJIS系でない場合
	        $message = mb_convert_encoding($message, 'ISO-2022-JP', $encoding);    // 文字コード変換
	    }
	    
	    
	    $headers .= "From: System Error Notifier <error-notifier@froute.co.jp>\r\n";
	    $headers .= "Subject: {$subject}\r\n";
	    
	    $message .= "------------------------\n";
		$message .= "Please check about next information.\n";
		$message .= "Server: {$_SERVER['SERVER_ADDR']}\n";
		$message .= "Path[me]: {$_SERVER['SCRIPT_FILENAME']}\n";
		
		
		// 必要に応じて文字コードを変換する
		if(mb_detect_encoding($message) != 'ASCII'){
    		$message = mb_convert_encoding($message, 'ISO-2022-JP', 'EUC-JP');
		}
	    
	    while($i < $max){
    	    if(!preg_match("/^[0-9a-z\.\-_]+@[0-9a-z\.\-]+\.[a-z]{2,4}$/i", $to[$i])){
    	            ++$fail;
    	            ++$i;
        	        continue;
    	    }
    	    if(preg_match('|[^@]+@[cdhknqrst]?\.?(?:softbank|vodafone|disney)\.ne\.jp', $to[$i])){
    	        // SoftBankの場合は本文をShift JIS（に見せかけて）で送信します。
        		$headers .= "Content-Type: text/plain; charset=Shift_JIS\r\n";
        	}else {    // DoCoMo,KDDIはISO-2022-JPで送信します。
        		$headers .= "Content-Type: text/plain; charset=ISO-2022-JP\r\n";
    	    }
    	    mail($to[$i], $subject, $message, "From: System Reporting <sys-alert@froute.co.jp>\r\n");
    	    //error_log($message, My_FRAMEWORK_ERRREPORT_ERRLOG_SYSTEM, $to[$i], $headers);    // error_log()を使用してメールで送信します。
    	    ++$i;
	    }
	    if($fail > 0){    // 1通も正常に送信できなければ $default_report_to宛に送信します。
	        mail($default_report_to, $subject, $message, "From: System Reporting <sys-alert@froute.co.jp>\r\n");
	        //error_log($message, My_FRAMEWORK_ERRREPORT_ERRLOG_SYSTEM, $default_report_to, $headers);    // error_log()を使用してメールで送信します。
	    }
	}
	
	/**
	 * エラーメッセージ出力
	 *
	 * @access public static
	 * @param string $str
	 */
	public static function error($str){
		//error_log("ERROR [" . date("Y-m-d H:i:s") . "] NicoCrawler : ".$str);
		error_log("[ERROR] [" . __CLASS__ . "::" . __METHOD__ . "] ".$str);
	}
	
	/**
	 * デバッグメッセージ出力
	 *
	 * @access public static
	 * @param string $str
	 */
	public static function debug($str){
		//error_log("ERROR [" . date("Y-m-d H:i:s") . "] NicoCrawler : ".$str);
		error_log("[DEBUG] [" . __CLASS__ . "::" . __METHOD__ . "] ".$str);
	}
}

?>