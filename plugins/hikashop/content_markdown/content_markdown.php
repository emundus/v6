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
class plgHikashopContent_markdown extends JPlugin
{
	protected $plugin_name = 'content_markdown';

	public function __construct(&$subject, $config) {
		parent::__construct($subject, $config);

		if(isset($this->params))
			return;

		$plugin = JPluginHelper::getPlugin('hikashop', $this->plugin_name);
		$this->params = new JRegistry(@$plugin->params);
	}

	public function onHkContentParserLoad(&$parsers) {
		if(isset($parsers['markdown']))
			return;

		$parsers['markdown'] = array(
			'name' => 'CONTENTPARSER_MARKDOWN',
			'editor' => false,
			'plugin' => $this->plugin_name
		);
	}

	public function onHkContentParse(&$content, $type) {
		if($type != 'markdown')
			return null;
		$content = $this->parse($content);
	}

	public function parse($content) {
		include_once dirname(__FILE__).DS.'lib'.DS.'parsedown'.DS.'parsedown.php';

		$content = htmlentities($content, ENT_COMPAT, 'UTF-8');

		$parsedown = new Parsedown();
		return $parsedown->text($content);
	}
}
