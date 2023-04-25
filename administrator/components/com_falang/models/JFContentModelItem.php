<?php
/**
 * @package     Falang for Joomla!
 * @author      Stéphane Bouey <stephane.bouey@faboba.com> - http://www.faboba.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright   Copyright (C) 2010-2017. Faboba.com All rights reserved.
 */

//namespace Joomla\Falang\Administrator\Model;

// No direct access to this file
defined('_JEXEC') or die;

//include_once(JPATH_ADMINISTRATOR."/components/com_content/Model/ArticleModel.php");
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\FormFactoryInterface;

use Joomla\Component\Content\Administrator\Model\ArticleModel;
use Joomla\Utilities\ArrayHelper;

class JFTempContentModelItem extends ArticleModel {
    /**
     * Overload Method to get a form object - we MUST NOT use JPATH_COMPONENT
     *
     * @param   string   $name     The name of the form.
     * @param   string   $source   The form source. Can be XML string if file flag is set to false.
     * @param   array    $options  Optional array of options for the form creation.
     * @param   boolean  $clear    Optional argument to force load a new form.
     * @param   string   $xpath    An optional xpath to search for the fields.
     *
     * @return  mixed  JForm object on success, False on error.
     *
     * @see     JForm
     * @since   11.1
     */
    protected function loadForm($name, $source = null, $options = array(), $clear = false, $xpath = false)
    {
        // Handle the optional arguments.
        $options['control']	= ArrayHelper::getValue($options, 'control', false);

        // Create a signature hash.
        $hash = md5($source.serialize($options));

        // Check if we can use a previously loaded form.
        if (isset($this->_forms[$hash]) && !$clear) {
            return $this->_forms[$hash];
        }

        // Get the form.
        if (strpos($name, "com_")===0){
            if (strpos($name , ".")>0){
                $component = substr($name, 0, strpos($name , "."));
            }
            else {
                $component = $name;
            }
            $componentpath = JPATH_BASE."/components/".$component;
            Form::addFormPath($componentpath.'/forms');
            Form::addFieldPath($componentpath.'/forms/fields');
        }
        else {
            Form::addFormPath(JPATH_COMPONENT.'/forms');
            Form::addFieldPath(JPATH_COMPONENT.'/forms/fields');
        }


	    try
	    {
		    $formFactory = $this->getFormFactory();
	    }
	    catch (\UnexpectedValueException $e)
	    {
		    // @Todo can be removed when the constructor argument becomes mandatory
		    $formFactory = Factory::getContainer()->get(FormFactoryInterface::class);
	    }

        try {
	        $form = $formFactory->createForm($name, $options);

	        // Load the data.
	        if (substr($source, 0, 1) == '<')
	        {
		        if ($form->load($source, false, $xpath) == false)
		        {
			        throw new \RuntimeException('Form::loadForm could not load form');
		        }
	        }
	        else
	        {
		        if ($form->loadFile($source, false, $xpath) == false)
		        {
			        throw new \RuntimeException('Form::loadForm could not load file');
		        }
	        }


            //$form_j3 = JForm::getInstance($name, $source, $options, false, $xpath);
	        //$form = $this->loadForm($name, $source, $options, false, $xpath);

            if (isset($options['load_data']) && $options['load_data']) {
                // Get the data for the form.
                $data = $this->loadFormData();
            } else {
                $data = array();
            }

            // Allow for additional modification of the form, and events to be triggered.
            // We pass the data because plugins may require it.
            $this->preprocessForm($form, $data);

            // Load the data into the form after the plugins have operated.
            $form->bind($data);

        } catch (Exception $e) {
	        $app = Factory::getApplication();
	        $app->enqueueMessage(Text::_($e->getMessage()), 'error');
            return false;
        }

        // Store the form for later.
        $this->_forms[$hash] = $form;

        return $form;
    }
}



class JFContentModelItem extends JFTempContentModelItem {

    public function __construct($config = array())
    {
        // Must set option value to override constructors attempts to find it!
        $this->option  = "com_content";
//sbou4
	    $this->name  = "article";
        return parent::__construct($config);
    }

//sbou4
    function &____getItem($translation=null)
    {
        $item = parent::getItem();
        return $item;

        $table = clone(parent::getItem());

        // I could pick up the URL here or treat as a special content element field type?
        if ($table->type == 'component'){

            // Note that to populate the initial value of the urlparams
            $conf = JFactory::getConfig();
            $elementTable = $conf->get('falang.elementTable',false);
            foreach ($elementTable->Fields as $efield) {
                if ($efield->Name=="link" && isset($efield->translationContent->value) && $efield->translationContent->value!==""){
                    $uri = new JURI($efield->translationContent->value);
                    if ($uri->getVar("option",false)){
                        $table->link = $efield->translationContent->value;
                    }
                }
            }

            $url = str_replace('index.php?', '', $table->link);
            $url = str_replace('&amp;', '&', $url);
            $table->linkparts = null;
            if(strpos($url, '&amp;') !== false)
            {
                $url = str_replace('&amp;','&',$url);
            }

            parse_str($url, $table->linkparts);

            $db = $this->getDBO();
            if ($component = @$table->linkparts['option']) {
                $query = 'SELECT `extension_id`' .
                    ' FROM `#__extensions`' .
                    ' WHERE `element` = "'.$db->getEscaped($component).'"';
                $db->setQuery( $query );
                $table->componentid = $db->loadResult();
            }
        }
        $item = $table;
        return $item;
    }


}
class JFDefaultContentModelItem extends JFTempContentModelItem {

    function &getItem($pk = null)
    {

	    $jinput = JFactory::getApplication()->input;

        $table =  parent::getItem();
        $clone = clone($table);
        // get an empty version for the defalut
        $jinput->set("edit",false);
        $table = null;
	    $jinput->set( 'cid',array(0));
        $table =  parent::getItem();
        $item = clone($table);
        $item->component_id = $clone->component_id;
        $item->type = $clone->type;

        //$component		= $this->getComponent();

        // restore original
        $table = $clone;

        return $item;
    }

}

