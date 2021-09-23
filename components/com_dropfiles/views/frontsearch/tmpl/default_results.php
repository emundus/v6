<?php JLoader::register('DropfilesFilesHelper', JPATH_ADMINISTRATOR . '/components/com_dropfiles/helpers/files.php');?>
<?php if (is_array($this->files) && count($this->files) > 0) : ?>
    <div class="Control-Section mediaMenuOption">
        <div class="mediaTableMenu">
            <a title="Columns"><i class="zmdi zmdi-settings"></i></a>
            <ul>
                <li>
                    <input type="checkbox" class="media-item" name="toggle-cols" id="toggle-col-MediaTable-0-1" value="description" > <label for="toggle-col-MediaTable-0-1"><?php echo JText::_('COM_DROPFILES_SEARCH_MEDIA_MENU_DESCRIPTION'); ?></label>
                </li>
                <li>
                    <input type="checkbox" class="media-item" name="toggle-cols" id="toggle-col-MediaTable-0-2" value="version" checked="checked"> <label for="toggle-col-MediaTable-0-2"><?php echo JText::_('COM_DROPFILES_SEARCH_MEDIA_MENU_VERSION'); ?></label>
                </li>
                <li>
                    <input type="checkbox" class="media-item" name="toggle-cols" id="toggle-col-MediaTable-0-3" value="size" checked="checked"> <label for="toggle-col-MediaTable-0-3"><?php echo JText::_('COM_DROPFILES_SEARCH_MEDIA_MENU_SIZE'); ?></label>
                </li>
                <li>
                    <input type="checkbox" class="media-item" name="toggle-cols" id="toggle-col-MediaTable-0-4" value="hits" checked="checked"> <label for="toggle-col-MediaTable-0-4"><?php echo JText::_('COM_DROPFILES_SEARCH_MEDIA_MENU_HITS'); ?></label>
                </li>
                <li>
                    <input type="checkbox" class="media-item" name="toggle-cols" id="toggle-col-MediaTable-0-5" value="date_added" checked="checked"> <label for="toggle-col-MediaTable-0-5"><?php echo JText::_('COM_DROPFILES_SEARCH_MEDIA_MENU_DATE_ADDED'); ?></label>
                </li>
                <li>
                    <input type="checkbox" class="media-item" name="toggle-cols" id="toggle-col-MediaTable-0-6" value="download" checked="checked"> <label for="toggle-col-MediaTable-0-6"><?php echo JText::_('COM_DROPFILES_SEARCH_MEDIA_MENU_DATE_DOWNLOAD'); ?></label>
                </li>
            </ul>
            <input type="hidden" class="media-list" name="media-list" id="total-media-list" value="" style="visibility: hidden">
        </div>
    </div>
    <table class="table">
        <thead>
            <th class="htitle file_title"><?php echo JText::_('COM_DROPFILES_SEARCH_FILE_TITLE'); ?></th>
            <th class="hdescription file_desc filehidden"><?php echo JText::_('COM_DROPFILES_SEARCH_FILE_DESCRIPTION'); ?></th>
            <th class="hversion file_version"><?php echo JText::_('COM_DROPFILES_SEARCH_FILE_VERSION'); ?></th>
            <th class="hsize file_size"><?php echo JText::_('COM_DROPFILES_SEARCH_FILE_SIZE'); ?></th>
            <th class="hhits file_hits"><?php echo JText::_('COM_DROPFILES_SEARCH_FILE_HITS'); ?></th>
            <th class="hcreated file_created"><?php echo JText::_('COM_DROPFILES_SEARCH_FILE_DATE_ADDED'); ?></th>
            <th class="hdownload file_download"><?php echo JText::_('COM_DROPFILES_SEARCH_FILE__DOWNLOAD'); ?></th>
        </thead>
        <tbody>

        <?php foreach ($this->files as $key => $file) : ?>
            <tr>
                <td class="file_title title">
                    <?php if ((int) $this->params->get('custom_icon', 1) === 1 && $file->custom_icon !== '') { ?>
                        <div class="custom-icon <?php echo $file->ext; ?>"><img
                                src="<?php echo $file->custom_icon_thumb; ?>" alt=""></div>
                    <?php } else { ?>
                        <span class="file-icon"><i class="<?php echo $file->ext; ?>"></i></span>
                    <?php } ?>
                    <a class="file-item dropfile-file-link" data-remoteurl="<?php
                    if ($file->remoteurl) {
                        echo 'true';
                    } else {
                        echo 'false';
                    }; ?>"
                       data-id="<?php echo $file->id ?>" href="<?php echo $file->link; ?>"
                       id="file-<?php echo $file->id; ?>"><?php echo $file->title; ?></a>
                </td>
                <td class="file_desc filehidden"><?php echo $file->description; ?></td>
                <td class="file_version"><?php echo $file->version; ?></td>
                <td class="file_size"><?php echo DropfilesFilesHelper::bytesToSize($file->size); ?></td>
                <td class="file_hits"><?php echo $file->hits; ?></td>
                <td class="file_created"><?php echo $file->created_time; ?></td>
                <td class="file_download viewer">
                    <a class="file-item dropfile-file-link downloadlink dropfiles_downloadlink" data-remoteurl="<?php
                    if ($file->remoteurl) {
                        echo 'true';
                    } else {
                        echo 'false';
                    }; ?>"
                       data-id="<?php echo $file->id ?>" href="<?php echo $file->link; ?>"
                       id="file-<?php echo $file->id; ?>">
                        <i class="zmdi zmdi-cloud-download dropfiles-download"></i>
                    </a>
                    <?php
                    $dropfileslightbox = (int) $this->params->get('usegoogleviewer', 1) === 1 ? 'dropfileslightbox' : '';
                    if ($this->params->get('usegoogleviewer', 1) > 0) : ?>
                        <?php if ($file->viewerlink) : ?>
                            <?php if (isset($file->openpdflink)) { ?>
                                <a href="<?php echo $file->openpdflink; ?>" class="openlink dropfiles_previewlink pdf_link"
                                   target="_blank">
                                    <i class="zmdi zmdi-filter-center-focus dropfiles-preview"></i>
                                </a>
                            <?php } else { ?>
                                <a data-id="<?php echo $file->id; ?>" data-catid="<?php echo $file->catid; ?>"
                                   data-file-type="<?php echo $file->ext; ?>"
                                   class="dropfiles_previewlink openlink <?php echo $dropfileslightbox ?>"
                                    <?php echo ((int) $this->params->get('usegoogleviewer', 1) === 2) ? 'target="_blank"' : ''; ?>
                                   href='<?php echo $file->viewerlink; ?>'>
                                    <i class="zmdi zmdi-filter-center-focus dropfiles-preview"></i>
                                </a>
                            <?php } ?>
                        <?php endif; ?>
                    <?php endif;
                    ?>
                </td>
            </tr>
        <?php endforeach; ?>

        </tbody>
    </table>
    <?php echo $this->pagination->getPaginationLinks(); ?>
<?php elseif ($this->doSearch) : ?>
    <p class="text-center"> <?php echo JText::_('COM_DROPFILES_SEARCH_NO_RESULT'); ?> </p>
    <?php
endif ?>
