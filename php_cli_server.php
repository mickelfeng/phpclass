<?php

ini_set('display_errors', false);
error_reporting(0);

if(!class_exists('thread')) {
	logs('PHP runtime environment not support multi thread');
	exit(0);
}

if(!function_exists('mime_content_type')) {
	logs('PHP runtime environment not support function mime_content_type()');
	exit(0);
}

class pthread extends thread {
	
	protected $socket = null;
	protected $arguments = null;
	protected $connections = 0;
	protected $octet_stream = false;
	
	public function __construct($socket, $arguments = array()) {
	
		$this->socket = $socket;
		$this->arguments = $arguments;
		if(!isset($this->arguments['ServerTokens']))
		$this->arguments['ServerTokens'] = 'off';

		
	}
	
	public function run() {
		
		date_default_timezone_set('UTC');
		
		$clients = 1;
		$maxRequests = !isset($this->arguments['MaxRequests'])?
		intval($this->arguments['MaxRequests']):
		100;
		$timeout = 5;
		
		$connfd = socket_accept($this->socket);
		socket_set_option($connfd, SOL_SOCKET, SO_RCVTIMEO, array('sec' => $timeout, 'usec' => 0));
		
		$session = 1;
		
		while($session) {
		
			$buffer = '';
			while (( $buffer .= socket_read($connfd, 1024, PHP_BINARY_READ) ))
			if(strpos($buffer, "\r\n\r\n") !== false) break;
			
			if($buffer == '') {
				socket_close($connfd);
				$session = 0;
			}else{
				
				$availableRequests = $maxRequests - $clients;
				$clients++;
				
				$i = 0;
				$headers = array();
				array_push($headers, 'HTTP/1.1 200 OK');
				array_push($headers, 'Date: '. gmtdate());
				array_push($headers, 'Server: PHP-CLI/1.0');
				array_push($headers, 'Content-Type: text/html; charset=utf-8');
				array_push($headers, 'Connection: close');

				if($this->arguments['ServerTokens'] == 'on')
				$headers[2] = 'Server: PHP-CLI';

				$buffer = explode("\r\n", $buffer);
				
				$http_user_agent = '';
				$http_request_method = '';
				$http_request_file = '';
				$http_protocol = '';
				$extension = '';
				$mime_types = '';
				$this->octet_stream = false;
				
				foreach($buffer as $line) {
					$pattern = '/(GET|POST)\s\/(.*)\s(HTTP\/1\.[0-1])$/';
					if(preg_match($pattern, $line)) {
						$http_request_method = preg_replace($pattern, '\\1', $line);
						$http_request_file = preg_replace($pattern, '\\2', $line);
						$http_protocol = preg_replace($pattern, '\\3', $line);
					}
					$pattern = '/^User\-Agent: (.+)$/';
					if(preg_match($pattern, $line)) {
						$http_user_agent = preg_replace($pattern, '\\1', $line);
					}
				}
				
				$local_request_file = $this->arguments['DocumentRoot'].'/'. $http_request_file;
				if(file_exists($local_request_file) && is_file($local_request_file)) 
				$extension = pathinfo($local_request_file, PATHINFO_EXTENSION);
				
				if(file_exists($local_request_file)) {
					$array_key_exists = array_key_exists($extension, $this->arguments['MimeTypes']);
					
					if(is_file($local_request_file)) {
						if($array_key_exists) {
							$mime_types = $this->arguments['MimeTypes'][$extension];
							$headers[3] = sprintf('Content-Type: %s; charset=%s', $mime_types, 'utf-8');
						}else{
							$this->octet_stream = true;
							$headers[3] = sprintf('Content-Type: application/octet-stream');
							array_push($headers, 'Accept-Ranges: bytes');
							array_push($headers, 'Accept-Length: '.filesize($local_request_file));
							array_push($headers, 'Content-Disposition: attachment; filename='.basename($local_request_file));
						}
					}
					
				}
				
				$html = '';
				$code = '';
				$this->HttpStatusCode($local_request_file, $headers, $html, $code);

				if($availableRequests > 0) {
					$headers[4] = "Connection: keep-alive";
					$headers[5] = 'Keep-Alive: timeout='.$timeout.', max='.$maxRequests;
				}
				

				$headers[6] = 'Content-Length: '. strlen($html); 
				
				$response = array(
				'header'=> implode("\r\n", $headers) . "\r\n",
				'html'=> $html);

				socket_write($connfd, implode("\r\n", $response));

				if($availableRequests <= 0) {
					socket_close($connfd);
					$session = 0;
				}
				
				$length = strlen($html);
				
				socket_getpeername($connfd, $address, $port);
				logs(sprintf('%s:%.0f -- "%s %s %s" %s %.0f "-" "%s"', 
				$address,
				$port,
				$http_request_method,
				'/'.$http_request_file,
				$http_protocol,
				$code,
				$length,
				$http_user_agent));
				//logs('times '. intval($clients - 1), false);
			}
		}
		
	}
	
	public function error_page($statusCode, $ServerTokens) {
	
		$httpStatus = array('403'=> '403 Forbidden', '404'=> '404 Not Found');
		$string = "<html>
		<head>
		<title>%s</title>
		</head>
		<body>
		<center><h1>%s</h1></center>
		<hr />
		<center>%s</center>
		</body>
		</html>";
		
		if(!in_array($ServerTokens, array('on', 'off')))
		$ServerTokens = 'off';
		
		return (string) sprintf($string, 
		$httpStatus[$statusCode],
		$httpStatus[$statusCode],	
		$ServerTokens == 'off' ? 'PHP-CLI/1.0' : 'PHP-CLI');
		
	}
	
	public function HttpStatusCode($file, &$headers, &$html, &$code) {
		
		$code = '200';
		if(!file_exists($file)) {
			$headers[0] = 'HTTP/1.1 404 Not Found';
			$html = $this->error_page('404', $this->arguments['ServerTokens']);
			$code = '404';
			return 0;
		}
		
		if(is_dir($file)){
			$find = false;
			$directoryIndex = $this->arguments['DirectoryIndex'];
			if(empty($directoryIndex)) {
				$headers[0] = 'HTTP/1.1 403 Forbidden';
				$code = '403';
			}else{
				$list = explode(' ', $directoryIndex);
				foreach($list as $index) {
					if(file_exists($file .'/'. $index)) {
						$file .= '/'. $index;
						if(file_exists($file) && is_file($file)) 
						$extension = pathinfo($file, PATHINFO_EXTENSION);
						$array_key_exists = array_key_exists($extension, $this->arguments['MimeTypes']);
						if($array_key_exists) {
							$mime_types = $this->arguments['MimeTypes'][$extension];
						}else{
							$this->otect_stream = true;
							$headers[3] = sprintf('Content-Type: application/octet-stream');
							array_push($headers, 'Accept-Ranges: bytes');
							array_push($headers, 'Accept-Length: '.filesize($local_request_file));
							array_push($headers, 'Content-Disposition: attachment; filename='.basename($local_request_file));
						}
						$find = true;
						break;
					}
				}
			}
			
			if(!$find) {
				$html = $this->error_page('403', $this->arguments['ServerTokens']);
			}else{
				if(!$this->octet_stream)
				$headers[3] = sprintf('Content-Type: %s; charset=%s', $mime_types, 'utf-8');
				$html = $this->get_local_handle_buffer($file);
			}
			
			return -1;
			
		}else{
		
			$html = $this->get_local_handle_buffer($file);
	
		}
		
		return 1;
	}
	
	public function get_local_handle_buffer($file) {
		$handle = fopen($file, 'rb');
		return $this->get_buffer($handle);
	}
	
	public function get_buffer($handle) {
		$buffer = '';
		if(!is_resource($handle)) return null;
		while(!feof($handle))
		$buffer .= fgets($handle, 1024);
		fclose($handle);
		return $buffer;
	}
	
}

function gmtdate() {
	return (string) date('D, d M Y H:i:s'). ' GMT';
}

function logs($string, $perfix = true) {
	ob_start();
	echo $perfix ? 
	sprintf("[ %s ] %s\n", date('d-M-Y H:i:s'), $string) : 
	sprintf("\0\0[ %s ]\n", $string);
	
	ob_end_flush();

}

$mime_types = array(
'htm'=> 'text/html',
'html'=> 'text/html',
'jpg'=> 'image/jpeg',
'jpeg'=> 'image/jpeg',
'png'=> 'image/png',
'js'=> 'text/javascript',
'css'=> 'text/css',
'xml'=> 'text/xml');

$conf = array(
'MimeTypes'=> $mime_types,
'ServerTokens'=> 'on',
'MaxRequests'=> 100,
'Timeout'=> 15,
'Listen'=> 8080,
'DocumentRoot'=> '/home/www', 
'DirectoryIndex'=> 'index.htm index.html');

error_reporting(E_ALL);
logs('Initializing the operating environment');
sleep(1);

set_time_limit(0);
logs('Initializing PHP-CLI execution timeout');
sleep(1);

$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
logs('Initializing the socket');
sleep(1);

logs('Initialization bind local address any ip address');
sleep(1);

$int = socket_bind($socket, '0.0.0.0', $conf['Listen']);
logs('Initialization bind local port '.$conf['Listen']);
if(!$int){
	logs($conf['Listen'].' Port is occupied by other services'."\n");
	exit(0);
}
sleep(1);

socket_listen($socket, 1024);
logs('Opening a socket listening');
sleep(1);

logs('Waiting for clients to access');
echo "\n";
$i = 0;

while(1) {
	$pthread[$i] = new pthread($socket, $conf);
	$pthread[$i]->start();
	$pthread[$i]->join();
}
