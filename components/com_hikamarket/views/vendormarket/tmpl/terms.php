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
if(!empty($this->vendor)) {
?>
<h1><?php echo $this->vendor->vendor_name; ?></h1>
<p><?php
	echo $this->vendor->vendor_terms;
?></p>
<?php
} else {
	echo empty($this->article) ? JText::_('TERMS_UNDEFINED') : JHTML::_('content.prepare', $this->article);
}
