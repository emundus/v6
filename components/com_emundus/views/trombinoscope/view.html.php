<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 28/03/2017
 * Time: 01:14
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.application.component.view');

class EmundusViewTrombinoscope extends JViewLegacy
{
    protected $actions;

    public function __construct($config = array())
    {
        parent::__construct($config);
    }

    public function display($tpl = null)
    {
        $document = JFactory::getDocument();
        $document->addStyleSheet(JURI::base()."media/com_emundus/css/emundus_trombinoscope.css" );
        //$document->addScript(JURI::base()."media/com_emundus/lib/jquery-1.10.2.min.js" );

        $current_user = JFactory::getUser();
        if( !EmundusHelperAccess::asPartnerAccessLevel($current_user->id) )
            die( JText::_('RESTRICTED_ACCESS') );

        $app = JFactory::getApplication();
        $fnums = $app->input->getString('fnums', null);

        $trombi = new EmundusModelTrombinoscope();
        // SET EDITOR PARAMS
        /*$params = array( 'smilies'=> '0' ,
            'style'  => '1' ,
            'layer'  => '0' ,
            'table'  => '1' ,
            'clear_entities'=>'1',
            'mode' => '1'
        );*/
        $params = array('mode' => 'simple');
        $editor = JFactory::getEditor('tinymce');

        $editor = JFactory::getEditor();
        // DISPLAY THE EDITOR (name, html, width, height, columns, rows, bottom buttons, id, asset, author, params)
        $wysiwyg = $editor->display('trombi_tmpl', $trombi->trombi_tpl, '100%', '250', '20', '20', true, 'trombi_tmpl', null, null, $params);
        

       // $this->assign('string_fnums', implode(',', $fnums));
        $this->assign('string_fnums', $fnums);
        // Option trombinoscope cochée par défaut
        $this->assign('trombi_checked', 'checked');
        $this->assign('badge_checked', '');
        $this->assign('selected_format', 'trombi');
        // Autres options
        $this->assign('trombi_tmpl', $trombi->trombi_tpl);
        $this->assign('badge_tmpl', $trombi->badge_tpl);
        $this->assign('default_margin', $trombi->default_margin);
        $this->assign('wysiwyg', $wysiwyg);

        parent::display($tpl);
    }
}

?>