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
$class_centpc = $this->params->get('google_credentials', '') ? '' : 'centpc';
?>
    <div id="mycategories">
        <div id="df-panel-toggle">
            <span class="icon-arrow-left-2"></span>
        </div>
        <?php if ($this->canDo->get('core.create')) : ?>
            <div id="newcategory"
                 class="btn-group button-primary btn-categories <?php echo $class_centpc; ?>">
                <a class="btn btn-default" href="">
                    <i class="material-icons material-icons-new_folder">create_new_folder</i>
                    <?php echo JText::_('COM_DROPFILES_LAYOUT_DROPFILES_NEW_CATEGORY'); ?>
                </a>
                <?php if (($this->params->get('google_credentials', '') !== '') ||
                    ($this->params->get('dropbox_token', '') !== '') ||
                    ($this->params->get('onedriveCredentials', '') !== '') ||
                    ($this->params->get('onedriveBusinessKey', '') !== '' && $this->params->get('onedriveBusinessSecret', '') !== '' &&
                        isset($this->params['onedriveBusinessConnected']) && (int) $this->params['onedriveBusinessConnected'] === 1)) : ?>
                    <ul class="dropdown-menu pull-right">
                        <?php if ($this->params->get('google_credentials', '')) { ?>
                            <li><a href="#" class="googleCat"><i class="google-drive-icon"></i>
                                    <?php echo JText::_('COM_DROPFILES_LAYOUT_DROPFILES_NEW_GOOGLEDRIVE'); ?>
                                </a>
                            </li>
                        <?php } ?>
                        <?php if ($this->params->get('dropbox_token', '') !== '') { ?>
                            <li>
                                <a href="#" class="dropboxcat">
                                    <i class="dropbox-icon"></i>
                                    <?php echo JText::_('Dropbox'); ?>
                                </a>
                            </li>
                        <?php } ?>
                        <?php if ($this->params->get('onedriveCredentials', '') !== '') { ?>
                            <li>
                                <a href="#" class="onedrivecat">
                                    <i class="onedrive-icon"></i>
                                    <?php echo JText::_('OneDrive'); ?>
                                </a>
                            </li>
                        <?php } ?>
                        <?php if ($this->params->get('onedriveBusinessKey', '') !== '' && $this->params->get('onedriveBusinessSecret', '') !== '' &&
                            isset($this->params['onedriveBusinessConnected']) && (int) $this->params['onedriveBusinessConnected'] === 1) { ?>
                            <li><a href="#" class="onedrivebusinesscat">
                                    <i class="onedrivebusiness-icon"></i>
                                    <?php echo JText::_('OneDrive Business'); ?>
                                </a>
                            </li>
                        <?php } ?>
                    </ul>
                <?php endif; ?>
            </div>

            <?php if ($this->params->get('google_credentials') !== null || $this->params->get('dropbox_token', '') !== '' || $this->params->get('onedriveCredentials', '') !== '' ||
                ($this->params->get('onedriveBusinessKey', '') !== '' && $this->params->get('onedriveBusinessSecret', '') !== '' &&
                    isset($this->params['onedriveBusinessConnected']) && (int) $this->params['onedriveBusinessConnected'] === 1)) : ?>
            <div class="dropfiles-sync-buttons">
                <?php if ($this->params->get('google_credentials') !== null) : ?>
                    <button href="#" id="btn-sync-gg" data-loading-text="Syncing with Google Drive"
                            class="btn btn-default btn-sync-google">
                        <i class="dropfiles-cloud-icon dropfiles-svg-icon-sync-googledrive"></i>
                    </button>
                <?php endif; ?>
                <?php if ($this->params->get('dropbox_token', '') !== '') { ?>
                    <button href="#" id="btn-sync-dropbox" data-loading-text="Syncing with Dropbox"
                            class="btn btn-default btn-sync-dropbox">
                        <i class="dropfiles-cloud-icon dropfiles-svg-icon-sync-dropbox"></i>
                    </button>
                <?php } ?>
                <?php if ($this->params->get('onedriveCredentials', '') !== '') { ?>
                    <button href="#" id="btn-sync-onedrive" data-loading-text="Syncing with OneDrive"
                            class="btn btn-onedrive btn-sync-onedrive">
                        <i class="dropfiles-cloud-icon dropfiles-svg-icon-sync-onedrive"></i>
                    </button>
                <?php } ?>
                <?php if ($this->params->get('onedriveBusinessKey', '') !== '' && $this->params->get('onedriveBusinessSecret', '') !== '' &&
                    isset($this->params['onedriveBusinessConnected']) && (int) $this->params['onedriveBusinessConnected'] === 1) { ?>
                    <button href="#" id="btn-sync-onedrive-business" title="Syncing with OneDrive Business" data-loading-text="Syncing with OneDrive Business"
                            class="btn btn-onedrive-business btn-sync-onedrive-business">
                        <i class="dropfiles-cloud-icon dropfiles-svg-icon-sync-onedrive"></i>
                    </button>
                <?php } ?>
            </div>
            <?php endif; ?>

        <?php endif; ?>
        <div class="nested dd">
            <ol id="categorieslist" class="dd-list nav bs-docs-sidenav2 ">
                <?php
                $content = '';
                if (!empty($this->categories)) {
                    $previouslevel = 1;
                    $categoreisCount = count($this->categories);
                    for ($index = 0; $index < $categoreisCount; $index++) {
                        if ($index + 1 !== $categoreisCount) {
                            $nextlevel = $this->categories[$index + 1]->level;
                        } else {
                            $nextlevel = 0;
                        }
                        if ($this->catid_active === $this->categories[$index]->id) {
                            $key = 0;
                        } else {
                            $key = 1;
                        }
                        $content .= openItem($this->categories[$index], $key, $this->canDo);
                        if ($nextlevel > $this->categories[$index]->level) {
                            $content .= openList();
                        } elseif ($nextlevel === $this->categories[$index]->level) {
                            $content .= closeItem();
                        } else {
                            $c = '';
                            $c .= closeItem();
                            $c .= closeList();
                            $content .= str_repeat($c, $this->categories[$index]->level - $nextlevel);
                        }
                        $previouslevel = $this->categories[$index]->level;
                    }
                }
                echo $content;
                ?>
            </ol>
            <input type="hidden" id="categoryToken" name="<?php echo JSession::getFormToken(); ?>"/>
        </div>
        <div id="dropfiles-hamburger">
            <div class="material-leftright"></div>
            <span><?php echo JText::_('COM_DROPFILES_LAYOUT_DROPFILES_CATEGORY'); ?></span>
        </div>
    </div>


<?php
/**
 * Open item
 *
 * @param object $category Category
 * @param string $key      Key
 * @param object $canDo    Can do
 *
 * @return string
 */
function openItem($category, $key, $canDo)
{
    switch ($category->type) {
        case 'googledrive':
            $icon = '<i class="google-drive-icon-white"></i> ';
            break;
        case 'dropbox':
            $icon = '<i class="dropbox-icon-white"></i> ';
            break;
        case 'onedrive':
            $icon = '<i class="onedrive-icon-white"></i> ';
            break;
        case 'onedrivebusiness':
            $icon = '<i class="onedrive-icon-white"></i> ';
            break;
        default:
            $icon = '<i class="zmdi zmdi-folder dropfiles-folder"></i>';
            break;
    }

    if (isset($category->disable) && $category->disable) {
        $disable = ' disable-cat ';
        $item_id_disable = 'data-item-disable="' . $category->id . '"';
        $dd_handle = '';
    } else {
        $disable = ' not-disable-cat ';
        $item_id_disable = '';
        $dd_handle = ' dd-handle ';
    }

    $return = '<li class="' . $disable . ' dd-item dd3-item ' . ($key ? '' : 'active') . '" data-id-category="';
    $return .= $category->id . '" data-author="' . $category->created_user_id . '" ' . $item_id_disable . ' >';
    $return .= '<div class="' . $disable . $dd_handle . ' dd3-handle">' . $icon . '</div>';
    $return .= '<div class="dd-content dd3-content ' . $dd_handle . $disable . '">';
    if ($canDo->get('core.delete') && !isset($category->disable)) {
        $return .= '<a class="trash" title="Delete"><i class="icon-trash"></i></a>';
    }
    if ($canDo->get('core.edit') || $canDo->get('core.edit.own') && !isset($category->disable)) {
        $return .= '<a class="edit" title="Edit"><i class="icon-edit"></i></a>';
    }
    $return .= '<a href="" class="t' . $disable . '">
                <span class="title">' . $category->title . '</span>
            </a>';
    $return .= '</div>';
    return $return;
}

/**
 * Close item
 *
 * @return string
 */
function closeItem()
{
    return '</li>';
}

/**
 * Item content
 *
 * @param object $category Category
 *
 * @return string
 */
function itemContent($category)
{
    if (isset($category->disable) && $category->disable) {
        $disable = ' disable-cat ';
        $dd_handle = '';
    } else {
        $disable = '';
        $dd_handle = ' dd-handle ';
    }

    return '<div class="' . $disable . $dd_handle . ' dd3-handle">
                <i class="zmdi zmdi-folder dropfiles-folder"></i>
            </div>
    <div class="dd-content dd3-content dd-handle"
        <i class="icon-chevron-right"></i>
        <a class="edit" title="Edit"><i class="icon-edit"></i></a>
        <a class="trash" title="Delete"><i class="icon-trash"></i></a>
        <a href="" class="t">
            <span class="title">' . $category->title . '</span>
        </a>
    </div>';
}

/**
 * Open List
 *
 * @return string
 */
function openList()
{
    return '<ol class="dd-list">';
}

/**
 * Close List
 *
 * @return string
 */
function closeList()
{
    return '</ol>';
}
