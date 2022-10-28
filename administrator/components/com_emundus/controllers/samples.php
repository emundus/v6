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
            $datas = JFactory::getApplication()->input->getArray();

            include_once(JPATH_SITE.'/administrator/components/com_emundus/models/samples.php');
            $mSamples = new EmundusModelSamples();

            if($datas['samples_campaigns']){
                $i = 0;
                $campaigns = [];
                while($i < $datas['samples_campaigns']) {
                    $campaigns[] = $mSamples->createSampleCampaign('Campagne de test ' . $i);
                    $i++;
                }
            }

            if($datas['samples_users']){
                $i = 0;
                $j = 0;
                while($i < $datas['samples_users']) {
                    $user = $mSamples->createSampleUser(9,'user'.$i.'.test@emundus.fr');
                    $i++;
                    if($datas['samples_files']){
                        while($j < $datas['samples_files']) {
                            $mSamples->createSampleFile($user->id);
                            $j++;
                        }
                    }
                }
            } elseif ($datas['samples_files']){
                $j = 0;
                while($j < $datas['samples_files']) {
                    $mSamples->createSampleFile();
                    $j++;
                }
            }
        }

    }
}
