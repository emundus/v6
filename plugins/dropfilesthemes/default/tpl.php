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
$showdownload     = (int) DropfilesBase::loadValue($this->params, 'showdownload', 1);
$showdownloadcate = (int) $this->componentParams->get('download_category', 0);
?>
<?php if ((int) DropfilesBase::loadValue($this->params, 'showsubcategories', 1) === 1) : ?>
    <script type="text/x-handlebars-template" id="dropfiles-template-default-categories-<?php echo $this->category->id; ?>">
        <div class="dropfiles-categories">
            <?php if ((int) DropfilesBase::loadValue($this->params, 'showcategorytitle', 1) === 1) : ?>
                {{#with category}}
                <div class="categories-head ">
                    <h2>{{title}}</h2>
                </div>
                {{/with}}
            <?php endif; ?>
            {{#if categories}}
            {{#each categories}}
            <a class="catlink dropfilescategory" href="#" data-idcat="{{id}}" title="{{title}}">
                <span>{{title}}</span>
                <i class="zmdi zmdi-folder dropfiles-folder"></i>
            </a>
            {{/each}}
            {{/if}}
            {{#with category}}
            {{#if parent_id}}
            <a class="catlink dropfilescategory backcategory" href="#" data-idcat="{{parent_id}}"><i
                        class="zmdi zmdi-chevron-left"></i><?php echo JText::_('COM_DROPFILES_DEFAULT_FRONT_BACK'); ?>
            </a>
            {{/if}}
            {{/with}}
            <div class="dropfilescategory_placeholder" style="margin-top: 0 !important; margin-bottom: 0 !important;"></div>
            <div class="dropfilescategory_placeholder" style="margin-top: 0 !important; margin-bottom: 0 !important;"></div>
            <div class="dropfilescategory_placeholder" style="margin-top: 0 !important; margin-bottom: 0 !important;"></div>
            <div class="dropfilescategory_placeholder" style="margin-top: 0 !important; margin-bottom: 0 !important;"></div>
            <div class="dropfilescategory_placeholder" style="margin-top: 0 !important; margin-bottom: 0 !important;"></div>
            <div class="dropfilescategory_placeholder" style="margin-top: 0 !important; margin-bottom: 0 !important;"></div>
            <div class="dropfilescategory_placeholder" style="margin-top: 0 !important; margin-bottom: 0 !important;"></div>
        </div>
    </script>
<?php endif; ?>


<script type="text/x-handlebars-template" id="dropfiles-template-default-files-<?php echo $this->category->id; ?>">
    {{#if category}}
    {{#if category.type}}
    <input type="hidden" id="current-category-type" class="type {{category.type}}" data-category-type="{{category.type}}"/>
    {{/if}}
    {{#if category.linkdownload_cat}}
    <input type="hidden" id="current-category-link" class="link" value="{{category.linkdownload_cat}}"/>
    {{/if}}
    {{/if}}
    {{#if files}}
    <div class="dropfiles_list">
        {{#each files}}{{#if ext}}
        <div class="file">
            <div class="filecontent">
                {{#if custom_icon}}
                <div class="custom-icon {{ext}}"><img src="{{custom_icon_thumb}}" alt=""/></div>
                {{else}}
                <div class="ext {{ext}}"><span class="txt">{{ext}}</span></div>
                {{/if}}
                <?php if ((int) DropfilesBase::loadValue($this->params, 'showtitle', 1) === 1) : ?>
                    <h3>
                        <?php if ($showdownloadcate === 1) : ?>
                            <label class="dropfiles_checkbox "><input class="cbox_file_download" type="checkbox" data-id="{{id}}" /><span></span></label>
                        <?php endif;?>
                        <a href="{{link}}" title="{{title}}">{{title}}</a>
                    </h3>
                <?php endif; ?>
                <div class="file-xinfo">
                    {{#if description}}
                    <div class="file-desc">{{{description}}}</div>
                    {{/if}}
                    <?php if ((int) DropfilesBase::loadValue($this->params, 'showversion', 1) === 1) : ?>
                        {{#if versionNumber}}
                        <div class="file-version"><span><?php echo JText::_('COM_DROPFILES_DEFAULT_FRONT_VERSION'); ?>
                                : </span> {{versionNumber}}&nbsp;
                        </div>
                        {{/if}}
                    <?php endif; ?>
                    <?php if ((int) DropfilesBase::loadValue($this->params, 'showsize', 1) === 1) : ?>
                        {{#if size}}
                        <div class="file-size"><span><?php echo JText::_('COM_DROPFILES_DEFAULT_FRONT_SIZE'); ?>
                                : </span> {{bytesToSize size}}
                        </div>
                        {{/if}}
                    <?php endif; ?>
                    <?php if ((int) DropfilesBase::loadValue($this->params, 'showhits', 1) === 1) : ?>
                        <div class="file-hits"><span><?php echo JText::_('COM_DROPFILES_DEFAULT_FRONT_HITS'); ?>
                                : </span> {{hits}}
                        </div>
                    <?php endif; ?>
                    <?php if ((int) DropfilesBase::loadValue($this->params, 'showdateadd', 1) === 1) : ?>
                        <div class="file-dated"><span><?php echo JText::_('COM_DROPFILES_DEFAULT_FRONT_SHOWDATEADD'); ?>
                                : </span> {{created_time}}
                        </div>
                    <?php endif; ?>
                    <?php if ((int) DropfilesBase::loadValue($this->params, 'showdatemodified', 0) === 1) : ?>
                        <div class="file-modified">
                            <span><?php echo JText::_('COM_DROPFILES_DEFAULT_FRONT_DATEMODIFIED'); ?> : </span>
                            {{modified_time}}
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="file-right">
                <?php
                if ((int) DropfilesBase::loadValue($this->params, 'showdownload', 1) === 1) : ?>
                    <?php if ($this->viewfileanddowload) : ?>
                        <a class="downloadlink"
                           href="{{link}}"><?php echo JText::_('COM_DROPFILES_DEFAULT_FRONT_DOWNLOAD'); ?><i
                                    class="zmdi zmdi-cloud-download dropfiles-download"></i></a>
                    <?php endif; ?>
                    <?php if ((int) $this->componentParams->get('usegoogleviewer', 1) > 0) : ?>
                        {{#if openpdflink}}
                        <a href="{{openpdflink}}"
                           class="openlink"
                           target="_blank"><?php echo JText::_('COM_DROPFILES_DEFAULT_FRONT_OPEN'); ?><i
                                    class="zmdi zmdi-filter-center-focus dropfiles-preview"></i></a>
                        {{else}}
                        {{#if viewerlink}}
                        <a data-id="{{id}}" data-catid="{{catid}}" data-file-type="{{ext}}"
                           class="openlink <?php echo $usegoogleviewer; ?>" <?php echo $target; ?>
                           href="{{viewerlink}}"><?php echo JText::_('COM_DROPFILES_DEFAULT_FRONT_PREVIEW'); ?><i
                                    class="zmdi zmdi-filter-center-focus dropfiles-preview"></i></a>
                        {{/if}}
                        {{/if}}
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
        {{/if}}{{/each}}
        <div class="file flexspan"></div>
    </div>
    {{/if}}
</script>

<?php if (!empty($this->files) || !empty($this->category)) : ?>
    <div class="dropfiles-content dropfiles-content-multi dropfiles-files dropfiles-content-default"
         data-category="<?php echo $this->category->id; ?>" data-category-name="<?php echo $this->category->title; ?>">
        <input type="hidden" id="current_category" value="<?php echo $this->category->id; ?>"/>
        <input type="hidden" id="current_category_slug" value="<?php echo $this->category->alias; ?>"/>
        <?php if ((int) DropfilesBase::loadValue($this->params, 'showbreadcrumb', 1) === 1) : ?>
            <ul class="breadcrumbs dropfiles-breadcrumbs-default">
                <li class="active">
                    <?php echo $this->category->title; ?>
                </li>
                <?php if ($this->user_id) : ?>
                    <a data-id="" data-catid="" data-file-type="" class="openlink-manage-files " target="_blank"
                       href="<?php echo $this->urlmanage ?>&task=site_manage&site_catid=<?php echo $this->category->id ?>&tmpl=dropfilesfrontend" data-urlmanage="<?php echo $this->urlmanage ?>">
                        <?php echo JText::_('COM_DROPFILES_MANAGE_FILES'); ?><i
                                class="zmdi zmdi-edit dropfiles-preview"></i>
                    </a>
                <?php endif; ?>
                <?php if ($showdownloadcate === 1 && isset($this->category->linkdownload_cat) && !empty($this->files)) : ?>
                    <a data-catid="" class="default-download-category download-all" href="<?php echo $this->category->linkdownload_cat; ?>"><?php echo JText::_('COM_DROPFILES_DOWNLOAD_ALL'); ?><i class="zmdi zmdi-check-all"></i></a>
                <?php endif;?>
            </ul>
        <?php else : ?>
            <?php if ($this->user_id) : ?>
                <div class="dropfiles-manage-files">
                    <a data-id="" data-catid="" data-file-type="" class="openlink-manage-files " target="_blank"
                       href="<?php echo $this->urlmanage ?>" data-urlmanage="<?php echo $this->urlmanage ?>">
                        <?php echo JText::_('COM_DROPFILES_MANAGE_FILES'); ?>
                        <i class="zmdi zmdi-edit dropfiles-preview"></i>
                    </a>
                </div>
            <?php endif; ?>
            <?php if ($showdownloadcate === 1 && isset($this->category->linkdownload_cat) && !empty($this->files)) : ?>
                <a data-catid="" class="default-download-category download-all" href="<?php echo $this->category->linkdownload_cat; ?>"><?php echo JText::_('COM_DROPFILES_DOWNLOAD_ALL'); ?><i class="zmdi zmdi-check-all"></i></a>
            <?php endif;?>
        <?php endif; ?>
        <div class="dropfiles-container">
            <?php if ((int) DropfilesBase::loadValue($this->params, 'showfoldertree', 0) === 1) : ?>
                <div class="dropfiles-foldertree-default dropfiles-foldertree">

                </div>
                <div class="dropfiles-open-tree"></div>
            <?php endif; ?>
            <div class="dropfiles-container-default <?php
            if ((int) DropfilesBase::loadValue($this->params, 'showfoldertree', 0) === 1) {
                echo ' with_foldertree';
            } ?>">
                <div class="dropfiles-categories">
                    <div class="categories-head">
                        <?php if ((int) DropfilesBase::loadValue($this->params, 'showcategorytitle', 1) === 1) : ?>
                            <h2><?php echo $this->category->title; ?></h2>
                        <?php endif; ?>
                    </div>
                    <?php if (is_array($this->categories) && count($this->categories) &&
                        (int) DropfilesBase::loadValue($this->params, 'showsubcategories', 1) === 1) : ?>
                        <?php foreach ($this->categories as $category) : ?>
                            <a class="dropfilescategory catlink" href="#" data-idcat="<?php echo $category->id; ?>"
                               title="<?php echo $category->title; ?>"><span><?php echo $category->title; ?></span><i
                                        class="zmdi zmdi-folder dropfiles-folder"></i></a>
                        <?php endforeach; ?>
                        <div class="dropfilescategory_placeholder" style="margin-top: 0 !important; margin-bottom: 0 !important;"></div>
                        <div class="dropfilescategory_placeholder" style="margin-top: 0 !important; margin-bottom: 0 !important;"></div>
                        <div class="dropfilescategory_placeholder" style="margin-top: 0 !important; margin-bottom: 0 !important;"></div>
                        <div class="dropfilescategory_placeholder" style="margin-top: 0 !important; margin-bottom: 0 !important;"></div>
                        <div class="dropfilescategory_placeholder" style="margin-top: 0 !important; margin-bottom: 0 !important;"></div>
                        <div class="dropfilescategory_placeholder" style="margin-top: 0 !important; margin-bottom: 0 !important;"></div>
                        <div class="dropfilescategory_placeholder" style="margin-top: 0 !important; margin-bottom: 0 !important;"></div>
                    <?php endif; ?>
                </div>
                <?php if (is_array($this->files) && count($this->files)) : ?>
                    <div class="dropfiles_list">
                        <?php foreach ($this->files as $file) : ?>
                            <?php if ($file->ext !== null) : ?>
                                <div class="file">
                                    <div class="filecontent">
                                        <?php if ((int) $this->componentParams->get('custom_icon', 1) === 1 &&
                                            $file->custom_icon !== '') : ?>
                                            <div class="custom-icon <?php echo $file->ext; ?>"><img
                                                        src="<?php echo $file->custom_icon_thumb; ?>" alt=""></div>
                                        <?php else : ?>
                                            <div class="ext <?php echo $file->ext; ?>"><span
                                                        class="txt"><?php echo $file->ext; ?></span></div>
                                        <?php endif; ?>
                                        <?php if ((int) DropfilesBase::loadValue($this->params, 'showtitle', 1) === 1) : ?>
                                            <h3>
                                                <?php if ($showdownloadcate === 1 && $this->category->type === 'default') : ?>
                                                    <label class="dropfiles_checkbox"><input class="cbox_file_download" type="checkbox" data-id="<?php echo $file->id;?>" /><span></span></label>
                                                <?php endif;?>
                                                <a class="dropfiles_downloadlink" title="<?php echo $file->title; ?>"
                                                   href="<?php echo $file->link; ?>"><?php echo $file->title; ?></a>
                                            </h3>
                                        <?php endif; ?>
                                        <div class="file-xinfo">
                                            <div class="file-desc"><?php echo $file->description; ?></div>
                                            <?php if ((int) DropfilesBase::loadValue($this->params, 'showversion', 1) === 1 &&
                                                trim($file->versionNumber)) : ?>
                                                <div class="file-version">
                                                <span><?php echo JText::_('COM_DROPFILES_DEFAULT_FRONT_VERSION'); ?>
                                                    : </span> <?php echo $file->versionNumber; ?>&nbsp;
                                                </div>
                                            <?php endif; ?>
                                            <?php if ((int) DropfilesBase::loadValue($this->params, 'showsize', 1) === 1) : ?>
                                                <div class="file-size">
                                                <span>
                                                    <?php echo JText::_('COM_DROPFILES_DEFAULT_FRONT_SIZE'); ?>:
                                                </span>
                                                    <?php echo DropfilesFilesHelper::bytesToSize($file->size); ?>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ((int) DropfilesBase::loadValue($this->params, 'showhits', 1) === 1) : ?>
                                                <div class="file-hits">
                                                <span><?php echo JText::_('COM_DROPFILES_DEFAULT_FRONT_HITS'); ?>
                                                    : </span> <?php echo $file->hits; ?></div>
                                            <?php endif; ?>
                                            <?php
                                            if ((int) DropfilesBase::loadValue($this->params, 'showdateadd', 1) === 1) :
                                                ?>
                                                <div class="file-dated">
                                                <span>
                                                    <?php echo JText::_('COM_DROPFILES_DEFAULT_FRONT_SHOWDATEADD'); ?>:
                                                </span>
                                                    <?php echo $file->created_time; ?>
                                                </div>
                                            <?php endif; ?>
                                            <?php
                                            if ((int) DropfilesBase::loadValue($this->params, 'showdatemodified', 0) === 1) :
                                                ?>
                                                <div class="file-modified">
                                                <span>
                                                    <?php
                                                    echo JText::_('COM_DROPFILES_DEFAULT_FRONT_DATEMODIFIED');
                                                    ?>:
                                                </span>
                                                    <?php echo $file->modified_time; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="file-right">
                                        <?php if ($this->viewfileanddowload && $showdownload === 1) : ?>
                                            <a class="downloadlink" href="<?php echo $file->link; ?>">
                                                <?php echo JText::_('COM_DROPFILES_DEFAULT_FRONT_DOWNLOAD'); ?>
                                                <i class="zmdi zmdi-cloud-download dropfiles-download"></i></a>
                                        <?php endif; ?>
                                        <?php if (isset($file->openpdflink)) { ?>
                                            <a href="<?php echo $file->openpdflink; ?>" class="openlink"
                                               target="_blank">
                                                <?php echo JText::_('COM_DROPFILES_DEFAULT_FRONT_OPEN'); ?>
                                                <i class="zmdi zmdi-filter-center-focus dropfiles-preview"></i>
                                            </a>
                                        <?php } else { ?>
                                            <?php if (isset($file->viewerlink)) : ?>
                                                <a data-id="<?php echo $file->id; ?>"
                                                   data-catid="<?php echo $file->catid; ?>"
                                                   data-file-type="<?php echo $file->ext; ?>"
                                                   class="openlink <?php echo $usegoogleviewer; ?>"
                                                    <?php echo $target; ?>
                                                   href="<?php echo $file->viewerlink; ?>">
                                                    <?php echo JText::_('COM_DROPFILES_DEFAULT_FRONT_PREVIEW'); ?>
                                                    <i class="zmdi zmdi-filter-center-focus dropfiles-preview"></i>
                                                </a>
                                            <?php endif; ?>
                                        <?php } ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <div class="file flexspan"></div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endif; ?>
