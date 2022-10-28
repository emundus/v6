<?php
/**
 * @package    HikaMarket for Joomla!
 * @version    4.1.0
 * @author     Obsidev S.A.R.L.
 * @copyright  (C) 2011-2022 OBSIDEV. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
$upload = hikamarket::acl('category/edit/images/upload');
$ajax = false;
if(!empty($this->upload_ajax))
	$ajax = true;
$options = array(
	'classes' => array(
		'mainDiv' => 'hikamarket_main_image_div',
		'contentClass' => 'hikamarket_category_image',
		'btn_add' => 'hikam_add_btn',
		'btn_upload' => 'hikam_upload_btn'
	),
	'upload' => $upload,
	'upload_base_url' => 'index.php?option=com_hikamarket&ctrl=upload',
	'gallery' => $upload,
	'text' => ($upload ? JText::_('HIKAM_CATEGORY_IMAGE_EMPTY_UPLOAD') : JText::_('HIKAM_CATEGORY_IMAGE_EMPTY')),
	'uploader' => array('category', 'category_image'),
	'vars' => array(
		'category_id' => $this->category->category_id,
		'file_type' => 'category'
	),
	'ajax' => $ajax
);

$content = '';
if(!empty($this->category->file_id) && !empty($this->category->file_path)) {
	$this->params = new stdClass();
	$this->params->file_id = $this->category->file_id;
	$this->params->file_path = $this->category->file_path;
	$this->params->file_ref_id = $this->category->file_ref_id;
	$content = $this->loadTemplate('image_entry');
}

echo $this->uploaderType->displayImageSingle('hikamarket_category_image', $content, $options);
