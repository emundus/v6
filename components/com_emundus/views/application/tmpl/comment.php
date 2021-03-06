<?php
/**
 * Created by PhpStorm.
 * User: brivalland
 * Date: 13/11/14
 * Time: 11:24
 */
JFactory::getSession()->set('application_layout', 'comment');

$offset = JFactory::getApplication()->get('offset', 'UTC');
$dateTime = new DateTime(gmdate('Y-m-d H:i:s'), new DateTimeZone($offset));
$now = $dateTime->format(JText::_('DATE_FORMAT_LC2'));

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
        <div class="panel panel-default widget em-container-comment">
            <div class="panel-heading em-container-comment-heading">

                <h3 class="panel-title">
                	<span class="glyphicon glyphicon-comment"></span>
                	<?php echo JText::_('COMMENTS'); ?>
                	<span class="label label-info"><?php echo count($this->userComments); ?></span>
                </h3>

                <div class="btn-group pull-right">
                    <button id="em-prev-file" class="btn btn-info btn-xxl"><i class="small arrow left icon"></i></button>
                    <button id="em-next-file" class="btn btn-info btn-xxl"><i class="small arrow right icon"></i></button>
                </div>

            </div>
            <div class="panel-body em-container-comment-body">
                <ul class="list-group">
                <?php
				if (count($this->userComments) > 0) {
					$i = 0;
					foreach ($this->userComments as $comment) { ?>
                    <li class="list-group-item" id="<?php echo $comment->id; ?>">
                        <div class="row">
                            <div class="col-xs-10 col-md-11">
                                <div class="em-list-status">
                                    <a href="#" class="comment-name"><?php echo htmlspecialchars($comment->reason, ENT_QUOTES, 'UTF-8'); ?></a>
                                    <input style="display: none;" name="cname" type="text" value="<?php echo htmlspecialchars($comment->reason, ENT_QUOTES, 'UTF-8'); ?>">
                                    <div class="mic-info comment-date em-list-status-date">
                                        <a href="#"><?php echo $comment->name; ?></a> - <?php echo $comment->date; ?>
                                    </div>
                                </div>
                                <div class="comment-text em-list-status-comment"><?php echo str_replace(["\r\n", "\r", "\n"], "<br/>", htmlspecialchars($comment->comment, ENT_QUOTES, 'UTF-8')); ?></div>
                                <textarea style="display: none;" class="ctext"><?php echo htmlspecialchars($comment->comment, ENT_QUOTES, 'UTF-8'); ?></textarea>
								<?php if (($this->_user->id == $comment->user_id && EmundusHelperAccess::asAccessAction(10, 'c', $this->_user->id, $this->fnum))|| EmundusHelperAccess::asAccessAction(10, 'u', $this->_user->id, $this->fnum)) :?>
                                <div class="action em-list-status-action">
                                    <div class="edit-comment-container">
                                        <button type="button" class="btn btn-info btn-xs edit-comment" title="<?php echo JText::_('EDIT');?>" >
                                            <span class="glyphicon glyphicon-edit"></span>
                                            <div class="hidden cid"><?php echo $comment->id; ?></div>
                                        </button>
                                        <?php if (($this->_user->id == $comment->user_id && EmundusHelperAccess::asAccessAction(10, 'c', $this->_user->id, $this->fnum)) || EmundusHelperAccess::asAccessAction(10, 'd', $this->_user->id, $this->fnum)) :?>
                                            <div class="action">
                                                <button type="button" class="btn btn-danger btn-xs delete-comment" title="<?php echo JText::_('DELETE');?>">
                                                    <span class="glyphicon glyphicon-trash"></span>
                                                </button>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="actions-edit-comment" style="display: none">
                                        <button type="button" class="btn btn-danger btn-xs cancel-edit-comment" title="<?php echo JText::_('CANCEL');?>" >
                                            <span class="glyphicon glyphicon-remove"></span>
                                            <div class="hidden cid"><?php echo $comment->id; ?></div>
                                        </button>
                                        <button type="button" class="btn btn-success btn-xs confirm-edit-comment" title="<?php echo JText::_('EDIT');?>" >
                                            <span class="glyphicon glyphicon-ok"></span>
                                            <div class="hidden cid"><?php echo $comment->id; ?></div>
                                        </button>
                                    </div>

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

	        <?php if (EmundusHelperAccess::asAccessAction(10, 'c', $this->_user->id, $this->fnum)): ?>
	            <div class="form em-decision-form" id="form"></div>
	        <?php endif; ?>

        </div>
    </div>
</div>

<script type="text/javascript">

function escapeHtml(unsafe) {
    return unsafe
         .replace(/&/g, "&amp;")
         .replace(/</g, "&lt;")
         .replace(/>/g, "&gt;")
         .replace(/"/g, "&quot;")
         .replace(/'/g, "&#039;");
 }

$(document).off('click', '.comments .delete-comment');
$(document).on('click', '.comments .delete-comment', function(e) {

	if (e.handle === true) {

		e.handle = false;
		url = 'index.php?option=com_emundus&controller=application&task=deletecomment';
		var id = $(this).parents('li').attr('id');

		$.ajax({
	            type:'GET',
	            url:url,
	            dataType:'json',
	            data:({comment_id:id}),
	            success: function(result) {

	                if (result.status) {

	                    $('.comments li#'+id).empty();
	                    $('.comments li#'+id).append(result.msg);
		                var nbCom = parseInt($('.panel-default.widget .panel-heading .label.label-info').text().trim());
		                nbCom--;
		                $('.panel-default.widget .panel-heading .label.label-info').html(nbCom);

	                } else {
                        $('.comments li#'+id).append('<p class="text-danger"><strong>'+result.msg+'</strong></p>');
	                }
	            },
	            error: function (jqXHR) {
	                console.log(jqXHR.responseText);
	            }
		});
	}
});

var textArea = '<hr><div id="form" class="em-decision-form-content">' +
                    '<input placeholder="<?php echo JText::_('TITLE');?>" class="form" id="comment-title" type="text" style="height:50px !important;width:100% !important;" value="" name="comment-title"/><br>' +
                    '<textarea placeholder="<?php echo JText::_('ENTER_COMMENT');?>" class="form" style="height:200px !important;width:100% !important;"  id="comment-body"></textarea><br>' +
                '<button type="button" class="btn btn-success"> <?php echo JText::_('ADD_COMMENT');?> </button></div>';

$('#form').append(textArea);

$(document).off('click', '#form .btn.btn-success');
$(document).on('click', '#form .btn.btn-success', function(f) {

	if (f.handle === true) {

		f.handle = false;
		var comment = $('#comment-body').val();
	    var title = $('#comment-title').val();

	    if (comment.length === 0) {
	        $('#comment-body').attr('style', 'height:250px !important;width:100% !important; border-color: red !important; background-color:pink !important;');
	        return;
	    }

	    $('.modal-body').empty();
	    $('.modal-body').append('<div>' +'<p>'+Joomla.JText._('COMMENT_SENT')+'</p>' +'<img src="'+loadingLine+'" alt="loading"/>' +'</div>');

	    $.ajax({
			type:'POST',
			url: 'index.php?option=com_emundus&controller=files&task=addcomment',
			dataType:'json',
            data:({id:1, fnums:'{"i":"<?=$this->fnum?>"}', title: title, comment:comment}),

			success: function(result) {

			    $('#form').empty();
				if (result.status) {

				    $('#form').append('<p class="text-success"><strong>'+result.msg+'</strong></p>');
					var li = ' <li class="list-group-item" id="'+result.id+'">'+
						'<div class="row">'+
							'<div class="col-xs-10 col-md-11">'+
								'<div>'+
									'<a href="#" class="comment-name">'+escapeHtml(title)+'</a>'+
                                    '<input style="display: none;" name="cname" type="text" value="'+escapeHtml(title)+'">'+
									'<div class="mic-info comment-date">'+
										'<a href="#"><?php echo $this->_user->name; ?></a> - <?php echo $now; ?>'+
									'</div>'+
								'</div>'+
								'<div class="comment-text">'+escapeHtml(comment).replace(/(?:\r\n|\r|\n)/g, '<br>')+'</div>'+
                                '<textarea style="display: none;" class="ctext">'+escapeHtml(comment)+'</textarea>'+
                                '<div class="action">'+
                                    '<div class="edit-comment-container">'+
                                        '<button type="button" class="btn btn-info btn-xs edit-comment" title="<?php echo JText::_('EDIT');?>" >'+
                                            '<span class="glyphicon glyphicon-edit"></span>'+
                                            '<div class="hidden cid">'+result.id+'</div>'+
                                        '</button>'+
                                        '<div class="action">'+
                                            '<button type="button" class="btn btn-danger btn-xs delete-comment" title="<?php echo JText::_('DELETE');?>">'+
                                                '<span class="glyphicon glyphicon-trash"></span>'+
                                            '</button>'+
                                        '</div>'+
                                    '</div>'+
                                    '<div class="actions-edit-comment" style="display: none">'+
                                        '<button type="button" class="btn btn-danger btn-xs cancel-edit-comment" title="<?php echo JText::_('CANCEL');?>" >'+
                                            '<span class="glyphicon glyphicon-remove"></span>'+
                                            '<div class="hidden cid">'+result.id+'</div>'+
                                        '</button>'+
                                        '<button type="button" class="btn btn-success btn-xs confirm-edit-comment" title="<?php echo JText::_('EDIT');?>" >'+
                                            '<span class="glyphicon glyphicon-ok"></span>'+
                                            '<div class="hidden cid">'+result.id+'</div>'+
                                        '</button>'+
                                    '</div>'+
                                '</div>'+
							'</div>'+
						'</div>'+
					'</li>';

					$('.comments .list-group').append(li);
					var nbCom = parseInt($('.panel-default.widget .panel-heading .label.label-info').text().trim());
					nbCom++;
					$('.panel-default .panel-heading .label.label-info').html(nbCom);

				} else {
					$('#form').append('<p class="text-danger"><strong>'+result.msg+'</strong></p>');
				}

				$('#form').append(textArea);
			},
			error: function (jqXHR, textStatus, errorThrown) {
				console.log(jqXHR.responseText);
			}
		});
	}
});

// Open the edition fields.
$(document).off('click', '.edit-comment');
$(document).on('click', '.edit-comment', function (e) {

    // Comment ID value is hidden in a div in the button for easier access.
    // We then find the comment global element which has an ID matching that one.
    var id = $($(this).find('.cid')[0]).text();

    var comment  = {
        element: $('#' + id)
    };

    comment.title   = $(comment.element.find('.comment-name')[0]);
    comment.tinput  = $(comment.element.find('input[name=cname]')[0]);
    comment.body    = $(comment.element.find('.comment-text')[0]);
    comment.binput  = $(comment.element.find('.ctext')[0]);
    comment.actions = $(comment.element.find('.actions-edit-comment')[0]);
    comment.edit    = $(comment.element.find('.edit-comment-container')[0]);

    // We've hidden some inputs in the comment, we just need to display them and hide the text.
    // We also need to show / hide the buttons.
    comment.title.hide();
    comment.tinput.val(comment.title.text());
    comment.tinput.show();
    comment.body.hide();
    comment.binput.val(comment.body.html().replace(/<br\s*[\/]?>/gi, "\n"));
    comment.binput.show();
    comment.actions.show();
    comment.edit.hide();

});

// Close the edition fields.
$(document).off('click', '.cancel-edit-comment');
$(document).on('click', '.cancel-edit-comment', function (e) {

    // We are using the 'id' as a central point of reference.
    // We need to get the ID hidden in the edit button.
    var id = $($(this).find('.cid')[0]).text();

    var comment  = {
        element: $('#' + id)
    };

    comment.title   = $(comment.element.find('.comment-name')[0]);
    comment.tinput  = $(comment.element.find('input[name=cname]')[0]);
    comment.body    = $(comment.element.find('.comment-text')[0]);
    comment.binput  = $(comment.element.find('.ctext')[0]);
    comment.actions = $(comment.element.find('.actions-edit-comment')[0]);
    comment.edit    = $(comment.element.find('.edit-comment-container')[0]);

    // We need to clear and hide the fields as well as the buttons.
    comment.title.show();
    comment.tinput.val('');
    comment.tinput.hide();
    comment.body.show();
    comment.binput.val('');
    comment.binput.hide();
    comment.actions.hide();
    comment.edit.show();

});

$(document).off('click', '.confirm-edit-comment');
$(document).on('click', '.confirm-edit-comment', function (e) {

    // We are using the 'id' as a central point of reference.
    var id = $($(this).find('.cid')[0]).text();

    var comment  = {
        element: $('#' + id)
    };

    comment.title   = $(comment.element.find('.comment-name')[0]);
    comment.tinput  = $(comment.element.find('input[name=cname]')[0]);
    comment.body    = $(comment.element.find('.comment-text')[0]);
    comment.binput  = $(comment.element.find('.ctext')[0]);
    comment.actions = $(comment.element.find('.actions-edit-comment')[0]);
    comment.edit    = $(comment.element.find('.edit-comment-container')[0]);
    comment.date    = $(comment.element.find('.comment-date')[0]);

    // Now we post the info in order to edit the comment.
    $.ajax({
        type:'POST',
        url:'index.php?option=com_emundus&controller=application&task=editcomment&format=raw',
        dataType: 'json',
        data: ({
            id: id,
            title: comment.tinput.val(),
            text: comment.binput.val()
        }),
        success: function(result) {

            // Hide the inputs and swap buttons.
            comment.binput.hide();
            comment.tinput.hide();
            comment.actions.hide();
            comment.edit.show();

            if (result.status) {

                // The information is updated on the page. The date and user are also modified on the front-end.
                comment.title.text(escapeHtml(comment.tinput.val()));
                comment.body.html(escapeHtml(comment.binput.val()).replace(/(?:\r\n|\r|\n)/g, '<br>'));
                comment.date.html('<a href="#"><?php echo $this->_user->name; ?></a> - <?php echo JHtml::_('date', date('Y-m-d H:i:s'), JText::_('DATE_FORMAT_LC2')); ?>');

            } else {
                comment.element.append('<p class="text-danger"><strong>'+result.msg+'</strong></p>');
            }

            // Show the new updated titles and clear the inputs.
            comment.title.show();
            comment.body.show();
            comment.tinput.val('');
            comment.binput.val('');

        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.log(jqXHR.responseText);

            // Reset everything back to the way it was and display an error.
            comment.element.append('<p class="text-danger"><strong><?php echo JText::_('ERROR'); ?></strong></p>');
            comment.title.show();
            comment.tinput.val('');
            comment.tinput.hide();
            comment.body.show();
            comment.binput.val('');
            comment.binput.hide();
            comment.actions.hide();
            comment.edit.show();
        }
    });
});
</script>
