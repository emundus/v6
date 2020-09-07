<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.3.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2020 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
ob_start();

$title_key = 'show_page_heading';

$titleType = 'h1';
if($this->module) {
	$title_key = 'showtitle';
	$titleType = 'h2';
}

$title = $this->params->get($title_key);
if(empty($title) && $title_key == 'show_page_heading') {
	$params = JComponentHelper::getParams('com_menus');
	$title = $params->get($title_key);
}

if(!empty($title) && hikaInput::get()->getVar('hikashop_front_end_main', 0)){
	if($this->module) {
		$heading = $this->params->get('title');
	} else {
		$heading = $this->params->get('page_title');
		if($this->params->get('page_heading')){
			$heading = $this->params->get('page_heading');
		}
	}
	?>
	<<?php echo $titleType; ?>>
	<?php echo $heading; ?>
	</<?php echo $titleType; ?>>
	<?php
}
if(!$this->module){
	$val = hikaInput::get()->getVar('hikashop_front_end_main',0);
	hikaInput::get()->set('hikashop_front_end_main',0);

	if(isset($this->element->category_canonical) && !empty($this->element->category_canonical)){
		$canonicalUrl = hikashop_cleanURL(hikashop_translate($this->element->category_canonical));

		$doc = JFactory::getDocument();
		$doc->addCustomTag( '<link rel="canonical" href="'.$canonicalUrl.'" />' );
	}
	if(($this->params->get('show_image') && !empty($this->element->file_path))|| ($this->params->get('show_description')&&!empty($this->element->category_description))){
		?>
		<div class="hikashop_category_description">
		<?php
		if($this->params->get('show_image') && !empty($this->element->file_path)){
			jimport('joomla.filesystem.file');
			if(JFile::exists($this->image->getPath($this->element->file_path,false))){
			?>
			<img src="<?php echo $this->image->getPath($this->element->file_path); ?>" class="hikashop_category_image" title="<?php echo $this->escape(@$this->element->file_description); ?>" alt="<?php echo $this->escape(@$this->element->file_name); ?>"/>
			<?php
			}
		}
		if($this->params->get('show_description',1)&&!empty($this->element->category_description)){
			?>
			<div class="hikashop_category_description_content">
			<?php echo JHTML::_('content.prepare',$this->element->category_description); ?>
			</div>
			<?php
		}
		?>
		</div>
	<?php
	}
	if(!empty($this->fields)){ ?>

		<?php
		ob_start();
		$this->fieldsClass->prefix = '';
		foreach($this->fields as $fieldName => $oneExtraField) {
			if(!empty($this->element->$fieldName)){ ?>
			<tr class="hikashop_category_custom_<?php echo $oneExtraField->field_namekey;?>_line">
				<td class="key">
					<span id="hikashop_category_custom_name_<?php echo $oneExtraField->field_id;?>" class="hikashop_category_custom_name">
						<?php echo $this->fieldsClass->getFieldName($oneExtraField);?>
					</span>
				</td>
				<td>
					<span id="hikashop_category_custom_value_<?php echo $oneExtraField->field_id;?>" class="hikashop_category_custom_value">
						<?php echo $this->fieldsClass->show($oneExtraField,$this->element->$fieldName); ?>
					</span>
				</td>
			</tr>
		<?php }
		}
		$custom_fields_html = ob_get_clean();
		if(!empty($custom_fields_html)){ ?>
		<div id="hikashop_category_custom_info_main" class="hikashop_category_custom_info_main">
			<h4><?php echo JText::_('CATEGORY_ADDITIONAL_INFORMATION');?></h4>
			<table class="hikashop_category_custom_info_main_table">
				<?php echo $custom_fields_html; ?>
			</table>
		</div>
		<?php }
	}
	hikaInput::get()->set('hikashop_front_end_main',$val);
}

$layout_type = $this->params->get('layout_type');
if(empty($layout_type) || $layout_type=='table') $layout_type = 'div';
$html = $this->loadTemplate($layout_type);
if(!empty($html)) echo '<div class="hikashop_subcategories_listing">'.$html.'</div>';

if(!$this->module){
	$data = $this->params->get('data');
	if(empty($data)) {
		$hk_p = $this->params->get('hk_product');
		if(!empty($hk_p)) {
			$data = new stdClass();
			$data->hk_product = $hk_p;
		}
	}
	if(isset($data->hk_product) && (is_object($data->hk_product) || is_array($data->hk_product))) {
		$js = '';
		$empty='';
		jimport('joomla.html.parameter');
		$params = new HikaParameter($empty);
		foreach($data->hk_product as $k => $v){
			$params->set($k,$v);
		}
		$params->set('content_synchronize', 1);
		$main_div_name = 'hikashop_category_information_module_'.$params->get('id');
		$params->set('main_div_name',$main_div_name);
		echo '<div class="hikashop_submodules" style="clear:both">'.hikashop_getLayout('product', 'listing', $params, $js).'</div>';
	}
	else if(!empty($this->modules)){
		$html = '';
		jimport('joomla.application.module.helper');
		foreach($this->modules as $module){
			$html .= JModuleHelper::renderModule($module);
		}
		if(!empty($html)){
			echo '<div class="hikashop_submodules" style="clear:both">'.$html.'</div>';
		}
	}
}
$html = ob_get_clean();
if(!empty($html)){
	$category_id = 0;
	if(!empty($this->element->category_id))
		$category_id = $this->element->category_id;
	if(!empty($this->row->category_id))
		$category_id = $this->row->category_id;
?>
	<div id="<?php echo $this->params->get('main_div_name');?>" class="hikashop_category_information hikashop_categories_listing_main hikashop_category_listing_<?php echo $category_id; ?>">
		<?php echo $html; ?>
	</div>
<?php }	?>
