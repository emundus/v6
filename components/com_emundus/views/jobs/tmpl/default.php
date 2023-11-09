<?php
/**
 * @version     1.0.0
 * @package     com_emundus
 * @copyright   Copyright (C) 2015. Tous droits réservés.
 * @license     GNU General Public License version 2 ou version ultérieure ; Voir LICENSE.txt
 * @author      emundus <dev@emundus.fr> - http://www.emundus.fr
 */
// no direct access
defined('_JEXEC') or die();


JHTML::_('script', 'system/multiselect.js', false, true);
// Import CSS
$document = JFactory::getDocument();
$app      = JFactory::getApplication();
$jinput   = $app->input;
$itemid   = $jinput->get('Itemid', 0, 'int');
//$document->addStyleSheet('components/com_emundus/assets/css/list.css');

$user       = JFactory::getUser();
$userId     = $user->get('id');
$listOrder  = $this->state->get('list.ordering');
$listDirn   = $this->state->get('list.direction');
$ordering   = ($listOrder == 'a.ordering');
$canCreate  = $user->authorise('core.create', 'com_emundus');
$canEdit    = $user->authorise('core.edit', 'com_emundus');
$canCheckin = $user->authorise('core.manage', 'com_emundus');
$canChange  = $user->authorise('core.edit.state', 'com_emundus');
$canDelete  = $user->authorise('core.delete', 'com_emundus');
$canView    = $user->authorise('core.viewjob', 'com_emundus');

//dropdown values for $item->domaine
$domaines = @EmundusHelperFiles::getElementsValuesOther(2262);
$values   = $domaines->sub_values;
$labels   = $domaines->sub_labels;
?>

<?php if ($user->guest): ?>
    <div class="alert alert-error">
		<?php echo JText::_('COM_EMUNDUS_JOBS_PLEASE_CONNECT_OR_LOGIN_TO_APPLY'); ?>
    </div>
<?php endif; ?>

<form action="<?php echo JURI::base(); ?>index.php?option=com_emundus&view=jobs" method="post" name="adminForm"
      id="adminForm">
	<?php echo $this->loadTemplate('filter'); ?>
	<?php if (count($this->items) == 0): ?>
        <div class="alert alert-warning">
            <b><?php echo JText::_('COM_EMUNDUS_JOBS_NO_RESULT'); ?></b>
        </div>
	<?php else: ?>
        <table class="front-end-list jobs">
            <thead>
            <tr>
                <th class="align-left">
					<?php echo JHtml::_('grid.sort', 'COM_EMUNDUS_JOBS_INTITULE_POSTE', 'a.intitule_poste', $listDirn, $listOrder); ?>
                </th>
                <th class="align-left">
					<?php echo JHtml::_('grid.sort', 'COM_EMUNDUS_JOBS_DOMAINE', 'a.domaine', $listDirn, $listOrder); ?>
                </th>
                <th class="align-left">
					<?php echo JHtml::_('grid.sort', 'COM_EMUNDUS_JOBS_ETABLISSEMENT', 'a.etablissement', $listDirn, $listOrder); ?>
                </th>
                <th class="align-left">
					<?php echo JHtml::_('grid.sort', 'COM_EMUNDUS_JOBS_SERVICE', 'a.service', $listDirn, $listOrder); ?>
                </th>
				<?php if (!$user->guest): ?>
                    <th class="align-center">
						<?php echo JHtml::_('grid.sort', 'COM_EMUNDUS_JOBS_ACTIONS', 'step', $listDirn, $listOrder); ?>
                    </th>
				<?php endif; ?>

            </tr>
            </thead>
            <tfoot>
            <tr>
                <td colspan="<?php echo isset($this->items[0]) ? count(get_object_vars($this->items[0])) : 10; ?>">
					<?php echo $this->pagination->getListFooter(); ?>
                </td>
            </tr>
            </tfoot>
            <tbody>
			<?php foreach ($this->items as $i => $item) : ?>
				<?php $canEdit = $user->authorise('core.edit', 'com_emundus'); ?>
				<?php if (!$canEdit && $user->authorise('core.edit.own', 'com_emundus')): ?>
					<?php $canEdit = JFactory::getUser()->id == $item->created_by; ?>
				<?php endif; ?>

                <tr class="row<?php echo $i % 2; ?>">
                    <td>
						<?php if (isset($item->checked_out) && $item->checked_out) : ?>
							<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'jobs.', $canCheckin); ?>
						<?php endif; ?>
                        <a class="em-job-detail" target="_blank"
                           href="<?php echo JURI::base() . 'index.php?option=com_emundus&view=job&id=' . (int) $item->id; ?>&Itemid=<?php echo $itemid; ?>">
                            <span class="glyphicon glyphicon-play"></span>
							<?php echo $item->intitule_poste; ?>
                        </a>
                    </td>
                    <td>
						<?php echo $labels[$item->domaine - 1]; ?>
                    </td>
                    <td>
						<?php echo $item->etablissement; ?>
                    </td>
                    <td>
						<?php echo $item->service; ?>
                    </td>
					<?php if (!$user->guest): ?>
                        <td class="align-left">
							<?php if ($canEdit || $canDelete): ?>
								<?php if ($canEdit): ?>
                                    <button onclick="this.disabled = true; window.location.href = '<?php echo JURI::base() . 'index.php?option=com_emundus&task=pdf_emploi&user=' . $item->user . '&rowid=' . $item->id; ?>';"
                                            class="btn-xs btn-mini"
                                            type="button"><?php echo JText::_('COM_EMUNDUS_JOBS_PDF'); ?></button>
								<?php endif; ?>
							<?php else: ?>
								<?php if ($item->student_id == $user->id): ?>
                                    <a href="<?php echo JURI::base() . 'index.php?option=com_emundus&controller=job&task=display&fnum=' . $item->fnum . '&id=' . $item->id; ?>"
                                       class="btn btn-success glyphicon glyphicon-eye-open"> <?php echo JText::_('COM_EMUNDUS_JOBS_DISPLAY'); ?></a>
                                    <button onclick="$('.btn').attr('disabled', true); window.location.href = '<?php echo JURI::base() . 'index.php?option=com_emundus&controller=job&task=cancel&fnum=' . $item->fnum . '&id=' . $item->id; ?>';"
                                            class="btn-xs btn-danger glyphicon glyphicon-trash" type="button"></button>
                                    <span class="label label-<?php echo $item->class; ?>"><?php echo $item->application_status; ?></span>
								<?php else: ?>
                                    <button onclick="$('.btn').attr('disabled', true); window.location.href = '<?php echo JURI::base() . 'index.php?option=com_emundus&controller=job&task=apply&id=' . $item->id; ?>';"
                                            class="btn btn-info glyphicon glyphicon-circle-arrow-right"
                                            type="button"> <?php echo JText::_('COM_EMUNDUS_JOBS_APPLY'); ?></button>
								<?php endif; ?>
							<?php endif; ?>
                        </td>
					<?php endif; ?>
                </tr>
			<?php endforeach; ?>
            </tbody>
        </table>
	<?php endif; ?>
    <div>
        <input type="hidden" name="task" value=""/>
        <input type="hidden" name="boxchecked" value="0"/>
        <input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
        <input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
		<?php echo JHtml::_('form.token'); ?>
    </div>
</form>

<div class="modal fade" id="largeModal" style="z-index:99999" tabindex="-1" role="dialog"
     aria-labelledby="em-modal-actions" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"
                    id="em-modal-actions-title"><?php echo JText::_('COM_EMUNDUS_JOBS_LOADING'); ?></h4>
            </div>
            <div class="modal-body">
                <img src="<?php echo JURI::base(); ?>media/com_emundus/images/icones/loader-line.gif">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-xs btn-danger"
                        data-dismiss="modal"><?php echo JText::_('COM_EMUNDUS_JOBS_CANCEL') ?></button>
            </div>
        </div>
    </div>
</div>


<script type="text/javascript">
    $(document).on('click', '.em-job', function (e) {
        e.preventDefault();
        var id = parseInt($(this).attr('id'));
        var url = $(this).attr('href');
        $('#largeModal').modal({backdrop: true}, 'toggle');

        $('.modal-title').empty();
        $('.modal-title').append($(this).children('a').text());
        $('.modal-body').empty();
        if ($('.modal-dialog').hasClass('modal-lg')) {
            $('.modal-dialog').removeClass('modal-lg');
        }
        $('.modal-body').attr('act-id', id);
        $('.modal-footer').show();


        $('.modal-footer').append('<div>' +
            '<img src="<?php echo JURI::base(); ?>media/com_emundus/images/icones/loader-line.gif" alt="loading" alt="loading"/>' +
            '</div>');
        $('.modal-footer').hide();

        $('.modal-dialog').addClass('modal-lg');
        $(".modal-body").empty();

        $(".modal-body").append('<iframe src="' + url + '" style="width:' + window.getWidth() * 0.9 + 'px; height:' + window.getHeight() * 0.9 + 'px; border:none"></iframe>');
    });


    if (typeof jQuery == 'undefined') {
        var headTag = document.getElementsByTagName("head")[0];
        var jqTag = document.createElement('script');
        jqTag.type = 'text/javascript';
        jqTag.src = '<?php echo JURI::base() . 'media/jui/js/jquery.min.js'; ?>';
        jqTag.onload = jQueryCode;
        headTag.appendChild(jqTag);
    } else {
        jQueryCode();
    }

    function jQueryCode() {
        jQuery('.delete-button').click(function () {
            var item_id = jQuery(this).attr('data-item-id');
            if (confirm("<?php echo JText::_('COM_EMUNDUS_DELETE_MESSAGE'); ?>")) {
                window.location.href = '<?php echo JURI::base(); ?> + "index.php?option=com_emundus&task=jobform.remove&id="' + item_id;
            }
        });
    }

    $('.sourcecoast.sclogin-modal-links.sclogin').remove();
</script>
