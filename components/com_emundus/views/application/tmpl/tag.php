<?php
defined('_JEXEC') or die('Restricted access');
JFactory::getSession()->set('application_layout', 'tag');
?>



<div class="tags">
    <div class="row">
        <div class="panel panel-default widget em-container-tags">
            <div class="panel-heading em-container-tags-heading">
                <h3 class="panel-title" style="display:inline-block">
                    <span class="glyphicon glyphicon-tags"></span>
                    <?php echo JText::_('COM_EMUNDUS_TAGS'); ?>
                    <span class="label label-info" style="float:unset"><?php echo count($this->tags); ?></span>
                </h3>&ensp;&ensp;

	            <?php if (EmundusHelperAccess::asAccessAction(14, 'c', $this->_user->id, $this->fnum)) :?>
                    <select class="chzn-select" multiple id="mytags">
                        <?php foreach($this->groupedTags as $category => $value) :?>
                            <optgroup value="<?php echo $category; ?>" label="<?= empty($category) ? JText::_('UNCATEGORIZED_TAGS') : JText::_($category) ;?>">
                            <?php foreach($value as $tag) :?>
                                <option value="<?php echo $tag['id']; ?>"><?php echo $tag['label']; ?></option>
                            <?php endforeach; ?>
                            </optgroup>
                        <?php endforeach; ?>

                    </select>&ensp;&ensp;
                    <button class="btn btn-success btn-xs" id="add-tags">
                        <?php echo JText::_('COM_EMUNDUS_ADD'); ?>
                    </button>
	            <?php endif;?>
                <div class="btn-group pull-right">
                    <button id="em-prev-file" class="btn btn-info btn-xxl"><i class="small arrow left icon"></i></button>
                    <button id="em-next-file" class="btn btn-info btn-xxl"><i class="small arrow right icon"></i></button>
                </div>
            </div>
            <div class="panel-body em-container-tags-body">
                <ul class="list-group">
                    <?php
                    if(count($this->tags) > 0) {
                        $i=0;
                        foreach($this->tags as $tag){ ?>
                            <li class="list-group-item" id="<?php echo $tag['id_tag']; ?>" fnum="<?php echo $this->fnum; ?>">
                                <div class="row">
                                    <div class="col-xs-10 col-md-11">
                                        <div>

                                            <div class="mic-info em-tags-date">
                                                <a href="#"><?php echo $tag['name']; ?></a> - <?php echo JHtml::_('date', $tag['date_time'], JText::_('DATE_FORMAT_LC2')); ?>
                                            </div>
                                        </div>
                                        <div class="comment-text em-tags-action">
                                            <h2><span class="label <?php echo $tag['class']; ?>" style="float:unset"><?php echo $tag['label']; ?></span>
                                                <?php if($this->_user->id == $tag['user_id'] || EmundusHelperAccess::asAccessAction(14, 'd', $this->_user->id, $this->fnum)):?>
                                                        <button type="button" class="btn btn-danger btn-xs" title="<?php echo JText::_('COM_EMUNDUS_ACTIONS_DELETE');?>">
                                                            <span class="glyphicon glyphicon-trash"></span>
                                                        </button>
                                                <?php endif; ?>
                                            </h2>
                                        </div>

                                    </div>
                                </div>
                            </li>
                            <?php
                            $i++;
                        }
                    } else echo JText::_('COM_EMUNDUS_TAGS_NO_TAG');
                    ?>
                </ul>
            </div>

            <?php if(EmundusHelperAccess::asAccessAction(14, 'c', $this->_user->id, $this->fnum)):?>
                <div class="form" id="form"></div>
            <?php endif;?>

        </div>
    </div>
</div>

<script type="text/javascript">
    //$(".tags .comment-text .btn.btn-danger.btn-xs").unbind("click");
    $(document).off('click', '.tags .comment-text .btn.btn-danger.btn-xs');
    $(document).on('click', '.tags .comment-text .btn.btn-danger.btn-xs', function(e)
    {
        if (e.handle === true) {
            e.handle = false;
            url = 'index.php?option=com_emundus&controller=application&task=deletetag';
            var id = $(this).parents('li').attr('id');
            var fnum = $(this).parents('li').attr('fnum');
            $.ajax({
                type:'GET',
                url:url,
                dataType:'json',
                data:({fnum:fnum, id_tag:id}),
                success: function(result)
                {
                    if(result.status)
                    {

                        $('.tags li[id='+id+']').empty();
                        $('.tags li[id='+id+']').append(result.msg);
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
    $(document).ready(function()
	{
        $('.chzn-select').chosen({placeholder_text_multiple: Joomla.JText._('PLEASE_SELECT_TAG'),
            width:'50%'});
        $('.chzn-select').trigger("chosen:updated");
    })

     $(document).on('click', '#add-tags', function(e)
    {
        if(e.handle === true) {
            e.handle = false;
            var tags = $("#mytags").val();

            url = 'index.php?option=com_emundus&controller='+$('#view').val()+'&task=tagfile';
            $.ajax(
                {
                    type:'POST',
                    url:url,
                    dataType:'json',
                    data:({fnums:'{"1":"<?php echo $this->fnum; ?>"}', tag: tags}),
                    success: function(result) {
                        if (result.status) {
                            var url = "index.php?option=com_emundus&view=application&format=raw&layout=tag&fnum=<?php echo $this->fnum; ?>";
                            $.ajax({
                                type:'get',
                                url:url,
                                dataType:'html',
                                success: function(result)
                                {
                                    $('#em-appli-block').empty();
                                    $('#em-appli-block').append(result);
                                },
                                error: function (jqXHR, textStatus, errorThrown)
                                {
                                    console.log(jqXHR.responseText);
                                }

                            });
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        console.log(jqXHR.responseText);
                    }
                });
            }
    });
</script>
