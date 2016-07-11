<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2016 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 * @version   $Id$
 */

// Protect from unauthorized access
defined('_JEXEC') or die;

class AdmintoolsControllerIpbls extends F0FController
{
	protected function onBeforeBrowse()
	{
		return $this->checkACL('admintools.security');
	}

	protected function onBeforeApply()
	{
		return $this->checkACL('admintools.security');
	}

	protected function onBeforeSave()
	{
		return $this->checkACL('admintools.security');
	}

	protected function onBeforePublish()
	{
		return $this->checkACL('admintools.security');
	}

	protected function onBeforeUnpublish()
	{
		return $this->checkACL('admintools.security');
	}

	protected function onBeforeRemove()
	{
		return $this->checkACL('admintools.security');
	}

	protected function onBeforeSavenew()
	{
		return $this->checkACL('admintools.security');
	}

    protected function onBeforeImport()
    {
        return $this->checkACL('admintools.security');
    }

    public function import()
    {
        $this->layout = 'import';

        $this->display();
    }

    public function doimport()
    {
        $app       = JFactory::getApplication();
        /** @var AdmintoolsModelIpbls $model */
        $model     = $this->getThisModel();
        $file      = $this->input->files->get('csvfile', null, 'raw');
        $delimiter = $this->input->getInt('csvdelimiters', 0);
        $field     = $this->input->getString('field_delimiter', '');
        $enclosure = $this->input->getString('field_enclosure', '');

        if ($file['error'])
        {
            $this->setRedirect('index.php?option=com_admintools&view=ipbls&task=import', JText::_('COM_ADMINTOOLS_IMPORT_ERR_UPLOAD'), 'error');

            return;
        }

        if ($delimiter != - 99)
        {
            list($field, $enclosure) = $model->decodeDelimiterOptions($delimiter);
        }

        // Import ok, but maybe I have warnings (ie skipped lines)
        try
        {
            $model->import($file['tmp_name'], $field, $enclosure);
        }
        catch (\RuntimeException $e)
        {
            //Uh oh... import failed, let's inform the user why it happened
            $app->enqueueMessage(JText::sprintf('COM_AKEEBASUBS_IMPORT_FAIL', $e->getMessage()), 'error');
        }

        $this->setRedirect('index.php?option=com_admintools&view=ipbls');
    }
}
