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
			$show_title-> value= !empty($title[1])?JText::_(trim($title[1])):JText::_(trim($title[0]));
			$returnObject->show_title = $show_title;
        endif;

        /**
         * ? importer intro ?
         */
            $returnObject->intro = $form->getIntro();
            $returnObject->intro_raw = $form->form->intro;
        /**
         * ? importer intro ?
         */
        if ($form->attribs) :
            $returnObject->attribs = $form->attribs;
        endif;

        if ($this->plugintop) :
            $returnObject->plugintop = $this->plugintop;
        endif;
        $Groups = new stdClass();

        foreach ($getGroup as $group) :
            $this->group = $group;
            $GroupProperties = $group->getGroupProperties($group->getFormModel());
            $groupElement = $group->getMyElements();
            ${"group_".$GroupProperties->id} = new stdClass();

            if ($GroupProperties->class) :
                ${"group_".$GroupProperties->id}->group_class = $GroupProperties->class;
            endif;
            if ($GroupProperties->id) :
                ${"group_".$GroupProperties->id}->group_id = $GroupProperties->id;
            endif;
            if ($GroupProperties->css) :
                ${"group_".$GroupProperties->id}->group_css = $GroupProperties->css;
            endif;
            if ($GroupProperties->showLegend) :
                ${"group_".$GroupProperties->id}->group_showLegend = $GroupProperties->title;
            endif;
            if ($GroupProperties->intro) :
                ${"group_".$GroupProperties->id}->group_intro = $GroupProperties->intro;
            endif;

            if ($GroupProperties->tmpl == 'default_repeatgroup') {
                // * something soon
            }elseif ($GroupProperties->tmpl == 'repeatgroup_table') {
                // * something soon
            }else {

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
                    ${"element".$o_element->id}->hidden = $content_element->hidden;
                    ${"element".$o_element->id}->labelsAbove=$labelsAbove;
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

                    if($o_element->published !== '0'){
                        $elements-> {"element".$o_element->id} = ${"element".$o_element->id};
                    }
                endforeach;
                ${"group_".$GroupProperties->id}-> elements = $elements;
            }


            if ($GroupProperties->outro) :
                ${"group_".$GroupProperties->id}->group_outro = $GroupProperties->outro;
            endif;

            if(!empty((array)${"group_".$GroupProperties->id}->elements) && ${"group_".$GroupProperties->id}->group_css !== ";display:none;"){
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
