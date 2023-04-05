<?php
/**
 * @ author Jose A. Luque
 * @ Copyright (c) 2011 - Jose A. Luque
 *
 * @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 */

defined('_JEXEC') or die;

use Joomla\CMS\Uri\Uri as JUri;

$html = JHtml::_('icons.buttons', $buttons);
?>
<?php 
if (version_compare(JVERSION, '3.20', 'lt')) {
    ?>
    <?php if (!empty($html)) : ?>
        <div class="j-links-groups">
            <h2 class="nav-header">Securitycheck Pro Info Module</h2>
                <ul class="j-links-group nav nav-list">
        <?php echo $html;?>
                </ul>
        </div>
    <?php endif;?>
<?php } else { ?>
    <link href="<?php echo JURI::root(); ?>media/com_securitycheckpro/new/vendor/font-awesome/css/fontawesome.css" rel="stylesheet" type="text/css">
    <link href="<?php echo JURI::root(); ?>media/com_securitycheckpro/new/vendor/font-awesome/css/fa-solid.css" rel="stylesheet" type="text/css">
    <link href="<?php echo JURI::root(); ?>media/com_securitycheckpro/stylesheets/cpanelui.css" rel="stylesheet" type="text/css">
    
    <?php if (!empty($html)) : ?>
    <div class="card-body">
        <nav class="quick-icons px-3 pb-3" aria-label="Securitycheck Pro Info Module" tabindex="-1">
            <ul class="nav flex-wrap">
        <?php echo $html; ?>
            </ul>    
        </nav>
    </div>
    <?php endif; ?>
<?php } ?>

