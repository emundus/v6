<?php
/**
 * HTML Form view class
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2016  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\Utilities\ArrayHelper;


jimport('joomla.application.component.view');
jimport('joomla.application.component.model');
require_once JPATH_SITE . '/components/com_fabrik/views/form/view.base.php';

/**
 * HTML Form view class
 *
 * @package  Joomla
 * @subpackage  Fabrik
 * @since       3.0.6
 */
class EmundusViewForm extends FabrikViewFormBase
{
    /**
     * Main setup routine for displaying the form/detail view
     * @since 0.1.0
     */
    public function display($tpl = null)
    {
        try {
            JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_fabrik/models');
            JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_emundus/models');

            error_reporting(E_ALL ^ E_NOTICE);

            /**
             *   *Instanciation des variables du form
             */
            $jinput = JFactory::getApplication()->input;

            // Display the template
            $formid = $jinput->getString('formid', null);

            $formbuilder = JModelLegacy::getInstance('formbuilder', 'EmundusModel');
            $form = JModelLegacy::getInstance('Form', 'FabrikFEModel');
            $form->setId(intval($formid));
            $getParams = $form->getParams();
            $getGroup = $form->getGroups();

            // Prepare languages
            $path_to_file = basename(__FILE__) . '/../language/overrides/';
            $path_to_files = array();
            $Content_Folder = array();

            $languages = JLanguageHelper::getLanguages();
            foreach ($languages as $language) {
                $path_to_files[$language->sef] = $path_to_file . $language->lang_code . '.override.ini';
                try {
                    if (file_exists($path_to_files[$language->sef])) {
                        $Content_Folder[$language->sef] = file_get_contents($path_to_files[$language->sef]);
                    }
                } catch (Exception $e) {
                    JLog::add('component/com_emundus/view/vue_jsonclean | Cannot find '.$language->sef.'language override file : ', JLog::ERROR, 'com_emundus');
                    continue;
                }
            }

            $returnObject = new stdClass();

            $returnObject->id = $form->id;

            $db = FabrikWorker::getDbo(true);
            $query = $db->getQuery(true);

            $query->select('id')
                ->from($db->quoteName('#__menu'))
                ->where($db->quoteName('link') . ' = ' . $db->quote('index.php?option=com_fabrik&view=form&formid=' . $form->id));
            $db->setQuery($query);
            $returnObject->menu_id = $db->loadResult();

            if ($getParams->get('show_page_heading') == 1) :
                $show_page_heading = new stdClass();
                $show_page_heading->class = 'componentheading a' . $getParams->get("pageclass_sfx");
                $show_page_heading->page_heading = $this->escape($getParams->get('page_heading'));
                $returnObject->show_page_heading = $show_page_heading;
            endif;

            if ($getParams->get('show-title') == 1) :
                $show_title = new stdClass();
                $show_title->class = "page-header";
                $title = explode(' - ', $form->getLabel());
                $show_title->titleraw = $form->form->label;
                $show_title->value = $form->getLabel();
                $show_title->label = new stdClass;
                foreach ($languages as $language) {
                    $show_title->label->{$language->sef} = $formbuilder->getTranslation($form->form->label,$language->lang_code);
                }
                $returnObject->show_title = $show_title;
            else :
                $show_title = new stdClass();
                $show_title->titleraw = '';
                $show_title->value = '';
                $show_title->label = new stdClass;
                foreach ($languages as $language) {
                    $show_title->label->{$language->sef} = '';
                }
                $returnObject->show_title = $show_title;
            endif;

            if ($form->getIntro()) :
                $returnObject->intro_value = $form->getIntro();
                $returnObject->intro = new stdClass;
                foreach ($languages as $language) {
                    $returnObject->intro->{$language->sef} = $formbuilder->getTranslation($form->form->intro,$language->lang_code);
                }
                $returnObject->intro_raw = strip_tags($form->form->intro);
            endif;

            if ($form->attribs) :
                $returnObject->attribs = $form->attribs;
            endif;

            if ($this->plugintop) :
                $returnObject->plugintop = $this->plugintop;
            endif;

			$db_table = $form->getTable();

            $Groups = new stdClass();

            foreach ($getGroup as $group) :
                $this->group = $group;
                $GroupProperties = $group->getGroupProperties($group->getFormModel());
                $groupElement = $group->getMyElements();

                ${"group_" . $GroupProperties->id} = new stdClass();

                $db = FabrikWorker::getDbo(true);
                $query = $db->getQuery(true);

                $query
                    ->select('fg.label,ffg.ordering,fg.params')
                    ->from($db->quoteName('#__fabrik_formgroup','ffg'))
                    ->leftJoin($db->quoteName('#__fabrik_groups','fg').' ON '.$db->quoteName('fg.id').' = '.$db->quoteName('ffg.group_id'))
                    ->where($db->quoteName('ffg.group_id') . ' = ' . $db->quote($GroupProperties->id));

                $db->setQuery($query);
                $group_infos = $db->loadObject();
                ${"group_" . $GroupProperties->id}->params = json_decode($group_infos->params);
                ${"group_" . $GroupProperties->id}->ordering = (int)$group_infos->ordering;
                ${"group_" . $GroupProperties->id}->group_showLegend = $GroupProperties->title;
                ${"group_" . $GroupProperties->id}->group_tag = $group_infos->label != '' ? $group_infos->label : strtoupper($formbuilder->replaceAccents($GroupProperties->name));
                ${"group_" . $GroupProperties->id}->label = new stdClass;
                foreach ($languages as $language) {
                    ${"group_" . $GroupProperties->id}->label->{$language->sef} = $formbuilder->getTranslation($group_infos->label,$language->lang_code);
                }

                if ($GroupProperties->class) :
                    ${"group_" . $GroupProperties->id}->group_class = $GroupProperties->class;
                endif;
                if ($GroupProperties->id) :
                    ${"group_" . $GroupProperties->id}->group_id = $GroupProperties->id;
                endif;
                if ($GroupProperties->css) :
                    ${"group_" . $GroupProperties->id}->group_css = $GroupProperties->css;
                endif;
                if ($GroupProperties->intro) :
                    ${"group_" . $GroupProperties->id}->group_intro = $GroupProperties->intro;
                endif;

                ${"group_" . $GroupProperties->id}->repeat_group = false;
                if ($GroupProperties->canRepeat == 1) {
                    ${"group_" . $GroupProperties->id}->repeat_group = true;
                }

                $elements = new stdClass();

                if(sizeof($groupElement) > 0) {
                    $display_group = false;
                } else {
                    $display_group = true;
                }

                foreach ($groupElement as $element) :
                    $this->element = $element;
                    $d_element = $this->element;
                    $o_element = $d_element->element;
                    if(in_array($o_element->name,['id','user','time_date','fnum','date_time'])){
                        ${"group_" . $GroupProperties->id}->cannot_delete = true;
                        if(!$display_group) {
                            continue;
                        }
                    } else {
                        $display_group = true;
                    }
                    if($o_element->plugin != 'emundusreferent') {
                        //if($o_element->plugin != 'calc') {
                        $el_parmas = json_decode($o_element->params);
						if($o_element->plugin != 'calc') {
							$content_element = $element->preRender('0', '1', '_emundus');
						} else {
							// We build the calc element because we don't want to execute the preRender function
							$content_element = new stdClass();
							$content_element->startRow = 0;
							$content_element->endRow = 0;
							$content_element->error = '';
							$content_element->plugin = 'calc';
							$content_element->hidden = !($o_element->hidden == "0");
							$content_element->id = $db_table->db_table_name.'___'.$o_element->name;
							$content_element->className = 'fb_el_'.$db_table->db_table_name.'___'.$o_element->name;
							$content_element->element = '<span class="fabrikinput fabrikElementReadOnly em-w-100 em-bg-neutral-200" style="display:inline-block;background-color: #B2B7C7; border: unset" name="'.$content_element->id.'" id="'.$content_element->id.'"></span>';
							$content_element->label_raw = $o_element->label;
							$content_element->label = '<label for="'.$content_element->id.'" class="fabrikLabel control-label" >'.$o_element->label.'</label>';
							$content_element->errorTag = '<span class="fabrikErrorMessage"></span>';
							$content_element->element_ro = '';
							$content_element->value = '';
							$content_element->containerClass = 'fabrikElementContainer plg-calc fb_el_'.$content_element->id;
							$content_element->element_raw = '';
							$content_element->dataEmpty = false;
							$content_element->labels = 1;
							$content_element->dlabels = 1;
							$content_element->tipAbove = '';
							$content_element->tipBelow = '';
							$content_element->tipSide = '';
						}
                        ${"element" . $o_element->id} = new stdClass();

                        $labelsAbove = $content_element->labels;
                        ${"element" . $o_element->id}->id = $o_element->id;
                        ${"element" . $o_element->id}->name = $o_element->name;
                        ${"element" . $o_element->id}->group_id = $GroupProperties->id;
                        ${"element" . $o_element->id}->hidden = $content_element->hidden;
                        ${"element" . $o_element->id}->default = $o_element->default;
                        ${"element" . $o_element->id}->labelsAbove = $labelsAbove;
                        ${"element" . $o_element->id}->plugin = $o_element->plugin;
                        if ($el_parmas->validations->plugin != null) {
                            if (is_array($el_parmas->validations->plugin)) {
                                $FRequire = in_array('notempty', $el_parmas->validations->plugin);
                            } elseif ($el_parmas->validations->plugin == 'notempty') {
                                $FRequire = true;
                            } else {
                                $FRequire = false;
                            }
                        } else {
                            $FRequire = false;
                        }


                        ${"element" . $o_element->id}->FRequire = $FRequire;
                        ${"element" . $o_element->id}->params = $el_parmas;
                        ${"element" . $o_element->id}->label_tag = $o_element->label;
                        ${"element" . $o_element->id}->label = new stdClass;
                        foreach ($languages as $language) {
                            ${"element" . $o_element->id}->label->{$language->sef} = $formbuilder->getTranslation(${"element" . $o_element->id}->label_tag,$language->lang_code);
                        }
                        ${"element" . $o_element->id}->labelToFind = $element->label;
                        ${"element" . $o_element->id}->publish = $element->isPublished();


                        if ($labelsAbove == 2) {
                            if ($el_parmas->tipLocation == 'above') :
                                ${"element" . $o_element->id}->tipAbove = $content_element->tipAbove;
                            endif;
                            if ($content_element->element) :
                                if ($o_element->plugin == 'date') {
                                    ${"element" . $o_element->id}->element = '<input data-v-8d3bb2fa="" class="form-control" type="date">';
                                } else {
                                    ${"element" . $o_element->id}->element = $content_element->element;
                                }
                            endif;
                            if ($content_element->error) :
                                ${"element" . $o_element->id}->error = $content_element->error;
                                ${"element" . $o_element->id}->errorClass = $el_parmas->class;
                            endif;
                            if ($el_parmas->tipLocation == 'side') :
                                ${"element" . $o_element->id}->tipSide = $content_element->tipSide;
                            endif;
                            if ($el_parmas->tipLocation == 'below') :
                                ${"element" . $o_element->id}->tipBelow = $content_element->tipBelow;
                            endif;
                        } else {
                            ${"element" . $o_element->id}->label_value = $content_element->label;

                            if ($el_parmas->tipLocation == 'above') :
                                ${"element" . $o_element->id}->tipAbove = $content_element->tipAbove;
                            endif;
                            if ($content_element->element) :
                                if ($o_element->plugin == 'date') {
                                    ${"element" . $o_element->id}->element = '<input data-v-8d3bb2fa="" class="form-control" type="date">';
                                } else {
                                    ${"element" . $o_element->id}->element = $content_element->element;
                                }
                            endif;
                            if ($content_element->error) :
                                ${"element" . $o_element->id}->error = $content_element->error;
                                ${"element" . $o_element->id}->errorClass = $el_parmas->class;
                            endif;
                            if ($el_parmas->tipLocation == 'side') :
                                ${"element" . $o_element->id}->tipSide = $content_element->tipSide;
                            endif;
                            if ($el_parmas->tipLocation == 'below') :
                                ${"element" . $o_element->id}->tipBelow = $content_element->tipBelow;
                            endif;
                        }

                        $elements->{"element" . $o_element->id} = ${"element" . $o_element->id};
                        //}
                    }
                endforeach;
                ${"group_" . $GroupProperties->id}->elements = $elements;


                if ($GroupProperties->outro) :
                    ${"group_" . $GroupProperties->id}->group_outro = $GroupProperties->outro;
                endif;

                if (${"group_" . $GroupProperties->id}->group_css === ";display:none;") {
                    ${"group_" . $GroupProperties->id}->hidden_group = -1;
                    ${"group_" . $GroupProperties->id}->group_css = '';
                } else {
                    ${"group_" . $GroupProperties->id}->hidden_group = 1;
                }

                if($display_group) {
                    $Groups->{"group_" . $GroupProperties->id} = ${"group_" . $GroupProperties->id};
                }
            endforeach;

            $returnObject->Groups = $Groups;

            if ($this->pluginbottom) :
                $returnObject->pluginbottom = $this->pluginbottom;
            endif;

            echo json_encode($returnObject);
        } catch(Exception $e){
            JLog::add('component/com_emundus/views/view.vue_jsonclean | Cannot getting the form datas : ' . preg_replace("/[\r\n]/"," ",$query->__toString().' -> '.$e->getMessage()), JLog::ERROR, 'com_emundus');
            return 0;
        }
    }
}
