<?php
/**
 * @package    Joomla
 * @subpackage eMundus
 * @link       http://www.emundus.fr
 * @license    GNU/GPL
 * @author     Benjamin Rivalland
 */

// No direct access

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.controller');
jimport( 'joomla.user.helper' );
require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'award.php');

use Joomla\CMS\Factory;

/**
 * eMundus Component Controller
 *
 * @package    Joomla
 * @subpackage eMundus
 */
//error_reporting(E_ALL);
/**
 * Class EmundusControllerFiles
 */
class EmundusControllerAward extends JControllerLegacy
{
    public function __construct($config = array())
    {
        parent::__construct($config);
    }

	public function addvote()
	{
		$fnum        = $this->input->post->getVar('fnum', null);
		$user        = $this->input->post->getString('user', null);
		$thematique  = $this->input->post->getString('thematique', null);
		$engagement  = $this->input->post->getString('engagement', null);
		$campaign_id = $this->input->post->getString('campaign_id', null);
		$student_id  = $this->input->post->getString('student_id', null);

		$m_award = $this->getModel('Award');

        try{
			$m_award->updatePlusNbVote($fnum, $user, $thematique, $engagement, $student_id, $campaign_id);
            $res = true;

        }
        catch(Exception $e){
            $res = false;
            echo "Captured Throwable: " . $e->getMessage() . PHP_EOL;
        }

        $results = array('status'=>$res);

        echo json_encode($results);
        exit;
    }

	public function favoris()
	{
		$fnum = $this->input->post->getString('fnum', null);
		$user = $this->input->post->getString('user', null);

		$m_award = $this->getModel('Award');

        try{
			$favoris = $m_award->getFavoris($fnum, $user);
            if(empty($favoris)){
				$m_award->addToFavoris($fnum, $user);
                $res='add';
            }
            else{
				$m_award->deleteToFavoris($fnum, $user);
                $res='delete';
            }

        }
        catch(Exception $e){
            $res = false;
            echo "Captured Throwable: " . $e->getMessage() . PHP_EOL;
        }
        $results = array('status'=>$res);

        echo json_encode($results);
        exit;

    }
}