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
class productMarketController extends hikamarketController {
	protected $rights = array(
		'display' => array(
			'listing',
			'getprice', 'editprice', 'gettree', 'findtree',
			'upload', 'image', 'file', 'file_entry', 'galleryimage',
			'edit_translation', 'cartlink', 'waitingapproval',
		),
		'add' => array('add'),
		'edit' => array('edit', 'variant', 'variants', 'characteristic', 'addimage', 'addfile', 'galleryselect', 'approve', 'saveorder'),
		'modify' => array('apply', 'save', 'save_translation', 'copy', 'toggle'),
		'delete' => array('delete')
	);
	protected $type = 'product';
	protected $config = null;

	protected $ordering = array(
		'type' => 'product',
		'pkey' => 'product_id',
		'table' => 'product_category',
		'groupMap' => 'category_id',
		'orderingMap' => 'ordering',
		'groupVal' => 0
	);

	public function __construct($config = array(), $skip = false) {
		parent::__construct($config, $skip);
		if(!$skip)
			$this->registerDefaultTask('listing');
		$this->config = hikamarket::config();
	}

	protected function raiseForbidden() {
		throw new JAccessExceptionNotallowed(JText::_('Access Forbidden'), 403);
	}

	protected function isNewProduct($product_id = null) {
		if(!hikamarket::acl('product/new') && hikamarket::acl('product/edit'))
			return false;
		if($product_id === null)
			$product_id = hikaInput::get()->getInt('product_id', 0);
		if(empty($product_id))
			return true;

		static $cache = array();
		if(isset($cache[$product_id]))
			return $cache[$product_id];
		$query = 'SELECT product_type FROM '.hikamarket::table('shop.product').' WHERE product_id = '.(int)$product_id;
		$db = JFactory::getDBO();
		$db->setQuery($query);
		$ret = $db->loadResult();
		$cache[$product_id] = ($ret == 'waiting_approval');
		return $cache[$product_id];
	}

	public function authorize($task) {
		if($task == 'toggle' || $task == 'delete') {
			$completeTask = hikaInput::get()->getCmd('task');
			if(strrpos($completeTask, '-') !== false) {
				$product_id = (int)substr($completeTask, strrpos($completeTask, '-') + 1);
			} else {
				$product_id = hikaInput::get()->getInt('product_id');
				if(empty($product_id))
					$product_id = hikaInput::get()->getInt('value');
			}
			if(empty($product_id))
				return false;
			if(!hikamarket::loginVendor())
				return false;
			if(!$this->config->get('frontend_edition',0))
				return false;
			if(!JSession::checkToken('request'))
				return false;
			if($task == 'toggle' && !hikamarket::acl('product/edit/published'))
				return false;
			if($task == 'delete' && !hikamarket::acl('product/delete'))
				return false;
			if(!hikamarket::isVendorProduct($product_id))
				return false;
			return true;
		}
		return parent::authorize($task);
	}

	public function listing() {
		if( !$this->config->get('frontend_edition',0) ) {
			$this->raiseForbidden();
			return false;
		}
		if(!hikamarket::loginVendor())
			return false;
		if(!hikamarket::acl('product/listing'))
			return hikamarket::deny('vendor', JText::sprintf('HIKAM_ACTION_DENY', JText::_('HIKAM_ACT_PRODUCT_LISTING')));

		hikaInput::get()->set('layout', 'listing');
		return parent::display();
	}

	public function save() {
		if(!$this->store())
			return $this->edit();

		$subtask = hikaInput::get()->getCmd('subtask', '');
		if($subtask == 'variant')
			return $this->variant();

		$app = JFactory::getApplication();
		$product_id = hikaInput::get()->getInt('cid');
		$cancel_action = hikaInput::get()->getCmd('cancel_action');
		if(!empty($cancel_action) && !empty($product_id)) {
			switch($cancel_action) {
				case 'product':
					$app->redirect( hikamarket::completeLink('shop.product&task=show&cid='.$product_id, false, true) );
					break;
				case 'url':
					$cancel_url = urldecode(hikaInput::get()->getString('cancel_url', ''));
					if(strpos($cancel_url,'http') !== 0 && strpos($cancel_url,'/') !== 0)
						$cancel_url = base64_decode($cancel_url);
					if(!empty($cancel_url) && !hikamarket::disallowUrlRedirect($cancel_url)) {
						$app->redirect( $cancel_url_decode );
					}
					break;
			}
		}

		$app->redirect( hikamarket::completeLink('product&task=listing', false, true) );
		return false;
	}

	public function edit() {
		if( !$this->config->get('frontend_edition',0) ) {
			$this->raiseForbidden();
			return false;
		}
		if( !hikamarket::loginVendor() )
			return false;
		if( !hikamarket::acl('product/edit') )
			return hikamarket::deny('product', JText::sprintf('HIKAM_ACTION_DENY', JText::_('HIKAM_ACT_PRODUCT_EDIT')));

		$product_id = hikamarket::getCID('product_id');
		if(!hikamarket::isVendorProduct($product_id)) {
			if(hikaInput::get()->getCmd('duplicate', 0) == 0)
				return hikamarket::deny('product', JText::_('HIKAM_PAGE_DENY'));

			$productClass = hikamarket::get('class.product');
			$product = $productClass->get($product_id);
			if(empty($product) || (int)$product->product_vendor_id > 0)
				return hikamarket::deny('product', JText::_('HIKAM_PAGE_DENY'));

			$query = 'SELECT c.characteristic_id FROM ' . hikamarket::table('shop.variant') . ' AS v '.
				' INNER JOIN ' . hikamarket::table('shop.characteristic') . ' AS c ON v.variant_characteristic_id = c.characteristic_id '.
				' WHERE c.characteristic_parent_id = 0 AND c.characteristic_alias = \'vendor\' AND v.variant_product_id = ' . (int)$product_id.' '.
				' ORDER BY v.ordering ASC';
			$db = JFactory::getDBO();
			$db->setQuery($query);
			$characteristic_id = (int)$db->loadResult();
			if(empty($characteristic_id))
				return hikamarket::deny('product', JText::_('HIKAM_PAGE_DENY'));

			$product_duplication = new stdClass();
			$product_duplication->product_id = $product_id;
			$product_duplication->characteristic_id = $characteristic_id;
			hikaInput::get()->set('product_duplication', $product_duplication);
		}

		hikaInput::get()->set('layout', 'form');
		return parent::display();
	}

	public function variant() {
		if( !$this->config->get('frontend_edition',0) ) {
			$this->raiseForbidden();
			return false;
		}
		if( !hikamarket::loginVendor() )
			return false;
		if( !hikamarket::acl('product/edit/variants') || !hikamarket::acl('product/variant') )
			return hikamarket::deny('product', JText::sprintf('HIKAM_ACTION_DENY', JText::_('HIKAM_ACT_PRODUCT_EDIT')));

		$product_id = hikamarket::getCID('variant_id');
		if(!hikamarket::isVendorProduct($product_id))
			return hikamarket::deny('product', JText::_('HIKAM_PAGE_DENY'));

		hikaInput::get()->set('layout', 'variant');
		if(hikaInput::get()->getCmd('tmpl', '') == 'component') {
			ob_end_clean();
			parent::display();
			exit;
		}
		return parent::display();
	}

	public function variants() {
		if( !$this->config->get('frontend_edition',0) ) {
			$this->raiseForbidden();
			return false;
		}
		if( !hikamarket::loginVendor() )
			return false;
		if( !hikamarket::acl('product/edit/variants'))
			return hikamarket::deny('product', JText::sprintf('HIKAM_ACTION_DENY', JText::_('HIKAM_ACT_PRODUCT_EDIT')));

		$product_id = hikaInput::get()->getInt('product_id', 0);
		if(!hikamarket::isVendorProduct($product_id))
			return hikamarket::deny('product', JText::_('HIKAM_PAGE_DENY'));

		hikaInput::get()->set('layout', 'form_variants');

		$subtask = hikaInput::get()->getCmd('subtask', '');
		if(!empty($subtask)) {
			switch($subtask) {
				case 'setdefault':
					$variant_id = hikaInput::get()->getInt('variant_id');
					$productClass = hikamarket::get('class.product');
					$ret = $productClass->setDefaultVariant($product_id, $variant_id);
					break;

				case 'add':
				case 'duplicate':
					JSession::checkToken('request') || die('Invalid Token');
					hikaInput::get()->set('layout', 'form_variants_add');
					break;

				case 'delete';
					JSession::checkToken('request') || die('Invalid Token');
					$cid = hikaInput::get()->get('cid', array(), 'array');
					if(empty($cid)) {
						ob_end_clean();
						echo '0';
						exit;
					}
					$productClass = hikamarket::get('class.product');
					$ret = $productClass->deleteVariants($product_id, $cid);
					ob_end_clean();
					if($ret !== false)
						echo $ret;
					else
						echo '0';
					exit;

				case 'populate':
					JSession::checkToken('request') || die('Invalid Token');
					hikaInput::get()->set('layout', 'form_variants_add');

					$productClass = hikamarket::get('class.product');
					$data = hikaInput::get()->get('data', array(), 'array');
					if(isset($data['variant_duplicate'])) {
						$cid = hikaInput::get()->get('cid', array(), 'array');
						hikamarket::toInteger($cid);
						$ret = $productClass->duplicateVariant($product_id, $cid, $data);
					} else
						$ret = $productClass->populateVariant($product_id, $data);
					if($ret !== false) {
						ob_end_clean();
						echo $ret;
						exit;
					}
					break;
			}
		}

		if(hikaInput::get()->getCmd('tmpl', '') == 'component') {
			ob_end_clean();
			parent::display();
			exit;
		}
		return parent::display();
	}

	public function characteristic() {
		if( !$this->config->get('frontend_edition',0) ) {
			$this->raiseForbidden();
			return false;
		}
		if( !hikamarket::loginVendor() )
			return false;
		if( !hikamarket::acl('product/edit/variants'))
			return hikamarket::deny('product', JText::sprintf('HIKAM_ACTION_DENY', JText::_('HIKAM_ACT_PRODUCT_EDIT')));

		$product_id = hikamarket::getCID('product_id');
		if(!hikamarket::isVendorProduct($product_id))
			return hikamarket::deny('product', JText::_('HIKAM_PAGE_DENY'));

		$subtask = hikaInput::get()->getCmd('subtask', '');
		if(empty($subtask)) {
		}

		$productClass = hikamarket::get('class.product');
		switch($subtask) {
			case 'add':
				JSession::checkToken() || die('Invalid Token');

				$vendor_id = hikamarket::loadVendor(false);
				$characteristic_id = hikaInput::get()->getInt('characteristic_id', 0);
				$characteristic_value_id = hikaInput::get()->getInt('characteristic_value_id', 0);
				$ret = $productClass->addCharacteristic($product_id, $characteristic_id, $characteristic_value_id, $vendor_id);
				ob_end_clean();
				if($ret === false)
					echo '-1';
				else
					echo (int)$ret;
				exit;

			case 'remove':
				JSession::checkToken() || die('Invalid Token');

				$characteristic_id = hikaInput::get()->getInt('characteristic_id', 0);
				$ret = $productClass->removeCharacteristic($product_id, $characteristic_id);
				ob_end_clean();
				if($ret === false)
					echo '-1';
				else
					echo (int)$ret;
				exit;
		}

		exit;
	}

	public function add() {
		if( !$this->config->get('frontend_edition',0) ) {
			$this->raiseForbidden();
			return false;
		}
		if( !hikamarket::loginVendor() )
			return false;
		if( !hikamarket::acl('product/add') )
			return hikamarket::deny('product', JText::sprintf('HIKAM_ACTION_DENY', JText::_('HIKAM_ACT_PRODUCT_EDIT')));

		$vendorClass = hikamarket::get('class.vendor');
		$vendor = hikamarket::loadVendor(true, false);

		$vendorCompleted = $vendorClass->checkVendorCompletion($vendor, false);
		if($vendorCompleted !== true)
			return hikamarket::deny('vendor&task=form', JText::_('VENDOR_UNCOMPLETED'));

		$limitation = $vendorClass->checkProductLimitation($vendor, false);
		if($limitation !== true) {
			if($limitation > 1)
				return hikamarket::deny('product', JText::sprintf('VENDOR_PRODUCT_LIMITATION_X_REACHED', $limitation));
			return hikamarket::deny('product', JText::_('VENDOR_PRODUCT_LIMITATION_REACHED'));
		}


		hikaInput::get()->set('layout', 'form');
		return parent::display();
	}

	public function copy() {
		if( !$this->config->get('frontend_edition',0) ) {
			$this->raiseForbidden();
			return false;
		}
		if( !hikamarket::loginVendor() )
			return false;

		$vendor = hikamarket::loadVendor(true, false);
		if( ($vendor->vendor_id != 0 && $vendor->vendor_id != 1) || !hikamarket::acl('product/add') )
			return hikamarket::deny('product', JText::sprintf('HIKAM_ACTION_DENY', JText::_('HIKAM_ACT_PRODUCT_COPY')));

		$product_id = hikamarket::getCID('product_id');
		if(!hikamarket::isVendorProduct($product_id))
			return hikamarket::deny('product', JText::_('HIKAM_PAGE_DENY'));

		$vendorClass = hikamarket::get('class.vendor');
		$limitation = $vendorClass->checkProductLimitation($vendor, false);
		if($limitation !== true) {
			if($limitation > 1)
				return hikamarket::deny('product', JText::sprintf('VENDOR_PRODUCT_LIMITATION_X_REACHED', $limitation));
			return hikamarket::deny('product', JText::_('VENDOR_PRODUCT_LIMITATION_REACHED'));
		}

		$app = JFactory::getApplication();
		$importHelper = hikamarket::get('shop.helper.import');
		if(!$importHelper->copyProduct($product_id)) {
			$app->enqueueMessage(JText::_('PRODUCT_SAVE_UNKNOWN_ERROR'), 'error');
		} else {
			$app->enqueueMessage(JText::_('HIKAM_SUCC_SAVED'));
		}

		$return_url = hikaInput::get()->getString('return_url', '');
		if(!empty($return_url)) {
			try{
				$return_url = urldecode(base64_decode($return_url));
			}catch(Exception $e) {
				$return_url = '';
			}
		}
		if(!empty($return_url))
			$app->redirect($return_url);
		$app->redirect(hikamarket::completeLink('product&task=listing', false, true));
	}

	public function delete() {
		if( !$this->config->get('frontend_edition', 0)) {
			$this->raiseForbidden();
			return false;
		}
		if( !hikamarket::loginVendor() )
			return false;

		$completeTask = hikaInput::get()->getCmd('task');
		if(strrpos($completeTask, '-') !== false) {
			$product_id = (int)substr($completeTask, strrpos($completeTask, '-') + 1);
		} else {
			$product_id = hikaInput::get()->getInt('product_id');
		}
		if(empty($product_id))
			return false;

		if(!hikamarket::acl('product/delete') || !hikamarket::isVendorProduct($product_id))
			return false;

		$productClass = hikamarket::get('shop.class.product');
		$shopConfig = hikamarket::config(false);
		if($shopConfig->get('use_trash', 0)) {
			$ret = $productClass->trash($product_id);
		} else {
			$ret = $productClass->delete($product_id);
		}

		$app = JFactory::getApplication();
		if(!empty($ret) && $ret > 0) {
			$app->enqueueMessage(JText::sprintf('SUCC_DELETE_ELEMENTS', 1), 'message');
		}
		$app->redirect(hikamarket::completeLink('product&task=listing', false, true));
	}

	public function saveorder() {
		if( !$this->config->get('frontend_edition',0) ) {
			$this->raiseForbidden();
			return false;
		}
		if( !hikamarket::loginVendor() )
			return false;
		$vendor_id = hikamarket::loadVendor(false);
		if( $vendor_id > 1 )
			return false;
		if( !hikamarket::acl('product/sort') )
			return hikamarket::deny('product', JText::sprintf('HIKAM_ACTION_DENY', JText::_('HIKAM_ACT_PRODUCT_SORT')));

		$category_explorer = $this->config->get('show_category_explorer', 1);
		if(!$category_explorer)
			return false;

		$app = JFactory::getApplication();

		$cid = hikamarket::getCID();
		if(empty($cid) || $cid == 1)
			$cid = (int)$app->getUserState(HIKAMARKET_COMPONENT.'.product.listing_cid');
		if(empty($cid))
			$cid = 1;

		$this->ordering['groupVal'] = $cid;
		parent::saveorder();

		$app = JFactory::getApplication();
		$app->redirect(hikamarket::completeLink('product&task=listing', false, true));
	}

	public function approve() {
		if( !$this->config->get('frontend_edition',0) ) {
			$this->raiseForbidden();
			return false;
		}
		if( !hikamarket::loginVendor() )
			return false;
		$vendor_id = hikamarket::loadVendor(false);
		if( $vendor_id > 1 || !$this->config->get('product_approval',0) )
			return false;
		if( !hikamarket::acl('product/approve') )
			return hikamarket::deny('product', JText::sprintf('HIKAM_ACTION_DENY', JText::_('HIKAM_ACT_PRODUCT_APPROVE')));

		$product_id = hikamarket::getCID('product_id');
		if(!hikamarket::isVendorProduct($product_id))
			return hikamarket::deny('product', JText::_('HIKAM_PAGE_DENY'));

		$productClass = hikamarket::get('class.product');
		$status = $productClass->approve($product_id);

		if($status) {
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::_('HIKAMARKET_PRODUCT_APPROVED'));
		}

		hikaInput::get()->set('layout', 'form');
		return parent::display();
	}

	public function waitingapproval() {
		if( !$this->config->get('frontend_edition',0) ) {
			$this->raiseForbidden();
			return false;
		}
		if( !hikamarket::loginVendor() )
			return false;
		if( !$this->config->get('product_approval',0) )
			return false;
		if( !hikamarket::acl('product/approve') )
			return hikamarket::deny('product', JText::sprintf('HIKAM_ACTION_DENY', JText::_('HIKAM_ACT_PRODUCT_APPROVE')));

		hikaInput::get()->set('layout', 'waitingapproval');
		return parent::display();
	}
	public function edit_translation() {
		if( !$this->config->get('frontend_edition',0) ) {
			$this->raiseForbidden();
			return false;
		}
		if( !hikamarket::loginVendor() )
			return false;
		if( !hikamarket::acl('product/edit/translations') )
			return hikamarket::deny('product', JText::sprintf('HIKAM_ACTION_DENY', JText::_('HIKAM_ACT_PRODUCT_EDIT')));

		$product_id = hikamarket::getCID('product_id');
		if(!hikamarket::isVendorProduct($product_id))
			return hikamarket::deny('product', JText::_('HIKAM_PAGE_DENY'));

		hikaInput::get()->set('layout', 'edit_translation');
		return parent::display();
	}

	public function save_translation() {
		if( !$this->config->get('frontend_edition',0) ) {
			$this->raiseForbidden();
			return false;
		}
		if( !hikamarket::loginVendor() )
			return false;
		if( !hikamarket::acl('product/edit/translations') )
			return hikamarket::deny('product', JText::sprintf('HIKAM_ACTION_DENY', JText::_('HIKAM_ACT_PRODUCT_EDIT')));

		$product_id = hikamarket::getCID('product_id');
		if(!hikamarket::isVendorProduct($product_id))
			return hikamarket::deny('product', JText::_('HIKAM_PAGE_DENY'));

		$product = null;
		$productClass = hikamarket::get('class.product');
		$product = $productClass->getRaw($product_id);
		if(!empty($product->product_id))
			$productClass->handleTranslation($product);
		$js = 'window.hikashop.ready(function(){window.parent.hikamarket.submitBox();});';
		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration($js);
	}

	public function store() {
		if( !$this->config->get('frontend_edition',0) ) {
			$this->raiseForbidden();
			return false;
		}
		if(!hikamarket::loginVendor())
			return false;
		if( !hikamarket::acl('product/edit') )
			return hikamarket::deny('product', JText::sprintf('HIKAM_ACTION_DENY', JText::_('HIKAM_ACT_PRODUCT_EDIT')));

		$productClass = hikamarket::get('class.product');
		if($productClass === null)
			return false;

		$subtask = hikaInput::get()->getCmd('subtask', '');
		if($subtask == 'variant') {
			$status = $productClass->frontSaveVariantForm();
		} else {
			$status = $productClass->frontSaveForm();
		}

		if($status) {
			hikaInput::get()->set('cid', $status);
			hikaInput::get()->set('fail', null);
		}
		return $status;
	}

	public function image() {
		if( !$this->config->get('frontend_edition',0) ) {
			$this->raiseForbidden();
			return false;
		}
		if( !hikamarket::loginVendor() )
			return false;
		if( !hikamarket::acl('product/edit') )
			return hikamarket::deny('product', JText::sprintf('HIKAM_ACTION_DENY', JText::_('HIKAM_ACT_PRODUCT_EDIT')));

		$product_id = hikaInput::get()->getInt('pid', 0);
		if(!hikamarket::isVendorProduct($product_id))
			return hikamarket::deny('product', JText::_('HIKAM_PAGE_DENY'));

		hikaInput::get()->set('layout', 'image');
		return parent::display();
	}

	public function file() {
		if( !$this->config->get('frontend_edition',0) ) {
			$this->raiseForbidden();
			return false;
		}
		if( !hikamarket::loginVendor() )
			return false;
		if( !hikamarket::acl('product/edit') )
			return hikamarket::deny('product', JText::sprintf('HIKAM_ACTION_DENY', JText::_('HIKAM_ACT_PRODUCT_EDIT')));

		$product_id = hikaInput::get()->getInt('pid', 0);
		if(!hikamarket::isVendorProduct($product_id))
			return hikamarket::deny('product', JText::_('HIKAM_PAGE_DENY'));

		hikaInput::get()->set('layout', 'file');
		return parent::display();
	}

	public function file_entry() {
		if( !$this->config->get('frontend_edition',0) ) {
			$this->raiseForbidden();
			return false;
		}
		if( !hikamarket::loginVendor() )
			return false;
		if( !hikamarket::acl('product/edit') )
			return hikamarket::deny('product', JText::sprintf('HIKAM_ACTION_DENY', JText::_('HIKAM_ACT_PRODUCT_EDIT')));

		$product_id = hikaInput::get()->getInt('pid', 0);
		if(!hikamarket::isVendorProduct($product_id))
			return hikamarket::deny('product', JText::_('HIKAM_PAGE_DENY'));

		hikaInput::get()->set('layout', 'form_file_entry');
		ob_end_clean();
		parent::display();
		exit;
	}

	public function cartlink() {
		if( !$this->config->get('frontend_edition',0) ) {
			$this->raiseForbidden();
			return false;
		}
		if( !hikamarket::loginVendor() )
			return false;
		if( !hikamarket::acl('product/edit') )
			return hikamarket::deny('product', JText::sprintf('HIKAM_ACTION_DENY', JText::_('HIKAM_ACT_PRODUCT_EDIT')));

		$product_id = hikaInput::get()->getInt('pid', 0);
		if(!hikamarket::isVendorProduct($product_id))
			return hikamarket::deny('product', JText::_('HIKAM_PAGE_DENY'));

		echo '
<textarea style="width:100%" rows="4">'.HIKASHOP_LIVE.'index.php?option='.HIKASHOP_COMPONENT.'&ctrl=product&task=updatecart&quantity=1&checkout=1&product_id='.$product_id.'</textarea><br/>
<textarea style="width:100%" rows="5">&lt;a class=&quot;hikashop_html_add_to_cart_link&quot; href="'.HIKASHOP_LIVE.'index.php?option='.HIKASHOP_COMPONENT.'&ctrl=product&task=updatecart&quantity=1&checkout=1&product_id='.$product_id.'">'.JText::_('ADD_TO_CART').'</a></textarea>
';
	}

	public function getUploadSetting($upload_key, $caller = '') {
		if( !hikamarket::loginVendor() )
			return false;
		if( !hikamarket::acl('product/edit') )
			return false;

		$product_id = hikaInput::get()->getInt('product_id', 0);
		if(empty($upload_key) || (!empty($product_id) && !hikamarket::isVendorProduct($product_id)))
			return false;

		$upload_value = null;
		$upload_keys = array(
			'product_image' => array(
				'type' => 'image',
				'view' => 'form_image_entry',
				'file_type' => 'product',
			),
			'product_file' => array(
				'type' => 'file',
				'view' => 'form_file_entry',
				'file_type' => 'file'
			),
		);

		if(empty($upload_keys[$upload_key]))
			return false;
		$upload_value = $upload_keys[$upload_key];

		$shopConfig = hikamarket::config(false);
		$vendor_id = hikamarket::loadVendor(false, false);

		$options = array();
		if($upload_value['type'] == 'image') {
			$options['upload_dir'] = $shopConfig->get('uploadfolder');
			$options['processing'] = 'resize';
		} else
			$options['upload_dir'] = $shopConfig->get('uploadsecurefolder');

		if($vendor_id > 1)
			$options['sub_folder'] = 'vendor'.$vendor_id.DS;

		$options['max_file_size'] = null;

		$product_type = hikaInput::get()->getCmd('product_type', 'product');
		if(!in_array($product_type, array('product','variant')))
			$product_type = 'product';

		return array(
			'limit' => 1,
			'type' => $upload_value['type'],
			'layout' => 'productmarket',
			'view' => $upload_value['view'],
			'options' => $options,
			'extra' => array(
				'product_id' => $product_id,
				'file_type' => $upload_value['file_type'],
				'product_type' => $product_type
			)
		);
	}

	public function manageUpload($upload_key, &$ret, $uploadConfig, $caller = '') {
		if(empty($ret))
			return;

		$config = hikamarket::config();
		$vendor_id = hikamarket::loadVendor(false, false);

		$product_id = (int)$uploadConfig['extra']['product_id'];
		if(!empty($product_id) && !hikamarket::isVendorProduct($product_id))
			return;

		$file_type = 'product';
		if(!empty($uploadConfig['extra']['file_type']))
			$file_type = $uploadConfig['extra']['file_type'];

		$sub_folder = '';
		if(!empty($uploadConfig['options']['sub_folder']))
			$sub_folder = str_replace('\\', '/', $uploadConfig['options']['sub_folder']);

		if($file_type == 'product')
			$ret->params->product_type = hikaInput::get()->getCmd('product_type', 'product');


		if($caller == 'upload' || $caller == 'addimage') {
			$file = new stdClass();
			$file->file_description = '';
			$file->file_name = $ret->name;
			$file->file_type = $file_type;
			$file->file_ref_id = $product_id;
			$file->file_path = $sub_folder.$ret->name;

			if($file_type != 'product') {
				$file->file_free_download = $config->get('upload_file_free_download', false);
				$file->file_limit = 0;
			}

			if(strpos($file->file_name, '.') !== false) {
				$file->file_name = substr($file->file_name, 0, strrpos($file->file_name, '.'));
			}

			$fileClass = hikamarket::get('shop.class.file');
			$status = $fileClass->save($file, $file_type);

			$ret->file_id = $status;
			$ret->params->file_id = $status;

			if($file_type != 'product') {
				$ret->params->file_free_download = $file->file_free_download;
				$ret->params->file_limit = $file->file_limit;
				$ret->params->file_size = @filesize($uploadConfig['upload_dir'] . @$uploadConfig['options']['sub_folder'] . $file->file_name);
			}

			return;
		}

		if($caller == 'galleryselect') {
			$file = new stdClass();
			$file->file_type = 'product';
			$file->file_ref_id = $product_id;
			$file->file_path = $sub_folder.$ret->name;

			$fileClass = hikamarket::get('shop.class.file');
			$status = $fileClass->save($file);

			$ret->file_id = $status;
			$ret->params->file_id = $status;

			return;
		}
	}

	public function addimage() {
		if( !$this->config->get('frontend_edition',0) ) {
			$this->raiseForbidden();
			return false;
		}
		if( !hikamarket::loginVendor() )
			return false;
		if( !hikamarket::acl('product/edit') )
			return hikamarket::deny('product', JText::sprintf('HIKAM_ACTION_DENY', JText::_('HIKAM_ACT_PRODUCT_EDIT')));

		$ret = $this->saveFile('image');
		if($ret)
			hikaInput::get()->set('layout', 'addimage');
		else
			hikaInput::get()->set('layout', 'image');
		return parent::display();
	}

	public function addfile() {
		if( !$this->config->get('frontend_edition',0) ) {
			$this->raiseForbidden();
			return false;
		}
		if( !hikamarket::loginVendor() )
			return false;
		if( !hikamarket::acl('product/edit') )
			return hikamarket::deny('product', JText::sprintf('HIKAM_ACTION_DENY', JText::_('HIKAM_ACT_PRODUCT_EDIT')));

		$ret = $this->saveFile('file');
		if($ret)
			hikaInput::get()->set('layout', 'addfile');
		else
			hikaInput::get()->set('layout', 'file');

		return parent::display();
	}

	private function saveFile($type) {
		$app = JFactory::getApplication();
		$fileClass = hikamarket::get('shop.class.file');
		$formData = hikaInput::get()->get('data', array(), 'array');

		if(!in_array($type, array('file', 'image')))
			return false;

		$file = new stdClass();
		$file->file_id = hikamarket::getCID('file_id');
		$file->file_type = (($type == 'file') ? 'file' : 'product');

		$fields = array(
			'image' => array('file_id', 'file_name', 'file_description', 'file_ref_id'),
			'file' => array('file_id', 'file_name', 'file_description', 'file_ref_id', 'file_free_download', 'file_limit')
		);
		$formData = hikaInput::get()->get('data', array(), 'array');
		foreach($formData['file'] as $column => $value) {
			if(!in_array($column, $fields[$type]))
				continue;

			hikamarket::secureField($column);
			$file->$column = strip_tags($value);
		}

		if(!empty($file->file_id)) {
			$file->old = $fileClass->get( (int)$file->file_id );

			if(empty($file->file_ref_id) || !isset($file->file_ref_id))
				$file->file_ref_id = (int)$file->old->file_ref_id;

			if((int)$file->old->file_ref_id != $file->file_ref_id || $file->old->file_type != $file->file_type) {
				$app->enqueueMessage('Invalid data', 'error');
				return false;
			}
		}

		$product_id = (int)$file->file_ref_id;
		if(!hikamarket::isVendorProduct($product_id))
			return false;

		$filemode = 'upload';
		if(!empty($formData['filemode']))
			$filemode = $formData['filemode'];

		$vendor_path = '';
		$vendor_id = hikamarket::loadVendor(false, false);
		if($vendor_id > 1) {
			$vendor_path = 'vendor' . $vendor_id;
		}

		if(!empty($file->file_id))
			$filemode = null;

		switch($filemode) {
			case 'upload':
				if(empty($file->file_id)) {
					$ids = $fileClass->storeFiles($file->file_type, $file->file_ref_id, 'files', $vendor_path);
					if(is_array($ids) && !empty($ids)) {
						$file->file_id = array_shift($ids);
						if(isset($file->file_path))
							unset($file->file_path);
					} else {
						return false;
					}
				}
				break;

			case 'path':
			default:
				if(isset($formData['filepath']))
					$file->file_path = trim($formData['filepath']);
				if(isset($formData['file']['file_path']))
					$file->file_path = trim($formData['file']['file_path']);
				break;
		}


		if(isset($file->file_path)) {
			if(strpos($file->file_path, '..') !== false) {
				$app->enqueueMessage('Invalid data', 'error');
				return false;
			}

			if($vendor_id > 1) {
				if(preg_match('#^([a-z]):[\/\\\]{1}#i', $file->file_path))
					$file->file_path = '';

				$file->file_path = ltrim($file->file_path, '/\\');
				$firstChar = substr($file->file_path, 0, 1);

				if(!in_array($firstChar, array('#', '@')) && substr($file->file_path, 0, strlen($vendor_path)) != $vendor_path)
					$file->file_path = $vendor_path . '/' . $file->file_path;
			}

			$shopConfig = hikamarket::config(false);
			$firstChar = substr($file->file_path, 0, 1);
			$isVirtual = in_array($firstChar, array('#', '@'));
			$isLink = (substr($file->file_path, 0, 7) == 'http://' || substr($file->file_path, 0, 8) == 'https://');

			if(!$isLink && !$isVirtual) {
				if($vendor_id > 1 && strpos($file->file_path, ':') !== false) {
					$app->enqueueMessage('File does not exists', 'error');
					return false;
				}

				if($firstChar == '/' || preg_match('#:[\/\\\]{1}#', $file->file_path)) {
					$clean_filename = JPath::clean($file->file_path);
					if(!JFile::exists($clean_filename)) {
						$app->enqueueMessage('Invalid data', 'error');
						return false;
					}

					$secure_path = $shopConfig->get('uploadsecurefolder');
					if((JPATH_ROOT != '') && strpos($clean_filename, JPath::clean(JPATH_ROOT)) !== 0 && strpos($clean_filename, JPath::clean($secure_path)) !== 0) {
						$app->enqueueMessage('Invalid data', 'error');
						return false;
					}
				} else {
					$secure_path = $shopConfig->get('uploadsecurefolder');
					$clean_filename = JPath::clean($secure_path . '/' . $file->file_path);
					if(!JFile::exists($clean_filename) && (JPATH_ROOT == '' || !JFile::exists(JPATH_ROOT . DS . $clean_filename))) {
						$app->enqueueMessage('File does not exists', 'error');
						return false;
					}
				}
			}
		}

		if(isset($file->file_ref_id) && empty($file->file_ref_id)) {
			unset($file->file_ref_id);
		}

		if(isset($file->file_limit)) {
			$limit = (int)$file->file_limit;
			if($limit == 0 && $file->file_limit !== 0 && $file->file_limit != '0') {
				$file->file_limit = -1;
			} else {
				$file->file_limit = $limit;
			}
		}

		JPluginHelper::importPlugin('hikamarket');
		JPluginHelper::importPlugin('hikashop');
		$do = true;
		$app->triggerEvent('onHikaBeforeFileSave', array(&$file, &$do));

		if(!$do)
			return false;

		if(empty($file->file_id) && (empty($file->file_path) || empty($file->file_ref_id))) {
			$app->enqueueMessage('Invalid data', 'error');
			return false;
		}

		if(empty($file->file_id) && $file->file_type == 'file' && empty($file->file_name)) {
			$app->enqueueMessage('Please provide a file name', 'error');
			return false;
		}

		if(isset($file->file_path) && empty($file->file_path))
			unset($file->file_path);
		if(isset($file->file_name) && empty($file->file_name) && $file->file_type == 'file')
			unset($file->file_path);

		$status = $fileClass->save($file);
		if(empty($file->file_id) && $status) {
			$file->file_id = $status;
		}
		if(!empty($file->file_id))
			hikaInput::get()->set('cid', $file->file_id);

		$app->triggerEvent('onHikaAfterFileSave', array(&$file));
		return $status;
	}

	public function editprice() {
		$product_id = hikaInput::get()->getInt('product_id', 0);
		$variant_product_id = hikaInput::get()->getInt('variant_product_id', 0);
		while(ob_get_level())
			@ob_end_clean();

		if(!hikamarket::loginVendor() || !$this->config->get('frontend_edition', 0)) {
			header('HTTP/1.1 403 Forbidden');
			exit;
		}
		if(empty($variant_product_id) && (!hikamarket::acl('product/edit/price') || !hikamarket::isVendorProduct($product_id))) {
			echo '';
			exit;
		}
		if(!empty($variant_product_id) && (!hikamarket::acl('product/variant/price') || !hikamarket::isVendorProduct($variant_product_id))) {
			echo '';
			exit;
		}

		hikaInput::get()->set('layout', 'form_price_entry');
		parent::display();
		exit;
	}

	public function getPrice() {
		if(!hikamarket::loginVendor() || !$this->config->get('frontend_edition', 0)) {
			header('HTTP/1.1 403 Forbidden');
			exit;
		}

		$price = hikaInput::get()->getString('price');
		$price = hikamarket::toFloat($price);
		$tax_id = hikaInput::get()->getInt('tax_id', 0);
		$product_id = hikaInput::get()->getInt('product_id', 0);
		$conversion = hikaInput::get()->getInt('conversion');
		$currencyClass = hikamarket::get('shop.class.currency');

		if($tax_id < 0 && $product_id > 0) {
			$productClass = hikamarket::get('shop.class.product');
			$product = $productClass->get($product_id);
			if($product)
				$tax_id = $product->product_tax_id;
		}

		while(ob_get_level())
			@ob_end_clean();

		$shopConfig = hikamarket::config(false);
		$main_tax_zone = explode(',', $shopConfig->get('main_tax_zone',1346) );
		if(count($main_tax_zone) && !empty($tax_id) && !empty($price) && !empty($main_tax_zone)) {
			if($conversion) {
				echo $currencyClass->getUntaxedPrice($price, array_shift($main_tax_zone), $tax_id, 5);
			} else {
				echo $currencyClass->getTaxedPrice($price, array_shift($main_tax_zone), $tax_id, 5);
			}
		} else {
			echo $price;
		}
		exit;
	}

	public function getTree() {
		while(ob_get_level())
			@ob_end_clean();

		if(!hikamarket::loginVendor() || !$this->config->get('frontend_edition',0)) {
			echo '[]';
			exit;
		}

		$config = hikamarket::config();

		$category_id = hikaInput::get()->getInt('category_id', 0);
		$displayFormat = hikaInput::get()->getString('displayFormat', '');
		$allvendors = hikaInput::get()->getInt('allvendors', 0);
		$variants = hikaInput::get()->getInt('variants', 0);
		$search = hikaInput::get()->getString('search', null);

		$vendor_chroot_category = (int)$config->get('vendor_chroot_category', 0);
		if(empty($category_id) && !empty($vendor_chroot_category) && $vendor_chroot_category == 1) {
			$vendor_id = hikamarket::loadVendor();
			$vendorClass = hikamarket::get('class.vendor');
			$category_id = $vendorClass->getRootCategory($vendor_id);
		}
		if(empty($category_id) && !empty($vendor_chroot_category))
			$category_id = (int)$config->get('vendor_root_category', 0);

		if(!hikamarket::isVendorCategory($category_id, null, true)) {
			echo '[]';
			exit;
		}

		$nameboxType = hikamarket::get('type.namebox');
		$options = array(
			'start' => $category_id,
			'displayFormat' => $displayFormat,
			'allvendors' => $allvendors,
			'variants' => $variants
		);
		$ret = $nameboxType->getValues($search, 'product', $options);
		if(!empty($ret)) {
			echo json_encode($ret);
			exit;
		}
		echo '[]';
		exit;
	}
	public function findTree() { return $this->getTree(); }

	public function upload() {
		if( !hikamarket::loginVendor() || !$this->config->get('frontend_edition',0) ) {
			header('HTTP/1.1 403 Forbidden');
			exit;
		}

		JSession::checkToken() || die('Invalid Token');

		$config = hikamarket::config();
		$shopConfig = hikamarket::config(false);
		$product_id = hikaInput::get()->getInt('product_id', 0);
		$file_type = hikaInput::get()->getString('file_type', 'product');

		if($file_type == 'product') {
			if(!hikamarket::acl('product/edit/images/upload')) {
				header('HTTP/1.1 403 Forbidden');
				return hikamarket::deny('product', JText::sprintf('HIKAM_ACTION_DENY', JText::_('HIKAM_ACT_PRODUCT_EDIT')));
			}
		} else {
			if(!hikamarket::acl('product/edit/files/upload')) {
				header('HTTP/1.1 403 Forbidden');
				return hikamarket::deny('product', JText::sprintf('HIKAM_ACTION_DENY', JText::_('HIKAM_ACT_PRODUCT_EDIT')));
			}
		}

		if($file_type == 'product') {
			$options = array(
				'upload_dir' => $shopConfig->get('uploadfolder'),
				'upload_url' => '',
				'type' => $file_type
			);
		} else {
			$options = array(
				'upload_dir' => $shopConfig->get('uploadsecurefolder'),
				'upload_url' => '',
				'type' => $file_type
			);
		}

		$options['upload_url'] = ltrim(JPath::clean(html_entity_decode($options['upload_dir'])),DS);
		$options['upload_url'] = str_replace(DS,'/',rtrim($options['upload_url'],DS).DS);
		$app = JFactory::getApplication();
		if(hikamarket::isAdmin()) {
			$options['upload_url'] = '../'.$options['upload_url'];
		} else {
			$options['upload_url'] = rtrim(JURI::base(true),'/').'/'.$options['upload_url'];
		}

		$options['upload_dir'] = rtrim(JPath::clean(html_entity_decode($options['upload_dir'])), DS.' ').DS;
		if(!preg_match('#^([A-Z]:)?/.*#',$options['upload_dir'])) {
			if(substr($options['upload_dir'], 0, 1) != '/' || !is_dir($options['upload_dir'])) {
				$options['upload_dir'] = JPath::clean(HIKASHOP_ROOT.DS.trim($options['upload_dir'], DS.' ').DS);
			}
		}

		$vendor_id = hikamarket::loadVendor(false, false);
		if($vendor_id > 0) {
			$options['upload_dir'] .= 'vendor'.$vendor_id.DS;
			$options['upload_url'] .= 'vendor'.$vendor_id.'/';
		}

		$uploadHelper = hikamarket::get('helper.upload');
		$ret = $uploadHelper->process($options);
		if($ret !== false && empty($ret->error)) {
			$helperImage = null;
			$fileType = 'file';
			if($file_type == 'product') {
				$fileType = 'image';
				$helperImage = hikamarket::get('shop.helper.image');
			}

			foreach($ret as &$r) {
				if(!empty($r->error))
					continue;

				$file = new stdClass();
				$file->file_description = '';
				$file->file_name = $r->name;
				$file->file_type = $file_type;
				$file->file_ref_id = $product_id;
				$file->file_path = $r->name;
				if($file_type != 'product')
					$file->file_free_download = $config->get('upload_file_free_download', false);

				if($vendor_id > 0)
					$file->file_path = 'vendor'.$vendor_id.'/'.$r->name; // Not "DS" here

				if(strpos($file->file_name, '.') !== false) {
					$file->file_name = substr($file->file_name, 0, strrpos($file->file_name, '.'));
				}

				$fileClass = hikamarket::get('shop.class.file');
				$status = $fileClass->save($file);
				if(empty($file->file_id)) {
					$file->file_id = $status;
				}
				$r->file_id = $status;
				$r->html = '';
				$js = '';

				if($status) {
					if($file_type == 'product') {
						$helperImage->resizeImage($file->file_path, 'image', null, null);
						$helperImage->display($file->file_path, false, '', '', '', 100, 100);
						$r->thumbnail_url = $helperImage->uploadFolder_url_thumb;

						$params = new stdClass();
						$params->product_id = $product_id;
						$params->file_id = $status;
						$params->file_path = $file->file_path;
						$params->file_name = $file->file_name;
						$params->product_type = hikaInput::get()->getCmd('product_type', 'product');

						$r->html = hikamarket::getLayout('productmarket', 'form_image_entry', $params, $js);
					} else {
						$params = new stdClass();
						$params->product_id = $product_id;
						$params->file_id = $status;
						$params->file_name = $file->file_name;
						$params->file_path = $file->file_path;
						$params->file_free_download = $file->file_free_download;
						$params->file_limit = -1;
						$params->file_size = @filesize($options['upload_dir'] . $file->file_name);
						$r->html = hikamarket::getLayout('productmarket', 'form_file_entry', $params, $js);
					}
				}

				unset($r->path);
				unset($r);
			}
		}

		while(ob_get_level())
			@ob_end_clean();
		echo json_encode($ret);
		exit;
	}
}
