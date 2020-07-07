<?php
// No direct access
defined('_JEXEC') or die;
$document = JFactory::getDocument();
$document->addStyleSheet("modules/mod_emundus_switch_funnel/style/mod_emundus_switch_funnel.css" );
?>
<?php if ($path) :?>
    <a class="switch-link" href="index.php">
        <i class="folder-switch folder open icon"></i>
    </a>
<?php endif; ?>
<?php if (!$path) :?>
        <a class="switch-link-setting" href="<?php echo $lang . '/' . $route->alias ?>">
            <i class="setting-switch setting open icon"></i>
        </a>
<?php endif; ?>
