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

// Load the JavaScript and css
JHtml::_('behavior.keepalive');
JHtml::_('jquery.framework');
$doc = JFactory::getDocument();
if (DropfilesBase::isJoomla40()) {
    JHtml::_('behavior.core');
    JHtml::_('bootstrap.framework');

    $doc->addScript(JURI::root() . 'components/com_dropfiles/assets/js/chosen.jquery.min.js');
    $doc->addStyleSheet(JURI::root() . 'components/com_dropfiles/assets/css/chosen.css');
    $doc->addScript(JURI::root() . 'components/com_dropfiles/assets/js/jquery.minicolors.min.js');
    $doc->addStyleSheet(JURI::root() . 'components/com_dropfiles/assets/css/jquery.minicolors.css');
    DropfilesHelper::mediaFieldAssets();
} else { // Joomla 3
    JHtml::_('behavior.framework', true);
    JHtml::_('behavior.modal', 'a.modal');
    JHtml::_('behavior.colorpicker');
    JHtml::_('behavior.calendar');
    JHtml::_('script', 'jui/chosen.jquery.min.js', false, true, false, false);
    JHtml::_('stylesheet', 'jui/chosen.css', false, true);
    $doc->addScript(JURI::root() . 'components/com_dropfiles/assets/js/fielduser.min.js');
    $doc->addScript(JURI::root() . 'components/com_dropfiles/assets/js/fieldmultiuser.js');
}

jimport('joomla.application.component.helper');
$params = JComponentHelper::getParams('com_dropfiles');
if ($params->get('custom_icon', 0)) {
    JHtml::_('script', 'media/mediafield.min.js', array('version' => 'auto', 'relative' => true));
}

$app = JFactory::getApplication();
$function = $app->input->get('function', 'jInsertCategory');

JText::script('COM_DROPFILES_JS_DELETE');
JText::script('COM_DROPFILES_JS_EDIT');
JText::script('COM_DROPFILES_JS_CANCEL');
JText::script('COM_DROPFILES_JS_OK');
JText::script('COM_DROPFILES_JS_CONFIRM');
JText::script('COM_DROPFILES_JS_SAVE');
JText::script('COM_DROPFILES_JS_SAVED');
JText::script('COM_DROPFILES_JS_DROP_FILES_HERE');
JText::script('COM_DROPFILES_JS_USE_UPLOAD_BUTTON');
JText::script('COM_DROPFILES_JS_ADD_REMOTE_FILE');
JText::script('COM_DROPFILES_JS_ARE_YOU_SURE');
JText::script('COM_DROPFILES_JS_BROWSER_NOT_SUPPORT_HTML5');
JText::script('COM_DROPFILES_JS_TOO_ANY_FILES');
JText::script('COM_DROPFILES_CTRL_FILES_UPLOAD_FILE_SUCCESS');
JText::script('COM_DROPFILES_CTRL_FILES_WRONG_FILE_EXTENSION');
JText::script('COM_DROPFILES_JS_FILE_TOO_LARGE');
JText::script('COM_DROPFILES_JS_ONLY_IMAGE_ALLOWED');
JText::script('COM_DROPFILES_JS_DBLCLICK_TO_EDIT_TITLE');
JText::script('COM_DROPFILES_JS_WANT_DELETE_CATEGORY');
JText::script('COM_DROPFILES_JS_SELECT_FILES');
JText::script('COM_DROPFILES_JS_IMAGE_PARAMETERS');
JText::script('COM_DROPFILES_JS_X_FILES_IMPORTED');
JText::script('COM_DROPFILES_JS_WAIT_UPLOADING');
JText::script('COM_DROPFILES_JS_ARE_YOU_SURE_DELETE');
JText::script('COM_DROPFILES_JS_FILE_MOVED');
JText::script('COM_DROPFILES_JS_FILE_COPIED');
JText::script('COM_DROPFILES_JS_FILES_MOVED');
JText::script('COM_DROPFILES_JS_FILES_COPIED');
JText::script('COM_DROPFILES_JS_FILES_REMOVED');
JText::script('COM_DROPFILES_JS_LINK_COPIED');
JText::script('COM_DROPFILES_JS_NO_FILES_SELETED');
JText::script('COM_DROPFILES_JS_NO_FILES_COPIED_CUT');
JText::script('COM_DROPFILES_DEFAULT_FRONT_COLUMNS');
JText::script('COM_DROPFILES_JS_REMOTE_FILE_TITLE');
JText::script('COM_DROPFILES_JS_REMOTE_FILE_URL');
JText::script('COM_DROPFILES_JS_REMOTE_FILE_REMOTE_URL');
JText::script('COM_DROPFILES_JS_REMOTE_FILE_TYPE');
JText::script('COM_DROPFILES_JS_CATEGORY_ORDER');
JText::script('COM_DROPFILES_JS_CATEGORY_SAVED');
JText::script('COM_DROPFILES_JS_CATEGORY_CREATED');
JText::script('COM_DROPFILES_JS_CATEGORY_RENAMED');
JText::script('COM_DROPFILES_JS_CATEGORY_REMOVED');
JText::script('COM_DROPFILES_JS_PLEASE_CREATE_A_FOLDER');
JText::script('COM_DROPFILES_MULTI_CATEGORY_FILE');
JText::script('COM_DROPFILES_MULTI_CATEGORY_EDIT_ORIGINAL_FILE');

$doc->addScriptDeclaration('gcaninsert=' . ($app->input->getBool('caninsert', false) ? 'true' : 'false') . ';');
$doc->addScriptDeclaration('e_name="' . $app->input->getString('e_name') . '";');

$collapse = DropfilesBase::getParam('catcollapsed', 0);
$allowedext_list = '7z,ace,bz2,dmg,gz,rar,tgz,zip,csv,doc,docx,html,key,keynote,odp,ods,odt,pages,pdf,pps,ppt,'
    . 'pptx,rtf,tex,txt,xls,xlsx,xml,bmp,exif,gif,ico,jpeg,jpg,png,psd,tif,tiff,aac,aif,aiff,alac,amr,au,cdda,'
    . 'flac,m3u,m4a,m4p, mid, mp3, mp4, mpa, ogg, pac, ra, wav, wma, 3gp,asf,avi,flv,m4v,mkv,mov,mpeg,mpg,'
    . 'rm,swf,vob,wmv';
$declaration =
    "if(typeof(Dropfiles)=='undefined'){"
    . '     Dropfiles={};'
    . '}'
    . 'Dropfiles.can = {};'
    . 'Dropfiles.can.config=' . (int)$this->canDo->get('core.admin') . ';'
    . 'Dropfiles.can.create=' . (int)$this->canDo->get('core.create') . ';'
    . 'Dropfiles.can.edit=' . (int)$this->canDo->get('core.edit') . ';'
    . 'Dropfiles.can.editown=' . (int)$this->canDo->get('core.edit.own') . ';'
    . 'Dropfiles.can.delete=' . (int)$this->canDo->get('core.delete') . ';'
    . 'Dropfiles.author=' . (int)JFactory::getUser()->id . ';'
    . 'Dropfiles.selected = {};'
    . 'Dropfiles.selected.access = false;'
    . 'Dropfiles.selected.ordering = false;'
    . 'Dropfiles.selected.orderingdir = false;'
    . 'Dropfiles.selected.usergroup = false;'
    . 'Dropfiles.collapse=' . ($collapse ? 'true' : 'false') . ';'
    . "Dropfiles.version='" . DropfilesComponentHelper::getVersion() . "';"
    . 'Dropfiles.maxfilesize = ' . $params->get('maxinputfile', 10) . ';'
    . 'Dropfiles.chunkSize = ' . DropfilesComponentHelper::getTrunkSize() . ';'
    . 'Dropfiles.addRemoteFile = ' . (int)$params->get('addremotefile', 0) . ';'
    . 'Dropfiles.indexgoogle = ' . (int)$params->get('indexgoogle', 1) . ';'
    . "Dropfiles.ajaxurl = '" . JUri::root() . "';"
    . "Dropfiles.categoryrestriction = '" . $params->get('categoryrestriction', 'accesslevel') . "';"
    . "Dropfiles.allowedext = '" . $params->get('allowedext', $allowedext_list) . "';";

$doc->addScriptDeclaration($declaration);
?>
<div id="navigation" style="text-align: center">
    <a href="javascript:void(0);" onclick="window.history.go(-1);" class="back-link">
        <?php echo JText::_('COM_DROPFILES_LAYOUT_MANAGEFILES_BACK'); ?>
    </a>
    <a href="<?php echo JUri::root(); ?>" class="home-link"><?php echo JText::_('COM_DROPFILES_LAYOUT_MANAGEFILES_HOMEPAGE'); ?>
    </a>
</div>
<?php
$joomla_ver = '';
if (DropfilesBase::isJoomla30()) {
    $joomla_ver = 'joomla30';
}
if (DropfilesBase::isJoomla40()) {
    $joomla_ver = 'joomla4';
}
?>
<div id="mybootstrap" class="<?php echo $joomla_ver; ?> managebootstrap">
    <?php echo $this->loadTemplate('cats'); ?>
    <div id="pwrapper">
        <div id="wpreview">
            <div class="dropfiles-btn-toolbar" id="dropfiles-toolbar">
                <div class="btn-wrapper">
                    <button onclick="Joomla.submitbutton('files.movefile');" class="btn btn-small" id="dropfiles-cut">
                        <span class="icon-scissors"></span>
                        <?php echo JText::_('COM_DROPFILES_CUT'); ?></button>
                </div>
                <div class="btn-wrapper">
                    <button onclick="Joomla.submitbutton('files.copyfile');" class="btn btn-small" id="dropfiles-copy">
                        <span class="icon-save-copy"></span>
                        <?php echo JText::_('COM_DROPFILES_COPY'); ?></button>
                </div>
                <div class="btn-wrapper">
                    <button onclick="Joomla.submitbutton('files.paste');" class="btn btn-small" id="dropfiles-paste">
                        <span class="icon-archive"></span>
                        <?php echo JText::_('COM_DROPFILES_PATSE'); ?></button>
                </div>
                <div class="btn-wrapper">
                    <button onclick="Joomla.submitbutton('files.delete')" class="btn btn-small" id="dropfiles-delete">
                        <span class="icon-trash"></span>
                        <?php echo JText::_('COM_DROPFILES_DELETE_FILES'); ?></button>
                </div>
                <div class="btn-wrapper">
                    <button onclick="Joomla.submitbutton('files.download')" class="btn btn-small"
                            id="dropfiles-download">
                        <span class="icon-download"></span>
                        <?php echo JText::_('COM_DROPFILES_DOWNLOAD_FILES'); ?></button>
                </div>
                <div class="btn-wrapper">
                    <button onclick="Joomla.submitbutton('files.uncheck')" class="btn btn-small" id="dropfiles-uncheck">
                        <span class="icon-remove"></span>
                        <?php echo JText::_('COM_DROPFILES_UNCHECK'); ?></button>
                </div>
            </div>
            <div class="dropfiles-filter-file">
                <div class="dropfiles-search-file hide">
                    <select id="dropfiles_filter_catid" class="chzn-select" name="catid">
                        <option value=""><?php echo ' ' . JText::_('COM_DROPFILES_SEARCH_ALL_CATEGORIES'); ?></option>
                        <?php
                        if (count($this->allCategories) > 0) {
                            foreach ($this->allCategories as $key => $category) {
                                echo '<option  data-type="' . $category->type . '" value="' . $category->id . '">'
                                    . str_repeat('-', ($category->level - 1)) . ' ' . $category->title
                                    . '</option>';
                            }
                        }
                        ?>

                    </select>
                    <input type="text" class="dropfiles-search-file-input">
                    <a href="#" class="btn btn-primary button-primary dropfiles-btn-search">
                        <?php echo JText::_('COM_DROPFILES_SEARCH_SEARCH_BUTTON'); ?>
                    </a>
                    <a href="#" class="btn dropfiles-btn-exit-search">
                        <?php echo JText::_('COM_DROPFILES_SEARCH_EXIT_SEARCH'); ?>
                    </a>
                </div>

                <i class="material-icons dropfiles-iconsearch restablesearch">search</i>
            </div>
            <div id="preview"></div>
        </div>
        <input type="hidden" name="id_category" value=""/>
    </div>
    <div id="rightcol" class="">
        <?php if ($app->input->getBool('caninsert')) : ?>
            <a id="insertcategory" class="btn btn-flat-active" href=""
               onclick="if (window.parent)
                       {
                       window.parent.jInsertEditorText(
                       insertCategory(),
                       '<?php echo JFactory::getApplication()->input->getVar('e_name'); ?>'
                       );
                       window.parent.SqueezeBox.close();
                       }">
                <?php echo JText::_('COM_DROPFILES_LAYOUT_DROPFILES_INSERT_CATEGORY'); ?>
            </a>
            <a id="insertfile" class="btn btn-flat-active" style="display: none;" href=""
               onclick="if (window.parent)
                       {
                       window.parent.jInsertEditorText(
                       insertFile(),
                       '<?php echo JFactory::getApplication()->input->getVar('e_name'); ?>'
                       );
                       window.parent.SqueezeBox.close();
                       }">
                <?php echo JText::_('COM_DROPFILES_LAYOUT_DROPFILES_INSERT_FILE'); ?>
            </a>
        <?php endif; ?>

        <div>
            <div class="categoryblock">
                <?php if ($this->canDo->get('core.edit') || $this->canDo->get('core.edit.own')) : ?>
                    <div class="themesblock">
                        <div id="theme-section" class="well category-section theme-section">
                            <legend class="main-legend"><?php echo JText::_('COM_DROPFILES_LAYOUT_DROPFILES_MAIN_SETTINGS'); ?></legend>
                            <div id="themeselect" class="control-group ">
                                <label class="control-label" for="themeselect"><?php echo JText::_('COM_DROPFILES_LAYOUT_DROPFILES_THEME'); ?></label>
                                <div class="controls">
                                    <?php
                                    $themes = DropfilesBase::getDropfilesThemes();
                                    foreach ($themes as $theme) :?>
                                        <a class="themebtn <?php echo strtolower($theme['name']); ?>" href="#"
                                           data-theme="<?php echo strtolower($theme['name']); ?>"
                                           title="<?php echo $theme['name']; ?>">
                                            <p><?php echo $theme['name']; ?></p>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <div id="galleryparams" class="well gallery-section ">
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($this->importFiles && $this->canDo->get('core.admin')) : ?>
                    <div class="well">
                        <h4 class="import-file-lb"><?php echo JText::_('COM_DROPFILES_LAYOUT_DROPFILES_IMPORT'); ?></h4>
                        <div id="filesimport">
                            <div id="jao"></div>
                            <div class="center category-btn-footer">
                                <button class="btn btn-large" id="importFilesBtn" type="button">
                                    <?php echo JText::_('COM_DROPFILES_LAYOUT_DROPFILES_IMPORT_BTN'); ?>
                                </button>
                                <button class="btn btn-mini" id="selectAllImportFiles" type="button">
                                    <?php echo JText::_('COM_DROPFILES_LAYOUT_DROPFILES_IMPORT_SELECTALL_BTN'); ?>
                                </button>
                                <button class="btn btn-mini" id="unselectAllImportFiles" type="button">
                                    <?php echo JText::_('COM_DROPFILES_LAYOUT_DROPFILES_IMPORT_UNSELECTALL_BTN'); ?>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <?php if ($this->canDo->get('core.edit') || $this->canDo->get('core.edit.own')) : ?>
                <div class="fileblock" style="display: none;">
                    <div class="well fileparams-list">
                        <div id="fileparams">

                        </div>
                    </div>
                    <div id="fileversion">
                        <div class="well">
                            <h4><?php echo JText::_('COM_DROPFILES_LAYOUT_DROPFILES_VERSION'); ?></h4>
                            <div id="versions_content"></div>
                            <div id="dropbox_version">
                                <div class="upload">
                                    <span class="message">
                                        <?php echo JText::_('COM_DROPFILES_JS_DROP_FILES_HERE'); ?>
                                    </span>

                                    <a href="" id="upload_button_version" class="btn btn-large btn-primary">
                                        <?php echo JText::_('COM_DROPFILES_JS_SELECT_FILES'); ?>
                                    </a>
                                </div>
                                <div class="progress progress-striped active hide">
                                    <div class="bar" style="width: 0;"></div>
                                </div>
                            </div>
                            <input class="hide" type="file" id="upload_input_version">
                            <div class="clr"></div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<form id="adminDropfiles" name="adminDropfiles">
    <?php
    $f_created_time = $this->fieldSet['jform_created_time'];
    echo '<div style="display: none">' . $f_created_time->renderField() . '</div>';
    ?>
</form>
<script>
    jQuery(document).ready(function ($) {
        $.ajax({
            url: "index.php?option=com_dropfiles&task=categories.getAllTags",
            type: "POST"
        }).done(function (data) {

        });
    })
</script>
