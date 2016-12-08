<?php
/**
 * @package   AdminTools
 * @copyright 2010-2016 Akeeba Ltd / Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\AdminTools\Admin\Toolbar;

defined('_JEXEC') or die;

use JFactory;
use JText;
use JToolbar;
use JToolbarHelper;

class Toolbar extends \FOF30\Toolbar\Toolbar
{
	/**
	 * Disable rendering a toolbar.
	 *
	 * @return array
	 */
	protected function getMyViews()
	{
		return array();
	}

	public function onControlPanelsBrowse()
	{
		// Set the toolbar title
		if (ADMINTOOLS_PRO)
		{
			JToolbarHelper::title(JText::_('COM_ADMINTOOLS_TITLE_DASHBOARD_PRO') . ' <small>' . ADMINTOOLS_VERSION . '</small>', 'admintools');
		}
		else
		{
			JToolbarHelper::title(JText::_('COM_ADMINTOOLS_TITLE_DASHBOARD_CORE') . ' <small>' . ADMINTOOLS_VERSION . '</small>', 'admintools');
		}

		JToolbarHelper::preferences('com_admintools');
	}

	public function onEmergencyOfflinesBrowse()
	{
		JToolbarHelper::title(JText::_('COM_ADMINTOOLS_TITLE_EOM'), 'admintools');
		JToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools');
	}

	public function onMasterPasswordsBrowse()
	{
		JToolbarHelper::title(JText::_('COM_ADMINTOOLS_TITLE_MASTERPW'), 'admintools');
		JToolbarHelper::save();
		JToolbarHelper::divider();
		JToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools');
	}

	public function onAdminPasswordsBrowse()
	{
		// Set the toolbar title
		JToolbarHelper::title(JText::_('COM_ADMINTOOLS_TITLE_ADMINPW'), 'admintools');
		JToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools');
	}

	public function onHtaccessMakersBrowse()
	{
		JToolbarHelper::title(JText::_('COM_ADMINTOOLS_TITLE_HTMAKER'), 'admintools');
		JToolbarHelper::save('save', 'COM_ADMINTOOLS_LBL_HTACCESSMAKER_SAVE');
		JToolbarHelper::apply('apply', 'COM_ADMINTOOLS_LBL_HTACCESSMAKER_APPLY');
		JToolbarHelper::divider();

		$bar = JToolbar::getInstance('toolbar');
		$bar->appendButton('Popup', 'search', 'Preview', 'index.php?option=com_admintools&view=HtaccessMaker&task=preview&tmpl=component', 640, 380);

		//JToolbarHelper::preview('index.php?option=com_admintools&view=HtaccessMaker&tmpl=component');

		JToolbarHelper::divider();
		JToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools');
	}

	public function onNginXConfMakersBrowse()
	{
		JToolbarHelper::title(JText::_('COM_ADMINTOOLS_TITLE_NGINXMAKER'), 'admintools');
		JToolbarHelper::save('save', 'COM_ADMINTOOLS_LBL_NGINXCONFMAKER_SAVE');
		JToolbarHelper::apply('apply', 'COM_ADMINTOOLS_LBL_NGINXCONFMAKER_APPLY');
		JToolbarHelper::divider();

		$bar = JToolbar::getInstance('toolbar');
		$bar->appendButton('Popup', 'preview', 'Preview', 'index.php?option=com_admintools&view=NginXConfMaker&task=preview&tmpl=component', 640, 380);
		//JToolbarHelper::preview('index.php?option=com_admintools&view=NginXConfMaker&tmpl=component');

		JToolbarHelper::divider();
		JToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools');
	}

	public function onWebConfigMakersBrowse()
	{
		JToolbarHelper::title(JText::_('COM_ADMINTOOLS_TITLE_WCMAKER'), 'admintools');
		JToolbarHelper::save('save', 'COM_ADMINTOOLS_LBL_WEBCONFIGMAKER_SAVE');
		JToolbarHelper::apply('apply', 'COM_ADMINTOOLS_LBL_WEBCONFIGMAKER_APPLY');
		JToolbarHelper::divider();

		$bar = JToolbar::getInstance('toolbar');
		$bar->appendButton('Popup', 'preview', 'Preview', 'index.php?option=com_admintools&view=WebConfigMaker&task=preview&tmpl=component', 640, 380);

		JToolbarHelper::divider();
		JToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools');
	}

	public function onWebApplicationFirewallsDefault()
	{
		JToolbarHelper::title(JText::_('COM_ADMINTOOLS_TITLE_WAF'), 'admintools');
		JToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools');
	}

	public function onConfigureWAFsDefault()
	{
		JToolbarHelper::title(JText::_('COM_ADMINTOOLS_TITLE_WAFCONFIG'), 'admintools');
		JToolbarHelper::apply();
		JToolbarHelper::save();
		JToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools&view=WebApplicationFirewall');
	}

	public function onWAFBlacklistedRequestsBrowse()
	{
		JToolbarHelper::title(JText::_('COM_ADMINTOOLS_TITLE_WAFBLACKLISTS'), 'admintools');

		if ($this->perms->create)
		{
			JToolbarHelper::addNew();
		}

		if ($this->perms->delete)
		{
			$msg = JText::_('COM_ADMINTOOLS_CONFIRM_DELETE');
			JToolbarHelper::deleteList(strtoupper($msg));
		}

		JToolbarHelper::divider();
		JToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools&view=WebApplicationFirewall');
	}

	public function onWAFBlacklistedRequestsAdd()
	{
		parent::onAdd();

		JToolbarHelper::title(JText::_('COM_ADMINTOOLS_TITLE_WAFBLACKLISTS_EDIT'), 'admintools');
	}

	public function onWAFBlacklistedRequestsEdit()
	{
		$this->onWAFBlacklistedRequestsAdd();
	}

	public function onExceptionsFromWAFsBrowse()
	{
		parent::onBrowse();

		JToolbarHelper::title(JText::_('COM_ADMINTOOLS_TITLE_WAFEXCEPTIONS'), 'admintools');
		JToolbarHelper::divider();
		JToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools&view=WebApplicationFirewall');
	}

	public function onExceptionsFromWAFsAdd()
	{
		parent::onAdd();

		JToolbarHelper::title(JText::_('COM_ADMINTOOLS_TITLE_WAFEXCEPTIONS_EDIT'), 'admintools');
	}

	public function onExceptionsFromWAFsEdit()
	{
		$this->onExceptionsFromWAFsAdd();
	}

	public function onWhitelistedAddressesBrowse()
	{
		if ($this->perms->delete)
		{
			JToolbarHelper::deleteList();
		}
		if ($this->perms->edit)
		{
			JToolbarHelper::editList();
		}
		if ($this->perms->create)
		{
			JToolbarHelper::addNew();
		}

		$this->renderSubmenu();

		JToolbarHelper::title(JText::_('COM_ADMINTOOLS_TITLE_IPWL'), 'admintools');
		JToolbarHelper::divider();
		JToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools&view=WebApplicationFirewall');
	}

	public function onWhitelistedAddressesAdd()
	{
		parent::onAdd();
		JToolbarHelper::title(JText::_('COM_ADMINTOOLS_TITLE_IPWL_EDIT'), 'admintools');
	}

	public function onWhitelistedAddressesEdit()
	{
		$this->onWhitelistedAddressesAdd();
	}

	public function onBlacklistedAddressesBrowse()
	{
		if ($this->perms->create)
		{
			$text = JText::_('COM_ADMINTOOLS_BLACKLISTEDADDRESSES_IMPORT');

			$html = <<<HTML
<button class="btn btn-small btn-primary" onclick="location.href='index.php?option=com_admintools&amp;view=BlacklistedAddresses&amp;task=import';">
	<span class="icon-upload"></span>
	$text
</button>
HTML;
			$bar = JToolbar::getInstance('toolbar');
			$bar->appendButton('Custom', $html);
		}

		$text = JText::_('COM_ADMINTOOLS_BLACKLISTEDADDRESSES_EXPORT');

		$html = <<<HTML
<button class="btn btn-small btn-primary" onclick="location.href='index.php?option=com_admintools&amp;view=BlacklistedAddresses&amp;format=csv';">
	<span class="icon-download"></span>
	$text
</button>
HTML;
		$bar = JToolbar::getInstance('toolbar');
		$bar->appendButton('Custom', $html);

		if ($this->perms->delete)
		{
			JToolbarHelper::deleteList();
		}
		if ($this->perms->edit)
		{
			JToolbarHelper::editList();
		}
		if ($this->perms->create)
		{
			JToolbarHelper::addNew();
		}

		$this->renderSubmenu();

		JToolbarHelper::title(JText::_('COM_ADMINTOOLS_TITLE_IPBL'), 'admintools');
		JToolbarHelper::divider();
		JToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools&view=WebApplicationFirewall');
	}

	public function onBlacklistedAddressesAdd()
	{
		parent::onAdd();
		JToolbarHelper::title(JText::_('COM_ADMINTOOLS_TITLE_IPBL_EDIT'), 'admintools');
	}

	public function onBlacklistedAddressesEdit()
	{
		$this->onBlacklistedAddressesAdd();
	}

	public function onBlacklistedAddressesImport()
	{
		JToolbarHelper::title(JText::_('COM_ADMINTOOLS_TITLE_IPBL'), 'admintools');
		JToolbarHelper::custom('doimport', 'upload', '', JText::_('COM_ADMINTOOLS_BLACKLISTEDADDRESSES_IMPORT'), false);
		JToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools&view=BlacklistedAddresses');
	}

	public function onBadWordsBrowse()
	{
		if ($this->perms->delete)
		{
			JToolbarHelper::deleteList();
		}
		if ($this->perms->edit)
		{
			JToolbarHelper::editList();
		}
		if ($this->perms->create)
		{
			JToolbarHelper::addNew();
		}

		$this->renderSubmenu();

		JToolbarHelper::title(JText::_('COM_ADMINTOOLS_TITLE_BADWORDS'), 'admintools');
		JToolbarHelper::divider();
		JToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools&view=WebApplicationFirewall');
	}

	public function onBadWordsAdd()
	{
		parent::onAdd();
		JToolbarHelper::title(JText::_('COM_ADMINTOOLS_TITLE_BADWORDS_EDIT'), 'admintools');
	}

	public function onBadWordsEdit()
	{
		$this->onBadWordsAdd();
	}

	public function onGeographicBlockingsBrowse()
	{
		JToolbarHelper::save();
		JToolbarHelper::divider();
		JToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools&view=WebApplicationFirewall');

		JToolbarHelper::title(JText::_('COM_ADMINTOOLS_TITLE_DASHBOARD') . ' &ndash; <small>' . JText::_('COM_ADMINTOOLS_TITLE_GEOBLOCK') . '</small>', 'admintools');
	}

	public function onSecurityExceptionsBrowse()
	{
		if ($this->perms->delete)
		{
			JToolbarHelper::deleteList();
		}

		$this->renderSubmenu();

		JToolbarHelper::title(JText::_('COM_ADMINTOOLS_TITLE_LOG'), 'admintools');
		JToolbarHelper::divider();
		JToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools&view=WebApplicationFirewall');
	}

	public function onAutoBannedAddressesBrowse()
	{
		if ($this->perms->delete)
		{
			JToolbarHelper::deleteList();
		}

		$this->renderSubmenu();

		JToolbarHelper::title(JText::_('COM_ADMINTOOLS_TITLE_IPAUTOBAN'), 'admintools');
		JToolbarHelper::divider();
		JToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools&view=WebApplicationFirewall');
	}

	public function onIPAutoBanHistoriesBrowse()
	{
		if ($this->perms->delete)
		{
			JToolbarHelper::deleteList();
		}

		$this->renderSubmenu();

		JToolbarHelper::title(JText::_('COM_ADMINTOOLS_TITLE_IPAUTOBANHISTORY'), 'admintools');
		JToolbarHelper::divider();
		JToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools&view=WebApplicationFirewall');
	}

	public function onDbprefixesBrowse()
	{
		JToolbarHelper::title(JText::_('COM_ADMINTOOLS_TITLE_DBPREFIX'), 'admintools');
		JToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools');
	}

	public function onAdminusersBrowse()
	{
		JToolbarHelper::title(JText::_('COM_ADMINTOOLS_TITLE_ADMINUSER'), 'admintools');
		JToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools');
	}

	public function onConfigureFixPermissionsBrowse()
	{
		$subtitle_key = 'COM_ADMINTOOLS_TITLE_FIXPERMSCONFIG';
		JToolbarHelper::title(JText::_('COM_ADMINTOOLS_TITLE_DASHBOARD') . ' &ndash; <small>' . JText::_($subtitle_key) . '</small>', 'admintools');

		JToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools');
	}

	public function onFixPermissionsBrowse()
	{
		JToolbarHelper::title(JText::_('COM_ADMINTOOLS_TITLE_FIXPERMS'), 'admintools');
	}

	public function onFixPermissionsRun()
	{
		$this->onFixPermissionsBrowse();
	}

	public function onSEOAndLinkToolsBrowse()
	{
		JToolbarHelper::title(JText::_('COM_ADMINTOOLS_TITLE_SEOANDLINK'), 'admintools');

		JToolbarHelper::apply();
		JToolbarHelper::save();
		JToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools');
	}

	public function onCleanTempDirectoriesBrowse()
	{
		JToolbarHelper::title(JText::_('COM_ADMINTOOLS_TITLE_CLEANTMP'), 'admintools');
	}

	public function onCleanTempDirectoriesRun()
	{
		$this->onCleanTempDirectoriesBrowse();
	}

	public function onChangeDBCollationsBrowse()
	{
		JToolbarHelper::title(JText::_('COM_ADMINTOOLS_TITLE_DBCHCOL'), 'admintools');
		JToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools');
	}

	public function onDatabaseTools()
	{
		// Set the toolbar title
		JToolbarHelper::title(JText::_('COM_ADMINTOOLS_TITLE_DBTOOLS'), 'admintools');
	}

	public function onRedirectionsBrowse()
	{
		JToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools');
		parent::onBrowse();
		JToolbarHelper::title(JText::_('COM_ADMINTOOLS_TITLE_REDIRS'), 'admintools');
	}

	public function onRedirectionsAdd()
	{
		parent::onAdd();
		JToolbarHelper::title(JText::_('COM_ADMINTOOLS_TITLE_REDIRS_EDIT'), 'admintools');
	}

	public function onRedirectionsEdit()
	{
		$this->onRedirectionsAdd();
	}

	public function onAclsBrowse()
	{
		JToolbarHelper::title(JText::_('COM_ADMINTOOLS_TITLE_DASHBOARD') . ' &ndash; <small>' . JText::_('COM_ADMINTOOLS_TITLE_ACL') . '</small>', 'admintools');
		JToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools');
	}

	public function onScannersBrowse()
	{
		JToolbarHelper::title(JText::_('COM_ADMINTOOLS_TITLE_SCANNER'), 'admintools');
		JToolbarHelper::apply();
		JToolbarHelper::save();
		JToolbarHelper::divider();
		JToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools&view=Scans');
	}

	public function onScansBrowse()
	{
		// Set toolbar title
		$subtitle_key = 'COM_ADMINTOOLS_TITLE_' . strtoupper($this->container->input->getCmd('view', 'cpanel'));
		JToolbarHelper::title(JText::_('com_admintools') . ' &ndash; <small>' . JText::_($subtitle_key) . '</small>', 'admintools');

		$canScan = JFactory::getUser()->authorise('core.manage', 'com_admintools');

		if ($canScan)
		{
			$bar = JToolbar::getInstance('toolbar');
			$icon = 'play';
			$bar->appendButton('Link', $icon, JText::_('COM_ADMINTOOLS_MSG_SCAN_SCANNOW'), 'javascript:startScan()');

			$iconPurge = 'trash';
			$bar->appendButton('Link', $iconPurge, JText::_('COM_ADMINTOOLS_MSG_SCAN_PURGE'), 'index.php?option=com_admintools&view=Scans&task=purge');
			JToolbarHelper::divider();
		}

		// Add toolbar buttons
		if ($this->perms->delete)
		{
			JToolbarHelper::deleteList();
		}

		JToolbarHelper::divider();
		JToolbarHelper::preferences('com_admintools');
		JToolbarHelper::divider();
		JToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools');
	}

	public function onScansEdit()
	{
		JToolbarHelper::title(JText::_('COM_ADMINTOOLS') . ' &ndash; <small>' . JText::_('COM_ADMINTOOLS_TITLE_SCANS_COMMENT') . '</small>', 'admintools');

		JToolbarHelper::apply();
		JToolbarHelper::save();
		JToolbarHelper::cancel();
	}

	public function onScanAlertsBrowse()
	{
		$scan_id = $this->container->input->getInt('scan_id', 0);

		$subtitle_key = 'COM_ADMINTOOLS_TITLE_' . strtoupper($this->container->input->getCmd('view', 'cpanel'));
		JToolbarHelper::title(JText::_('com_admintools') . ' &ndash; <small>' . JText::sprintf($subtitle_key, $scan_id) . '</small>', 'admintools');

		JToolbarHelper::publishList('publish', 'COM_ADMINTOOLS_LBL_SCANALERTS_MARKSAFE');
		JToolbarHelper::unpublishList('unpublish', 'COM_ADMINTOOLS_LBL_SCANALERTS_MARKUNSAFE');

		JToolbarHelper::divider();

		$bar = JToolbar::getInstance('toolbar');

		$printLink = 'index.php?option=com_admintools&view=ScanAlerts&tmpl=component&layout=print&scan_id='.$scan_id;
		$bar->appendButton('Link', 'print', JText::_('COM_ADMINTOOLS_MSG_COMMON_PRINT'), $printLink);

		$csvLink = 'index.php?option=com_admintools&view=ScanAlerts&format=csv&scan_id='.$scan_id;
		$bar->appendButton('Link', 'download', JText::_('COM_ADMINTOOLS_MSG_COMMON_CSV'), $csvLink);

		JToolbarHelper::divider();
		JToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools&view=Scans');
	}

	public function onScanAlertsEdit()
	{
		JToolbarHelper::apply();
		JToolbarHelper::save();
		JToolbarHelper::cancel();
	}

	public function onTwofactorsBrowse()
	{
		JToolbarHelper::title(JText::_('COM_ADMINTOOLS_TITLE_TWOFACTOR'), 'admintools');
		JToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools&view=WebApplicationFirewall');
	}

	public function onWAFEmailTemplatesBrowse()
	{
		parent::onBrowse();
		JToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools&view=WebApplicationFirewall');
	}

	public function onImportAndExportsExport()
	{
		JToolbarHelper::title(JText::_('COM_ADMINTOOLS_TITLE_EXPORT_SETTINGS'), 'admintools');

		JToolbarHelper::apply('doexport', JText::_('COM_ADMINTOOLS_TITLE_EXPORT_SETTINGS'));
		JToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools');
	}

	public function onImportAndExportsImport()
	{
		JToolbarHelper::title(JText::_('COM_ADMINTOOLS_TITLE_IMPORT_SETTINGS'), 'admintools');

		JToolbarHelper::apply('doimport', JText::_('COM_ADMINTOOLS_TITLE_IMPORT_SETTINGS'));
		JToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools');
	}

	public function onQuickStartsBrowse()
	{
		// Set toolbar title
		$subtitle_key = strtoupper('COM_ADMINTOOLS_TITLE_' . $this->container->input->getCmd('view', 'cpanel'));
		JToolbarHelper::title(JText::_('COM_ADMINTOOLS') . ': ' . JText::_($subtitle_key), 'admintools');

		JToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools');
	}

	public function onSchedulingInformations()
	{
		// Set the toolbar title
		JToolbarHelper::title(JText::_('COM_ADMINTOOLS_TITLE_SCHEDULINGINFORMATION'), 'admintools');
		JToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools');
	}
}