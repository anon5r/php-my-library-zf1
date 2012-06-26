<?php
require_once 'My/Logger.php';
require_once 'My/Utility.php';
require_once 'Zend/Http/Client.php';

/**
 * My Service API
 * 
 * ATTENTION:
 * This class required any external library.
 * The required  external libraries are 
 * "XML_Unserializer" provided by PEAR Library, 
 * and "Zend_Http_Client" provided by ZendFramework.
 * 
 * @author anon <anon@anoncom.net>
 * @since Mon, 16 Feb 2009 20:00:00 +0900
 */
class My_Service_Api{
	
	const VERSION = '0.3.2';
	
    const REQUEST_METHOD_GET	= 'GET';
    const REQUEST_METHOD_POST	= 'POST';
    const REQUEST_METHOD_HEAD	= 'HEAD';
    
    const REQUEST_SCHEME_HTTP	= 'http';
    const REQUEST_SCHEME_HTTPS	= 'https';
    const REQUEST_SCHEME_FTP	= 'ftp';
    const REQUEST_SCHEME_FILE	= 'file';
    
    const REQUEST_AUTH_METHOD_PLAIN		= 'plain';
    const REQUEST_AUTH_METHOD_BASIC		= 'basic';
    const REQUEST_AUTH_METHOD_DIGEST	= 'digest';
    
    const RESPONSE_DATA_FORMAT_TEXT	= 'text';
    const RESPONSE_DATA_FORMAT_XML	= 'xml';
    const RESPONSE_DATA_FORMAT_JSON	= 'json';
    const RESPONSE_DATA_FORMAT_PHP	= 'php';	// serialized php
    const RESPONSE_DATA_FORMAT_CSV	= 'csv';
    const RESPONSE_DATA_FORMAT_TSV	= 'tsv';
    
    /**
     * @var My_Logger
     */
    protected static $logger = NULL;
    
    /**
	 * @var string
	 */
	private $scheme			 = self::REQUEST_SCHEME_HTTP;
    
	/**
	 * @var string
	 */
	private $host			 = 'example.com';
	
	/**
	 * @var int
	 */
	private $port			 = 80;
	
	/**
	 * @var string
	 */
	private $path			 = '';
	
	/**
	 * @var string
	 */
	private $authUser		= '';
	
	/**
	 * @var string
	 */
	private $authPass		= '';
	
	/**
	 * @var string
	 */
	private $authMethod		 = '';
	
	/**
	 * @var array
	 */
	private $queries		 = array();
	
	/**
	 * @var enum
	 */
	private $requestMethod	 = self::REQUEST_METHOD_GET;
	
	/**
	 * @var string Received response body
	 */
	private $response		 = '';
	
	
	/**
	 * @var int seconds of connection timed out
	 */
	private $timeout		 = 5;
	
	
	/**
	 * http request header 
	 *
	 * @var array header information
	 */
	private $requestHeaders;
	
	/**
	 * requested url
	 *
	 * @var string
	 */
	private $requestedUrl = '';
	
	/**
	 * Constructor
	 */
	public function __construct( ) {
		if ( self::$logger instanceof My_Logger === FALSE ) {
			//self::$logger = My_Logger::factory( __CLASS__ );
		} 
	}
	
	/**
	 * set authenticate user
	 * 
	 * @param string $user user
	 * @param string $password password
	 * @param string $method 
	 */
	public function setRequestAuth( $user, $password, $method = self::REQUEST_AUTH_METHOD_BASIC ) {
		$this->authUser = $user;
		$this->authPass = $password;
		$this->authMethod = $method;
	}
	
	/**
	 * set timeout to request api
	 * 
	 * @access public
	 * @param int $second seconds of timeout
	 */
	public function setTimeout( $second ) {
		$this->timeout = $second;
	}
	
	/**
	 * set request scheme for connecting to api server
	 *
	 * @access public
	 * @param string $scheme
	 */
	public function setRequestScheme( $scheme ) {
		$this->scheme = strtolower( $scheme );
	}
	
	/**
	 * set host to api server
	 *
	 * @access public
	 * @param string $host
	 */
	public function setRequestHost( $host ) {
		$this->host = $host;
	}
	
	/**
	 * set port to api server
	 *
	 * @access public
	 * @param int $port
	 */
	public function setRequestPort( $port ) {
		$this->port = $port;
	}
	
	/**
	 * set path to api server
	 *
	 * @access public
	 * @param string $path
	 */
	public function setRequestPath( $path ) {
		$this->path = $path;
	}
	
	/**
	 * set data for request
	 *
	 * @access public
	 * @param string $key
	 * @param string $value
	 */
	public function addRequestQuery( $key, $value ) {
		$this->queries[$key] = $value;
	}
	
	/**
	 * set request query
	 * 
	 * $queries = 'foo=value&bar=value&baz=value';
	 * $api->setQuery( $queries );
	 * 
	 *
	 * or
	 * 
	 * 
	 * $queries['foo'] = 'value';
	 * $queries['bar'] = 'value';
	 * $queries['baz'] = 'value';
	 * $api->setQuery( $queries );
	 * 
	 * 
	 * or
	 * 
	 * $queries = array();
	 * $queries[] = 'foo=value';
	 * $queries[] = 'bar=value';
	 * $queries[] = 'baz=value';
	 * $api->setQuery( $queries );
	 * 
	 * @access public
	 * @param array|string query
	 */
	public function setRequestQuery( $queries ) {
		if ( is_array( $queries ) == false && strpos( '&', $queries ) !== false && strpos( '=', $queries ) !== false ) {
			$queries = explode( '&', $queries );
		}
		if ( is_array( $queries ) && count( $queries ) > 0 ) {
			foreach ( $queries as $key => $value ) {
				if ( ctype_digit( strval( $key ) ) == true && strpos( '=', $value ) !== false ) {
					list( $key, $value ) = explode( '=', $value );
				}
				$this->addRequestQuery($key, $value );
			}
		} else {
			// generate exception
			if ( self::$this->logger instanceof My_LogFactory ) {
				self::$logger->warning( 'Invalid format in parametor. Not set in value' );
			} else {
				error_log( '[WARNING] Invalid format in parametor. Not set in value' );
			}
		}
	}
	
	/**
	 * set request method
	 * 
	 * @access public
	 * @param string
	 */
	public function setRequestMethod( $method ) {
		switch ( $method ) {
			case self::REQUEST_METHOD_GET:
			case self::REQUEST_METHOD_POST:
			case self::REQUEST_METHOD_HEAD:
			case self::REQUEST_METHOD_PUT:
				$this->method = $method;
				break;
			default:
				$this->method = self::REQUEST_METHOD_GET;
				break;
		}
	}
	
	/**
	 * add http request header information
	 * 
	 * @access public
	 * @param string $key request header key name
	 * @param string $value request header value of key
	 */
	public function addRequestHeader( $key, $value ) {
		if ( My_Utility::matchesIn( $key, '_' ) ) {
			$key = strtr( $key, '_', '-' );
		}
		$this->requestHeaders[ strtolower( $key ) ] = $value;
	}
	
	/**
	 * clear registered all request headers
	 * 
	 * @access public
	 * @return void
	 */
	public function clearRequestHeader( ) {
		$this->requestHeaders = array();
	}
	
	
	/**
	 * set logger (for My_Logger)
	 * 
	 * @access public
	 * @param My_Logger $logger
	 */
	public function setLogger( My_Logger $logger ) {
		self::$logger = $logger;
	}
	
	
	/**
	 * call API (programing interface)
	 * 
	 * @access public
	 * @return bool
	 */
	public function call() {
		switch ($this->scheme ) {
			case self::REQUEST_SCHEME_HTTP:
			case self::REQUEST_SCHEME_HTTPS:
				return $this->call_Http();
			/*
			case self::REQUEST_SCHEME_FTP:
				// TODO: please implement to call_Ftp() method.
				return $this->call_Ftp(); break;
			*/
			case self::REQUEST_SCHEME_FILE:
				// TODO: please implement to call_File() method.
				return $this->call_File();
		}
		return false;
	}
	
	/**
	 * call API by HTTP request
	 * 
	 * @access private
	 * @return bool
	 */
	private function call_Http() {
		
		
		// Check the destination host survival
		if (My_Utility::isAliveHost( $this->host, $this->port ) === FALSE ) {
			self::$logger->alert( 'Failed to connect request server "' . $this->host . ':' . $this->port . '"' );
			return false;
		}
		
		$url = $this->scheme . '://' . $this->host;
		if ( 
			( $this->scheme == self::REQUEST_SCHEME_HTTP && $this->port != 80 )
			|| ( $this->scheme == self::REQUEST_SCHEME_HTTPS && $this->port != 443 )
		) {
			$url .= ':' . $this->port;
		}
		$url .=  $this->path;
		
		if ( count( $this->requestHeaders ) > 0 ) {
			$headers = $this->requestHeaders;
		}else {
			// set Default
			$headers = array( 
				'Accept-Language' => 'ja',
				'User-Agent' => 'Mozilla/4.0 (anoncom.net; APIRequest/' . self::VERSION . '; +http://anoncom.net/)',
			);
		}
		
		$config = array(
			'timeout' => $this->timeout,
			'useragent' => $headers['User-Agent'],
		);
		
		
		$client = new Zend_Http_Client();
		
		$client->setConfig( $config );
		
		$client->resetParameters();
		$client->setMethod( Zend_Http_Client::GET );
		
		if ( $this->authUser !== NULL && strlen( $this->authUser ) > 0 ) {
			switch ( $this->authMethod ) {
				case self::REQUEST_AUTH_METHOD_BASIC:
					$client->setAuth( $this->authUser, $this->authPass, Zend_Http_Client::AUTH_BASIC );
					break;
				case self::REQUEST_AUTH_METHOD_DIGEST:
					self::$logger->warning( 'not support authentication method.' );
				//	$client->setAuth( $this->authUser, $this->authPass, Zend_Http_Client::AUTH_DIGEST );
					break;
			}
		}
		
		$client->setHeaders( $headers );
		
		$client->setUri( $url );
		
		switch ( strtoupper( $this->requestMethod )) {
			case self::REQUEST_METHOD_GET:
				// GET
				$client->setMethod( Zend_Http_Client::GET );
				if ( isset( $this->queries ) && is_array( $this->queries )) {
					$query = '';
					foreach ( $this->queries as $key => $value ) {
						$client->setParameterGet( $key, $value );
						$query.= rawurlencode( $key ) . '=' . rawurlencode( $value ) . '&';
					}
					$query = substr( $query, 0, ( strlen( $query ) - 1 ));
				}
				break;
					
			case self::REQUEST_METHOD_POST:
				// POST
				$client->clearPostData();
				$client->setMethod( Zend_Http_Client::POST );
				if ( is_array( $this->queries ) && is_array( $this->queries )) {
					foreach ( $this->queries as $key => $value ) {
						$client->setParameterPost( $key, $value );
					}
				}
				break;
			
			case self::REQUEST_METHOD_HEAD:
				// HEAD
				$client->setMethod( Zend_Http_Client::HEAD );
				if ( isset( $this->queries ) && is_array( $this->queries )) {
					$query = '';
					foreach ( $this->queries as $key => $value ) {
						$client->setParameterGet( $key, $value );
						$query.= rawurlencode( $key ) . '=' . rawurlencode( $value ) . '&';
					}
					$query = substr( $query, 0, ( strlen( $query ) - 1 ));
				}
				break;
			
			// Case of POST or GET or HEAD
			default:
				if ( self::$logger instanceof My_LogFactory === TRUE ) {
					self::$logger->error( 'Invalid request method: ' . $this->method );
				}else {
					error_log( '[ERROR] Invalid request method: ' . $this->method );
				}
				return false;
		}
		if ( strtoupper( $this->requestMethod ) === self::REQUEST_METHOD_GET ) {
			//self::$logger->debug( 'GET: ' . $url . '?' . $query );
			$this->requestedUrl = $url . '?' . $query;
		}else {
			//self::$logger->debug( 'POST: ' . $url . ' Params: ' . $query );
			$this->requestedUrl = $url;
		}
		
		try{
			$response = $client->request();
		}catch( Zend_Http_Client_Exception $e ) {
			self::$logger->error( 'Got Exception, the message as "' . $e->getMessage() .'"' );
			//self::$logger->debug( $e->getTraceAsString() );
			return false;
		}
		
		if ( $response->getStatus() !== 200 ) {
			self::$logger->alert( 'Got invalid response code => ' . $response->getStatus() . ' ' . $response->getMessage() );
			return false;
		}
		
		$this->response = $response->getBody();
		
		return true;
		
	}
	

	/**
	 * call API by FILE request
	 * 
	 * @access private
	 * @return bool
	 */
	private function call_File() {
		
		/*
		// Check the destination host survival
		if ( My_Utility::isAliveHost( $this->host, $this->port ) === FALSE ) {
			self::$logger->alert( 'Failed to connect request server "' . $this->host . ':' . $this->port . '"' );
			return false;
		}
		*/
		if ( strlen( $this->host ) > 0 && $this->host != 'localhost' ) {
			require_once 'My/Exception.php';
			throw new My_Exception( 'Could not support other host "' . $this->host . '" by this scheme ( FILE ).' );
		}
		
		//$url = $this->scheme . '://' . $this->host . $this->path;
		
		if ( ( is_file( $this->path ) && is_readable( $this->path ) ) == FALSE ) {
			return false;
		}
		
		$this->response = file_get_contents( $this->path ) ;
		
		return true;
		
	}
	
	/**
	 * get requested url for API
	 *
	 * @return string requested url for API
	 */
	function getRequestedUrl() {
		return $this->requestedUrl;
	}
	
	/**
	 * clear to data for request
	 * 
	 * @access public
	 * @return void
	 */
	public function clearRequestData() {
		$this->queries = null;
	}
	
	
	
	/**
	 * get received data from API
	 * 
	 * @access public
	 * @return string received response data ( not manufactured data ) from API
	 */
	public function getData() {
		return $this->response;
	}
	

	/**
	 * parse received data to array
	 * 
	 * @access public
	 * @param string $format received data format
	 * @param mixed|null $data
	 * @return array
	 */
	public function parse( $format = self::RESPONSE_DATA_FORMAT_XML, $data = null ) {
		
		switch ( $format ) {
			case self::RESPONSE_DATA_FORMAT_XML:
				return $this->parse_Xml( $data );
			case self::RESPONSE_DATA_FORMAT_JSON:
				return $this->parse_Json( $data );
			case self::RESPONSE_DATA_FORMAT_TEXT:
				return $this->parse_Text( $data );
			case self::RESPONSE_DATA_FORMAT_PHP:
				return $this->parse_Php( $data );
			case self::RESPONSE_DATA_FORMAT_CSV:
				return $this->parse_Csv( $data );
			case self::RESPONSE_DATA_FORMAT_TSV:
				return $this->parse_Tsv( $data );
			default:
				break;
		}
		return $data;
		
	}

	/**
	 * parse xml data to array
	 * 
	 * @access private
	 * @uses XML_Unserializer
	 * @param string|null $xml
	 * @return array
	 */
	private function parse_Xml( $xml = null ) {
		//return $this->parse_Xml_SimpleXml( $xml );
		return $this->parse_Xml_PearXml( $xml );
	}
	
	private function parse_Xml_SimpleXml( $xml = null ) {
		if ( $xml === null ) {
			$xml = $this->response;
		}
		if ( function_exists( 'libxml_use_internal_errors' ) ) {
			libxml_use_internal_errors( true );
		}
		$parser = simplexml_load_string( $xml );
		$parsedData = ( array )$parser;
		
		if ( function_exists( 'libxml_get_errors' ) ) {
			$parseErrors = libxml_get_errors();
			if ( count( $parseErrors ) > 0 ) {
				require_once 'My/Exception.php';
				throw new My_Exception( 
					'Unable to parse xml. This data has including causes parsing error.  '
				 	. self::getXmlErrorToString( $parseErrors[0], explode( "\n", $xml ) )
				);
			}
		}
		
		if ( is_array( $parsedData ) === FALSE ) {
			self::$logger->error( $parsedData );
			return array();
		}
		
		
		return $parsedData;
	}
	
	private function parse_Xml_PearXml( $xml = null ) {
		require_once 'XML/Unserializer.php';
		if ( $xml === null ) {
			$xml = $this->response;
		}
		
		$options = array( 
			XML_UNSERIALIZER_OPTION_ATTRIBUTES_PARSE 	=> 'parseAttributes',
			XML_UNSERIALIZER_OPTION_RETURN_RESULT 		=> true,
		);
		$parser = new XML_Unserializer( $options );
		
		$parsedData = $parser->unserialize( $xml, false, $options );
		if ( PEAR::isError( $parsedData ) ) {
			self::$logger->error( $parsedData->getMessage() );
			return array();
		}
		
		if ( is_array( $parsedData ) === FALSE ) {
			self::$logger->error( $parsedData );
			return array();
		}
		
		
		return $parsedData;
	}
	
	/**
	 * parse json data to array
	 * 
	 * @access private
	 * @uses json_decode
	 * @param string|null $json
	 * @return array
	 */
	private function parse_Json( $json = null ) {
		if ( $json === null ) {
			$json = $this->response;
		}
		// json_decode is supported on newer PHP 5.2.0 or newer PECL 1.2.0. 
		if ( version_compare( PHP_VERSION, '5.2.0', '<' )) {
			// does already exists function name of json_decode 
			if ( function_exists( 'json_decode' ) == FALSE ) {
				// when not supported...
				trigger_error( 'undefined function "json_decode". please install pecl 1.2.0 and json module or to use php 5.2.0.', E_USER_ERROR );
				return array();
			}
		}
		
		return json_decode( $json, true );
	}
	
	/**
	 * parse serialized php text data to array
	 * 
	 * @access private
	 * @param string|null $php
	 * @return array
	 */
	private function parse_Php( $php = null ) {
		if ( $php === null ) {
			$php = $this->response;
		}
		return unserialize( $php );
	}
	
	/**
	 * parse tsv ( text separated value ) data to array
	 * 
	 * @access private
	 * @param strong|null $tsv
	 * @return array
	 */
	private function parse_Tsv( $tsv = null ) {
		if ( $tsv === NULL ) {
			$tsv = $this->__response;
		}
		
		$lines = preg_split( "/\r?\n/", $tsv );
		// get fields line
		$fieldline = array_shift( $lines );
		$fieldline = trim( $fieldline );
		$fields = explode( "\t", $fieldline );
		$fieldCount = count( $fields );
		foreach ( $lines as $line ) {
			$line = trim( $line );
			if ( strlen( $line ) == 0 ) continue;
			
			$linedata = explode( "\t", $line );
			$datas = null; $i = 0;
			if ( $i < $fieldCount ) {
				$datas[$fields[$i]] = $linedata[$i]; $i++;
			}
			$parsedData[] = $datas;
		}
		return $parsedData;
	}
	
	/**
	 * parse csv ( comma separated value ) data to array
	 * 
	 * @access private
	 * @param string|null $csv
	 * @return array
	 */
	private function parse_Csv( $csv = null ) {
		if ( $csv === NULL ) {
			$csv = $this->__response;
		}
		// output temporary file
		// its want to use fgetcsv function, 
		// that is safty parsing for csv data.
		// becausethe the funcion cannot be to use on variables data.
		$fp = tmpfile();
		
		$length = strlen( $csv );
			
		// write csv data
		fwrite( $fp, $csv, $length );
		// rewind the position of a file pointer
		rewind( $fp );
		
		// get fields line
		$fields = fgetcsv( $fp, 0 );
		
		$fieldCount = count( $fields );
		
		$parsedData = array();
		while ( feof( $fp ) == FALSE ) {
			// read line data
			$linedata = fgetcsv( $fp, 0 );
			
			if ( $linedata == NULL || count( $linedata ) == 0 ) continue;
			
			$datas = null; $i = 0;
			while ( $i < $fieldCount ) {
				$datas[$fields[$i]] = $linedata[$i]; $i++;
			}
			$parsedData[] = $datas;
		}
		return $parsedData;
	}
	
	/**
	 * return to message of xml error
	 * 
	 * @param libXMLError $error
	 * @param array $xml Line splited xml array
	 * @return string
	 */
	private static function getXmlErrorToString( libXMLError $error, array $xml )
	{
		$return  = $xml[$error->line - 1] . "\n"
				 . str_repeat( '-', $error->column ) . "^\n";
		
		switch ( $error->level ) {
			case LIBXML_ERR_WARNING:
				$return .= 'Warning ' . $error->code . ': ';
				break;
			 case LIBXML_ERR_ERROR:
				$return .= 'Error ' . $error->code . ': ';
				break;
			case LIBXML_ERR_FATAL:
				$return .= 'Fatal ' . $error->code . ': ';
				break;
		}
		
		$return .= trim( $error->message )
				 . '  Line: ' . $error->line
				 . '  Column: ' . $error->column
				 ;
		
		if ( $error->file ) {
			$return .= '  File: ' . $error->file;
		}
		return $return;
	}
}
