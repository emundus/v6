<?php
/**
 * @package    eMundus
 * @subpackage Components
 * @link       http://www.emundus.fr
 * @license    GNU/GPL
 * @author     Benjamin Rivalland
 */

// no direct access

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

use Joomla\CMS\Factory;

/**
 * HTML View class for the Emundus Component
 *
 * @package    Emundus
 */
class EmundusViewEvaluation extends JViewLegacy
{
    private $app;
    var $_db = null;
    private $_user;

    protected $itemId;
    protected $actions;
    protected $items;
    protected $display;
    protected $cfnum;
    protected $code;
    protected $fnum_assoc;
    protected $filters;
    protected $form_url_edit;
    protected $datas;
    protected $colsSup;
    protected $accessObj;
    protected $pageNavigation;
    protected $users;
    protected $formid;
    protected $lists;
    protected $pagination;
    protected $use_module_for_filters = false;
    protected bool $open_file_in_modal = false;
    protected string $modal_ratio = '66/33';

    public function __construct($config = array())
    {
        require_once(JPATH_ROOT . '/components/com_emundus/helpers/list.php');
        require_once(JPATH_ROOT . '/components/com_emundus/helpers/access.php');
        require_once(JPATH_ROOT . '/components/com_emundus/helpers/emails.php');
        require_once(JPATH_ROOT . '/components/com_emundus/helpers/export.php');
        require_once(JPATH_ROOT . '/components/com_emundus/helpers/filters.php');
        require_once(JPATH_ROOT . '/components/com_emundus/models/users.php');
        require_once(JPATH_ROOT . '/components/com_emundus/models/files.php');

        $this->app   = Factory::getApplication();
        $this->_user = Factory::getUser();
        $this->_db = JFactory::getDBO();

        $menu                         = $this->app->getMenu();
        if (!empty($menu)) {
            $current_menu                 = $menu->getActive();
            if (!empty($current_menu)) {
                $menu_params                  = $menu->getParams($current_menu->id);
                $this->use_module_for_filters = boolval($menu_params->get('em_use_module_for_filters', 0));
                $this->open_file_in_modal     = boolval($menu_params->get('em_open_file_in_modal', 0));

                if ($this->open_file_in_modal) {
                    $this->modal_ratio = $menu_params->get('em_modal_ratio', '66/33');
                }
            }
        }

        parent::__construct($config);
    }

    public function display($tpl = null)
    {
        if (!EmundusHelperAccess::asEvaluatorAccessLevel($this->_user->id)) {
            die(JText::_('COM_EMUNDUS_ACCESS_RESTRICTED_ACCESS'));
        }

        $jinput       = $this->app->input;
        $this->itemId = $jinput->getInt('Itemid', null);

        $menu         = $this->app->getMenu();
        $current_menu = $menu->getActive();
        $menu_params  = $menu->getParams($current_menu->id);

        $columnSupl = explode(',', $menu_params->get('em_other_columns'));
        $layout     = $jinput->getString('layout', 0);

        $m_files = new EmundusModelFiles();

        switch ($layout) {
            case 'menuactions':
                $this->display = $jinput->getString('display', 'none');

                $items   = EmundusHelperFiles::getMenuList($menu_params);
                $actions = $m_files->getAllActions();

                $menuActions = array();
                foreach ($items as $item) {
                    if (!empty($item->note)) {
                        $note = explode('|', $item->note);
                        if ($actions[$note[0]][$note[1]] == 1) {
                            $actions[$note[0]]['multi'] = $note[2];
                            $actions[$note[0]]['grud']  = $note[1];
                            $item->action               = $actions[$note[0]];
                            $menuActions[]              = $item;
                        }
                    }
                    else {
                        $menuActions[] = $item;
                    }
                }

                $this->items = $menuActions;
                break;

            case 'filters':
                if (!$this->use_module_for_filters) {
                    $m_evaluation = $this->getModel('Evaluation');
                    $m_user       = new EmundusModelUsers();

                    $m_evaluation->code = $m_user->getUserGroupsProgrammeAssoc($this->_user->id);

                    // get all fnums manually associated to user
                    $groups                   = $m_user->getUserGroups($this->_user->id, 'Column');
                    $fnum_assoc_to_groups     = $m_user->getApplicationsAssocToGroups($groups);
                    $fnum_assoc               = $m_user->getApplicantsAssoc($this->_user->id);
                    $m_evaluation->fnum_assoc = array_merge($fnum_assoc_to_groups, $fnum_assoc);
                    $this->code               = $m_evaluation->code;
                    $this->fnum_assoc         = $m_evaluation->fnum_assoc;

                    // reset filter
                    $this->filters = EmundusHelperFiles::resetFilter();
                }
                break;

            default :
                $this->cfnum = $jinput->getString('cfnum', null);

                $params                        = JComponentHelper::getParams('com_emundus');
                $evaluators_can_see_other_eval = $params->get('evaluators_can_see_other_eval', 0);

                $m_evaluation = $this->getModel('Evaluation');
                $h_files      = new EmundusHelperFiles();
                $m_files      = new EmundusModelFiles();
                $m_user       = new EmundusModelUsers();

                $m_evaluation->code = $m_user->getUserGroupsProgrammeAssoc($this->_user->id);

                // get all fnums manually associated to user
                $groups                   = $m_user->getUserGroups($this->_user->id, 'Column');
                $fnum_assoc_to_groups     = $m_user->getApplicationsAssocToGroups($groups);
                $fnum_assoc               = $m_user->getApplicantsAssoc($this->_user->id);
                $m_evaluation->fnum_assoc = array_merge($fnum_assoc_to_groups, $fnum_assoc);
                $this->code               = $m_evaluation->code;
                $this->fnum_assoc         = $m_evaluation->fnum_assoc;

                // reset filter
                $this->filters = EmundusHelperFiles::resetFilter();

                // Do not display photos unless specified in params
                $displayPhoto = false;

                if (!empty($m_evaluation->fnum_assoc) || !empty($m_evaluation->code)) {
                    // get applications files
                    $this->users = $m_evaluation->getUsers($this->cfnum);
                } else {
                    $this->users = array();
                }

                // Get elements from model and proccess them to get an easy to use array containing the element type
                $elements = $m_evaluation->getElementsVar();
                if (!empty($elements)) {
                    foreach ($elements as $elt) {
                        $elt_name          = $elt->tab_name . "___" . $elt->element_name;
                        $eltarr[$elt_name] = [
                            "plugin"    => $elt->element_plugin,
                            "tab_name"  => $elt->tab_name,
                            "params"    => $elt->element_attribs,
                            "fabrik_id" => $elt->id
                        ];
                    }
                }

                if (isset($eltarr)) {
                    $elements = $eltarr;
                }

                // Columns
                $defaultElements                    = $this->get('DefaultElements');
                $this->datas                        = array(array('check' => '#', 'name' => JText::_('COM_EMUNDUS_FILES_APPLICATION_FILES'), 'c.status' => JText::_('COM_EMUNDUS_STATUS')));
                $fl                                 = array();
                $fl['jos_emundus_evaluations.user'] = JText::_('COM_EMUNDUS_EVALUATION_EVALUATOR');
                // Get eval crieterion
                if (!empty($defaultElements)) {
                    foreach ($defaultElements as $key => $elt) {
                        $fl[$elt->tab_name . '.' . $elt->element_name] = $elt->element_label;
                    }
                }

                // merge eval criterion on application files
                $this->datas[0] = array_merge($this->datas[0], $fl);

                $fnumArray = array();

                if (!empty($this->users)) {

                    $taggedFile = array();
                    foreach ($columnSupl as $col) {
                        $col = explode('.', $col);
                        switch ($col[0]) {
                            case 'evaluators':
                                $this->datas[0]['EVALUATORS'] = JText::_('COM_EMUNDUS_EVALUATION_EVALUATORS');
                                $this->colsSup['evaluators']  = $h_files->createEvaluatorList($col[1], $m_evaluation);
                                break;
                            case 'overall':
                                $this->datas[0]['overall'] = JText::_('COM_EMUNDUS_EVALUATIONS_OVERALL');
                                $this->colsSup['overall']  = array();
                                break;
                            case 'tags':
                                $taggedFile                   = $m_evaluation->getTaggedFile();
                                $this->datas[0]['eta.id_tag'] = JText::_('COM_EMUNDUS_TAGS');
                                $this->colsSup['id_tag']      = array();
                                break;
                            case 'access':
                                $this->datas[0]['access'] = JText::_('COM_EMUNDUS_ASSOCIATED_TO');
                                $this->colsSup['access']  = array();
                                break;
                            case 'photos':
                                $displayPhoto = true;
                                break;
                            case 'module':
                                // Get every module without a positon.
                                $mod_emundus_custom = array();
                                foreach (JModuleHelper::getModules('') as $module) {
                                    if ($module->module == 'mod_emundus_custom' && ($module->menuid == 0 || $module->menuid == $jinput->get('Itemid', null))) {
                                        $mod_emundus_custom[$module->title] = $module->content;
                                        $this->datas[0][$module->title]     = JText::_($module->title);
                                        $this->colsSup[$module->title]      = array();
                                    }
                                }
                                break;
                            default:
                                break;
                        }
                    }

                    $i = 0;
                    foreach ($this->users as $user) {
                        $usObj       = new stdClass();
                        $usObj->val  = 'X';
                        $fnumArray[] = $user['fnum'];
                        // get evaluation form ID

                        $this->formid = $m_evaluation->getEvaluationFormByProgramme($user['code']);

                        $form_url_view       = 'index.php?option=com_fabrik&c=form&view=details&formid=' . $this->formid . '&tmpl=component&iframe=1&rowid=';
                        $this->form_url_edit = 'index.php?option=com_fabrik&c=form&view=form&formid=' . $this->formid . '&tmpl=component&iframe=1&rowid=';
                        $line                = array('check' => $usObj);

                        if (array_key_exists($user['fnum'], $taggedFile)) {
                            $class        = $taggedFile[$user['fnum']]['class'];
                            $usObj->class = $taggedFile[$user['fnum']]['class'];
                        }
                        else {
                            $class        = null;
                            $usObj->class = null;
                        }

                        foreach ($user as $key => $value) {
                            $userObj = new stdClass();

                            if ($key == 'fnum') {

                                $userObj->val   = $value;
                                $userObj->class = $class;
                                $userObj->type  = 'fnum';
                                if ($displayPhoto) {
                                    $userObj->photo = $h_files->getPhotos($value);
                                }
                                else {
                                    $userObj->photo = "";
                                }
                                $userObj->user       = JFactory::getUser((int) substr($value, -7));
                                $userObj->user->name = $user['name'];
                                $line['fnum']        = $userObj;

                            }
                            elseif ($key == 'name' || $key == 'status_class' || $key == 'step' || $key == 'code') {
                                continue;
                            }
                            elseif ($key == 'evaluator') {

                                if ($this->formid > 0 && !empty($value)) {

                                    if ($evaluators_can_see_other_eval || EmundusHelperAccess::asAccessAction(5, 'r', $this->_user->id)) {
                                        $link_view = '<a href="' . $form_url_view . $user['evaluation_id'] . '" target="_blank" data-remote="' . $form_url_view . $user['evaluation_id'] . '" id="em_form_eval_' . $i . '-' . $user['evaluation_id'] . '"><span class="glyphicon icon-eye-open" title="' . JText::_('COM_EMUNDUS_DETAILS') . '">  </span></a>';
                                    }

                                    if (EmundusHelperAccess::asAccessAction(5, 'u', $this->_user->id)) {
                                        $link_edit = '<a href="' . $this->form_url_edit . $user['evaluation_id'] . '" target="_blank"><span class="glyphicon icon-edit" title="' . JText::_('COM_EMUNDUS_ACTIONS_EDIT') . '"> </span></a>';
                                    }

                                    $userObj->val = @$link_view . ' ' . @$link_edit . ' ' . $value;

                                }
                                else {
                                    $userObj->val = $value;
                                }

                                $userObj->type     = 'html';
                                $line['evaluator'] = $userObj;

                            }
                            elseif (isset($elements) && in_array($key, array_keys($elements))) {

                                $userObj->val          = $value;
                                $userObj->type         = $elements[$key]['plugin'];
                                $userObj->status_class = $user['status_class'];
                                $userObj->id           = $elements[$key]['fabrik_id'];
                                $userObj->params       = $elements[$key]['params'];
                                $line[$key]            = $userObj;

                                // Radiobuttons are a strange beast, we need to get all of the values
                                if ($userObj->type == 'radiobutton') {
                                    $params         = json_decode($userObj->params);
                                    $userObj->radio = array_combine($params->sub_options->sub_labels, $params->sub_options->sub_values);
                                }

                            }
                            else {
                                $userObj->val          = $value;
                                $userObj->type         = 'text';
                                $userObj->status_class = $user['status_class'];
                                $line[$key]            = $userObj;
                            }
                        }

                        if (!empty($this->colsSup) && is_array($this->colsSup)) {
                            foreach ($this->colsSup as $key => $obj) {

                                $userObj = new stdClass();
                                if (!is_null($obj)) {

                                    if (array_key_exists($user['fnum'], $obj)) {
                                        $userObj->val                     = $obj[$user['fnum']];
                                        $userObj->type                    = 'html';
                                        $userObj->fnum                    = $user['fnum'];
                                        $line[JText::_(strtoupper($key))] = $userObj;
                                    }
                                    else {
                                        $userObj->val  = '';
                                        $userObj->type = 'html';
                                        $line[$key]    = $userObj;
                                    }
                                }
                                elseif (!empty($mod_emundus_custom) && array_key_exists($key, $mod_emundus_custom)) {
                                    $line[$key] = "";
                                }
                            }
                        }
                        $this->datas[$line['fnum']->val . '-' . $i] = $line;
                        $i++;
                    }

                    if (isset($this->colsSup['overall'])) {
                        $this->colsSup['overall'] = $m_evaluation->getEvaluationAverageByFnum($fnumArray);
                    }

                    if (isset($this->colsSup['id_tag'])) {
                        $tags                    = $m_files->getTagsByFnum($fnumArray);
                        $this->colsSup['id_tag'] = EmundusHelperFiles::createTagsList($tags);
                    }

                    if (isset($this->colsSup['access'])) {
                        $this->accessObj = $m_files->getAccessorByFnums($fnumArray);
                    }

                    if (!empty($mod_emundus_custom)) {
                        foreach ($mod_emundus_custom as $key => $module) {
                            if (isset($this->colsSup[$key])) {
                                $this->colsSup[$key] = $h_files->createHTMLList($module, $fnumArray);
                            }
                        }
                    }

                }
                else {
                    $this->datas = JText::_('COM_EMUNDUS_NO_RESULT');
                }

                /* Get the values from the state object that were inserted in the model's construct function */
                $this->lists['order_dir'] = $this->app->getSession()->get('filter_order_Dir');
                $this->lists['order']     = $this->app->getSession()->get('filter_order');
                $this->pagination         = $this->get('Pagination');
                $this->pageNavigation     = $this->get('PageNavigation');
                break;
        }

        parent::display($tpl);
    }

}


