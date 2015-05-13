<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 * @version   $Id$
 */

// Protect from unauthorized access
defined('_JEXEC') or die;

class AdmintoolsModelImportexports extends F0FModel
{
    public function exportData()
    {
        $return = array();

        $exportData = $this->input->get('exportdata', array(), 'array', 2);

        if(isset($exportData['wafconfig']))
        {
            $config = F0FModel::getTmpInstance('Wafconfig', 'AdmintoolsModel')->getConfig();

            // Let's unset two factor auth stuff
            unset($config['twofactorauth']);
            unset($config['twofactorauth_secret']);
            unset($config['twofactorauth_panic']);

            $return['wafconfig'] = $config;
        }

        if(isset($exportData['ipblacklist']))
        {
            $return['ipblacklist'] = F0FModel::getTmpInstance('Ipbls', 'AdmintoolsModel')->getList(true);
        }

        if(isset($exportData['ipwhitelist']))
        {
            $return['ipwhitelist'] = F0FModel::getTmpInstance('Ipwls', 'AdmintoolsModel')->getList(true);
        }

        if(isset($exportData['badwords']))
        {
            $return['badwords'] = F0FModel::getTmpInstance('Badwords', 'AdmintoolsModel')->getList(true);
        }

        if(isset($exportData['emailtemplates']))
        {
            $return['emailtemplates'] = F0FModel::getTmpInstance('Waftemplates', 'AdmintoolsModel')->getList(true);
        }

        return $return;
    }

    public function importData()
    {
        $db = $this->getDbo();

        $input = new F0FInput('files');
        $file  = $input->get('importfile', null, 'file', 2);

        // Sanity checks
        if(!$file)
        {
            $this->setError(JText::_('COM_ADMINTOOLS_IMPORTEXPORT_NOFILE'));
            return false;
        }

        $data = file_get_contents($file['tmp_name']);

        if($data === false)
        {
            $this->setError(JText::_('COM_ADMINTOOLS_IMPORTEXPORT_ERR_READING_FILE'));
            return false;
        }

        $data = json_decode($data, true);

        if(!$data)
        {
            $this->setError(JText::_('COM_ADMINTOOLS_IMPORTEXPORT_ERR_READING_FILE'));
            return false;
        }

        // Everything seems ok, let's start importing data
        $result = true;

        if(isset($data['wafconfig']))
        {
            /** @var AdmintoolsModelWafconfig $config */
            $config = F0FModel::getTmpInstance('Wafconfig', 'AdmintoolsModel');
            $config->saveConfig($data['wafconfig']);
        }

        if(isset($data['ipblacklist']))
        {
            try
            {
                $db->truncateTable('#__admintools_ipblock');

                $insert = $db->getQuery(true)
                             ->insert($db->qn('#__admintools_ipblock'))
                             ->columns(array($db->qn('ip'), $db->qn('description')));

                // I could have several records, let's create a single big query
                foreach ($data['ipblacklist'] as $row)
                {
                    $insert->values($db->q($row['ip']).', '.$db->q($row['description']));
                }

                $db->setQuery($insert)->execute();

            }
            catch(Exception $e)
            {
                $this->setError(JText::_('COM_ADMINTOOLS_IMPORTEXPORT_ERR_BLACKLIST'));
                $result = false;
            }

        }

        if(isset($data['ipwhitelist']))
        {
            try
            {
                $db->truncateTable('#__admintools_adminiplist');

                // I could have several records, let's create a single big query
                $insert = $db->getQuery(true)
                             ->insert($db->qn('#__admintools_adminiplist'))
                             ->columns(array($db->qn('ip'), $db->qn('description')));

                foreach ($data['ipwhitelist'] as $row)
                {
                    $insert->values($db->q($row['ip']).', '.$db->q($row['description']));
                }

                $db->setQuery($insert)->execute();

            }
            catch(Exception $e)
            {
                $this->setError(JText::_('COM_ADMINTOOLS_IMPORTEXPORT_ERR_WHITELIST'));
                $result = false;
            }
        }

        if(isset($data['badwords']))
        {
            try
            {
                $db->truncateTable('#__admintools_badwords');

                // I could have several records, let's create a single big query
                $insert = $db->getQuery(true)
                    ->insert($db->qn('#__admintools_badwords'))
                    ->columns(array($db->qn('word')));

                foreach ($data['badwords'] as $row)
                {
                    $insert->values($db->q($row['word']));
                }

                $db->setQuery($insert)->execute();

            }
            catch(Exception $e)
            {
                $this->setError(JText::_('COM_ADMINTOOLS_IMPORTEXPORT_ERR_BADWORDS'));
                $result = false;
            }
        }

        if(isset($data['emailtemplates']))
        {
            try
            {
                $db->truncateTable('#__admintools_waftemplates');
            }
            catch(Exception $e)
            {
                $this->setError(JText::_('COM_ADMINTOOLS_IMPORTEXPORT_ERR_EMAILTEMPLATES'));
                $result = false;
            }

            $table = F0FModel::getTmpInstance('Waftemplate', 'AdmintoolsModel')->getTable();

            // Most likely I will only have 10-12 templates max, so I can use the table instead of directly writing inside the db
            foreach ($data['emailtemplates'] as $row)
            {
                $table->reset();
                $table->admintools_waftemplate_id = null;

                // Let's leave primary key handling to the database
                unset($row['admintools_waftemplate_id']);
                unset($row['created_by']);
                unset($row['created_on']);
                unset($row['modified_by']);
                unset($row['modified_on']);

                // Calling the save method will trigger all the checks
                if(!$table->save($row))
                {
                    // There was an error, better stop here
                    $this->setError(JText::_('COM_ADMINTOOLS_IMPORTEXPORT_ERR_EMAILTEMPLATES'));
                    $result = false;
                    break;
                }
            }
        }

        return $result;
    }
}