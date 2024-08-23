<?php
/**
 * @package     Falang for Joomla!
 * @author      Stéphane Bouey <stephane.bouey@faboba.com> - http://www.faboba.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright   Copyright (C) 2010-2017. Faboba.com All rights reserved.
 */

// No direct access to this file
defined('_JEXEC') or die;

require_once (JPATH_SITE.'/components/com_emundus/helpers/cache.php');
$hash = EmundusHelperCache::getCurrentGitHash();

$document = JFactory::getDocument();
$document->addStyleSheet("modules/mod_falang/style/mod_falang_emundus.css?".$hash);

// mod_falang helper set display to false because of the parameter layout=edit
// we need to set it to true to switch language
foreach ($list as $key=> $language) {
    $list[$key]->display = true;
}
?>
<form name="lang" method="post" action="<?php echo htmlspecialchars(JUri::current()); ?>" style="margin-bottom: 0">
    <?php if (!$params->get('advanced_dropdown',0)) : ?>
        <?php foreach($list as $language):?>
            <?php if (!empty($language->active)) : ?>
            <button type="button" id="mod_falang_emundus___button" class="mod_falang_emundus___button" onclick="displayOtherLanguages()">
                <?php if ($params->get('show_name', 1)):?>
                    <?php echo $params->get('full_name', 1) ? $language->title_native : strtoupper($language->sef);?>
                <?php endif; ?>
                <span class="material-icons" style="font-size: 30px">expand_more</span>
            </button>
            <?php endif; ?>
        <?php endforeach; ?>

    <div id="mod_falang_emundus___other_languages" class="mod_falang_emundus___other_languages em-border-neutral-400" style="display: none">
        <?php foreach($list as $language):?>
        <span <?php if (!empty($language->active)) : ?>class="mod_falang_emundus___other_languages_selected"<?php endif; ?>>
            <a href="<?= $language->link; ?>"><?php echo $language->title_native; ?></a>
        </span>
        <?php endforeach; ?>
    </div>
    <?php else : ?>

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

<script>
    function displayOtherLanguages(){
        let other_languages = document.getElementById('mod_falang_emundus___other_languages');
        if(other_languages.style.display === 'none'){
            other_languages.style.display = 'flex';
        } else {
            other_languages.style.display = 'none';
        }
    }

    document.addEventListener('click', function (e) {
        let other_languages = document.getElementById('mod_falang_emundus___other_languages');
        let clickInsideModule = false;

        if(other_languages.style.display === 'flex') {
            e.composedPath().forEach((pathElement) => {
                if (pathElement.id == "mod_falang_emundus___other_languages" || pathElement.id == "mod_falang_emundus___button") {
                    clickInsideModule = true;
                }
            });

            if (!clickInsideModule) {
                other_languages.style.display = 'none';
            }
        }
    });
</script>

