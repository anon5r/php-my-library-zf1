<?php


define('My_SEARCH_CLASS_TYPE_DB',      0);    // データベース通信
define('My_SEARCH_CLASS_TYPE_XML',     1);    // XML通信
define('My_SEARCH_CLASS_TYPE_SOAP',    2);    // SOAP通信
define('My_SEARCH_CLASS_TYPE_SOCKET',  9);    // ソケット通信

/**
 * 検索クラス
 *
 */
abstract class My_Search {
	
	protected $keyword;        // 検索キーワード

	protected $page;           // ページ数 
	protected $fetch_size;     // 取得件数
	protected $result;         // 検索結果
    
	
	/**
	 * constructor
	 * @param
	 */
	abstract function __construct() {
	    // TODO: インスタンス生成時に行うべき処理があれば記述
	}
	
	/**
	 * destructor
	 */
	abstract function __destruct() {
        // TODO: インスタンス開放時に行うべき処理があれば記述
	}

    /**
     * @return int
     */
    public function getFetchSize (){
        return $this->fetch_size;
    }
    /**
     * @return string
     */
    public function getKeyword (){
        return $this->keyword;
    }
    /**
     * @return int
     */
    public function getPage (){
        return $this->page;
    }
    /**
     * @return Object
     */
    public function getResult (){
        return $this->result;
    }
    /**
     * @param int $fetch_size
     */
    public function setFetchSize ($fetch_size){
        $this->fetch_size = $fetch_size;
    }
    /**
     * @param string $keyword
     */
    public function setKeyword ($keyword){
        $this->keyword = $keyword;
    }
    /**
     * @param int $page
     */
    public function setPage ($page){
        $this->page = $page;
    }
    /**
     * @param Object $result
     */
    public function setResult ($result){
        $this->result = $result;
    }

    /**
	 *	初期化を行います
	 */
	public abstract function initialize() {
	    // TODO: サブクラスで設定すべき初期値があれば・・
	}

	
	public static function factory($type = My_SEARCH_CLASS_TYPE_DB){
	    
	    $class = NULL;    // クラス名
	    
	    switch($type){
	        case My_CORESEARCH_CLASS_TYPE_DB:
	            $class = 'My_Search_Db'; break;
	        case My_CORESEARCH_CLASS_TYPE_XML:
	            $class = 'My_Search_Xml'; break;
	        default:
	            error_log('Unknown search class type.');
	            return null;
	    }
	    
	    
	    try{
	        $instance = My_Loader::loadClass($class);
	    }catch(My_Exception $fe){
	        My_Search::error($fe->getMessage());
	    }
	    
        return $instance;
	    
	}

	/**
	 * @param integer $_mode 検索方法を変更したい場合等
	 */
	abstract protected function search($_mode = -1) {

		// 前処理
		$this->preprocess();

		$flag = false;
		// 必要であれば、$mode で検索方法を切り替えれるようにする？！
		// とりあえず、このメソッドの実装もサブクラスに任せます・・
		switch ($_mode) {
		default:
			$flag = $this->_search();
			break;
		}

		// 後処理
		$this->after_process();

		// 後処理
		$this->finish_process();

		return $flag;
	}

	/**
	 * Preprocess
	 */
	abstract protected function preprocess() {
	    // TODO: 前処理があれば記述
	}

	/**
	 * After Process
	 */
	abstract protected function afterprocess() {
        // TODO: 後処理があれば記述
	}
	
}

?>
