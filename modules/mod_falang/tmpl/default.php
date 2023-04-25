<?php
/**
 * @package     Falang for Joomla!
 * @author      Stéphane Bouey <stephane.bouey@faboba.com> - http://www.faboba.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright   Copyright (C) 2010-2017. Faboba.com All rights reserved.
 */

// No direct access to this file
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Helper\ModuleHelper;

HTMLHelper::_('stylesheet', 'mod_falang/template.css', array('version' => 'auto', 'relative' => true));

//add alternate tag
$doc = Factory::getDocument();
$default_lang = ComponentHelper::getParams('com_languages')->get('site', 'en-GB');
$current_lang = Factory::getLanguage()->getTag();
$languagesCode = LanguageHelper::getLanguages('lang_code');

$sef = Factory::getApplication()->getCfg('sef');

/* Support of alternate tag bassed on language filter parameters  */
$remove_default_prefix = 0;
//set default in case of language filter is not use (ex : joomsef)
$alternate_meta    = 1;
$xdefault          = 1;
$xdefault_language = $default_lang;

$filter_plugin = PluginHelper::getPlugin('system', 'languagefilter');
if (!empty($filter_plugin)) {
    $filter_plugin_params  = new Registry($filter_plugin->params);
    $remove_default_prefix = $filter_plugin_params->get('remove_default_prefix','0');
    $alternate_meta        = $filter_plugin_params->get('alternate_meta', 1);
    $xdefault              = $filter_plugin_params->get('xdefault', 1);
    $xdefault_language     = $filter_plugin_params->get('xdefault_language', $default_lang);
}



// hack to fix the fact that $language->link already contains the rootpath of the joomla site
// ex falang3/en for http//localhost/falang3

$uri_base = substr(URI::base(false), 0,strlen(URI::base(false))-strlen(URI::base(true)));
foreach($list as $language) {
    if ($alternate_meta && $language->display == '1')
    {
        if ($sef == '1')
        {
            $link = $uri_base . substr($language->link, 1);
            if (($language->lang_code == $default_lang) && $remove_default_prefix == '1')
            {
                $link = preg_replace('|/' . $language->sef . '/|', '/', $link, 1);
                //remove last slash for default language
                $link = rtrim($link, "/");
                $doc->addCustomTag('<link rel="alternate" href="' . $link . '" hreflang="' . $language->sef . '" />');
            }
            else
            {
                $doc->addCustomTag('<link rel="alternate" href="' . $link . '" hreflang="' . $language->sef . '" />');
            }


        }
        else
        {
            $doc->addCustomTag('<link rel="alternate" href="' . $language->link . '" hreflang="' . $language->sef . '" />');
        }

        if ($xdefault)
        {
            $loc_xdefault_language = ($xdefault_language == 'default') ? $default_lang : $xdefault_language;

            if (($loc_xdefault_language == $language->lang_code) && isset($languagesCode[$loc_xdefault_language]))
            {
                // Use a custom tag because addHeadLink is limited to one URI per tag
                if ($sef == '1')
                {
                    $doc->addCustomTag('<link rel="alternate" href="' . $link . '"  hreflang="x-default" />');
                }
                else
                {
                    $doc->addCustomTag('<link rel="alternate" href="' . $language->link . '"  hreflang="x-default" />');
                }
            }

        }
    }

}

?>

<?php
// Support of language domain from yireo
$yireo_plugin = PluginHelper::getPlugin('system', 'languagedomains');
if (!empty($yireo_plugin)) {
    foreach($list as $language):
        if (empty($language->link) || in_array($language->link, array('/', 'index.php'))) $language->link = '/?lang='.$language->sef;
    endforeach;
}
?>


<div class="mod-languages<?php echo $moduleclass_sfx ?> <?php echo ($params->get('dropdown', 1) && $params->get('advanced_dropdown', 1)) ? ' advanced-dropdown' : '';?>">
<?php if ($headerText) : ?>
	<div class="pretext"><p><?php echo $headerText; ?></p></div>
<?php endif; ?>

<?php if ($params->get('dropdown',1)) : ?>
    <?php require ModuleHelper::getLayoutPath('mod_falang', $params->get('layout', 'default') . '_dropdown'); ?>
<?php else : ?>
    <?php require ModuleHelper::getLayoutPath('mod_falang', $params->get('layout', 'default') . '_list'); ?>
<?php endif; ?>

<?php if ($footerText) : ?>
	<div class="posttext"><p><?php echo $footerText; ?></p></div>
<?php endif; ?>
</div>
