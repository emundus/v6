<?php
/**
 * Created by PhpStorm.
 * User: yoan
 * Date: 16/09/14
 * Time: 09:15
 */

require_once(JPATH_SITE . '/components/com_emundus/helpers/date.php');

$config        = JFactory::getConfig();
$config_offset = $config->get('offset');
$offset        = $config_offset ?: 'Europe/Paris';
$timezone      = new DateTimeZone($offset);
?>
<style>
    .em-cell .material-icons {
        font-size: 24px !important;
    }
</style>
<input type="hidden" id="view" name="view" value="users">
<?php if (!empty($this->users)) : ?>
    <div class="container-result">
        <div class="em-mt-8 em-ml-8">
			<?php echo $this->pagination->getResultsCounter(); ?>
        </div>
        <div id="countCheckedCheckbox" class="countCheckedCheckbox"></div>
    </div>

    <div class="em-data-container">
    <table class="table table-striped table-hover em-data-container-table" id="em-data">
    <thead>
    <tr>
		<?php foreach ($this->users[0] as $key => $v) : ?>
			<?php if ($key === 'id') : ?>
                <th id="checkuser">
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
                        <label for="em-check-all" class="em-w-100">
                            <input value="-1" id="em-check-all" type="checkbox" class="em-check"/>
                            <span id="span-check-all"><?= JText::_('COM_EMUNDUS_FILTERS_CHECK_ALL'); ?></span>
                        </label>
                        <label class="em-check-all-all em-w-100" for="em-check-all-all">
                            <input value="all" id="em-check-all-all" type="checkbox" class="em-check-all-all"/>
                            <span id="span-check-all-all"><?= JText::_('COM_EMUNDUS_FILTERS_CHECK_ALL_ALL'); ?></span>
                        </label>
                        <label class="em-check-none em-w-100" for="em-check-none">
                            <span id="span-check-none"><?= JText::_('COM_EMUNDUS_FILTERS_CHECK_NONE'); ?></span>
                        </label>
                    </div>
                <th id="<?php echo $key ?>">
                    <p class="em-cell">
						<?php if ($this->lists['order'] == $key) : ?>
							<?php if ($this->lists['order_dir'] == 'desc') : ?>
                                <span class="glyphicon glyphicon-sort-by-attributes-alt"></span>
							<?php else : ?>
                                <span class="glyphicon glyphicon-sort-by-attributes"></span>
							<?php endif; ?>
						<?php endif; ?>
                        <span>#</span>
                    </p>
                </th>
			<?php else : ?>
				<?php if (!in_array($key, ['active', 'is_applicant_profile'])) : ?>
                    <th id="<?php echo $key ?>">
						<?php if ($this->lists['order'] == $key) : ?>
                            <p class="em-cell">
								<?php if ($this->lists['order_dir'] == 'desc') : ?>
                                    <span class="glyphicon glyphicon-sort-by-attributes-alt"></span>
								<?php else : ?>
                                    <span class="glyphicon glyphicon-sort-by-attributes"></span>
								<?php endif; ?>
                                <strong><?php echo JText::_('COM_EMUNDUS_' . strtoupper($key)) ?></strong>
                            </p>
						<?php else : ?>
                            <p class="em-cell">
                                <strong><?php echo JText::_('COM_EMUNDUS_' . strtoupper($key)) ?></strong>
                            </p>
						<?php endif; ?>
                    </th>
				<?php endif; ?>
			<?php endif; ?>
		<?php endforeach; ?>
    </tr>
    </thead>
    <tbody>

	<?php foreach ($this->users as $l => $user) : ?>
        <tr>
		<?php foreach ($user as $k => $value) : ?>
			<?php if (!in_array($k, ['active', 'is_applicant_profile'])) : ?>
				<?php if ($k == 'id') : ?>
                    <td>
                        <div class="em-cell">
                            <input type="checkbox" name="<?php echo $value ?>_check" id="<?php echo $value ?>_check"
                                   class='em-check'/>
                        </div>
                    </td>
                    <td>
                        <div class="em-cell">
                            <label for="<?php echo $value ?>_check">
								<?php
								echo ($l * 1 + 1 + $this->pagination->limitstart) . '#' . $value;
								?>
                            </label>
                        </div>
                    </td>
				<?php elseif ($k == 'registerDate' || $k == 'lastvisitDate') : ?>
                    <td>
                        <div class="em-cell">
                            <label for="<?php echo $value ?>_check">
								<?php
								echo EmundusHelperDate::displayDate($value, 'COM_EMUNDUS_DATE_FORMAT', $timezone === 'UTC' ? 1 : 0);
								?>
                            </label>
                        </div>
                    </td>
				<?php elseif ($k == 'block') : ?>
					<?php if ($value == 0 && $user->active != -1) : ?>
                        <td>
                            <div class="em-cell">
                                <span class="material-icons" style="color:var(--main-500);"
                                      title="<?php echo JText::_('COM_EMUNDUS_USERS_ACTIVATE_ACCOUNT_SINGLE') ?>">verified</span>
                            </div>
                        </td>
					<?php elseif ($user->active == -1): ?>
                        <td>
                            <div class="em-cell">
                                <span class="material-icons em-yellow-500-color"
                                      title="<?php echo JText::_('COM_EMUNDUS_USERS_ACTIVATE_WAITING') ?>">new_releases</span>
                            </div>
                        </td>
					<?php else : ?>
                        <td>
                            <div class="em-cell">
                                <span class="material-icons em-red-500-color"
                                      title="<?php echo JText::_('COM_EMUNDUS_USERS_BLOCK_ACCOUNT_SINGLE') ?>">block</span>
                            </div>
                        </td>
					<?php endif; ?>
				<?php elseif ($k == 'profile') : ?>
                    <td>
						<?php if ($user->is_applicant_profile == 0) : ?>
                            <div class="em-cell">
								<?php echo $value; ?>
                            </div>
						<?php else : ?>
                            <div class="em-cell">
								<?= JText::_('COM_EMUNDUS_APPLICANT'); ?>
                            </div>
						<?php endif; ?>
                    </td>
				<?php elseif ($k == 'newsletter') : ?>
					<?php if ($value == 1) : ?>
                        <td>
                            <div class="em-cell">
								<?php echo JText::_('JYES'); ?>
                            </div>
                        </td>
					<?php else: ?>
                        <td>
                            <div class="em-cell">
								<?php echo JText::_('JNO'); ?>
                            </div>
                        </td>
					<?php endif; ?>
				<?php else: ?>
                    <td <?php if ($k == 'groupe') {
						echo 'class="em-cell-scroll"';
					} ?>>
                        <div class="em-cell">
							<?php echo $value; ?>
                        </div>
                    </td>
				<?php endif; ?>
                </div>
                </td>
			<?php endif; ?>
		<?php endforeach; ?>
        </tr>
	<?php endforeach; ?>
    </tbody>
    </table>
    </div>
    <div class="em-container-pagination">
        <label for="pager-select"
               class="em-container-pagination-label"><?php echo JText::_('COM_EMUNDUS_DISPLAY') ?></label>
        <select name="pager-select" class="chzn-select" id="pager-select">
            <option value="0" <?php if ($this->pagination->limit == 100000) {
				echo "selected=true";
			} ?>><?php echo JText::_('COM_EMUNDUS_ACTIONS_ALL') ?></option>
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
        <div class="em-container-pagination-selectPage">
            <ul class="pagination pagination-sm">
                <li><a href="#em-data" id="<?php echo $this->pagination->{'pagesStart'} ?>"><span
                                class='material-icons'>navigate_before</span></a></li>
				<?php if ($this->pagination->{'pagesTotal'} > 15) : ?>

					<?php for ($i = 1; $i <= 5; $i++) : ?>
                        <li <?php if ($this->pagination->{'pagesCurrent'} == $i) {
							echo 'class="active"';
						} ?>><a id="<?php echo $i; ?>" href="#em-data"><?php echo $i; ?></a></li>
					<?php endfor; ?>
                    <li class="disabled"><span>...</span></li>
					<?php if ($this->pagination->{'pagesCurrent'} <= 5) : ?>
						<?php for ($i = 6; $i <= 10; $i++) : ?>
                            <li <?php if ($this->pagination->{'pagesCurrent'} == $i) {
								echo 'class="active"';
							} ?>><a id="<?php echo $i; ?>" href="#em-data"><?php echo $i; ?></a></li>
						<?php endfor; ?>
					<?php else : ?>
						<?php for ($i = ($this->pagination->{'pagesCurrent'} - 2); $i <= ($this->pagination->{'pagesCurrent'} + 2); $i++) : ?>
							<?php if ($i <= $this->pagination->{'pagesTotal'}) : ?>
                                <li <?php if ($this->pagination->{'pagesCurrent'} == $i) {
									echo 'class="active"';
								} ?>><a id="<?= $i ?>" href="#em-data"><?= $i ?></a></li>
							<?php endif; ?>
						<?php endfor; ?>
					<?php endif; ?>
                    <li class="disabled"><span>...</span></li>
					<?php for ($i = ($this->pagination->{'pagesTotal'} - 4); $i <= $this->pagination->{'pagesTotal'}; $i++) : ?>
                        <li <?php if ($this->pagination->{'pagesCurrent'} == $i) {
							echo 'class="active"';
						} ?>><a id="<?php echo $i; ?>" href="#em-data"><?php echo $i; ?></a></li>
					<?php endfor; ?>
				<?php else : ?>
					<?php for ($i = 1; $i <= $this->pagination->{'pagesStop'}; $i++) : ?>
                        <li <?php if ($this->pagination->{'pagesCurrent'} == $i) {
							echo 'class="active"';
						} ?>><a id="<?php echo $i; ?>" href="#em-data"><?php echo $i; ?></a></li>
					<?php endfor; ?>
				<?php endif; ?>
                <li><a href="#em-data" id="<?php echo $this->pagination->{'pagesTotal'} ?>"><span
                                class='material-icons'>navigate_next</span></a></li>
            </ul>
        </div>
    </div>

<?php else : ?>
	<?php echo JText::_('COM_EMUNDUS_NO_RESULT'); ?>
<?php endif; ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@8"></script>
<script>
    $(document).ready(function () {
        const dataContainer = document.querySelector('.em-data-container')
        if (dataContainer) {
            DoubleScroll(dataContainer);
        }
    });
</script>

<script>
    $('.selectAll').css('display', 'none');
    $('.selectDropdown').click(function () {

        $('.selectContainer').removeClass('borderSelect');
        $('.selectAll').slideToggle(function () {

            if ($(this).is(':visible')) {

                $('.selectContainer').addClass('borderSelect');
                $(document).click(function (e) {

                    var container = $(".selectDropdown");

                    if (!container.is(e.target) && container.has(e.target).length === 0) {
                        $('.selectAll').slideUp();
                        $('.selectContainer').removeClass('borderSelect');
                    }
                });
            }
        });
    });

    $('.selectAll>span').off('click');
    $('.selectAll>span').click(function () {
        $('.selectAll').slideUp();
    });

    $('#span-check-all-all').off('click');
    $('#span-check-all-all').click(function () {
        $('.selectAll.em-check-all-all#em-check-all-all').prop('checked', true);// all
        //$('.em-check#em-check-all').prop('checked',true);//.selectPage Page
        //$('.em-check-all#em-check-all').prop('checked',true);//.selectAll Page
        $('.em-check').prop('checked', true);
        reloadActions('files', undefined, true);
    });

    $('#span-check-none').off('click');
    $('#span-check-none').click(function () {
        $('#em-check-all-all').prop('checked', false);
        $('.em-check#em-check-all').prop('checked', false);
        $('.em-check-all#em-check-all').prop('checked', false);
        $('.em-check').prop('checked', false);
        $('#countCheckedCheckbox').html('');
        reloadActions('files', undefined, false);
    });

    $('.em-check, .em-check-all-all').off('change');
    $(document).on('change', '.em-check, .em-check-all-all', function () {

        let countCheckedCheckbox = $('.em-check').not('#em-check-all.em-check,#em-check-all-all.em-check ').filter(':checked').length;
        let allCheck = $('.em-check-all-all#em-check-all-all').is(':checked');
        let nbChecked = allCheck == true ? Joomla.JText._('COM_EMUNDUS_FILTERS_SELECT_ALL') : countCheckedCheckbox;
        //console.log(countCheckedCheckbox);
        let files = countCheckedCheckbox === 1 ? Joomla.JText._('COM_EMUNDUS_USERS_SELECT_USER') : Joomla.JText._('COM_EMUNDUS_USERS_SELECT_USERS');
        if (countCheckedCheckbox !== 0) {
            $('#countCheckedCheckbox').html('<p>' + Joomla.JText._('COM_EMUNDUS_FILTERS_YOU_HAVE_SELECT') + nbChecked + ' ' + files + '</p>');
        } else {
            $('#countCheckedCheckbox').html('');
        }

    });
</script>
