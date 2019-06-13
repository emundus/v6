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
        $current_user = JFactory::getUser();
        if (!EmundusHelperAccess::asPartnerAccessLevel($current_user->id) )
            die( JText::_('RESTRICTED_ACCESS') );

        $app = JFactory::getApplication();
        $fnums = $app->input->getString('fnums', null);

        $trombi = new EmundusModelTrombinoscope();
        //$trombi_tpl = $trombi->getTrombiTpl();
        //$badge_tpl = $trombi->getBadgeTpl();


        $htmlLetters = $trombi->selectHTMLLetters();
        $templ = [];

        foreach ($htmlLetters as $letter){
            $templ[$letter['title']] = $letter;
        }
        //var_dump($templ['Trombinoscope']).die();
        //var_dump($htmlLetters[0]['id']).die();
        $fnums_json_decode = $trombi->fnums_json_decode($fnums);

        //$file = $this->getModel('Files');
        $programme = $trombi->getProgByFnum($fnums_json_decode[0]['fnum']);
        $trombi->set_template($programme['code'], 'trombi');
        $trombi->set_template($programme['code'], 'badge');

        $form_elements_id_list = 'index.php?option=com_emundus&view=export_select_columns&format=raw&code='.$programme['code'].'&layout=programme&rowid='.$programme['id'];

        // SET EDITOR PARAMS
        /*$params = array( 'smilies'=> '0' ,
            'style'  => '1' ,
            'layer'  => '0' ,
            'table'  => '1' ,
            'clear_entities'=>'1',
            'mode' => '1'
        );*/
        $params = array('mode' => 'simple');

        $editor = JFactory::getEditor();
        // DISPLAY THE EDITOR (name, html, width, height, columns, rows, bottom buttons, id, asset, author, params)
        //Modifié : $trombi->trombitpl à la place de $trombi_tpl
        $wysiwyg = $editor->display('trombi_tmpl', $templ[$htmlLetters[0]['title']]['body'], '100%', '250', '20', '20', true, 'trombi_tmpl', null, null, $params);
        

       // $this->assign('string_fnums', implode(',', $fnums));
        $this->assign('string_fnums', $fnums);
        // Option trombinoscope cochée par défaut
        $this->assign('trombi_checked', 'checked');
        $this->assign('badge_checked', '');
        $this->assign('selected_format', 'trombi');
        // Autres options
        //$this->assign('trombi_tmpl', $trombi_tpl); //Modifié $trombi->trombitpl à la place de $trombi_tpl
        //$this->assign('badge_tmpl', $badge_tpl); //Modifié $trombi->badge_tpl à la place de $badge_tpl
        $this->assign('default_margin', $trombi->default_margin);
        $this->assign('wysiwyg', $wysiwyg);
        $this->assign('form_elements_id_list', $form_elements_id_list);
        $this->assign('htmlLetters', $htmlLetters);
        $this->assign('templ', $templ);

        parent::display($tpl);
    }
}

?>