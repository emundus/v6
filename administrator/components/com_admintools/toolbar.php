<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2016 Nicholas K. Dionysopoulos
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
			JToolbarHelper::title(JText::_('ADMINTOOLS_TITLE_DASHBOARD_PRO') . ' <small>' . ADMINTOOLS_VERSION . '</small>', 'admintools');
		}
		else
		{
			JToolbarHelper::title(JText::_('ADMINTOOLS_TITLE_DASHBOARD_CORE') . ' <small>' . ADMINTOOLS_VERSION . '</small>', 'admintools');
		}

		JToolbarHelper::preferences('com_admintools');
	}

	public function onEomsBrowse()
	{
		JToolbarHelper::title(JText::_('ADMINTOOLS_TITLE_EOM'), 'admintools');
		JToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools');
	}

	public function onMasterpwsBrowse()
	{
		JToolbarHelper::title(JText::_('ADMINTOOLS_TITLE_MASTERPW'), 'admintools');
		JToolbarHelper::save();
		JToolbarHelper::divider();
		JToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools');
	}

	public function onAdminpwsBrowse()
	{
		// Set the toolbar title
		JToolbarHelper::title(JText::_('ADMINTOOLS_TITLE_ADMINPW'), 'admintools');
		JToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools');
	}

	public function onHtmakersBrowse()
	{
		JToolbarHelper::title(JText::_('ADMINTOOLS_TITLE_HTMAKER'), 'admintools');
		JToolbarHelper::save('save', 'ATOOLS_LBL_HTMAKER_SAVE');
		JToolbarHelper::apply('apply', 'ATOOLS_LBL_HTMAKER_APPLY');
		JToolbarHelper::divider();

		$bar = JToolbar::getInstance('toolbar');
		$bar->appendButton('Popup', 'preview', 'Preview', 'index.php?option=com_admintools&view=htmaker&task=preview&tmpl=component', 640, 380);

		//JToolbarHelper::preview('index.php?option=com_admintools&view=htmaker&tmpl=component');

		JToolbarHelper::divider();
		JToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools');
	}

	public function onNginxmakersBrowse()
	{
		JToolbarHelper::title(JText::_('ADMINTOOLS_TITLE_NGINXMAKER'), 'admintools');
		JToolbarHelper::save('save', 'ATOOLS_LBL_NGINXMAKER_SAVE');
		JToolbarHelper::apply('apply', 'ATOOLS_LBL_NGINXMAKER_APPLY');
		JToolbarHelper::divider();

		$bar = JToolbar::getInstance('toolbar');
		$bar->appendButton('Popup', 'preview', 'Preview', 'index.php?option=com_admintools&view=nginxmaker&task=preview&tmpl=component', 640, 380);
		//JToolbarHelper::preview('index.php?option=com_admintools&view=nginxmaker&tmpl=component');

		JToolbarHelper::divider();
		JToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools');
	}

	public function onWcmakersBrowse()
	{
		JToolbarHelper::title(JText::_('ADMINTOOLS_TITLE_WCMAKER'), 'admintools');
		JToolbarHelper::save('save', 'ATOOLS_LBL_WCMAKER_SAVE');
		JToolbarHelper::apply('apply', 'ATOOLS_LBL_WCMAKER_APPLY');
		JToolbarHelper::divider();

		$bar = JToolbar::getInstance('toolbar');
		$bar->appendButton('Popup', 'preview', 'Preview', 'index.php?option=com_admintools&view=wcmaker&task=preview&tmpl=component', 640, 380);

		JToolbarHelper::divider();
		JToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools');
	}

	public function onWafsAdd()
	{
		JToolbarHelper::title(JText::_('ADMINTOOLS_TITLE_WAF'), 'admintools');
		JToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools');
	}

	public function onWafconfigsBrowse()
	{
		JToolbarHelper::title(JText::_('ADMINTOOLS_TITLE_WAFCONFIG'), 'admintools');
		JToolbarHelper::apply();
		JToolbarHelper::save();
		JToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools&view=waf');
	}

    public function onWafblacklistsBrowse()
    {
        JToolbarHelper::title(JText::_('ADMINTOOLS_TITLE_WAFBLACKLISTS'), 'admintools');

        if ($this->perms->create)
        {
            JToolbarHelper::addNew();
        }

        if ($this->perms->delete)
        {
            $msg = JText::_($this->input->getCmd('option', 'com_foobar') . '_CONFIRM_DELETE');
            JToolbarHelper::deleteList(strtoupper($msg));
        }

        JToolbarHelper::divider();
        JToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools&view=waf');
    }

    public function onWafblacklistsAdd()
    {
        parent::onAdd();
        JToolbarHelper::title(JText::_('ADMINTOOLS_TITLE_WAFBLACKLISTS_EDIT'), 'admintools');
    }

    public function onWafblacklistsEdit()
    {
        $this->onWafblacklistsAdd();
    }

	public function onWafexceptionsBrowse()
	{
		parent::onBrowse();
		JToolbarHelper::title(JText::_('ADMINTOOLS_TITLE_WAFEXCEPTIONS'), 'admintools');
		JToolbarHelper::divider();
		JToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools&view=waf');
	}

	public function onWafexceptionsAdd()
	{
		parent::onAdd();
		JToolbarHelper::title(JText::_('ADMINTOOLS_TITLE_WAFEXCEPTIONS_EDIT'), 'admintools');
	}

	public function onWafexceptionsEdit()
	{
		$this->onWafexceptionsAdd();
	}

	public function onIpwlsBrowse()
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

		JToolbarHelper::title(JText::_('ADMINTOOLS_TITLE_IPWL'), 'admintools');
		JToolbarHelper::divider();
		JToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools&view=waf');
	}

	public function onIpwlsAdd()
	{
		parent::onAdd();
		JToolbarHelper::title(JText::_('ADMINTOOLS_TITLE_IPWL_EDIT'), 'admintools');
	}

	public function onIpwlsEdit()
	{
		$this->onIpwlsAdd();
	}

	public function onIpblsBrowse()
	{
        if ($this->perms->create)
        {
            $text = JText::_('COM_ADMINTOOLS_IPBLS_IMPORT');

            $html = <<<HTML
<button class="btn btn-small btn-primary" onclick="location.href='index.php?option=com_admintools&amp;view=ipbls&amp;task=import';">
	<span class="icon-upload"></span>
	$text
</button>
HTML;
            $bar = JToolBar::getInstance('toolbar');
            $bar->appendButton('Custom', $html);
        }

        $text = JText::_('COM_ADMINTOOLS_IPBLS_EXPORT');

        $html = <<<HTML
<button class="btn btn-small btn-primary" onclick="location.href='index.php?option=com_admintools&amp;view=ipbls&amp;format=csv';">
	<span class="icon-download"></span>
	$text
</button>
HTML;
        $bar = JToolBar::getInstance('toolbar');
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

		JToolbarHelper::title(JText::_('ADMINTOOLS_TITLE_IPBL'), 'admintools');
		JToolbarHelper::divider();
		JToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools&view=waf');
	}

	public function onIpblsAdd()
	{
		parent::onAdd();
		JToolbarHelper::title(JText::_('ADMINTOOLS_TITLE_IPBL_EDIT'), 'admintools');
	}

	public function onIpblsEdit()
	{
		$this->onIpblsAdd();
	}

    public function onIpblsImport()
    {
        JToolbarHelper::title(JText::_('ADMINTOOLS_TITLE_IPBL'), 'admintools');
        JToolbarHelper::custom('doimport', 'upload', '', JText::_('COM_ADMINTOOLS_IPBLS_IMPORT'), false);
        JToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools&view=ipbls');
    }

	public function onBadwordsBrowse()
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

		JToolbarHelper::title(JText::_('ADMINTOOLS_TITLE_BADWORDS'), 'admintools');
		JToolbarHelper::divider();
		JToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools&view=waf');
	}

	public function onBadwordsAdd()
	{
		parent::onAdd();
		JToolbarHelper::title(JText::_('ADMINTOOLS_TITLE_BADWORDS_EDIT'), 'admintools');
	}

	public function onBadwordsEdit()
	{
		$this->onBadwordsAdd();
	}

	public function onGeoblocksBrowse()
	{
		JToolbarHelper::save();
		JToolbarHelper::cancel();

		$subtitle_key = 'ADMINTOOLS_TITLE_' . strtoupper($this->input->getCmd('view', 'cpanel'));
		JToolbarHelper::title(JText::_('ADMINTOOLS_TITLE_DASHBOARD') . ' &ndash; <small>' . JText::_($subtitle_key) . '</small>', 'admintools');
	}

	public function onLogsBrowse()
	{
		if ($this->perms->delete)
		{
			JToolbarHelper::deleteList();
		}

		$this->renderSubmenu();

		JToolbarHelper::title(JText::_('ADMINTOOLS_TITLE_LOG'), 'admintools');
		JToolbarHelper::divider();
		JToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools&view=waf');
	}

	public function onIpautobansBrowse()
	{
		if ($this->perms->delete)
		{
			JToolbarHelper::deleteList();
		}

		$this->renderSubmenu();

		JToolbarHelper::title(JText::_('ADMINTOOLS_TITLE_IPAUTOBAN'), 'admintools');
		JToolbarHelper::divider();
		JToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools&view=waf');
	}

	public function onIpautobanhistoriesBrowse()
	{
		if ($this->perms->delete)
		{
			JToolbarHelper::deleteList();
		}

		$this->renderSubmenu();

		JToolbarHelper::title(JText::_('ADMINTOOLS_TITLE_IPAUTOBANHISTORY'), 'admintools');
		JToolbarHelper::divider();
		JToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools&view=waf');
	}

	public function onDbprefixesBrowse()
	{
		JToolbarHelper::title(JText::_('ADMINTOOLS_TITLE_DBPREFIX'), 'admintools');
		JToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools');
	}

	public function onAdminusersBrowse()
	{
		JToolbarHelper::title(JText::_('ADMINTOOLS_TITLE_ADMINUSER'), 'admintools');
		JToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools');
	}

	public function onFixpermsconfigsBrowse()
	{
		$subtitle_key = 'ADMINTOOLS_TITLE_' . strtoupper($this->input->getCmd('view', 'cpanel'));
		JToolbarHelper::title(JText::_('ADMINTOOLS_TITLE_DASHBOARD') . ' &ndash; <small>' . JText::_($subtitle_key) . '</small>', 'admintools');

		JToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools');
	}

	public function onFixpermsBrowse()
	{
		JToolbarHelper::title(JText::_('ADMINTOOLS_TITLE_FIXPERMS'), 'admintools');
	}

	public function onFixpermsRun()
	{
		$this->onFixpermsBrowse();
	}

	public function onSeoandlinksBrowse()
	{
		JToolbarHelper::title(JText::_('ADMINTOOLS_TITLE_SEOANDLINK'), 'admintools');

		JToolbarHelper::apply();
		JToolbarHelper::save();
		JToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools');
	}

	public function onCleantmpsBrowse()
	{
		JToolbarHelper::title(JText::_('ADMINTOOLS_TITLE_CLEANTMP'), 'admintools');
	}

	public function onCleantmpsRun()
	{
		$this->onCleantmpsBrowse();
	}

	public function onPostsetupsBrowse()
	{
		JToolbarHelper::title(JText::_('COM_ADMINTOOLS') . ': <small>' . JText::_('COM_ADMINTOOLS_POSTSETUP_TITLE') . '</small>', 'admintools');
	}

	public function onDbchcolsBrowse()
	{
		JToolbarHelper::title(JText::_('ADMINTOOLS_TITLE_DBCHCOL'), 'admintools');
		JToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools');
	}

	public function onDbtools()
	{
		// Set the toolbar title
		JToolbarHelper::title(JText::_('ADMINTOOLS_TITLE_DBTOOLS'), 'admintools');
	}

	public function onRedirsBrowse()
	{
		parent::onBrowse();
		JToolbarHelper::title(JText::_('ADMINTOOLS_TITLE_REDIRS'), 'admintools');
	}

	public function onRedirsAdd()
	{
		parent::onAdd();
		JToolbarHelper::title(JText::_('ADMINTOOLS_TITLE_REDIRS_EDIT'), 'admintools');
	}

	public function onRedirsEdit()
	{
		$this->onRedirsAdd();
	}

	public function onAclsBrowse()
	{
		JToolbarHelper::title(JText::_('ADMINTOOLS_TITLE_DASHBOARD') . ' &ndash; <small>' . JText::_('ADMINTOOLS_TITLE_ACL') . '</small>', 'admintools');
		JToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools');
	}

	public function onScannersBrowse()
	{
		JToolbarHelper::title(JText::_('ADMINTOOLS_TITLE_SCANNER'), 'admintools');
		JToolbarHelper::apply();
		JToolbarHelper::save();
		JToolbarHelper::divider();
		JToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools&view=scans');
	}

	public function onScansBrowse()
	{
		// Set toolbar title
		$subtitle_key = $this->input->getCmd('option', 'com_foobar') . '_TITLE_' . strtoupper($this->input->getCmd('view', 'cpanel'));
		JToolbarHelper::title(JText::_($this->input->getCmd('option', 'com_foobar')) . ' &ndash; <small>' . JText::_($subtitle_key) . '</small>', str_replace('com_', '', $this->input->getCmd('option', 'com_foobar')));

		$canScan = JFactory::getUser()->authorise('core.manage', 'com_admintools');

		if ($canScan)
		{
			$bar = JToolBar::getInstance('toolbar');
			$icon = 'play';
			$bar->appendButton('Link', $icon, JText::_('COM_ADMINTOOLS_MSG_SCANS_SCANNOW'), 'javascript:startScan()');

            $iconPurge = 'trash';
            $bar->appendButton('Link', $iconPurge, JText::_('COM_ADMINTOOLS_MSG_SCANS_PURGE'), 'index.php?option=com_admintools&view=scans&task=purge');
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

	public function onScanalertsBrowse()
	{
		$scan_id = $this->input->getInt('scan_id', 0);

		$subtitle_key = $this->input->getCmd('option', 'com_foobar') . '_TITLE_' . strtoupper($this->input->getCmd('view', 'cpanel'));
		JToolbarHelper::title(JText::_($this->input->getCmd('option', 'com_foobar')) . ' &ndash; <small>' . JText::sprintf($subtitle_key, $scan_id) . '</small>', str_replace('com_', '', $this->input->getCmd('option', 'com_foobar')));

		JToolbarHelper::publishList('publish', 'COM_ADMINTOOLS_LBL_SCANALERTS_MARKSAFE');
		JToolbarHelper::unpublishList('unpublish', 'COM_ADMINTOOLS_LBL_SCANALERTS_MARKUNSAFE');

		JToolbarHelper::divider();
		$bar = JToolBar::getInstance('toolbar');
		$bar->appendButton('Link', 'print', JText::_('COM_ADMINTOOLS_MSG_COMMON_PRINT'), 'javascript:printReport()');
		$icon = 'download';
		$bar->appendButton('Link', $icon, JText::_('COM_ADMINTOOLS_MSG_COMMON_CSV'), 'javascript:exportCSV()');

		JToolbarHelper::divider();
		JToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools&view=scans');
	}

	public function onScanalertsEdit()
	{
		JToolbarHelper::apply();
		JToolbarHelper::save();
		JToolbarHelper::cancel();
	}

	public function onTwofactorsBrowse()
	{
		JToolbarHelper::title(JText::_('ADMINTOOLS_TITLE_TWOFACTOR'), 'admintools');
		JToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools&view=waf');
	}

	public function onWaftemplatesBrowse()
	{
		parent::onBrowse();
		JToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools&view=waf');
	}

    public function onImportexportsExport()
    {
        JToolbarHelper::title(JText::_('ATOOLS_TITLE_EXPORT_SETTINGS'), 'admintools');

        JToolbarHelper::apply('doexport', JText::_('ATOOLS_TITLE_EXPORT_SETTINGS'));
        JToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools');
    }

    public function onImportexportsImport()
    {
        JToolbarHelper::title(JText::_('ATOOLS_TITLE_IMPORT_SETTINGS'), 'admintools');

        JToolbarHelper::apply('doimport', JText::_('ATOOLS_TITLE_IMPORT_SETTINGS'));
        JToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools');
    }

	public function onCheckfilesShow()
	{
		JToolbarHelper::title(JText::_('COM_ADMINTOOLS_CHECKFILE'), 'admintools');
	}

	public function onQuickstartsBrowse()
	{
		// Set toolbar title
		$option = $this->input->getCmd('option', 'com_foobar');
		$subtitle_key = strtoupper($option . '_TITLE_' . $this->input->getCmd('view', 'cpanel'));
		JToolbarHelper::title(JText::_(strtoupper($option)) . ': ' . JText::_($subtitle_key), str_replace('com_', '', $option));

		JToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools&view=waf');
	}

	public function onSchedules()
	{
		// Set the toolbar title
		JToolbarHelper::title(JText::_('COM_ADMINTOOLS_TITLE_SCHEDULING'), 'admintools');
		JToolbarHelper::back('JTOOLBAR_BACK', 'index.php?option=com_admintools');
	}
}