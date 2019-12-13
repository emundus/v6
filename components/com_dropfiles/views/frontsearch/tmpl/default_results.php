<?php if (is_array($this->files) && count($this->files) > 0) : ?>
    <table class="table">
        <thead>
        <th width="50%">
            <a href="#" class="orderingCol <?php
            if ($this->ordering === 'type') {
                echo 'curentOrderingCol';
            } ?>"
               data-ordering="type"
               data-direction="<?php echo ($this->ordering === 'type' && $this->dir === 'asc') ? 'desc' : 'asc'; ?>">
                <?php echo JText::_('COM_DROPFILES_SEARCH_FILETYPE'); ?></a>
            /
            <a href="#" class="orderingCol <?php
            if ($this->ordering === 'title') {
                echo 'curentOrderingCol';
            } ?>"
               data-ordering="title"
               data-direction="<?php echo ($this->ordering === 'title' && $this->dir === 'asc') ? 'desc' : 'asc'; ?>">
                <?php echo JText::_('COM_DROPFILES_SEARCH_FILENAME'); ?></a>
        </th>
        <?php if ($this->params->get('usegoogleviewer', 1) > 0) : ?>
            <th><?php echo JText::_('COM_DROPFILES_DEFAULT_FRONT_OPEN'); ?></th>
        <?php endif; ?>
        <th><a href="#" class="orderingCol <?php echo ($this->ordering === 'created') ? 'curentOrderingCol' : ''; ?>"
               data-ordering="created"
               data-direction="<?php echo ($this->ordering === 'created' && $this->dir === 'asc') ? 'desc' : 'asc'; ?>">
                <?php echo JText::_('COM_DROPFILES_CREATION_DATE'); ?></a>
        </th>
        <th><a href="#" class="orderingCol <?php echo ($this->ordering === 'cat') ? 'curentOrderingCol' : ''; ?>"
               data-ordering="cat"
               data-direction="<?php echo ($this->ordering === 'cat' && $this->dir === 'asc') ? 'desc' : 'asc'; ?>">
                <?php echo JText::_('COM_DROPFILES_SEARCH_CATEGORY'); ?></a>
        </th>
        </thead>
        <tbody>

        <?php foreach ($this->files as $key => $file) : ?>
            <tr>
                <td class="title">
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
                <?php
                $dropfileslightbox = (int) $this->params->get('usegoogleviewer', 1) === 1 ? 'dropfileslightbox' : '';
                if ($this->params->get('usegoogleviewer', 1) > 0) : ?>
                    <td class="viewer">
                        <?php if ($file->viewerlink) : ?>
                            <a data-id="<?php echo $file->id; ?>" data-catid="<?php echo $file->catid; ?>"
                               data-file-type="<?php echo $file->ext; ?>"
                               class="openlink <?php echo $dropfileslightbox ?>"
                                <?php
                                echo ((int) $this->params->get('usegoogleviewer', 1) === 2) ? 'target="_blank"' : ''; ?>
                               href='<?php echo $file->viewerlink; ?>'>
                                <img src="<?php JUri::root(); ?>components/com_dropfiles/assets/images/open_24.png"
                                     title="<?php echo JText::_('COM_DROPFILES_DEFAULT_FRONT_OPEN'); ?>"/>
                            </a>
                        <?php endif; ?>
                    </td>
                <?php endif; ?>
                <td class="created">
                    <?php echo $file->created_time; ?>
                </td>
                <td class="catname">
                    <a target="_blank"
                       href="<?php
                               $urlRoute = 'index.php?option=com_dropfiles&task=frontsearch.viewcat&catid=';
                               echo JRoute::_($urlRoute . $file->catid);
                        ?>">
                        <?php echo $file->cattitle; ?>
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>

        </tbody>
    </table>
    <?php echo $this->pagination->getPaginationLinks(); ?>
<?php elseif ($this->doSearch) : ?>
    <h5 class="text-center"><?php echo JText::_('COM_DROPFILES_SEARCH_NO_RESULT'); ?></h5>
    <?php
endif ?>
