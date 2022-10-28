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

<input type="hidden" id="view" name="view" value="admission">
<div class="panel panel-default em-data">
	<?php if (is_array($this->datas)) :?>
		<div>
			<?php echo $this->pagination->getResultsCounter(); ?>
		</div>
        <?php echo $this->pageNavigation; ?>
		<div class="em-data-container">
            <table class="table table-striped table-hover" id="em-data">
                <thead>
                <tr>
                    <?php foreach ($this->datas[0] as $kl => $v) :?>
                        <?php if ($kl == "jos_emundus_final_grade.user") :?>
                        <!-- Skips extra collumn -->
                        <?php else :?>
                        <th title="<?php echo JText::_($v); ?>" id="<?php echo $kl; ?>" >
                            <p class="em-cell">
                                <?php if ($kl == 'check') :?>
                                    <label for="em-check-all">
                                        <input type="checkbox" value="-1" id="em-check-all" class="em-check" style="width:20px !important;"/>
                                        <span><?php echo JText::_('COM_EMUNDUS_FILTERS_CHECK_ALL')?></span>
                                    </label>
                                    <label class="em-hide em-check-all-all" for="em-check-all-all">
                                        <input class="em-check-all-all em-hide" type="checkbox" name="check-all-all" value="all" id="em-check-all-all" style="width:20px !important;"/>
                                        <span class="em-hide em-check-all-all"><?php echo JText::_('COM_EMUNDUS_FILTERS_CHECK_ALL_ALL')?></span>
                                    </label>
                                <?php elseif (@$this->lists['order'] == $kl) :?>
                                    <?php if (@$this->lists['order_dir'] == 'desc') :?>
                                        <span class="glyphicon glyphicon-sort-by-attributes-alt"></span>
                                    <?php else :?>
                                        <span class="glyphicon glyphicon-sort-by-attributes"></span>
                                    <?php endif; ?>
                                    <strong>
                                        <?php echo JText::_($v); ?>
                                    </strong>
                                <?php else :?>
                                    <?php echo JText::_($v); ?>
                                <?php endif; ?>
                            </p>
                        </th>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tr>
                </thead>
                <tbody>

                <?php foreach ($this->datas as $key => $line) :?>
                    <?php if ($key != 0) :?>
                        <tr>
                            <?php $cfnum = $line['fnum']->val; ?>
                            <?php foreach ($line as $k => $value) :?>
                                <td <?php echo ($k == 'check' && $value->class != null)?'class="'.$value->class.'"':''; ?>>
                                    <div class="em-cell">
                                        <?php if ($k == 'check') :?>
                                            <label for = "<?php echo $line['fnum']->val; ?>_check">
                                                <input type="checkbox" name="<?php echo $line['fnum']->val; ?>_check" id="<?php echo $line['fnum']->val; ?>_check" class='em-check' style="width:20px !important;"/>
                                                <?php
                                                    $tab = explode('-', $key);
                                                    echo ($tab[1] + 1 + $this->pagination->limitstart);
                                                ?>
                                            </label>
                                        <?php elseif ($k == 'status') :?>
                                            <span class="label label-<?php echo $value->status_class; ?>" title="<?php echo $value->val; ?>"><?php echo $value->val; ?></span>
                                        <?php elseif ($k == 'fnum') :?>
                                            <a href="#<?php echo $value->val ?>|open" id="<?php echo $value->val; ?>" class="em_file_open">
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
                                            <?php echo $this->accessObj[$line['fnum']->val]; ?>
                                        <?php elseif ($k == "id_tag") :?>
                                            <?php echo @$this->colsSup['id_tag'][$line['fnum']->val]; ?>
                                        <?php elseif (array_key_exists($k, $this->colsSup)) :?>
	                                        <?= @$this->colsSup[$k][$line['fnum']->val]; ?>
                                        <?php else:?>

                                            <?php if ($value->type == 'text' ) :?>
                                                <?php echo strip_tags($value->val); ?>
                                            <?php elseif ($value->type == 'textarea' && EmundusHelperAccess::asAccessAction(34,'u',$value->user->id)) :?>
                                                <textarea class="input-medium em-cell-textarea" id="<?php echo $cfnum.'-'.$value->id; ?>"><?php echo $value->val; ?></textarea>
                                                <span class="glyphicon glyphicon-share-alt em-textarea" id="<?php echo $cfnum.'-'.$value->id.'-span'; ?>" aria-hidden="true" style="color:black;"></span>
                                            <?php elseif ($value->type == 'date') :?>
                                                <h5 class="em-date">
                                                    <strong>
                                                        <?php if (!isset($value->val) || $value->val == "0000-00-00 00:00:00") :?>
                                                                <span class="em-radio" id="<?php echo $cfnum.'-'.$value->id.'-'.$value->val; ?>" aria-hidden="true"></span>
                                                        <?php else: ?>
                                                            <?php
                                                                $params = json_decode($value->params);
                                                                $formatted_date = DateTime::createFromFormat('Y-m-d H:i:s', $value->val);
                                                                echo $formatted_date->format($params->date_form_format);
                                                            ?>
                                                        <?php endif; ?>
                                                    </strong>
                                                </h5>
                                            <?php elseif ($value->type == 'radiobutton') :?>
                                                <select name="<?php echo $cfnum.'-'.$value->id; ?>" class="em-radio input-medium" id="<?php echo $cfnum.'-'.$value->id; ?>"
                                                <?php
                                                    if (strtolower($value->val) == "yes" || strtolower($value->val) == "oui" || $value->val == 1) {
                                                        echo "style='border: solid 3px #BCCB56'";
                                                    } elseif (strtolower($value->val) == "no" || strtolower($value->val) == "non" || $value->val === 0) {
                                                        echo "style='border: solid 3px #E09541'";
                                                    } elseif (!empty($value->val)) {
                                                        echo "style='border: solid 3px #49A0CD'";
                                                    }
                                                ?>
                                                >
                                                    <?php if (!isset($value->val)) :?>
                                                        <option value="" disabled="disabled" selected="selected"> <?php echo JText::_('COM_EMUNDUS_PLEASE_SELECT'); ?> </option>
                                                    <?php endif; ?>
                                                    <?php foreach ($value->radio as $rlabel => $rval) :?>
                                                        <option value="<?php echo $rval; ?>" <?php echo ($value->val == $rlabel)? "selected=true":''?>> <?php echo JText::_($rlabel); ?> </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            <?php elseif ($value->type == 'field') :?>
                                                <input class="admission_input" type="text" id="<?php echo $cfnum.'-'.$value->id; ?>" name="<?php echo $value->val; ?>" value="<?php echo $value->val; ?>"></input>
                                                <span class="glyphicon glyphicon-share-alt em-field" id="<?php echo $cfnum.'-'.$value->id.'-span'; ?>" aria-hidden="true" style="color:black;"></span>
                                            <?php elseif ($value->type == 'fileupload') :?>
                                                <?php if (!empty($value->val) && $value->val != "/") :?>
                                                    <a href="<?php echo $value->val ?>" target="_blank"> <?php echo JText::_('COM_EMUNDUS_ATTACHMENTS_LINK_TO_DOWNLOAD'); ?> <span class="glyphicon glyphicon-save"></span> </a>
                                                <?php else :?>
                                                    <p> No File </p>
                                                <?php endif; ?>
                                            <?php else :?>
                                                <?php echo $value->val; ?>
                                            <?php endif; ?>

                                        <?php endif; ?>
                                    </div>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endif;?>
                <?php  endforeach;?>
                </tbody>
            </table>
		</div>
		<div class="em-container-pagination">
            <div class="em-container-pagination-select">
                <label for="pager-select" class="em-container-pagination-label"><?php echo JText::_('COM_EMUNDUS_DISPLAY')?></label>
                <select name="pager-select" class="chzn-select" id="pager-select">
                    <option value="0" <?php echo ($this->pagination->limit == 0)?"selected=true":'';?>><?php echo JText::_('COM_EMUNDUS_ACTIONS_ALL')?></option>
                    <option value="5" <?php echo ($this->pagination->limit == 5)?"selected=true":'';?>>5</option>
                    <option value="10" <?php echo ($this->pagination->limit == 10)?"selected=true":'';?>>10</option>
                    <option value="15" <?php echo ($this->pagination->limit == 15)?"selected=true":'';?>>15</option>
                    <option value="20" <?php echo ($this->pagination->limit == 20)?"selected=true":'';?>>20</option>
                    <option value="25" <?php echo ($this->pagination->limit == 25)?"selected=true":'';?>>25</option>
                    <option value="30" <?php echo ($this->pagination->limit == 30)?"selected=true":'';?>>30</option>
                    <option value="50" <?php echo ($this->pagination->limit == 50)?"selected=true":'';?>>50</option>
                    <option value="100" <?php echo ($this->pagination->limit == 100)?"selected=true":'';?>>100</option>
                </select>
            </div>
            <?php echo $this->pageNavigation; ?>
		</div>
	<?php else :?>
		<?php echo $this->datas; ?>
	<?php endif;?>
</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@8"></script>
<script type="text/javascript">
    $(document).ready(function(){
        $('#rt-mainbody-surround').children().addClass('mainemundus');
        $('#rt-main').children().addClass('mainemundus');
        $('#rt-main').children().children().addClass('mainemundus');
        $('.em-data-container').doubleScroll();
    });
    window.parent.$("html, body").animate({scrollTop : 0}, 300);
</script>
