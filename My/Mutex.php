<?php
/**
 * 似非Mutex処理
 *
 * @since Mon, 26 May 2008
 * @version 0.0.1
 * @copyright anoncom.net
 */
class My_Mutex{
	
	private $id;
	private $sem_id;
	private $is_acquired = false;
	private $is_windows = false;
	private $filename = '';
	private $filepointer;

	function __construct()
	{
		if(substr(PHP_OS, 0, 3) == 'WIN')
			$this->is_windows = true;
	}

	public function init($id, $filename = '')
	{
		$this->id = $id;

		if($this->is_windows)
		{
			if(empty($filename)){
				print "no filename specified";
				return false;
			}
			else
				$this->filename = $filename;
		}
		else
		{
			if(!($this->sem_id = sem_get($this->id, 1))){
				print "Error getting semaphore";
				return false;
			}
		}

		return true;
	}

	public function acquire()
	{
		if($this->is_windows)
		{
			if(($this->filepointer = @fopen($this->filename, "w+")) == false)
			{
				print "error opening mutex file<br>";
				return false;
			}
			
			if(flock($this->filepointer, LOCK_EX) == false)
			{
				print "error locking mutex file<br>";
				return false;
			}
		}
		else
		{
			if (! sem_acquire($this->sem_id)){
				print "error acquiring semaphore";
				return false;
			}
		}

		$this->is_acquired = true;
		return true;
	}

	public function release()
	{
		if(!$this->is_acquired)
			return true;

		if($this->is_windows)
		{
			if(flock($this->filepointer, LOCK_UN) == false)
			{
				print "error unlocking mutex file<br>";
				return false;
			}

			fclose($this->filepointer);
		}
		else
		{
			if (! sem_release($this->sem_id)){
				print "error releasing semaphore";
				return false;
			}
		}

		$this->is_acquired = false;
		return true;
	}

	public function getId()
	{
		return $this->sem_id;
	}

		
	/**
	 * 既にプロセスが稼動しているかを判定します。
	 * 二重起動の防止などに使用します。
	 * ※exec関数を使用しているため、safeモードのphpでは使用できません。
	 *
	 * @access public static
	 * @author Yoshihiko Kimura <kimura@froute.co.jp>
	 * @author anon <anon@anoncom.net>
	 * @param string $self 判定対象となるプロセス名またはファイル名を指定します。
	 * @return boolean 既に稼動している場合にはtrue, 見つからなければfalseを返します。
	 */
	function isRunning($self){
		$cmd = "ps auxw | grep {$self}";
		exec($cmd, $array);
		if(!is_array($array)){	// 失敗
			return false;
		}
		$i = 0;
		$max = count($array);
		$match = 0;
		$search = preg_quote($self);
		while($i < $max){
			if(!preg_match("/grep/", $array[$i]) && preg_match("/{$search}/", $array[$i])){
				++$match;
				if($match > 2){
					return true;
				}
			}
	        ++$i;
		}
		return false;
	}
}

/**
 * Mutexサンプル
 *
 */
function __mutex_sample(){
    $mutex = new My_Framework_Mutex();
    $mutex->init(1, "mutex_file.txt");
    $mutex->acquire();

    //Whatever you want single-threaded here...
    $mutex->release();
}

?>