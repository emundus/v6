<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') || die;

use Akeeba\AdminTools\Admin\Helper\Select;

/** @var $this \FOF40\View\DataView\Html */
?>
<form action="index.php" method="post" name="adminForm" id="adminForm" class="akeeba-form--horizontal" enctype="multipart/form-data">

    <div class="akeeba-panel--primary">
        <header class="akeeba-block-header">
            <h3>@lang('COM_ADMINTOOLS_IMPORTANDEXPORT_DETAILS')</h3>
        </header>

        <div class="akeeba-form-group">
            <label>@lang('COM_ADMINTOOLS_IMPORTANDEXPORT_DELIMITERS')</label>

            {{ \Akeeba\AdminTools\Admin\Helper\Select::csvdelimiters('csvdelimiters', 1, array('class'=>'minwidth')) }}

            <p class="akeeba-help-text">
                @lang('COM_ADMINTOOLS_IMPORTANDEXPORT_DELIMITERS_DESC')
            </p>
        </div>

        <div class="akeeba-form-group" id="field_delimiter" style="display:none;">
            <label>@lang('COM_ADMINTOOLS_IMPORTANDEXPORT_FIELD_DELIMITERS')</label>

            <input type="text" name="field_delimiter" value="">
            <p class="akeeba-help-text">
                @lang('COM_ADMINTOOLS_IMPORTANDEXPORT_FIELD_DELIMITERS_DESC')
            </p>
        </div>

        <div class="akeeba-form-group" id="field_enclosure" style="display:none">
            <label>@lang('COM_ADMINTOOLS_IMPORTANDEXPORT_FIELD_ENCLOSURE')</label>

            <input type="text" name="field_enclosure" value="">
            <p class="akeeba-help-text">
                @lang('COM_ADMINTOOLS_IMPORTANDEXPORT_FIELD_ENCLOSURE_DESC')
            </p>
        </div>

        <div class="akeeba-form-group">
            <label>@lang('COM_ADMINTOOLS_IMPORTANDEXPORT_FILE')</label>

            <input type="file" name="csvfile"/>
            <p class="akeeba-help-text">
                @lang('COM_ADMINTOOLS_IMPORTANDEXPORT_FILE_DESC')
            </p>
        </div>
    </div>

    <input type="hidden" name="option" value="com_admintools"/>
    <input type="hidden" name="view" value="BlacklistedAddresses"/>
    <input type="hidden" name="task" value=""/>
    <input type="hidden" name="@token()" value="1"/>
</form>
