<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.4
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="item-page" itemscope="" itemtype="https://schema.org/Article">
	<div class="page-header">
		<h2 itemprop="headline">
			<?php echo $this->article->title; ?>
		</h2>
	</div>
	<div itemprop="articlebody">
<?php
echo empty($this->article->text) ? JText::_('PRIVACY_CONSENT_ARTICLE_UNDEFINED') : JHTML::_('content.prepare', $this->article->text);
?>
	</div>
</div>
