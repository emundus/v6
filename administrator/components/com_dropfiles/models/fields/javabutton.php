<?php
/**
 * Dropfiles
 *
 * We developed this code with our hearts and passion.
 * We hope you found it useful, easy to understand and to customize.
 * Otherwise, please feel free to contact us at contact@joomunited.com *
 *
 * @package   Dropfiles
 * @copyright Copyright (C) 2013 JoomUnited (http://www.joomunited.com). All rights reserved.
 * @copyright Copyright (C) 2013 Damien Barr?re (http://www.crac-design.com). All rights reserved.
 * @license   GNU General Public License version 2 or later; http://www.gnu.org/licenses/gpl-2.0.html
 */

defined('_JEXEC') || die;

jimport('joomla.form.formfield');


/**
 * Class JFormFieldJavaButton
 */
class JFormFieldJavaButton extends JFormField
{
    /**
     * Type
     *
     * @var string
     */
    protected $type = 'javabutton';

    /**
     * Method to get the field input button
     *
     * @return string
     */
    protected function getInput()
    {
        // Make pid for this session
        $pid = sha1(time() . uniqid());
        // Build the script
        $script = array();
        $script[] = "jQuery(document.body).ready(function($){
            $(document).on('click', '#" . $this->id . "', function (e) {
            e.preventDefault();
            $.ajax({
                url: \"index.php?option=com_dropfiles&task=config.clonetheme\",
                type: \"POST\",
                data: {fromtheme: $('select[name=\"jform[fromtheme]\"]').val(), newtheme: $('input[name=\"jform[newtheme]\"]').val()},
                dataType : 'json'
            }).done(function(res){
                alert(res.datas);
                $('#jform_newtheme').val('');
                window.location.reload();
            });
            return false;
        });
    });";
        // Add to document head
        JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));
        $html = array();

        // The reindex button
        $html[] = '<div class="button2-left">';
        $html[] = '  <div class="blank">';

        $html[] = '<a id="'.$this->id.'" style="text-decoration:none;" class="btn button ju-btn ju-btn-connect"';
        $html[] = ' title="Clone">Clone</a>';

        $html[] = '  </div>';
        $html[] = '</div>';

        return implode("\n", $html);
    }
}
