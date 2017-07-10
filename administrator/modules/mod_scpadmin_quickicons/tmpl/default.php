<?php
/**
* @ author Jose A. Luque
* @ Copyright (c) 2011 - Jose A. Luque
* @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die;

// Securitycheck Pro Info Module Iconmoon fonts
/*$iconmoon_css = DIRECTORY_SEPARATOR . "media" . DIRECTORY_SEPARATOR . "com_securitycheckpro" . DIRECTORY_SEPARATOR . "fonts" . DIRECTORY_SEPARATOR . "style.css";
$document = JFactory::getDocument();
$document->addStyleSheet($iconmoon_css);*/

$html = JHtml::_('icons.buttons', $buttons);
?>
<?php if (!empty($html)): ?>
	<div class="j-links-groups">
		<h2 class="nav-header">Securitycheck Pro Info Module</h2>
			<ul class="j-links-group nav nav-list">
				<?php echo $html;?>
			</ul>
	</div>
<?php endif;?>
