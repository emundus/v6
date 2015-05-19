<?php
/**
 * @package    eMundus
 * @subpackage Components
 *             components/com_emundus/emundus.php
 * @link       http://www.decisionpublique.fr
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
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.controller');

/**
 * eMundus Component Controller
 *
 * @package    Joomla.Tutorials
 * @subpackage Components
 */
//error_reporting(E_ALL);
/**
 * Class EmundusControllerFiles
 */
class EmundusControllerFiles extends JControllerLegacy
{
    /**
     * @var JUser|null
     */
    var $_user = null;
    /**
     * @var JDatabase|null
     */
    var $_db = null;

    /**
     * @param array $config
     */
    public function __construct($config = array())
    {
        //require_once (JPATH_COMPONENT.DS.'helpers'.DS.'javascript.php');
        require_once (JPATH_COMPONENT.DS.'helpers'.DS.'files.php');
        require_once (JPATH_COMPONENT.DS.'helpers'.DS.'filters.php');
        require_once (JPATH_COMPONENT.DS.'helpers'.DS.'list.php');
        require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
        require_once (JPATH_COMPONENT.DS.'helpers'.DS.'emails.php');
        require_once (JPATH_COMPONENT.DS.'helpers'.DS.'export.php');
        require_once (JPATH_COMPONENT.DS.'helpers'.DS.'menu.php');


        $this->_user = JFactory::getUser();
        $this->_db = JFactory::getDBO();

        parent::__construct($config);
    }

    /**
     * @param bool $cachable
     * @param bool $urlparams
     */
    public function display($cachable = false, $urlparams = false)
    {
        // Set a default view if none exists
        if ( ! JRequest::getCmd( 'view' ) )
        {
            $default = 'files';
            JRequest::setVar('view', $default );
        }
        parent::display();
    }

////// EMAIL APPLICANT WITH CUSTOM MESSAGE///////////////////
    /**
     *
     */
    public function applicantemail()
    {
        require_once (JPATH_COMPONENT.DS.'helpers'.DS.'emails.php');
        @EmundusHelperEmails::sendApplicantEmail();
    }

    /**
     *
     */
    public function groupmail()
    {
        require_once (JPATH_COMPONENT.DS.'helpers'.DS.'emails.php');
        @EmundusHelperEmails::sendGroupEmail();
    }

    /**
     *
     */
    public function clear()
    {
        @EmundusHelperFiles::clear();
        echo json_encode((object)(array('status' => true)));
        exit;
    }

    /**
     *
     */
    public function setfilters()
    {

        $jinput = JFactory::getApplication()->input;
        $filterName = $jinput->getString('id', null);
        $elements = $jinput->getString('elements', null);
        $multi = $jinput->getString('multi', null);

        @EmundusHelperFiles::clearfilter();

        if($multi == "true")
        {
            $filterval = $jinput->get('val', array(), 'ARRAY');
        }
        else
        {
            $filterval = $jinput->getString('val', null);
        }

        $session = JFactory::getSession();
        $params = $session->get('filt_params');

        if($elements == 'false')
        {
            $params[$filterName] = $filterval;
        }
        else
        {
            $vals = (array)json_decode(stripslashes($filterval));

            if(isset($vals[0]->name))
            {
                foreach ($vals as $val)
                {
                    if($val->adv_fil)
                        $params['elements'][$val->name] = $val->value;
                    else
                        $params[$val->name] = $val->value;
                }

            }
            else
                $params['elements'][$filterName] = $filterval;
        }

        $session->set('filt_params', $params);


        $session->set('limitstart', 0);
        echo json_encode((object)(array('status' => true)));
        exit();

    }

    /**
     * @throws Exception
     */
    public function loadfilters()
    {
        try
        {
            $jinput = JFactory::getApplication()->input;
            $id = $jinput->getInt('id', null);
            $filter = @EmundusHelperFiles::getEmundusFilters($id);
            $params = (array) json_decode($filter->constraints);
            $params['select_filter'] = $id;
            $params =  json_decode($filter->constraints, true);

            JFactory::getSession()->set('select_filter', $id);
            if(isset($params['filter_order']))
            {
                JFactory::getSession()->set('filter_order', $params['filter_order']);
                JFactory::getSession()->set('filter_order_Dir', $params['filter_order_Dir']);
            }
            JFactory::getSession()->set('filt_params', $params['filter']);
            if(!empty($params['col']))
                JFactory::getSession()->set('adv_cols', $params['col']);

            echo json_encode((object)(array('status' => true)));
            exit();
        }
        catch(Exception $e)
        {
            throw new Exception;
        }
    }

    /**
     *
     */
    public function order()
    {
        $jinput = JFactory::getApplication()->input;
        $order = $jinput->getString('filter_order', null);
        $ancientOrder = JFactory::getSession()->get('filter_order');
        $params = JFactory::getSession()->get('filt_params');
        JFactory::getSession()->set('filter_order', $order);
        $params['filter_order'] = $order;

        if($order == $ancientOrder)
        {
            if(JFactory::getSession()->get('filter_order_Dir') == 'desc')
            {
                JFactory::getSession()->set('filter_order_Dir', 'asc');
                $params['filter_order_Dir'] = 'asc';
            }
            else
            {
                JFactory::getSession()->set('filter_order_Dir', 'desc');
                $params['filter_order_Dir'] = 'desc';
            }
        }
        else
        {
            JFactory::getSession()->set('filter_order_Dir', 'asc');
            $params['filter_order_Dir'] = 'asc';
        }
        JFactory::getSession()->set('filt_params', $params);
        echo json_encode((object)(array('status' => true)));
        exit;
    }

    /**
     *
     */
    public function setlimit()
    {
        $jinput = JFactory::getApplication()->input;
        $limit = $jinput->getInt('limit', null);

        JFactory::getSession()->set('limit', $limit);
        JFactory::getSession()->set('limitstart', 0);

        echo json_encode((object)(array('status' => true)));
        exit;
    }

    /**
     *
     */
    public function savefilters()
    {
        $name = JRequest::getVar('name', null, 'POST', 'none',0);
        $current_user = JFactory::getUser();
        $user_id = $current_user->id;
        $itemid = JRequest::getVar('Itemid', null, 'GET', 'none',0);

        $filt_params = JFactory::getSession()->get('filt_params');
        $adv_params = JFactory::getSession()->get('adv_cols');
        $constraints = array('filter'=>$filt_params, 'col'=>$adv_params);

        $constraints = json_encode($constraints);

        if(empty($itemid))
        {
            $itemid = JRequest::getVar('Itemid', null, 'POST', 'none',0);
        }

        $time_date = (date('Y-m-d H:i:s'));

        $query = "INSERT INTO #__emundus_filters (time_date,user,name,constraints,item_id) values('".$time_date."',".$user_id.",'".$name."',".$this->_db->quote($constraints).",".$itemid.")";
        $this->_db->setQuery( $query );

        try
        {
            $this->_db->Query();
            $query = 'select f.id, f.name from #__emundus_filters as f where f.time_date = "'.$time_date.'" and user = '.$user_id.' and name="'.$name.'" and item_id="'.$itemid.'"';
            $this->_db->setQuery($query);
            $result = $this->_db->loadObject();
            echo json_encode((object)(array('status' => true, 'filter' => $result)));
            exit;

        }
        catch (Exception $e)
        {
            echo json_encode((object)(array('status' => false)));
            exit;
        }
    }

    /**
     *
     */
    public function deletefilters()
    {
        $jinput = JFactory::getApplication()->input;
        $filter_id = $jinput->getInt('id', null);

        $query="DELETE FROM #__emundus_filters WHERE id=".$filter_id;
        $this->_db->setQuery( $query );
        $result=$this->_db->Query();

        if($result!=1)
        {
            echo json_encode((object)(array('status' => false)));
            exit;
        }
        else
        {
            echo json_encode((object)(array('status' => true)));
            exit;
        }
    }

    /**
     *
     */
    public function setlimitstart()
    {
        $jinput = JFactory::getApplication()->input;
        $limistart = $jinput->getInt('limitstart', null);
        $limit = intval(JFactory::getSession()->get('limit'));
        $limitstart = ($limit != 0 ? ($limistart > 1 ? (($limistart - 1) * $limit) : 0) : 0);
        JFactory::getSession()->set('limitstart', $limitstart);

        echo json_encode((object)(array('status' => true)));
        exit;
    }

    /**
     * @throws Exception
     */
    public function getadvfilters()
    {
        try
        {
            $elements = @EmundusHelperFiles::getElements();

            echo json_encode((object)(array('status' => true, 'default' => JText::_('PLEASE_SELECT'), 'defaulttrash' => JText::_('REMOVE_SEARCH_ELEMENT'), 'options' => $elements)));

            exit;
        }
        catch(Exception $e)
        {
            throw $e;
        }
    }

    /**
     * @throws Exception
     */
    public function getbox()
    {
        try
        {
            $jinput = JFactory::getApplication()->input;
            $id = $jinput->getInt('id', null);
            $index = $jinput->getInt('index', null);
            $params = JFactory::getSession()->get('filt_params');
            $element = @EmundusHelperFiles::getElementsName($id);
            $tab_name = (isset($element[$id]->table_join)?$element[$id]->table_join:$element[$id]->tab_name);
            $key = $tab_name . '.' . $element[$id]->element_name;
            $params['elements'][$key] = '';

            $advCols = JFactory::getSession()->get('adv_cols');

            if(!JFactory::getSession()->has('adv_cols') || count($advCols) == 0)
            {
                $advCols = array($index => $id);
            }
            else
            {
                $advCols = JFactory::getSession()->get('adv_cols');
                if(isset($advCols[$index])) {
                    $lastId = @$advCols[$index];
                    if (!in_array($id, $advCols))
                    {
                        $advCols[$index] = $id;
                    }
                    if(array_key_exists($index, $advCols))
                    {
                        $lastElt = @EmundusHelperFiles::getElementsName($lastId);
                        $tab_name = (isset($lastElt[$lastId]->table_join)?$lastElt[$lastId]->table_join:$lastElt[$lastId]->tab_name);
                        unset($params['elements'][$tab_name . '.' . $lastElt[$lastId]->element_name]);
                    }
                }
                else
                    $advCols[$index] = $id;
            }
            JFactory::getSession()->set('filt_params', $params);
            JFactory::getSession()->set('adv_cols', $advCols);

            $html= @EmundusHelperFiles::setSearchBox($element[$id], '', $tab_name . '.' . $element[$id]->element_name, $index);

            echo json_encode((object)(array('status' => true, 'default' => JText::_('PLEASE_SELECT'), 'defaulttrash' => JText::_('REMOVE_SEARCH_ELEMENT'), 'html' => $html)));
            exit;
        }
        catch(Exception $e)
        {
            throw $e;
        }
    }

    /**
     *
     */
    public function deladvfilter()
    {
        $jinput = JFactory::getApplication()->input;
        $name = $jinput->getString('elem', null);
        $id = $jinput->getInt('id',null);
        $params = JFactory::getSession()->get('filt_params');
        $advCols = JFactory::getSession()->get('adv_cols');
        unset($params['elements'][$name]);
        unset($advCols[$id]);
        JFactory::getSession()->set('filt_params', $params);
        JFactory::getSession()->set('adv_cols', $advCols);


        echo json_encode((object)(array('status' => true)));
        exit;
    }

    /**
     *
     */
    public function addcomment()
    {
        $jinput = JFactory::getApplication()->input;
        $user = JFactory::getUser()->id;
        $fnums = $jinput->getString('fnums', null);
        $title = $jinput->getString('title', '');
        $comment = $jinput->getString('comment', null);
        $fnums = (array) json_decode(stripslashes($fnums));
        $appModel = $this->getModel('Application');

        foreach($fnums as $fnum)
        {
            if(EmundusHelperAccess::asAccessAction(10, 'c', $user, $fnum))
            {
                $aid = intval(substr($fnum, 21, 7));
                $res = $appModel->addComment((array('applicant_id' => $aid, 'user_id' => $user, 'reason' => $title, 'comment_body' => $comment, 'fnum' => $fnum)));
                if($res == 0)
                {
                    echo json_encode((object)(array('status' => false, 'msg' => JText::_('ERROR'). $res)));
                    exit;
                }
            }
        }

        echo json_encode((object)(array('status' => true, 'msg' => JText::_('COMMENT_SUCCESS'), 'id' => $res)));
        exit;
    }

    /**
     *
     */
    public function gettags()
    {
        $model = $this->getModel('Files');


        $tags = $model->getAllTags();

        echo json_encode((object)(array('status' => true,
                                        'tags' => $tags,
                                        'tag' => JText::_('TAGS'),
                                        'select_tag' => JText::_('PLEASE_SELECT_TAG'))));
        exit;
    }

    /**
     * Add a tag to an application
     */
    public function tagfile()
    {
        $jinput = JFactory::getApplication()->input;
        $fnums = $jinput->getString('fnums', null);
        $tag = $jinput->getInt('tag', null);
        $fnums = ($fnums=='all')?'all':(array) json_decode(stripslashes($fnums));
        $model = $this->getModel('Files');

        if($fnums == "all")
        {
            $fnums = $model->getAllFnums();
        }
        $validFnums = array();

        foreach($fnums as $fnum)
        {
            if(EmundusHelperAccess::asAccessAction(14, 'c', $this->_user->id, $fnum))
            {
                $validFnums[] = $fnum;
            }
        }
        unset($fnums);

        $res = $model->tagFile($validFnums, $tag);
        $tagged = $model->getTaggedFile($tag);

        echo json_encode((object)(array('status' => true, 'msg' => JText::_('TAG_SUCCESS'), 'tagged' => $tagged)));
        exit;
    }

    /**
     *
     */
    public function share()
    {
        $jinput = JFactory::getApplication()->input;
        $fnums = $jinput->getString('fnums', null);
        $actions = $jinput->getString('actions', null);
        $groups = $jinput->getString('groups', null);
        $evals = $jinput->getString('evals', null);

        $actions = (array) json_decode(stripslashes($actions));

        $fnums = (array) json_decode(stripslashes($fnums));
        $model = $this->getModel('Files');
        $validFnums = array();
        foreach($fnums as $fnum)
        {
            if(EmundusHelperAccess::asAccessAction(11, 'c', $this->_user->id, $fnum))
            {
                $validFnums[] = $fnum;
            }
        }
        unset($fnums);
        if(count($validFnums) > 0)
        {
            if(!empty($groups))
            {
                $groups = (array) json_decode(stripslashes($groups));
                $res = $model->shareGroups($groups, $actions, $validFnums);
            }

            if(!empty($evals))
            {
                $evals = (array) json_decode(stripslashes($evals));
                $res = $model->shareUsers($evals, $actions, $validFnums);
            }

            if($res !== false)
            {
                $msg = JText::_('SHARE_SUCCESS');
            }
            else
            {
                $msg = JText::_('SHARE_ERROR');
            }
        }
        else
        {
            $fnums = $model->getAllFnums();
            if($groups !== null)
            {
                $groups = (array) json_decode(stripslashes($groups));
                $res = $model->shareGroups($groups, $actions, $fnums);
            }

            if($evals !== null)
            {
                $evals = (array) json_decode(stripslashes($evals));
                $res = $model->shareUsers($evals, $actions, $fnums);
            }

            if($res !== false)
            {
                $msg = JText::_('SHARE_SUCCESS');
            }
            else
            {
                $msg = JText::_('SHARE_ERROR');

            }
        }
        echo json_encode((object)(array('status' => $res, 'msg' => $msg)));
        exit;
    }

    /**
     *
     */
    public function getstate()
    {
        $model = $this->getModel('Files');


        $states = $model->getAllStatus();

        echo json_encode((object)(array('status' => true,
                                        'states' => $states,
                                        'state' => JText::_('STATE'),
                                        'select_state' => JText::_('PLEASE_SELECT_STATE'))));
        exit;
    }

    /**
     *
     */
    public function updatestate()
    {
        $jinput = JFactory::getApplication()->input;
        $fnums = $jinput->getString('fnums', null);
        $state = $jinput->getInt('state', null);

        $fnums = (array) json_decode(stripslashes($fnums));
        $model = $this->getModel('Files');
        if(!is_array($fnums) || count($fnums) == 0 || $fnums[0] == "all")
        {
            $fnums = $model->getAllFnums();
        }

        $validFnums = array();

        foreach($fnums as $fnum)
        {
            if(EmundusHelperAccess::asAccessAction(13, 'u', $this->_user->id, $fnum))
            {
                $validFnums[] = $fnum;
            }
        }

        $res = $model->updateState($validFnums, $state);

        if($res !== false)
        {
            // Get all codes from fnum
            $fnumsInfos = $model->getFnumsInfos($validFnums);
            $code = array();
            foreach ($fnumsInfos as $fnum) {
                $code[] = $fnum['training'];
            }

            // Get triggered email
            include_once(JPATH_BASE.'/components/com_emundus/models/emails.php');
            $emails = new EmundusModelEmails;
            $trigger_emails = $emails->getEmailTrigger($state, $code, 1);

            if (count($trigger_emails) > 0) {
                foreach ($trigger_emails as $key => $trigger_email) {
                    // Manage with selected fnum
                    foreach($fnumsInfos as $file) {
                        $post = array();
                        $tags = $emails->setTags($file['applicant_id'], $post);

                        $from = preg_replace($tags['patterns'], $tags['replacements'], $trigger_email[$file['training']]['tmpl']['emailfrom']);
                        $from_id = 62;
                        $fromname = preg_replace($tags['patterns'], $tags['replacements'], $trigger_email[$file['training']]['tmpl']['name']);
                        $to = $file['email'];
                        $subject = preg_replace($tags['patterns'], $tags['replacements'], $trigger_email[$file['training']]['tmpl']['subject']);
                        $body = preg_replace($tags['patterns'], $tags['replacements'], $trigger_email[$file['training']]['tmpl']['message']);
                        $mode = 1;
                        //$attachment[] = $path_file;
                        $replyto = $from;
                        $replytoname = $fromname;

                        $res = JUtility::sendMail($from, $fromname, $to, $subject, $body, true);
                        if ($res) {
                            $message = array(
                                'user_id_from' => $from_id,
                                'user_id_to' => $file['applicant_id'],
                                'subject' => $subject,
                                'message' => '<i>' . JText::_('MESSAGE') . ' ' . JText::_('SENT') . ' ' .
                                    JText::_('TO') . ' ' . $to . '</i><br>' . $body
                            );
                            $emails->logEmail($message);
                        }
                    }
                    // Manage with default recipient by programme
                    foreach ($trigger_email as $code => $trigger) {
                        foreach ($trigger['to']['recipients'] as $key => $recipient) {

                            $post = array();
                            $tags = $emails->setTags($recipient['id'], $post);

                            $from = preg_replace($tags['patterns'], $tags['replacements'], $trigger['tmpl']['emailfrom']);
                            $from_id = 62;
                            $fromname = preg_replace($tags['patterns'], $tags['replacements'], $trigger['tmpl']['name']);
                            $to = $recipient['email'];
                            $subject = preg_replace($tags['patterns'], $tags['replacements'], $trigger['tmpl']['subject']);
                            $body = preg_replace($tags['patterns'], $tags['replacements'], $trigger['tmpl']['message']);
                            $mode = 1;
                            //$attachment[] = $path_file;
                            $replyto = $from;
                            $replytoname = $fromname;

                            $res = JUtility::sendMail($from, $fromname, $to, $subject, $body, true);
                            if ($res) {
                                $message = array(
                                    'user_id_from' => $from_id,
                                    'user_id_to' => $recipient['id'],
                                    'subject' => $subject,
                                    'message' => '<i>' . JText::_('MESSAGE') . ' ' . JText::_('SENT') . ' ' .
                                        JText::_('TO') . ' ' . $to . '</i><br>' . $body
                                );
                                $emails->logEmail($message);
                            }

                        }
                    }
                }
            }

            $msg = JText::_('STATE_SUCCESS');
        }
        else
        {
            $msg = JText::_('STATE_ERROR');

        }
        echo json_encode((object)(array('status' => $res, 'msg' => $msg)));
        exit;
    }

    /**
     *
     */
    public function unlinkevaluators()
    {
        $jinput = JFactory::getApplication()->input;
        $fnum = $jinput->getString('fnum', null);
        $id = $jinput->getint('id', null);
        $group = $jinput->getString('group', null);

        $model = $this->getModel('Files');
        if($group == "true")
        {
            $res = $model->unlinkEvaluators($fnum, $id, true);
        }
        else
        {
            $res = $model->unlinkEvaluators($fnum, $id, false);
        }

        if($res)
        {
            $msg = JText::_('SUCCESS_SUPPR_EVAL');
        }
        else
        {
            $msg = JText::_('ERROR_SUPPR_EVAL');
        }

        echo json_encode((object)(array('status' => $res, 'msg' => $msg)));
        exit;
    }

    /**
     *
     */
    public function getfnuminfos()
    {
        $jinput = JFactory::getApplication()->input;
        $fnum = $jinput->getString('fnum', null);
        $res = false;
        $fnumInfos = null;

        if($fnum != null)
        {
            $model = $this->getModel('Files');
            $fnumInfos = $model->getFnumInfos($fnum);
            if($fnum !== false)
                $res = true;
        }
        JFactory::getSession()->set('application_fnum', $fnum);
        echo json_encode((object)(array('status' => $res, 'fnumInfos' => $fnumInfos)));
        exit;
    }

    /**
     *
     */
    public function deletefile()
    {
        $jinput = JFactory::getApplication()->input;
        $fnum = $jinput->getString('fnum', null);
        $model = $this->getModel('Files');
        $res = $model->changePublished($fnum);

        $result = array('status' => $res);

        echo json_encode((object)$result);
        exit;
    }

    /**
     *
     */
    public function getformelem()
    {
        //Filters
        $model = $this->getModel('Files');
        $defaultElements = $model->getDefaultElements();
        //$elements = EmundusHelperFilters::getElements();
        $elements = @EmundusHelperFiles::getElements();
        $res = array('status' => true, 'elts' => $elements, 'defaults' => $defaultElements);
        echo json_encode((object)$res);
        exit;
    }

    /**
     *
     */
    public function send_elements()
    {
        require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
        $current_user = JFactory::getUser();
        if(!@EmundusHelperAccess::asPartnerAccessLevel($current_user->id))
            die( JText::_('RESTRICTED_ACCESS') );

        $jinput = JFactory::getApplication()->input;
        $fnums = $jinput->getVar('fnums', null);
        $fnums = (array) json_decode(stripcslashes($fnums));
        $model = $this->getModel('Files');
        if(!is_array($fnums) || count($fnums)==0 || $fnums===null || @$fnums[0] == "all")
        {
            $fnums = $model->getAllFnums();
        }
        $validFnums = array();
        foreach($fnums as $fnum)
        {
            if(EmundusHelperAccess::asAccessAction(6, 'c', $this->_user->id, $fnum) && $fnum != 'em-check-all-all' && $fnum != 'em-check-all')
            {
                $validFnums[] = $fnum;
            }
        }
        $elts = $jinput->getString('elts', null);
//$elts = '{"0":"224","1":"1738","2":"1974","3":"2533","4":"2535","5":"2573","6":"2577","7":"2581","8":"2617","9":"2587","10":"2546","11":"2547","12":"2549","13":"2590","14":"2594","15":"2567","16":"2621"}';
        $elts = (array) json_decode(stripcslashes($elts));
        $objs = $jinput->getString('objs', null);
        $objs = (array) json_decode(stripcslashes($objs));
        $methode = $jinput->getString('methode', 0);

        // export Excel
        $name = $this->export_xls($validFnums, $objs, $elts, $methode);
        $result = array('status' => true, 'name' => $name);
        echo json_encode((object) $result);
        exit();
    }

    /**
     *
     */
    public function zip()
    {
        require_once (JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
        $current_user = JFactory::getUser();
        if(!@EmundusHelperAccess::asPartnerAccessLevel($current_user->id))
            die( JText::_('RESTRICTED_ACCESS') );
        $jinput = JFactory::getApplication()->input;
        $fnums = $jinput->getVar('fnums', null);
        $fnums = (array) json_decode(stripslashes($fnums));
        $model = $this->getModel('Files');
        if(!is_array($fnums) || count($fnums) == 0 || @$fnums[0] == "all")
        {
            $fnums = $model->getAllFnums();
        }
        $validFnums = array();
        foreach($fnums as $fnum)
        {
            if(EmundusHelperAccess::asAccessAction(6, 'c', $this->_user->id, $fnum))
            {
                $validFnums[] = $fnum;
            }
        }

        if (extension_loaded('zip'))
            $name = $this->export_zip($validFnums);
        else
            $name = $this->export_zip_pcl($validFnums);

        echo json_encode((object) array('status' => true, 'name' => $name));
        exit();
    }

    /**
     * @param $val
     * @return int|string
     */
    public function return_bytes($val)
    {
        $val = trim($val);
        $last = strtolower($val[strlen($val)-1]);
        switch($last)
        {
            // Le modifieur 'G' est disponible depuis PHP 5.1.0
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }

        return $val;
    }

    /**
     * @param $array
     * @param $orderArray
     * @return array
     */
    public function sortArrayByArray($array,$orderArray)
    {
        $ordered = array();
        foreach($orderArray as $key)
        {
            if(array_key_exists($key,$array))
            {
                $ordered[$key] = $array[$key];
                unset($array[$key]);
            }
        }
        return $ordered + $array;
    }

    /**
     * @param $object
     * @param $orderArray
     * @return array
     */
    public function sortObjectByArray($object, $orderArray)
    {

        $properties=get_object_vars($object);
        return sortArrayByArray($properties,$orderArray);
    }

    /**
     * @param $fnums
     * @param $objs
     * @param $element_id
     * @param $methode  aggregate in one cell (0) or split one data per line
     * @return string
     * @throws Exception
     */
    public function export_xls($fnums, $objs, $element_id, $methode=0)
    {
        //$mainframe = JFactory::getApplication();
        $current_user = JFactory::getUser();

        if( !@EmundusHelperAccess::asPartnerAccessLevel($current_user->id)
        )
            die( JText::_('RESTRICTED_ACCESS') );

        @set_time_limit(10800);
        jimport( 'joomla.user.user' );
        error_reporting(0);
        /** PHPExcel */
        ini_set('include_path', JPATH_BASE.DS.'libraries'.DS);

        include 'PHPExcel.php';
        include 'PHPExcel/Writer/Excel5.php';

        //$filename = 'emundus_applicants_'.date('Y.m.d').'.xls';

        $model = $this->getModel('Files');
        $elements = @EmundusHelperFiles::getElementsName(implode(',',$element_id));
        $fnumsArray = $model->getFnumArray($fnums, $elements, $methode);
        $status = $model->getStatusByFnums($fnums);

        $menu = @JSite::getMenu();
        $current_menu  = $menu->getActive();
        $menu_params = $menu->getParams($current_menu->id);
        $columnSupl = explode(',', $menu_params->get('em_actions'));
        $columnSupl = array_merge($columnSupl, $objs);
        $colOpt = array();
        $modelApp = $this->getModel('Application');

        foreach ($columnSupl as $col)
        {
            $col = explode('.', $col);
            switch ($col[0])
            {
                case "photo":
                    $colOpt['PHOTO'] = @EmundusHelperFiles::getPhotos($model, JURI::base());
                    break;
                case "forms":
                    $colOpt['forms'] = $modelApp->getFormsProgress(null, null, $fnums);
                    break;
                case "attachment":
                    $colOpt['attachment'] = $modelApp->getAttachmentsProgress(null, null, $fnums);
                    break;
                case "assessment":
                    $colOpt['assessment'] = @EmundusHelperFiles::getEvaluation('text', $fnums);
                    break;
                case "comment":
                    $colOpt['comment'] = $model->getCommentsByFnum($fnums);
                    break;
                case 'evaluators':
                    $colOpt['evaluators'] = @EmundusHelperFiles::createEvaluatorList($col[1], $model);
                    break;
            }
        }

        // Excel colonne
        $colonne_by_id = array();
        for ($i=ord("A");$i<=ord("Z");$i++) {
            $colonne_by_id[]=chr($i);
        }
        for ($i=ord("A");$i<=ord("Z");$i++) {
            for ($j=ord("A");$j<=ord("Z");$j++) {
                $colonne_by_id[]=chr($i).chr($j);
                if(count($colonne_by_id) == count($fnums)) break;
            }
        }

        // Create new PHPExcel object
        $objPHPExcel = new PHPExcel();
        // Initiate cache
        $cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
        $cacheSettings = array( 'memoryCacheSize' => '32MB');
        PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
        // Set properties
        $objPHPExcel->getProperties()->setCreator("Décision Publique : http://www.decisionpublique.fr/");
        $objPHPExcel->getProperties()->setLastModifiedBy("Décision Publique");
        $objPHPExcel->getProperties()->setTitle("eMmundus Report");
        $objPHPExcel->getProperties()->setSubject("eMmundus Report");
        $objPHPExcel->getProperties()->setDescription("Report from open source eMundus plateform : http://www.emundus.fr/");


        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->setTitle('Extraction');
        $objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        $objPHPExcel->getActiveSheet()->freezePane('A2');

        $i = 0;
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($i, 1, JText::_('F_NUM'));
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($i)->setWidth('40');
        $i++;
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($i, 1, JText::_('STATUS'));
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($i)->setWidth('40');
        $i++;
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($i, 1, JText::_('LAST_NAME'));
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($i)->setWidth('30');
        $i++;
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($i, 1, JText::_('FIRST_NAME'));
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($i)->setWidth('30');
        $i++;
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($i, 1, JText::_('EMAIL'));
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($i)->setWidth('30');
        $i++;
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($i, 1, JText::_('CAMPAIGN'));
        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($i)->setWidth('30');
        $i++;
//die(var_dump($elements));
        /*		foreach($fnumsArray[0] as $fKey => $fLine)
                {
                    if($fKey != 'fnum')
                    {
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($i, 1, JText::_(strtoupper($fKey)));
                        $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($i)->setWidth('30');

                        $i++;
                    }
                }
        */
//var_dump($fnumsArray);
        foreach($elements as $fKey => $fLine)
        {
            if($fLine->element_name != 'fnum' && $fLine->element_name != 'code' && $fLine->element_name != 'campaign_id')
            {
                $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($i, 1, $fLine->element_label);
                $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($i)->setWidth('30');

                $i++;
            }
        }
        foreach($colOpt as $kOpt => $vOpt)
        {
            $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($i, 1, JText::_(strtoupper($kOpt)));
            $objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($i)->setWidth('30');

            $i++;
        }
        $line = 2;
        foreach($fnumsArray as $fnunLine)
        {
            $col = 0;

            foreach($fnunLine as $k => $v)
            {
                if ($k != 'code' && $k != 'campaign_id' && $k != 'jos_emundus_campaign_candidature___campaign_id' && $k != 'c___campaign_id') {

                    if($k === 'fnum')
                    {
                        $objPHPExcel->getActiveSheet()->setCellValueExplicitByColumnAndRow($col, $line, (string) $v, PHPExcel_Cell_DataType::TYPE_STRING);
                        $col++;
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, $line, $status[$v]['value']);
                        $col++;
                        $uid = intval(substr($v, 21, 7));
                        $userProfil = JUserHelper::getProfile($uid)->emundus_profile;
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, $line, strtoupper($userProfil['lastname']));
                        $col++;
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, $line, $userProfil['firstname']);
                        $col++;
                    }
                    else
                    {
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, $line, $v);
                        $col++;
                    }
                }
            }

            foreach($colOpt as $kOpt => $vOpt)
            {
                switch($kOpt)
                {
                    case "photo":
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, $line, JText::_('photo'));
                        break;
                    case "forms":
                        $val = $vOpt[$fnunLine['fnum']];
                        $objPHPExcel->getActiveSheet()->getStyle($colonne_by_id[$col].':'.$colonne_by_id[$col])->getAlignment()->setWrapText(true);
                        if($val == 0) {
                            $rgb='FF6600';
                        } elseif($val == 100) {
                            $rgb='66FF66';
                        } elseif($val == 50) {
                            $rgb='FFFF00';
                        } else {
                            $rgb='FFFFFF';
                        }
                        $objPHPExcel->getActiveSheet()->getStyle($colonne_by_id[$col].$line)->applyFromArray(
                            array('fill' 	=> array('type'		=> PHPExcel_Style_Fill::FILL_SOLID,
                                                     'color'		=> array('argb' => 'FF'.$rgb)
                            ),
                            )
                        );
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, $line, $val.'%');
                        $objPHPExcel->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);
                        break;
                    case "attachment":
                        $val = $vOpt[$fnunLine['fnum']];
                        $objPHPExcel->getActiveSheet()->getStyle($colonne_by_id[$col].':'.$colonne_by_id[$col])->getAlignment()->setWrapText(true);
                        if($val == 0) {
                            $rgb='FF6600';
                        } elseif($val == 100) {
                            $rgb='66FF66';
                        } elseif($val == 50) {
                            $rgb='FFFF00';
                        } else {
                            $rgb='FFFFFF';
                        }
                        $objPHPExcel->getActiveSheet()->getStyle($colonne_by_id[$col].$line)->applyFromArray(
                            array('fill' 	=> array('type'		=> PHPExcel_Style_Fill::FILL_SOLID,
                                                     'color'		=> array('argb' => 'FF'.$rgb)
                            ),
                            )
                        );
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, $line, $val.'%');
                        $objPHPExcel->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);
                        //$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, $line, $vOpt[$fnunLine['fnum']]."%");
                        break;
                    case "assessment":
                        $eval = '';
                        $evaluations = $vOpt[$fnunLine['fnum']];
                        foreach ($evaluations as $evaluation) {
                            $eval .= $evaluation;
                            $eval .= chr(10).'______'.chr(10);
                        }
//						$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, $line, $vOpt[$fnunLine['fnum']]);
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, $line, $eval);
                        break;
                    case "comment":
                        $comments="";
                        foreach($colOpt['comment'] as $comment)
                        {
                            if($comment['fnum'] == $fnunLine['fnum'])
                            {
                                $comments .= $comment['reason'] . " | " . $comment['comment_body']."\rn";
                            }
                        }
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, $line, $comments);
                        break;
                    case 'evaluators':
                        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, $line, $vOpt[$fnunLine['fnum']]);
                        break;
                }
                $col++;
            }
            $line++;
        }

        $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);

        $objWriter->save(JPATH_BASE.DS.'tmp'.DS.JFactory::getUser()->id.'_extraction.xls');
        return JFactory::getUser()->id.'_extraction.xls';
        //$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
        // Echo done
    }

    /**
     * @param $filename
     * @param string $mimePath
     * @return bool
     */
    function get_mime_type($filename, $mimePath = '../etc') {
        $fileext = substr(strrchr($filename, '.'), 1);
        if (empty($fileext)) return (false);
        $regex = "/^([\w\+\-\.\/]+)\s+(\w+\s)*($fileext\s)/i";
        $lines = file("$mimePath/mime.types");
        foreach($lines as $line) {
            if (substr($line, 0, 1) == '#') continue; // skip comments
            $line = rtrim($line) . " ";
            if (!preg_match($regex, $line, $matches)) continue; // no match to the extension
            return ($matches[1]);
        }
        return (false); // no match at all
    }

    /**
     *
     */
    public function download()
    {
        $jinput = JFactory::getApplication()->input;

        $name = $jinput->getString('name', null);

        $file = JPATH_BASE.DS.'tmp'.DS.$name;

        if (file_exists($file)) {
            $mime_type = $this->get_mime_type($file);
            header('Content-type: application/'.$mime_type);
            header('Content-Disposition: inline; filename='.basename($file));
            header('Last-Modified: '.gmdate('D, d M Y H:i:s') . ' GMT');
            header('Cache-Control: no-store, no-cache, must-revalidate');
            header('Cache-Control: pre-check=0, post-check=0, max-age=0');
            header('Pragma: anytextexeptno-cache', true);
            header('Cache-control: private');
            header('Expires: 0');
            //header('Content-Transfer-Encoding: binary');
            //header('Content-Length: ' . filesize($file));
            //header('Accept-Ranges: bytes');

            ob_clean();
            flush();
            readfile($file);
            exit;
        } else {
            echo JText::_('FILE_NOT_FOUND').' : '.$file;
        }
    }
    /*
    * 	Create a zip file containing all documents attached to application fil number
    */
    /**
     * @param $fnums
     * @return string
     */
    function export_zip($fnums)
    {
        $view 			= JRequest::getCmd( 'view' );
        $current_user 	= JFactory::getUser();
        if ((!@EmundusHelperAccess::asPartnerAccessLevel($current_user->id)) &&
            $view != 'renew_application'
        )
            die( JText::_('RESTRICTED_ACCESS') );

        require_once(JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
        require_once(JPATH_BASE.DS.'libraries'.DS.'emundus'.DS.'pdf.php');

        $zip = new ZipArchive();
        $nom = date("Y-m-d").'_'.rand(1000,9999).'_x'.(count($fnums)-1).'.zip';

        $path = JPATH_BASE.DS.'tmp'.DS.$nom;
        $model = $this->getModel('Files');
        $files = $model->getFilesByFnums($fnums);

        if(file_exists($path))
            unlink($path);

        $users = array();
        foreach($fnums as $fnum)
        {
            $sid = intval(substr($fnum, -7));
            $users[$fnum] = JFactory::getUser($sid);

            if (!is_numeric($sid) || empty($sid)) {
                continue;
            }

            if($zip->open($path, ZipArchive::CREATE) == TRUE)
            {
                $dossier = EMUNDUS_PATH_ABS.$users[$fnum]->id.DS.$fnum;

                application_form_pdf($users[$fnum]->id, $fnum, false);
                $application_pdf = $fnum.'_application.pdf';

                $filename = $fnum.'_'.$application_pdf;

                if(!$zip->addFile($dossier.DS.$application_pdf, $filename)) {
                    continue;
                }

                $zip->close();
            } else {
                die ("ERROR");
            }
        }

        if($zip->open($path, ZipArchive::CREATE) == TRUE)
        {

            foreach($files as $key => $file)
            {
                $filename = $file['fnum'].'_'.$users[$file['fnum']]->name.DS.$file['filename'];
                $dossier = EMUNDUS_PATH_ABS.$users[$file['fnum']]->id.DS;

                if(!$zip->addFile($dossier.$file['filename'], $filename)) {
                    continue;
                }
            }

            $zip->close();

        } else {
            die ("ERROR");
        }

        return $nom;
    }

    /*
    * 	Create a zip file containing all documents attached to application fil number
    */
    /**
     * @param $fnums
     * @return string
     */
    function export_zip_pcl($fnums)
    {
        $view 			= JRequest::getCmd( 'view' );
        $current_user 	= JFactory::getUser();
        if ((!@EmundusHelperAccess::asPartnerAccessLevel($current_user->id)) &&
            $view != 'renew_application'
        )
            die( JText::_('RESTRICTED_ACCESS') );

        require_once(JPATH_COMPONENT.DS.'helpers'.DS.'access.php');
        require_once(JPATH_BASE.DS.'libraries'.DS.'emundus'.DS.'pdf.php');
        require_once(JPATH_BASE.DS.'libraries'.DS.'pclzip-2-8-2'.DS.'pclzip.lib.php');


        $nom = date("Y-m-d").'_'.rand(1000,9999).'_x'.(count($fnums)-1).'.zip';
        $path = JPATH_BASE.DS.'tmp'.DS.$nom;

        $zip = new PclZip($path);

        $model = $this->getModel('Files');
        $files = $model->getFilesByFnums($fnums);

        if(file_exists($path))
            unlink($path);

        $users = array();
        foreach($fnums as $fnum)
        {
            $sid = intval(substr($fnum, -7));
            $users[$fnum] = JFactory::getUser($sid);

            if (!is_numeric($sid) || empty($sid)) {
                continue;
            }

            $dossier = EMUNDUS_PATH_ABS.$users[$fnum]->id;
            $dir = $fnum.'_'.$users[$fnum]->name;
            application_form_pdf($users[$fnum]->id, $fnum, false);
            $application_pdf = $fnum.'_application.pdf';

            $zip->add($dossier.DS.$application_pdf, PCLZIP_OPT_REMOVE_ALL_PATH, PCLZIP_OPT_ADD_PATH, $dir);

        }


        foreach($files as $key => $file)
        {
            $dir = $file['fnum'].'_'.$users[$file['fnum']]->name;

            $dossier = EMUNDUS_PATH_ABS.$users[$file['fnum']]->id.DS;

            $zip->add($dossier.$file['filename'], PCLZIP_OPT_REMOVE_ALL_PATH, PCLZIP_OPT_ADD_PATH, $dir);

        }



        return $nom;
    }

    /*
    * 	Get evaluation Fabrik formid by fnum
    */
    /**
     *
     */
    function getformid()
    {
        $current_user = JFactory::getUser();

        if( !@EmundusHelperAccess::asPartnerAccessLevel($current_user->id)
        )
            die( JText::_('RESTRICTED_ACCESS') );

        $jinput = JFactory::getApplication()->input;
        $fnum = $jinput->getString('fnum', null);

        $model = $this->getModel('Files');
        $res = $model->getFormidByFnum($fnum);

        $formid = ($res>0)?$res:29;

        $result = array('status' => true, 'formid' => $formid);
        echo json_encode((object) $result);
        exit();
    }


    /*
    * 	Get my evaluation by fnum
    */
    /**
     *
     */
    function getevalid()
    {
        $current_user = JFactory::getUser();

        if( !@EmundusHelperAccess::asPartnerAccessLevel($current_user->id))
            die( JText::_('RESTRICTED_ACCESS') );

        $jinput = JFactory::getApplication()->input;
        $fnum = $jinput->getString('fnum', null);

        $evaluation = $this->getModel('Evaluation');
        $myEval = $evaluation->getEvaluationsFnumUser($fnum, $current_user->id);
        $evalid = ($myEval[0]->id>0)?$myEval[0]->id:-1;

        $result = array('status' => true, 'evalid' => $evalid);
        echo json_encode((object) $result);
        exit();
    }

    public function getdocs()
    {
        $jinput = JFactory::getApplication()->input;
        $code = $jinput->getString('code', "");
        $model = $this->getModel('Files');

        $res = new stdClass();
        $res->status = true;
        $res->options = $model->getDocsByProg($code);

        echo json_encode($res);
        exit();
    }

    public function generatedoc()
    {
        $jinput = JFactory::getApplication()->input;
        $fnums = $jinput->getString('fnums', "");
        $fnumsArray = explode(",", $fnums);
        $code = $jinput->getString('code', "");
        $idTmpl = $jinput->getString('id_tmpl', "");
        $model = $this->getModel('Files');
        $modelEvaluation = $this->getModel('Evaluation');
        $user = JFactory::getUser();
        $fnumsArray = $model->checkFnumsDoc($code, $fnumsArray);
        $tmpl = $modelEvaluation->getLettersTemplateByID($idTmpl);
        $attachInfos = $model->getAttachmentInfos($tmpl[0]['attachment_id']);
        switch($tmpl[0]['template_type'])
        {
            case 1:
                //Simple FILE

                break;
            case 2:
                //Generate PDF

                break;
            case 3:
                // template DOCX
                require_once JPATH_LIBRARIES.DS.'PHPWord'.DS.'src'.DS.'Autoloader.php';
                /*
                if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
                    \PhpOffice\PhpWord\Autoloader::register();
                }
                */
                $res = new stdClass();
                $res->status = true;
                $res->files = array();
                $fnumsInfos = $model->getFnumsTagsInfos($fnumsArray);
                $const = array('user_id' => $user->id, 'user_email' => $user->email, 'user_name' => $user->name, 'current_date' => date('d/m/Y', time()));
                try
                {
                    $preprocess = new TemplateProcessor(JPATH_BASE.$tmpl[0]['file']);

                    $tags = $preprocess->getVariables();
                    $idFabrik = array();
                    $setupTags = array();
                    foreach($tags as $i => $val)
                    {
                        $tag = strip_tags($val);
                        if(is_numeric($tag))
                        {
                            $idFabrik[] = $tag;
                        }
                        else
                        {
                            $setupTags[] = $tag;
                        }
                    }
                    $fabrikElts = $model->getValueFabrikByIds($idFabrik);
                    $fabrikValues = array();
                    foreach($fabrikElts as $elt)
                    {
                        $params = json_decode($elt['params']);
                        $groupParams = json_decode($elt['group_params']);
                        $isDate = ($elt['plugin'] == 'date');
                        $isDatabaseJoin = ($elt['plugin'] === 'databasejoin');
                        if($groupParams->repeat_group_button == 1 || $isDatabaseJoin)
                        {
                            $fabrikValues[$elt['id']] = $model->getFabrikValueRepeat($elt['group_id'], $elt['db_table_name'], $elt['name'], $fnumsArray, $params, $elt['plugin'], $groupParams->repeat_group_button == 1);
                        }
                        else
                        {
                            if($isDate)
                            {
                                $fabrikValues[$elt['id']] = $model->getFabrikValue($fnumsArray, $elt['db_table_name'], $elt['name'], $params->date_form_format);
                            }
                            else
                            {
                                $fabrikValues[$elt['id']] = $model->getFabrikValue($fnumsArray, $elt['db_table_name'], $elt['name']);
                            }
                        }
                        if($elt['plugin'] == "checkbox" || $elt['plugin'] == "dropdown" || $elt['plugin'] == "radiobutton")
                        {
                            foreach($fabrikValues[$elt['id']] as $fnum => $val)
                            {
                                if(($elt['plugin'] == "checkbox") || ($elt['plugin'] == "radiobutton"))
                                {
                                    $val = json_decode($val['val']);
                                }
                                else
                                {
                                    $val = explode(',', $val['val']);
                                }

                                foreach($val as $k => $v)
                                {
                                    $index = array_search(trim($v),$params->sub_options->sub_values);
                                    $val[$k] = $params->sub_options->sub_labels[$index];
                                }
                                $fabrikValues[$elt['id']][$fnum]['val'] = implode(", ", $val);
                            }
                        }
                        if($elt['plugin'] == "birthday")
                        {
                            foreach($fabrikValues[$elt['id']] as $fnum => $val)
                            {
                                $val = explode(',', $val['val']);
                                foreach($val as $k => $v)
                                {
                                    $val[$k] = date($params->details_date_format, strtotime($v));
                                }
                                $fabrikValues[$elt['id']][$fnum]['val'] = implode(",", $val);
                            }
                        }
                    }
                    foreach($fnumsArray as $fnum)
                    {
                        $preprocess = new TemplateProcessor(JPATH_BASE.$tmpl[0]['file']);
                        if(isset($fnumsInfos[$fnum]))
                        {
                            foreach($setupTags as $tag)
                            {
                                $lowerTag = strtolower($tag);
                                if(array_key_exists($lowerTag, $const))
                                {
                                    $preprocess->setValue($tag, $const[$lowerTag]);
                                }
                                else
                                {
                                    $preprocess->setValue($tag, @$fnumsInfos[$fnum][$lowerTag]);
                                }
                            }
                            foreach($idFabrik as $id)
                            {
                                if(isset($fabrikValues[$id][$fnum]))
                                {
                                    $preprocess->setValue($id, $fabrikValues[$id][$fnum]['val']);
                                }
                                else
                                {
                                    $preprocess->setValue($id, '');
                                }
                            }

                            $rand = rand(0, 1000000);
                            if(!file_exists(EMUNDUS_PATH_ABS.$fnumsInfos[$fnum]['applicant_id']))
                            {
                                mkdir(EMUNDUS_PATH_ABS.$fnumsInfos[$fnum]['applicant_id'], 0775);
                            }

                            $filename = str_replace(' ', '', $fnumsInfos[$fnum]['applicant_name']).$attachInfos['lbl']."-".md5($rand.time()).".docx";

                            $preprocess->saveAs(EMUNDUS_PATH_ABS.$fnumsInfos[$fnum]['applicant_id'].DS.$filename);

                            $upId = $model->addAttachement($fnum, $filename, $fnumsInfos[$fnum]['applicant_id'], $fnumsInfos[$fnum]['campaign_id'], $tmpl[0]['attachment_id'], $attachInfos['description']);

                            $res->files[] = array('filename' => $filename, 'upload' => $upId, 'url' => EMUNDUS_PATH_REL.$fnumsInfos[$fnum]['applicant_id'].'/', );
                        }
                        unset($preprocess);
                    }
                    echo json_encode($res);
                }
                catch(Exception $e)
                {
                    $res->status = false;
                    $res->msg = JText::_("AN_ERROR_OCURRED").':'. $e->getMessage();
                    echo json_encode($res);
                    exit();
                }
                break;
        }
        exit();

    }

    public function exportzipdoc()
    {
        $jinput = JFactory::getApplication()->input;
        $idFiles = explode(",", $jinput->getStrings('ids', ""));
        $model = $this->getModel('Files');
        $files = $model->getAttachmentsById($idFiles);

        $nom = date("Y-m-d").'_'.md5(rand(1000,9999).time()).'_x'.(count($files)-1).'.zip';
        $path = JPATH_BASE.DS.'tmp'.DS.$nom;

        if (extension_loaded('zip')) {
            $zip = new ZipArchive();

            if($zip->open($path, ZipArchive::CREATE) == TRUE)
            {
                foreach($files as $key => $file)
                {
                    $filename = EMUNDUS_PATH_ABS.$file['user_id'].DS.$file['filename'];
                    if(!$zip->addFile($filename, $file['filename']))
                    {
                        continue;
                    }
                }
                $zip->close();
            } else {
                die ("ERROR");
            }

        } else {
            require_once(JPATH_BASE.DS.'libraries'.DS.'pclzip-2-8-2'.DS.'pclzip.lib.php');
            $zip = new PclZip($path);

            foreach($files as $key => $file)
            {
                $user = JFactory::getUser($file['user_id']);
                $dir = $file['fnum'].'_'.$user->name;
                $filename = EMUNDUS_PATH_ABS.$file['user_id'].DS.$file['filename'];

                $zip->add($filename, PCLZIP_OPT_REMOVE_ALL_PATH, PCLZIP_OPT_ADD_PATH, $dir);

                if(!$zip->addFile($filename, $file['filename']))
                {
                    continue;
                }
            }
        }

        $mime_type = $this->get_mime_type($path);
        header('Content-type: application/'.$mime_type);
        header('Content-Disposition: inline; filename='.basename($path));
        header('Last-Modified: '.gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Cache-Control: pre-check=0, post-check=0, max-age=0');
        header('Pragma: anytextexeptno-cache', true);
        header('Cache-control: private');
        header('Expires: 0');
        ob_clean();
        flush();
        readfile($path);
        exit;
    }

    public function exportonedoc()
    {
        require_once JPATH_LIBRARIES.DS.'PHPWord'.DS.'src'.DS.'Autoloader.php';
        /*
        if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
            \PhpOffice\PhpWord\Autoloader::register();
            $rendererName = \PhpOffice\PhpWord\Settings::PDF_RENDERER_TCPDF;
            \PhpOffice\PhpWord\Settings::setPdfRenderer($rendererName, JPATH_LIBRARIES . DS . 'emundus' . DS . 'tcpdf');
        }
        */
        $jinput = JFactory::getApplication()->input;
        $idFiles = explode(",", $jinput->getStrings('ids', ""));
        $model = $this->getModel('Files');
        $files = $model->getAttachmentsById($idFiles);
        $nom = date("Y-m-d").'_'.md5(rand(1000,9999).time()).'_x'.(count($files)-1).'.pdf';
        $path = JPATH_BASE.DS.'tmp'.DS.$nom;
        $wordPHP = new PhpWord();
        $docs = array();
        foreach($files as $key => $file)
        {
            $filename = EMUNDUS_PATH_ABS.$file['user_id'].DS.$file['filename'];
            $tmpName = JPATH_BASE.DS.'tmp'.DS.$file['filename'];
            $document = $wordPHP->loadTemplate($filename);
            $document->saveAs($tmpName); // Save to temp file
            /*
            if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
                $wordPHP = \PhpOffice\PhpWord\IOFactory::load($tmpName); // Read the temp file
                $xmlWriter = \PhpOffice\PhpWord\IOFactory::createWriter($wordPHP, 'PDF');

			    $xmlWriter->save($tmpName.'.pdf');  // Save to PDF
            }
            */
            $docs[] = $tmpName.'.pdf';
            unlink($tmpName); // Delete the temp file
        }
        require_once(JPATH_LIBRARIES.DS.'emundus'.DS.'fpdi.php');
        $pdf = new ConcatPdf();
        $pdf->setFiles($docs);
        $pdf->concat();
        if(isset($docs))
        {
            foreach($docs as $fn)
            {
                unlink($fn);
            }
        }
        $pdf->Output($path, 'I');
        exit;
    }
}
