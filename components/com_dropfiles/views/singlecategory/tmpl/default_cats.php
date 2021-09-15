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
$centpc = $this->params->get('google_credentials', '') ? '' : 'centpc';

?>
    <div id="mycategories" class="hidden">
        <div id="df-panel-toggle"><span class="icon-arrow-left-2"></span></div>
        <?php if ($this->canDo->get('core.create')) : ?>
            <div id="newcategory"
                 class="btn-group button-primary btn-categories withSyncCloud <?php echo $centpc; ?>">
                <a class="btn btn-default" href=""><i class="icon-plus">
                        <i class="material-icons material-icons-new_folder">create_new_folder</i></i>
                    <?php echo JText::_('COM_DROPFILES_LAYOUT_DROPFILES_NEW_CATEGORY'); ?>
                </a>
                <?php if ($this->params->get('google_credentials', '')) : ?>
                    <ul class="dropdown-menu pull-right">
                        <li><a href="#" class="googleCat"><i class="google-drive-icon"></i>
                                <?php echo JText::_('COM_DROPFILES_LAYOUT_DROPFILES_NEW_GOOGLEDRIVE'); ?>
                            </a></li>
                    </ul>
                <?php endif; ?>
            </div>
            <?php if ($this->params->get('google_credentials') !== null) : ?>
                <button href="#" id="btn-sync-gg" data-loading-text="Syncing with Google Drive"
                        class="btn btn-default btn-sync-google">
                    <i class="icon-refresh"></i>&nbsp;Sync with Google Drive
                </button>
            <?php endif; ?>
        <?php endif; ?>
        <div class="nested dd">
            <ol id="categorieslist" class="dd-list nav bs-docs-sidenav2 ">
                <?php
                $content = '';
                if (!empty($this->categories)) {
                    $previouslevel = 1;
                    $categoriesCount = count($this->categories);
                    for ($index = 0; $index < $categoriesCount; $index++) {
                        if ($index + 1 !== $categoriesCount) {
                            $nextlevel = $this->categories[$index + 1]->level;
                        } else {
                            $nextlevel = 0;
                        }
                        $content .= openItem($this->categories[$index], $index, $this->canDo);
                        if ($nextlevel > $this->categories[$index]->level) {
                            $content .= openlist();
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
    $icon = '';
    if ($category->type === 'googledrive') {
        $icon = '<i class="google-drive-icon-white"></i> ';
    }
    $return = '<li class="dd-item dd3-item ' . ($key ? '' : 'active') . '" data-id-category="';
    $return .= $category->id . '" data-author="' . $category->created_user_id . '">
        <div class="dd-handle dd3-handle"><i class="zmdi zmdi-folder dropfiles-folder"></i></div>
        <div class="dd-content dd3-content dd-handle">';
    if ($canDo->get('core.edit') || $canDo->get('core.edit.own')) {
        $return .= '<a class="edit" title="Edit"><i class="icon-edit"></i></a>';
    }
    if ($canDo->get('core.delete')) {
        $return .= '<a class="trash" title="Delete"><i class="icon-trash"></i></a>';
    }
    $return .= '<a href="" class="t">' . $icon . '
                <span class="title">' . $category->title . '</span>
            </a>
        </div>';

    return $return;
}

/**
 * Close Item
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
    return '<div class="dd-handle dd3-handle"><i class="zmdi zmdi-folder dropfiles-folder"></i></div>
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
 * Open list
 *
 * @return string
 */
function openlist()
{
    return '<ol class="dd-list">';
}

/**
 * Close list
 *
 * @return string
 */
function closelist()
{
    return '</ol>';
}
