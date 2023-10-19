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
if(empty($this->rows))
	return;

$pagination = $this->config->get('pagination','bottom');
if(in_array($pagination,array('top','both')) && $this->params->get('show_limit') && $this->pageInfo->elements->total > $this->pageInfo->limit->value) {
	$this->pagination->form = '_top';
?>
	<form action="<?php echo hikashop_currentURL();?>" method="post" name="adminForm_<?php echo $this->params->get('main_div_name').$this->category_selected;?>_top">
		<div class="hikashop_subcategories_pagination hikashop_subcategories_pagination_top">
		<?php echo $this->pagination->getListFooter($this->params->get('limit')); ?>
		<span class="hikashop_results_counter"><?php echo $this->pagination->getResultsCounter(); ?></span>
		</div>
		<input type="hidden" name="filter_order_<?php echo $this->params->get('main_div_name').$this->category_selected;?>" value="<?php echo $this->pageInfo->filter->order->value; ?>" />
		<input type="hidden" name="filter_order_Dir_<?php echo $this->params->get('main_div_name').$this->category_selected;?>" value="<?php echo $this->pageInfo->filter->order->dir; ?>" />
		<?php echo JHTML::_('form.token'); ?>
	</form>
<?php } ?>
	<div class="hikashop_subcategories">
<?php

$app = JFactory::getApplication();
$in_hikashop_context = (hikaInput::get()->getString('option') == HIKASHOP_COMPONENT && in_array(hikaInput::get()->getString('ctrl','category'), array('category', 'product')));
$cid = 0;
$last_category_selected = 0;

$before_nb_products = JText::_('BEFORE_NUMBER_OF_PRODUCTS');
if($before_nb_products == 'BEFORE_NUMBER_OF_PRODUCTS') $before_nb_products = ' (';
$after_nb_products = JText::_('AFTER_NUMBER_OF_PRODUCTS');
if($after_nb_products == 'AFTER_NUMBER_OF_PRODUCTS') $after_nb_products = ')';

if($in_hikashop_context) {
	if(hikaInput::get()->getString('ctrl','category') == 'product' && hikaInput::get()->getString('task','listing') == 'show') {
		$last_category_selected = (int)$app->getUserState(HIKASHOP_COMPONENT.'.last_category_selected', 0);
		$config =& hikashop_config();
		$pathway_sef_name = $config->get('pathway_sef_name', 'category_pathway');
		$cid = hikaInput::get()->getInt($pathway_sef_name, 0);
	} else {
		$cid = hikaInput::get()->getInt('cid', 0);
	}
}

$only_if_products = $this->params->get('only_if_products', 0);
switch($this->params->get('child_display_type')){
	case 'nochild':
	default:
?>
	<ul class="hikashop_category_list <?php echo $this->params->get('ul_class_name'); ?>">
<?php
		$columns = $this->params->get('columns');
		if(empty($columns)|| $columns == 0)
			$columns = 1;
		$width = (int)(100 / $columns) - 2;
		$current_column = 1;

		if(empty($width))
			$width='style="float:left;"';
		else
			$width='style="float:left;width:'.$width.'%;"';

		$found = 0;
		if($in_hikashop_context) {
			$found = $last_category_selected;
			foreach($this->rows as $row) {
				if($cid == $row->category_id) {
					$found = (int)$row->category_id;
					$app->setUserState(HIKASHOP_COMPONENT.'.last_category_selected', (int)$row->category_id);
					break;
				}
			}
		}

		foreach($this->rows as $row) {
			if($only_if_products && $row->number_of_products < 1)
				continue;

			$link = $this->getLink($row);
			$class = ($found == $row->category_id) ? ' current active' : '';
?>
		<li class="hikashop_category_list_item<?php echo $class; ?>" <?php echo $width; ?>>
			<a href="<?php echo $link; ?>"><?php
				echo $row->category_name;
				if($this->params->get('number_of_products', 0)) {
					echo $before_nb_products . $row->number_of_products . $after_nb_products;
				}
			?></a>
		</li>
<?php
			if($current_column >= $columns) {
				$current_column = 0;
			}
			$current_column++;
		}
?>
	</ul>
<?php
		break;

	case 'allchildsexpand':
		if($this->params->get('ul_display_simplelist', 0)) {
?>
	<ul class="hikashop_category_list <?php echo $this->params->get('ul_class_name'); ?>">
<?php
			foreach($this->rows as $k => $row) {
				if($only_if_products && $row->number_of_products < 1)
					continue;

				$found = 0;
				if($in_hikashop_context) {
					$found = $last_category_selected;
					if($cid == $row->category_id) {
						$found = (int)$row->category_id;
						$app->setUserState(HIKASHOP_COMPONENT.'.last_category_selected', (int)$row->category_id);
					}
				}

				$link = $this->getLink($row);
				$class = ($found == $row->category_id) ? ' current active' : '';

?>
		<li class="hikashop_category_list_item<?php echo $class; ?>">
			<a href="<?php echo $link; ?>"><?php
				echo $row->category_name;
				if($this->params->get('number_of_products', 0))
					echo $before_nb_products . $row->number_of_products . $after_nb_products;
			?></a>
<?php
				if(!empty($row->childs)) {
					$limit = $this->params->get('child_limit');
					$i = 0;
?>			<ul>
<?php
					foreach($row->childs as $child) {
						if($only_if_products && $child->number_of_products < 1)
							continue;
						if(!empty($limit) && $i >= $limit) {
							break;
						}
						$i++;
						$link = $this->getLink($child);
						$class = ($found == $child->category_id) ? ' current active' : '';
?>
				<li class="hikashop_category_list_item<?php echo $class; ?>">
					<a href="<?php echo $link; ?>"><?php
						echo $child->category_name;
						if($this->params->get('number_of_products', 0))
							echo $before_nb_products. $child->number_of_products . $after_nb_products;
					?></a>
				</li>
<?php
					}
?>
			</ul>
<?php
				}
?>
		</li>
<?php
			}
?>
	</ul>
<?php
		} else {
?>
	<div id="category_panel_<?php echo $this->params->get('id');?>" class="pane-sliders">
<?php
			foreach($this->rows as $k => $row) {
				if($only_if_products && $row->number_of_products < 1)
					continue;
				if($this->params->get('number_of_products', 0)) {
					$row->category_name .= ' (' . $row->number_of_products . ')';
				}

				if(!$this->module || $this->params->get('links_on_main_categories')){
					$link = $this->getLink($row);
					$row->category_name = '<a href="' . $link . '">' . $row->category_name . '</a>';
				}
?>
		<div class="panel">
			<h4 class="jpane-toggler title" id="category_pane_<?php echo $k;?>" style="cursor:default;">
				<span><?php echo $row->category_name; ?></span>
			</h4>
			<div class="jpane-slider content">
				<ul class="hikashop_category_list <?php echo $this->params->get('ul_class_name'); ?>">
<?php
				if(!empty($row->childs)) {
					$app = JFactory::getApplication();

					$found = '';
					if($in_hikashop_context) {
						foreach($row->childs as $child) {
							if($cid == $child->category_id) {
								$found = (int)$child->category_id;
								$app->setUserState(HIKASHOP_COMPONENT.'.last_category_selected', (int)$child->category_id);
								break;
							}
						}
					}

					$limit = $this->params->get('child_limit');
					$i = 0;
					foreach($row->childs as $child) {
						if($only_if_products && $child->number_of_products < 1)
							continue;
						if(!empty($limit) && $i >= $limit)
							break;

						$i++;
						$link = $this->getLink($child);
						$class = ($found == $child->category_id) ? ' current active' : '';
?>
					<li class="hikashop_category_list_item<?php echo $class; ?>">
						<a href="<?php echo $link; ?>"><?php
							echo $child->category_name;
							if($this->params->get('number_of_products', 0)) {
								echo $before_nb_products . $child->number_of_products . $after_nb_products;
							}
						?></a>
					</li>
<?php
						}
				} else {
					echo JText::_('HIKA_LISTING_LIST_EMPTY');
				}
?>
				</ul>
			</div>
		</div>
<?php
			}
?>
	</div>
<?php
		}
		break;

	case 'allchilds':
		$found = -1;
		$sub_selected = -1;
		if($in_hikashop_context) {
			if(hikaInput::get()->getString('ctrl', 'category') == 'product' && hikaInput::get()->getString('task', 'listing') == 'show') {
				$database = JFactory::getDBO();
				$query = 'SELECT category_id FROM '.hikashop_table('product_category').' WHERE product_id = ' . (int)hikashop_getCID('product_id') . ' ORDER BY product_category_id ASC';
				$database->setQuery($query);
				$cid = $database->loadColumn();
				if(empty($cid)) {
					$class = hikashop_get('class.product');
					$product = $class->get(hikashop_getCID('product_id'));
					if($product && $product->product_type == 'variant' && $product->product_parent_id) {
						$query = 'SELECT category_id FROM '.hikashop_table('product_category').' WHERE product_id = ' . (int)$product->product_parent_id . ' ORDER BY product_category_id ASC';
						$database->setQuery($query);
						$cid = $database->loadColumn();
					}
				}
			}
			if(empty($cid)){
				if(!HIKASHOP_J30){
					$menuClass = hikashop_get('class.menus');
					$menuData = $menuClass->get($this->params->get('itemid',0));
					if(@$menuData->hikashop_params['content_type']=='product' && isset($menuData->hikashop_params['selectparentlisting']))
						$cid = $menuData->hikashop_params['selectparentlisting'];
				}else{
					$app = JFactory::getApplication();
					$menuItem = $app->getMenu()->getActive();
					if(is_object($menuItem)){
						$category_params = $menuItem->getParams();
						$hkParams = $category_params->get('hk_product',false);
						if(isset($hkParams->category))
							$cid = $hkParams->category;
					}
				}
			}

			if(!is_array($cid))
				$cid = array($cid);

			$i = 0;
			foreach($this->rows as $k => $row) {
				if($only_if_products && $row->number_of_products < 1)
					continue;
				if(in_array((int)$row->category_id,$cid)) {
					$found = $i;
					break;
				}
				if(!empty($row->childs)) {
					foreach($row->childs as $child) {
						if(in_array($child->category_id,$cid)) {
							$found = $i;
							$sub_selected = $row->category_id;
							break 2;
						}
					}
				}
				$i++;
			}

			$app = JFactory::getApplication();
			if($found >= 0) {
				$app->setUserState(HIKASHOP_COMPONENT.'.last_category_selected', $found);
			} elseif(hikaInput::get()->getString('ctrl', 'category') != 'category' || hikaInput::get()->getString('task','listing') != 'listing') {
				$found = (int)$last_category_selected;
			}
		} else {
			$cid = array(0);
		}

		if($this->params->get('ul_display_simplelist', 0)) {
?>
	<ul class="hikashop_category_list <?php echo $this->params->get('ul_class_name'); ?>">
<?php
			foreach($this->rows as $k => $row) {
				if($only_if_products && $row->number_of_products < 1)
					continue;

				$link = $this->getLink($row);
				$class = ($cid == $row->category_id) ? ' current active' : '';

?>
		<li class="hikashop_category_list_item<?php echo $class; ?>">
			<a href="<?php echo $link; ?>"><?php
				echo $row->category_name;
				if($this->params->get('number_of_products', 0))
					echo $before_nb_products . $row->number_of_products . $after_nb_products;
			?></a>
<?php
				if(!empty($row->childs)) {
					$sub_selected = false;
					if(!in_array($row->category_id, $cid)) {
						foreach($row->childs as $child) {
							if(in_array($child->category_id, $cid)) {
								$sub_selected = true;
								break;
							}
						}
					}

					if($cid == $row->category_id || $sub_selected == $row->category_id) {
						$limit = $this->params->get('child_limit');
						$i = 0;
?>			<ul>
<?php
						foreach($row->childs as $child) {
							if($only_if_products && $child->number_of_products < 1)
								continue;
							if(!empty($limit) && $i >= $limit)
								break;
							$i++;
							$link = $this->getLink($child);
							$class = ($found == $child->category_id) ? ' current active' : '';
?>
				<li class="hikashop_category_list_item<?php echo $class; ?>">
					<a href="<?php echo $link; ?>"><?php
						echo $child->category_name;
						if($this->params->get('number_of_products', 0))
							echo $before_nb_products . $child->number_of_products . $after_nb_products;
					?></a>
				</li>
<?php
						}
?>
			</ul>
<?php
					}
				}
?>
		</li>
<?php
			}
?>
	</ul>
<?php
		} else {
			jimport('joomla.html.pane');
			$this->tabs = hikashop_get('helper.sliders');
			$this->tabs->setOptions(array(
				'startOffset' => $found,
				'startTransition' => 0,
				'displayFirst' => 0
			));
			echo $this->tabs->startPane('category_panel_'.$this->params->get('id'));
			foreach($this->rows as $k => $row) {
				if($only_if_products && $row->number_of_products < 1)
					continue;
				if($this->params->get('number_of_products', 0)) {
					$row->category_name .= ' (' . $row->number_of_products . ')';
				}

				if( !$this->module || $this->params->get('links_on_main_categories') || empty($row->childs)) {
					$link = $this->getLink($row);
					$row->category_name = '<a href="'.$link.'">'.$row->category_name.'</a>';
				}

				$toOpen = false;
				if(in_array($row->category_id,$cid))
					$toOpen = true;

				if(!empty($row->childs)) {
					foreach($row->childs as $child) {
						if(in_array($child->category_id,$cid))
							$toOpen = true;
					}
				}

				echo $this->tabs->startPanel($row->category_name, 'category_pane_'.$k, !empty($row->childs), $toOpen);
				if(!empty($row->childs)) {
?>
		<ul class="hikashop_category_list <?php echo $this->params->get('ul_class_name'); ?>">
<?php
					foreach($row->childs as $child) {
						if($only_if_products && $child->number_of_products < 1)
							continue;
						$class = (in_array($child->category_id, $cid)) ? ' current active' : '';
						$link = $this->getLink($child);
?>
			<li class="hikashop_category_list_item<?php echo $class; ?>">
				<a class="hikashop_category_list_item_link" href="<?php echo $link; ?>"><?php
					echo $child->category_name;
					if($this->params->get('number_of_products', 0))
						echo $before_nb_products . $child->number_of_products . $after_nb_products;
				?></a>
			</li>
<?php
					}
?>
		</ul>
<?php
				} else {
					echo JText::_('HIKA_LISTING_LIST_EMPTY');
				}
				echo $this->tabs->endPanel();
			}
			echo $this->tabs->endPane();
		}
		break;
	}
?>
	</div>
<?php
if(in_array($pagination,array('bottom','both')) && $this->params->get('show_limit') && $this->pageInfo->elements->total > $this->pageInfo->limit->value) {
	$this->pagination->form = '_bottom';
?>
	<form action="<?php echo hikashop_currentURL();?>" method="post" name="adminForm_<?php echo $this->params->get('main_div_name').$this->category_selected;?>_bottom">
		<div class="hikashop_subcategories_pagination hikashop_subcategories_pagination_bottom">
		<?php echo $this->pagination->getListFooter($this->params->get('limit')); ?>
		<span class="hikashop_results_counter"><?php echo $this->pagination->getResultsCounter(); ?></span>
		</div>
		<input type="hidden" name="filter_order_<?php echo $this->params->get('main_div_name').$this->category_selected;?>" value="<?php echo $this->pageInfo->filter->order->value; ?>" />
		<input type="hidden" name="filter_order_Dir_<?php echo $this->params->get('main_div_name').$this->category_selected;?>" value="<?php echo $this->pageInfo->filter->order->dir; ?>" />
		<?php echo JHTML::_('form.token'); ?>
	</form>
<?php }
