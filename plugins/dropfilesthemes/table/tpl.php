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
$dropfileslightbox = ((int) $this->componentParams->get('usegoogleviewer', 1) === 1) ? 'dropfileslightbox' : '';
$target            = ((int) $this->componentParams->get('usegoogleviewer', 1) === 2) ? 'target="_blank"' : '';

?>
    <script type="text/x-handlebars-template" id="dropfiles-template-table-categories">
        <?php if ((int) DropfilesBase::loadValue($this->params, 'table_showsubcategories', 1) === 1) : ?>
            <div class="categories-head ">
                <?php if ((int) DropfilesBase::loadValue($this->params, 'table_showcategorytitle', 1) === 1 &&
                           (int) DropfilesBase::loadValue($this->params, 'table_showcategoriesposition', 0) === 0) : ?>
                    {{#with category}}
                    <h2>{{title}}</h2>
                    {{/with}}
                <?php endif; ?>
            </div>
            {{#with category}}
            {{#if parent_id}}
            <a class="catlink  dropfilescategory backcategory" href="#" data-idcat="{{parent_id}}">
                <i class="zmdi zmdi-chevron-left"></i><?php echo JText::_('COM_DROPFILES_DEFAULT_FRONT_BACK'); ?>
            </a>
            {{/if}}
            {{/with}}
        <?php endif; ?>

        <?php if ((int) DropfilesBase::loadValue($this->params, 'table_showcategorytitle', 1) === 1 &&
                   (int) DropfilesBase::loadValue($this->params, 'table_showcategoriesposition', 0) === 1) : ?>
            <div class="categories-head ">
                {{#with category}}
                <h2>{{title}}</h2>
                {{/with}}
            </div>
        <?php endif; ?>
        {{#each categories}}
        <a class="dropfilescategory catlink" href="#" data-idcat="{{id}}"><span>{{title}}</span>
            <i class="zmdi zmdi-folder dropfiles-folder"></i>
        </a>
        {{/each}}
    </script>

    <script type="text/x-handlebars-template" id="dropfiles-template-table">
        {{#if files}}
        {{#each files}}{{#if ext}}
        <tr>
            <td class="extcol">
                {{#if custom_icon}}
                <div class="custom-icon {{ext}}"><img src="{{custom_icon_thumb}}" alt=""></div>
                {{else}}
                <span class="ext {{ext}}"></span>
                {{/if}}
            </td>

            <?php if ((int) DropfilesBase::loadValue($this->params, 'table_showtitle', 1) === 1) : ?>
                <td>
                    <a class="title" href='{{link}}'>{{title}}</a>
                </td>
            <?php endif; ?>

            <?php if ((int) DropfilesBase::loadValue($this->params, 'table_showversion', 1) === 1) : ?>
                <td>
                    {{versionNumber}}
                </td>
            <?php endif; ?>

            <?php if ((int) DropfilesBase::loadValue($this->params, 'table_showdescription', 1) === 1) : ?>
                <td>
                    {{{description}}}
                </td>
            <?php endif; ?>

            <?php if ((int) DropfilesBase::loadValue($this->params, 'table_showsize', 1) === 1) : ?>
                <td>
                    {{bytesToSize size}}
                </td>
            <?php endif; ?>

            <?php if ((int) DropfilesBase::loadValue($this->params, 'table_showhits', 1) === 1) : ?>
                <td>
                    {{hits}}
                </td>
            <?php endif; ?>

            <?php if ((int) DropfilesBase::loadValue($this->params, 'table_showdateadd', 0) === 1) : ?>
                <td>
                    {{created_time}}
                </td>
            <?php endif; ?>

            <?php if ((int) DropfilesBase::loadValue($this->params, 'table_showdatemodified', 0) === 1) : ?>
                <td>
                    {{modified_time}}
                </td>
            <?php endif; ?>
            <?php if ((int) DropfilesBase::loadValue($this->params, 'table_showdownload', 1) === 1) : ?>
                <td>
                    <?php if ($this->viewfileanddowload) : ?>
                        <a class="downloadlink" href='{{link}}'>
                            <?php echo JText::_('COM_DROPFILES_DEFAULT_FRONT_DOWNLOAD'); ?>
                            <i class="zmdi zmdi-cloud-download dropfiles-download"></i>
                        </a>
                    <?php endif; ?>
                    <?php if ((int) $this->componentParams->get('usegoogleviewer', 1) > 0) : ?>
                        {{#if openpdflink}}
                        <a href='{{openpdflink}}' class="openlink" target="_blank">
                            <?php echo JText::_('COM_DROPFILES_DEFAULT_FRONT_OPEN'); ?>
                            <i class="zmdi zmdi-filter-center-focus dropfiles-preview"></i>
                        </a>
                        {{else}}
                        {{#if viewerlink}}
                        <a data-id="{{id}}" data-catid="{{catid}}" data-file-type="{{ext}}"
                           class="openlink <?php echo $dropfileslightbox; ?>" <?php echo $target; ?>
                           href='{{viewerlink}}'>
                            <?php echo JText::_('COM_DROPFILES_DEFAULT_FRONT_PREVIEW'); ?>
                            <i class="zmdi zmdi-filter-center-focus dropfiles-preview"></i></a>
                        {{/if}}
                        {{/if}}

                    <?php endif; ?>
                </td>
            <?php endif; ?>
        </tr>
        {{/if}}{{/each}}
        {{/if}}


    </script>

<?php if (!empty($this->files) || !empty($this->categories)) : ?>
    <div class="dropfiles-content dropfiles-content-table dropfiles-content-multi dropfiles-files
        <?php echo $this->dropfilesclass; ?>"
         data-category="<?php echo $this->category->id; ?>" data-current="<?php echo $this->category->id; ?>" data-category-name="<?php echo $this->category->title; ?>">
        <input type="hidden" id="current_category" value="<?php echo $this->category->id; ?>"/>
        <input type="hidden" id="current_category_slug" value="<?php echo $this->category->alias; ?>"/>
        <?php if ((int) DropfilesBase::loadValue($this->params, 'table_showbreadcrumb', 1) === 1) : ?>
            <ul class="breadcrumbs dropfiles-breadcrumbs-table">
                <li class="active">
                    <?php echo $this->category->title; ?>
                </li>
                <?php if ($this->user_id) : ?>
                    <a data-id="" data-catid="" data-file-type="" class="openlink-manage-files " target="_blank"
                       href="<?php echo $this->urlmanage ?>" data-urlmanage="<?php echo $this->urlmanage ?>">
                        <?php echo JText::_('COM_DROPFILES_MANAGE_FILES'); ?>
                        <i class="zmdi zmdi-edit dropfiles-preview"></i>
                    </a>
                <?php endif; ?>
            </ul>
        <?php endif; ?>
        <div class="dropfiles-container">
            <?php if ((int) DropfilesBase::loadValue($this->params, 'table_showfoldertree', 0) === 1) : ?>
                <div class="dropfiles-foldertree-table dropfiles-foldertree">
                </div>
                <div class="dropfiles-open-tree"></div>
            <?php endif; ?>
            <div class="dropfiles-container-table <?php
            if ((int) DropfilesBase::loadValue($this->params, 'table_showfoldertree', 0) === 1) {
                echo ' with_foldertree';
            } ?>">
                <div class="dropfiles-categories">
                    <div class="categories-head ">
                        <?php if ((int) DropfilesBase::loadValue($this->params, 'table_showcategorytitle', 1) === 1 &&
                                   (int) DropfilesBase::loadValue($this->params, 'table_showcategoriesposition', 0) === 0) : ?>
                            <h2><?php echo $this->category->title; ?></h2>
                        <?php endif; ?>
                    </div>
                    <?php if ((int) DropfilesBase::loadValue($this->params, 'table_showcategorytitle', 1) === 1 &&
                               (int) DropfilesBase::loadValue($this->params, 'table_showcategoriesposition', 0) === 1) : ?>
                        <h2><?php echo $this->category->title; ?></h2>
                    <?php endif; ?>
                    <?php if (is_array($this->categories) && count($this->categories) &&
                              (int) DropfilesBase::loadValue($this->params, 'table_showsubcategories', 1) === 1) : ?>
                        <?php foreach ($this->categories as $category) : ?>
                            <a class="dropfilescategory catlink" href="#"
                               data-idcat="<?php echo $category->id; ?>"> <span><?php echo $category->title; ?>
                                </span><i class="zmdi zmdi-folder dropfiles-folder"></i></a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <table class="<?php echo $this->tableclass; ?> mediaTable">
                    <thead>
                    <tr>
                        <th>#</th>
                        <?php if ((int) DropfilesBase::loadValue($this->params, 'table_showtitle', 1) === 1) : ?>
                            <th class="essential persist">
                                <?php echo JText::_('COM_DROPFILES_DEFAULT_FRONT_TITLE'); ?>
                            </th>
                        <?php endif; ?>

                        <?php if ((int) DropfilesBase::loadValue($this->params, 'table_showversion', 1) === 1) : ?>
                            <th class="optional">
                                <?php echo JText::_('COM_DROPFILES_DEFAULT_FRONT_VERSION'); ?>
                            </th>
                        <?php endif; ?>

                        <?php if ((int) DropfilesBase::loadValue($this->params, 'table_showdescription', 1) === 1) : ?>
                            <th class="optional">
                                <?php echo JText::_('COM_DROPFILES_DEFAULT_FRONT_DESCRIPTION'); ?>
                            </th>
                        <?php endif; ?>

                        <?php if ((int) DropfilesBase::loadValue($this->params, 'table_showsize', 1) === 1) : ?>
                            <th class="optional">
                                <?php echo JText::_('COM_DROPFILES_DEFAULT_FRONT_SIZE'); ?>
                            </th>
                        <?php endif; ?>

                        <?php if ((int) DropfilesBase::loadValue($this->params, 'table_showhits', 1) === 1) : ?>
                            <th class="optional">
                                <?php echo JText::_('COM_DROPFILES_DEFAULT_FRONT_HITS'); ?>
                            </th>
                        <?php endif; ?>

                        <?php if ((int) DropfilesBase::loadValue($this->params, 'table_showdateadd', 0) === 1) : ?>
                            <th class="optional">
                                <?php echo JText::_('COM_DROPFILES_DEFAULT_FRONT_SHOWDATEADD'); ?>
                            </th>
                        <?php endif; ?>

                        <?php if ((int) DropfilesBase::loadValue($this->params, 'table_showdatemodified', 0) === 1) : ?>
                            <th class="optional">
                                <?php echo JText::_('COM_DROPFILES_DEFAULT_FRONT_DATEMODIFIED'); ?>
                            </th>
                        <?php endif; ?>
                        <?php if ((int) DropfilesBase::loadValue($this->params, 'table_showdownload', 1) === 1) : ?>
                            <th class="essential">
                                <?php echo JText::_('COM_DROPFILES_DEFAULT_FRONT_DOWNLOAD'); ?>
                            </th>
                        <?php endif; ?>
                    </tr>
                    </thead>

                    <tbody>
                    <?php
                    if (is_array($this->files) && count($this->files)) :
                        foreach ($this->files as $file) : ?>
                            <?php if ($file->ext !== null) : ?>
                                <tr>
                                    <td class="extcol">
                                        <a href="<?php echo $file->link; ?>">
                                            <?php if ((int) $this->componentParams->get('custom_icon', 1) === 1 &&
                                                $file->custom_icon !== '') : ?>
                                                <div class="custom-icon <?php echo $file->ext; ?>">
                                                    <img src="<?php echo $file->custom_icon_thumb; ?>" alt="">
                                                </div>
                                            <?php else : ?>
                                                <span class="ext <?php echo $file->ext; ?>"></span>
                                            <?php endif; ?></a></td>

                                    <?php if ((int) DropfilesBase::loadValue($this->params, 'table_showtitle', 1) === 1) : ?>
                                        <td>
                                            <a class="title"
                                               href="<?php echo $file->link; ?>"><?php echo $file->title; ?></a>
                                        </td>
                                    <?php endif; ?>

                                    <?php if ((int) DropfilesBase::loadValue($this->params, 'table_showversion', 1) === 1) : ?>
                                        <td>
                                            <?php echo $file->versionNumber; ?>
                                        </td>
                                    <?php endif; ?>

                                    <?php if ((int) DropfilesBase::loadValue($this->params, 'table_showdescription', 1) === 1) : ?>
                                        <td>
                                            <?php echo $file->description; ?>
                                        </td>
                                    <?php endif; ?>

                                    <?php if ((int) DropfilesBase::loadValue($this->params, 'table_showsize', 1) === 1) : ?>
                                        <td>
                                            <?php echo DropfilesFilesHelper::bytesToSize($file->size); ?>
                                        </td>
                                    <?php endif; ?>

                                    <?php if ((int) DropfilesBase::loadValue($this->params, 'table_showhits', 1) === 1) : ?>
                                        <td>
                                            <?php echo $file->hits; ?>
                                        </td>
                                    <?php endif; ?>

                                    <?php if ((int) DropfilesBase::loadValue($this->params, 'table_showdateadd', 0) === 1) : ?>
                                        <td>
                                            <?php echo $file->created_time; ?>
                                        </td>
                                    <?php endif; ?>

                                    <?php
                                    if ((int) DropfilesBase::loadValue($this->params, 'table_showdatemodified', 0) === 1) :
                                        ?>
                                        <td>
                                            <?php echo $file->modified_time; ?>
                                        </td>
                                    <?php endif; ?>
                                    <?php if ((int) DropfilesBase::loadValue($this->params, 'table_showdownload', 1) === 1) : ?>
                                        <td class="">
                                            <?php if ($this->viewfileanddowload) : ?>
                                                <a class="downloadlink dropfiles_downloadlink"
                                                   href="<?php echo $file->link; ?>">
                                                    <?php echo JText::_('COM_DROPFILES_DEFAULT_FRONT_DOWNLOAD'); ?>
                                                    <i class="zmdi zmdi-cloud-download dropfiles-download"></i></a>
                                            <?php endif; ?>
                                            <?php if (isset($file->openpdflink)) { ?>
                                                <br/>
                                                <a href="<?php echo $file->openpdflink; ?>" target="_blank">
                                                    <?php echo JText::_('COM_DROPFILES_DEFAULT_FRONT_OPEN'); ?>
                                                    <i class="zmdi zmdi-filter-center-focus dropfiles-preview"></i>
                                                </a>
                                            <?php } else { ?>
                                                <?php if (isset($file->viewerlink)) : ?>
                                                    <a data-id="<?php echo $file->id; ?>"
                                                       data-catid="<?php echo $file->catid; ?>"
                                                       data-file-type="<?php echo $file->ext; ?>"
                                                       class="openlink <?php echo $dropfileslightbox; ?>"
                                                        <?php
                                                        if ((int) $this->componentParams->get('usegoogleviewer', 1) === 2) {
                                                            echo 'target="_blank"';
                                                        } else {
                                                            echo '';
                                                        }; ?>
                                                       href="<?php echo $file->viewerlink; ?>">
                                                        <?php echo JText::_('COM_DROPFILES_DEFAULT_FRONT_PREVIEW'); ?>
                                                        <i class="zmdi zmdi-filter-center-focus dropfiles-preview"></i>
                                                    </a>
                                                <?php endif; ?>
                                            <?php } ?>

                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach;
                    endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>
