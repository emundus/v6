<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.4.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2020 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class plgHikashopWaitlist_notify extends JPlugin
{
	var $message = '';

	function __construct(&$subject, $config) {
		parent::__construct($subject, $config);
	}

	function onHikashopCronTrigger(&$messages) {
		$pluginsClass = hikashop_get('class.plugins');
		$plugin = $pluginsClass->getByName('hikashop','waitlist_notify');
		if(empty($plugin->params['period'])){
			$plugin->params['period'] = 7200;
		}
		$this->period = $plugin->params['period'];
		if(!empty($plugin->params['last_cron_update']) && $plugin->params['last_cron_update']+$plugin->params['period']>time()){
			return true;
		}
		$plugin->params['last_cron_update']=time();
		$pluginsClass->save($plugin);
		$this->checkWaitlists();
		if(!empty($this->message)){
			$messages[] = $this->message;
		}
		return true;
	}

	function checkWaitlists() {
		$config =& hikashop_config();
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();

		$waitlist_send_limit = $config->get('product_waitlist_send_limit', 5);
		$query='SELECT a.*, b.* FROM '.hikashop_table('waitlist').' AS a '.
			' INNER JOIN '.hikashop_table('product').' AS b ON (a.product_id = b.product_id)'.
			' LEFT JOIN '.hikashop_table('product').' AS c ON (c.product_id = b.product_parent_id)'.
			' WHERE (b.product_quantity > 0) OR (b.product_quantity = -1 AND b.product_type = '.$db->Quote('main').') '.
			'   OR (b.product_type = '.$db->Quote('variant').' AND b.product_quantity = -1 AND (c.product_quantity > 0 OR c.product_quantity = -1))'.
			' ORDER BY a.product_id ASC, a.date ASC;';
		$db->setQuery($query);
		$notifies = $db->loadObjectList();
		if(empty($notifies)) {
			$this->message = 'Waitlist notifies checked (empty)';
			$app->enqueueMessage($this->message);
			return true;
		}

		$cpt = 0;
		$infos = null;
		$sends = array();
		foreach($notifies as $notify) {
			if( !isset($sends[$notify->product_id]) ) {
				$sends[$notify->product_id] = array();
			}

			$c = count($sends[$notify->product_id]);
			if( $c >= $notify->product_quantity && $notify->product_quantity >= 0 )
				continue;
			if( $c >= $waitlist_send_limit && $waitlist_send_limit > 0 )
				continue;

			if(!empty($notify->language)) {
				$reload = $this->_setLocale($notify->language);
			}

			if($notify->product_type == 'variant') {
				if(!isset($productClass))
					$productClass = hikashop_get('class.product');

				$db->setQuery('SELECT * FROM '.hikashop_table('variant').' AS a LEFT JOIN '.hikashop_table('characteristic') .' AS b ON a.variant_characteristic_id=b.characteristic_id WHERE a.variant_product_id='.(int)$notify->product_id.' ORDER BY a.ordering');
				$notify->characteristics = $db->loadObjectList();

				$parentProduct = $productClass->get((int)$notify->product_parent_id);
				$productClass->checkVariant($notify, $parentProduct);
			}

			if(!isset($mailClass))
				$mailClass = hikashop_get('class.mail');

			$sends[$notify->product_id][] = $notify->waitlist_id;

			$mail = $mailClass->get('waitlist_notification', $notify);
			$mail->subject = JText::sprintf($mail->subject, HIKASHOP_LIVE);
			$mail->dst_email = $notify->email;
			$mail->dst_name = $notify->name;
			$mailClass->sendMail($mail);

			$query = 'DELETE FROM '.hikashop_table('waitlist').' WHERE waitlist_id = '.(int)$notify->waitlist_id.';';
			$db->setQuery($query);
			$db->execute();

			$cpt++;
		}

		$this->message = 'Waitlist notifies checked (' . (int)$cpt . ')';
		$app->enqueueMessage($this->message);

		if(!empty($this->oldLocale))
			$this->_setLocale($this->oldLocale);
		return true;
	}

	function _setLocale($locale){
		$config = JFactory::getConfig();
		$oldLang = $config->get('language');

		if($oldLang == $locale)
			return false;

		if(!isset($this->oldLocale))
			$this->oldLocale = $oldLang;
		$config->set('language',$locale);

		$debug = $config->get('debug');

		$lang = JFactory::getLanguage();
		$override_path = hikashop_getLanguagePath(JPATH_ROOT).DS.'overrides'.DS.$locale.'.override.ini';
		$lang->load(HIKASHOP_COMPONENT, JPATH_SITE, $locale, true );
		if(file_exists($override_path))
			hikashop_loadTranslationFile($override_path);

		return true;
	}
}
