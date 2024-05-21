<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class hikashopImageHelper {
	public $thumbnail = 1;

	public $uploadFolder_url = null;
	public $uploadFolder = null;
	public $thumbnail_x = 100;
	public $thumbnail_y = 100;

	public $main_uploadFolder_url = null;
	public $main_uploadFolder = null;
	public $main_thumbnail_x = null;
	public $main_thumbnail_y = null;

	public $override = false;

	protected $image_mode = null;

	public function __construct() {
		$app = JFactory::getApplication();
		$config =& hikashop_config();

		$uploadFolder = ltrim(JPath::clean(html_entity_decode($config->get('uploadfolder'))),DS);
		$uploadFolder = rtrim($uploadFolder,DS).DS;
		$this->uploadFolder_url = str_replace(DS,'/',$uploadFolder);
		$this->uploadFolder = JPATH_ROOT.DS.$uploadFolder;

		if(hikashop_isClient('administrator')) {
			$this->uploadFolder_url = '../'.$this->uploadFolder_url;
		}else{
			$this->uploadFolder_url = rtrim(JURI::base(true),'/').'/'.$this->uploadFolder_url;
		}
		$this->main_uploadFolder_url = $this->uploadFolder_url;
		$this->main_uploadFolder = $this->uploadFolder;

		$this->thumbnail = (int)$config->get('thumbnail', 1);

		$this->thumbnail_x = (int)$config->get('thumbnail_x', 100);
		$this->thumbnail_y = (int)$config->get('thumbnail_y', 100);
		$this->main_thumbnail_x = $this->thumbnail_x;
		$this->main_thumbnail_y = $this->thumbnail_y;

		$this->image_mode = $this->autoDetectMode();

		static $override = null;
		if($override === null) {
			$override = false;
			$chromePath = JPATH_THEMES.DS.$app->getTemplate().DS.'html'.DS.'hikashop_image.php';
			if(file_exists($chromePath)) {
				require_once ($chromePath);
				$override = true;
			}
		}
		$this->override = $override;
	}

	private function autoDetectMode() {
		static $lib = null;
		if($lib !== null)
			return $lib;

		if(extension_loaded('gd')) {
			$lib = 'GD';
			return $lib;
		}

		$app = JFactory::getApplication();
		if(hikashop_isClient('administrator')) {
			$app->enqueueMessage('The PHP GD extension could not be found. Thus, it is impossible to generate thumbnails in PHP from your images. If you want HikaShop to generate thumbnails you need to install/activate GD or ask your hosting company to do so.');
		}

		$lib = false;
		return false;
	}

	public function getPath($file_path, $url = true) {
		if($url)
			return $this->uploadFolder_url . $file_path;
		return $this->uploadFolder . $file_path;
	}

	public function getFileExtension($filename) {
		return strtolower(substr($filename, strrpos($filename, '.') + 1));
	}

	public function getDefaultImage() {
		jimport('joomla.filesystem.file');

		$config = hikashop_config();
		$path = $config->get('default_image');
		$file_path = HIKASHOP_MEDIA.'images'.DS.'barcode.png';
		if(!empty($path) && JFile::exists($this->main_uploadFolder . $path))
			$file_path = $this->main_uploadFolder . $path;
		return $file_path;
	}

	public function checkSize(&$width, &$height, &$fileObj) {
		jimport('joomla.filesystem.file');

		if(!empty($fileObj->file_path) && JFile::exists($this->main_uploadFolder . $fileObj->file_path)) {
			$file_path = $this->main_uploadFolder . $fileObj->file_path;
		} else {
			$file_path = $this->getDefaultImage();
		}

		if(empty($file_path))
			return;

		list($f_w, $f_h) = @getimagesize($file_path);
		if(empty($width)) {
			if($f_h >= $height)
				list($width, $height) = $this->scaleImage($f_w, $f_h, 0, $height);
			else
				$width = $this->main_thumbnail_x;
		}
		if(empty($height)) {
			if($f_w >= $width)
				list($width, $height) = $this->scaleImage($f_w, $f_h, $width, 0);
			else
				$height = $this->main_thumbnail_y;
		}
	}

	public function scaleImage($x, $y, $cx, $cy, $scaleMode = 'inside') {
		if(empty($cx)) $cx = 9999;
		if(empty($cy)) $cy = 9999;

		if($x < $cx && $y < $cy)
			return false;

		if($x > 0) $rx = $cx / $x;
		if($y > 0) $ry = $cy / $y;

		switch($scaleMode) {
			case 'outside':
				$r = ($rx > $ry) ? $rx : $ry;
			break;

			case 'inside':
			default:
				$r = ($rx > $ry) ? $ry : $rx;
			break;
		}
		$x = intval($x * $r);
		$y = intval($y * $r);
		return array($x, $y);
	}

	protected function _checkImage($path) {
		if(empty($path))
			return false;
		jimport('joomla.filesystem.file');
		return JFile::exists($path);
	}

	protected function getMemoryLimit() {
		static $memory_limit = null;
		if($memory_limit !== null)
			return $memory_limit;

		$memory_limit = ini_get('memory_limit');
		if(preg_match('/^(\d+)\s*(.)$/', $memory_limit, $matches)) {
			$m = array('G' => 1073741824, 'M' => 1048576, 'K' => 1024);
			$unit = strtoupper($matches[2]);
			if(isset($m[ $unit ]))
				$memory_limit = (int)$matches[1] * $m[ $unit ];
			else
				$memory_limit = 0;
		}
		$memory_limit = (int)$memory_limit;
		return $memory_limit;
	}

	public function getImage($filename, &$extension) {
		if(file_exists($filename)) {
			$types = array('gif' => 1, 'jpg' => 2, 'jpeg' => 2, 'png' => 3, 'webp' => 18);
			$data = @getimagesize($filename);
			if(!empty($data) && @$types[$extension] != $data[2]) {
				$extension = array_search($data[2], $types);
			}
			$extension = strtolower(trim($extension));

			$res = false;
			switch($this->image_mode) {
				case 'Imagick':
					$res = $this->getImage_Imagick($filename, $extension);
					break;
				case 'GD':
				default:
					$res = $this->getImage_GD($filename, $extension);
					break;
			}

			if($res !== false)
				return $res;
		}

		hikashop_writeToLog('Image '. $filename . ' could not be opened when trying to generate its thumbnail');

		static $done = false;
		$app = JFactory::getApplication();
		if(hikashop_isClient('administrator') && !$done) {
			$done = true;
			$app->enqueueMessage('The '.$this->image_mode.' library for thumbnails creation is installed and activated on your website. However, it is not configured to support &quot;'.$extension.'&quot; images. Please make sure that you\'re using a valid image extension and contact your hosting company or system administrator in order to make sure that the GD library on your web server supports the image extension: '.$extension);
		}
	}

	protected function getImage_GD($filename, &$extension) {
		$ret = array();
		switch($extension) {
			case 'gif':
				if(!function_exists('imagecreatefromgif'))
					return false;
				$ret['res'] = imagecreatefromgif($filename);
				$ret['gd_tridx'] = imagecolortransparent($ret['res']);
				imagealphablending($ret['res'], false);
				imagesavealpha($ret['res'], true);
				break;
			case 'jpg':
			case 'jpeg':
				if(!function_exists('imagecreatefromjpeg'))
					return false;
				$ret['res'] = imagecreatefromjpeg($filename);
				break;
			case 'webp':
				case 'webp':
					if(!function_exists('imagecreatefromwebp'))
						return false;
					$ret['res'] = imagecreatefromwebp($filename);
					break;
			case 'png':
				if(!function_exists('imagecreatefrompng'))
					return false;
				$ret['res'] = imagecreatefrompng($filename);
				$ret['gd_tridx'] = imagecolortransparent($ret['res']);
				imagealphablending($ret['res'], false);
				imagesavealpha($ret['res'], true);
				break;
		}
		if(empty($ret))
			return false;

		if(function_exists('exif_read_data')) {
			$exif = @exif_read_data($filename);
		}

		if(empty($exif['Orientation']))
			$exif = array('Orientation' => 1);
		$ret['orientation'] = $exif['Orientation'];
		$ret['autorotate'] = ($ret['orientation'] != 1);

		$ret['ext'] = $extension;
		return $ret;
	}

	protected function getImage_Imagick($filename, &$extension) {
		$ret = array();

		$ret['res'] = new Imagick($filename);

		if(empty($ret['res']))
			return false;

		$ret['orientation'] = $ret['res']->getImageOrientation();
		$ret['autorotate'] = !empty($ret['orientation']) && ($ret['orientation'] != Imagick::ORIENTATION_TOPLEFT);

		$ret['ext'] = $extension;
		return $ret;
	}


	public function createThumbRes(&$source, $size, $options = array()) {
		if(isset($size['width']))
			$size = array('x' => (int)$size['width'], 'y' => (int)$size['height']);
		if(!isset($size['x']))
			$size = array('x' => (int)$size[0], 'y' => (int)$size[1]);

		$res = false;
		switch($this->image_mode) {
			case 'Imagick':
				$res = $this->createThumbRes_Imagick($source, $size, $options);
				break;
			case 'GD':
			default:
				$res = $this->createThumbRes_GD($source, $size, $options);
				break;
		}

		if($res !== false)
			return $res;
		return $res;
	}

	protected function createThumbRes_Imagick(&$source, $size, $options = array()) {

		$ret = clone $source;
		$ret['res']->setBackgroundColor(new ImagickPixel('transparent'));

		$origin_width = $ret['res']->getImageWidth();
		$origin_height = $ret['res']->getImageHeight();

		if($options['scale'] == 'outside' ) {
			if ($origin_width > $origin_height) {
				$resize_w = $origin_width * $new_h / $origin_height;
				$resize_h = $new_h;
			} else {
				$resize_w = $new_w;
				$resize_h = $origin_height * $new_w / $origin_width;
			}
			$ret['res']->cropImage($sx, $sy, ($resize_w - $new_w) / 2, ($resize_h - $new_h) / 2);
		} else {
			$sx = !empty($options['scaling'][0]) ? $options['scaling'][0] : $size['x'];
			$sy = !empty($options['scaling'][1]) ? $options['scaling'][1] : $size['y'];
			if(empty($options['forcesize'])) {
				if($origin_width < $sx)
					$sx = $origin_width;
				if($origin_height < $sy)
					$sy = $origin_height;
			}
			$ret['res']->thumbnailImage($sx, $sy, empty($options['forcesize']), false);
		}
		return $ret;
	}

	protected function createThumbRes_GD(&$source, $size, $options = array()) {
		$ret = array(
			'res' => imagecreatetruecolor($size['x'], $size['y'])
		);
		if($ret['res'] === false)
			return false;

		$ret['ext'] = $source['ext'];

		if(isset($source['gd_tridx'])) {
			$ret['gd_tridx'] = $source['gd_tridx'];

			$palletSize = imagecolorstotal($source['res']);
			if($source['ext'] == 'png') {
				imagealphablending($ret['res'], false);
				$color = imagecolorallocatealpha($ret['res'], 0, 0, 0, 127);
				imagefill($ret['res'], 0, 0, $color);
				imagesavealpha($ret['res'], true);
			} elseif($source['gd_tridx'] >= 0 && $source['gd_tridx'] < $palletSize) {
				$trnprt_color = imagecolorsforindex($source['res'], $source['gd_tridx']);
				$color = imagecolorallocate($ret['res'], $trnprt_color['red'], $trnprt_color['green'], $trnprt_color['blue']);
				imagecolortransparent($ret['res'], $color);
				imagefill($ret['res'], 0, 0, $color);
			}
		} else {
			$bgcolor = $this->GD_getBackgroundColor($ret['res'], @$options['background']);
			imagefill($ret['res'], 0, 0, $bgcolor);
		}

		if(function_exists('imageantialias')) {
			imageantialias($ret['res'], true);
		}

		$origin_width = imagesx($source['res']);
		$origin_height = imagesy($source['res']);
		$x = 0;
		$y = 0;
		$sx = !empty($options['scaling'][0]) ? $options['scaling'][0] : $size['x'];
		$sy = !empty($options['scaling'][1]) ? $options['scaling'][1] : $size['y'];
		if(!empty($options['forcesize'])) {
			$x = ($size['x'] - $options['scaling'][0]) / 2;
			$y = ($size['y'] - $options['scaling'][1]) / 2;
		} else {
			if($origin_width < $sx)
				$sx = $origin_width;
			if($origin_height < $sy)
				$sy = $origin_height;
		}

		if(function_exists('imagecopyresampled')) {
			imagecopyresampled($ret['res'], $source['res'], (int)$x, (int)$y, 0, 0, (int)$sx, (int)$sy, (int)$origin_width, (int)$origin_height);
		} else {
			imagecopyresized($ret['res'], $source['res'], (int)$x, (int)$y, 0, 0, (int)$sx, (int)$sy, (int)$origin_width, (int)$origin_height);
		}

		return $ret;
	}

	public function setResFilter(&$res, $filter, $options = null) {
		switch($this->image_mode) {
			case 'Imagick':
				return $this->setResFilter_Imagick($res, $filter, $options);
			case 'GD':
			default:
				return $this->setResFilter_GD($res, $filter, $options);
		}
		return false;
	}

	public function setResFilter_Imagick(&$res, $filter, $options) {
		switch($filter) {
			case 'grayscale':
				$res['res']->setImageColorspace(imagick::COLORSPACE_GRAY);
				break;
			case 'blur':
				$res['res']->blurImage(5, 3);
				break;
			case 'negate':
				$res['res']->negateImage(false);
				break;
			case 'brightness':
				$value = (int)$options;
				if(empty($value) || $value < -255 || $value > 255)
					return false;
				if($value > 100)
					$value = 100;
				$res['res']->brightnessContrastImage($value, 0);
				break;
		}
		return true;
	}

	public function setResFilter_GD(&$res, $filter, $options) {
		$imgFilterFunc = function_exists('imagefilter');
		switch($filter) {
			case 'grayscale':
				if(!$imgFilterFunc)
					return false;
				return imagefilter($res['res'], IMG_FILTER_GRAYSCALE);
			case 'blur':
				if(!$imgFilterFunc)
					return false;
				return imagefilter($res['res'], IMG_FILTER_GAUSSIAN_BLUR);
			case 'negate':
				if(!$imgFilterFunc)
					return false;
				return imagefilter($res['res'], IMG_FILTER_NEGATE);
			case 'brightness':
				if(!$imgFilterFunc)
					return false;
				$value = (int)$options;
				if(empty($value) || $value < -255 || $value > 255)
					return false;
				return imagefilter($res['res'], IMG_FILTER_BRIGHTNESS, $value);
		}
		return false;
	}

	public function setResCorners(&$res, $radius = 0, $options = array()) {
		if(empty($radius) || (int)$radius < 2)
			return false;
		switch($this->image_mode) {
			case 'Imagick':
				return $this->setResCorners_Imagick($res, $radius, $options);
			case 'GD':
			default:
				return $this->setResCorners_GD($res, $radius, $options);
		}
		return false;
	}

	protected function setResCorners_Imagick(&$res, $radius, $options) {
		if(!method_exists($res['res'], 'roundCorners'))
			return false;

		$h = sqrt($radius * $radius / 2);
		$res['res']->roundCorners($h, $h);
		return true;
	}

	protected function setResCorners_GD(&$res, $radius, $options) {
		$corner_image = imagecreatetruecolor($radius, $radius);
		imagealphablending($corner_image, false);
		imagesavealpha($corner_image, true);

		$bgcolor = $this->GD_getBackgroundColor($corner_image, @$options['background']);
		$color = imagecolorallocatealpha($corner_image, 0, 0, 0, 127);
		imagecolortransparent($corner_image, $color);
		imagefill($corner_image, 0, 0, $bgcolor);
		imagefilledellipse($corner_image, $radius, $radius, $radius * 2, $radius * 2, $color);

		$res_w = imagesx($res['res']);
		$res_y = imagesy($res['res']);

		imagecopymerge($res['res'], $corner_image, 0, 0, 0, 0, $radius, $radius, 100);
		$corner_image = imagerotate($corner_image, 90, 0);
		imagecopymerge($res['res'], $corner_image, 0, $res_y - $radius, 0, 0, $radius, $radius, 100);
		$corner_image = imagerotate($corner_image, 90, 0);
		imagecopymerge($res['res'], $corner_image, $res_w - $radius, $res_y - $radius, 0, 0, $radius, $radius, 100);
		$corner_image = imagerotate($corner_image, 90, 0);
		imagecopymerge($res['res'], $corner_image, $res_w - $radius, 0, 0, 0, $radius, $radius, 100);
	}

	public function setResQuality(&$res, $quality) {
		switch($this->image_mode) {
			case 'Imagick':
				return $this->setResQuality_Imagick($res, $quality);
			case 'GD':
			default:
				return $this->setResQuality_GD($res, $quality);
		}
		return false;
	}

	protected function setResQuality_Imagick(&$res, $quality) {
		$res['quality'] = $quality;
	}

	protected function setResQuality_GD(&$res, $quality) {
		$res['quality'] = $quality;
	}

	public function saveResImage(&$res, $filename) {
		$folder = dirname($filename);
		jimport('joomla.filesystem.folder');
		if(!JFolder::exists($folder))
			JFolder::create($folder);

		switch($this->image_mode) {
			case 'Imagick':
				return $this->saveResImage_Imagick($res, $filename);
			case 'GD':
			default:
				return $this->saveResImage_GD($res, $filename);
		}
		return false;
	}

	protected function saveResImage_Imagick(&$res, $filename) {
		$status = false;
		switch($res['ext']) {
			case 'gif':
				$res['res']->setImageFormat('gif');
				break;
			case 'jpg':
			case 'jpeg':
				if(empty($res['quality']))
					$res['quality'] = 95;
				$res['res']->setImageCompressionQuality($res['quality']);
				$res['res']->setImageFormat('jpeg');
				break;
			case 'webp':
				if(empty($res['quality']))
					$res['quality'] = 95;
				$res['res']->setImageCompressionQuality($res['quality']);
				$res['res']->setImageFormat('webp');
				break;
			case 'png':
				if(empty($res['quality']))
					$res['quality'] = 9;
				$res['res']->setImageCompressionQuality($res['quality']);
				$res['res']->setImageFormat('png');
				break;
		}
		return file_put_contents($filename, $res['res']);
	}

	protected function saveResImage_GD(&$res, $filename) {
		$status = false;
		switch($res['ext']) {
			case 'gif':
				$status = imagegif($res['res'], $filename);
				break;
			case 'jpg':
			case 'jpeg':
				if(empty($res['quality']))
					$res['quality'] = 95;
				$status = imagejpeg($res['res'], $filename, $res['quality']);
				break;
			case 'webp':
				if(empty($res['quality']))
					$res['quality'] = 95;
				$status = imagewebp($res['res'], $filename, $res['quality']);
				break;
			case 'png':
				if(empty($res['quality']))
					$res['quality'] = 9;
				$status = imagepng($res['res'], $filename, $res['quality']);
				break;
		}
		return $status;
	}

	public function getImageResContent(&$res) {
		switch($this->image_mode) {
			case 'Imagick':
				return $this->getImageResContent_Imagick($res);
			case 'GD':
			default:
				return $this->getImageResContent_GD($res);
		}
		return false;
	}

	protected function getImageResContent_Imagick(&$res) {
		switch($res['ext']) {
			case 'jpg':
			case 'jpeg':
			case 'webp':
				$res['res']->setImageCompressionQuality($res['quality']);
				break;
			case 'png':
				$res['res']->setImageCompressionQuality($res['quality']);
				break;
		}
		return $res['res']->getImageBlob();
	}

	protected function getImageResContent_GD(&$res) {
		ob_start();
		switch($res['ext']) {
			case 'gif':
				$status = imagegif($res['res']);
				break;
			case 'jpg':
			case 'jpeg':
				$status = imagejpeg($res['res'], null, $res['quality']);
				break;
			case 'webp':
				$status = imagewebp($res['res'], null, $res['quality']);
				break;
			case 'png':
				$status = imagepng($res['res'], null, $res['quality']);
				break;
		}
		if(!$status)
			return null;
		return ob_get_clean();
	}

	public function freeRes(&$res) {
		switch($this->image_mode) {
			case 'Imagick':
				$this->freeRes_Imagick($res);
				break;
			case 'GD':
			default:
				$this->freeRes_GD($res);
				break;
		}

		$res = false;
		unset($res);
	}

	protected function freeRes_Imagick(&$res) {
		if(!empty($res['res']))
			$res['res']->clear();
	}

	protected function freeRes_GD(&$res) {
		if(!empty($res['res']))
			@imagedestroy($res['res']);
	}

	public function orientateImage(&$res){
		switch($this->image_mode) {
			case 'Imagick':
				$this->orientateImage_Imagick($res);
				break;
			case 'GD':
			default:
				$this->orientateImage_GD($res);
				break;
		}
	}

	protected function orientateImage_GD(&$res){
		$rotate = 0;
		$flip = null;
		switch($res['orientation']) {
			case 2:
				$flip = IMG_FLIP_HORIZONTAL;
				break;
			case 3:
				$flip = IMG_FLIP_VERTICAL;
				break;
			case 4:
				$flip = IMG_FLIP_BOTH;
				break;
			case 5:
				$rotate = -90;
				$flip = IMG_FLIP_HORIZONTAL;
				break;
			case 6:
				$rotate = -90;
				break;
			case 7:
				$rotate = 90;
				$flip = IMG_FLIP_HORIZONTAL;
				break;
			case 8:
				$rotate = 90;
				break;
			case 1:
			default:
				break;
		}

		if($rotate != 0) {
			$old =& $res['res'];
			unset($res['res']);
			$res['res'] = imagerotate($old, $rotate, 0);
			imagedestroy($old);
		}
		if($flip !== null) {
			imageflip($res['res'], $flip);
		}
		 $res['orientation'] = 1;
	}

	protected function orientateImage_Imagick(&$res){
		switch ($res['orientation']) {
			case Imagick::ORIENTATION_TOPLEFT:
				break;
			case Imagick::ORIENTATION_TOPRIGHT:
				$res['res']->flopImage();
				break;
			case Imagick::ORIENTATION_BOTTOMRIGHT:
				$res['res']->rotateImage("#000", 180);
				break;
			case Imagick::ORIENTATION_BOTTOMLEFT:
				$res['res']->flopImage();
				$res['res']->rotateImage("#000", 180);
				break;
			case Imagick::ORIENTATION_LEFTTOP:
				$res['res']->flopImage();
				$res['res']->rotateImage("#000", -90);
				break;
			case Imagick::ORIENTATION_RIGHTTOP:
				$res['res']->rotateImage("#000", 90);
				break;
			case Imagick::ORIENTATION_RIGHTBOTTOM:
				$res['res']->flopImage();
				$res['res']->rotateImage("#000", 90);
				break;
			case Imagick::ORIENTATION_LEFTBOTTOM:
				$res['res']->rotateImage("#000", -90);
				break;
			default:
				break;
		}
		$res['res']->setImageOrientation(Imagick::ORIENTATION_TOPLEFT);
		$res['orientation'] = Imagick::ORIENTATION_TOPLEFT;
	}


	function getThumbnail($filename, $size = null, $options = array(), $relativePath = true, $cachePath = null) {
		$config =& hikashop_config();
		$scalemode = 'inside';

		$jconf = JFactory::getConfig();
		$jdebug = $jconf->get('debug');

		$ret = new stdClass();
		$ret->success = false;
		$ret->external = false;
		$ret->path = $filename;
		$ret->height = 0;
		$ret->width = 0;
		$ret->req_height = 0;
		$ret->req_width = 0;

		$fullFilename = $filename;
		if($relativePath === true)
			$fullFilename = $this->uploadFolder . $filename;
		if(is_string($relativePath))
			$fullFilename = $relativePath . $filename;

		$clean_filename = $fullFilename;
		try{
			$clean_filename = JPath::clean(realpath($fullFilename));
			if((JPATH_ROOT != '') && strpos($clean_filename, JPath::clean(JPATH_ROOT)) !== 0) {
				if(!defined('MULTISITES_MASTER_ROOT_PATH') || MULTISITES_MASTER_ROOT_PATH == '' || strpos($clean_filename, JPath::clean(MULTISITES_MASTER_ROOT_PATH)) !== 0)
					return $ret;
			}
		}catch(Exception $e) {
		}


		if(empty($size) || !is_array($size) || (!isset($size['x']) && !isset($size[0]) && !isset($size['width'])))
			$size = array('x' => (int)$config->get('thumbnail_x', 100), 'y' => (int)$config->get('thumbnail_y', 100));
		if(isset($size['width']))
			$size = array('x' => (int)$size['width'], 'y' => (int)$size['height']);
		if(!isset($size['x']))
			$size = array('x' => (int)$size[0], 'y' => (int)$size[1]);
		$ret->req_height = $size['y'];
		$ret->req_width = $size['x'];

		if(!empty($filename) && preg_match('#^https?://#i', $filename) === 1) {
			$ret->url = $ret->origin_url = $filename;
			$ret->filename = basename($filename);
			$urlArray = parse_url($filename);
			$url = ($urlArray['scheme'].'://'.$urlArray['host'].str_replace('%2F', '/', urlencode($urlArray['path'])));
			$url .= isset($urlArray['query']) ? '?'.$urlArray['query'] : '';
			list($ret->width, $ret->height) = @getimagesize($url);
			$ret->success = true;
			$ret->external = true;
			return $ret;
		}

		if($cachePath !== false && empty($cachePath))
			$cachePath = $this->uploadFolder;
		else if($cachePath !== false)
			$cachePath = rtrim(JPath::clean($cachePath), DS) . DS;

		if(!JFile::exists($fullFilename)) {
			if($jdebug && !empty($filename)) {
				$p = JProfiler::getInstance('Application');
				$dbgtrace = debug_backtrace();
				$dbgfile = str_replace('\\', '/', $dbgtrace[0]['file']);
				$dbgline = $dbgtrace[0]['line'];
				unset($dbgtrace);
				$p->mark('HikaShop image ['.$fullFilename.'] does not exists (from: '.substr($dbgfile, strrpos($dbgfile, '/')+1).':'.$dbgline.')');
			}

			if(!isset($options['default']))
				return $ret;

			$ret->path = $filename = $config->get('default_image');
			if($ret->path == 'barcode.png') {
				$fullFilename = HIKASHOP_MEDIA.'images'.DS . ltrim($ret->path, DS);
				$ret->url = rtrim(HIKASHOP_IMAGES, '/') . '/' . ltrim($ret->path, '/');
				$ret->origin_url = rtrim(HIKASHOP_IMAGES, '/') . '/' . ltrim($ret->path, '/');
				$ret->default_image = true;
				$ret->filename = $ret->path;
			} else {
				$fullFilename = $this->uploadFolder . $ret->path;
			}
			if(!JFile::exists($fullFilename)) {
				return $ret;
			}
			$clean_filename = JPath::clean(realpath($fullFilename));
			unset($ret->filename);
		}

		$optString = '';
		if(!empty($options['forcesize'])) $optString .= 'f';
		if(!empty($options['grayscale'])) $optString .= 'g';
		if(!empty($options['blur'])) $optString .= 'b';

		if(!empty($options['scale'])) {
			switch($options['scale']) {
				case 'outside':
					$scalemode = 'outside';
					$optString .= 'sO';
				case 'inside':
					break;
			}
		}

		if(!isset($options['background'])) {
			$options['background'] = $config->get('images_stripes_background', '');
		}

		if(!empty($options['background']) && is_string($options['background']) && strtolower($options['background']) != '#ffffff' && strtolower($options['background']) != 'none') {
			$optString .= 'c'.trim(strtoupper($options['background']), '#');
		}

		if(!empty($options['radius']) && (int)$options['radius'] > 2) $optString .= 'r'.(int)$options['radius'];

		$destFolder = 'thumbnails' . DS . $size['y'] . 'x' . $size['x'] . $optString;

		$ret->ext = $extension = $this->getFileExtension($filename); // strtolower(substr($filename, strrpos($filename, '.') + 1));
		if($ret->ext == 'jpg')
			$ret->ext = 'jpeg';

		$origin = new stdClass();
		if($extension == 'svg') {
			$scaling = false;
			$origin->width = $ret->req_width;
			$origin->height = $ret->req_height;
			$options['forcesize'] = false;
		} else {
			list($origin->width, $origin->height) = getimagesize($clean_filename);
			$ret->orig_height = $origin->height;
			$ret->orig_width = $origin->width;

			$scaling = $this->scaleImage($origin->width, $origin->height, $size['x'], $size['y'], $scalemode);
			if($scaling !== false) {
				$this->thumbnail_x = $scaling[0];
				$this->thumbnail_y = $scaling[1];
				if(empty($size['x']))
					$size['x'] = $scaling[0];
				if(empty($size['y']))
					$size['y'] = $scaling[1];
			} else {
				$this->thumbnail_x = $origin->width;
				$this->thumbnail_y = $origin->height;
			}


			if($cachePath !== false && JFile::exists($cachePath . $destFolder . DS . $filename)) {
				$ret->success = true;
				$ret->path = $destFolder . DS . $filename;
				$ret->filename = $filename;
				$ret->url = $this->uploadFolder_url . str_replace(array('\\/', '\\', '//') , '/', $ret->path);
				if(empty($ret->origin_url))
					$ret->origin_url = $this->uploadFolder_url . ltrim(str_replace(array('\\/', '\\', '//') , '/', $filename), '/');
				list($ret->width, $ret->height) = getimagesize($cachePath . $destFolder . DS . $filename);


				if($config->get('add_webp_images', 1) && function_exists('imagewebp')) {

					$status = true;
					$webpfile = preg_replace('#\.'. $extension.'$#i','.webp', $filename);
					if(!JFile::exists($cachePath . $destFolder . DS . $webpfile)) {
						$resThumb = $this->getImage($cachePath . $destFolder . DS . $filename, $extension);
						if(empty($options['webp_quality']))
							$options['webp_quality'] = $config->get('webp_image_quality', 80);
						if(in_array($resThumb['ext'], array('gif','png')))
							imagepalettetotruecolor($resThumb['res']);
						if(!empty($resThumb['res']))
							$status = imagewebp($resThumb['res'], $cachePath . $destFolder . DS . $webpfile, $options['webp_quality']);
						else
							$status = false;
					}

					if($status)
						$ret->webpurl = $this->uploadFolder_url . str_replace(array('\\/', '\\', '//') , '/', $destFolder . DS . $webpfile);

				}

				return $ret;
			}
		}


		if($scaling === false && empty($options['forcesize'])) {
			$ret->success = true;
			$ret->width = $origin->width;
			$ret->height = $origin->height;
			$ret->filename = $filename;
			$ret->data = file_get_contents($fullFilename);
			$ret->url = $this->uploadFolder_url . str_replace(array('\\/', '\\', '//') , '/', $ret->path);
			if(empty($ret->origin_url))
				$ret->origin_url = $this->uploadFolder_url . ltrim(str_replace(array('\\/', '\\', '//') , '/', $filename), '/');
			else
				$ret->url = $ret->origin_url;

			if($config->get('add_webp_images', 1) && function_exists('imagewebp') && empty($ret->default_image)) {
				$webpfile = preg_replace('#\.'. $extension.'$#i','.webp', $fullFilename);
				$status = true;
				if(!JFile::exists($webpfile)) {
					$resMain = $this->getImage($fullFilename, $extension);
					if(empty($options['webp_quality']))
							$options['webp_quality'] = $config->get('webp_image_quality', 80);
					if(in_array($resMain['ext'], array('gif','png')))
						imagepalettetotruecolor($resMain['res']);
					if(!empty($resMain['res']))
						$status = imagewebp($resMain['res'], $webpfile, $options['webp_quality']);
					else
						$status = false;
				}

				if($status) {
					$webpfile = preg_replace('#\.'. $extension.'$#i','.webp', $filename);
					$ret->webpurl = $this->uploadFolder_url . str_replace(array('\\/', '\\', '//') , '/', $webpfile);
				}
			}
			return $ret;
		}
		unset($ret->url);
		if($scaling === false) {
			$scaling = array($origin->width, $origin->height);
		}

		$quality = array(
			'jpg' => 95,
			'webp' => 95,
			'png' => 9
		);
		if(!empty($options['quality'])) {
			if(is_array($options['quality'])) {
				if(!empty($options['quality']['jpg']))
					$quality['jpg'] = (int)$options['quality']['jpg'];
				if(!empty($options['quality']['png']))
					$quality['png'] = (int)$options['quality']['png'];
				if(!empty($options['quality']['webp']))
					$quality['webp'] = (int)$options['quality']['webp'];
			} elseif((int)$options['quality'] > 0) {
				$quality['jpg'] = (int)$options['quality'];
			}
		}

		if($config->get('image_check_memory', 1)) {
			$memory_limit = $this->getMemoryLimit();
			if($memory_limit > 0) {
				$rest = $memory_limit - memory_get_usage();

				$e_x = empty($options['forcesize']) ? $scaling[0] : $size['x'];
				$e_y = empty($options['forcesize']) ? $scaling[1] : $size['y'];
				$estimation = (($origin->width * $origin->height) + ($e_x * $e_y)) * 8;

				if($estimation > $rest) {
					$ret->success = false;
					$app = JFactory::getApplication();
					$app->enqueueMessage(JText::sprintf('WARNING_IMAGE_TOO_BIG_FOR_MEMORY', $filename));
					return $ret;
				}
			}
		}

		$resMain = $this->getImage($fullFilename, $extension);
		if(!$resMain)
			return $ret;

		$options['scaling'] = $scaling;
		$thumbSize = array(
			'x' => empty($options['forcesize']) || empty($size['x']) ? $scaling[0] : $size['x'],
			'y' => empty($options['forcesize']) || empty($size['y']) ? $scaling[1] : $size['y'],
		);
		$resThumb = $this->createThumbRes($resMain, $thumbSize, $options);

		switch($extension) {
			case 'png':
				$this->setResQuality($resThumb, $quality['png']);
				break;
			case 'jpg':
			case 'jpeg':
			case 'webp':
				$this->setResQuality($resThumb, $quality['jpg']);
				break;
		}

		$this->freeRes($resMain);

		if(!empty($options['radius']) && (int)$options['radius'] > 2)
			$this->setResCorners($resThumb, (int)$options['radius']);

		if(!empty($options['grayscale']))
			$this->setResFilter($resThumb, 'grayscale', null);
		if(!empty($options['blur']))
			$this->setResFilter($resThumb, 'blur', $options['blur']);

		$imageContent = $this->getImageResContent($resThumb);
		$status = ($imageContent !== false && $imageContent !== null);



		if($cachePath === false) {
			$this->freeRes($resThumb);
			$ret->success = $status;
			$ret->data = $imageContent;
			return $ret;
		}

		$ret->success = $status && JFile::write($cachePath . $destFolder . DS . $filename, $imageContent);
		if($ret->success) {
			list($ret->width, $ret->height) = getimagesize($cachePath . $destFolder . DS . $filename);
			$ret->path = $destFolder . DS . $filename;
			$ret->filename = $filename;
			$ret->url = $this->uploadFolder_url . str_replace(array('\\/', '\\', '//') , '/', $ret->path);
			if(empty($ret->origin_url))
				$ret->origin_url = $this->uploadFolder_url . ltrim(str_replace(array('\\/', '\\', '//') , '/', $filename), '/');

			if($config->get('add_webp_images', 1) && function_exists('imagewebp')) {
				if(empty($options['webp_quality']))
					$options['webp_quality'] = $config->get('webp_image_quality', 80);
				$webpfile = preg_replace('#\.'. $resThumb['ext'].'$#i','.webp', $filename);

				if(in_array($resThumb['ext'], array('gif','png')))
					imagepalettetotruecolor($resThumb['res']);
				if(!empty($resThumb['res']))
					$status = imagewebp($resThumb['res'], $cachePath . $destFolder . DS . $webpfile, $options['webp_quality']);
				else
					$status = false;
				if($status)
					$ret->webpurl = $this->uploadFolder_url . str_replace(array('\\/', '\\', '//') , '/', $destFolder . DS . $webpfile);
			}
		} else  {
			static $image_generation_warning = null;
			if($image_generation_warning === null) {
				$app = JFactory::getApplication();
				$app->enqueueMessage(JText::sprintf('WRITABLE_FOLDER', $cachePath . $destFolder), 'error');
				$image_generation_warning = true;
			}
		}

		$this->freeRes($resThumb);

		return $ret;
	}


	public function display($path, $addpopup = true, $title = '', $options = '', $optionslink = '', $width = 0, $height = 0, $alt = '') {
		$config =& hikashop_config();
		$this->thumbnail = (int)$config->get('thumbnail', 1);

		jimport('joomla.filesystem.file');

		$this->uploadFolder_url = $this->main_uploadFolder_url;
		$this->uploadFolder = $this->main_uploadFolder;

		$html = '';
		if(!JFile::exists($this->uploadFolder . $path)) {
			$path = $config->get('default_image');
			if($path == 'barcode.png') {
				$this->uploadFolder_url = HIKASHOP_IMAGES;
				$this->uploadFolder = HIKASHOP_MEDIA.'images'.DS;
			}
			if(!JFile::exists($this->uploadFolder. $path)) {
				$this->uploadFolder_url = $this->main_uploadFolder_url;
				$this->uploadFolder = $this->main_uploadFolder;
				return $html;
			}
		}

		if(empty($alt)) {
			$alt = $title;
		} else {
			$title = $alt;
		}

		$extension = $this->getFileExtension($path);
		if($extension == 'svg') {
			$this->width = max((int)$width, 0);
			$this->height = max((int)$height, 0);
			$this->thumbnail = false;
			$options .= ' height="' . $this->height . '" width="' . $this->width . '" ';
		} else {
			list($this->width, $this->height) = getimagesize($this->uploadFolder . $path);
		}

		$module = false;
		if($width != 0 && $height != 0) {
			$module = array(
				0 => $height,
				1 => $width
			);
			$this->main_thumbnail_x = $width;
			$this->main_thumbnail_y = $height;
		}
		$html = $this->displayThumbnail($path, $title, is_string($addpopup), $options, $module, $alt);

		if($addpopup) {
			$popup_x = (int)$config->get('max_x_popup',760);
			$popup_y = (int)$config->get('max_y_popup',480);
			$this->width += 20;
			$this->height += 30;
			if($this->width > $popup_x)
				$this->width = $popup_x;
			if($this->height > $popup_y)
				$this->height = $popup_y;
			if(is_string($addpopup)) {
				static $first=true;
				if($first) {
					if($this->override && function_exists('hikashop_image_toggle_js')) {
						$js = hikashop_image_toggle_js($this);
					} else {
						$js = '
function hikashopChangeImage(id,url,x,y,obj,nTitle,nAlt){
	if(nAlt === undefined) nAlt = \'\';
	image=document.getElementById(id);
	if(image){
		image.src=url;
		if(x) image.width=x;
		if(y) image.height=y;
		if(nAlt) image.alt=nAlt;
		if(nTitle) image.title=nTitle;
	}
	image_link = document.getElementById(id+\'_link\');
	if(image_link){
		image_link.href=obj.href;
		image_link.rel=obj.rel;
		if(nAlt) image_link.title=nAlt;
		if(nTitle) image_link.title=nTitle;
	}

	var myEls = getElementsByClass(\'hikashop_child_image\');
	for ( i=0;i<myEls.length;i++ ) {
		myEls[i].style.border=\'0px\';
	}

	obj.childNodes[0].style.border=\'1px solid\';
	return false;
}

function getElementsByClass(searchClass,node,tag) {
	var classElements = new Array();
	if ( node == null )
		node = document;
	if ( tag == null )
		tag = \'*\';
	var els = node.getElementsByTagName(tag);
	var elsLen = els.length;
	var pattern = new RegExp("(^|\\s)"+searchClass+"(\\s|$)");
	for (i = 0, j = 0; i < elsLen; i++) {
		if ( pattern.test(els[i].className) ) {
			classElements[j] = els[i];
			j++;
		}
	}
	return classElements;
}

window.hikashop.ready( function() {
	image_link = document.getElementById(\'hikashop_image_small_link_first\');
	if(image_link){
		image_link.childNodes[0].style.border=\'1px solid\';
	}
});
';
					}
					$doc = JFactory::getDocument();
					$doc->addScriptDeclaration("\n<!--\n".$js."\n//-->\n");
					$first = false;
					$optionslink.=' id="hikashop_image_small_link_first" ';
					JHTML::_('behavior.modal');
				}
				if(!empty($this->no_size_override)) {
					$this->thumbnail_x = '';
					$this->thumbnail_y = '';
					$this->uploadFolder_url_thumb = $this->uploadFolder_url . $path;
				}
				if($this->override && function_exists('hikashop_small_image_link_render')) {
					$html = hikashop_small_image_link_render($this,$path,$addpopup,$optionslink,$html,$title,$alt);
				} else {
					$html = '<a title="'.$title.'" alt="'.$alt.'" class="hikashop_image_small_link" rel="{handler: \'image\'}" href="'.$this->uploadFolder_url.$path.'" onclick="SqueezeBox.fromElement(this,{parse: \'rel\'});return false;" target="_blank" onmouseover="return hikashopChangeImage(\''.$addpopup.'\',\''.$this->uploadFolder_url_thumb.'\',\''.$this->thumbnail_x.'\',\''.$this->thumbnail_y.'\',this,\''.$title.'\',\''.$alt.'\');" '.$optionslink.'>'.$html.'</a>';
				}
			} else {
				JHTML::_('behavior.modal');

				if($this->override && function_exists('hikashop_image_link_render')) {
					$html = hikashop_image_link_render($this,$path,$addpopup,$optionslink,$html,$title,$alt);
				} else {
					$html = '<a title="'.$title.'" alt="'.$alt.'" rel="{handler: \'image\'}" target="_blank" href="'.$this->uploadFolder_url.$path.'" onclick="SqueezeBox.fromElement(this,{parse: \'rel\'});return false;" '.$optionslink.'>'.$html.'</a>';
				}
			}
		}
		$this->uploadFolder_url = $this->main_uploadFolder_url;
		$this->uploadFolder = $this->main_uploadFolder;
		return $html;
	}

	function displayThumbnail($path, $title = '', $reduceSize = false, $options = '', $module = false, $alt = '') {
		if((empty($this->main_thumbnail_x) && !empty($this->main_thumbnail_y)) || (empty($this->main_thumbnail_y) && !empty($this->main_thumbnail_x))) {
			$module[0] = $this->main_thumbnail_y;
			$module[1] = $this->main_thumbnail_x;
		}
		$new = $this->scaleImage($this->width, $this->height, $this->main_thumbnail_x, $this->main_thumbnail_y);

		if($new !== false) {
			$this->thumbnail_x = $new[0];
			$this->thumbnail_y = $new[1];
		}else{
			$this->thumbnail_x = $this->width;
			$this->thumbnail_y = $this->height;
		}

		if($module) {
			if(empty($this->main_thumbnail_y)) $this->main_thumbnail_y = 0;
			if(empty($this->main_thumbnail_x)) $this->main_thumbnail_x = 0;
			$folder = 'thumbnail_'.$this->main_thumbnail_y.'x'.$this->main_thumbnail_x;
		} else {
			$folder = 'thumbnail_'.$this->thumbnail_y.'x'.$this->thumbnail_x;
		}

		if(!$reduceSize && !$module) {
			$options .= ' height="'.$this->thumbnail_y.'" width="'.$this->thumbnail_x.'" ';
		}

		if($this->thumbnail){
			jimport('joomla.filesystem.file');
			$ok = true;
			JPath::check($this->uploadFolder.$folder.DS.$path);
			if(!JFile::exists($this->uploadFolder.$folder.DS.$path)){
				if($module){
					$ok = $this->generateThumbnail($path, $module);
				}
				else{
					$ok = $this->generateThumbnail($path);
				}
			}

			if($ok){
				if(is_array($ok)){
					$folder='thumbnail_'.$ok[0].'x'.$ok[1];
				}
				$this->uploadFolder_url_thumb=$this->uploadFolder_url.$folder.'/'.$path;
				return '<img src="'.$this->uploadFolder_url_thumb.'" alt="'.htmlentities($alt).'" title="'.htmlentities($title).'" '.$options.' />';
			}
		}
		$this->uploadFolder_url_thumb=$this->uploadFolder_url.$path;

		return '<img src="'.$this->uploadFolder_url_thumb.'" alt="'.htmlentities($alt).'" title="'.htmlentities($title).'" '.$options.' />';
	}

	function generateThumbnail($file_path, $module = false){
		$ok = true;
		if(!$this->thumbnail)
			return $ok;

		$ok = false;
		if(!$this->image_mode)
			return $ok;

		$config =& hikashop_config();
		list($this->width, $this->height) = getimagesize($this->uploadFolder.$file_path);

		if($module) {
			$thumbnail_x=$module[1];
			$thumbnail_y=$module[0];
		} else {
			$thumbnail_x = $config->get('thumbnail_x', 100);
			$thumbnail_y = $config->get('thumbnail_y', 100);
		}

		if(!$thumbnail_x && !$thumbnail_y) {
			return true;
		}

		$new = $this->scaleImage($this->width, $this->height, $thumbnail_x, $thumbnail_y);
		if($new !== false) {
			if(empty($thumbnail_y))
				$thumbnail_y = 0;
			if(empty($thumbnail_x))
				$thumbnail_x = 0;

			$ok = $this->_resizeImage($file_path, $new[0], $new[1], $this->uploadFolder.'thumbnail_'.$thumbnail_y.'x'.$thumbnail_x.DS);
			if($ok & !$module){
				$ok = array($new[1], $new[0]);
			}
		}
		return $ok;
	}

	public function autoRotate($file_path) {
		$image = $this->uploadFolder.$file_path;
		$extension = $this->getFileExtension($image);
		$resMain = $this->getImage($image, $extension);
		if(!empty($resMain) && $resMain['autorotate']) {
			$this->orientateImage($resMain);
			$this->saveResImage($resMain, $image);
		}
		$this->freeRes($resMain);
	}

	function resizeImage($file_path, $type = 'image', $options = array()) {

		$config =& hikashop_config();
		$image_x = $config->get('image_x',0);
		$image_y = $config->get('image_y',0);
		$watermark_name = $config->get('watermark','');

		if(!empty($options['image_x']) || !empty($options['image_y'])) {
			$image_x = $options['image_x'];
			$image_y = $options['image_y'];
		}
		if(isset($options['watermark'])) {
			$watermark_name = $options['watermark'];
		}

		$ok = true;
		if(($image_x || $image_y) || !empty($watermark_name)){
			$new = getimagesize($this->uploadFolder . $file_path);
			$this->width=$new[0];
			$this->height=$new[1];

			if(!$image_x && !$image_y && empty($watermark_name)){
				return true;
			}
			if($image_x || $image_y){
				$new = $this->scaleImage($this->width, $this->height,$image_x,$image_y);
				if($new === false) {
					$new = array($this->width, $this->height);
				}
			}

			$ok = $this->_resizeImage($file_path, $new[0], $new[1], $this->uploadFolder, $type, $watermark_name);
		}
		return $ok;
	}

	function _resizeImage($file_path, $newWidth, $newHeight, $dstFolder = '', $type = 'thumbnail', $watermark = '') {
		$image = $this->uploadFolder.$file_path;

		if(empty($dstFolder))
			$dstFolder = $this->uploadFolder.'thumbnail_'.$this->thumbnail_y.'x'.$this->thumbnail_x.DS;
		$watermark_path = '';

		if(hikashop_level(2) && $type == 'image') {
			$config =& hikashop_config();
			$watermark_name = $watermark;
			if(empty($watermark_name) && $watermark_name !== false)
				$watermark_name = $config->get('watermark', '');

			if(!empty($watermark_name)) {
				$watermark_path = $this->main_uploadFolder.$watermark_name;

				if(!$this->_checkImage($watermark_path)) {
					$watermark_path = '';
				} else {
					$wm_extension = strtolower(substr($watermark_path,strrpos($watermark_path,'.')+1));
					$watermark = $this->getImage($watermark_path,$wm_extension);
					if(!$watermark) {
						$watermark_path = '';
					}
				}
			}
		}

		$extension = strtolower(substr($file_path,strrpos($file_path,'.')+1));

		$img = $this->getImage($image,$extension);
		if(!$img) return false;

		if($newWidth!=$this->width || $newHeight!=$this->height) {
			$thumb = $this->createThumbRes($img, array('width' => $newWidth, 'height' => $newHeight), array());
		} else {
			$thumb =& $img;
		}

		if(!empty($watermark_path)){
			list($wm_width,$wm_height) = getimagesize($watermark_path);
			$padding = 3;
			$dest_x = $newWidth - $wm_width - $padding;
			if($dest_x < 0) $dest_x = 0;
			$dest_y = $newHeight - $wm_height - $padding;
			if($dest_y < 0) $dest_y = 0;
			$this->addWaterMark($thumb, $watermark, $dest_x, $dest_y, $wm_width, $wm_height);
			$this->freeRes($watermark);
		}

		$dest = $dstFolder.$file_path;
		$status = $this->saveResImage($thumb, $dest);

		$this->freeRes($img);
		$this->freeRes($thumb);
		return $status;
	}

	public function addWaterMark($thumb, $watermark, $dest_x, $dest_y, $wm_width, $wm_height, $opacity=null){
		if(is_null($opacity)) {
			$config = hikashop_config();
			$opacity = (int)$config->get('opacity',0);
		}

		switch($this->image_mode) {
			case 'Imagick':
				$this->addWaterMark_Imagick($thumb, $watermark, $dest_x, $dest_y, $wm_width, $wm_height, $opacity);
				break;
			case 'GD':
			default:
				$this->addWaterMark_GD($thumb, $watermark, $dest_x, $dest_y, $wm_width, $wm_height, $opacity);
				break;
		}
	}

	protected function addWaterMark_Imagick($thumb, $watermark, $dest_x, $dest_y, $wm_width, $wm_height, $opacity) {
		$thumb['res']->compositeImage($watermark['res'], Imagick::COMPOSITE_OVER, $dest_x, $dest_y);
	}

	protected function addWaterMark_GD($thumb, $watermark, $dest_x, $dest_y, $wm_width, $wm_height, $opacity) {
		$trnprt_color=null;
		if(in_array($watermark['ext'], array('gif','png'))){
			if ($watermark['gd_tridx'] >= 0) {
				$trnprt_color = imagecolorsforindex($watermark['res'], $watermark['gd_tridx']);
			}
		}
		$this->GD_imagecopymerge_alpha($thumb['res'], $watermark['res'], $dest_x, $dest_y, 0, 0, $wm_width, $wm_height, $opacity, $trnprt_color);
	}


	protected function GD_getBackgroundColor($resource, $color) {
		$bgcolor = false;
		if(!empty($color)) {
			if(is_array($color)) {
				$bgcolor = imagecolorallocatealpha($resource, $color[0], $color[1], $color[2], 0);
				if($bgcolor === false || $bgcolor === -1)
					$bgcolor = imagecolorallocate($resource, $color[0], $color[1], $color[2]);
			} elseif( is_string($color) && strtolower($color) != 'none' && strlen(ltrim($color, '#')) == 6) {
				$rgb = str_split(ltrim($color, '#'), 2);
				$bgcolor = imagecolorallocatealpha($resource, hexdec($rgb[0]), hexdec($rgb[1]), hexdec($rgb[2]), 0);
				if($bgcolor === false || $bgcolor === -1)
					$bgcolor = imagecolorallocate($resource, hexdec($rgb[0]), hexdec($rgb[1]), hexdec($rgb[2]));
			}
		} else {
			$bgcolor = imagecolorallocatealpha($resource, 255, 255, 255, 127);
		}
		if($bgcolor === false) {
			$bgcolor = imagecolorallocatealpha($resource, 255, 255, 255, 0);
			if($bgcolor === false || $bgcolor === -1)
				$bgcolor = imagecolorallocate($resource, 255, 255, 255);
		}
		return $bgcolor;
	}

	protected function GD_imagecopymerge_alpha($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct, $trans = NULL) {
		$dst_w = imagesx($dst_im);
		$dst_h = imagesy($dst_im);

		$src_x = max($src_x, 0);
		$src_y = max($src_y, 0);
		$dst_x = max($dst_x, 0);
		$dst_y = max($dst_y, 0);
		if ($dst_x + $src_w > $dst_w)
			$src_w = $dst_w - $dst_x;
		if ($dst_y + $src_h > $dst_h)
			$src_h = $dst_h - $dst_y;

		for($x_offset = 0; $x_offset < $src_w; $x_offset++) {
			for($y_offset = 0; $y_offset < $src_h; $y_offset++) {
				$srccolor = imagecolorsforindex($src_im, imagecolorat($src_im, $src_x + $x_offset, $src_y + $y_offset));
				$dstcolor = imagecolorsforindex($dst_im, imagecolorat($dst_im, $dst_x + $x_offset, $dst_y + $y_offset));

				if (is_null($trans) || ($srccolor !== $trans)) {
					$src_a = $srccolor['alpha'] * $pct / 100;
					$src_a = 127 - $src_a;
					$dst_a = 127 - $dstcolor['alpha'];
					$dst_r = ($srccolor['red'] * $src_a + $dstcolor['red'] * $dst_a * (127 - $src_a) / 127) / 127;
					$dst_g = ($srccolor['green'] * $src_a + $dstcolor['green'] * $dst_a * (127 - $src_a) / 127) / 127;
					$dst_b = ($srccolor['blue'] * $src_a + $dstcolor['blue'] * $dst_a * (127 - $src_a) / 127) / 127;
					$dst_a = 127 - ($src_a + $dst_a * (127 - $src_a) / 127);
					$color = imagecolorallocatealpha($dst_im, $dst_r, $dst_g, $dst_b, $dst_a);
					if (!imagesetpixel($dst_im, $dst_x + $x_offset, $dst_y + $y_offset, $color))
						return false;
					imagecolordeallocate($dst_im, $color);
				}
			}
		}
		return true;
	}
}

if(!function_exists('imageflip')) {
	define("IMG_FLIP_HORIZONTAL", 1);
	define("IMG_FLIP_VERTICAL", 2);
	define("IMG_FLIP_BOTH", 3);

	function imageflip($resource, $mode) {
		if($mode == IMG_FLIP_VERTICAL || $mode == IMG_FLIP_BOTH)
			$resource = imagerotate($resource, 180, 0);
		if($mode == IMG_FLIP_HORIZONTAL || $mode == IMG_FLIP_BOTH)
			$resource = imagerotate($resource, 90, 0);
		return $resource;
	}
}

