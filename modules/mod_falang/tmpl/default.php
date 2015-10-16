<?php
/**
 * @package		Joomla.Site
 * @subpackage	mod_falang
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
JHtml::_('stylesheet', 'mod_falang/template.css', array(), true);

//add alternate tag
$doc = JFactory::getDocument();
$default_lang = JComponentHelper::getParams('com_languages')->get('site', 'en-GB');
$current_lang = JFactory::getLanguage()->getTag();

$sef = JFactory::getApplication()->getCfg('sef');

$remove_default_prefix = 0;
$filter_plugin = JPluginHelper::getPlugin('system', 'languagefilter');
if (!empty($filter_plugin)) {
    $filter_plugin_params = new JRegistry($filter_plugin->params);
    $remove_default_prefix = $filter_plugin_params->get('remove_default_prefix','0');
}

//add an alterante by language
foreach($list as $language) {
    if ($sef == '1') {
        if (($language->lang_code == $default_lang) && $remove_default_prefix == '1') {
            $link = JURI::base() . substr($language->link, 1);
            $link = preg_replace('|/' . $language->sef . '/|', '/', $link, 1);
            //remove last slash for default language
            $link = rtrim($link, "/");
            $doc->addCustomTag('<link rel="alternate" href="' . $link . '" hreflang="' . $language->lang_code . '" />');
        } else {
            $link = JURI::base() . substr($language->link, 1);
            $doc->addCustomTag('<link rel="alternate" href="' . $link . '" hreflang="' . $language->lang_code . '" />');
        }

    } else {
        $doc->addCustomTag('<link rel="alternate" href="' . JURI::base() . substr($language->link, 1) . '" hreflang="' . $language->lang_code . '" />');
    }
}

?>
<div class="mod-languages<?php echo $moduleclass_sfx ?>">
<?php if ($headerText) : ?>
	<div class="pretext"><p><?php echo $headerText; ?></p></div>
<?php endif; ?>

<?php if ($params->get('dropdown',1)) : ?>
    <form name="lang" method="post" action="<?php echo htmlspecialchars(JUri::current()); ?>">
	<select class="inputbox" onchange="document.location.replace(this.value);" >
	<?php foreach($list as $language):?>
        <?php if ($language->display) { ?>
		    <option value="<?php echo $language->link;?>" <?php echo !empty($language->active) ? 'selected="selected"' : ''?>><?php echo $language->title_native;?></option>
        <?php } else { ?>
            <option disabled="disabled" style="opacity: 0.5" value="<?php echo $language->link;?>" <?php echo !empty($language->active) ? 'selected="selected"' : ''?>><?php echo $language->title_native;?></option>
        <?php } ?>
            <?php endforeach; ?>
	</select>
	</form>
<?php else : ?>
	<ul class="<?php echo $params->get('inline', 1) ? 'lang-inline' : 'lang-block';?>">
	<?php foreach($list as $language):?>
        <!-- >>> [PAID] >>> -->
        <?php if ($params->get('show_active', 0) || !$language->active):?>
            <li class="<?php echo $language->active ? 'lang-active' : '';?>" dir="<?php echo JLanguage::getInstance($language->lang_code)->isRTL() ? 'rtl' : 'ltr' ?>">
            <?php if ($language->display) { ?>
                <a href="<?php echo $language->link;?>">
                    <?php if ($params->get('image', 1)):?>
                        <?php echo JHtml::_('image', $imagesPath.$language->image.'.'.$imagesType, $language->title_native, array('title'=>$language->title_native), $relativePath);?>
                    <?php else : ?>
                        <?php echo $params->get('full_name', 1) ? $language->title_native : strtoupper($language->sef);?>
                    <?php endif; ?>
                </a>
            <?php } else { ?>
                <?php if ($params->get('image', 1)):?>
                    <?php echo JHtml::_('image', $imagesPath.$language->image.'.'.$imagesType, $language->title_native, array('title'=>$language->title_native,'style'=>'opacity:0.5'), $relativePath);?>
                <?php else : ?>
                    <?php echo $params->get('full_name', 1) ? $language->title_native : strtoupper($language->sef);?>
                <?php endif; ?>
            <?php } ?>
			</li>
		<?php endif;?>
        <!-- <<< [PAID] <<< -->
        
	<?php endforeach;?>
	</ul>
<?php endif; ?>

<?php if ($footerText) : ?>
	<div class="posttext"><p><?php echo $footerText; ?></p></div>
<?php endif; ?>
</div>
