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
        require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'award.php');

        $this->_user = JFactory::getSession()->get('emundusUser');

        $this->_db = JFactory::getDBO();

        parent::__construct($config);
    }
    public function addvote(){

        $jinput = JFactory::getApplication()->input;
        $fnum = $jinput->post->getString('fnum', null);

        $m_model = new EmundusModelAward();

        try{
            $res = $m_model->updatePlusNbVote($fnum);
        }
        catch(Exception $e){
            $res = false;
            echo "Captured Throwable: " . $e->getMessage() . PHP_EOL;
        }

        $nb_vote_update = $m_model->getNbVote($fnum);


        $results = array('status'=>$res,'nb_vote'=>$nb_vote_update);

        echo json_encode($results);
        exit;
    }
    public function deletevote(){
        $jinput = JFactory::getApplication()->input;
        $fnum = $jinput->post->getString('fnum', null);

        $m_model = new EmundusModelAward();

        try{
            $res = $m_model->updateMinusNbVote($fnum);
        }
        catch(Exception $e){
            $res = false;
            echo "Captured Throwable: " . $e->getMessage() . PHP_EOL;
        }

        $nb_vote_update = $m_model->getNbVote($fnum);


        $results = array('status'=>$res,'nb_vote'=>$nb_vote_update);

        echo json_encode($results);
        exit;
    }
}