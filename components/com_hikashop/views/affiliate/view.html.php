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
class AffiliateViewAffiliate extends HikaShopView {
	var $ctrl= 'affiliate';
	var $nameListing = 'AFFILIATES';
	var $nameForm = 'AFFILIATE';
	var $icon = 'affiliate';
	var $module=false;
	var $triggerView = true;

	function display($tpl = null,$params=array()){
		$this->paramBase = HIKASHOP_COMPONENT.'.'.$this->getName();
		$function = $this->getLayout();
		$this->params = $params;
		if(method_exists($this,$function)) $this->$function();
		parent::display($tpl);
	}
	function show(){
		$user = hikashop_loadUser(true);
		$userClass = hikashop_get('class.user');
		$userClass->loadPartnerData($user);
		$config =& hikashop_config();
		if(empty($user->user_params->user_custom_fee)){
			$user->user_params->user_partner_click_fee = $config->get('partner_click_fee',0);
			$user->user_params->user_partner_lead_fee = $config->get('partner_lead_fee',0);
			$user->user_params->user_partner_percent_fee = $config->get('partner_percent_fee',0);
			$user->user_params->user_partner_flat_fee = $config->get('partner_flat_fee',0);
		}

		$query = 'SELECT * FROM '.hikashop_table('banner').' WHERE banner_published=1 ORDER BY banner_ordering ASC;';
		$database = JFactory::getDBO();
		$database->setQuery($query);
		$banners = $database->loadObjectList();
		$this->assignRef('banners',$banners);
		$advanced_stats = $config->get('affiliate_advanced_stats',1);
		$this->assignRef('advanced_stats',$advanced_stats);
		$this->assignRef('user',$user);
		$currencyType = hikashop_get('type.currency');
		$this->assignRef('currencyType',$currencyType);
		$currencyHelper = hikashop_get('class.currency');
		$this->assignRef('currencyHelper',$currencyHelper);
		$popup = hikashop_get('helper.popup');
		$this->assignRef('popup',$popup);
		if(HIKASHOP_J40)
			JHtml::_('bootstrap.tooltip', '.hasTooltip', array('placement' => 'left'));
		else
			JHTML::_('behavior.tooltip');
		$affiliate_plugin = hikashop_import('system','hikashopaffiliate');
		$partner_id = (int)@$user->user_id;
		$this->assignRef('partner_id',$partner_id);
		$partner_info=$affiliate_plugin->params->get('partner_key_name','partner_id').'='.$partner_id;
		$this->assignRef('partner_info',$partner_info);
		$allow_currency_selection = $config->get('allow_currency_selection');
		$this->assignRef('allow_currency_selection',$allow_currency_selection);
		global $Itemid;
		$url_itemid='';
		if(!empty($Itemid))
			$url_itemid = '&Itemid='.$Itemid;
		$this->assignRef('url_itemid', $url_itemid);

		if(empty($this->user->user_id)){
			$app = JFactory::getApplication();

			$url = 'index.php?option=com_users&view=login'.$url.$this->url_itemid;
			$url = JRoute::_($url.'&return='.urlencode(base64_encode(hikashop_currentUrl('',false))));
			$app->enqueueMessage( '<a href="'.$url.'">'.JText::_('PLEASE_LOGIN_FIRST').'</a>');
		}
		$config =& hikashop_config();
		$affiliate_terms = $config->get('affiliate_terms',0);
		$this->assignRef('affiliate_terms',$affiliate_terms);

		$this->toolbar = array();
		$this->toolbar['apply'] = array(
			'icon' => 'apply',
			'name' => JText::_('HIKA_APPLY'),
			'javascript' => "submitbutton('apply'); return false;",
			'fa' => array('html' => '<i class="fas fa-check-circle"></i>')
		);

		global $Itemid;
		$this->Itemid = $Itemid;

		$app = JFactory::getApplication();
		$menus	= $app->getMenu();
		$menu	= $menus->getActive();
		$show_page_heading = true;
		$params = null;
		if(!empty($menu) && method_exists($menu, 'getParams')) {
			$params = $menu->getParams();
			$show_page_heading = $params->get('show_page_heading');
		}
		if(is_null($show_page_heading)) {
			$com_menus = JComponentHelper::getParams('com_menus');
			if(!empty($com_menus))
				$show_page_heading = $com_menus->get('show_page_heading');
		}
		if(!empty($menu) && method_exists($menu, 'getParams') && $menu->link == 'index.php?option=com_hikashop&view=affiliate&layout=show') {
			if($show_page_heading)
				$this->title = $params->get('page_heading');
			$title = $params->get('page_title');
			if(empty($title))
				$title = $menu->title;
			hikashop_setPageTitle($title);

			$robots = $params->get('robots');
			if (!$robots) {
				$jconfig = JFactory::getConfig();
				$robots = $jconfig->get('robots', '');
			}
			if($robots) {
				$doc = JFactory::getDocument();
				$doc->setMetadata('robots', $robots);
			}

		} else {
			if($show_page_heading)
				$this->title = JText::_('AFFILIATE');
			hikashop_setPageTitle('AFFILIATE');
			$pathway = $app->getPathway();
			$pathway->addItem(JText::_('AFFILIATE'), hikashop_completeLink('affiliate'.$this->url_itemid));

			$this->toolbar['back'] = array(
				'icon' => 'back',
				'name' => JText::_('HIKA_BACK'),
				'url' => hikashop_completeLink('user&task=cpanel'.$this->url_itemid),
				'fa' => array('html' => '<i class="fas fa-arrow-circle-left"></i>')
			);
		} 
		$this->params = $app->getParams();
		$doc = JFactory::getDocument();

		if ($this->params->get('menu-meta_keywords')) {
			$doc->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}
		if ($this->params->get('menu-meta_description')) {
			$doc->setMetadata('description', $this->params->get('menu-meta_description'));
		}


		$robots = $this->params->get('robots');
		if (!$robots) {
			$jconfig = JFactory::getConfig();
			$robots = $jconfig->get('robots', '');
		}
		if($robots) {
			$doc->setMetadata('robots', $robots);
		}

	}

	function sales(){
		$this->paramBase.='_sales';
		$app = JFactory::getApplication();
		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();
		$pageInfo->search = $app->getUserStateFromRequest( $this->paramBase.".search", 'search', '', 'string' );
		$pageInfo->filter->order->value = $app->getUserStateFromRequest( $this->paramBase.".filter_order", 'filter_order',	'b.order_id','cmd' );
		$pageInfo->filter->order->dir	= $app->getUserStateFromRequest( $this->paramBase.".filter_order_Dir", 'filter_order_Dir',	'desc',	'word' );
		$pageInfo->limit->value = $app->getUserStateFromRequest( $this->paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int' );
		$pageInfo->limit->start = $app->getUserStateFromRequest( $this->paramBase.'.limitstart', 'limitstart', 0, 'int' );
		$database = JFactory::getDBO();
		$config =& hikashop_config();
		$partner_valid_status_list=explode(',',$config->get('partner_valid_status','confirmed,shipped'));
		foreach($partner_valid_status_list as $k => $partner_valid_status){
			$partner_valid_status_list[$k]= $database->Quote($partner_valid_status);
		}
		$user_id = hikashop_loadUser();
		$filters = array('b.order_partner_id='.$user_id,'b.order_partner_paid=0','b.order_status IN ('.implode(',',$partner_valid_status_list).')');

		$searchMap = array('c.id','c.username','c.name','a.user_email','b.order_user_id','b.order_id','b.order_full_price','b.order_number');

		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.hikashop_getEscaped(HikaStringHelper::strtolower(trim($pageInfo->search)),true).'%\'';
			$filter = '('.implode(" LIKE $searchVal OR ",$searchMap)." LIKE $searchVal".')';

			$filters[] =  $filter;
		}
		$order = '';
		if(!empty($pageInfo->filter->order->value)){
			$order = ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;
		}
		if(!empty($filters)){
			$filters = ' WHERE ('. implode(') AND (',$filters).')';
		}else{
			$filters = '';
		}

		$query = ' FROM '.hikashop_table('order').' AS b LEFT JOIN '.hikashop_table('user').' AS a ON b.order_user_id=a.user_id LEFT JOIN '.hikashop_table('users',false).' AS c ON a.user_cms_id=c.id '.$filters.$order;
		$database->setQuery('SELECT a.*,b.*,c.*'.$query,(int)$pageInfo->limit->start,(int)$pageInfo->limit->value);
		$rows = $database->loadObjectList();
		if(!empty($pageInfo->search)){
			$rows = hikashop_search($pageInfo->search,$rows,'order_id');
		}
		$database->setQuery('SELECT COUNT(*)'.$query);
		$pageInfo->elements = new stdClass();
		$pageInfo->elements->total = $database->loadResult();
		$pageInfo->elements->page = count($rows);
		jimport('joomla.html.pagination');
		$pagination = new JPagination( $pageInfo->elements->total, $pageInfo->limit->start, $pageInfo->limit->value );

		$toggleClass = hikashop_get('helper.toggle');
		$this->assignRef('toggleClass',$toggleClass);
		$this->assignRef('rows',$rows);
		$this->assignRef('pageInfo',$pageInfo);
		$this->assignRef('pagination',$pagination);
		$currencyClass = hikashop_get('class.currency');
		$this->assignRef('currencyHelper',$currencyClass);

		global $Itemid;
		$url_itemid='';
		if(!empty($Itemid))
			$url_itemid = '&Itemid='.$Itemid;
		$this->assignRef('url_itemid', $url_itemid);
	}

	function clicks(){
		$this->paramBase.='_clicks';
		$app = JFactory::getApplication();
		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();
		$pageInfo->search = $app->getUserStateFromRequest( $this->paramBase.".search", 'search', '', 'string' );
		$pageInfo->filter->order->value = $app->getUserStateFromRequest( $this->paramBase.".filter_order", 'filter_order',	'a.click_id','cmd' );
		$pageInfo->filter->order->dir	= $app->getUserStateFromRequest( $this->paramBase.".filter_order_Dir", 'filter_order_Dir',	'desc',	'word' );
		$pageInfo->limit->value = $app->getUserStateFromRequest( $this->paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int' );
		$pageInfo->limit->start = $app->getUserStateFromRequest( $this->paramBase.'.limitstart', 'limitstart', 0, 'int' );
		$pageInfo->filter->unpaid = $app->getUserStateFromRequest( $this->paramBase.'.unpaid', 'unpaid', 1, 'int' );

		$database	= JFactory::getDBO();
		$user = hikashop_loadUser(true);
		$this->assignRef('user',$user);
		$filters = array('a.click_partner_id='.$user->user_id,'a.click_partner_paid=0');

		if(!empty($pageInfo->filter->unpaid)){
			$filters[] = 'a.click_partner_price > 0';
		}
		$searchMap = array('a.click_referer','a.click_ip','a.click_id');
		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.hikashop_getEscaped($pageInfo->search,true).'%\'';
			$filters[] = '('.implode(" LIKE $searchVal OR ",$searchMap)." LIKE $searchVal".')';
		}
		$order = '';
		if(!empty($pageInfo->filter->order->value)){
			$order = ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;
		}
		if(!empty($filters)){
			$filters = ' WHERE ('. implode(') AND (',$filters).')';
		}else{
			$filters = '';
		}

		$query = ' FROM '.hikashop_table('click').' AS a'.$filters.$order;
		$database->setQuery('SELECT a.*'.$query,(int)$pageInfo->limit->start,(int)$pageInfo->limit->value);
		$rows = $database->loadObjectList();
		if(!empty($pageInfo->search)){
			$rows = hikashop_search($pageInfo->search,$rows,'click_id');
		}
		$database->setQuery('SELECT COUNT(*)'.$query);
		$pageInfo->elements = new stdClass();
		$pageInfo->elements->total = $database->loadResult();
		$pageInfo->elements->page = count($rows);
		jimport('joomla.html.pagination');
		$pagination = new JPagination( $pageInfo->elements->total, $pageInfo->limit->start, $pageInfo->limit->value );
		$this->assignRef('rows',$rows);
		$this->assignRef('pageInfo',$pageInfo);
		$this->assignRef('pagination',$pagination);
		$currencyClass = hikashop_get('class.currency');
		$this->assignRef('currencyHelper',$currencyClass);

		global $Itemid;
		$url_itemid='';
		if(!empty($Itemid))
			$url_itemid = '&Itemid='.$Itemid;
		$this->assignRef('url_itemid', $url_itemid);
	}

	function leads(){
		$this->paramBase.='_leads';
		$app = JFactory::getApplication();
		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();
		$fieldsClass = hikashop_get('class.field');
		$fields = $fieldsClass->getData('backend_listing','user',false);
		$this->assignRef('fields',$fields);
		$this->assignRef('fieldsClass',$fieldsClass);
		$pageInfo->search = $app->getUserStateFromRequest( $this->paramBase.".search", 'search', '', 'string' );
		$pageInfo->filter->order->value = $app->getUserStateFromRequest( $this->paramBase.".filter_order", 'filter_order',	'a.user_id','cmd' );
		$pageInfo->filter->order->dir	= $app->getUserStateFromRequest( $this->paramBase.".filter_order_Dir", 'filter_order_Dir',	'desc',	'word' );
		$pageInfo->limit->value = $app->getUserStateFromRequest( $this->paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int' );
		$pageInfo->limit->start = $app->getUserStateFromRequest( $this->paramBase.'.limitstart', 'limitstart', 0, 'int' );
		$database	= JFactory::getDBO();
		$user = hikashop_loadUser(true);
		$this->assignRef('user',$user);
		$filters = array('a.user_partner_id='.$user->user_id,'a.user_partner_paid=0');

		$searchMap = array('a.user_id','a.user_email','b.username','b.email','b.name');
		foreach($fields as $field){
			$searchMap[]='a.'.$field->field_namekey;
		}
		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.hikashop_getEscaped($pageInfo->search,true).'%\'';
			$filters[] = '('.implode(" LIKE $searchVal OR ",$searchMap)." LIKE $searchVal".')';
		}
		$order = '';
		if(!empty($pageInfo->filter->order->value)){
			$order = ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;
		}
		if(!empty($filters)){
			$filters = ' WHERE ('. implode(') AND (',$filters).')';
		}else{
			$filters = '';
		}

		$query = ' FROM '.hikashop_table('user').' AS a LEFT JOIN '.hikashop_table('users',false).' AS b ON a.user_cms_id=b.id '.$filters.$order;
		$database->setQuery('SELECT a.*,b.*'.$query,(int)$pageInfo->limit->start,(int)$pageInfo->limit->value);
		$rows = $database->loadObjectList();
		$fieldsClass->handleZoneListing($fields,$rows);
		if(!empty($pageInfo->search)){
			$rows = hikashop_search($pageInfo->search,$rows,'user_id');
		}
		$database->setQuery('SELECT COUNT(*)'.$query);
		$pageInfo->elements = new stdClass();
		$pageInfo->elements->total = $database->loadResult();
		$pageInfo->elements->page = count($rows);
		jimport('joomla.html.pagination');
		$pagination = new JPagination( $pageInfo->elements->total, $pageInfo->limit->start, $pageInfo->limit->value );
		$this->assignRef('rows',$rows);
		$this->assignRef('pageInfo',$pageInfo);
		$this->assignRef('pagination',$pagination);
		$currencyClass = hikashop_get('class.currency');
		$this->assignRef('currencyHelper',$currencyClass);

		global $Itemid;
		$url_itemid='';
		if(!empty($Itemid))
			$url_itemid = '&Itemid='.$Itemid;
		$this->assignRef('url_itemid', $url_itemid);
	}

}
