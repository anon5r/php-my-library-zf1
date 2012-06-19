<?php


/**
 * 簡易クラスローダ
 *
 * @author anon <anon@anoncom.net>
 */
class My_Loader
{
	
	function __construct() {}
	
	/**
	 * 指定されたクラスを読み込み、インスタンスを返します。
	 * 
	 * @param string $class クラス名
	 * @param string|NULL $path クラスディレクトリ または クラスファイルパス
	 * @param bool $once requireの際にonceで読み込むか否か
	 * @param array|NULL インスタンス生成時に引き渡す引数
	 * @param string|NULL $loaderMethod インスタンス生成時に呼び出される、コンストラクタ以外のメソッド名
	 * @throws My_Exception
	 */
	public static function loadClass($class, $path = NULL, $once = TRUE, $params = NULL, $loaderMethod = NULL)
	{
		if(is_null($path)){
			$dirs = explode(':', ini_get('include_path'));
			$classfile_exists = false;
			foreach($dirs as $dir){
			    $filepath = $dir . '/' . $class . '.php';
				if(My_Loader::isFileAvailable($filepath)){
    				$classfile_exists = true;
    				break;
				}
			
			}
			if(!$classfile_exists){
    			require_once 'My/Exception.php';
    			throw new MyException('Unable to load class, causes instance "' . $class . '".');
			}
		}elseif(is_file($path)){
			$filepath = $path;
		}else{
		    $filepath = $path . '/' . $class . '.php';
			if(!My_Loader::isFileAvailable($filepath)){
				require_once 'My/Exception.php';
				throw new MyException('Unable to load class, causes instance "' . $class . '".');
			}	
		}
		
	
		if($once){
			require_once($filepath);
		}else{
			require($filepath);
		}
	
		if(!class_exists($class)){
		    require_once 'My/Exception.php';
			throw new MyException('Unable to load class, causes defined class name "' . $class . '".');
		}
		if(!is_null($loaderMethod)){
		    if(is_null($params)){
		        $instance = call_user_func(array($class, $loaderMethod));
		    }else{
    		    $instance = call_user_func(array($class, $loaderMethod), $params);
		    }
		}
		
		
		if(is_null($params)){
    		$instance = new $class;
		}else{
		    $instance = new $class($params);
		}
		return $instance;
	}
	
	/**
	 * ファイルが存在するか確認します
	 *
	 * @param string $filename
	 * @return bool
	 */
	private static function isFileAvailable($filename)
	{
		if(file_exists($filename) && is_readable($filename)){
			return true;
		}else{
			return false;
		}
	}
}
