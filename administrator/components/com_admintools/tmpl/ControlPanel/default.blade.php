<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

use Akeeba\AdminTools\Admin\View\ControlPanel\Html;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/** @var  Html $this For type hinting in the IDE */

// Protect from unauthorized access
defined('_JEXEC') || die;

?>
@include('admin:com_admintools/ControlPanel/warnings')

<div>
	<div class="akeeba-container--50-50">
		<div>
			@if ($this->isRescueMode)
				<div class="akeeba-block--failure">
					<div>
						<h3>@lang('COM_ADMINTOOLS_CONTROLPANEL_RESCUEMODE_HEAD')</h3>
						<p>
							@lang('COM_ADMINTOOLS_CONTROLPANEL_RESCUEMODE_MESSAGE')
						</p>
						<p>
							<a class="akeeba-btn--primary"
							   href="https://www.akeeba.com/documentation/troubleshooter/atwafissues.html"
							   target="_blank"
							>
								<span class="akion-information-circled"></span>
								@lang('COM_ADMINTOOLS_CONTROLPANEL_RESCUEMODE_BTN_HOWTOUNBLOCK')
							</a>
							<a class="akeeba-btn--red"
							   href="index.php?option=com_admintools&view=ControlPanel&task=endRescue"
							>
								<span class="akion-power"></span>
								@lang('COM_ADMINTOOLS_CONTROLPANEL_RESCUEMODE_BTN_ENDRESCUE')
							</a>
						</p>
					</div>
				</div>
			@else
				@include('admin:com_admintools/ControlPanel/plugin_warning')
			@endif

			<div id="selfBlocked" class="text-center" style="display: none;">
				<a class="akeeba-btn--red--big"
				   href="@route('index.php?option=com_admintools&view=ControlPanel&task=unblockme')">
					<span class="akion-unlocked"></span>
					@lang('COM_ADMINTOOLS_CONTROLPANEL_UNBLOCK_ME')
				</a>
			</div>

			@unless ($this->hasValidPassword)
				@include('admin:com_admintools/ControlPanel/masterpassword')
			@endunless

			@include('admin:com_admintools/ControlPanel/security')
			@include('admin:com_admintools/ControlPanel/tools')

			@if (ADMINTOOLS_PRO && !$this->needsQuickSetup)
				@include('admin:com_admintools/ControlPanel/quicksetup')
			@endif
		</div>

		<div>
			<div class="akeeba-panel--default">
				<header class="akeeba-block-header">
					<h3>@lang('COM_ADMINTOOLS_LBL_CONTROLPANEL_UPDATES')</h3>
				</header>

				<div>
					<p>
						Admin Tools version {{ ADMINTOOLS_VERSION }} &bull;
						<a href="#" id="btnAdminToolsChangelog" class="akeeba-btn--primary--small">CHANGELOG</a>
					</p>

					<p>
						Copyright &copy; 2010&ndash;{{ date('Y') }} Nicholas K. Dionysopoulos /
						<a href="https://www.akeeba.com">Akeeba Ltd</a>
					</p>
					<p>
						If you use Admin Tools {{ ADMINTOOLS_PRO ? 'Professional' : 'Core' }}, please post a rating and
						a review at the <a href="http://extensions.joomla.org/extensions/extension/access-a-security/site-security/admin-tools{{ ADMINTOOLS_PRO ? '-professional' : '' }}">
						Joomla! Extensions Directory</a>.
					</p>
				</div>

				<div id="akeeba-changelog" tabindex="-1" role="dialog" aria-hidden="true" style="display:none;">
					<div class="akeeba-renderer-fef">
						<div class="akeeba-panel--info">
							<header class="akeeba-block-header">
								<h3>@lang('CHANGELOG')</h3>
							</header>
							<div id="DialogBody">
								{{ $this->formattedChangelog }}
							</div>
						</div>
					</div>
				</div>
			</div>

			@unless($this->isPro)
				<div style="text-align: center;">
					<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
						<input type="hidden" name="cmd" value="_s-xclick">
						<input type="hidden" name="hosted_button_id" value="6ZLKK32UVEPWA">

						<p>
							<input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-butcc-donate.gif"
								   border="0"
								   name="submit" alt="PayPal - The safer, easier way to pay online."
								   style="width: 73px;">
							<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1"
								 height="1">
						</p>
					</form>
				</div>
			@endunless

			@if ($this->isPro && $this->showstats)
				@include('admin:com_admintools/ControlPanel/graphs')
				@include('admin:com_admintools/ControlPanel/stats')
			@else
				<?php $this->container->platform->addScriptOptions('admintools.ControlPanel.graphs', 0); ?>
			@endif

			<div id="disclaimer" class="akeeba-block--info">
				<h3>@lang('COM_ADMINTOOLS_LBL_CONTROLPANEL_DISCLAIMER')</h3>

				<p>
					@lang('COM_ADMINTOOLS_LBL_CONTROLPANEL_DISTEXT')
				</p>
			</div>
		</div>
	</div>
</div>

{{ $this->statsIframe ?: '' }}
