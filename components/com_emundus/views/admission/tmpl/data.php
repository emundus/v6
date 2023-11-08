<?php
/**
 * @version        $Id: data.php 14401 2014-09-16 14:10:00Z brivalland $
 * @package        Joomla
 * @subpackage     Emundus
 * @copyright      Copyright (C) 2005 - 2015 eMundus SAS. All rights reserved.
 * @license        GNU/GPL, see LICENSE.php
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
	<?php if (is_array($this->datas)) : ?>
        <div class="container-result">
            <div class="em-ml-8 em-flex-row">
				<?= $this->pagination->getResultsCounter(); ?>
                <div class="em-ml-16">|</div>
                <div class="em-ml-16 em-flex-row">
                    <label for="pager-select"
                           class="em-mb-0-important em-mr-4"><?= JText::_('COM_EMUNDUS_DISPLAY') ?></label>
                    <select name="pager-select" id="pager-select" class="em-select-no-border">
                        <option value="0" <?php if ($this->pagination->limit == 0) {
							echo "selected=true";
						} ?>><?= JText::_('COM_EMUNDUS_ACTIONS_ALL') ?></option>
                        <option value="5" <?php if ($this->pagination->limit == 5) {
							echo "selected=true";
						} ?>>5
                        </option>
                        <option value="10" <?php if ($this->pagination->limit == 10) {
							echo "selected=true";
						} ?>>10
                        </option>
                        <option value="15" <?php if ($this->pagination->limit == 15) {
							echo "selected=true";
						} ?>>15
                        </option>
                        <option value="20" <?php if ($this->pagination->limit == 20) {
							echo "selected=true";
						} ?>>20
                        </option>
                        <option value="25" <?php if ($this->pagination->limit == 25) {
							echo "selected=true";
						} ?>>25
                        </option>
                        <option value="30" <?php if ($this->pagination->limit == 30) {
							echo "selected=true";
						} ?>>30
                        </option>
                        <option value="50" <?php if ($this->pagination->limit == 50) {
							echo "selected=true";
						} ?>>50
                        </option>
                        <option value="100" <?php if ($this->pagination->limit == 100) {
							echo "selected=true";
						} ?>>100
                        </option>
                    </select>
                </div>
            </div>
			<?php echo $this->pageNavigation ?>
            <div id="countCheckedCheckbox" class="countCheckedCheckbox" style="display: none"></div>
        </div>
        <div class="em-data-container" style="padding-bottom: unset">
            <table class="table table-striped table-hover" id="em-data">
                <thead>
                <tr>
					<?php foreach ($this->datas[0] as $kl => $v) : ?>
						<?php if ($kl == "jos_emundus_final_grade.user") : ?>
                            <!-- Skips extra collumn -->
						<?php else : ?>
                            <th title="<?php echo JText::_(strip_tags($v)); ?>" id="<?php echo $kl; ?>">
                                <p class="em-cell">
									<?php if ($kl == 'check') : ?>
                                <div class="selectContainer" id="selectContainer">
                                    <div class="selectPage">
                                        <input type="checkbox" value="-1" id="em-check-all" class="em-hide em-check">
                                        <label for="em-check-all" class="check-box"></label>
                                    </div>
                                    <div class="selectDropdown" id="selectDropdown">
                                        <i class="fas fa-sort-down"></i>
                                    </div>

                                </div>
                                <div class="selectAll" id="selectAll_admission">
                                    <label>
                                        <input value="-1" id="em-check-all-page" class="em-check-all-page"
                                               type="checkbox"/>
                                        <span id="span-check-all"><?= JText::_('COM_EMUNDUS_FILTERS_CHECK_ALL'); ?></span>
                                    </label>
                                    <label class="em-check-all-all" for="em-check-all-all">
                                        <input value="all" id="em-check-all-all" type="checkbox"
                                               class="em-check-all-all"/>
                                        <span id="span-check-all-all"><?= JText::_('COM_EMUNDUS_FILTERS_CHECK_ALL_ALL'); ?></span>
                                    </label>
                                    <label class="em-check-none" for="em-check-none">
                                        <span id="span-check-none"><?= JText::_('COM_EMUNDUS_FILTERS_CHECK_NONE'); ?></span>
                                    </label>
                                </div>
								<?php elseif (@$this->lists['order'] == $kl) : ?>
									<?php if (@$this->lists['order_dir'] == 'desc') : ?>
                                        <span class="glyphicon glyphicon-sort-by-attributes-alt"></span>
									<?php else : ?>
                                        <span class="glyphicon glyphicon-sort-by-attributes"></span>
									<?php endif; ?>
                                    <strong>
										<?php echo JText::_($v); ?>
                                    </strong>
								<?php else : ?>
									<?php echo JText::_($v); ?>
								<?php endif; ?>
                                </p>
                            </th>
						<?php endif; ?>
					<?php endforeach; ?>
                </tr>
                </thead>
                <tbody>

				<?php foreach ($this->datas as $key => $line) : ?>
					<?php if ($key != 0) : ?>
                        <tr>
							<?php $cfnum = $line['fnum']->val; ?>
							<?php foreach ($line as $k => $value) : ?>
                                <td <?php echo ($k == 'check' && $value->class != null) ? 'class="' . $value->class . '"' : ''; ?>>
                                    <div class="em-cell">
										<?php if ($k == 'check') : ?>
                                            <label for="<?php echo $line['fnum']->val; ?>_check">
                                                <input type="checkbox" name="<?php echo $line['fnum']->val; ?>_check"
                                                       id="<?php echo $line['fnum']->val; ?>_check" class='em-check'
                                                       style="width:20px !important;"/>
												<?php
												$tab = explode('-', $key);
												echo($tab[1] + 1 + $this->pagination->limitstart);
												?>
                                            </label>
										<?php elseif ($k == 'status') : ?>
                                            <span class="label label-<?php echo $value->status_class; ?>"
                                                  title="<?php echo $value->val; ?>"><?php echo $value->val; ?></span>
										<?php elseif ($k == 'fnum') : ?>
                                            <a href="#<?php echo $value->val ?>|open" id="<?php echo $value->val; ?>"
                                               class="em_file_open">
												<?php if (isset($value->photo) && !$anonymize_data) : ?>
                                                    <div class="em_list_photo"><?= $value->photo; ?></div>
												<?php endif; ?>
                                                <div class="em_list_text">
													<?php if ($anonymize_data) : ?>
                                                        <div class="em_list_fnum"><?= $value->val; ?></div>
													<?php else : ?>
                                                        <span class="em_list_text"
                                                              title="<?= $value->val; ?>"> <strong> <?= $value->user->name; ?></strong></span>
                                                        <div class="em_list_email"><?= $value->user->email; ?></div>
                                                        <div class="em_list_email"><?= $value->user->id; ?></div>
													<?php endif; ?>
                                                </div>
                                            </a>
										<?php elseif ($k == "access") : ?>
											<?php echo $this->accessObj[$line['fnum']->val]; ?>
										<?php elseif ($k == "id_tag") : ?>
											<?php echo @$this->colsSup['id_tag'][$line['fnum']->val]; ?>
										<?php elseif (array_key_exists($k, $this->colsSup)) : ?>
											<?= @$this->colsSup[$k][$line['fnum']->val]; ?>
										<?php else: ?>

											<?php if ($value->type == 'text') : ?>
												<?php echo strip_tags($value->val); ?>
											<?php elseif ($value->type == 'textarea' && EmundusHelperAccess::asAccessAction(34, 'u', $value->user->id)) : ?>
                                                <textarea class="input-medium em-cell-textarea"
                                                          id="<?php echo $cfnum . '-' . $value->id; ?>"><?php echo $value->val; ?></textarea>
                                                <span class="glyphicon glyphicon-share-alt em-textarea"
                                                      id="<?php echo $cfnum . '-' . $value->id . '-span'; ?>"
                                                      aria-hidden="true" style="color:black;"></span>
											<?php elseif ($value->type == 'date') : ?>
                                                <h5 class="em-date">
                                                    <strong>
														<?php if (!isset($value->val) || $value->val == "0000-00-00 00:00:00") : ?>
                                                            <span class="em-radio"
                                                                  id="<?php echo $cfnum . '-' . $value->id . '-' . $value->val; ?>"
                                                                  aria-hidden="true"></span>
														<?php else: ?>
															<?php
															$params         = json_decode($value->params);
															$formatted_date = DateTime::createFromFormat('Y-m-d H:i:s', $value->val);
															echo $formatted_date->format($params->date_form_format);
															?>
														<?php endif; ?>
                                                    </strong>
                                                </h5>
											<?php elseif ($value->type == 'radiobutton') : ?>
                                                <select name="<?php echo $cfnum . '-' . $value->id; ?>"
                                                        class="em-radio input-medium"
                                                        id="<?php echo $cfnum . '-' . $value->id; ?>"
													<?php
													if (strtolower($value->val) == "yes" || strtolower($value->val) == "oui" || $value->val == 1) {
														echo "style='border: solid 3px #BCCB56'";
													}
                                                    elseif (strtolower($value->val) == "no" || strtolower($value->val) == "non" || $value->val === 0) {
														echo "style='border: solid 3px #E09541'";
													}
                                                    elseif (!empty($value->val)) {
														echo "style='border: solid 3px #49A0CD'";
													}
													?>
                                                >
													<?php if (!isset($value->val)) : ?>
                                                        <option value="" disabled="disabled"
                                                                selected="selected"> <?php echo JText::_('COM_EMUNDUS_PLEASE_SELECT'); ?> </option>
													<?php endif; ?>
													<?php foreach ($value->radio as $rlabel => $rval) : ?>
                                                        <option value="<?php echo $rval; ?>" <?php echo ($value->val == $rlabel) ? "selected=true" : '' ?>> <?php echo JText::_($rlabel); ?> </option>
													<?php endforeach; ?>
                                                </select>
											<?php elseif ($value->type == 'field') : ?>
                                                <input class="admission_input" type="text"
                                                       id="<?php echo $cfnum . '-' . $value->id; ?>"
                                                       name="<?php echo $value->val; ?>"
                                                       value="<?php echo $value->val; ?>"></input>
                                                <span class="glyphicon glyphicon-share-alt em-field"
                                                      id="<?php echo $cfnum . '-' . $value->id . '-span'; ?>"
                                                      aria-hidden="true" style="color:black;"></span>
											<?php elseif ($value->type == 'fileupload') : ?>
												<?php if (!empty($value->val) && $value->val != "/") : ?>
                                                    <a href="<?php echo $value->val ?>"
                                                       target="_blank"> <?php echo JText::_('COM_EMUNDUS_ATTACHMENTS_LINK_TO_DOWNLOAD'); ?>
                                                        <span class="glyphicon glyphicon-save"></span> </a>
												<?php else : ?>
                                                    <p> No File </p>
												<?php endif; ?>
											<?php else : ?>
												<?php echo $value->val; ?>
											<?php endif; ?>

										<?php endif; ?>
                                    </div>
                                </td>
							<?php endforeach; ?>
                        </tr>
					<?php endif; ?>
				<?php endforeach; ?>
                </tbody>
            </table>
        </div>
	<?php else : ?>
		<?php echo $this->datas; ?>
	<?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@8"></script>
<script type="text/javascript">
    function checkurl() {
        var url = $(location).attr('href');
        var menuAction = null;
        var headerNav = null;
        var containerResult = null;

        url = url.split("#");
        $('.alert.alert-warning').remove();

        if (url[1] != null && url[1].length >= 20) {
            url = url[1].split("|");
            var fnum = {};
            fnum.fnum = url[0];

            if (fnum.fnum != null && fnum.fnum !== "close") {
                addLoader();
                $('#' + fnum.fnum + '_check').prop('checked', true);

                $.ajax({
                    type: 'get',
                    url: 'index.php?option=com_emundus&controller=files&task=getfnuminfos',
                    async: true,
                    dataType: "json",
                    data: ({fnum: fnum.fnum}),
                    success: function (result) {
                        if (result.status && result.fnumInfos != null) {
                            var fnumInfos = result.fnumInfos;
                            fnum.name = fnumInfos.name;
                            fnum.label = fnumInfos.label;
                            openFiles(fnum);
                        } else {
                            removeLoader();
                            $(".panel.panel-default").prepend("<div class=\"alert alert-warning\"><?= JText::_('COM_EMUNDUS_APPLICATION_CANNOT_OPEN_FILE') ?></div>");
                        }
                    },
                    error: function (jqXHR) {
                        removeLoader();
                        $("<div class=\"alert alert-warning\"><?= JText::_('COM_EMUNDUS_APPLICATION_CANNOT_OPEN_FILE') ?></div>").prepend($(".panel.panel-default"));
                        console.log(jqXHR.responseText);
                    }
                })

            }
        } else {
            $('.em-close-minimise').remove();
        }

    }

    $(document).ready(function () {
        checkurl();
        $('#rt-mainbody-surround').children().addClass('mainemundus');
        $('#rt-main').children().addClass('mainemundus');
        $('#rt-main').children().children().addClass('mainemundus');

        menuAction = document.querySelector('.em-menuaction');
        headerNav = document.querySelector('#g-navigation .g-container');
        containerResult = document.querySelector('.container-result');
        setTimeout(() => {
            $('.container-result').css('top', (headerNav.offsetHeight + menuAction.offsetHeight) + 'px');
            $('#em-data th').css('top', (headerNav.offsetHeight + menuAction.offsetHeight + containerResult.offsetHeight) + 'px');
        }, 2000);

        const dataContainer = document.querySelector('.em-data-container')
        if (dataContainer) {
            DoubleScroll(dataContainer);
        }
    });
    window.parent.$("html, body").animate({scrollTop: 0}, 300);

</script>


<script>
    const selectDropdownContainer = document.querySelector('#selectAll_admission');
    const countFiles = document.querySelector('#countCheckedCheckbox');
    selectDropdownContainer.style.display = 'none';

    $('.selectDropdown').click(function () {
        if (selectDropdownContainer.style.display === 'none') {
            selectDropdownContainer.style.display = 'flex';
        } else {
            selectDropdownContainer.style.display = 'none';
        }
    });

    $(document).click(function (e) {
        var container = $(".selectDropdown");

        if (!container.is(e.target) && container.has(e.target).length === 0) {
            selectDropdownContainer.style.display = 'none';
        }
    });

    function checkAllFiles() {
        $('#em-check-all-all').prop('checked', true);

        selectAllFiles();
    }

    function displayCount() {
        countFiles.style.display = 'block';
        countFiles.style.backgroundColor = '#EDEDED';
        $('#em-data th').css('top', (headerNav.offsetHeight + menuAction.offsetHeight + containerResult.offsetHeight) + 'px');
    }

    function hideCount() {
        countFiles.style.display = 'none';
        $('#em-data th').css('top', (headerNav.offsetHeight + menuAction.offsetHeight + containerResult.offsetHeight) + 'px');
        countFiles.style.backgroundColor = 'transparent';
        $('.em-close-minimise').remove();
    }

    function selectAllFiles() {
        let allCheck = $('.em-check-all-all#em-check-all-all').is(':checked');

        if (allCheck === true) {
            $('.em-check-all-page#em-check-all-page').prop('checked', false);
            $('.em-check').prop('checked', true);

            displayCount();
            countFiles.innerHTML = '<p>' + Joomla.JText._('COM_EMUNDUS_FILTERS_YOU_HAVE_SELECT') + Joomla.JText._('COM_EMUNDUS_FILTERS_SELECT_ALL') + Joomla.JText._('COM_EMUNDUS_FILES_FILES') + '</p>';

            document.querySelector('.selectContainer').style.backgroundColor = '#F3F3F3';

            reloadActions('files', undefined, true);

        } else {
            $('.em-check').prop('checked', false);

            hideCount();
            countFiles.innerHTML = '';

            document.querySelector('.selectContainer').style.backgroundColor = 'transparent';

            reloadActions('files', undefined, false);
        }
    }


    $('#selectAll_admission>span').click(function () {
        $('#selectAll_admission').slideUp();
    });

    $('#span-check-none').click(function () {
        $('#em-check-all-all').prop('checked', false);
        $('.em-check#em-check-all').prop('checked', false);
        $('.em-check-all#em-check-all').prop('checked', false);
        $('.em-check').prop('checked', false);
        $('.nav.navbar-nav').hide();
        hideCount();
        countFiles.innerHTML = '';
        reloadActions('files', undefined, false);

        document.querySelector('.selectContainer').style.backgroundColor = 'transparent';
    });

    $(document).on('change', '.em-check-all-all', function (e) {
        selectAllFiles();
    })

    $(document).on('change', '.em-check-all-page,.selectPage #em-check-all', function (e) {
        let pageCheckAll = $('.selectPage #em-check-all').is(':checked');
        let is_checked = false;

        if (e.target.id === 'em-check-all') {
            if (pageCheckAll === false) {
                $('.em-check-all-page#em-check-all-page').prop('checked', false);
            } else {
                is_checked = true;
            }
        }

        let pageCheck = $('.em-check-all-page#em-check-all-page').is(':checked');

        if (e.target.id === 'em-check-all-page') {
            if (pageCheck === false) {
                $('.selectPage #em-check-all').prop('checked', false);
            } else {
                is_checked = true;
            }
        }

        if (is_checked) {
            $('.em-check-all-all#em-check-all-all').prop('checked', false);
            $('.em-check').prop('checked', true);

            let countCheckedCheckbox = $('.em-check').not('#em-check-all.em-check,#em-check-all-all.em-check').filter(':checked').length;
            let files = countCheckedCheckbox === 1 ? Joomla.JText._('COM_EMUNDUS_FILES_FILE') : Joomla.JText._('COM_EMUNDUS_FILES_FILES');

            if (countCheckedCheckbox !== 0) {
                displayCount();
                countFiles.innerHTML = '<p>' + Joomla.JText._('COM_EMUNDUS_FILTERS_YOU_HAVE_SELECT') + countCheckedCheckbox + ' ' + files + '. <a class="em-pointer" onclick="checkAllFiles()">' + Joomla.JText._('COM_EMUNDUS_FILES_SELECT_ALL_FILES') + '</a></p>';
            } else {
                hideCount();
                countFiles.innerHTML = '';
            }

            document.querySelector('.selectContainer').style.backgroundColor = '#F3F3F3';

        } else {
            $('.em-check').prop('checked', false);
            hideCount();
            countFiles.innerHTML = '';

            document.querySelector('.selectContainer').style.backgroundColor = 'transparent';
        }

        if (e.target.id === 'em-check-all-page') {
            if (is_checked) {
                reloadActions('files', undefined, true);
            } else {
                reloadActions('files', undefined, false);
            }
        }
    })

    $(document).on('change', '.em-check', function (e) {
        let countCheckedCheckbox = $('.em-check').not('#em-check-all.em-check,#em-check-all-all.em-check').filter(':checked').length;
        let files = countCheckedCheckbox === 1 ? Joomla.JText._('COM_EMUNDUS_FILES_FILE') : Joomla.JText._('COM_EMUNDUS_FILES_FILES');

        if (countCheckedCheckbox !== 0) {
            displayCount();
            countFiles.innerHTML = '<p>' + Joomla.JText._('COM_EMUNDUS_FILTERS_YOU_HAVE_SELECT') + countCheckedCheckbox + ' ' + files + '. <a class="em-pointer" onclick="checkAllFiles()">' + Joomla.JText._('COM_EMUNDUS_FILES_SELECT_ALL_FILES') + '</a></p>';
        } else {
            hideCount();
            countFiles.innerHTML = '';
        }
    });
</script>
