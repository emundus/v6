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


//-- No direct access
defined('_JEXEC') || die('=;)');
DropfilesFilesHelper::includeJSHelper();
$usegoogleviewer  = ((int) $this->componentParams->get('usegoogleviewer', 1) === 1) ? 'dropfileslightbox' : '';
$target           = ((int) $this->componentParams->get('usegoogleviewer', 1) === 2) ? 'target="_blank"' : '';
$showdownloadcate = (int) $this->componentParams->get('download_category', 0);

?>

    <script type="text/x-handlebars-template" id="dropfiles-template-tree-box">
        {{#with file}}
        <div class="dropblock dropfiles-dropblock-content">
            <a href="javascript:void(null)" class="dropfiles-close"></a>
            <div class="filecontent">
                {{#if custom_icon}}
                <div class="custom-icon {{ext}}"><img src="{{custom_icon_thumb}}" alt=""></div>
                {{else}}
                <div class="ext {{ext}}"><span class="txt">{{ext}}</div>
                {{/if}}
                <h3><a class="downloadlink" href="{{link}}"><span>{{title}}</span></a></h3>

                <div class="dropfiles-extra">
                    <?php if ((int) DropfilesBase::loadValue($this->params, 'tree_showdescription', 1) === 1) : ?>
                        {{#if description}}
                        <div><span><?php echo JText::_('COM_DROPFILES_DEFAULT_FRONT_DESCRIPTION'); ?> : </span>
                            {{{description}}}&nbsp;
                        </div>
                        {{/if}}
                    <?php endif; ?>
                    <?php if ((int) DropfilesBase::loadValue($this->params, 'tree_showversion', 1) === 1) : ?>
                        {{#if versionNumber}}
                        <div><span><?php echo JText::_('COM_DROPFILES_DEFAULT_FRONT_VERSION'); ?> : </span>
                            {{versionNumber}}&nbsp;
                        </div>
                        {{/if}}
                    <?php endif; ?>
                    <?php if ((int) DropfilesBase::loadValue($this->params, 'tree_showsize', 1) === 1) : ?>
                        {{#if size}}
                        <div><span><?php echo JText::_('COM_DROPFILES_DEFAULT_FRONT_SIZE'); ?> : </span> {{bytesToSize size}}
                        </div>
                        {{/if}}
                    <?php endif; ?>
                    <?php if ((int) DropfilesBase::loadValue($this->params, 'tree_showhits', 1) === 1) : ?>
                        {{#if hits}}
                        <div><span><?php echo JText::_('COM_DROPFILES_DEFAULT_FRONT_HITS'); ?> : </span> {{hits}}</div>
                        {{/if}}
                    <?php endif; ?>
                    <?php if ((int) DropfilesBase::loadValue($this->params, 'tree_showdateadd', 1) === 1) : ?>
                        {{#if created_time}}
                        <div><span><?php echo JText::_('COM_DROPFILES_DEFAULT_FRONT_SHOWDATEADD'); ?> : </span>
                            {{created_time}}
                        </div>
                        {{/if}}
                    <?php endif; ?>
                    <?php if ((int) DropfilesBase::loadValue($this->params, 'tree_showdatemodified', 0) === 1) : ?>
                        {{#if modified_time}}
                        <div><span><?php echo JText::_('COM_DROPFILES_DEFAULT_FRONT_DATEMODIFIED'); ?> : </span>
                            {{modified_time}}
                        </div>
                        {{/if}}
                    <?php endif; ?>
                </div>
            </div>
            <div class="extra-content">
                <div class="extra-downloadlink">
                    <?php if ($this->viewfileanddowload) : ?>
                        <a href="{{link}}"><?php echo JText::_('COM_DROPFILES_DEFAULT_FRONT_DOWNLOAD'); ?><i
                                    class="zmdi zmdi-cloud-download dropfiles-download"></i></a>
                    <?php endif; ?>
                </div>
                <?php if ((int) $this->componentParams->get('usegoogleviewer', 1) > 0) : ?>
                    {{#if openpdflink}}
                    <div class="extra-openlink">
                        <a class="openlink" href="{{openpdflink}}"
                           target="_blank"><?php echo JText::_('COM_DROPFILES_DEFAULT_FRONT_OPEN'); ?><i
                                    class="zmdi zmdi-filter-center-focus dropfiles-preview"></i></a>
                    </div>
                    {{else}}
                    {{#if viewerlink}}
                    <div class="extra-openlink">
                        <a data-id="{{id}}" data-catid="{{catid}}" data-file-type="{{ext}}"
                           class="openlink <?php echo $usegoogleviewer; ?>" <?php echo $target; ?>
                           href="{{viewerlink}}"><?php echo JText::_('COM_DROPFILES_DEFAULT_FRONT_PREVIEW'); ?><i
                                    class="zmdi zmdi-filter-center-focus dropfiles-preview"></i></a>
                    </div>
                    {{/if}}
                    {{/if}}

                <?php endif; ?>
            </div>
        </div>
        {{/with}}
    </script>


<?php if ((int) DropfilesBase::loadValue($this->params, 'tree_showsubcategories', 1) === 1) : ?>
    <script type="text/x-handlebars-template" id="dropfiles-template-tree-categories">
        {{#if categories}}
        {{#each categories}}
        <li class="directory collapsed">
            <a class="catlink" href="#" data-idcat="{{id}}">
                <div class="icon-open-close" data-id="{{id}}"></div>
                <i class="zmdi zmdi-folder dropfiles-folder"></i>
                <span>{{title}}</span>
            </a>
        </li>
        {{/each}}
        {{/if}}
    </script>
<?php endif; ?>

    <script type="text/x-handlebars-template" id="dropfiles-template-tree-files">
        {{#if files}}
        {{#each files}}{{#if ext}}

        {{#if custom_icon}}
        <li class="custom-icon {{ext}}">
            <?php if ($showdownloadcate === 1) : ?>
                <label class="dropfiles_checkbox"><input class="cbox_file_download" type="checkbox" data-id="{{id}}" /><span></span></label>
            <?php endif;?>
            <a class="dropfile-file-link" href="
            <?php
            if (!$this->download_popup) {
                echo '{{link_download_popup}}';
            } else {
                echo '#';
            } ?>"
               data-id="{{id}}"><img src="{{custom_icon_thumb}}" alt=""/>{{title}}
            </a></li>
        {{else}}
        <li class="ext {{ext}}">
            <?php if ($showdownloadcate === 1) : ?>
                <label class="dropfiles_checkbox"><input class="cbox_file_download" type="checkbox" data-id="{{id}}" /><span></span></label>
            <?php endif;?>
            <i class="dropfile-file ext {{ext}}"></i>
            <a class="dropfile-file-link" href="<?php
            if (!$this->download_popup) {
                echo '{{link_download_popup}}';
            } else {
                echo '#';
            } ?>"
               data-id="{{id}}">
                {{title}}
            </a>
        </li>
        {{/if}}

        {{/if}}{{/each}}
        </div>
        {{/if}}
    </script>

    <script type="text/x-handlebars-template" id="dropfiles-template-tree-type">
        {{#if category}}
            {{#if category.type}}
                <input type="hidden" id="current-category-type" class="type {{category.type}}" data-category-type="{{category.type}}"/>
            {{/if}}
            {{#if category.linkdownload_cat}}
            <input type="hidden" id="current-category-link" class="link" value="{{category.linkdownload_cat}}"/>
            {{/if}}
        {{/if}}
    </script>
<?php if ($this->category !== null) : ?>
    <?php if (!empty($this->files) || !empty($this->categories)) : ?>
        <div class="dropfiles-content dropfiles-content-multi dropfiles-files dropfiles-content-tree"
             data-category="<?php echo $this->category->id; ?>" data-current="<?php echo $this->category->id; ?>">
            <input type="hidden" id="current_category" value="<?php echo $this->category->id; ?>"/>
            <input type="hidden" id="current_category_slug" value="<?php echo $this->category->alias; ?>"/>
            <div class="categories-head  <?php
            if ($this->user_id) {
                echo 'manage-files-head';
            } ?>">
                <?php if ((int) DropfilesBase::loadValue($this->params, 'tree_showcategorytitle', 1) === 1) : ?>
                    <li class="active"><?php echo $this->category->title; ?></li>
                <?php endif; ?>
                <?php if ($this->user_id) : ?>
                    <a data-id="" data-catid="" data-file-type="" class="openlink-manage-files " target="_blank"
                       href="<?php echo $this->urlmanage ?>" data-urlmanage="<?php echo $this->urlmanage ?>">
                        <?php echo JText::_('COM_DROPFILES_MANAGE_FILES'); ?>
                        <i class="zmdi zmdi-edit dropfiles-preview"></i>
                    </a>
                <?php endif; ?>
                <?php if ($showdownloadcate === 1 && isset($this->category->linkdownload_cat) && !empty($this->files)) : ?>
                    <a data-catid="" class="tree-download-category download-all" href="<?php echo $this->category->linkdownload_cat; ?>"><?php echo JText::_('COM_DROPFILES_DOWNLOAD_ALL'); ?><i class="zmdi zmdi-check-all"></i></a>
                <?php endif;?>
            </div>
            <?php $titlec = ((int) DropfilesBase::loadValue($this->params, 'tree_showtitle', 1) === 0) ? 'tree-hide-title' : '' ;?>
            <ul class="tree-list <?php echo  $titlec ;?>">
                <?php if (is_array($this->categories) && count($this->categories) &&
                          (int) DropfilesBase::loadValue($this->params, 'tree_showsubcategories', 1) === 1) : ?>
                                        <?php foreach ($this->categories as $category) : ?>
                        <li class="directory collapsed">
                            <a class="catlink" href="#" data-idcat="<?php echo $category->id; ?>">
                                <div class="icon-open-close" data-id="<?php echo $category->id; ?>"></div>
                                <i class="zmdi zmdi-folder dropfiles-folder"></i>
                                <span><?php echo $category->title; ?></span>
                            </a>
                        </li>
                                        <?php endforeach; ?>
                <?php endif; ?>
                <?php if (is_array($this->files) && count($this->files)) : ?>
                    <?php foreach ($this->files as $file) : ?>
                        <?php if ($file->ext !== null) : ?>
                            <?php if ((int) $this->componentParams->get('custom_icon', 1) === 1 &&
                                $file->custom_icon !== '') : ?>
                                <li class="custom-icon <?php echo $file->ext; ?>">
                                    <?php if ($showdownloadcate === 1 && $this->category->type === 'default') : ?>
                                        <label class="dropfiles_checkbox"><input class="cbox_file_download" type="checkbox" data-id="<?php echo $file->id;?>" /><span></span></label>
                                    <?php endif;?>
                                    <a class="dropfile-file-link" href="<?php
                                    if (!$this->download_popup) {
                                        echo $file->link_download_popup;
                                    } else {
                                        echo '#';
                                    } ?>" data-id="<?php echo $file->id; ?>">
                                        <img src="<?php echo $file->custom_icon_thumb; ?>" alt=""/>
                                        <?php if ((int) DropfilesBase::loadValue($this->params, 'tree_showtitle', 1) === 1) : ?>
                                            <?php echo $file->title; ?>
                                        <?php endif;?>
                                    </a>
                                </li>
                            <?php else : ?>
                                <li class="ext <?php echo $file->ext; ?>">
                                    <?php if ($showdownloadcate === 1 && $this->category->type === 'default') : ?>
                                        <label class="dropfiles_checkbox"><input class="cbox_file_download" type="checkbox" data-id="<?php echo $file->id;?>" /><span></span></label>
                                    <?php endif;?>
                                    <i class="dropfile-file ext <?php echo $file->ext; ?>"></i>
                                    <a class="dropfile-file-link" href="<?php
                                    if (!$this->download_popup) {
                                        echo $file->link_download_popup;
                                    } else {
                                        echo '#';
                                    } ?>" data-id="<?php echo $file->id; ?>">
                                        <?php if ((int) DropfilesBase::loadValue($this->params, 'tree_showtitle', 1) === 1) : ?>
                                            <?php echo $file->title; ?>
                                        <?php endif;?>
                                    </a>
                                </li>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
    <?php endif; ?>
<?php endif; ?>
