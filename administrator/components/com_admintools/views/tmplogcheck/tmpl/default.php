<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2016 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 * @version   $Id$
 */

// Protect from unauthorized access
defined('_JEXEC') or die;

JHtml::_('behavior.modal');

$complete = JText::_('ATOOLS_LBL_CHECKCOMPLETED');

$js = <<<JS
akeeba.jQuery(document).ready(function($){
    $.ajax('index.php?option=com_admintools&view=tmplogcheck&task=check&format=json', {
        success : function(response){
            var match = response.match(/###(.*?)###/)
            
            if(match[1] === undefined)
            {
                alert('Invalid response from the server!');
                return;
            }
            
            data = JSON.decode(match[1])
            
            var css_class = 'alert-info';
            
            if(!data.result)
            {
                css_class = 'alert-warning';
            }
            
            if(data.msg)
            {
                $('#message').html(data.msg).addClass(css_class).show();
            }
                   
            $('.progress .bar').css('width', '100%');
            $('.progress').removeClass('progress-striped');
            $('#check-header').html('$complete');
        }
    })
})
JS;

AkeebaStrapper::addJSdef($js);
?>
<h1 id="check-header"><?php echo JText::_('ATOOLS_LBL_CHECKINPROGRESS'); ?></h1>

<div class="progress progress-striped active">
    <div class="bar"></div>
</div>

<div id="message" class="alert" style="display:none"></div>

<div id="autoclose" class="alert alert-info" style="display:none">
    <p><?php echo JText::_('ATOOLS_LBL_AUTOCLOSE_IN_3S'); ?></p>
</div>