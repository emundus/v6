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
class hikashopBadgeClass extends hikashopClass {
	var $tables = array('badge');
	var $pkeys = array('badge_id');
	var $toggle = array('badge_published'=>'badge_id');

	public function saveForm() {
		$element = new stdClass();
		$element->badge_id = hikashop_getCID('badge_id');
		$formData = hikaInput::get()->get('data', array(), 'array' );
		jimport('joomla.filter.filterinput');
		$safeHtmlFilter = JFilterInput::getInstance(array(), array(), 1, 1);
		$nameboxes = array('badge_discount_id','badge_category_id','badge_product_id');
		foreach($formData['badge'] as $column => $value) {
			hikashop_secureField($column);
			if(in_array($column,$nameboxes)){
				hikashop_toInteger($value);
				$element->$column = ','.implode(',',$value).',';
			}else{
				$element->$column = $safeHtmlFilter->clean(strip_tags($value), 'string');
			}
		}
		foreach($nameboxes as $namebox){
			if(!isset($element->$namebox)){
				$element->$namebox = '';
			}
		}
		if(!empty($element->badge_start)){
			$element->badge_start = hikashop_getTime($element->badge_start);
		}
		if(!empty($element->badge_end)){
			$element->badge_end = hikashop_getTime($element->badge_end);
		}
		$fileClass = hikashop_get('class.file');
		$element->badge_image = $fileClass->saveFile();
		if(empty($element->badge_image))
			unset($element->badge_image);
		$status = $this->save($element);

		return $status;
	}

	public function loadBadges(&$row) {
		$discount = new stdClass();
		$qty = $row->product_quantity;
		if(isset($row->main)) {
			if(@$row->main->discount)
				$discount =& $row->main->discount;
			elseif(@$row->discount)
				$discount =& $row->discount;
			$product_id = $row->main->product_id;
			if($row->product_quantity == -1)
				$qty = $row->main->product_quantity;
		} else {
			if(@$row->discount)
				$discount =& $row->discount;
			$product_id = $row->product_id;
		}

		$period = time() - $row->product_created;

		$badge_filters = array(
			'a.badge_start <= '.time(),
			'(a.badge_end >= '.time().' OR a.badge_end = 0)',
			'a.badge_published = 1',
			'(a.badge_quantity = \'\' OR a.badge_quantity = '.(int)$qty.')',
			'(a.badge_new_period = 0 OR a.badge_new_period >= '.(int)$period.')',
		);
		if($discount && isset($discount->discount_id)) {
			$badge_filters[] = '(badge_discount_id = '.(int)@$discount->discount_id.' OR badge_discount_id = \'0\' OR badge_discount_id = \'\' OR badge_discount_id LIKE \'%,'.(int)@$discount->discount_id.',%\')';
		} else {
			$badge_filters[] = '(badge_discount_id = \'0\' OR badge_discount_id = \'\')';
		}

		$categories = array(
			'originals' => array(),
			'parents' => array()
		);
		$categoryClass = hikashop_get('class.category');
		$productClass = hikashop_get('class.product');

		if(!isset($row->categories) && isset($row->main->categories))
			$row->categories =& $row->main->categories;

		if(isset($row->categories)) {
			$oneCat = reset($row->categories);
			if(is_object($oneCat))
				$loadedCategories = array_keys($row->categories);
			else
				$loadedCategories = $row->categories;
		} else
			$loadedCategories = $productClass->getCategories($product_id);

		if(!empty($row->main->product_manufacturer_id))
			$categories['originals'][$row->main->product_manufacturer_id] = $row->main->product_manufacturer_id;
		if(!empty($row->product_manufacturer_id))
			$categories['originals'][$row->product_manufacturer_id] = $row->product_manufacturer_id;

		if(!empty($loadedCategories)) {
			foreach($loadedCategories as $cat) {
				$categories['originals'][$cat] = $cat;
			}
		}

		$parents = $categoryClass->getParents($loadedCategories);
		if(!empty($parents) && is_array($parents)) {
			foreach($parents as $parent) {
				$categories['parents'][$parent->category_id] = $parent->category_id;
			}
		}

		hikashop_addACLFilters($badge_filters,'badge_access', 'a');

		$badge_filters = implode(' AND ',$badge_filters);

		if(!empty($categories)) {
			$categories_filter = array(' AND ((badge_category_childs = 0 AND (badge_category_id = \'0\' OR badge_category_id = \'\'');
			if(!empty($categories['originals'])) {
				foreach($categories['originals'] as $cat) {
					$categories_filter[] = 'badge_category_id = \''.(int)$cat.'\'';
					$categories_filter[] = 'badge_category_id LIKE \'%,'.(int)$cat.',%\'';
				}
			}
			$badge_filters .= implode(' OR ',$categories_filter).'))';

			$categories_filter = array(' OR (badge_category_childs = 1 AND (badge_category_id=\'0\' OR badge_category_id=\'\'');
			if(!empty($categories['parents'])) {
				foreach($categories['parents'] as $cat) {
					$categories_filter[] = 'badge_category_id = \''.(int)$cat.'\'';
					$categories_filter[] = 'badge_category_id LIKE \'%,'.(int)$cat.',%\'';
				}
			}
			$badge_filters .= implode(' OR ',$categories_filter).')))';
		}

		static $badges = array();
		$key = sha1($badge_filters);

		if(!isset($badges[$key])) {
			$query = 'SELECT a.* FROM '.hikashop_table('badge').' AS a WHERE '.$badge_filters.' ORDER BY a.badge_ordering ASC,a.badge_id ASC';
			$this->database->setQuery($query);
			$badges[$key] = $this->database->loadObjectList();
		}

		$badgesForProduct = array();
		if(is_array($badges[$key]) && !empty($badges[$key])) {
			foreach($badges[$key] as $badge){
				if(!empty($badge->badge_product_id)) {
					if(!is_array($badge->badge_product_id))
						$badge->badge_product_id = explode(',',$badge->badge_product_id);
					if(!in_array($product_id,$badge->badge_product_id))
						continue;
				}
				$badgesForProduct[] = $badge;
			}
		}

		if(!empty($badgesForProduct)) {
			$row->badges = $badgesForProduct;
		} else {
			$row->badges = null;
		}
	}

	public function placeBadges(&$image, &$badges, $vertical = 0, $horizontal = 0, $echo = true) {
		if(empty($badges))
			return;

		$options = array();

		if(is_array($vertical)) {
			$options = $vertical;
			if(!isset($options['vertical']))
				$options['vertical'] = 0;
			if(!isset($options['horizontal']))
				$options['horizontal'] = 0;
			if(!isset($options['echo']))
				$options['echo'] = true;
		} else {
			$options['vertical'] = $vertical;
			$options['horizontal'] = $horizontal;
			$options['echo'] = $echo;
		}

		$position1 = 0; $position2 = 0; $position3 = 0; $position4 = 0;

		$backup_main_x = $image->main_thumbnail_x;
		$backup_main_y = $image->main_thumbnail_y;

		if(!empty($options['thumbnail']) && !empty($options['thumbnail']->req_width) && !empty($options['thumbnail']->req_height)) {
			$width_real = $options['thumbnail']->req_width;
			$height_real = $options['thumbnail']->req_height;
		} else {
			$width_real = $image->thumbnail_x;
			$height_real = $image->thumbnail_y;
		}
		$html = '';

		$config = hikashop_config();

		foreach($badges as $badge) {
			if(empty($badge->badge_published))
				continue;

			if(!empty($badge->badge_keep_size)) {
				list($badge_width, $badge_height) = getimagesize($image->getPath(@$badge->badge_image,false));
			} else {
				$badge_width = intval(($width_real * $badge->badge_size) / 100);
				$badge_height = intval(($height_real * $badge->badge_size) / 100);
			}

			$position = $badge->badge_position;
			$position_top = (int)($badge->badge_vertical_distance + $options['vertical']);
			$position_right = (int)($badge->badge_horizontal_distance + $options['horizontal']);
			$position_left = (int)($badge->badge_horizontal_distance + $options['horizontal']);
			$position_bottom = (int)($badge->badge_vertical_distance + $options['vertical']);

			$styletopleft = 'position:absolute; z-index:2; top:'.$position_top.'px; left:'.$position_left.'px; margin-top:10px;';
			$styletopright = 'position:absolute; z-index:3; top:'.$position_top.'px; right:'.$position_right.'px; margin-top:10px;';
			$stylebottomleft = 'position:absolute; z-index:4; bottom:'.$position_bottom.'px; left:'.$position_left.'px; margin-bottom:10px;';
			$stylebottomright = 'position:absolute; z-index:5; bottom:'.$position_bottom.'px; right:'.$position_right.'px; margin-bottom:10px;';

			$image_options = array('default' => true,'forcesize' => $config->get('image_force_size', true), 'scale' => $config->get('image_scale_mode', 'inside'));
			$img = $image->getThumbnail(@$badge->badge_image, array('width' => $badge_width, 'height' => $badge_height), $image_options);
			if(!$img)
				continue;

			$imageDisplayed = '<img class="hikashop_product_badge_image" title="'.htmlentities(@$badge->badge_name, ENT_COMPAT, 'UTF-8').'" alt="'.htmlentities(@$badge->badge_name, ENT_COMPAT, 'UTF-8').'" src="'.$img->url.'"/>';
			if(!empty($badge->badge_url)) {
				$imageDisplayed = '<a href="'.hikashop_cleanURL($badge->badge_url).'">'. $imageDisplayed . '</a>';
			}
			if($position == 'topleft' && ($position1 == 0 || $badge->badge_ordering < $position1)) {
				$html .= '<div class="hikashop_badge_div hikashop_badge_topleft_div" style="' . $styletopleft . '">' . $imageDisplayed . '</div>';
				$position1 = $badge->badge_ordering;
			}
			elseif($position == 'topright' && ($position2 == 0 || $badge->badge_ordering < $position2)) {
				$html .= '<div class="hikashop_badge_div hikashop_badge_topright_div" style="' . $styletopright . '">' . $imageDisplayed . '</div>';
				$position2 = $badge->badge_ordering;
			}
			elseif($position == 'bottomright' && ($position3 == 0 || $badge->badge_ordering < $position3)) {
				$html .= '<div class="hikashop_badge_div hikashop_badge_bottomright_div" style="' . $stylebottomright . '">' . $imageDisplayed . '</div>';
				$position3 = $badge->badge_ordering;
			}
			elseif($position == 'bottomleft' && ($position4 == 0 || $badge->badge_ordering < $position4)) {
				$html .= '<div class="hikashop_badge_div hikashop_badge_bottomleft_div" style="' . $stylebottomleft . '">' . $imageDisplayed . '</div>';
				$position4 = $badge->badge_ordering;
			}
		}

		$image->main_thumbnail_x = $backup_main_x;
		$image->main_thumbnail_y = $backup_main_y;
		if(!$options['echo'])
			return $html;

		echo $html;
		return null;
	}
	public function delete(&$elements) {
		$do = true;
		JPluginHelper::importPlugin('hikashop');
		$app = JFactory::getApplication();
		$app->triggerEvent('onBeforeBadgeDelete', array(&$elements, &$do));

		if(!$do)
			return false;

		$status = parent::delete($elements);
		if($status) {
			$app->triggerEvent('onAfterBadgeDelete', array(&$elements));
		}
		return $status;
	}

	public function save(&$element) {
		$isNew = empty($element->badge_id);

		$do = true;
		JPluginHelper::importPlugin('hikashop');
		$app = JFactory::getApplication();
		if($isNew) {
			$app->triggerEvent('onBeforeBadgeCreate', array( &$element, &$do ));
		} else {
			$app->triggerEvent('onBeforeBadgeUpdate', array( &$element, &$do ));
		}

		if(!$do)
			return false;

		$status = parent::save($element);
		if(!$status)
			return $status;

		if($isNew) {
			$app->triggerEvent('onAfterBadgeCreate', array( &$element ));
		} else {
			$app->triggerEvent('onAfterBadgeUpdate', array( &$element ));
		}

		if($isNew) {
			$element->badge_id = $status;
			$orderHelper = hikashop_get('helper.order');
			$orderHelper->pkey = 'badge_id';
			$orderHelper->table = 'badge';
			$orderHelper->orderingMap = 'badge_ordering';
			$orderHelper->reOrder();
		}
		return $status;
	}
}
