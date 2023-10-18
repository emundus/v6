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

class plgAcymailingHikashop extends JPlugin{
	var $cats = array();
	var $tags = array();

	function __construct(&$subject, $config){
		parent::__construct($subject, $config);
		if(!isset($this->params)){
			$plugin = JPluginHelper::getPlugin('acymailing', 'hikashop');
			$this->params = new acyParameter($plugin->params);
		}

		$this->acypluginsHelper = acymailing_get('helper.acyplugins');
		$this->db = JFactory::getDBO();
	}

	function loadAcymailing(){
		if(isset($this->hikashop_installed)) return $this->hikashop_installed;
		if(!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
		if(!include_once(rtrim(JPATH_ADMINISTRATOR, DS).DS.'components'.DS.'com_hikashop'.DS.'helpers'.DS.'helper.php')){
			$this->hikashop_installed = false;
		}else{
			$this->hikashop_installed = true;
		}
		return $this->hikashop_installed;
	}

	function acymailing_getPluginType(){
		$onePlugin = new stdClass();
		$onePlugin->name = 'HikaShop';
		$onePlugin->function = 'acymailinghikashop_show';
		$onePlugin->help = 'plugin-hikashop';
		return $onePlugin;
	}

	function acymailinghikashop_show(){
		$config = acymailing_config();
		if(version_compare($config->get('version'), '5.2.0', '<')){
			acymailing_display('Please download and install the latest AcyMailing version otherwise this plugin will NOT work', 'error');
			return;
		}
		if(!$this->loadAcymailing()) return 'Please install HikaShop before using the HikaShop tag plugin';
		$app = JFactory::getApplication();
		$contentType = array();
		$contentType[] = JHTML::_('select.option', "title", JText::_('TITLE_ONLY'));
		$contentType[] = JHTML::_('select.option', "intro", JText::_('INTRO_ONLY'));
		$contentType[] = JHTML::_('select.option', "full", JText::_('FULL_TEXT'));
		$priceDisplay = array();
		$priceDisplay[] = JHTML::_('select.option', "full", JText::_('APPLY_DISCOUNTS'));
		$priceDisplay[] = JHTML::_('select.option', "no_discount", JText::_('NO_DISCOUNT'));
		$priceDisplay[] = JHTML::_('select.option', "none", JText::_('HIKASHOP_NO'));
		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();
		$paramBase = ACYMAILING_COMPONENT.'.hikashop';
		$pageInfo->filter->order->value = $app->getUserStateFromRequest($paramBase.".filter_order", 'filter_order', 'a.product_id', 'cmd');
		$pageInfo->filter->order->dir = $app->getUserStateFromRequest($paramBase.".filter_order_Dir", 'filter_order_Dir', 'desc', 'word');
		if(strtolower($pageInfo->filter->order->dir) !== 'desc') $pageInfo->filter->order->dir = 'asc';
		$pageInfo->search = $app->getUserStateFromRequest($paramBase.".search", 'search', '', 'string');
		$pageInfo->search = JString::strtolower(trim($pageInfo->search));
		$pageInfo->lang = $app->getUserStateFromRequest($paramBase.".lang", 'lang', '', 'string');
		$pageInfo->contenttype = $app->getUserStateFromRequest($paramBase.".contenttype", 'contenttype', 'full', 'string');
		$pageInfo->pricedisplay = $app->getUserStateFromRequest($paramBase.".pricedisplay", 'pricedisplay', 'full', 'string');
		$pageInfo->limit->value = $app->getUserStateFromRequest($paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int');
		$pageInfo->limit->start = $app->getUserStateFromRequest($paramBase.'.limitstart', 'limitstart', 0, 'int');

		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.acymailing_getEscaped($pageInfo->search).'%\'';
			$filters[] = "a.product_id LIKE $searchVal OR a.product_description LIKE $searchVal OR a.product_name LIKE $searchVal OR a.product_code LIKE $searchVal";
		}
		$whereQuery = '';
		if(!empty($filters)){
			$whereQuery = ' WHERE ('.implode(') AND (', $filters).')';
		}
		$query = 'SELECT SQL_CALC_FOUND_ROWS a.* FROM '.acymailing_table('hikashop_product', false).' as a';
		if(!empty($whereQuery)) $query .= $whereQuery;
		if(!empty($pageInfo->filter->order->value)){
			$query .= ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;
		}
		$this->db->setQuery($query, $pageInfo->limit->start, $pageInfo->limit->value);
		$rows = $this->db->loadObjectList();
		if(!empty($pageInfo->search)){
			$rows = acymailing_search($pageInfo->search, $rows);
		}
		$this->db->setQuery('SELECT FOUND_ROWS()');
		$pageInfo->elements = new stdClass();
		$pageInfo->elements->total = $this->db->loadResult();
		$pageInfo->elements->page = count($rows);
		jimport('joomla.html.pagination');
		$pagination = new JPagination($pageInfo->elements->total, $pageInfo->limit->start, $pageInfo->limit->value);
		$tabs = acymailing_get('helper.acytabs');
		echo $tabs->startPane('hikashop_tab');
		echo $tabs->startPanel(JText::_('PRODUCTS'), 'hikashop_product');
		?>
		<script language="javascript" type="text/javascript">
			<!--
			var selectedContents = new Array();
			function applyContent(productid, rowClass){
				var tmp = selectedContents.indexOf(productid)
				if(tmp != -1){
					window.document.getElementById('content' + productid).className = rowClass;
					delete selectedContents[tmp];
				}else{
					window.document.getElementById('content' + productid).className = 'selectedrow';
					selectedContents.push(productid);
				}
				updateTagProd();
			}

			function updateTagProd(){
				var tag = '';
				var information = '';

				for(var i = 0; i < document.adminForm.contenttype.length; i++){
					if(document.adminForm.contenttype[i].checked){
						information += '| type:' + document.adminForm.contenttype[i].value;
					}
				}

				for(i = 0; i < document.adminForm.pricedisplay.length; i++){
					if(document.adminForm.pricedisplay[i].checked){
						information += '| price:' + document.adminForm.pricedisplay[i].value;
					}
				}

				if(window.document.getElementById('jflang') && window.document.getElementById('jflang').value != ''){
					information += '| lang:' + window.document.getElementById('jflang').value;
				}

				for(var i in selectedContents){
					if(selectedContents[i] && !isNaN(i)){
						tag = tag + '{hikashop_product:' + selectedContents[i] + information + '}<br />';
					}
				}

				setTag(tag);
			}
			//-->
		</script>
		<div class="onelineblockoptions">
			<table width="100%" class="acymailing_table">
				<tr>
					<td>
						<?php echo JText::_('DISPLAY'); ?>
					</td>
					<td>
						<?php echo JHTML::_('acyselect.radiolist', $contentType, 'contenttype', 'size="1" onchange="updateTagProd();"', 'value', 'text', $pageInfo->contenttype); ?>
					</td>
					<td>
						<?php $jflanguages = acymailing_get('type.jflanguages');
						$jflanguages->onclick = ' onchange="updateTagProd();" ';
						echo $jflanguages->display('lang', $pageInfo->lang); ?>
					</td>
				</tr>
				<tr>
					<td>
						<?php echo JText::_('PRICE'); ?>
					</td>
					<td colspan="2">
						<?php echo JHTML::_('acyselect.radiolist', $priceDisplay, 'pricedisplay', 'size="1" onchange="updateTagProd();"', 'value', 'text', $pageInfo->pricedisplay); ?>
					</td>
				</tr>
			</table>
		</div>
		<div class="onelineblockoptions">
			<table class="acymailing_table_options">
				<tr>
					<td>
						<?php acymailing_listingsearch($pageInfo->search); ?>
					</td>
				</tr>
			</table>
			<table class="acymailing_table" cellpadding="1" width="100%">
				<thead>
				<tr>
					<th></th>
					<th class="title">
						<?php echo JHTML::_('grid.sort', JText::_('HIKA_NAME'), 'a.product_name', $pageInfo->filter->order->dir, $pageInfo->filter->order->value); ?>
					</th>
					<th class="title">
						<?php echo JHTML::_('grid.sort', JText::_('HIKA_DESCRIPTION'), 'a.product_description', $pageInfo->filter->order->dir, $pageInfo->filter->order->value); ?>
					</th>
					<th class="title titleid">
						<?php echo JHTML::_('grid.sort', JText::_('ID'), 'a.product_id', $pageInfo->filter->order->dir, $pageInfo->filter->order->value); ?>
					</th>
				</tr>
				</thead>
				<tfoot>
				<tr>
					<td colspan="4">
						<?php echo $pagination->getListFooter(); ?>
						<?php echo $pagination->getResultsCounter(); ?>
					</td>
				</tr>
				</tfoot>
				<tbody>
				<?php
				$k = 0;
				for($i = 0, $a = count($rows); $i < $a; $i++){
					$row =& $rows[$i];
					?>
					<tr id="content<?php echo $row->product_id ?>" class="<?php echo "row$k"; ?>" onclick="applyContent(<?php echo $row->product_id; ?>,'<?php echo "row$k"; ?>');" style="cursor:pointer;">
						<td class="acytdcheckbox"></td>
						<td>
							<?php
							echo acymailing_tooltip('CODE : '.$row->product_code, $row->product_name, '', $row->product_name);
							?>
						</td>
						<td>
							<?php
							echo $row->product_description;
							?>
						</td>
						<td class="hk_center">
							<?php echo $row->product_id; ?>
						</td>
					</tr>
					<?php
					$k = 1 - $k;
				}
				?>
				</tbody>
			</table>
		</div>
		<input type="hidden" name="boxchecked" value="0"/>
		<input type="hidden" name="filter_order" value="<?php echo $pageInfo->filter->order->value; ?>"/>
		<input type="hidden" name="filter_order_Dir" value="<?php echo $pageInfo->filter->order->dir; ?>"/>
		<?php
		echo $tabs->endPanel();
		echo $tabs->startPanel(JText::_('HIKA_CATEGORIES'), 'hikashop_auto');
		$type = JFactory::getApplication()->input->getString('type');
		$this->db->setQuery('SELECT * FROM '.acymailing_table('hikashop_category', false).' WHERE category_type=\'product\' ORDER BY `category_ordering` ASC');
		$categories = $this->db->loadObjectList('category_id');
		$this->cats = array();
		foreach($categories as $oneCat){
			$this->cats[$oneCat->category_parent_id][] = $oneCat;
		}
		$catClass = hikashop_get('class.category');
		$root = $catClass->getRoot();
		?>
		<script language="javascript" type="text/javascript">
			<!--
			var selectedCat = new Array();
			function applyAutoProduct(catid, rowClass){
				if(catid == 'all'){
					if(window.document.getElementById('product_cat' + catid).className == 'selectedrow'){
						window.document.getElementById('product_catall').className = rowClass;
					}else{
						window.document.getElementById('product_cat' + catid).className = 'selectedrow';
						for(var key in selectedCat){
							if(!isNaN(key)){
								window.document.getElementById('product_cat' + key).className = rowClass;
								delete selectedCat[key];
							}
						}
					}
				}else{
					window.document.getElementById('product_catall').className = 'row0';
					if(selectedCat[catid]){
						window.document.getElementById('product_cat' + catid).className = rowClass;
						delete selectedCat[catid];
					}else{
						window.document.getElementById('product_cat' + catid).className = 'selectedrow';
						selectedCat[catid] = 'product';
					}
				}
				updateTagAuto();
			}

			function updateTagAuto(){
				var tag = '{hikashop_auto_product:';
				for(var icat in selectedCat){
					if(selectedCat[icat] == 'product'){
						tag += icat + '-';
					}
				}
				for(var i = 0; i < document.adminForm.contenttypeauto.length; i++){
					if(document.adminForm.contenttypeauto[i].checked){
						tag += '| type:' + document.adminForm.contenttypeauto[i].value;
					}
				}


				if(document.adminForm.min_article && document.adminForm.min_article.value && document.adminForm.min_article.value != 0){
					tag += '| min:' + document.adminForm.min_article.value;
				}
				if(document.adminForm.max_article.value && document.adminForm.max_article.value != 0){
					tag += '| max:' + document.adminForm.max_article.value;
				}
				if(document.adminForm.contentorder.value){
					tag += "| order:" + document.adminForm.contentorder.value + "," + document.adminForm.contentorderdir.value;
				}
				if(document.adminForm.contentfilter && document.adminForm.contentfilter.value){
					tag += '| filter:' + document.adminForm.contentfilter.value;
				}
				if(window.document.getElementById('jflang_auto') && window.document.getElementById('jflang_auto').value != ''){
					tag += '| lang:' + window.document.getElementById('jflang_auto').value;
				}
				for(i = 0; i < document.adminForm.pricedisplayauto.length; i++){
					if(document.adminForm.pricedisplayauto[i].checked){
						tag += '| price:' + document.adminForm.pricedisplayauto[i].value;
					}
				}

				tag += '}';
				setTag(tag);
			}
			//-->
		</script>
		<div class="onelineblockoptions">
			<table width="100%" class="acymailing_table">
				<tr>
					<td>
						<?php echo JText::_('DISPLAY'); ?>
					</td>
					<td colspan="2">
						<?php echo JHTML::_('acyselect.radiolist', $contentType, 'contenttypeauto', 'size="1" onclick="updateTagAuto();"', 'value', 'text', 'full'); ?>
					</td>
					<td>
						<?php $jflanguages = acymailing_get('type.jflanguages');
						if(!empty($jflanguages->values)){
							$jflanguages->id = 'jflang_auto';
							$jflanguages->onclick = 'onchange="updateTagAuto();"';
							echo $jflanguages->display('language');
						} ?>
					</td>
				</tr>
				<tr>
					<td>
						<?php echo JText::_('PRICE'); ?>
					</td>
					<td colspan="3">
						<?php echo JHTML::_('acyselect.radiolist', $priceDisplay, 'pricedisplayauto', 'size="1" onclick="updateTagAuto();"', 'value', 'text', 'full'); ?>
					</td>
				</tr>
				<tr>
					<td>
						<label for="max_article"><?php echo JText::_('MAX_ARTICLE'); ?></label>
					</td>
					<td>
						<input type="text" name="max_article" id="max_article" style="width:50px" value="" onchange="updateTagAuto();"/>
					</td>
					<td>
						<?php echo JText::_('ACY_ORDER'); ?>
					</td>
					<td>
						<?php
						$values = array('product_id' => 'ACY_ID', 'product_created' => 'CREATED_DATE', 'product_modified' => 'MODIFIED_DATE', 'product_name' => 'HIKA_TITLE');
						echo $this->acypluginsHelper->getOrderingField($values, 'product_id', 'DESC');
						?>
					</td>
				</tr>
				<?php if($type == 'autonews'){ ?>
					<tr>
						<td>
							<label for="min_article"><?php echo JText::_('MIN_ARTICLE'); ?></label>
						</td>
						<td>
							<input type="text" name="min_article" id="min_article" style="width:50px" value="1" onchange="updateTagAuto();"/>
						</td>
						<td>
							<?php echo JText::_('FILTER'); ?>
						</td>
						<td>
							<?php $filter = acymailing_get('type.contentfilter');
							$filter->onclick = 'updateTagAuto();';
							echo $filter->display('contentfilter', 'created', false); ?>
						</td>
					</tr>
				<?php } ?>
			</table>
		</div>
		<div class="onelineblockoptions">
			<table class="acymailing_table" cellpadding="1" width="100%">
				<tr id="product_catall" class="<?php echo "row0"; ?>" onclick="applyAutoProduct('all','<?php echo "row$k" ?>');" style="cursor:pointer;">
					<td class="acytdcheckbox"></td>
					<td><?php echo JText::_('ACY_ALL'); ?></td>
				</tr>
				<?php $k = 0;
				echo $this->displayChildren($root, $k); ?>
			</table>
		</div>
		<?php
		echo $tabs->endPanel();
		echo $tabs->startPanel(JText::_('HIKA_ABANDONED_CART'), 'hikashop_abandonedcart');
		?>
		<script language="javascript" type="text/javascript">
			<!--
			function updateTagCart(){
				var tag = '{hikashop_abandonedcart:';
				for(var icat in selectedCat){
					if(selectedCat[icat] == 'product'){
						tag += icat + '-';
					}
				}
				for(var i = 0; i < document.adminForm.contenttypecart.length; i++){
					if(document.adminForm.contenttypecart[i].checked){
						tag += '| type:' + document.adminForm.contenttypecart[i].value;
					}
				}
				if(window.document.getElementById('jflang_cart') && window.document.getElementById('jflang_cart').value != ''){
					tag += '| lang:' + window.document.getElementById('jflang_cart').value;
				}
				if(window.document.getElementById('nbdayscart') && window.document.getElementById('nbdayscart').value != ''){
					tag += '| nbdayscart:' + window.document.getElementById('nbdayscart').value;
				}
				if(document.adminForm.paymentcart.value != ''){
					tag += '| paymentcart:' + document.adminForm.paymentcart.value;
				}
				tag += '}';
				setTag(tag);
			}
			//-->
		</script>
		<div class="onelineblockoptions">
			<table width="100%" class="acymailing_table">
				<tr>
					<td>
						<?php echo JText::_('DISPLAY'); ?>
					</td>
					<td colspan="2">
						<?php echo JHTML::_('acyselect.radiolist', $contentType, 'contenttypecart', 'size="1" onclick="updateTagCart();"', 'value', 'text', 'full'); ?>
					</td>
					<td>
						<?php $jflanguages = acymailing_get('type.jflanguages');
						if(!empty($jflanguages->values)){
							$jflanguages->id = 'jflang_cart';
							$jflanguages->onclick = 'onchange="updateTagCart();"';
							echo $jflanguages->display('language');
						} ?>
					</td>
				</tr>
				<tr>
					<td>
						<?php echo JText::sprintf('DAYS_AFTER_ORDERING', '<input class="inputbox" type="text" id="nbdayscart" name="nbdayscart" style="width:50px" value="1" onchange="updateTagCart()" />'); ?>
					</td>
					<td colspan="3">
						<?php
						$payment = hikashop_get('type.payment');
						$payment->extra = 'onchange="updateTagCart()"';
						echo $payment->display("paymentcart", '', false); ?>
					</td>
				</tr>
			</table>
		</div>
		<?php
		echo $tabs->endPanel();
		echo $tabs->startPanel(JText::_('COUPONS'), 'hikashop_coupon');
		$currency = hikashop_get('type.currency');
		$config =& hikashop_config();
		?>
		<script language="javascript" type="text/javascript">
			<!--
			function updateTag(){
				var tagname = document.adminForm.minimum_order.value + '|';
				tagname += document.adminForm.quota.value + '|';
				tagname += document.adminForm.start.value + '|';
				tagname += document.adminForm.end.value + '|';
				tagname += document.adminForm.percent_amount.value + '|';
				tagname += document.adminForm.flat_amount.value + '|';
				tagname += document.adminForm.currency_id.value + '|';
				tagname += document.adminForm.coupon_code.value + '|';
				tagname += document.adminForm.product_id.value + '|';
				tagname += document.adminForm.tax_id.value + '|';
				tagname += document.adminForm.validity_period.value;
				setTag('{hikashop_coupon:' + tagname + '}');
			}
			//-->
		</script>
		<div class="onelineblockoptions">
			<table style="width:100%;">
				<tr>
					<td style="width:50%;">
						<table class="acymailing_table" style="margin:auto">
							<tr>
								<td class="acykey">
									<label for="coupon_code">
										<?php echo JText::_('DISCOUNT_CODE'); ?>
									</label>
								</td>
								<td>
									<input type="text" id="coupon_code" onchange="updateTag();" value="[name][key][value]"/>
								</td>
							</tr>
							<tr>
								<td class="acykey">
									<label for="flat_amount">
										<?php echo JText::_('DISCOUNT_FLAT_AMOUNT'); ?>
									</label>
								</td>
								<td>
									<input style="width:50px;" type="text" id="flat_amount" onchange="updateTag();" value="0"/><?php echo $currency->display('currency_id', (int)$config->get('main_currency'), ' style="width:100px;" '); ?>
								</td>
							</tr>
							<tr>
								<td class="acykey">
									<label for="percent_amount">
										<?php echo JText::_('DISCOUNT_PERCENT_AMOUNT'); ?>
									</label>
								</td>
								<td>
									<input style="width:50px;" type="text" id="percent_amount" onchange="updateTag();" value="0"/>
								</td>
							</tr>
						</table>
					</td>
					<td>
						<table class="acymailing_table" style="margin:auto">
							<tr>
								<td class="acykey">
									<label for="start">
										<?php echo JText::_('DISCOUNT_START_DATE'); ?>
									</label>
								</td>
								<td>
									<?php echo JHTML::_('calendar', '', 'start', 'start', '%Y-%m-%d %H:%M', array('style' => 'width:100px', 'onchange' => 'updateTag();')); ?>
								</td>
							</tr>
							<tr>
								<td class="acykey">
									<label for="end">
										<?php echo JText::_('DISCOUNT_END_DATE'); ?>
									</label>
								</td>
								<td>
									<?php echo JHTML::_('calendar', '', 'end', 'end', '%Y-%m-%d %H:%M', array('style' => 'width:100px', 'onchange' => 'updateTag();')); ?>
								</td>
							</tr>
							<tr>
								<td class="acykey">
									<label for="validity_period">
										<?php echo JText::_('VALIDITY_PERIOD'); ?>
									</label>
								</td>
								<td>
									<input style="width:60px;" type="text" id="validity_period" onchange="updateTag();" value="" placeholder="<?php echo JText::_('HIKA_UNLIMITED'); ?>"/> <?php echo JText::_('DAYS'); ?>
								</td>
							</tr>
							<?php if(hikashop_level(1)){ ?>
								<tr>
									<td class="acykey">
										<label for="minimum_order">
											<?php echo JText::_('MINIMUM_ORDER_VALUE'); ?>
										</label>
									</td>
									<td>
										<input style="width:50px;" type="text" id="minimum_order" value="0" onchange="updateTag();"/>
									</td>
								</tr>
								<tr>
									<td class="acykey">
										<label for="quota">
											<?php echo JText::_('DISCOUNT_QUOTA'); ?>
										</label>
									</td>
									<td>
										<input style="width:50px;" type="text" id="quota" value="" onchange="updateTag();"/>
									</td>
								</tr>
								<tr>
									<td class="acykey">
										<label for="product_id">
											<?php echo JText::_('PRODUCT'); ?>
										</label>
									</td>
									<td>
										<?php
										$this->db->setQuery("SELECT `product_id`, CONCAT(product_name,' ( ',product_code,' )') as `title` FROM #__hikashop_product WHERE `product_type`='main' AND `product_published`=1  ORDER BY `product_code` ASC");
										$results = $this->db->loadObjectList();
										$obj = new stdClass();
										$obj->product_id = '';
										$obj->title = JText::_('HIKA_NONE');
										array_unshift($results, $obj);
										echo JHTML::_('select.genericlist', $results, 'product_id', 'size="1" style="width:150px;"  onchange="updateTag();"', 'product_id', 'title', '');
										?>
									</td>
								</tr>
								<tr>
									<td class="acykey">
										<label for="tax_id">
											<?php echo JText::_('TAXATION_CATEGORY'); ?>
										</label>
									</td>
									<td>
										<?php
										$categoryType = hikashop_get('type.categorysub');
										$categoryType->type = 'tax';
										$categoryType->field = 'category_id';
										echo $categoryType->display('', '', 'onchange="updateTag();"', true, 'tax_id');
										?>
									</td>
								</tr>
							<?php }else{ ?>
								<tr>
									<td>
										<input type="hidden" id="minimum_order" value="0"/>
										<input type="hidden" id="quota" value=""/>
									</td>
								</tr>
							<?php } ?>
						</table>
					</td>
				</tr>
			</table>
		</div>
		<?php
		echo $tabs->endPanel();
		echo $tabs->endPane();
	}

	function displayChildren($parentid, &$k, $level = 0){
		if(empty($this->cats[$parentid])) return;
		foreach($this->cats[$parentid] as $oneCat){
			$k = 1 - $k;
			echo '<tr id="product_cat'.$oneCat->category_id.'" class="row'.$k.'" onclick="applyAutoProduct('.$oneCat->category_id.',\'row'.$k.'\');" style="cursor:pointer;"><td class="acytdcheckbox"></td><td>';
			echo str_repeat('- - ', $level).$oneCat->category_name.'</td></tr>';
			$this->displayChildren($oneCat->category_id, $k, $level + 1);
		}
	}

	function acymailing_replacetags(&$email){
		if(!$this->loadAcymailing()) return;
		$this->_replaceAuto($email);
		$this->_replaceProducts($email);
	}

	function acymailing_replaceusertags(&$email, &$user, $send = true){
		if(!$this->loadAcymailing()) return;

		$this->_replaceCoupons($email, $user, $send);
		$this->_replaceAbandonedCarts($email, $user);
	}

	function _replaceAbandonedCarts(&$email, &$user, $send = true){
		$tags = $this->acypluginsHelper->extractTags($email, 'hikashop_abandonedcart');
		if(empty($tags)) return;

		$tagsReplaced = array();
		foreach($tags as $i => $oneTag){
			if(isset($tagsReplaced[$i])) continue;
			$tagsReplaced[$i] = $this->_replaceAbandonedCart($oneTag, $user);
		}

		$this->acypluginsHelper->replaceTags($email, $tagsReplaced, true);

		$this->_replaceProducts($email);
	}

	function _replaceAbandonedCart($oneTag, $user){
		if(empty($user->userid)) return '';

		$delay = 0;
		if(!empty($oneTag->nbdayscart)){
			$delay = ($oneTag->nbdayscart * 86400);
		}

		$senddate = (time() - $delay);

		$config =& hikashop_config();
		$createdstatus = $config->get('order_created_status', 'created');

		$myquery = 'SELECT c.product_id
					FROM #__hikashop_order AS a
					LEFT JOIN #__hikashop_order AS b
						ON a.order_user_id = b.order_user_id
						AND b.order_id > a.order_id
					JOIN #__hikashop_order_product AS c
						ON a.order_id = c.order_id
					JOIN #__hikashop_user AS hikauser
						ON a.order_user_id = hikauser.user_id ';

		if(!empty($oneTag->paymentcart)) $myquery .= 'JOIN #__hikashop_payment AS payment
														ON payment.payment_type = a.order_payment_method
														AND payment.payment_id = '.intval($oneTag->paymentcart);

		$myquery .= ' WHERE hikauser.user_cms_id = '.intval($user->userid).' AND a.order_status = '.$this->db->Quote($createdstatus).' AND b.order_id IS NULL ';
		$myquery .= ' AND FROM_UNIXTIME(a.order_created,"%Y %d %m") = FROM_UNIXTIME('.$senddate.',"%Y %d %m")';
		$this->db->setQuery($myquery);

		$Products = acymailing_loadResultArray($this->db);
		if(empty($Products)) return '';

		$arrayElements = array();
		foreach($Products as $oneProductId){
			$args = array();
			$args[] = 'hikashop_product:'.$oneProductId;
			if(!empty($oneTag->type)) $args[] = 'type:'.$oneTag->type;
			if(!empty($oneTag->lang)) $args[] = 'lang:'.$oneTag->lang;
			$arrayElements[] = '{'.implode('|', $args).'}';
		}
		$stringTag = $this->acypluginsHelper->getFormattedResult($arrayElements, $oneTag);
		return $stringTag;
	}

	function _replaceCoupons(&$email, &$user, $send = true){
		$results = array();
		$match = '#{hikashop_coupon:(.*)}#Ui';
		$variables = array('subject', 'body', 'altbody');
		$found = false;
		foreach($variables as $var){
			if(empty($email->$var)) continue;
			$found = preg_match_all($match, $email->$var, $results[$var]) || $found;
			if(empty($results[$var][0])) unset($results[$var]);
		}
		if(!$found) return;
		$tags = array();
		foreach($results as $var => $allresults){
			foreach($allresults[0] as $i => $oneTag){
				if(isset($tags[$oneTag])) continue;
				if(!$send || empty($user->subid)){
					$tags[$oneTag] = '<i>'.JText::_('ACY_CHECK_EMAIL_COUPON').'</i>';
				}else{
					$tags[$oneTag] = $this->generateCoupon($allresults, $i, $user);
				}
			}
		}

		foreach(array_keys($results) as $var){
			$email->$var = str_replace(array_keys($tags), $tags, $email->$var);
		}
	}

	function _replaceAuto(&$email){
		$this->acymailing_generateautonews($email);
		if(empty($this->tags)) return;
		$this->acypluginsHelper->replaceTags($email, $this->tags, true);
	}

	function acymailing_generateautonews(&$email){
		$tags = $this->acypluginsHelper->extractTags($email, 'hikashop_auto_product');
		$return = new stdClass();
		$return->status = true;
		$return->message = '';
		$this->tags = array();

		if(empty($tags)) return $return;

		foreach($tags as $oneTag => $parameter){
			if(isset($this->tags[$oneTag])) continue;
			$allcats = explode('-', $parameter->id);
			$selectedArea = array();
			foreach($allcats as $oneCat){
				if(empty($oneCat)) continue;
				$selectedArea[] = intval($oneCat);
			}

			$query = 'SELECT DISTINCT b.`product_id` FROM '.acymailing_table('hikashop_product_category', false).' as a LEFT JOIN '.acymailing_table('hikashop_product', false).' as b ON a.product_id = b.product_id';
			$where = array();
			if($this->params->get('stock', 0) == '1') $where[] = '(b.product_quantity = -1 || b.product_quantity > 0)';
			if(!empty($selectedArea)){
				$where[] = 'a.category_id IN ('.implode(',', $selectedArea).')';
			}
			$where[] = "b.`product_published` = 1";
			if(!empty($parameter->filter) AND !empty($email->params['lastgenerateddate'])){
				$condition = 'b.`product_created` >\''.$email->params['lastgenerateddate'].'\'';
				if($parameter->filter == 'modify'){
					$condition .= ' OR b.`product_modified` >\''.$email->params['lastgenerateddate'].'\'';
				}
				$where[] = $condition;
			}
			$query .= ' WHERE ('.implode(') AND (', $where).')';
			if(!empty($parameter->order)){
				$ordering = explode(',', $parameter->order);
				if($ordering[0] == 'rand'){
					$query .= ' ORDER BY rand()';
				}else{
					$query .= ' ORDER BY b.`'.acymailing_secureField(trim($ordering[0])).'` '.acymailing_secureField(trim($ordering[1]));
				}
			}
			if(!empty($parameter->max)) $query .= ' LIMIT '.(int)$parameter->max;
			$this->db->setQuery($query);
			$allArticles = acymailing_loadResultArray($this->db);

			if(!empty($parameter->min) && count($allArticles) < $parameter->min){
				$return->status = false;
				$return->message = 'Not enough products for the tag '.$oneTag.' : '.count($allArticles).' / '.$parameter->min;
			}

			$stringTag = '';
			if(!empty($allArticles)){
				if(file_exists(ACYMAILING_TEMPLATE.'plugins'.DS.'hikashop_auto_product.php')){
					ob_start();
					require(ACYMAILING_TEMPLATE.'plugins'.DS.'hikashop_auto_product.php');
					$stringTag = ob_get_clean();
				}else{
					$arrayElements = array();
					foreach($allArticles as $oneArticleId){
						$args = array();
						$args[] = 'hikashop_product:'.$oneArticleId;
						if(!empty($parameter->type)) $args[] = 'type:'.$parameter->type;
						if(!empty($parameter->lang)) $args[] = 'lang:'.$parameter->lang;
						if(!empty($parameter->price)) $args[] = 'price:'.$parameter->price;
						$arrayElements[] = '{'.implode('|', $args).'}';
					}
					$stringTag = $this->acypluginsHelper->getFormattedResult($arrayElements, $parameter);

				}
			}
			$this->tags[$oneTag] = $stringTag;
		}
		return $return;
	}

	private function _replaceProducts(&$email){

		$tags = $this->acypluginsHelper->extractTags($email, 'hikashop_product');
		if(empty($tags)) return;

		$this->readmore = empty($email->template->readmore) ? JText::_('JOOMEXT_READ_MORE') : '<img src="'.ACYMAILING_LIVE.$email->template->readmore.'" alt="'.JText::_('JOOMEXT_READ_MORE', true).'" />';

		$tagsReplaced = array();
		foreach($tags as $i => $oneTag){
			if(isset($tagsReplaced[$i])) continue;
			$tagsReplaced[$i] = $this->_replaceProduct($oneTag, $email);
		}

		$this->acypluginsHelper->replaceTags($email, $tagsReplaced, true);
	}

	function _replaceProduct($tag, &$email){
		if(empty($tag->lang) && !empty($email->language)) $tag->lang = $email->language;
		$this->db->setQuery('SELECT b.*,a.*
						FROM '.acymailing_table('hikashop_product', false).' as a
						LEFT JOIN '.acymailing_table('hikashop_file', false).' as b ON a.product_id=b.file_ref_id AND file_type=\'product\'
						WHERE a.product_id = '.$tag->id.'
						ORDER BY b.file_ordering ASC, b.file_id ASC
						LIMIT 1');
		$product = $this->db->loadObject();
		if(empty($product)){
			$app = JFactory::getApplication();
			$j = new JVersion(); $version=substr($j->getShortVersion(), 0,1);
			if($version < 4) {
				if($app->isAdmin()) $app->enqueueMessage('The product "'.$tag->id.'" could not be loaded', 'notice');
			}else{
				if($app->isClient('administrator')) $app->enqueueMessage('The product "'.$tag->id.'" could not be loaded', 'notice');
			}
			return '';
		}

		if($product->product_type == 'variant'){
			$this->db->setQuery('SELECT * FROM '.hikashop_table('variant').' AS a LEFT JOIN '.hikashop_table('characteristic').' AS b ON a.variant_characteristic_id=b.characteristic_id WHERE a.variant_product_id='.(int)$tag->id.' ORDER BY a.ordering');
			$product->characteristics = $this->db->loadObjectList();
			$productClass = hikashop_get('class.product');
			$this->db->setQuery('SELECT b.*,a.*
							FROM '.acymailing_table('hikashop_product', false).' as a
							LEFT JOIN '.acymailing_table('hikashop_file', false).' as b ON a.product_id=b.file_ref_id AND file_type=\'product\'
							WHERE a.product_id = '.(int)$product->product_parent_id.'
							ORDER BY b.file_ordering ASC, b.file_id ASC
							LIMIT 1');
			$parentProduct = $this->db->loadObject();
			$productClass->checkVariant($product, $parentProduct);
		}

		$translationHelper = hikashop_get('helper.translation');
		if($translationHelper->isMulti(true, false)) $this->acypluginsHelper->translateItem($product, $tag, 'hikashop_product');

		$varFields = array();
		foreach($product as $fieldName => $oneField){
			$varFields['{'.$fieldName.'}'] = $oneField;
		}

		$tag->itemid = intval($this->params->get('itemid'));
		$config =& hikashop_config();
		$currencyClass = hikashop_get('class.currency');
		$main_currency = $currency_id = (int)$config->get('main_currency', 1);
		$zone_id = explode(',', $config->get('main_tax_zone', 0));

		$zone_id = count($zone_id) ? array_shift($zone_id) : 0;

		$ids = array($product->product_id);
		$discount_before_tax = (int)$config->get('discount_before_tax', 0);
		$currencyClass->getPrices($product, $ids, $currency_id, $main_currency, $zone_id, $discount_before_tax);
		$finalPrice = '';
		if(empty($tag->price) || $tag->price == 'full'){
			if($this->params->get('vat', 1)){
				$finalPrice = @$currencyClass->format($product->prices[0]->price_value_with_tax, $product->prices[0]->price_currency_id);
			}else{
				$finalPrice = $currencyClass->format($product->prices[0]->price_value, $product->prices[0]->price_currency_id);
			}
			if(!empty($product->discount)){
				if($this->params->get('vat', 1)){
					$finalPrice = '<strike>'.$currencyClass->format($product->prices[0]->price_value_without_discount_with_tax, $product->prices[0]->price_currency_id).'</strike> '.$finalPrice;
				}else{
					$finalPrice = '<strike>'.$currencyClass->format($product->prices[0]->price_value_without_discount, $product->prices[0]->price_currency_id).'</strike> '.$finalPrice;
				}
			}
		}elseif($tag->price == 'no_discount'){
			if($this->params->get('vat', 1)){
				$finalPrice = $currencyClass->format($product->prices[0]->price_value_without_discount_with_tax, $product->prices[0]->price_currency_id);
			}else{
				$finalPrice = $currencyClass->format($product->prices[0]->price_value_without_discount, $product->prices[0]->price_currency_id);
			}
		}
		$varFields['{finalPrice}'] = $finalPrice;

		if(empty($tag->type) || $tag->type == 'full'){
			$description = $product->product_description;
		}else{
			$pos = strpos($product->product_description, '<hr id="system-readmore"');
			if($pos !== false){
				$description = substr($product->product_description, 0, $pos);
			}else{
				$description = substr($product->product_description, 0, 100).'...';
			}
		}

		$link = 'index.php?option=com_hikashop&ctrl=product&task=show&cid='.$product->product_id;
		if(!empty($tag->lang)) $link .= '&lang='.substr($tag->lang, 0, strpos($tag->lang, ','));
		if(!empty($tag->itemid)) $link .= '&Itemid='.$tag->itemid;
		if(!empty($product->product_canonical)) $link = $product->product_canonical;
		$link = acymailing_frontendLink($link);
		$varFields['{link}'] = $link;

		$image = hikashop_get('helper.image');
		if(!empty($product->file_path)) {
			$img = $image->getThumbnail($product->file_path, null);
			if($img->success)
				$varFields['{pictHTML}'] = $img->url;
			else
				$varFields['{pictHTML}'] = $image->display($product->file_path, false, $product->product_name);
		}

		if(file_exists(ACYMAILING_MEDIA.'plugins'.DS.'hikashop_product.php')){
			ob_start();
			require(ACYMAILING_MEDIA.'plugins'.DS.'hikashop_product.php');
			$result = ob_get_clean();
			$result = str_replace(array_keys($varFields), $varFields, $result);
			return $result;
		}

		$result = '';
		$astyle = '';

		if(empty($tag->type) || $tag->type != 'title'){
			$result .= '<div class="acymailing_product">';
			$astyle = 'style="text-decoration:none;" name="product-'.$product->product_id.'"';
		}

		$result .= '<a '.$astyle.' target="_blank" href="'.$link.'">';
		if(empty($tag->type) || $tag->type != 'title') $result .= '<h2 class="acymailing_title">';
		$result .= $product->product_name;
		if(!empty($finalPrice)) $result .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$finalPrice;
		if(empty($tag->type) || $tag->type != 'title') $result .= '</h2>';
		$result .= '</a>';
		if(empty($tag->type) || $tag->type != 'title'){
			if(!empty($product->file_path)){
				$config =& hikashop_config();
				$uploadFolder = ltrim(JPath::clean(html_entity_decode($config->get('uploadfolder'))), DS);
				$uploadFolder = rtrim($uploadFolder, DS).DS;
				$image->uploadFolder_url = str_replace(DS, '/', $uploadFolder);
				$image->uploadFolder_url = ACYMAILING_LIVE.$image->uploadFolder_url;
				$pictureHTML = $image->display($product->file_path, false, $product->product_name, '', '', $config->get('thumbnail_x', 100), $config->get('thumbnail_y', 100));
				$pictureHTML = '<a target="_blank" style="text-decoration:none;border:0" href="'.$link.'" >'.$pictureHTML.'</a>';
				$result .= '<table class="acymailing_content"><tr><td valign="top" style="padding-right:5px">'.$pictureHTML.'</td><td>'.$description.'</td></tr></table>';
			}else{
				$result .= $description;
			}
		}
		if(empty($tag->type) || $tag->type != 'title') $result .= '</div>';

		$result = $this->acypluginsHelper->removeJS($result);

		return $result;
	}

	function generateCoupon(&$allresults, $i, &$user){

		list($minimum_order, $quota, $start, $end, $percent_amount, $flat_amount, $currency_id, $code, $product_id, $tax_id, $validity_period) = explode('|', $allresults[1][$i]);
		jimport('joomla.user.helper');
		$key = JUserHelper::genrandompassword(5);

		if(!hikashop_level(1)){
			$minimum_order = 0;
			$quota = '';
			$product_id = '';
		}

		if($percent_amount > 0){
			$value = $percent_amount;
		}else{
			$value = $flat_amount;
		}
		$value = str_replace(',', '.', $value);
		if($start){
			$start = hikashop_getTime($start);
		}
		if($end){
			$end = hikashop_getTime($end);
		}
		if((int)$validity_period > 0) {
			$end_period = time() + (int)$validity_period * 86400;
			if($end_period > $end)
				$end = $end_period;
		}

		$clean_name = strtoupper($user->name);
		$space = strpos($clean_name, ' ');
		if(!empty($space)){
			$clean_name = substr($clean_name, 0, $space);
		}

		$code = str_replace(array('[name]', '[clean_name]', '[subid]', '[email]', '[key]', '[flat]', '[percent]', '[value]', '[prodid]'), array($user->name, $clean_name, $user->subid, $user->email, $key, $flat_amount, $percent_amount, $value, $product_id), $code);
		$this->db->setQuery('INSERT IGNORE INTO '.acymailing_table('hikashop_discount', false).'(
		`discount_code`,
		`discount_percent_amount`,
		`discount_flat_amount`,
		`discount_type`,
		`discount_start`,
		`discount_end`,
		`discount_minimum_order`,
		`discount_quota`,
		`discount_currency_id`,
		`discount_product_id`,
		`discount_tax_id`,
		`discount_published`
		) VALUES ('.$this->db->Quote($code).','.$this->db->Quote($percent_amount).','.$this->db->Quote($flat_amount).',\'coupon\','.$this->db->Quote($start).','.$this->db->Quote($end).','.$this->db->Quote($minimum_order).','.$this->db->Quote($quota).','.$this->db->Quote(hikashop_getCurrency()).','.$this->db->Quote($product_id).','.$this->db->Quote($tax_id).',
		1)');
		$this->db->execute();

		return $code;
	}

	function onAcyDisplayFilters(&$type, $context = 'massactions'){
		if(!$this->loadAcymailing()) return '';
		if($this->params->get('displayfilter_'.$context, true) == false) return '';

		$this->db->setQuery("SELECT `product_id` as value, CONCAT(`product_name`,' ( ',`product_code`,' ) ') as text FROM ".acymailing_table('hikashop_product', false)." ORDER BY `product_code` ASC LIMIT 1000");
		$allProducts = $this->db->loadObjectList();
		if(!empty($allProducts)){
			$selectOne = new stdClass;
			$selectOne->value = 0;
			$selectOne->text = JText::_('ACY_ONE_PRODUCT');
			array_unshift($allProducts, $selectOne);
		}
		$hikaBuy = array();
		$hikaBuy[] = JHTML::_('select.option', '1', JText::_('ACY_BOUGHT'));
		$hikaBuy[] = JHTML::_('select.option', '0', JText::_('ACY_DIDNOTBOUGHT'));

		$hikaGroupsParams = acymailing_get('type.operatorsin');
		$hikaGroupsParams->js = 'onchange="countresults(__num__)"';
		$operators = acymailing_get('type.operators');
		$operators->extra = 'onchange="countresults(__num__)"';

		$fields = acymailing_getColumns('#__hikashop_user');

		$hikaFields = array();
		if(!empty($fields)){
			foreach($fields as $oneField => $fieldType){
				$hikaFields[] = JHTML::_('select.option', $oneField, $oneField);
			}
		}

		$return = '';
		$return .= '<div id="filter__num__hikaallorders">';
		$return .= $hikaGroupsParams->display("filter[__num__][hikaallorders][type]").' ';
		$category = hikashop_get('type.categorysub');
		$category->type = 'status';
		$return .= $category->display("filter[__num__][hikaallorders][status]", '', 'size="1" onchange="countresults(__num__)" ', false);
		$payment = hikashop_get('type.payment');
		$payment->extra = 'onchange="countresults(__num__)"';
		$return .= $payment->display("filter[__num__][hikaallorders][payment]", '', false);
		$return .= '<br/> <input onclick="displayDatePicker(this,event)" name="filter[__num__][hikaallorders][cdateinf]" type="text" onchange="countresults(__num__)" /> < '.JText::_('CREATED_DATE').' < <input onclick="displayDatePicker(this,event)" type="text" name="filter[__num__][hikaallorders][cdatesup]" onchange="countresults(__num__)"  />';
		$return .= '<br/> <input onclick="displayDatePicker(this,event)" name="filter[__num__][hikaallorders][mdateinf]" type="text" onchange="countresults(__num__)" /> < '.JText::_('MODIFIED_DATE').' < <input onclick="displayDatePicker(this,event)" type="text" name="filter[__num__][hikaallorders][mdatesup]" onchange="countresults(__num__)" />';
		$return .= '<br/> <input onclick="displayDatePicker(this,event)" name="filter[__num__][hikaallorders][idateinf]" type="text" onchange="countresults(__num__)" /> < '.JText::_('INVOICE_DATE').' < <input onclick="displayDatePicker(this,event)" type="text" name="filter[__num__][hikaallorders][idatesup]" onchange="countresults(__num__)" />';
		$return .= '</div>';
		$type['hikaallorders'] = 'HikaShop '.JText::_('ORDERS');

		if(!empty($allProducts)){

			$dateFilters = array();
			$dateFilters[] = JHTML::_('select.option', 'order_created', JText::_('CREATED_DATE'));
			$dateFilters[] = JHTML::_('select.option', 'order_modified', JText::_('MODIFIED_DATE'));
			$dateFilters[] = JHTML::_('select.option', 'order_invoice_created', JText::_('INVOICE_DATE'));


			$return .= '<div id="filter__num__hikaorder">'.JHTML::_('select.genericlist', $hikaBuy, "filter[__num__][hikaorder][type]", 'class="custom-select" size="1" onchange="countresults(__num__)" ', 'value', 'text').' ';
			$return .= JHTML::_('select.genericlist', $allProducts, "filter[__num__][hikaorder][product]", 'class="custom-select" style="max-width:200px" size="1" onchange="countresults(__num__)" ', 'value', 'text');

			$this->db->setQuery('SELECT `category_id` AS value, `category_name` AS text FROM '.acymailing_table('hikashop_category', false).' WHERE `category_type` = "product" ORDER BY `category_name` ASC LIMIT 1000');
			$allCats = $this->db->loadObjectList();
			if(!empty($allCats)){
				$selectOne = new stdClass;
				$selectOne->value = 0;
				$selectOne->text = JText::_('ACY_ANY_CATEGORY');
				array_unshift($allCats, $selectOne);
			}

			$return .= ' '.JHTML::_('select.genericlist', $allCats, "filter[__num__][hikaorder][cat]", 'class="custom-select" style="max-width:200px" size="1" onchange="countresults(__num__)" ', 'value', 'text');
			$return .= '<br/> <input onclick="displayDatePicker(this,event)" type="text" name="filter[__num__][hikaorder][creationdateinf]" onchange="countresults(__num__)" /> < '.JHTML::_('select.genericlist', $dateFilters, "filter[__num__][hikaorder][datefield]", 'class="custom-select" size="1" onchange="countresults(__num__)" ', 'value', 'text').' < <input onclick="displayDatePicker(this,event)" type="text" name="filter[__num__][hikaorder][creationdatesup]" onchange="countresults(__num__)" />';
			$return .= '</div>';
			$type['hikaorder'] = 'HikaShop '.JText::_('CUSTOMERS');
		}

		if(!empty($hikaFields)){
			$return .= '<div id="filter__num__hikafield">'.JHTML::_('select.genericlist', $hikaFields, "filter[__num__][hikafield][map]", 'class="custom-select" onchange="countresults(__num__)" size="1"', 'value', 'text');
			$return .= ' '.$operators->display("filter[__num__][hikafield][operator]").' <input class="inputbox" type="text" name="filter[__num__][hikafield][value]" size="50" value="" onchange="countresults(__num__)" />';
			$return .= '</div>';
			$type['hikafield'] = 'HikaShop '.JText::_('FIELD');
		}

		$this->db->setQuery("SELECT `zone_namekey` AS value, CONCAT(`zone_name`,' ( ',`zone_name_english`,' )') AS text FROM ".acymailing_table('hikashop_zone', false)." WHERE `zone_type` = 'country' ORDER BY `zone_name_english` ASC LIMIT 1000");
		$allCountries = $this->db->loadObjectList();

		$selectOne = new stdClass;
		$selectOne->value = 0;
		$selectOne->text = JText::_('COUNTRYCAPTION');
		array_unshift($allCountries, $selectOne);

		$jsOnChange = "displayCondFilter('displayStates', 'toChange__num__',__num__,'country='+this.value); ";

		$return .= '<div id="filter__num__hikaaddress">';
		$return .= $hikaGroupsParams->display("filter[__num__][hikaaddress][type]").' ';
		$return .= JHTML::_('select.genericlist', $allCountries, "filter[__num__][hikaaddress][country]", 'class="custom-select" onchange="'.$jsOnChange.'countresults(__num__)" size="1"', 'value', 'text');
		$return .= ' <span id="toChange__num__"></span>';
		$return .= '</div>';
		$type['hikaaddress'] = 'HikaShop '.JText::_('ADDRESSCAPTION');

		$return .= '<div id="filter__num__hikareminder">';
		$val = '<input class="inputbox" type="text" name="filter[__num__][hikareminder][nbdays]" style="width:50px" value="1" onchange="countresults(__num__)" />';
		$return .= JText::sprintf('DAYS_AFTER_ORDERING', $val).'<br/>';
		$payment = hikashop_get('type.payment');
		$payment->extra = 'onchange="countresults(__num__)"';
		$return .= $payment->display("filter[__num__][hikareminder][payment]", '', false);
		$return .= '</div>';
		$type['hikareminder'] = 'HikaShop Reminder';
		$acyconfig = acymailing_config();
		if(version_compare($acyconfig->get('version'), '4.9.4', '<')){
			echo 'Please update AcyMailing, the HikaShop plugin may not work properly with this version';
		}
		return $return;
	}

	function onAcyTriggerFct_displayStates(){
		$num = hikaInput::get()->getInt('num');
		$country = hikaInput::get()->getString('country');

		if(empty($country)) return '';

		$this->db->setQuery("SELECT z.`zone_namekey` AS value, CONCAT(z.`zone_name`,' ( ',z.`zone_name_english`,' )') AS text
						FROM ".acymailing_table('hikashop_zone', false)." AS z
						JOIN ".acymailing_table('hikashop_zone_link', false)." AS l ON z.zone_namekey = l.zone_child_namekey
						WHERE z.`zone_type` = 'state' AND l.zone_parent_namekey = ".$this->db->Quote($country)."
						ORDER BY z.`zone_name_english` ASC LIMIT 1000");
		$states = $this->db->loadObjectList();

		$selectOne = new stdClass;
		$selectOne->value = 0;
		$selectOne->text = JText::_(' - - - ');
		array_unshift($states, $selectOne);

		return JHTML::_('select.genericlist', $states, "filter[".$num."][hikaaddress][state]", 'onchange="countresults('.$num.')" class="inputbox" size="1"', 'value', 'text', 0, 'filter'.$num.'hikaaddressstate');
	}

	function onAcyProcessFilterCount_hikaaddress(&$query, $filter, $num){
		$this->onAcyProcessFilter_hikaaddress($query, $filter, $num);
		return JText::sprintf('SELECTED_USERS', $query->count());
	}

	function onAcyProcessFilter_hikaaddress(&$query, $filter, $num){
		if(!$this->loadAcymailing() || empty($filter['country'])) return;

		$join = '#__hikashop_user AS '.$num.'hikauser ON sub.email = '.$num.'hikauser.user_email';
		$join2 = '#__hikashop_address AS '.$num.'hikaaddress ON '.$num.'hikauser.user_id = '.$num.'hikaaddress.address_user_id ';
		if(!empty($filter['state'])){
			$join2 .= 'AND address_state = '.$query->db->Quote($filter['state']);
		}else $join2 .= 'AND address_country = '.$query->db->Quote($filter['country']);


		if($filter['type'] == 'IN'){
			$query->join[$num.'hikashopaddressuser'] = $join;
			$query->join[$num.'hikashopaddress'] = $join2;
		}else{
			$query->leftjoin[$num.'hikashopaddressuser'] = $join;
			$query->leftjoin[$num.'hikashopaddress'] = $join2;
			$query->where[$num.'hikashopaddress'] = $num.'hikaaddress.address_user_id IS NULL';
		}
	}

	function onAcyProcessFilterCount_hikaallorders(&$query, $filter, $num){
		$this->onAcyProcessFilter_hikaallorders($query, $filter, $num);
		return JText::sprintf('SELECTED_USERS', $query->count());
	}

	function onAcyProcessFilterCount_hikafield(&$query, $filter, $num){
		$this->onAcyProcessFilter_hikafield($query, $filter, $num);
		return JText::sprintf('SELECTED_USERS', $query->count());
	}

	function onAcyProcessFilterCount_hikaorder(&$query, $filter, $num){
		$this->onAcyProcessFilter_hikaorder($query, $filter, $num);
		return JText::sprintf('SELECTED_USERS', $query->count());
	}

	function onAcyProcessFilterCount_hikareminder(&$query, $filter, $num){
		$this->onAcyProcessFilter_hikareminder($query, $filter, $num);
		return JText::sprintf('SELECTED_USERS', $query->count());
	}

	function onAcyProcessFilter_hikaallorders(&$query, $filter, $num){
		if(!$this->loadAcymailing()) return;

		$lj = "`#__hikashop_user` as hikaallordersUser$num on hikaallordersUser$num.user_email = sub.`email` LEFT JOIN `#__hikashop_order` as hikaallorders$num ON hikaallorders$num.`order_user_id` = hikaallordersUser$num.user_id";
		if(!empty($filter['status'])) $lj .= " AND hikaallorders$num.`order_status` = ".$this->db->Quote($filter['status']);
		if(!empty($filter['cdateinf'])){
			$filter['cdateinf'] = acymailing_replaceDate($filter['cdateinf']);
			if(!is_numeric($filter['cdateinf'])) $filter['cdateinf'] = strtotime($filter['cdateinf']);
			$lj .= " AND hikaallorders$num.`order_created` > ".$this->db->Quote($filter['cdateinf']);
		}
		if(!empty($filter['cdatesup'])){
			$filter['cdatesup'] = acymailing_replaceDate($filter['cdatesup']);
			if(!is_numeric($filter['cdatesup'])) $filter['cdatesup'] = strtotime($filter['cdatesup']);
			$lj .= " AND hikaallorders$num.`order_created` < ".$this->db->Quote($filter['cdatesup']);
		}
		if(!empty($filter['mdateinf'])){
			$filter['mdateinf'] = acymailing_replaceDate($filter['mdateinf']);
			if(!is_numeric($filter['mdateinf'])) $filter['mdateinf'] = strtotime($filter['mdateinf']);
			$lj .= " AND hikaallorders$num.`order_modified` > ".$this->db->Quote($filter['mdateinf']);
		}
		if(!empty($filter['mdatesup'])){
			$filter['mdatesup'] = acymailing_replaceDate($filter['mdatesup']);
			if(!is_numeric($filter['mdatesup'])) $filter['mdatesup'] = strtotime($filter['mdatesup']);
			$lj .= " AND hikaallorders$num.`order_modified` < ".$this->db->Quote($filter['mdatesup']);
		}
		if(!empty($filter['idateinf'])){
			$filter['idateinf'] = acymailing_replaceDate($filter['idateinf']);
			if(!is_numeric($filter['idateinf'])) $filter['idateinf'] = strtotime($filter['idateinf']);
			$lj .= " AND hikaallorders$num.`order_invoice_created` > ".$this->db->Quote($filter['idateinf']);
		}
		if(!empty($filter['idatesup'])){
			$filter['idatesup'] = acymailing_replaceDate($filter['idatesup']);
			if(!is_numeric($filter['idatesup'])) $filter['idatesup'] = strtotime($filter['idatesup']);
			$lj .= " AND hikaallorders$num.`order_invoice_created` < ".$this->db->Quote($filter['idatesup']);
		}
		if(!empty($filter['payment'])){
			$column = 'order_payment_method';
			if(is_numeric($filter['payment'])){
				$column = 'order_payment_id';
			}
			$lj .= " AND hikaallorders$num.`".$column."` = ".$this->db->Quote($filter['payment']);
		}
		$query->leftjoin['hikaallorders_'.$num] = $lj;

		$operator = ($filter['type'] == 'IN') ? 'IS NOT NULL' : 'IS NULL';
		$query->where[] = "hikaallorders$num.order_id ".$operator;
	}

	function onAcyProcessFilter_hikafield(&$query, $filter, $num){
		if(!$this->loadAcymailing()) return;
		if($filter['map'] == 'user_created') $filter['value'] = acymailing_replaceDate($filter['value']);

		$query->join[$num.'hikashopfield'] = '#__hikashop_user AS '.$num.'hikafield ON sub.email = '.$num.'hikafield.user_email';
		$query->where[$num.'hikashopfield'] = $query->convertQuery($num.'hikafield', $filter['map'], $filter['operator'], $filter['value']);
	}

	function onAcyProcessFilter_hikareminder(&$query, $filter, $num){
		if(!$this->loadAcymailing()) return;


		$delay = 0;
		$comparisionFormat = '%Y %d %m';
		if(!empty($filter['nbtype'])){
			$delay = ($filter['nbdays'] * (int)$filter['nbtype']);
			switch((int)$filter['nbtype']){
				case 60:
					$comparisionFormat = '%Y %d %m %h %i';
					break;
				case 3600:
					$comparisionFormat = '%Y %d %m %h';
					break;
				case 604800:
					$comparisionFormat = '%Y %d %u';
					break;
				case 2635200:
					$comparisionFormat = '%Y %d';
					break;
				case 31622400:
					$comparisionFormat = '%Y';
					break;
				case 86400:
				default:
					break;
			}
		}elseif(!empty($filter['nbdays'])){
			$delay = ($filter['nbdays'] * 86400);
		}elseif(!empty($filter['senddate'])) $delay = ($filter['senddate'] * 3600);

		$senddate = (time() - $delay);

		$config =& hikashop_config();
		$createdstatus = $config->get('order_created_status', 'created');

		$myquery = 'SELECT hikauser.user_email
					FROM #__hikashop_order AS a
					LEFT JOIN #__hikashop_order AS b
						ON a.order_user_id = b.order_user_id
						AND b.order_id > a.order_id
					JOIN #__hikashop_user AS hikauser
						ON a.order_user_id = hikauser.user_id ';

		if(!empty($filter['payment'])) $myquery .= 'JOIN #__hikashop_payment AS payment ON payment.payment_type = a.order_payment_method AND payment.payment_id = '.intval($filter['payment']);

		$myquery .= ' WHERE a.order_status = '.$this->db->Quote($createdstatus).' AND b.order_id IS NULL ';
		if(!empty($senddate)) $myquery .= 'AND FROM_UNIXTIME(a.order_created,"%Y %d %m") = FROM_UNIXTIME('.$senddate.',"%Y %d %m")';
		$this->db->setQuery($myquery);

		$allOrders = acymailing_loadResultArray($this->db);

		if(empty($allOrders)) $allOrders[] = '-1';
		$query->where[] = "sub.email IN ('".implode("','", $allOrders)."')";
	}

	function onAcyProcessFilter_hikaorder(&$query, $filter, $num){
		if(!$this->loadAcymailing()) return;

		$config =& hikashop_config();
		$statuses = $config->get('invoice_order_statuses', 'confirmed,shipped');
		if(empty($statuses)) $statuses = 'confirmed,shipped';
		$statuses = explode(',', $statuses);
		$condition = array();
		foreach($statuses as $status){
			if(!empty($status)) $condition[] = $query->db->Quote($status);
		}
		$myquery = "SELECT DISTINCT b.user_email
					FROM #__hikashop_order_product AS a
					LEFT JOIN #__hikashop_order AS c ON a.order_id = c.order_id
					LEFT JOIN #__hikashop_user AS b on c.order_user_id = b.user_id";
		$filters = array();
		if(count($condition))
			$filters[] = "c.order_status IN (".implode(',',$condition).")";

		if(!empty($filter['product']) && is_numeric($filter['product'])) {
			$filters[] = "a.product_id = ".(int) $filter['product'];
		} elseif(!empty($filter['cat']) && is_numeric($filter['cat'])) {
			$myquery .= ' LEFT JOIN #__hikashop_product as hkp ON a.product_id = hkp.product_id LEFT JOIN #__hikashop_product_category AS hkpc ON (a.product_id = hkpc.product_id) OR (hkp.product_parent_id > 0 AND hkp.product_parent_id = hkpc.product_id) ';
			$filters[] = 'hkpc.category_id = '.(int)$filter['cat'];
		}

		$datesVar = array('creationdatesup', 'creationdateinf');
		foreach($datesVar as $oneDate){
			if(empty($filter[$oneDate])) continue;
			$filter[$oneDate] = acymailing_replaceDate($filter[$oneDate]);
			if(!is_numeric($filter[$oneDate])) $filter[$oneDate] = strtotime($filter[$oneDate]);
		}

		if(empty($filter['datefield'])) $filter['datefield'] = 'order_created';

		if(!empty($filter['creationdateinf'])) $filters[] = 'c.`'.$filter['datefield'].'` > '.$filter['creationdateinf'];
		if(!empty($filter['creationdatesup'])) $filters[] = 'c.`'.$filter['datefield'].'` < '.$filter['creationdatesup'];

		if(count($filters)){
			$myquery .= ' WHERE '.implode(' AND ', $filters);
		}

		$query->db->setQuery($myquery);
		$allEmails = acymailing_loadResultArray($query->db);
		if(empty($allEmails)) $allEmails[] = 'none';
		if(empty($filter['type'])){
			$query->where[] = "sub.email NOT IN ('".implode("','", $allEmails)."')";
		}else{
			$query->where[] = "sub.email IN ('".implode("','", $allEmails)."')";
		}
	}

	function onAcyDisplayTriggers(&$triggers){
		if(!$this->loadAcymailing()) return;

		$statusClass = hikashop_get('type.categorysub');
		$statusClass->type = 'status';
		$statusClass->load();

		if(empty($statusClass->categories)) return;

		$triggers['hikaorder'] = new stdClass();
		$triggers['hikaorder']->name = JText::_('HIKASHOP_ORDER_STATUS_CHANGED_TO');
		$plugin = JPluginHelper::getPlugin('hikashop', 'acymailing');
		if(empty($plugin)){
			$triggers['hikaorder']->name .= ' (If you want to use this feature you need to publish the plugin AcyMailing for HikaShop in the hikashop configuration page under the plugins tab)';
		}

		foreach($statusClass->categories as $category){
			if(empty($category->value)){
				$val = str_replace(' ', '_', strtoupper($category->category_name));
				$category->value = JText::_($val);
				if($val == $category->value){
					$category->value = $category->category_name;
				}
			}
			$triggers['hikaorder']->triggers['hikaorder_'.$category->category_name] = $category->value;
		}
	}
}//endclass
