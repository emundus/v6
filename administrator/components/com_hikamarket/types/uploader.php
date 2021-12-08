<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.0.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2021 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class HikaMarketUploaderType {

	protected static $init = false;

	public function __construct() {
		$this->popup = hikamarket::get('shop.helper.popup');
	}

	protected function initialize() {
		if(self::$init === true)
			return;

		hikamarket::loadJslib('jquery');
		hikamarket::loadJslib('jquery-upload');
		hikamarket::loadJslib('hkuploader');

		self::$init = true;
	}

	private function processOptions(&$options) {
		$t = hikamarket::getFormToken();

		if(!empty($options['uploader'])) {
			$params = '';
			if(!empty($options['vars'])) {
				$options['formData'] = $options['vars'];
				$options['formData'][$t] = 1;
				foreach($options['vars'] as $k => $v) {
					$params .= '&' . urlencode($k) . '=' . urlencode($v);
				}
			}

			if(!empty($options['upload'])) {
				$options['uploadUrls'] = array(
					0 => hikamarket::completeLink('upload&task=image&uploader='.$options['uploader'][0].'&field='.$options['uploader'][1].$params.'&'.$t.'=1',true),
					1 => hikamarket::completeLink('upload&task=upload&upload=image&uploader='.$options['uploader'][0].'&field='.$options['uploader'][1], true, false, true)
				);
			}

			if(!empty($options['gallery'])) {
				$options['browseUrl'] = hikamarket::completeLink('upload&task=galleryimage&uploader='.$options['uploader'][0].'&field='.$options['uploader'][1].$params,true);
			}
		}
		if(empty($options['classes'])) {
			$options['classes'] = array(
				'mainDiv' => 'hikamarket_main_image_div'
			);
		}
		if(!isset($options['classes']['mainDiv']))
			$options['classes']['mainDiv'] = '';
		if(!isset($options['classes']['firstImg']))
			$options['classes']['firstImg'] = 'hikamarket_upload_imagethumb_main';
		if(!isset($options['classes']['otherImg']))
			$options['classes']['otherImg'] = 'hikamarket_upload_imagethumb_small';
		if(!isset($options['classes']['contentClass']))
			$options['classes']['contentClass'] = '';

		if(empty($options['uploadUrls']))
			$options['uploadUrls'] = null;
		if(empty($options['browseUrl']))
			$options['browseUrl'] = null;
		if(empty($options['text']))
			$options['text'] = '';
		if(!empty($options['formData'])) {
			if(!is_string($options['formData']))
				$options['formData'] = json_encode($options['formData']);
		} else {
			$options['formData'] = '{\''.$t.'\':1}';
		}
	}

	public function displayImageSingle($id, $content = '', $options = array()) {
		$this->initialize();
		$this->processOptions($options);

		$ret = '
<div id="'.$id.'_main" class="hikamarket_dropzone">
	<div class="'.$options['classes']['mainDiv'].'">
		<div class="hikamarket_uploader_image_add '.$id.'_add">';

		if(!empty($options['uploadUrls'])) {
			$ret .= $this->popup->display(
				'<span class="hikam_upload_btn"></span>',
				'MARKET_UPLOAD_IMAGE',
				$options['uploadUrls'][0],
				$id.'_uploadpopup',
				750, 460, ' onclick="return window.hkUploaderList[\''.$id.'\'].uploadFile(this);"', '', 'link'
			);
		}

		if(!empty($options['browseUrl'])) {
			$ret .= $this->popup->display(
				'<span class="hikam_add_btn"></span>',
				'MARKET_ADD_IMAGE',
				$options['browseUrl'],
				$id.'_addpopup',
				750, 460, ' onclick="return window.hkUploaderList[\''.$id.'\'].browseImage(this);"', '', 'link'
			);
		}

		$ret .= '
		</div>
		<div id="'.$id.'_content" class="hikamarket_uploader_singleimage_content">' . $content . '</div>
		<div id="'.$id.'_empty" class="hikamarket_uploader_image_empty" style="'.(!empty($content) ? 'display:none;' : '') . '">
			<span>'.$options['text'].'</span>
		</div>
	</div>
</div>';
		if(!empty($options['uploadUrls'])) {
			$ret .= '
<input id="'.$id.'" type="file"/>
<div id="'.$id.'_list"></div>
';
		}

		$js = '';
		if(!empty($options['uploadUrls'])) {
			$js .= "\r\n" . 'var hkUploader_'.$id.' = new hkUploaderMgr("'.$id.'", {mode: \'single\', url:\''.$options['uploadUrls'][1].'\',formData:'.$options['formData'].'})';
		}

		$doc = JFactory::getDocument();
		if(!empty($js))
			$doc->addScriptDeclaration("\r\n".'hkjQuery(document).ready(function($){'.$js."\r\n".'});');

		return $ret;
	}

	public function displayImageMultiple($id, $content = array(), $options = array()) {
		$this->initialize();
		$this->processOptions($options);

		$ret = '
<div id="'.$id.'_main" class="hikamarket_dropzone">
	<div class="'.$options['classes']['mainDiv'].'">
		<div class="hikamarket_uploader_image_add '.$id.'_add">';

		if(!empty($options['uploadUrls'])) {
			$ret .= $this->popup->display(
				'<span class="hikam_upload_btn"></span>',
				'MARKET_UPLOAD_IMAGE',
				$options['uploadUrls'][0],
				$id.'_uploadpopup',
				750, 460, ' onclick="return window.hkUploaderList[\''.$id.'\'].uploadFile(this);"', '', 'link'
			);
		}

		if(!empty($options['browseUrl'])) {
			$ret .= $this->popup->display(
				'<span class="hikam_add_btn"></span>',
				'MARKET_ADD_IMAGE',
				$options['browseUrl'],
				$id.'_addpopup',
				750, 460, ' onclick="return window.hkUploaderList[\''.$id.'\'].browseImage(this);"', '', 'link'
			);
		}

		$contentHtml = '';
		if(!empty($content)) {
			if(is_string($content)) {
				$contentHtml = $content;
			} else {
				foreach($content as $k => $c) {
					$liClass = ($k == 0) ? $options['classes']['firstImg'] : $options['classes']['otherImg'];
					$contentHtml .= '<li class="'.$liClass.'">'.$c.'</li>';
				}
			}
		}

		$ret .= '
		</div>
		<ul id="'.$id.'_content" class="hikamarket_uploader_multiimage_content hkContent '.$options['classes']['contentClass'].'">' . $contentHtml . '</ul>
		<div id="'.$id.'_empty" class="hikamarket_uploader_image_empty" style="'.(!empty($content) ? 'display:none;' : '') . '">
			<span>'.$options['text'].'</span>
		</div>
	</div>
</div>';
		if(!empty($options['uploadUrls'])) {
			$ret .= '
<input id="'.$id.'" type="file" multiple/>
<div id="'.$id.'_list"></div>
';
		}

		$js = '';
		if(!empty($options['uploadUrls'])) {
			$js .= "\r\n" . 'var hkUploader_'.$id.' = new hkUploaderMgr("'.$id.'", {mode: \'listImg\', url:\''.$options['uploadUrls'][1].'\', formData:'.$options['formData'].', options: { imgClasses:[\''.$options['classes']['firstImg'].'\',\''.$options['classes']['otherImg'].'\']} })';
		}

		$doc = JFactory::getDocument();
		if(!empty($js))
			$doc->addScriptDeclaration("\r\n".'hkjQuery(document).ready(function($){'.$js."\r\n".'});');

		return $ret;
	}
}
