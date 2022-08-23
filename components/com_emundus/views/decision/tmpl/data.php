<?php
/**
 * @version		$Id: data.php 14401 2014-09-16 14:10:00Z brivalland $
 * @package		Joomla
 * @subpackage	Emundus
 * @copyright	Copyright (C) 2005 - 2015 eMundus SAS. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */
defined('_JEXEC') or die('Restricted access');
$anonymize_data = EmundusHelperAccess::isDataAnonymized(JFactory::getUser()->id);
?>
<input type="hidden" id="view" name="view" value="decision">
<div class="panel panel-default em-data">
	<?php if (is_array($this->datas)) :?>
    <div class="container-result">
        <div>
			<?= $this->pagination->getResultsCounter(); ?>
		</div>
        <div id="countCheckedCheckbox" class="countCheckedCheckbox"></div>
        <?php echo $this->pageNavigation; ?>
    </div>
		<div class="em-data-container">
			<table class="table table-striped table-hover" id="em-data">
			<thead>
			<tr>
				<?php foreach($this->datas[0] as $kl => $v) :?>
					<th title="<?= JText::_($v); ?>" id="<?= $kl; ?>" >
						<div class="em-cell">
							<?php if ($kl == 'check') :?>
                                <div class="selectContainer" id="selectContainer">
                                    <div class="selectPage">
                                        <input type="checkbox" value="-1" id="em-check-all" class="em-hide em-check">
                                        <label for="em-check-all" class="check-box"></label>
                                    </div>
                                    <div class="selectDropdown" id="selectDropdown">
                                        <i class="fas fa-sort-down"></i>
                                    </div>
                                </div>

                                <div class="selectAll" id="selectAll">
                                    <label for="em-check-all">
                                        <input value="-1" id="em-check-all" type="checkbox" class="em-check" />
                                        <span id="span-check-all"><?= JText::_('COM_EMUNDUS_FILTERS_CHECK_ALL');?></span>
                                    </label>
                                    <label class="em-check-all-all" for="em-check-all-all">
                                        <input value="all" id="em-check-all-all" type="checkbox" class="em-check-all-all" />
                                        <span id="span-check-all-all"><?= JText::_('COM_EMUNDUS_FILTERS_CHECK_ALL_ALL'); ?></span>
                                    </label>
                                    <label class="em-check-none" for="em-check-none">
                                        <span id="span-check-none"><?= JText::_('COM_EMUNDUS_FILTERS_CHECK_NONE'); ?></span>
                                    </label>
                                </div>
							<?php elseif ($this->lists['order'] == $kl) :?>
								<?php if ($this->lists['order_dir'] == 'desc') :?>
									<span class="glyphicon glyphicon-sort-by-attributes-alt"></span>
								<?php else: ?>
									<span class="glyphicon glyphicon-sort-by-attributes"></span>
								<?php endif; ?>
								<strong>
									<?= JText::_($v); ?>
								</strong>
							<?php else: ?>
								<?= JText::_($v); ?>
							<?php endif;?>
						</div>
					</th>
				<?php endforeach; ?>
			</tr>
			</thead>
			<tbody>

			<?php foreach ($this->datas as $key => $line):?>
				<?php if ($key != 0) :?>
					<tr>
						<?php foreach ($line as $k => $value):?>
						  <?php if ($k != 'evaluation_id'): ?>

							<td <?= ($k == 'check' && $value->class != null)?'class="'.$value->class.'"':''; ?>>
								<div class="em-cell" >
									<?php if ($k == 'check'): ?>
										<label for = "<?= $line['fnum']->val ?>_check">
											<input type="checkbox" name="<?= $line['fnum']->val ?>_check" id="<?= $line['fnum']->val ?>_check" class='em-check' style="width:20px !important;"/>
											<?php
												$tab = explode('-', $key);
												echo ($tab[1] + 1 + $this->pagination->limitstart);
												?>
										</label>
									<?php elseif ($k == 'status') :?>
                                        <span class="label label-<?= $value->status_class; ?>" title="<?= $value->val; ?>"><?= $value->val; ?></span>
									<?php elseif ($k == 'fnum') :?>
										<a href="#<?= $value->val ?>|open" id="<?= $value->val; ?>" class="em_file_open">
											<?php if (isset($value->photo) && !$anonymize_data) :?>
                                                <div class="em_list_photo"><?= $value->photo; ?></div>
											<?php endif; ?>
                                            <div class="em_list_text">
												<?php if ($anonymize_data) :?>
                                                    <div class="em_list_fnum"><?= $value->val; ?></div>
												<?php else :?>
                                                    <span class="em_list_text" title="<?= $value->val; ?>"> <strong> <?= $value->user->name; ?></strong></span>
                                                    <div class="em_list_email"><?= $value->user->email; ?></div>
                                                    <div class="em_list_email"><?= $value->user->id; ?></div>
												<?php endif; ?>
                                            </div>
										</a>
									<?php elseif ($k == "access") :?>
										<?= $this->accessObj[$line['fnum']->val]; ?>
									<?php elseif (array_key_exists($k, $this->colsSup)) :?>
										<?= @$this->colsSup[$k][$line['fnum']->val]; ?>
									<?php else :
											if ($value->type == 'text') {
												echo strip_tags($value->val);
											} elseif ($value->val !== 'COM_EMUNDUS_PLEASE_SELECT') {
												// Do not display the typical COM_EMUNDUS_PLEASE_SELECT text used for empty dropdowns.
                                                echo JText::_($value->val);
											}
                                        endif; ?>
								</div>
							</td>
  						  <?php endif; ?>
						<?php endforeach; ?>
					</tr>
				<?php endif;?>
			<?php  endforeach;?>
			</tbody>
		</table>

		</div>
		<div class="em-container-pagination">
            <div class="em-container-pagination-select">
                <label for = "pager-select" class="em-container-pagination-label"><?= JText::_('COM_EMUNDUS_DISPLAY')?></label>
                <select name="pager-select" class="chzn-select" id="pager-select">
                    <option value="0" <?php if($this->pagination->limit == 0){echo "selected=true";}?>><?= JText::_('COM_EMUNDUS_ACTIONS_ALL')?></option>
                    <option value="5" <?php if($this->pagination->limit == 5){echo "selected=true";}?>>5</option>
                    <option value="10" <?php if($this->pagination->limit == 10){echo "selected=true";}?>>10</option>
                    <option value="15" <?php if($this->pagination->limit == 15){echo "selected=true";}?>>15</option>
                    <option value="20" <?php if($this->pagination->limit == 20){echo "selected=true";}?>>20</option>
                    <option value="25" <?php if($this->pagination->limit == 25){echo "selected=true";}?>>25</option>
                    <option value="30" <?php if($this->pagination->limit == 30){echo "selected=true";}?>>30</option>
                    <option value="50" <?php if($this->pagination->limit == 50){echo "selected=true";}?>>50</option>
                    <option value="100" <?php if($this->pagination->limit == 100){echo "selected=true";}?>>100</option>
                </select>
            </div>
            <?php echo $this->pageNavigation; ?>
		</div>
	<?php else:?>
		<?= $this->datas; ?>
	<?php endif;?>
</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@8"></script>
<script type="text/javascript">
    //refreshFilter();
    function checkurl() {
        var url = $(location).attr('href');
        url = url.split("#");
        $('.alert.alert-warning').remove();
        if (url[1] != null && url[1].length >= 20) {
            url = url[1].split("|");
            var fnum = new Object();
            fnum.fnum = url[0];
            if (fnum != null && fnum.fnum != "close") {
                addLoader();
                $.ajax({
                    type:'get',
                    url:'index.php?option=com_emundus&controller=files&task=getfnuminfos',
                    dataType:"json",
                    data:({fnum: fnum.fnum}),
                    success: function(result)
                    {
                        if (result.status && result.fnumInfos != null)
                        {
                            console.log(result);
                            var fnumInfos = result.fnumInfos;
                            fnum.name = fnumInfos.name;
                            fnum.label = fnumInfos.label;
                            openFiles(fnum);
                        } else {
                            console.log(result);
                            removeLoader();
                            $(".panel.panel-default").prepend("<div class=\"alert alert-warning\"><?= JText::_('COM_EMUNDUS_APPLICATION_CANNOT_OPEN_FILE') ?></div>");
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown)
                    {
                        removeLoader();
                        $("<div class=\"alert alert-warning\"><?= JText::_('COM_EMUNDUS_APPLICATION_CANNOT_OPEN_FILE') ?></div>").prepend($(".panel.panel-default"));
                        console.log(jqXHR.responseText);
                    }
                })
            }
        }

    }
    $(document).ready(function(){
        //checkurl();
        $('#rt-mainbody-surround').children().addClass('mainemundus');
        $('#rt-main').children().addClass('mainemundus');
        $('#rt-main').children().children().addClass('mainemundus');
        $('.em-data-container').doubleScroll();
    });
    window.parent.$("html, body").animate({scrollTop : 0}, 300);
</script>

<script>
    $('.selectAll').css('display','none');
    $('.selectDropdown').click(function() {

        $('.selectContainer').removeClass('borderSelect');
        $('.selectAll').slideToggle(function() {

            if ($(this).is(':visible')) {

                $('.selectContainer').addClass('borderSelect');
                $(document).click(function (e) {

                    var container = $(".selectDropdown");

                    if (!container.is(e.target) && container.has(e.target).length === 0){
                        $('.selectAll').slideUp();
                        $('.selectContainer').removeClass('borderSelect');
                    }
                });
            }
        });
    });

    $('.selectAll>span').click(function() {
        $('.selectAll').slideUp();
    });

    $('#span-check-all-all').click(function() {
        $('.selectAll.em-check-all-all#em-check-all-all').prop('checked',true);// all
        //$('.em-check#em-check-all').prop('checked',true);//.selectPage Page
        //$('.em-check-all#em-check-all').prop('checked',true);//.selectAll Page
        $('.em-check').prop('checked',true);
        reloadActions('files', undefined, true);
    });

    $('#span-check-none').click(function(){
        $('#em-check-all-all').prop('checked',false);
        $('.em-check#em-check-all').prop('checked',false);
        $('.em-check-all#em-check-all').prop('checked',false);
        $('.em-check').prop('checked',false);
        $('#countCheckedCheckbox').html('');
        reloadActions('files', undefined, false);
    });

    $(document).on('change', '.em-check, .em-check-all-all', function() {

        let countCheckedCheckbox = $('.em-check').not('#em-check-all.em-check,#em-check-all-all.em-check ').filter(':checked').length;
        let allCheck = $('.em-check-all-all#em-check-all-all').is(':checked');
        let nbChecked = allCheck == true ? Joomla.JText._('COM_EMUNDUS_FILTERS_SELECT_ALL') : countCheckedCheckbox;

        let files = countCheckedCheckbox === 1 ? Joomla.JText._('COM_EMUNDUS_FILES_FILE') : Joomla.JText._('COM_EMUNDUS_FILES_FILES');
        if (countCheckedCheckbox !== 0) {
            $('#countCheckedCheckbox').html('<p>'+Joomla.JText._('COM_EMUNDUS_FILTERS_YOU_HAVE_SELECT') + nbChecked + ' ' + files+'</p>');
        } else {
            $('#countCheckedCheckbox').html('');
        }
    });
</script>
