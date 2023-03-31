<?php
/**
 * Created by PhpStorm.
 * User: bhubinet
 * Date: 04/09/22
 */
defined( '_JEXEC' ) or die( JText::_('RESTRICTED_ACCESS') );
require_once (JPATH_COMPONENT_SITE.DS.'helpers'.DS.'access.php');
class EmundusControllerSamples extends JControllerLegacy
{
	function display($cachable = false, $urlparams = false) {
		// Set a default view if none exists
		if ( ! JRequest::getCmd( 'view' ) ) {
			$default = 'migration';
			JRequest::setVar('view', $default );
		}
		parent::display();
	}

    function generate(){
        if(EmundusHelperAccess::asAdministratorAccessLevel(JFactory::getUser()->id)) {
            $app = JFactory::getApplication();
            $datas = $app->input->getArray();

            include_once(JPATH_SITE.'/administrator/components/com_emundus/models/samples.php');
            $mSamples = new EmundusModelSamples();

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
