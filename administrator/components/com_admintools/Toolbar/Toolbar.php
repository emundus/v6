<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\AdminTools\Admin\Toolbar;

use FOF40\Toolbar\Toolbar as FOFToolbar;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

defined('_JEXEC') || die;

class Toolbar extends FOFToolbar
{
	public function onControlPanelsBrowse()
	{
		// Set the toolbar title
		if (ADMINTOOLS_PRO)
		{
			ToolbarHelper::title(Text::_('COM_ADMINTOOLS_TITLE_DASHBOARD_PRO') . ' <small>' . ADMINTOOLS_VERSION . '</small>', 'admintools');
		}
		else
		{
			ToolbarHelper::title(Text::_('COM_ADMINTOOLS_TITLE_DASHBOARD_CORE') . ' <small>' . ADMINTOOLS_VERSION . '</small>', 'admintools');
		}

		ToolbarHelper::preferences('com_admintools');
	}

	public function onEmergencyOfflinesBrowse()
	{
		ToolbarHelper::title(Text::_('COM_ADMINTOOLS_TITLE_EOM'), 'admintools');
		ToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools');
	}

	public function onMasterPasswordsBrowse()
	{
		ToolbarHelper::title(Text::_('COM_ADMINTOOLS_TITLE_MASTERPW'), 'admintools');
		ToolbarHelper::save();
		ToolbarHelper::divider();
		ToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools');
	}

	public function onAdminPasswordsBrowse()
	{
		// Set the toolbar title
		ToolbarHelper::title(Text::_('COM_ADMINTOOLS_TITLE_ADMINPW'), 'admintools');
		ToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools');
	}

	public function onHtaccessMakersBrowse()
	{
		ToolbarHelper::title(Text::_('COM_ADMINTOOLS_TITLE_HTMAKER'), 'admintools');
		ToolbarHelper::save('save', 'COM_ADMINTOOLS_LBL_HTACCESSMAKER_SAVE');
		ToolbarHelper::apply('apply', 'COM_ADMINTOOLS_LBL_HTACCESSMAKER_APPLY');
		ToolbarHelper::divider();

		Text::script('COM_ADMINTOOLS_LBL_SERVERTECH_RESET_CONFIRM', true);

		$text = Text::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_RESET');
		$html = <<<HTML
<button class="btn btn-sm btn-small btn-danger" onclick="if (!confirm(Joomla.JText._('COM_ADMINTOOLS_LBL_SERVERTECH_RESET_CONFIRM'))) return;Joomla.submitbutton('reset');">
	<span class="icon-lightning"></span>
	$text
</button>
HTML;
		$bar  = \Joomla\CMS\Toolbar\Toolbar::getInstance('toolbar');
		$bar->appendButton('Custom', $html);

		$bar = \Joomla\CMS\Toolbar\Toolbar::getInstance('toolbar');
		$bar->appendButton('Popup', 'search', 'Preview', 'index.php?option=com_admintools&view=HtaccessMaker&task=preview&tmpl=component', 640, 380);

		ToolbarHelper::divider();
		ToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools');
	}

	public function onNginXConfMakersBrowse()
	{
		ToolbarHelper::title(Text::_('COM_ADMINTOOLS_TITLE_NGINXMAKER'), 'admintools');
		ToolbarHelper::save('save', 'COM_ADMINTOOLS_LBL_NGINXCONFMAKER_SAVE');
		ToolbarHelper::apply('apply', 'COM_ADMINTOOLS_LBL_NGINXCONFMAKER_APPLY');
		ToolbarHelper::divider();

		Text::script('COM_ADMINTOOLS_LBL_SERVERTECH_RESET_CONFIRM', true);

		$text = Text::_('COM_ADMINTOOLS_LBL_NGINXCONFMAKER_RESET');
		$html = <<<HTML
<button class="btn btn-sm btn-small btn-danger" onclick="if (!confirm(Joomla.JText._('COM_ADMINTOOLS_LBL_SERVERTECH_RESET_CONFIRM'))) return;Joomla.submitbutton('reset');">
	<span class="icon-lightning"></span>
	$text
</button>
HTML;
		$bar  = \Joomla\CMS\Toolbar\Toolbar::getInstance('toolbar');
		$bar->appendButton('Custom', $html);

		$bar = \Joomla\CMS\Toolbar\Toolbar::getInstance('toolbar');
		$bar->appendButton('Popup', 'search', 'Preview', 'index.php?option=com_admintools&view=NginXConfMaker&task=preview&tmpl=component', 640, 380);

		ToolbarHelper::divider();
		ToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools');
	}

	public function onWebConfigMakersBrowse()
	{
		ToolbarHelper::title(Text::_('COM_ADMINTOOLS_TITLE_WCMAKER'), 'admintools');
		ToolbarHelper::save('save', 'COM_ADMINTOOLS_LBL_WEBCONFIGMAKER_SAVE');
		ToolbarHelper::apply('apply', 'COM_ADMINTOOLS_LBL_WEBCONFIGMAKER_APPLY');
		ToolbarHelper::divider();

		Text::script('COM_ADMINTOOLS_LBL_SERVERTECH_RESET_CONFIRM', true);

		$text = Text::_('COM_ADMINTOOLS_LBL_WEBCONFIGMAKER_RESET');
		$html = <<<HTML
<button class="btn btn-sm btn-small btn-danger" onclick="if (!confirm(Joomla.JText._('COM_ADMINTOOLS_LBL_SERVERTECH_RESET_CONFIRM'))) return;Joomla.submitbutton('reset');">
	<span class="icon-lightning"></span>
	$text
</button>
HTML;
		$bar  = \Joomla\CMS\Toolbar\Toolbar::getInstance('toolbar');
		$bar->appendButton('Custom', $html);

		$bar = \Joomla\CMS\Toolbar\Toolbar::getInstance('toolbar');
		$bar->appendButton('Popup', 'search', 'Preview', 'index.php?option=com_admintools&view=WebConfigMaker&task=preview&tmpl=component', 640, 380);

		ToolbarHelper::divider();
		ToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools');
	}

	public function onWebApplicationFirewallsDefault()
	{
		ToolbarHelper::title(Text::_('COM_ADMINTOOLS_TITLE_WAF'), 'admintools');
		ToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools');
	}

	public function onConfigureWAFsDefault()
	{
		ToolbarHelper::title(Text::_('COM_ADMINTOOLS_TITLE_WAFCONFIG'), 'admintools');
		ToolbarHelper::apply();
		ToolbarHelper::save();
		ToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools&view=WebApplicationFirewall');
	}

	public function onWAFBlacklistedRequestsBrowse()
	{
		ToolbarHelper::title(Text::_('COM_ADMINTOOLS_TITLE_WAFBLACKLISTS'), 'admintools');

		if ($this->perms->create)
		{
			ToolbarHelper::addNew();
		}

		if ($this->perms->delete)
		{
			$msg = Text::_('COM_ADMINTOOLS_CONFIRM_DELETE');
			ToolbarHelper::deleteList(strtoupper($msg));
		}

		ToolbarHelper::divider();
		ToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools&view=WebApplicationFirewall');
	}

	public function onWAFBlacklistedRequestsAdd()
	{
		parent::onAdd();

		ToolbarHelper::title(Text::_('COM_ADMINTOOLS_TITLE_WAFBLACKLISTS_EDIT'), 'admintools');
	}

	public function onWAFBlacklistedRequestsEdit()
	{
		$this->onWAFBlacklistedRequestsAdd();
	}

	public function onExceptionsFromWAFsBrowse()
	{
		parent::onBrowse();

		ToolbarHelper::title(Text::_('COM_ADMINTOOLS_TITLE_WAFEXCEPTIONS'), 'admintools');
		ToolbarHelper::divider();
		ToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools&view=WebApplicationFirewall');
	}

	public function onExceptionsFromWAFsAdd()
	{
		parent::onAdd();

		ToolbarHelper::title(Text::_('COM_ADMINTOOLS_TITLE_WAFEXCEPTIONS_EDIT'), 'admintools');
	}

	public function onExceptionsFromWAFsEdit()
	{
		$this->onExceptionsFromWAFsAdd();
	}

	public function onWhitelistedAddressesBrowse()
	{
		if ($this->perms->delete)
		{
			ToolbarHelper::deleteList();
		}
		if ($this->perms->edit)
		{
			ToolbarHelper::editList();
		}
		if ($this->perms->create)
		{
			ToolbarHelper::addNew();
		}

		$this->renderSubmenu();

		ToolbarHelper::title(Text::_('COM_ADMINTOOLS_TITLE_IPWL'), 'admintools');
		ToolbarHelper::divider();
		ToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools&view=WebApplicationFirewall');
	}

	public function onWhitelistedAddressesAdd()
	{
		parent::onAdd();
		ToolbarHelper::title(Text::_('COM_ADMINTOOLS_TITLE_IPWL_EDIT'), 'admintools');
	}

	public function onWhitelistedAddressesEdit()
	{
		$this->onWhitelistedAddressesAdd();
	}

	public function onBlacklistedAddressesBrowse()
	{
		if ($this->perms->create)
		{
			$text = Text::_('COM_ADMINTOOLS_BLACKLISTEDADDRESSES_IMPORT');

			$html = <<<HTML
<button class="btn btn-sm btn-small btn-primary" onclick="location.href='index.php?option=com_admintools&amp;view=BlacklistedAddresses&amp;task=import';">
	<span class="icon-upload"></span>
	$text
</button>
HTML;
			$bar  = \Joomla\CMS\Toolbar\Toolbar::getInstance('toolbar');
			$bar->appendButton('Custom', $html);
		}

		$text = Text::_('COM_ADMINTOOLS_BLACKLISTEDADDRESSES_EXPORT');

		$html = <<<HTML
<button class="btn btn-sm btn-small btn-primary" onclick="location.href='index.php?option=com_admintools&amp;view=BlacklistedAddresses&amp;task=export&amp;format=csv';">
	<span class="icon-download"></span>
	$text
</button>
HTML;
		$bar  = \Joomla\CMS\Toolbar\Toolbar::getInstance('toolbar');
		$bar->appendButton('Custom', $html);

		if ($this->perms->delete)
		{
			ToolbarHelper::deleteList();
		}
		if ($this->perms->edit)
		{
			ToolbarHelper::editList();
		}
		if ($this->perms->create)
		{
			ToolbarHelper::addNew();
		}

		$this->renderSubmenu();

		ToolbarHelper::title(Text::_('COM_ADMINTOOLS_TITLE_IPBL'), 'admintools');
		ToolbarHelper::divider();
		ToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools&view=WebApplicationFirewall');
	}

	public function onBlacklistedAddressesAdd()
	{
		parent::onAdd();
		ToolbarHelper::title(Text::_('COM_ADMINTOOLS_TITLE_IPBL_EDIT'), 'admintools');
	}

	public function onBlacklistedAddressesEdit()
	{
		$this->onBlacklistedAddressesAdd();
	}

	public function onBlacklistedAddressesImport()
	{
		ToolbarHelper::title(Text::_('COM_ADMINTOOLS_TITLE_IPBL'), 'admintools');
		ToolbarHelper::custom('doimport', 'upload', '', Text::_('COM_ADMINTOOLS_BLACKLISTEDADDRESSES_IMPORT'), false);
		ToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools&view=BlacklistedAddresses');
	}

	public function onBadWordsBrowse()
	{
		if ($this->perms->delete)
		{
			ToolbarHelper::deleteList();
		}
		if ($this->perms->edit)
		{
			ToolbarHelper::editList();
		}
		if ($this->perms->create)
		{
			ToolbarHelper::addNew();
		}

		$this->renderSubmenu();

		ToolbarHelper::title(Text::_('COM_ADMINTOOLS_TITLE_BADWORDS'), 'admintools');
		ToolbarHelper::divider();
		ToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools&view=WebApplicationFirewall');
	}

	public function onBadWordsAdd()
	{
		parent::onAdd();
		ToolbarHelper::title(Text::_('COM_ADMINTOOLS_TITLE_BADWORDS_EDIT'), 'admintools');
	}

	public function onBadWordsEdit()
	{
		$this->onBadWordsAdd();
	}

	public function onSecurityExceptionsBrowse()
	{
		if ($this->perms->delete)
		{
			ToolbarHelper::deleteList();
		}

		$this->renderSubmenu();

		ToolbarHelper::title(Text::_('COM_ADMINTOOLS_TITLE_LOG'), 'admintools');
		ToolbarHelper::divider();
		ToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools&view=WebApplicationFirewall');
	}

	public function onAutoBannedAddressesBrowse()
	{
		if ($this->perms->delete)
		{
			ToolbarHelper::deleteList();
		}

		$this->renderSubmenu();

		ToolbarHelper::title(Text::_('COM_ADMINTOOLS_TITLE_IPAUTOBAN'), 'admintools');
		ToolbarHelper::divider();
		ToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools&view=WebApplicationFirewall');
	}

	public function onIPAutoBanHistoriesBrowse()
	{
		if ($this->perms->delete)
		{
			ToolbarHelper::deleteList();
		}

		$this->renderSubmenu();

		ToolbarHelper::title(Text::_('COM_ADMINTOOLS_TITLE_IPAUTOBANHISTORY'), 'admintools');
		ToolbarHelper::divider();
		ToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools&view=WebApplicationFirewall');
	}

	public function onUnblockIPsBrowse()
	{
		ToolbarHelper::title(Text::_('COM_ADMINTOOLS_TITLE_UNBLOCKIP'), 'admintools');
		ToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools&view=WebApplicationFirewall');
	}

	public function onDbprefixesBrowse()
	{
		ToolbarHelper::title(Text::_('COM_ADMINTOOLS_TITLE_DBPREFIX'), 'admintools');
		ToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools');
	}

	public function onAdminusersBrowse()
	{
		ToolbarHelper::title(Text::_('COM_ADMINTOOLS_TITLE_ADMINUSER'), 'admintools');
		ToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools');
	}

	public function onConfigureFixPermissionsBrowse()
	{
		$subtitle_key = 'COM_ADMINTOOLS_TITLE_FIXPERMSCONFIG';
		ToolbarHelper::title(Text::_($subtitle_key), 'admintools');

		ToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools');
	}

	public function onFixPermissionsBrowse()
	{
		ToolbarHelper::title(Text::_('COM_ADMINTOOLS_TITLE_FIXPERMS'), 'admintools');
	}

	public function onFixPermissionsRun()
	{
		$this->onFixPermissionsBrowse();
	}

	public function onSEOAndLinkToolsBrowse()
	{
		ToolbarHelper::title(Text::_('COM_ADMINTOOLS_TITLE_SEOANDLINK'), 'admintools');

		ToolbarHelper::apply();
		ToolbarHelper::save();
		ToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools');
	}

	public function onCleanTempDirectoriesBrowse()
	{
		ToolbarHelper::title(Text::_('COM_ADMINTOOLS_TITLE_CLEANTMP'), 'admintools');
	}

	public function onCleanTempDirectoriesRun()
	{
		$this->onCleanTempDirectoriesBrowse();
	}

	public function onChangeDBCollationsBrowse()
	{
		ToolbarHelper::title(Text::_('COM_ADMINTOOLS_TITLE_DBCHCOL'), 'admintools');
		ToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools');
	}

	public function onDatabaseTools()
	{
		// Set the toolbar title
		ToolbarHelper::title(Text::_('COM_ADMINTOOLS_TITLE_DBTOOLS'), 'admintools');
	}

	public function onRedirectionsBrowse()
	{
		ToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools');
		ToolbarHelper::custom('copy', 'copy.png', 'copy_f2.png', 'JLIB_HTML_BATCH_COPY', false);
		parent::onBrowse();
		ToolbarHelper::title(Text::_('COM_ADMINTOOLS_TITLE_REDIRS'), 'admintools');
	}

	public function onRedirectionsAdd()
	{
		parent::onAdd();
		ToolbarHelper::title(Text::_('COM_ADMINTOOLS_TITLE_REDIRS_EDIT'), 'admintools');
	}

	public function onRedirectionsEdit()
	{
		$this->onRedirectionsAdd();
	}

	public function onAclsBrowse()
	{
		ToolbarHelper::title(Text::_('COM_ADMINTOOLS_TITLE_ACL'), 'admintools');
		ToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools');
	}

	public function onScannersBrowse()
	{
		ToolbarHelper::title(Text::_('COM_ADMINTOOLS_TITLE_SCANNER'), 'admintools');
		ToolbarHelper::apply();
		ToolbarHelper::save();
		ToolbarHelper::divider();
		ToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools&view=Scans');
	}

	public function onScansBrowse()
	{
		// Set toolbar title
		$subtitle_key = 'COM_ADMINTOOLS_TITLE_' . strtoupper($this->container->input->getCmd('view', 'cpanel'));
		ToolbarHelper::title(Text::_($subtitle_key), 'admintools');

		$canScan = $this->container->platform->getUser()->authorise('core.manage', 'com_admintools');

		if ($canScan)
		{
			$bar  = \Joomla\CMS\Toolbar\Toolbar::getInstance('toolbar');
			$icon = 'play';
			$bar->appendButton('Link', $icon, Text::_('COM_ADMINTOOLS_MSG_SCAN_SCANNOW'), 'javascript:startScan()');

			$iconPurge = 'trash';
			$bar->appendButton('Link', $iconPurge, Text::_('COM_ADMINTOOLS_MSG_SCAN_PURGE'), 'index.php?option=com_admintools&view=Scans&task=purge');
			ToolbarHelper::divider();
		}

		// Add toolbar buttons
		if ($this->perms->delete)
		{
			ToolbarHelper::deleteList();
		}

		ToolbarHelper::divider();
		ToolbarHelper::preferences('com_admintools');
		ToolbarHelper::divider();
		ToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools');
	}

	public function onScansEdit()
	{
		ToolbarHelper::title(Text::_('COM_ADMINTOOLS_TITLE_SCANS_COMMENT'), 'admintools');

		ToolbarHelper::apply();
		ToolbarHelper::save();
		ToolbarHelper::cancel();
	}

	public function onScanAlertsBrowse()
	{
		$scan_id = $this->container->input->getInt('scan_id', 0);
		$bar     = \Joomla\CMS\Toolbar\Toolbar::getInstance('toolbar');

		$subtitle_key = 'COM_ADMINTOOLS_TITLE_' . strtoupper($this->container->input->getCmd('view', 'cpanel'));
		ToolbarHelper::title(Text::sprintf($subtitle_key, $scan_id), 'admintools');

		ToolbarHelper::publishList('publish', 'COM_ADMINTOOLS_LBL_SCANALERTS_MARKSAFE');
		ToolbarHelper::unpublishList('unpublish', 'COM_ADMINTOOLS_LBL_SCANALERTS_MARKUNSAFE');
		$markAllSafeLink = 'index.php?option=com_admintools&view=ScanAlerts&task=markallsafe&scan_id=' . $scan_id;
		$bar->appendButton('Link', 'checkmark', Text::_('COM_ADMINTOOLS_LBL_SCANALERTS_MARKALLSAFE'), $markAllSafeLink);

		ToolbarHelper::divider();

		$printLink = 'index.php?option=com_admintools&view=ScanAlerts&tmpl=component&layout=print&scan_id=' . $scan_id;
		$bar->appendButton('Link', 'print', Text::_('COM_ADMINTOOLS_MSG_COMMON_PRINT'), $printLink);

		$csvLink = 'index.php?option=com_admintools&view=ScanAlerts&format=csv&scan_id=' . $scan_id;
		$bar->appendButton('Link', 'download', Text::_('COM_ADMINTOOLS_MSG_COMMON_CSV'), $csvLink);

		ToolbarHelper::divider();
		ToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools&view=Scans');
	}

	public function onScanAlertsEdit()
	{
		ToolbarHelper::apply();
		ToolbarHelper::save();
		ToolbarHelper::cancel();
	}

	public function onTwofactorsBrowse()
	{
		ToolbarHelper::title(Text::_('COM_ADMINTOOLS_TITLE_TWOFACTOR'), 'admintools');
		ToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools&view=WebApplicationFirewall');
	}

	public function onWAFEmailTemplatesBrowse()
	{
		parent::onBrowse();
		ToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools&view=WebApplicationFirewall');
	}

	public function onImportAndExportsExport()
	{
		ToolbarHelper::title(Text::_('COM_ADMINTOOLS_TITLE_EXPORT_SETTINGS'), 'admintools');

		ToolbarHelper::apply('doexport', Text::_('COM_ADMINTOOLS_TITLE_EXPORT_SETTINGS'));
		ToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools');
	}

	public function onImportAndExportsImport()
	{
		ToolbarHelper::title(Text::_('COM_ADMINTOOLS_TITLE_IMPORT_SETTINGS'), 'admintools');

		ToolbarHelper::apply('doimport', Text::_('COM_ADMINTOOLS_TITLE_IMPORT_SETTINGS'));
		ToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools');
	}

	public function onQuickStartsBrowse()
	{
		// Set toolbar title
		$subtitle_key = strtoupper('COM_ADMINTOOLS_TITLE_' . $this->container->input->getCmd('view', 'cpanel'));
		ToolbarHelper::title(Text::_('COM_ADMINTOOLS') . ': ' . Text::_($subtitle_key), 'admintools');

		ToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools');
	}

	public function onSchedulingInformations()
	{
		// Set the toolbar title
		ToolbarHelper::title(Text::_('COM_ADMINTOOLS_TITLE_SCHEDULINGINFORMATION'), 'admintools');
		ToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools');
	}

	public function onTempSuperUsersBrowse()
	{
		ToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools');

		parent::onBrowse();
	}

	/**
	 * Disable rendering a toolbar.
	 *
	 * @return array
	 */
	protected function getMyViews(): array
	{
		return [];
	}
}
