<?php
/**
 * @package     Falang for Joomla!
 * @author      Stéphane Bouey <stephane.bouey@faboba.com> - http://www.faboba.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright   Copyright (C) 2010-2017. Faboba.com All rights reserved.
 */

// No direct access to this file
defined('_JEXEC') or die;
$document = JFactory::getDocument();
$document->addStyleSheet("modules/mod_falang/style/mod_falang_emundus.css");

// mod_falang helper set display to false because of the parameter layout=edit
// we need to set it to true to switch language
foreach ($list as $key=> $language) {
    $list[$key]->display = true;
}
?>
<form name="lang" method="post" action="<?php echo htmlspecialchars(JUri::current()); ?>">
    <?php if (!$params->get('advanced_dropdown',0)) : ?>
    	<select class="inputbox" onchange="document.location.replace(this.value);" >
            <?php foreach($list as $language):?>
                <?php if ($language->display) { ?>
                    <option value="<?php echo $language->link;?>" <?php echo !empty($language->active) ? 'selected="selected"' : ''?>><?php echo $language->title_native;?></option>
                <?php } else { ?>
                    <option disabled="disabled" style="opacity: 0.5" value="<?php echo $language->link;?>" <?php echo !empty($language->active) ? 'selected="selected"' : ''?>><?php echo $language->title_native;?></option>
                <?php } ?>
            <?php endforeach; ?>
        </select>
    <?php else : ?>

        <script type="application/javascript">
            jQuery(function() {
                var speed = 150;
                jQuery('div.advanced-dropdown').hover(
                    function()
                    {
                        jQuery(this).find('ul').filter(':not(:animated)').slideDown({duration: speed});
                    },
                    function()
                    {
                        jQuery(this).find('ul').filter(':not(:animated)').slideUp({duration: speed});
                    }
                );
            });
        </script>

        <!-- >>> [PAID] >>> -->
    <?php foreach($list as $language):?>
        <?php if ($language->active) :?>
        <a href="javascript:;" class="langChoose">
            <div class="langChoose__img_label">
                <?php if ($params->get('image', 1)):?>
                    <?php echo JHtml::_('image', $imagesPath.$language->image.'.'.$imagesType, $language->title_native, array('title'=>$language->title_native), $relativePath);?>
                <?php endif; ?>
                <?php if ($params->get('show_name', 1)):?>
                    <?php echo $params->get('full_name', 1) ? $language->title_native : strtoupper($language->sef);?>
                <?php endif; ?>
            </div>
            <span class="caret"></span>
        </a>
    <?php endif; ?>
    <?php endforeach;?>
        <ul class="<?php echo $params->get('inline', 1) ? 'lang-inline' : 'lang-block';?>" style="display: none">
            <?php foreach($list as $language):?>
                <?php if ($params->get('show_active', 0) || !$language->active):?>
                    <li class="<?php echo $language->active ? 'lang-active' : '';?>" dir="<?php echo JLanguage::getInstance($language->lang_code)->isRTL() ? 'rtl' : 'ltr' ?>">
                        <?php if ($language->display) { ?>
                            <a href="<?php echo $language->link;?>">
                                <?php if ($params->get('image', 1)):?>
                                    <?php echo JHtml::_('image', $imagesPath.$language->image.'.'.$imagesType, $language->title_native, array('title'=>$language->title_native), $relativePath);?>
                                <?php endif; ?>
                                <?php if ($params->get('show_name', 1)):?>
                                    <?php echo $params->get('full_name', 1) ? $language->title_native : strtoupper($language->sef);?>
                                <?php endif; ?>
                                <?php if($language->active){?> <i class="fa fa-check lang_checked"></i> <?php } ?>
                            </a>
                        <?php } else { ?>
                            <?php if ($params->get('image', 1)):?>
                                <?php echo JHtml::_('image', $imagesPath.$language->image.'.'.$imagesType, $language->title_native, array('title'=>$language->title_native,'style'=>'opacity:0.5'), $relativePath);?>
                            <?php else : ?>
                                <?php if ($params->get('show_name', 1)):?>
                                    <?php echo $params->get('full_name', 1) ? $language->title_native : strtoupper($language->sef);?>
                                <?php endif; ?>
                                <?php if($language->active){?> <i class="fa fa-check lang_checked"></i> <?php } ?>
                            <?php endif; ?>
                        <?php } ?>
                    </li>
                <?php endif;?>
            <?php endforeach;?>
        </ul>
        <!-- <<< [PAID] <<< -->


    <?php endif; ?>
</form>

