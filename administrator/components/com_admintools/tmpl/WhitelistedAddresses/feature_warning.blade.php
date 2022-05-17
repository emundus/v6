<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2022 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') || die;

/** @var  Akeeba\AdminTools\Admin\View\WhitelistedAddresses\Html $this */

?>
@if ($this->componentParams->getValue('ipwl', 0) != 1)
<div class="akeeba-block--failure">
	<h3>@lang('COM_ADMINTOOLS_WHITELISTEDADDRESSES_ERR_NOTENABLED_TITLE')</h3>
	<p>
		@lang('COM_ADMINTOOLS_WHITELISTEDADDRESSES_ERR_NOTENABLED_BODY')
	</p>
</div>
@endif
