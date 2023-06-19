<?php
/**
 * @package	HikaShop for Joomla!
 * @version	4.7.3
 * @author	hikashop.com
 * @copyright	(C) 2010-2023 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><html>
	<head>
<?php
	$doc = JFactory::getDocument();
	$head = $doc->loadRenderer('head');
	if(HIKASHOP_J30)
		echo $head->render('');
	else
		echo $head->render();
?>
	</head>
	<body>
		<?php echo hikashop_display(JText::_('RESSOURCE_NOT_ALLOWED'),'error'); ?>
	</body>
</html>
<?php exit;
