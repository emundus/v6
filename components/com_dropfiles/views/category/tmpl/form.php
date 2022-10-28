<?php
/**
 * Dropfiles
 *
 * We developed this code with our hearts and passion.
 * We hope you found it useful, easy to understand and to customize.
 * Otherwise, please feel free to contact us at contact@joomunited.com *
 *
 * @package   Dropfiles
 * @copyright Copyright (C) 2013 JoomUnited (http://www.joomunited.com). All rights reserved.
 * @copyright Copyright (C) 2013 Damien Barrère (http://www.crac-design.com). All rights reserved.
 * @license   GNU General Public License version 2 or later; http://www.gnu.org/licenses/gpl-2.0.html
 */

// No direct access.
defined('_JEXEC') || die;

$dropfiles_params = JComponentHelper::getParams('com_dropfiles');
$themes = array();
$path_dropfilespluginbase = JPATH_ADMINISTRATOR . '/components/com_dropfiles/classes/dropfilesPluginBase.php';
JLoader::register('DropfilesPluginBase', $path_dropfilespluginbase);
$defaultThemes = DropfilesPluginBase::getDropfilesThemes();
$availableThemes = DropfilesBase::getDropfilesThemes();
foreach ($availableThemes as $theme) {
    if (!in_array($theme['id'], $defaultThemes)) {
        $plg_id = JPluginHelper::getPlugin('dropfilesthemes', $theme['id'])->id;
        $themes[] = strtolower($theme['name']);
    }
}
$prefix = 'jform_params_';
$clonemargin = array();
$margint = array();
$marginr = array();
$marginb = array();
$marginl = array();
$sliderinit = array();
$subcate = array();
$categorytitle = array();
$breadcrumb = array();
$foldertree = array();
$downloadpopup = array();
$columns = array();
$bgdownload = array();
$colordownload = array();
$categoriesposition = array();
$clonecategory = array();
foreach ($themes as $name) {
    //CATEGORY SECTION OF CLONE THEME
    //    margin
    $margint[] = $prefix . $name . '_margintop';
    $marginr[] = $prefix . $name . '_marginright';
    $marginb[] = $prefix . $name . '_marginbottom';
    $marginl[] = $prefix . $name . '_marginleft';
    //   sliderinit
    $sliderinit[] = $prefix . $name . '_sliderinit';
    //   subcategories
    $subcate[] = $prefix . $name . '_showsubcategories';
    //   category title
    $categorytitle[] = $prefix . $name . '_showcategorytitle';
    //   breadcrumb
    $breadcrumb[] = $prefix . $name . '_showbreadcrumb';
    //   folder tree
    $foldertree[] = $prefix . $name . '_showfoldertree';
    //   download popup
    $downloadpopup[] = $prefix . $name . '_download_popup';

    //FILE SECTION OF CLONE THEME
    //   columns
    $columns[] = $prefix . $name . '_columns';
    //   bgdownload
    $bgdownload[] = $prefix . $name . '_bgdownloadlink';
    //   colordownload
    $colordownload[] = $prefix . $name . '_colordownloadlink';
    //   categoriesposition
    $categoriesposition[] = $prefix . $name . '_showcategoriesposition';
}
$clonemargin = array_merge($margint, $marginr, $marginb, $marginl);
$clonecategory = array_merge($subcate, $categorytitle, $breadcrumb, $foldertree);

if ((int) $dropfiles_params->get('loadthemecategory', 1) === 0) : ?>
    <style type="text/css">
        .hide_params_box {
            display: none;
        }
    </style>
<?php endif; ?>
<?php
if ($this->form) {
    $fieldSet = $this->form->getFieldset();
    if (!empty($fieldSet)) {
        ?>
        <form class="dropfilesparams">
            <div class="fieldset-settings-container">
                <button class="btn dropfiles-save-submit" type="submit"><?php echo JText::_('COM_DROPFILES_JS_SAVE_SETTINGS'); ?></button>
                <div class="category-visibility-ordering-section">
                    <?php echo $this->form->getInput('id');
                    foreach ($fieldSet as $name => $field) : ?>
                        <?php if (in_array($field->id, array('jform_access', 'jform_params_ordering',
                            'jform_params_orderingdir', 'jform_params_usergroup'))) : ?>
                            <?php echo $field->label; ?>
                            <span class="paraminput input-block-level"><?php echo $field->input; ?></span>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
                <?php if ((int) $dropfiles_params->get('add_category_owner', 0) === 1 || (int) $dropfiles_params->get('restrictfile', 0) === 1) : ?>
                    <div id="permission-settings" class="well category-section permission-settings">
                        <legend><?php echo JText::_('COM_DROPFILES_LAYOUT_DROPFILES_PERMISSION_SETTINGS'); ?></legend>
                        <div class="control-group ">
                            <div class="controls">
                                <?php foreach ($fieldSet as $name => $field) : ?>
                                    <?php if (in_array($field->id, array('jform_params_canview', 'jform_created_user_id'))) : ?>
                                        <?php echo $field->label; ?>
                                        <span class="paraminput input-block-level"><?php echo $field->input; ?></span>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif;?>

                <div class="hide_params_box">
                    <div id="category-layout" class="well category-section category-layout">
                        <legend><?php echo JText::_('COM_DROPFILES_LAYOUT_DROPFILES_CATEGORY_LAYOUTS'); ?></legend>
                        <div class="control-group ">
                            <div class="controls ">
                                <?php foreach ($fieldSet as $name => $field) : ?>
                                    <?php if (in_array($field->id, array('jform_params_marginleft', 'jform_params_margintop',
                                        'jform_params_marginright', 'jform_params_marginbottom',
                                        'jform_params_ggd_marginleft', 'jform_params_ggd_margintop', 'jform_params_ggd_marginright',
                                        'jform_params_ggd_marginbottom', 'jform_params_sliderinit'))
                                        || in_array($field->id, $clonemargin)
                                        || in_array($field->id, $sliderinit)) : ?>
                                        <?php echo $field->label; ?>
                                        <span class="paraminput input-block-level <?php echo $field->id ?>">
                                                                <?php echo $field->input; ?>
                                            </span>
                                        <!--<span class="help-block"><?php echo $field->description; ?></span>-->
                                    <?php endif; ?>
                                <?php endforeach; ?>
                                <?php foreach ($fieldSet as $name => $field) : ?>
                                    <?php if (in_array($field->id, array('jform_params_showcategorytitle',
                                        'jform_params_showbreadcrumb', 'jform_params_showfoldertree', 'jform_params_showsubcategories',
                                        'jform_params_table_showsubcategories', 'jform_params_table_showcategorytitle',
                                        'jform_params_table_showbreadcrumb', 'jform_params_table_showfoldertree',
                                        'jform_params_ggd_showsubcategories', 'jform_params_ggd_showcategorytitle',
                                        'jform_params_ggd_showbreadcrumb', 'jform_params_ggd_showfoldertree',
                                        'jform_params_ggd_download_popup', 'jform_params_tree_showsubcategories',
                                        'jform_params_tree_showcategorytitle', 'jform_params_tree_download_popup'
                                        ))
                                    || in_array($field->id, $clonecategory)
                                    || in_array($field->id, $downloadpopup)) : ?>
                                        <div class="ju-container">
                                            <?php echo $field->label; ?>
                                            <div class="ju-switch-button">
                                                <label class="switch">
                                                    <input type="checkbox" name="<?php echo $field->id ?>" id="<?php echo $field->id; ?>">
                                                    <span class="dropfiles-slider"></span>
                                                </label>
                                                <span class="paraminput input-block-level <?php echo $field->id ?>">
                                                                <?php echo $field->input; ?>
                                            </span>
                                            </div>
                                            <!--<span class="help-block"><?php echo $field->description; ?></span>-->
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <div id="file-layout" class="well category-section file-layout">
                        <legend><?php echo JText::_('COM_DROPFILES_LAYOUT_DROPFILES_FILE_LAYOUTS'); ?></legend>
                        <div class="control-group ">
                            <div class="controls">
                                <?php foreach ($fieldSet as $name => $field) : ?>
                                    <?php if (!in_array($field->id, array('jform_id', 'jform_access', 'jform_params_ordering',
                                        'jform_params_orderingdir', 'jform_params_canview', 'jform_created_user_id',
                                        'jform_params_usergroup', 'jform_params_marginleft', 'jform_params_margintop',
                                        'jform_params_marginright', 'jform_params_marginbottom', 'jform_params_showcategorytitle',
                                        'jform_params_showbreadcrumb', 'jform_params_showfoldertree', 'jform_params_showsubcategories',
                                        'jform_params_table_showsubcategories', 'jform_params_table_showcategorytitle',
                                        'jform_params_table_showbreadcrumb', 'jform_params_table_showfoldertree',
                                        'jform_params_ggd_marginleft', 'jform_params_ggd_margintop', 'jform_params_ggd_marginright',
                                        'jform_params_ggd_marginbottom', 'jform_params_ggd_showsubcategories',
                                        'jform_params_ggd_showcategorytitle', 'jform_params_ggd_showbreadcrumb',
                                        'jform_params_ggd_showfoldertree', 'jform_params_ggd_download_popup',
                                        'jform_params_tree_showsubcategories', 'jform_params_tree_showcategorytitle',
                                        'jform_params_tree_download_popup', 'jform_params_sliderinit'))
                                        && !in_array($field->id, $clonemargin)
                                        && !in_array($field->id, $clonecategory)
                                        && !in_array($field->id, $sliderinit)
                                        && !in_array($field->id, $downloadpopup)) : ?>
                                        <?php if (!in_array($field->id, array('jform_params_columns', 'jform_params_bgdownloadlink', 'jform_params_colordownloadlink',
                                            'jform_params_ggd_bgdownloadlink', 'jform_params_ggd_colordownloadlink', 'jform_params_table_bgdownloadlink',
                                            'jform_params_table_colordownloadlink', 'jform_params_table_showcategoriesposition', 'jform_params_tree_bgdownloadlink', 'jform_params_tree_colordownloadlink'))
                                            && !in_array($field->id, $columns)
                                            && !in_array($field->id, $bgdownload)
                                            && !in_array($field->id, $colordownload)
                                            && !in_array($field->id, $categoriesposition)) : ?>
                                            <div class="ju-container">
                                                <?php echo $field->label; ?>
                                                <div class="ju-switch-button">
                                                    <label class="switch">
                                                        <input type="checkbox" name="<?php echo $field->id ?>" id="<?php echo $field->id; ?>">
                                                        <span class="dropfiles-slider"></span>
                                                    </label>
                                                    <span class="paraminput input-block-level <?php echo $field->id ?>">
                                                                    <?php echo $field->input; ?>
                                                        </span>
                                                </div>
                                                <!--<span class="help-block"><?php echo $field->description; ?></span>-->
                                            </div>
                                        <?php else :?>
                                            <?php echo $field->label; ?>
                                            <span class="paraminput input-block-level <?php echo $field->id ?>">
                                                    <?php echo $field->input; ?>
                                                </span>
                                            <!--<span class="help-block"><?php echo $field->description; ?></span>-->
                                        <?php endif; ?>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <span class="paraminput"><?php echo JHtml::_('form.token'); ?></span>
                <button class="btn dropfiles-save-submit" type="submit"><?php echo JText::_('COM_DROPFILES_JS_SAVE_SETTINGS'); ?></button>
            </div>
        </form>
    <?php }
}
?>
