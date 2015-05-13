<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

class AdmintoolsToolbar extends F0FToolbar
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

	public function onCpanelsBrowse()
	{
		// Set the toolbar title
		if (ADMINTOOLS_PRO)
		{
			JToolBarHelper::title(JText::_('ADMINTOOLS_TITLE_DASHBOARD_PRO') . ' <small>' . ADMINTOOLS_VERSION . '</small>', 'admintools');
		}
		else
		{
			JToolBarHelper::title(JText::_('ADMINTOOLS_TITLE_DASHBOARD_CORE') . ' <small>' . ADMINTOOLS_VERSION . '</small>', 'admintools');
		}

		JToolBarHelper::preferences('com_admintools');
	}

	public function onEomsBrowse()
	{
		JToolBarHelper::title(JText::_('ADMINTOOLS_TITLE_EOM'), 'admintools');
		JToolBarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools');
	}

	public function onMasterpwsBrowse()
	{
		JToolBarHelper::title(JText::_('ADMINTOOLS_TITLE_MASTERPW'), 'admintools');
		JToolBarHelper::save();
		JToolBarHelper::divider();
		JToolBarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools');
	}

	public function onAdminpwsBrowse()
	{
		// Set the toolbar title
		JToolBarHelper::title(JText::_('ADMINTOOLS_TITLE_ADMINPW'), 'admintools');
		JToolBarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools');
	}

	public function onHtmakersBrowse()
	{
		JToolBarHelper::title(JText::_('ADMINTOOLS_TITLE_HTMAKER'), 'admintools');
		JToolBarHelper::save('save', 'ATOOLS_LBL_HTMAKER_SAVE');
		JToolBarHelper::apply('apply', 'ATOOLS_LBL_HTMAKER_APPLY');
		JToolBarHelper::divider();
		JToolBarHelper::preview('index.php?option=com_admintools&view=htmaker&tmpl=component');
		JToolBarHelper::divider();
		JToolBarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools');
	}

	public function onNginxmakersBrowse()
	{
		JToolBarHelper::title(JText::_('ADMINTOOLS_TITLE_NGINXMAKER'), 'admintools');
		JToolBarHelper::save('save', 'ATOOLS_LBL_NGINXMAKER_SAVE');
		JToolBarHelper::apply('apply', 'ATOOLS_LBL_NGINXMAKER_APPLY');
		JToolBarHelper::divider();
		JToolBarHelper::preview('index.php?option=com_admintools&view=nginxmaker&tmpl=component');
		JToolBarHelper::divider();
		JToolBarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools');
	}

	public function onWafsAdd()
	{
		JToolBarHelper::title(JText::_('ADMINTOOLS_TITLE_WAF'), 'admintools');
		JToolBarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools');
	}

	public function onWafconfigsBrowse()
	{
		JToolBarHelper::title(JText::_('ADMINTOOLS_TITLE_WAFCONFIG'), 'admintools');
		JToolBarHelper::apply();
		JToolBarHelper::save();
		JToolBarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools&view=waf');
	}

    public function onWafblacklistsBrowse()
    {
        JToolBarHelper::title(JText::_('ADMINTOOLS_TITLE_WAFBLACKLISTS'), 'admintools');

        if ($this->perms->create)
        {
            JToolBarHelper::addNew();
        }

        if ($this->perms->delete)
        {
            $msg = JText::_($this->input->getCmd('option', 'com_foobar') . '_CONFIRM_DELETE');
            JToolBarHelper::deleteList(strtoupper($msg));
        }

        JToolBarHelper::divider();
        JToolBarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools&view=waf');
    }

    public function onWafblacklistsAdd()
    {
        parent::onAdd();
        JToolBarHelper::title(JText::_('ADMINTOOLS_TITLE_WAFBLACKLISTS_EDIT'), 'admintools');
    }

    public function onWafblacklistsEdit()
    {
        $this->onWafblacklistsAdd();
    }

	public function onWafexceptionsBrowse()
	{
		parent::onBrowse();
		JToolBarHelper::title(JText::_('ADMINTOOLS_TITLE_WAFEXCEPTIONS'), 'admintools');
		JToolBarHelper::divider();
		JToolBarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools&view=waf');
	}

	public function onWafexceptionsAdd()
	{
		parent::onAdd();
		JToolBarHelper::title(JText::_('ADMINTOOLS_TITLE_WAFEXCEPTIONS_EDIT'), 'admintools');
	}

	public function onWafexceptionsEdit()
	{
		$this->onWafexceptionsAdd();
	}

	public function onIpwlsBrowse()
	{
		if ($this->perms->delete)
		{
			JToolBarHelper::deleteList();
		}
		if ($this->perms->edit)
		{
			JToolBarHelper::editList();
		}
		if ($this->perms->create)
		{
			JToolBarHelper::addNew();
		}

		$this->renderSubmenu();

		JToolBarHelper::title(JText::_('ADMINTOOLS_TITLE_IPWL'), 'admintools');
		JToolBarHelper::divider();
		JToolBarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools&view=waf');
	}

	public function onIpwlsAdd()
	{
		parent::onAdd();
		JToolBarHelper::title(JText::_('ADMINTOOLS_TITLE_IPWL_EDIT'), 'admintools');
	}

	public function onIpwlsEdit()
	{
		$this->onIpwlsAdd();
	}

	public function onIpblsBrowse()
	{
		if ($this->perms->delete)
		{
			JToolBarHelper::deleteList();
		}
		if ($this->perms->edit)
		{
			JToolBarHelper::editList();
		}
		if ($this->perms->create)
		{
			JToolBarHelper::addNew();
		}

		$this->renderSubmenu();

		JToolBarHelper::title(JText::_('ADMINTOOLS_TITLE_IPBL'), 'admintools');
		JToolBarHelper::divider();
		JToolBarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools&view=waf');
	}

	public function onIpblsAdd()
	{
		parent::onAdd();
		JToolBarHelper::title(JText::_('ADMINTOOLS_TITLE_IPBL_EDIT'), 'admintools');
	}

	public function onIpblsEdit()
	{
		$this->onIpblsAdd();
	}

	public function onBadwordsBrowse()
	{
		if ($this->perms->delete)
		{
			JToolBarHelper::deleteList();
		}
		if ($this->perms->edit)
		{
			JToolBarHelper::editList();
		}
		if ($this->perms->create)
		{
			JToolBarHelper::addNew();
		}

		$this->renderSubmenu();

		JToolBarHelper::title(JText::_('ADMINTOOLS_TITLE_BADWORDS'), 'admintools');
		JToolBarHelper::divider();
		JToolBarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools&view=waf');
	}

	public function onBadwordsAdd()
	{
		parent::onAdd();
		JToolBarHelper::title(JText::_('ADMINTOOLS_TITLE_BADWORDS_EDIT'), 'admintools');
	}

	public function onBadwordsEdit()
	{
		$this->onBadwordsAdd();
	}

	public function onGeoblocksBrowse()
	{
		JToolBarHelper::save();
		JToolBarHelper::cancel();

		$subtitle_key = 'ADMINTOOLS_TITLE_' . strtoupper($this->input->getCmd('view', 'cpanel'));
		JToolBarHelper::title(JText::_('ADMINTOOLS_TITLE_DASHBOARD') . ' &ndash; <small>' . JText::_($subtitle_key) . '</small>', 'admintools');
	}

	public function onLogsBrowse()
	{
		if ($this->perms->delete)
		{
			JToolBarHelper::deleteList();
		}

		$this->renderSubmenu();

		JToolBarHelper::title(JText::_('ADMINTOOLS_TITLE_LOG'), 'admintools');
		JToolBarHelper::divider();
		JToolBarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools&view=waf');
	}

	public function onIpautobansBrowse()
	{
		if ($this->perms->delete)
		{
			JToolBarHelper::deleteList();
		}

		$this->renderSubmenu();

		JToolBarHelper::title(JText::_('ADMINTOOLS_TITLE_IPAUTOBAN'), 'admintools');
		JToolBarHelper::divider();
		JToolBarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools&view=waf');
	}

	public function onIpautobanhistoriesBrowse()
	{
		if ($this->perms->delete)
		{
			JToolBarHelper::deleteList();
		}

		$this->renderSubmenu();

		JToolBarHelper::title(JText::_('ADMINTOOLS_TITLE_IPAUTOBANHISTORY'), 'admintools');
		JToolBarHelper::divider();
		JToolBarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools&view=waf');
	}

	public function onDbprefixesBrowse()
	{
		JToolBarHelper::title(JText::_('ADMINTOOLS_TITLE_DBPREFIX'), 'admintools');
		JToolBarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools');
	}

	public function onAdminusersBrowse()
	{
		JToolBarHelper::title(JText::_('ADMINTOOLS_TITLE_ADMINUSER'), 'admintools');
		JToolBarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools');
	}

	public function onFixpermsconfigsBrowse()
	{
		$subtitle_key = 'ADMINTOOLS_TITLE_' . strtoupper($this->input->getCmd('view', 'cpanel'));
		JToolBarHelper::title(JText::_('ADMINTOOLS_TITLE_DASHBOARD') . ' &ndash; <small>' . JText::_($subtitle_key) . '</small>', 'admintools');

		JToolBarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools');
	}

	public function onFixpermsBrowse()
	{
		JToolBarHelper::title(JText::_('ADMINTOOLS_TITLE_FIXPERMS'), 'admintools');
	}

	public function onFixpermsRun()
	{
		$this->onFixpermsBrowse();
	}

	public function onSeoandlinksBrowse()
	{
		JToolBarHelper::title(JText::_('ADMINTOOLS_TITLE_SEOANDLINK'), 'admintools');

		JToolBarHelper::apply();
		JToolBarHelper::save();
		JToolBarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools');
	}

	public function onCleantmpsBrowse()
	{
		JToolBarHelper::title(JText::_('ADMINTOOLS_TITLE_CLEANTMP'), 'admintools');
	}

	public function onCleantmpsRun()
	{
		$this->onCleantmpsBrowse();
	}

	public function onPostsetupsBrowse()
	{
		JToolBarHelper::title(JText::_('COM_ADMINTOOLS') . ': <small>' . JText::_('COM_ADMINTOOLS_POSTSETUP_TITLE') . '</small>', 'admintools');
	}

	public function onDbchcolsBrowse()
	{
		JToolBarHelper::title(JText::_('ADMINTOOLS_TITLE_DBCHCOL'), 'admintools');
		JToolBarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools');
	}

	public function onDbtools()
	{
		// Set the toolbar title
		JToolBarHelper::title(JText::_('ADMINTOOLS_TITLE_DBTOOLS'), 'admintools');
	}

	public function onRedirsBrowse()
	{
		parent::onBrowse();
		JToolBarHelper::title(JText::_('ADMINTOOLS_TITLE_REDIRS'), 'admintools');
	}

	public function onRedirsAdd()
	{
		parent::onAdd();
		JToolBarHelper::title(JText::_('ADMINTOOLS_TITLE_REDIRS_EDIT'), 'admintools');
	}

	public function onRedirsEdit()
	{
		$this->onRedirsAdd();
	}

	public function onAclsBrowse()
	{
		JToolBarHelper::title(JText::_('ADMINTOOLS_TITLE_DASHBOARD') . ' &ndash; <small>' . JText::_('ADMINTOOLS_TITLE_ACL') . '</small>', 'admintools');
		JToolBarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools');
	}

	public function onScannersBrowse()
	{
		JToolBarHelper::title(JText::_('ADMINTOOLS_TITLE_SCANNER'), 'admintools');
		JToolBarHelper::apply();
		JToolBarHelper::save();
		JToolBarHelper::divider();
		JToolBarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools&view=scans');
	}

	public function onScansBrowse()
	{
		// Set toolbar title
		$subtitle_key = $this->input->getCmd('option', 'com_foobar') . '_TITLE_' . strtoupper($this->input->getCmd('view', 'cpanel'));
		JToolBarHelper::title(JText::_($this->input->getCmd('option', 'com_foobar')) . ' &ndash; <small>' . JText::_($subtitle_key) . '</small>', str_replace('com_', '', $this->input->getCmd('option', 'com_foobar')));

		$canScan = JFactory::getUser()->authorise('core.manage', 'com_admintools');

		if ($canScan)
		{
			$bar = JToolBar::getInstance('toolbar');
			$icon = 'play';
			$bar->appendButton('Link', $icon, JText::_('COM_ADMINTOOLS_MSG_SCANS_SCANNOW'), 'javascript:startScan()');

            $iconPurge = 'trash';
            $bar->appendButton('Link', $iconPurge, JText::_('COM_ADMINTOOLS_MSG_SCANS_PURGE'), 'index.php?option=com_admintools&view=scans&task=purge');
			JToolBarHelper::divider();
		}

		// Add toolbar buttons
		if ($this->perms->delete)
		{
			JToolBarHelper::deleteList();
		}

		JToolBarHelper::divider();
		JToolBarHelper::preferences('com_admintools');
		JToolBarHelper::divider();
		JToolBarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools');
	}

	public function onScanalertsBrowse()
	{
		$scan_id = $this->input->getInt('scan_id', 0);

		$subtitle_key = $this->input->getCmd('option', 'com_foobar') . '_TITLE_' . strtoupper($this->input->getCmd('view', 'cpanel'));
		JToolBarHelper::title(JText::_($this->input->getCmd('option', 'com_foobar')) . ' &ndash; <small>' . JText::sprintf($subtitle_key, $scan_id) . '</small>', str_replace('com_', '', $this->input->getCmd('option', 'com_foobar')));

		JToolBarHelper::publishList('publish', 'COM_ADMINTOOLS_LBL_SCANALERTS_MARKSAFE');
		JToolBarHelper::unpublishList('unpublish', 'COM_ADMINTOOLS_LBL_SCANALERTS_MARKUNSAFE');

		JToolBarHelper::divider();
		$bar = JToolBar::getInstance('toolbar');
		$bar->appendButton('Link', 'print', JText::_('COM_ADMINTOOLS_MSG_COMMON_PRINT'), 'javascript:printReport()');
		$icon = 'download';
		$bar->appendButton('Link', $icon, JText::_('COM_ADMINTOOLS_MSG_COMMON_CSV'), 'javascript:exportCSV()');

		JToolBarHelper::divider();
		JToolBarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools&view=scans');
	}

	public function onScanalertsEdit()
	{
		JToolBarHelper::apply();
		JToolBarHelper::save();
		JToolBarHelper::cancel();
	}

	public function onTwofactorsBrowse()
	{
		JToolBarHelper::title(JText::_('ADMINTOOLS_TITLE_TWOFACTOR'), 'admintools');
		JToolBarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools&view=waf');
	}

	public function onWaftemplatesBrowse()
	{
		parent::onBrowse();
		JToolBarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools&view=waf');
	}

    public function onImportexportsExport()
    {
        JToolbarHelper::title(JText::_('ATOOLS_TITLE_EXPORT_SETTINGS'), 'admintools');

        JToolbarHelper::apply('doexport', JText::_('ATOOLS_TITLE_EXPORT_SETTINGS'));
        JToolBarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools');
    }

    public function onImportexportsImport()
    {
        JToolbarHelper::title(JText::_('ATOOLS_TITLE_IMPORT_SETTINGS'), 'admintools');

        JToolbarHelper::apply('doimport', JText::_('ATOOLS_TITLE_IMPORT_SETTINGS'));
        JToolBarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools');
    }

	public function onCheckfilesShow()
	{
		JToolbarHelper::title(JText::_('COM_ADMINTOOLS_CHECKFILE'), 'admintools');
	}
}