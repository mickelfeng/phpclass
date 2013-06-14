<?php
/**
Verify code class. <br />
this will need the extension of the gb library.

@author  chenxin
@email	chenxin619315@gmail.com
*/

class VerifyCode {
	
	/**letters generator array.*/
	private static $_letters = array('1','2','3','4','5','6','7','8','9','0',
			'a','b','c','d','e','f','g','h','i','j','k','l','m','n','o',
			'p','q','r','s','t','u','v','w','x','y','z','A','B','C','D',
			'E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S',
			'T','U','V','W','X','Y','Z');
	private $_length = 0;
	
	/**configuration array.*/
	private $_config = array('x'=>0, 'y'=>0, 'w'=>100, 'h'=>50, 'f'=>3);
	
	/*image resource pointer*/
	private $_image = NULL;
	private $_codes = NULL;
	
	/*instance pointer.*/
	private static $_instance = NULL;
	
	private function __construct() {
		$this->_length = count(self::$_letters);
	}
	
	/**
	get the instance of the VerifyCode.
	
	@return	$_instance
	*/
	public static function getInstance() {
		if ( ! (self::$_instance instanceof self) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	/**
	configure the attribute of the Verify code. <br />
	*/
	public function configure( $_array = NULL ) {
		if ( $_array != NULL ) {
			if ( isset( $_array['x'] ) ) $this->_config['x'] = $_array['x'];
			if ( isset( $_array['y'] ) ) $this->_config['y'] = $_array['y'];
			if ( isset( $_array['w'] ) ) $this->_config['w'] = $_array['w'];
			if ( isset( $_array['h'] ) ) $this->_config['h'] = $_array['h'];
			if ( isset( $_array['f'] ) ) $this->_config['f'] = $_array['f'];
		}
		return self::$_instance;
	}
	
	/**
	 * generate some new verify code chars
	 *	and draw them on the image. <br />
	 *
	 * @param	$_size		number of chars to generate
	 * @return	$_codes
	 */
	public function generate( $_size = 3 ) {
		assert($_size > 0);
		
		$this->_codes = array();
		while ( $_size-- > 0 ) {
			$this->_codes[] = self::$_letters[mt_rand() % $this->_length];
		}
		
		return implode('', $this->_codes);
	}
	
	/**
	 * create a image resource,
	 * 		draw the codes just generated. <br />
	 */
	private function createImage() {
		if ( $this->_image != NULL ) imagedestroy( $this->_image );
		$this->_image = imagecreatetruecolor($this->_config['w'], $this->_config['h']);
		switch ( mt_rand() % 4 ) {
		case 0: $_bg = imagecolorallocate($this->_image, 250, 250, 250); break;
		case 1: $_bg = imagecolorallocate($this->_image, 255, 252, 232); break;
		case 2: $_bg = imagecolorallocate($this->_image, 254, 245, 243); break;
		case 3: $_bg = imagecolorallocate($this->_image, 233, 255, 242); break;
		}
		imagefilledrectangle($this->_image, 0, 0, $this->_config['w'], $this->_config['h'], $_bg);
		//imagefilter($this->_image, IMG_FILTER_EMBOSS);
		
		switch ( mt_rand() % 5 ) {
		case 0:	$_color = imagecolorallocate($this->_image, 128, 128, 128);	break;	//gray
		case 1:	$_color = imagecolorallocate($this->_image, 16, 9, 140);	break;	//blue
		case 2: $_color = imagecolorallocate($this->_image, 65, 125, 0);	break;	//green
		case 3: $_color = imagecolorallocate($this->_image, 255, 75, 45);	break;	//read
		case 4: $_color = imagecolorallocate($this->_image, 238, 175, 7);	break;	//orange
		}
		//$_color = imagecolorallocate($this->_image, 238, 175, 7);
		$_font = dirname(__FILE__) . DIRECTORY_SEPARATOR. 'fonts' . DIRECTORY_SEPARATOR . 'ariblk.ttf';
		//$_angle = (mt_rand() & 0x01) == 0 ? mt_rand() % 30 : - mt_rand() % 30;
		
		//draw the code chars
		$_size = count($this->_codes);
		$_xstep = ($this->_config['w'] - 2 * $this->_config['x']) / $_size;
		$_ret = 0;
		for ( $i = 0; $i < $_size; $i++ ) {
			$_ret = mt_rand();
			imagettftext($this->_image, $this->_config['f'],
					($_ret & 0x01) == 0 ? $_ret % 30 : - ($_ret % 30),
						$this->_config['x'] + $i * $_xstep, $this->_config['y'],
							$_color, $_font, $this->_codes[$i]);
		}
	}
	
	/**
	 * save the image resource to a file. <br />
	 *
	 * @param	$_file
	 */
	public function saveToFile( $_file ) {
		throw new Exception('Invalid call to function saveToFile.');
	}
	
	/**
	 * show the image resource to the browser
	 *	by send a header message. <br />
	 *
	 * @param	$_suffix image suffix
	 */
	public function show( $_suffix = 'png' ) {
		//echo implode(',', $this->_codes);
		$this->createImage();
		
		if ( $_suffix == 'gif' && function_exists("imagegif")) {
			header("Content-type: image/gif");
			imagegif($this->_image);
		} elseif ( $_suffix == 'jpeg' && function_exists("imagejpeg")) {
			header("Content-type: image/jpeg");
			imagejpeg($this->_image, "", 0.9);
		} elseif ( $_suffix == 'png' && function_exists("imagepng")) {
			header("Content-type: image/png");
			imagepng($this->_image);
		} elseif ( $_suffix == 'jpeg' && function_exists("imagewbmp")) {
			header("Content-type: image/vnd.wap.wbmp");
			imagewbmp($this->_image);
		} else {
			die("No image support in this PHP server");
		} 
	}
}
?>
<?php
require_once 'VerifyCode.class.php';

//1.create a new VerifyCode instance.
$_verify = VerifyCode::getInstance();
//2.configure the verify code and generate 4 chars
$_vcode = $_verify->configure(array('x'=>10, 'y'=>35, 'w'=>135, 'h'=>50, 'f'=>24))->generate(4);
//3.send the image to the browser(gif,jpg,png)
$_verify->show('gif');
//echo $_vcode;
?>

