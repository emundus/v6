<?php
/**
 * Created by PhpStorm.
 * User: brivalland
 * Date: 13/11/14
 * Time: 11:24
 */
JFactory::getSession()->set('application_layout', 'comment');
?>

<style type="text/css">
	.widget .panel-body { padding:0px; }
	.widget .list-group { margin-bottom: 0; }
	.widget .panel-title { display:inline }
	.widget .label-info { float: right; }
	.widget li.list-group-item {border-radius: 0;border: 0;border-top: 1px solid #ddd;}
	.widget li.list-group-item:hover { background-color: rgba(86,61,124,.1); }
	.widget .mic-info { color: #666666;font-size: 11px; }
	.widget .action { margin-top:5px; }
	.widget .comment-text { font-size: 12px; }
	.widget .btn-block { border-top-left-radius:0px;border-top-right-radius:0px; }
</style>

<div class="comments">
    <div class="row">
        <div class="panel panel-default widget">
            <div class="panel-heading">
                
                <h3 class="panel-title">
                	<span class="glyphicon glyphicon-comment"></span> 
                	<?php echo JText::_('COMMENTS'); ?>
                	<span class="label label-info"><?php echo count($this->userComments); ?></span>
                </h3>
                
            </div>
            <div class="panel-body">
                <ul class="list-group">
                <?php
				if(count($this->userComments) > 0) {
					$i=0;
					foreach($this->userComments as $comment){ ?>
                    <li class="list-group-item" id="<?php echo $comment->id; ?>">
                        <div class="row">
                            <div class="col-xs-10 col-md-11">
                                <div>
                                    <a href="#"><?php echo $comment->reason; ?></a>
                                    <div class="mic-info">
                                        <a href="#"><?php echo $comment->name; ?></a> - <?php echo JHtml::_('date', $comment->date, JText::_('DATE_FORMAT_LC2')); ?>
                                    </div>
                                </div>
                                <div class="comment-text">
                                    <?php echo $comment->comment; ?>
                                </div>
                                <?php if($this->_user->id == $comment->user_id || EmundusHelperAccess::asAccessAction(10, 'd', $this->_user->id, $this->fnum)):?>
                                <div class="action">
                                    <button type="button" class="btn btn-danger btn-xs" title="<?php echo JText::_('DELETE');?>">
                                        <span class="glyphicon glyphicon-trash"></span>
                                    </button>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </li>
                 <?php
						$i++;
					}
				} else echo JText::_('NO_COMMENT');
				?>  
                </ul>
            </div>

	        <?php if(EmundusHelperAccess::asAccessAction(10, 'c', $this->_user->id, $this->fnum)):?>
	            <div class="form" id="form"></div>
	        <?php endif;?>
        
        </div>
    </div>
</div>

<script type="text/javascript">

$(document).off('click', '.comments .btn.btn-danger.btn-xs');
$(document).on('click', '.comments .btn.btn-danger.btn-xs', function(e)
{ 
	if (e.handle === true) {
		e.handle = false;
		url = 'index.php?option=com_emundus&controller=application&task=deletecomment';
		var id = $(this).parents('li').attr('id');
	    $.ajax({
	            type:'GET',
	            url:url,
	            dataType:'json',
	            data:({comment_id:id}),
	            success: function(result)
	            {
	                if(result.status)
	                {

	                    $('.comments li#'+id).empty();
	                    $('.comments li#'+id).append(result.msg);
		                var nbCom = parseInt($('.panel-default.widget .panel-heading .label.label-info').text().trim())
		                nbCom--;
		                $('.panel-default.widget .panel-heading .label.label-info').html(nbCom);
	                }
	                else
	                {
	                    $('#form').append('<p class="text-danger"><strong>'+result.msg+'</strong></p>');
	                }
	            },
	            error: function (jqXHR, textStatus, errorThrown)
	            {
	                console.log(jqXHR.responseText);
	            }
	           });
	}
});

var textArea = '<hr><div id="form">' +
                    '<input placeholder="<?php echo JText::_('TITLE');?>" class="form" id="comment-title" type="text" style="height:50px !important;width:100% !important;" value="" name="comment-title"/><br>' +
                    '<textarea placeholder="<?php echo JText::_('ENTER_COMMENT');?>" class="form" style="height:200px !important;width:100% !important;"  id="comment-body"></textarea><br>' + 
                '<button type="button" class="btn btn-success"><?php echo JText::_('ADD_COMMENT');?></button></div>';

$('#form').append(textArea);

$(document).off('click', '#form .btn.btn-success');
$(document).on('click', '#form .btn.btn-success', function(f)
{ 
	if (f.handle === true) {
		f.handle = false;
		var comment = $('#comment-body').val();
	    var title = $('#comment-title').val();
	    if (comment.length == 0)
	    {
	        $('#comment-body').attr('style', 'height:250px !important;width:100% !important; border-color: red !important; background-color:pink !important;');
	        return;
	    }
	    $('.modal-body').empty();
	    $('.modal-body').append('<div>' +'<p>'+Joomla.JText._('COMMENT_SENT')+'</p>' +'<img src="'+loadingLine+'" alt="loading"/>' +'</div>');
	    url = 'index.php?option=com_emundus&controller=files&task=addcomment';

	    $.ajax({
	            type:'POST',
	            url:url,
	            dataType:'json',
	            data:({id:1, fnums:'{"i":"'+$('#application_fnum').val()+'"}', title: title, comment:comment}),
	            success: function(result)
	            {
	                $('#form').empty();
	                if(result.status)
	                {
	                    $('#form').append('<p class="text-success"><strong>'+result.msg+'</strong></p>');
	                    var li = ' <li class="list-group-item" id="'+result.id+'">'+
	                        '<div class="row">'+
	                            '<div class="col-xs-10 col-md-11">'+
	                                '<div>'+
	                                    '<a href="#">'+title+'</a>'+
	                                    '<div class="mic-info">'+
	                                        '<a href="#"><?php echo $this->_user->name; ?></a> - <?php echo JHtml::_('date', date('Y-m-d H:i:s'), JText::_('DATE_FORMAT_LC2')); ?>'+
	                                    '</div>'+
	                                '</div>'+
	                                '<div class="comment-text">'+comment+'</div>'+
	                                '<div class="action">'+
	                                    '<button type="button" class="btn btn-danger btn-xs" title="<?php echo JText::_('DELETE');?>">'+
	                                        '<span class="glyphicon glyphicon-trash"></span>'+
	                                    '</button>'+
	                                '</div>'+
	                            '</div>'+
	                        '</div>'+
	                    '</li>';
	                    $('.comments .list-group').append(li);
		                var nbCom = parseInt($('.panel-default.widget .panel-heading .label.label-info').text().trim())
		                nbCom++;
		                $('.panel-default .panel-heading .label.label-info').html(nbCom);
	                }
	                else
	                {
	                    $('#form').append('<p class="text-danger"><strong>'+result.msg+'</strong></p>');
	                }

	                $('#form').append(textArea);
	            },
	            error: function (jqXHR, textStatus, errorThrown)
	            {
	                console.log(jqXHR.responseText);
	            }
	           });
	}
});
</script>
