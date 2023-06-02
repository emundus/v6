<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class hikashopProduct_informationType{
	function load($fields,$id, $sort){

		$this->values = array();
		$this->values[] = JHTML::_('select.option', 'weight',JText::_('PRODUCT_WEIGHT'));
		$this->values[] = JHTML::_('select.option', 'volume',JText::_('PRODUCT_VOLUME'));
		$this->values[] = JHTML::_('select.option', 'height',JText::_('PRODUCT_HEIGHT'));
		$this->values[] = JHTML::_('select.option', 'length',JText::_('PRODUCT_LENGTH'));
		$this->values[] = JHTML::_('select.option', 'width',JText::_('PRODUCT_WIDTH'));
		$this->values[] = JHTML::_('select.option', 'surface',JText::_('PRODUCT_SURFACE'));
		if(empty($id) || $id=='datafilterfilter_data_cursor'){
			$this->values[] = JHTML::_('select.option', 'b.product_name',JText::_('PRODUCT_NAME'));
			$this->values[] = JHTML::_('select.option', 'b.product_code',JText::_('PRODUCT_CODE'));
			$this->values[] = JHTML::_('select.option', 'price',JText::_('PRICE'));
			$this->values[] = JHTML::_('select.option', 'b.product_sort_price',JText::_('PRODUCT_SORT_PRICE'));
			$this->values[] = JHTML::_('select.option', 'b.product_average_score',JText::_('RATING'));
			$fieldClass = hikashop_get('class.field');
			$fields = $fieldClass->getData('all','product');
			if(!empty($fields)){
				foreach($fields as $field){
					$this->values[] = JHTML::_('select.option', 'b.'.$field->field_namekey,$field->field_realname);
				}
			}
		}

		if(!empty($fields) && is_array($fields)){
			foreach($fields as $field){
				$this->values[] = JHTML::_('select.option', $field->field_namekey, $field->field_realname);
			}
		}
		if(!$sort)
			return;

		$this->values[] = JHTML::_('select.option', 'b.product_created',JText::_('CREATION_DATE'));
		$this->values[] = JHTML::_('select.option', 'b.product_sales',JText::_('SALES'));
		$this->values[] = JHTML::_('select.option', 'b.product_modified',JText::_('MODIFICATION_DATE'));
		$this->values[] = JHTML::_('select.option', 'b.product_hit',JText::_('CLICKS'));

		$values = array();
		foreach($this->values as $value) {
			if(JText::_('SORT_ASCENDING_'.$value->text)!='SORT_ASCENDING_'.$value->text){ $asc_name=JText::_('SORT_ASCENDING_'.$value->text); }
			else{ $asc_name=JText::sprintf('SORT_ASCENDING', $value->text); }

			if(JText::_('SORT_DESCENDING_'.$value->text)!='SORT_DESCENDING_'.$value->text){ $desc_name=JText::_('SORT_DESCENDING_'.$value->text); }
			else{ $desc_name=JText::sprintf('SORT_DESCENDING', JText::_($value->text)); }
			$values[$value->value.'--lth'] = $asc_name;
			$values[$value->value.'--htl'] = $desc_name;
		}


		$this->values = $values;
	}

	function display($map,$value, $fields='', $option='size="1" ',$id=null, $sort=false){

		$this->load($fields,$id, $sort);
		if($sort) {
			$nameboxType = hikashop_get('type.namebox');
			if(!is_array($value))
				$value = explode(',', trim((string)$value));
			return $nameboxType->display(
				$map,
				$value,
				hikashopNameboxType::NAMEBOX_MULTIPLE,
				'rawlist',
				array(
					'delete' => true,
					'sort' => true,
					'default_text' => '<em>'.JText::_('HIKA_NONE').'</em>',
					'rawdata' => $this->values
				)
			);
		}
		return JHTML::_('select.genericlist', $this->values, $map, 'class="custom-select" '.$option, 'value', 'text', $value, $id );
	}

}
