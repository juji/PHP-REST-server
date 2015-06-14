<?php
	if(!defined('__ISINCLUDED')) {die('Direct access not permitted');}

	class Error{
		static $url = 'unsupported API URL';
		static $method = 'unsupported HTTP VERB';
		static $auth = 'Need to login';
		static $data = 'Data is empty';
		static $nomessage = 'Error-Message is empty. Call your developer';
		static $logfile;

		static function fatal($str){
			return '<b>FATAL-ERROR, CALL DEVELOPER.</b><br />'.$str;
		}

		static function nodata($str){
			return $str.' should not be empty.';
		}

		static function modul($m){
			return 'Modul Not found: '.$m;
		}

		static function init(){
			//error reporting
			error_reporting(E_ALL | E_STRICT);

			ob_start(function($out){

				$b = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, Config::$BACKTRACE_LIMIT);
				$e = error_get_last();


				if(!empty($e['message'])) {
					
					if(Config::$LOG_FILE){ Error::$logfile = fopen(Config::$LOG_FILE,'ab'); }

					$st = "<br /><b>".$e['message']."</b><br />( ".$e['file'].' ['.$e['line']."] )<br />";
					foreach ($b as $key => $v) {
						$st .= '  '.$v['file'].' ['.$v['line']."]; function: ".$v['function']."<br />";
					}
					$st .= "<br />";
					
					if(Config::$ERROR_EMAIL) mail(Config::$ERROR_EMAIL, Config::$ERROR_EMAIL_SUBJECT, $st,
													'From: api-system@'.$_SERVER['HTTP_HOST']);

					Response::setStatus(502);
					Response::sendHeaders();
					
					return Response::create(array('status'=>false,'message'=>Config::$ERROR_MSSG?Config::$ERROR_MSSG:$st));

				}

				return $out;
			});

		}
	}




	//------------------------------
	class Rest{
		// about this server.

		static private $rootdir = '';

		// isCors() Boolean
		// root() rootDir String

		static function isCors(){
			return Config::$CORS && Request::origin() &&
			(Config::$CORS_ALLOW == '*' || 
			(sizeof(Config::$CORS_ALLOW) && is_array(Config::$CORS_ALLOW) && 
			in_array(Request::originDomain(),Config::$CORS_ALLOW)));
		}

		public static function root(){
			return self::$rootdir;
		}

		private static function getModule(){
			$u = Request::uri();
			if(!sizeof($u)) Response::error(Error::$url, 404);
			$f = $u[0];
			if(file_exists('_module/'.$f.'.php')) {
				include ('_module/'.$f.'.php');
			}else if (file_exists('_module/'.$f.'/index.php')){
				include ('_module/'.$f.'/index.php');
			}else{
				Response::error( Error::modul($f), 404 );
			}

		}

		public static function init(){

			Error::init();

			//init all
			self::$rootdir = Config::$ROOT_DIR;
			Request::init();
			//check CORS
			if(Config::$CORS && Config::$CORS_ALLOW!='*'&&!self::isCors()){
				die('Not CORS');
			}

			Router::init(self::$rootdir);

			// set OPTIONS response for CORS
			if(Config::$CORS && self::isCors()) Router::add('options','*',function(){
				Response::sendHeaders();
				die();
			});
			
		}

		public static function start(){

			self::getModule();
			Router::notfound(function(){ Response::error(Error::$url, 404); });
			Router::start();

			// set OPTIONS response for CORS
			

		}


		//debug
		static function pp($d,$r=false){
			$s='<pre>';
			$s.=print_r($d,$r);
			$s.= '</pre>'; 
			if($r) return $s;
			print $s;
		}

	}




	//------------------------------
	class Request{

		//to handle request. deps: Config

		static private $u;		// uri in array
		static private $m;		// method lowercase
		static private $d;		// data
		static private $q;		// url query
		static private $x;		// is xhr (x-requested-with)
		static private $o;		// origin header
		static private $od;		// origin domain

		static function uri(){ return self::$u; }
		static function method(){ return self::$m; }
		static function data(){ return self::$d; }
		static function query(){ return self::$q; }
		static function xhr(){ return self::$x; }
		static function origin(){ return self::$o; }
		static function originDomain(){ return self::$od; }

		static private function normalizeUri(){ 
			if(!sizeof(self::$u)) return; 
			if(self::$u[0]) return; 
			array_shift(self::$u);
			self::normalizeUri();
		}

		static function init(){
			$method = strtolower($_SERVER['REQUEST_METHOD']);

			//get method
			self::$m = $method;
			
			//get data
			if ($method == "put" || $method == "delete") {
				
				parse_str(file_get_contents('php://input'), self::$d);
				$m = strtoupper($method);
				$GLOBALS["_{$m}"] = self::$d;
				
				// Add these request vars into _REQUEST, 
				// mimicing default behavior, 
				// PUT/DELETE will override existing COOKIE/GET vars
				$_REQUEST = Utils::clean(self::$d) + $_REQUEST;
				
			} else if ($method == "get") {
				self::$d = $_GET;
			} else if ($method == "post") {
				self::$d = $_POST;
			}
			
			//clean data
			if(!empty(self::$d)) self::$d = self::clean(self::$d);

			//add $_PUT and $_DELETE, cleaan $_GET aand $_POST
			if ($method == "put") $GLOBALS['_PUT'] = self::$d;
			if ($method == "delete") $GLOBALS['_DELETE'] = self::$d;
			if ($method == "get") $GLOBALS['_GET'] = self::$d;
			if ($method == "post") $GLOBALS['_POST'] = self::$d;

			//is xhr?
			self::$x = (
				isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
				strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'
			) || (
				!empty(Config::$CONTENT_TYPE) &&
				Config::$CONTENT_TYPE == 'json'
			);


			// get origin
			self::$o = isset($_SERVER['HTTP_ORIGIN']) &&  $_SERVER['HTTP_ORIGIN'] ? $_SERVER['HTTP_ORIGIN'] : false;
			self::$od = self::$o ? parse_url(self::$o) : false;
			self::$od = self::$od && isset(self::$od['host']) ? self::$od['host'] : false;


			//uri, in array
			self::$q = $_SERVER['QUERY_STRING'];
			self::$u = preg_replace('/\?.*/', '', $_SERVER['REQUEST_URI']);
			self::$u = str_replace(Rest::root(), '', self::$u);
			self::$u = preg_replace('`/+`', '/', self::$u);
			self::$u = preg_replace('`/$`', '', self::$u);
			self::$u = preg_replace('`^/`', '', self::$u);
			self::$u = explode('/',self::$u);
			self::normalizeUri();
		}

		private static function cleanRec($arr){
			foreach($arr as $k=>&$v){
				if(is_array($v)) $v = self::cleanRec($v);
				else $v = stripslashes($v);
			}
			return $arr;
		}
	
		private static function clean($str){
			if(get_magic_quotes_gpc()){
				if(is_array($str)){
					$str = self::cleanRec($str);
				}else{
					$str = stripslashes($str);
				}
			}
			return $str;
		}
	}



	//------------------------------
	class Response{

		//to send response. deps: Config, Request, API
		static private $status;

		// setStatus();
		// sendHeaders();
		// ok();
		// error();

		public static function setStatus($int){
			self::$status = $int;
		}

		public static function sendHeaders(){

			//status
			if(self::$status!=200) http_response_code(self::$status);

			// CORS
    		if (Rest::isCors()) {
    		    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    		    header("Access-Control-Allow-Methods: " . Config::$CORS_METHODS);
    		    header("Access-Control-Allow-Headers: " . Config::$CORS_HEADERS);
    		    header("Access-Control-Allow-Credentials: true");
    		}
    		
    		//cache control
    		header("Cache-Control: no-store, no-cache, must-revalidate");
			header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

			//content type
			header('Content-Type: ' . (Request::xhr() ? 'application/json' : 'text/html'));

		}

		private static function send($data){
			if(!is_array($data)) throw new Exception('API response should return JSON');
			if(!isset($data['status'])) throw new Exception('API response status unknown');
			if(!isset($data['message'])) throw new Exception('API response message unknown');
			$str = self::create($data);

			self::sendHeaders();
			if(Request::method()=='head'){
				header('Content-Length: '.strlen($str));
				die();
			}
			die($str);

		}

		public static function ok($str=true){
			self::send(array('status'=>true,'message'=>$str));
		}
		
		public static function error($str=false,$s=400){
			self::$status = $s;
			$str = $str ? $str : Error::$nomessage;
			self::send(array('status'=>false,'message'=>$str));
		}

		public static function create($json){
			$json = json_encode($json);
			if(Request::xhr()) return $json;
			return "<!DOCTYPE HTML>\n".
				"<html><head><script>".(Rest::isCors()?"document.domain='".Request::originDomain()."';":'').
				"</script></head><body><div id=\"".Config::$HTMLELEMENT_ID."\">$json</div></body></html>";
		}
	}


	//------------------------------
	class Router{
		
		public static $routes;
		public static $base;
		public static $notfound;
		

		public static function init($base=''){
			self::$routes = array();
			self::$base = preg_replace('/\/+/','/', '/'. preg_replace('`/$`','',preg_replace('`^/`','',$base)) . '/');
			if(self::$base=='/') self::$base = self::getDir();
		}


		private static function getDir(){
			return preg_replace('/\/+/','/',str_replace(realpath($_SERVER['DOCUMENT_ROOT']), '', getcwd()) . '/');
		}


		public static function notfound($c){
			if(!is_callable($c))
			throw new Exception('Router error: Need a callable to respond a route');
			self::$notfound = $c;
		}

		
		public static function add($pattern,$callable){
			$num = func_num_args();
			$arg = func_get_args();
			if($num==3){ 
				$arg[1] = preg_replace('`/+$`','',$arg[1]);
				$arg[1] = preg_replace('`^/+`','',$arg[1]);
				$arg[0] = strtolower($arg[0]);

				if( strpos($arg[0], ' ') !== false ){

					$arg[0] = explode(',',str_replace('/\s+/', ' ', $arg[0]));
					
					foreach ($arg[0] as $key => $value)
					self::$routes[] = array($value,$arg[1],$arg[2]);

				}else{

					self::$routes[] = $arg;

				}
			}
			else if($num==2){ 
				$arg[0] = preg_replace('`/+$`','',$arg[0]);
				$arg[0] = preg_replace('`^/+`','',$arg[0]);
				self::$routes[] = array('get',$arg[0],$arg[1]); 
			}
			else throw new Exception('Router error: argument should be 2 or 3');
			
			if(!is_callable(self::$routes[sizeof(self::$routes)-1][2]))
			throw new Exception('Router error: Need a callable to respond a route');
		}


		private static function clean($str){
			if(get_magic_quotes_gpc()) return stripslashes($str);
			return $str;
		}
		

		public static function start(){
			
			$method = strtolower($_SERVER['REQUEST_METHOD']);

			if(!empty($_GET['_method'])) $method = self::$clean($_GET['_method']);
			if(!empty($_POST['_method'])) $method = self::$clean($_POST['_method']);

			$path = preg_replace('`^'.preg_quote(self::$base).'`','',preg_replace('`\?.*$`','',$_SERVER['REQUEST_URI']));
			$path = preg_replace('`/+$`','',$path);

			$__arr = array();
			$__arr['method'] = $method;
			$__arr['path'] = $path;
			$__arr['base'] = self::$base;
			$__arr['vars'] = array();

			$func = false;
			foreach(self::$routes as $k=>$v){
				if($v[0]!=$method) continue;
				
				$r = preg_replace('/\:[^\/\*]+\*/','(.*?)',$v[1]);
				$r = preg_replace('/\*/','(.*?)',$v[1]);
				$r = preg_replace('/\:[^\/]+/','([^\/]+)',$r);
				if(!preg_match_all('`^'.$r.'$`',$path,$pat)) continue;

				$__arr['match'] = $v[1];
				$__arr['vars'] = array();
				preg_match_all('/\:([^\/]+)/',$v[1],$var);
				foreach($var[1] as $kk=>$vv){
					$__arr['vars'][preg_replace('/\*/','',$vv)] = $pat[$kk+1][0];
				}

				$func = $v[2];

			}

			if($func) call_user_func($func,$__arr);
			else call_user_func(self::$notfound,$__arr);

			return true;
		}
		
	}
	
?>
