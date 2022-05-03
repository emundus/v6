<?php
/**
 * @package    Joomla
 * @subpackage eMundus
 * @link       http://www.emundus.fr
 * @license    GNU/GPL
 * @author     Benjamin Rivalland
 */

// No direct access
/*
if (version_compare(PHP_VERSION, '5.3.0') >= 0) {

    use PhpOffice\PhpWord\IOFactory;
    use PhpOffice\PhpWord\PhpWord;
    use PhpOffice\PhpWord\TemplateProcessor;
}
*/

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
//use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.controller');
jimport( 'joomla.user.helper' );
require_once (JPATH_SITE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'award.php');

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


        $this->_user = JFactory::getSession()->get('emundusUser');

        $this->_db = JFactory::getDBO();

        parent::__construct($config);
    }
    public function addvote(){

        $jinput = JFactory::getApplication()->input;
        $fnum = $jinput->post->getVar('fnum', null);
        $user = $jinput->post->getString('user', null);
        $thematique = $jinput->post->getString('thematique', null);
        $engagement = $jinput->post->getString('engagement', null);
        $campaign_id = $jinput->post->getString('campaign_id', null);
        $student_id = $jinput->post->getString('student_id', null);



        $m_model = new EmundusModelAward();

        try{
            $m_model->updatePlusNbVote($fnum,$user,$thematique,$engagement, $student_id, $campaign_id);
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
    public function favoris(){
        $jinput = JFactory::getApplication()->input;
        $fnum = $jinput->post->getString('fnum', null);
        $user = $jinput->post->getString('user', null);

        $m_model = new EmundusModelAward();

        try{
            $favoris = $m_model->getFavoris($fnum,$user);
            if(empty($favoris)){
                $m_model->addToFavoris($fnum,$user);
                $res='add';
            }
            else{
                $m_model->deleteToFavoris($fnum,$user);
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