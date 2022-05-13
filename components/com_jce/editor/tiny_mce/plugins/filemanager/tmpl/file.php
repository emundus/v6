<?php

/**
 * @copyright     Copyright (c) 2009-2021 Ryan Demmer. All rights reserved
 * @license       GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses
 */
defined('JPATH_PLATFORM') or die;
?>

<div class="uk-grid uk-grid-small uk-form-row">
  <label class="uk-form-label uk-width-1-5" for="href" class="hastip" title="<?php echo JText::_('WF_LABEL_URL_DESC'); ?>"><?php echo JText::_('WF_LABEL_URL'); ?></label>
  <div class="uk-form-controls uk-form-icon uk-form-icon-flip uk-width-4-5">
    <input id="href" type="text" value="" class="filebrowser" data-filebrowser required />
  </div>
</div>

<div class="uk-grid uk-grid-small uk-form-row">
  <label class="uk-form-label uk-width-1-5" for="title" class="hastip" title="<?php echo JText::_('WF_LABEL_TITLE_DESC'); ?>"><?php echo JText::_('WF_LABEL_TITLE'); ?></label>
  <div class="uk-form-controls uk-width-4-5">
    <input id="title" type="text" value="" />
  </div>
</div>

<div class="uk-grid uk-grid-small uk-form-row">
  <label for="format" class="uk-form-label uk-width-1-5 hastip" title="<?php echo JText::_('WF_FILEMANAGER_FORMAT_DESC', 'Format'); ?>"><?php echo JText::_('WF_FILEMANAGER_FORMAT', 'Format'); ?></label>
  <div class="uk-form-controls uk-width-3-10">
    <select id="format">
      <option value="link"><?php echo JText::_('WF_OPTION_FILEMANAGER_FORMAT_LINK', 'Link'); ?></option>
      <option value="embed"><?php echo JText::_('WF_OPTION_FILEMANAGER_FORMAT_IFRAME', 'Embed'); ?></option>
    </select>
  </div>

  <label for="format_openwith" class="uk-form-label uk-width-2-10 hastip" title="<?php echo JText::_('WF_FILEMANAGER_FORMAT_OPENWITH_DESC', 'Open With'); ?>"><?php echo JText::_('WF_FILEMANAGER_FORMAT_OPENWITH', 'Open With'); ?></label>
  <div class="uk-form-controls uk-width-3-10">
    <select id="format_openwith">
      <option value=""><?php echo JText::_('WF_OPTION_NOT_SET'); ?></option>
      <option value="googledocs"><?php echo JText::_('WF_OPTION_FILEMANAGER_GOOGLEDOCS', 'Google Docs Viewer'); ?></option>
      <option value="officeapps"><?php echo JText::_('WF_OPTION_FILEMANAGER_OFFICEAPPS', 'Office Apps Viewer'); ?></option>
    </select>
  </div>
</div>

<div class="uk-placeholder filemanager-link-options">
  <div class="uk-grid uk-grid-small uk-form-row">
    <label for="text" class="uk-form-label uk-width-2-10 hastip" title="<?php echo JText::_('WF_FILEMANAGER_TEXT_DESC'); ?>"><?php echo JText::_('WF_FILEMANAGER_TEXT'); ?></label>
    <div class="uk-form-controls uk-width-4-10">
      <input id="text" type="text" value="" required />
    </div>

    <label for="target" class="uk-form-label uk-width-1-10 hastip" title="<?php echo JText::_('WF_LABEL_TARGET_DESC'); ?>"><?php echo JText::_('WF_LABEL_TARGET'); ?></label>
    <div class="uk-form-controls uk-width-3-10">
      <select id="target">
        <option value=""><?php echo JText::_('WF_OPTION_NOT_SET'); ?></option>
        <option value="_self"><?php echo JText::_('WF_OPTION_TARGET_SELF'); ?></option>
        <option value="_blank"><?php echo JText::_('WF_OPTION_TARGET_BLANK'); ?></option>
        <option value="_parent"><?php echo JText::_('WF_OPTION_TARGET_PARENT'); ?></option>
        <option value="_top"><?php echo JText::_('WF_OPTION_TARGET_TOP'); ?></option>
        <option value="download"><?php echo JText::_('WF_OPTION_TARGET_DOWNLOAD', 'Download'); ?></option>
      </select>
    </div>
  </div>
  <div class="uk-grid uk-grid-small uk-form-row">
    <label class="uk-form-label uk-width-1-5 hastip" title="<?php echo JText::_('WF_FILEMANAGER_LAYOUT_DESC'); ?>"><?php echo JText::_('WF_FILEMANAGER_LAYOUT'); ?></label>
    <div class="uk-width-4-5 uk-grid uk-grid-small" id="layout">

      <div class="uk-form-controls uk-width-1-6" id="layout_icon" data-type="icon">
        <div class="uk-panel uk-panel-box">
          <label class="uk-form-label uk-width-1-1">
            <input type="checkbox" id="layout_icon_check" />
            <?php echo JText::_('WF_FILEMANAGER_LAYOUT_ICON'); ?>
          </label>
        </div>
      </div>

      <div class="uk-form-controls uk-width-1-6" id="layout_text" data-type="text">
        <div class="uk-panel uk-panel-box">
          <label class="uk-form-label uk-width-1-1">
            <input type="checkbox" id="layout_text_check" checked disabled />
            <?php echo JText::_('WF_FILEMANAGER_LAYOUT_TEXT'); ?>
          </label>
        </div>
      </div>

      <div class="uk-form-controls uk-width-2-6" id="layout_size" data-type="size">
        <div class="uk-panel uk-panel-box">
          <label class="uk-form-label uk-width-2-5">
            <input type="checkbox" id="layout_size_check" />
            <?php echo JText::_('WF_FILEMANAGER_LAYOUT_SIZE'); ?>
          </label>
          <div class="uk-form-icon uk-form-icon-flip uk-width-3-5">
            <input type="text" value="" />
            <a href="#" title="<?php echo JText::_('WF_FILEMANAGER_LAYOUT_RELOAD'); ?>" class="uk-icon-refresh layout_option_reload"></a>
          </div>
        </div>
      </div>

      <div class="uk-form-controls uk-width-2-6" id="layout_date" data-type="date">
        <div class="uk-panel uk-panel-box">
          <label class="uk-form-label uk-width-2-5">
            <input type="checkbox" id="layout_date_check" />
            <?php echo JText::_('WF_FILEMANAGER_LAYOUT_DATE'); ?>
          </label>
          <div class="uk-form-icon uk-form-icon-flip uk-width-3-5">
            <input type="text" value="" />
            <a href="#" title="<?php echo JText::_('WF_FILEMANAGER_LAYOUT_RELOAD'); ?>" class="uk-icon-refresh layout_option_reload"></a>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="uk-grid uk-grid-small uk-form-row">
    <label for="size_class" class="uk-form-label uk-width-1-5 hastip" title="<?php echo JText::_('WF_FILEMANAGER_SIZE_CLASS_DESC'); ?>"><?php echo JText::_('WF_FILEMANAGER_SIZE_CLASS'); ?></label>
    <div class="uk-form-controls uk-width-3-10">
      <input type="text" id="size_class" class="uk-datalist" list="size_class_datalist" multiple />
      <datalist id="size_class_datalist"></datalist>
    </div>
    <label for="date_class" class="uk-form-label uk-width-1-5 hastip" title="<?php echo JText::_('WF_FILEMANAGER_DATE_CLASS_DESC'); ?>"><?php echo JText::_('WF_FILEMANAGER_DATE_CLASS'); ?></label></td>
    <div class="uk-form-controls uk-width-3-10">
      <input type="text" id="date_class" class="uk-datalist" list="date_class_datalist" multiple />
      <datalist id="date_class_datalist"></datalist>
    </div>
  </div>
</div>
<div class="uk-placeholder filemanager-embed-options">
  <div class="uk-grid uk-grid-small uk-form-row">
    <label class="hastip uk-form-label uk-width-1-5" title="<?php echo JText::_('WF_LABEL_DIMENSIONS_DESC'); ?>">
      <?php echo JText::_('WF_LABEL_DIMENSIONS'); ?>
    </label>
    <div class="uk-form-control uk-width-1-1 uk-width-small-4-5 uk-form-constrain uk-flex">

      <div class="uk-form-controls">
        <input type="text" id="embed_width" value="" class="uk-text-muted uk-text-center" />
      </div>

      <div class="uk-form-controls">
        <strong class="uk-form-label uk-text-center uk-vertical-align-middle">&times;</strong>
      </div>

      <div class="uk-form-controls">
        <input type="text" id="embed_height" value="" class="uk-text-muted uk-text-center" />
      </div>

      <label class="uk-form-label">
        <input class="uk-constrain-checkbox" type="checkbox" checked />
        <?php echo JText::_('WF_LABEL_PROPORTIONAL'); ?>
      </label>
    </div>
  </div>

  <div class="uk-grid uk-grid-small uk-form-row">
    <label for="align" class="hastip uk-form-label uk-width-1-5" title="<?php echo JText::_('WF_LABEL_ALIGN_DESC'); ?>">
      <?php echo JText::_('WF_LABEL_ALIGN'); ?>
    </label>

    <div class="uk-width-2-5">
      <div class="uk-form-controls uk-width-9-10">
        <select id="align">
          <option value=""><?php echo JText::_('WF_OPTION_NOT_SET'); ?></option>
          <optgroup label="------------">
            <option value="left"><?php echo JText::_('WF_OPTION_ALIGN_LEFT'); ?></option>
            <option value="center"><?php echo JText::_('WF_OPTION_ALIGN_CENTER'); ?></option>
            <option value="right"><?php echo JText::_('WF_OPTION_ALIGN_RIGHT'); ?></option>
          </optgroup>
          <optgroup label="------------">
            <option value="top"><?php echo JText::_('WF_OPTION_ALIGN_TOP'); ?></option>
            <option value="middle"><?php echo JText::_('WF_OPTION_ALIGN_MIDDLE'); ?></option>
            <option value="bottom"><?php echo JText::_('WF_OPTION_ALIGN_BOTTOM'); ?></option>
          </optgroup>
        </select>
      </div>
    </div>
    <div class="uk-width-2-5">
      <label for="clear" class="hastip uk-form-label uk-width-3-10" title="<?php echo JText::_('WF_LABEL_CLEAR_DESC'); ?>">
        <?php echo JText::_('WF_LABEL_CLEAR'); ?>
      </label>
      <div class="uk-form-controls uk-width-7-10">
        <select id="clear" disabled>
          <option value=""><?php echo JText::_('WF_OPTION_NOT_SET'); ?></option>
          <option value="none"><?php echo JText::_('WF_OPTION_CLEAR_NONE'); ?></option>
          <option value="both"><?php echo JText::_('WF_OPTION_CLEAR_BOTH'); ?></option>
          <option value="left"><?php echo JText::_('WF_OPTION_CLEAR_LEFT'); ?></option>
          <option value="right"><?php echo JText::_('WF_OPTION_CLEAR_RIGHT'); ?></option>
        </select>
      </div>
    </div>
  </div>

  <div class="uk-hidden-mini uk-grid uk-grid-small">
    <label for="margin" class="hastip uk-form-label uk-width-1-5" title="<?php echo JText::_('WF_LABEL_MARGIN_DESC'); ?>">
      <?php echo JText::_('WF_LABEL_MARGIN'); ?>
    </label>
    <div class="uk-form-controls uk-width-4-5 uk-grid uk-grid-small uk-form-equalize">

      <label for="margin_top" class="uk-form-label">
        <?php echo JText::_('WF_OPTION_TOP'); ?>
      </label>
      <div class="uk-form-controls">
        <input type="text" id="margin_top" value="" />
      </div>

      <label for="margin_right" class="uk-form-label">
        <?php echo JText::_('WF_OPTION_RIGHT'); ?>
      </label>
      <div class="uk-form-controls">
        <input type="text" id="margin_right" value="" />
      </div>

      <label for="margin_bottom" class="uk-form-label">
        <?php echo JText::_('WF_OPTION_BOTTOM'); ?>
      </label>
      <div class="uk-form-controls">
        <input type="text" id="margin_bottom" value="" />
      </div>

      <label for="margin_left" class="uk-form-label">
        <?php echo JText::_('WF_OPTION_LEFT'); ?>
      </label>
      <div class="uk-form-controls">
        <input type="text" id="margin_left" value="" />
      </div>
      <label class="uk-form-label">
        <input type="checkbox" class="uk-equalize-checkbox" checked />
        <?php echo JText::_('WF_LABEL_EQUAL'); ?>
      </label>
    </div>
  </div>

  <!-- Sample image for setting css styles -->
  <img id="sample" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" class="uk-hidden" />
</div>