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
class EmundusonboardViewForm extends FabrikViewFormBase
{
    /**
     * Main setup routine for displaying the form/detail view
     * @param returnObject est l'objet retourné
     */
    public function display($tpl = null)
    {
        JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_fabrik/models');

        error_reporting(E_ALL ^ E_NOTICE);

        /**
         *   *Instanciation des variables du form
         */
        $jinput = JFactory::getApplication()->input;

        // Display the template
        $formid = $jinput->getString('formid', null);

        $form         = JModelLegacy::getInstance('Form', 'FabrikFEModel');
        $form->setId(intval($formid));
        $getParams		= $form->getParams();
        $getGroup		= $form->getGroups();



        $returnObject = new stdClass();

        $returnObject->id = $form->id;

        if ($getParams->get('show_page_heading') == 1) :
            $show_page_heading = new stdClass();
            $show_page_heading->class = 'componentheading a' . $getParams->get("pageclass_sfx");
            $show_page_heading->page_heading = $this->escape($getParams->get('page_heading'));
            $returnObject->show_page_heading =$show_page_heading;
        endif;

        if ($getParams->get('show-title') == 1) :
            $show_title = new stdClass();
            $show_title->class = "page-header";
            $title = explode('-', $form->getLabel());
            $show_title->titleraw = $form->form->label;
            $show_title->value = !empty($title[1])?JText::_(trim($title[1])):JText::_(trim($title[0]));
            $returnObject->show_title = $show_title;
        endif;

        if ($form->getIntro()) :
            $returnObject->intro = $form->getIntro();
            $returnObject->intro_raw = $form->form->intro;
        endif;

        if ($form->attribs) :
            $returnObject->attribs = $form->attribs;
        endif;

        if ($this->plugintop) :
            $returnObject->plugintop = $this->plugintop;
        endif;

        $Groups = new stdClass();

        JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_emundus_onboard/models');
        $formbuilder = JModelLegacy::getInstance('formbuilder', 'EmundusonboardModel');

        foreach ($getGroup as $group) :
            $this->group = $group;
            $GroupProperties = $group->getGroupProperties($group->getFormModel());
            $groupElement = $group->getMyElements();

            ${"group_".$GroupProperties->id} = new stdClass();

            $db    = FabrikWorker::getDbo(true);
            $query = $db->getQuery(true);

            $query
                ->select('ordering')
                ->from($db->quoteName('#__fabrik_formgroup'))
                ->where($db->quoteName('group_id') . ' = ' . $db->quote($GroupProperties->id));

            $db->setQuery($query);
            ${"group_".$GroupProperties->id}->ordering = $db->loadResult();

            ${"group_".$GroupProperties->id}->group_showLegend = $GroupProperties->title;
            ${"group_".$GroupProperties->id}->group_tag = $GroupProperties->name;
            ${"group_".$GroupProperties->id}->label_fr = $formbuilder->getTranslationFr($GroupProperties->name);
            ${"group_".$GroupProperties->id}->label_en = $formbuilder->getTranslationEn($GroupProperties->name);

            if ($GroupProperties->class) :
                ${"group_".$GroupProperties->id}->group_class = $GroupProperties->class;
            endif;
            if ($GroupProperties->id) :
                ${"group_".$GroupProperties->id}->group_id = $GroupProperties->id;
            endif;
            if ($GroupProperties->css) :
                ${"group_".$GroupProperties->id}->group_css = $GroupProperties->css;
            endif;
            if ($GroupProperties->intro) :
                ${"group_".$GroupProperties->id}->group_intro = $GroupProperties->intro;
            endif;

            ${"group_".$GroupProperties->id}->repeat_group = false;
            if ($GroupProperties->canRepeat == 1) {
                ${"group_".$GroupProperties->id}->repeat_group = true;
            }

            $elements = new stdClass();

            foreach ($groupElement as $element) :
                $this->element = $element;
                $d_element = $this->element;
                $o_element = $d_element->element;
                $el_parmas = json_decode($o_element->params);
                $content_element = $element->preRender('0','1','bootstrap');
                ${"element".$o_element->id} = new stdClass();

                $labelsAbove = $content_element->labels;

                ${"element".$o_element->id}->id = $o_element->id;
                ${"element".$o_element->id}->group_id = $GroupProperties->id;
                ${"element".$o_element->id}->hidden = $content_element->hidden;
                ${"element".$o_element->id}->labelsAbove=$labelsAbove;
                ${"element".$o_element->id}->plugin=$o_element->plugin;
                if($el_parmas->validations->plugin != null){
                    if(is_array($el_parmas->validations->plugin)) {
                        $FRequire = in_array('notempty', $el_parmas->validations->plugin);
                    } elseif ($el_parmas->validations->plugin == 'notempty') {
                        $FRequire = true;
                    }
                } else {
                    $FRequire = false;
                }


                ${"element".$o_element->id}->FRequire=$FRequire;
                ${"element".$o_element->id}->params=$el_parmas;
                ${"element".$o_element->id}->label_tag='ELEMENT_' . $GroupProperties->id . '_' . $o_element->id;
                ${"element".$o_element->id}->label_fr = $formbuilder->getTranslationFr(${"element".$o_element->id}->label_tag);
                ${"element".$o_element->id}->label_en = $formbuilder->getTranslationEn(${"element".$o_element->id}->label_tag);
                ${"element".$o_element->id}->labelToFind=$element->label;
                ${"element".$o_element->id}->publish=$element->isPublished();



                if ($labelsAbove == 2)
                {
                    if ($el_parmas->tipLocation == 'above') :
                        ${"element".$o_element->id}->tipAbove=$content_element->tipAbove;
                    endif;
                    if ($content_element->element) :
                        ${"element".$o_element->id}->element=$content_element->element;
                    endif;
                    if ($content_element->error) :
                        ${"element".$o_element->id}->error=$content_element->error;
                        ${"element".$o_element->id}->errorClass=$el_parmas->class;
                    endif;
                    if ($el_parmas->tipLocation == 'side') :
                        ${"element".$o_element->id}->tipSide=$content_element->tipSide;
                    endif;
                    if ($el_parmas->tipLocation == 'below') :
                        ${"element".$o_element->id}->tipBelow=$content_element->tipBelow;
                    endif;
                }else
                {
                    ${"element".$o_element->id}->label=$content_element->label;

                    if ($el_parmas->tipLocation == 'above') :
                        ${"element".$o_element->id}->tipAbove=$content_element->tipAbove;
                    endif;
                    if ($content_element->element) :
                        ${"element".$o_element->id}->element=$content_element->element;
                    endif;
                    if ($content_element->error) :
                        ${"element".$o_element->id}->error=$content_element->error;
                        ${"element".$o_element->id}->errorClass=$el_parmas->class;
                    endif;
                    if ($el_parmas->tipLocation == 'side') :
                        ${"element".$o_element->id}->tipSide=$content_element->tipSide;
                    endif;
                    if ($el_parmas->tipLocation == 'below') :
                        ${"element".$o_element->id}->tipBelow=$content_element->tipBelow;
                    endif;
                }

                $elements-> {"element".$o_element->id} = ${"element".$o_element->id};
            endforeach;
            ${"group_".$GroupProperties->id}-> elements = $elements;


            if ($GroupProperties->outro) :
                ${"group_".$GroupProperties->id}->group_outro = $GroupProperties->outro;
            endif;

            if(${"group_".$GroupProperties->id}->group_css !== ";display:none;"){
                $Groups->{"group_".$GroupProperties->id} = ${"group_".$GroupProperties->id};
            }
        endforeach;

        $returnObject->Groups=$Groups;

        if ($this->pluginbottom) :
            $returnObject->pluginbottom = $this->pluginbottom;
        endif;

        /**
         * @param returnObject
         * *Contient toute les informations
         */
        // var_dump($returnObject).die();
        echo json_encode($returnObject);
    }
}
