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
$link_download_popup = '#';
$allowedgoogleext    = 'pdf,ppt,doc,xls,dxf,ps,eps,xps,psd,tif,tiff,bmp,svg,pages,ai,dxf,ttf,txt,mp3,mp4,png,gif,ico,jpeg,jpg';

?>
    <script type="text/javascript">
        dropfilesGVExt = ["<?php echo implode('","', explode(',', DropfilesBase::loadValue($this->params, 'allowedgoogleext', $allowedgoogleext))); ?>"];
        function checkBoxSelectFileInit(event) {
            event.stopPropagation();
        }
    </script>

    <script type="text/x-handlebars-template" id="dropfiles-template-ggd-box">
        {{#with file}}
        <div class="dropblock dropfiles-dropblock-content">
            <a href="#" class="dropfiles-close"></a>
            <div class="filecontent">
                {{#if custom_icon}}
                <div class="custom-icon {{ext}}"><img src="{{custom_icon_thumb}}" alt=""></div>
                {{else}}
                <div class="ext {{ext}}"><span class="txt">{{ext}}</div>
                {{/if}}
                <?php if ((int) DropfilesBase::loadValue($this->params, 'ggd_showtitle', 1) === 1) : ?>
                    {{#if title}}
                    <h3><a class="downloadlink" href="{{link}}" title="{{title}}"><span>{{title}}</span></a></h3>
                    {{/if}}
                <?php endif; ?>

                <div class="dropfiles-extra">
                    <?php if ((int) DropfilesBase::loadValue($this->params, 'ggd_showdescription', 1) === 1) : ?>
                        {{#if description}}
                        <div>{{{description}}}</div>
                        {{/if}}
                    <?php endif; ?>
                    <?php if ((int) DropfilesBase::loadValue($this->params, 'ggd_showversion', 1) === 1) : ?>
                        {{#if versionNumber}}
                        <div><span>
                                <?php echo JText::_('COM_DROPFILES_DEFAULT_FRONT_VERSION'); ?>
                            </span>:&nbsp;{{versionNumber}}&nbsp;
                        </div>
                        {{/if}}
                    <?php endif; ?>
                    <?php if ((int) DropfilesBase::loadValue($this->params, 'ggd_showsize', 1) === 1) : ?>
                        {{#if size}}
                        <div><span>
                                <?php echo JText::_('COM_DROPFILES_DEFAULT_FRONT_SIZE'); ?>
                            </span>:&nbsp;{{bytesToSize size}}
                        </div>
                        {{/if}}
                    <?php endif; ?>
                    <?php if ((int) DropfilesBase::loadValue($this->params, 'ggd_showhits', 1) === 1) : ?>
                        {{#if hits}}
                        <div><span><?php echo JText::_('COM_DROPFILES_DEFAULT_FRONT_HITS'); ?></span>:&nbsp;{{hits}}
                        </div>
                        {{/if}}
                    <?php endif; ?>
                    <?php if ((int) DropfilesBase::loadValue($this->params, 'ggd_showdateadd', 1) === 1) : ?>
                        {{#if created_time}}
                        <div><span>
                                <?php echo JText::_('COM_DROPFILES_DEFAULT_FRONT_SHOWDATEADD'); ?>
                            </span>:&nbsp;{{created_time}}
                        </div>
                        {{/if}}
                    <?php endif; ?>
                    <?php if ((int) DropfilesBase::loadValue($this->params, 'ggd_showdatemodified', 0) === 1) : ?>
                        {{#if modified_time}}
                        <div><span>
                                <?php echo JText::_('COM_DROPFILES_DEFAULT_FRONT_DATEMODIFIED'); ?>
                            </span>:&nbsp;{{modified_time}}
                        </div>
                        {{/if}}
                    <?php endif; ?>
                </div>
            </div>
            <div class="extra-content">
                <?php if ((int) DropfilesBase::loadValue($this->params, 'ggd_showdownload', 1) === 1) : ?>
                    <div class="extra-downloadlink">
                        <?php if ($this->viewfileanddowload) : ?>
                            {{#if remoteurl}}
                            <a href="{{link}}"
                               target="_blank"><?php echo JText::_('COM_DROPFILES_DEFAULT_FRONT_DOWNLOAD'); ?><i
                                        class="zmdi zmdi-cloud-download dropfiles-download"></i></a>
                            {{else}}
                            <a href="{{link}}"><?php echo JText::_('COM_DROPFILES_DEFAULT_FRONT_DOWNLOAD'); ?><i
                                        class="zmdi zmdi-cloud-download dropfiles-download"></i></a>
                            {{/if}}
                        <?php endif; ?>
                    </div>
                    <?php if ((int) DropfilesBase::loadValue($this->params, 'usegoogleviewer', 1) > 0) : ?>
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
                               href="{{viewerlink}}"><?php echo JText::_('COM_DROPFILES_DEFAULT_FRONT_PREVIEW'); ?>
                                <i class="zmdi zmdi-filter-center-focus dropfiles-preview"></i></a>
                        </div>
                        {{/if}}
                        {{/if}}

                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
        {{/with}}
    </script>


<?php if ((int) DropfilesBase::loadValue($this->params, 'ggd_showsubcategories', 1) === 1) : ?>
    <script type="text/x-handlebars-template" id="dropfiles-template-ggd-categories-<?php echo $this->category->id; ?>">
        <div class="dropfiles-categories">
            <?php if ((int) DropfilesBase::loadValue($this->params, 'ggd_showcategorytitle', 1) === 1) : ?>
                {{#with category}}
                <div class="categories-head ">
                    <h2>{{title}}</h2>
                </div>
                {{/with}}
            <?php endif; ?>
            {{#with category}}
            {{#if parent_id}}
            <a class="catlink  dropfilescategory backcategory" href="#" data-idcat="{{parent_id}}">
                <i class="zmdi zmdi-chevron-left"></i><?php echo JText::_('COM_DROPFILES_DEFAULT_FRONT_BACK'); ?>
            </a>
            {{/if}}
            {{/with}}

            {{#if categories}}
            {{#each categories}}
            <a class="dropfilescategory catlink" href="#" data-idcat="{{id}}" title="{{title}}"><span>{{title}}</span>
                <i class="zmdi zmdi-folder dropfiles-folder"></i>
            </a>
            {{/each}}
            {{/if}}
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

    <script type="text/x-handlebars-template" id="dropfiles-template-ggd-files-<?php echo $this->category->id; ?>">
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
            <a
                    class="dropfiles-file-link"
                    title="{{title}}"
                    href="<?php
                    if (!$this->download_popup) {
                        echo '{{link_download_popup}}';
                    } else {
                        echo '#';
                    } ?>"
                    data-id="{{id}}">
                <?php if ($showdownloadcate === 1) : ?>
                    <label class="dropfiles_checkbox" onclick="checkBoxSelectFileInit(event)"><input class="cbox_file_download" type="checkbox" data-id="{{id}}" /><span></span></label>
                <?php endif;?>
                <div class="dropblock">
                    {{#if custom_icon}}
                    <div class="custom-icon {{ext}}"><img src="{{custom_icon_thumb}}" alt=""></div>
                    {{else}}
                    <div class="ext {{ext}}"><span class="txt">{{ext}}</div>
                    {{/if}}
                </div>
                <div class="droptitle">
                    {{title}}
                </div>
            </a>
            {{/if}}{{/each}}
        </div>
        {{/if}}
    </script>

<?php if (!empty($this->files) || !empty($this->categories)) : ?>
    <div class="dropfiles-content dropfiles-content-ggd dropfiles-content-multi dropfiles-files"
         data-category="<?php echo $this->category->id; ?>" data-current="<?php echo $this->category->id; ?>" data-category-name="<?php echo $this->category->title; ?>">
        <input type="hidden" id="current_category" value="<?php echo $this->category->id; ?>"/>
        <input type="hidden" id="current_category_slug" value="<?php echo $this->category->alias; ?>"/>
        <?php if ((int) DropfilesBase::loadValue($this->params, 'ggd_showbreadcrumb', 1) === 1) : ?>
            <ul class="breadcrumbs dropfiles-breadcrumbs-ggd">
                <li class="active">
                    <?php echo $this->category->title; ?>
                </li>
                <?php if ($this->user_id) : ?>
                    <a data-id="" data-catid="" data-file-type="" class="openlink-manage-files " target="_blank"
                       href="<?php echo $this->urlmanage ?>" data-urlmanage="<?php echo $this->urlmanage ?>">
                        <?php echo JText::_('COM_DROPFILES_MANAGE_FILES'); ?><i
                                class="zmdi zmdi-edit dropfiles-preview"></i>
                    </a>
                <?php endif; ?>
                <?php if ($showdownloadcate === 1 && isset($this->category->linkdownload_cat) && !empty($this->files)) : ?>
                    <a data-catid="" class="ggd-download-category download-all" href="<?php echo $this->category->linkdownload_cat; ?>"><?php echo JText::_('COM_DROPFILES_DOWNLOAD_ALL'); ?><i class="zmdi zmdi-check-all"></i></a>
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
                <a data-catid="" class="ggd-download-category download-all" href="<?php echo $this->category->linkdownload_cat; ?>"><?php echo JText::_('COM_DROPFILES_DOWNLOAD_ALL'); ?><i class="zmdi zmdi-check-all"></i></a>
            <?php endif;?>
        <?php endif; ?>
        <div class="dropfiles-container">
            <?php if ((int) DropfilesBase::loadValue($this->params, 'ggd_showfoldertree', 0) === 1) : ?>
                <div class="dropfiles-foldertree dropfiles-foldertree-ggd">
                </div>
                <div class="dropfiles-open-tree"></div>
            <?php endif; ?>
            <div class="dropfiles-container-ggd <?php
            if ((int) DropfilesBase::loadValue($this->params, 'ggd_showfoldertree', 0) === 1) {
                echo ' with_foldertree';
            } ?>">
                <div class="dropfiles-categories">
                    <div class="categories-head ">
                        <?php if ((int) DropfilesBase::loadValue($this->params, 'ggd_showcategorytitle', 1) === 1) : ?>
                            <h2><?php echo $this->category->title; ?></h2>
                        <?php endif; ?>
                    </div>
                    <?php
                    if (is_array($this->categories) && count($this->categories) &&
                        (int) DropfilesBase::loadValue($this->params, 'ggd_showsubcategories', 1) === 1) :?>
                        <?php foreach ($this->categories as $category) : ?>
                            <a class="dropfilescategory catlink" href="#" data-idcat="<?php echo $category->id; ?>"
                               title="<?php echo $category->title; ?>">
                                <span><?php echo $category->title; ?></span>
                                <i class="zmdi zmdi-folder dropfiles-folder"></i>
                            </a>
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
                                <a class="dropfiles-file-link"
                                   href="<?php echo $this->download_popup
                                       ? $link_download_popup : $file->link_download_popup; ?>"
                                   title="<?php echo $file->title; ?>" data-id="<?php echo $file->id; ?>">
                                    <div class="dropblock">
                                        <?php if ($showdownloadcate === 1 && $this->category->type === 'default') : ?>
                                            <label class="dropfiles_checkbox" onclick="checkBoxSelectFileInit(event)"><input class="cbox_file_download" type="checkbox" data-id="<?php echo $file->id;?>" /><span></span></label>
                                        <?php endif;?>
                                        <?php
                                        if ((int) $this->componentParams->get('custom_icon', 1) === 1 &&
                                            $file->custom_icon !== '') :
                                            ?>
                                            <div class="custom-icon <?php echo $file->ext; ?>">
                                                <img src="<?php echo $file->custom_icon_thumb; ?>" alt="">
                                            </div>
                                        <?php else : ?>
                                            <div class="ext <?php echo $file->ext; ?>">
                                            <span class="txt"><?php echo $file->ext; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="droptitle">
                                        <?php if ((int) DropfilesBase::loadValue($this->params, 'ggd_showtitle', 1) === 1) : ?>
                                            <?php echo $file->title; ?>
                                        <?php endif; ?>
                                    </div>
                                </a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endif; ?>
