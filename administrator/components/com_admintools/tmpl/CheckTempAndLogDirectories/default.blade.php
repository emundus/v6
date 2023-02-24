<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

use Joomla\CMS\Language\Text;

/** @var $this \Akeeba\AdminTools\Admin\View\CheckTempAndLogDirectories\Html */

defined('_JEXEC') || die;

?>
<h1 id="check-header">
    @lang('COM_ADMINTOOLS_LBL_CHECKTEMPANDLOGDIRECTORIES_CHECKINPROGRESS')
</h1>

<div class="akeeba-progress">
    <div class="akeeba-progress-fill" style="width:0%;"></div>
    <div class="akeeba-progress-status"></div>
</div>

<div id="message" class="" style="display:none"></div>

<div id="autoclose" class="akeeba-block--info" style="display:none">
	<p>@lang('COM_ADMINTOOLS_LBL_COMMON_AUTOCLOSEIN3S')</p>
</div>
