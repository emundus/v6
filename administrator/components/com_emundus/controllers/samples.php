<?php
/**
 * Samples controller class
 *
 * @package     Joomla.Administrator
 * @subpackage  eMundus
 * @copyright   Copyright (C) 2005-2020  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;

require_once (JPATH_SITE.'/components/com_emundus/helpers/access.php');

class EmundusControllerSamples extends JControllerLegacy
{
    private JUser|null $user = null;

	function display($cachable = false, $urlparams = false) {
        $input = Factory::getApplication()->input;

        // Set a default view if none exists
        if (!$input->getCmd( 'view')) {
            $default = 'samples';
            $input->set('view', $default);
        }

        $this->user = Factory::getUser();

        parent::display();
	}

    function generate(){
        if(EmundusHelperAccess::asAdministratorAccessLevel($this->user->id)) {
            $app = Factory::getApplication();
            $datas = $app->input->getArray();

            include_once(JPATH_SITE.'/administrator/components/com_emundus/models/samples.php');
            $mSamples = new EmundusAdminModelSamples();

            if($datas['samples_programs']){
                $i = 0;
                $codes = [];
                while($i < $datas['samples_programs']) {
                    $codes[] = $mSamples->createSampleProgram('Programme ' . $i,'prog-' . $i);
                    $i++;
                }
            }

            if($datas['samples_campaigns']){
                $i = 0;
                $campaigns = [];
                while($i < $datas['samples_campaigns']) {
                    $campaigns[] = $mSamples->createSampleCampaign('Campagne ' . $i);
                    $i++;
                }
            }

            if($datas['samples_users']){
                $i = 0;
                $nb_files_created = 0;

                while($i < $datas['samples_users']) {
	                $j = 0;
                    $user = $mSamples->createSampleUser(9);
                    $i++;
                    if ($datas['samples_files']) {
                        while($j < $datas['samples_files']) {
                            $nb_files_created += $mSamples->createSampleFile($user->id);
                            $j++;
                        }
                    }
                }

                if ($datas['samples_files']) {
                    $app->enqueueMessage($nb_files_created . ' dossiers ont été créés.');
                }
            } elseif ($datas['samples_files']){
                $nb_files_created = 0;
                $j = 0;
                while($j < $datas['samples_files']) {
                    sleep(1);
                    $nb_files_created += $mSamples->createSampleFile();
                    $j++;
                }

                $app->enqueueMessage($nb_files_created . ' dossiers ont été créés.');
            }

            $app->redirect(JURI::base() . 'index.php?option=com_emundus&controller=samples');
        }
    }
}
