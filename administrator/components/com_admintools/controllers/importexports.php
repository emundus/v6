<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 * @version   $Id$
 */

// Protect from unauthorized access
defined('_JEXEC') or die;

class AdmintoolsControllerImportexports extends F0FController
{
    public function __construct($config = array())
    {
        parent::__construct($config);

    }

    public function export()
    {
        $this->layout = 'export';

        parent::browse();
    }

    public function import()
    {
        $this->layout = 'import';

        parent::browse();
    }

    public function doexport()
    {
        $data = $this->getThisModel()->exportData();

        if($data)
        {
            $json = json_encode($data);

            // Clear cache
            while (@ob_end_clean())
            {
                ;
            }

            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Cache-Control: public", false);

            // Send MIME headers
            header("Content-Description: File Transfer");
            header('Content-Type: json');
            header("Accept-Ranges: bytes");
            header('Content-Disposition: attachment; filename="admintools_settings.json"');
            header('Content-Transfer-Encoding: text');
            header('Connection: close');
            header('Content-Length: ' . strlen($json));

            echo $json;

            JFactory::getApplication()->close();
        }
        else
        {
            $this->setRedirect('index.php?option=com_admintools&view=importexport&task=export', JText::_('COM_ADMINTOOLS_IMPORTEXPORT_SELECT_DATA_WARN'), 'warning');
        }
    }

    public function doimport()
    {
        $model  = $this->getThisModel();
        $result = $model->importData();

        if($result)
        {
            $type = null;
            $msg  = JText::_('COM_ADMINTOOLS_IMPORTEXPORT_IMPORT_OK');
        }
        else
        {
            $type = 'error';
            $msg  = $model->getError();
        }

        $this->setRedirect('index.php?option=com_admintools&view=importexport&task=import', $msg, $type);
    }
}
