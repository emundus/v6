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


JHTML::_('behavior.tooltip');
JHTML::_('script', 'system/multiselect.js', false, true);
// Import CSS
$document = JFactory::getDocument();
$document->addStyleSheet('components/com_emundus/assets/css/list.css');

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
$canView    = $user->authorise('core.viewthesis', 'com_emundus');
?>

<?php if ($user->guest): ?>
    <div class="alert alert-warning">
        <b><?php echo JText::_('WARNING'); ?>
            ! </b> <?php echo JText::_('COM_EMUNDUS_THESIS_PLEASE_CONNECT_OR_LOGIN_TO_APPLY'); ?>
    </div>

    <div class="row">
        <div class="col-md-5">
            <div class="rt-block box1">
                <div class="module-surround">
                    <div class="module-title">
                        <h2 class="title"><?php echo JText::_('COM_EMUNDUS_THESIS_LOGIN'); ?></h2>
                    </div>
                    <div class="module-content">


                        <div class="custombox1">
                            <p><?php echo JText::_('COM_EMUNDUS_THESIS_LOGIN_DESC'); ?></p>

                            <a class="readon"
                               href="index.php?option=com_users&view=login&Itemid=<?php echo $this->itemid; ?>"><?php echo JText::_('COM_EMUNDUS_THESIS_LOGIN_BTN'); ?></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="rt-block box1">
                <div class="module-surround">
                    <div class="module-title">
                        <h2 class="title"><?php echo JText::_('COM_EMUNDUS_THESIS_REGISTER'); ?></h2>
                    </div>
                    <div class="module-content">


                        <div class="custombox1">
                            <p><?php echo JText::_('COM_EMUNDUS_THESIS_REGISTER_DESC'); ?></p>

                            <a class="readon"
                               href="index.php?option=com_users&view=registration&course=csc&Itemid=<?php echo $this->itemid; ?>"><?php echo JText::_('COM_EMUNDUS_THESIS_REGISTER_BTN'); ?></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php endif; ?>
<br>
<blockquote>
    <p><?php echo JText::_('COM_EMUNDUS_THESIS_HEADER_INFO'); ?></p>
</blockquote>

<form action="<?php echo JRoute::_('index.php?option=com_emundus&view=thesiss'); ?>" method="post" name="adminForm"
      id="adminForm">
	<?php echo $this->loadTemplate('filter'); ?>
	<?php if (count($this->items) == 0): ?>
        <div class="alert alert-warning">
            <b><?php echo JText::_('COM_EMUNDUS_THESIS_NO_RESULT'); ?></b>
        </div>
	<?php else: ?>
        <table class="table table-hover">
            <thead>
            <tr>
                <th class="align-left">
					<?php echo JHtml::_('grid.sort', 'COM_EMUNDUS_THESIS_PROPOSAL', 'a.titre', $listDirn, $listOrder); ?>
                </th>
                <th class="align-left">
					<?php echo JHtml::_('grid.sort', 'COM_EMUNDUS_THESIS_ED', 'a.doctoral_school', $listDirn, $listOrder); ?>
                </th>
                <th class="align-left">
					<?php echo JHtml::_('grid.sort', 'COM_EMUNDUS_THESIS_DIRECTOR', 'user.name', $listDirn, $listOrder); ?>
                </th>

				<?php if (!$user->guest): ?>
                    <th class="align-center">
						<?php echo JHtml::_('grid.sort', 'COM_EMUNDUS_THESIS_ACTIONS', 'step', $listDirn, $listOrder); ?>
                    </th>
				<?php endif; ?>

            </tr>
            </thead>
            <tfoot>
            <tr>
                <td colspan="<?php echo isset($this->items[0]) ? count(get_object_vars($this->items[0])) : 10; ?>">
					<?php
					echo $this->pagination->getListFooter();
					echo $this->pagination->getResultsCounter();
					?>
                </td>
            </tr>
            </tfoot>
            <tbody>
			<?php foreach ($this->items as $i => $item) : ?>
				<?php $canEdit = $user->authorise('core.edit', 'com_emundus'); ?>
				<?php if (!$canEdit && $user->authorise('core.edit.own', 'com_emundus')): ?>
					<?php $canEdit = JFactory::getUser()->id == $item->created_by; ?>
				<?php endif; ?>

                <tr class="<?php echo $item->student_id == $user->id ? $item->class : ''; ?>">
                    <td>
						<?php if (isset($item->checked_out) && $item->checked_out) : ?>
							<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'thesiss.', $canCheckin); ?>
						<?php endif; ?>
                        <a class="em-thesis"
                           href="<?php echo JRoute::_('index.php?option=com_emundus&view=thesis&id=' . (int) $item->id); ?>&tmpl=component">
                            <span class="glyphicon glyphicon-play"></span>
							<?php echo $item->titre; ?>
                        </a>
                    </td>
                    <td>
						<?php echo $item->domain; ?>
                    </td>
                    <td>
						<?php echo $item->thesis_supervisor; ?>
                    </td>
					<?php if (!$user->guest): ?>
                        <td class="align-left">
                            <a href='<?php echo JRoute::_('index.php?option=com_emundus&task=pdf_thesis&user=' . $item->user . '&rowid=' . $item->id, false, 2); ?>'
                               ;" class="glyphicon glyphicon-file"
                            target="_blank"><?php echo JText::_('COM_EMUNDUS_THESIS_PDF'); ?></a><br>
							<?php if ($canEdit || $canDelete): ?>
								<?php if ($canEdit): ?>
                                    <button onclick="this.disabled = true; window.location.href = '<?php echo JRoute::_('index.php?option=com_emundus&task=pdf_thesis&user=' . $item->user . '&rowid=' . $item->id, false, 2); ?>';"
                                            class="btn-xs btn-mini"
                                            type="button"><?php echo JText::_('COM_EMUNDUS_THESIS_PDF'); ?></button>
								<?php endif; ?>
							<?php else: ?>
								<?php if ($item->student_id == $user->id): ?>
                                    <button onclick="$('.btn').attr('disabled', true); window.location.href = '<?php echo JRoute::_('index.php?option=com_emundus&controller=thesis&task=display&fnum=' . $item->fnum . '&id=' . $item->id, false, 2); ?>';"
                                            class="btn-xs btn-success glyphicon glyphicon-eye-open"
                                            type="button"> <?php echo JText::_('COM_EMUNDUS_THESIS_DISPLAY'); ?></button>
                                    <button onclick="$('.btn').attr('disabled', true); window.location.href = '<?php echo JRoute::_('index.php?option=com_emundus&controller=thesis&task=cancel&fnum=' . $item->fnum . '&id=' . $item->id, false, 2); ?>';"
                                            class="btn-xs btn-danger glyphicon glyphicon-trash" type="button"></button>
                                    <span class="label label-<?php echo $item->class; ?>"><?php echo $item->application_status; ?></span>
								<?php elseif (!$this->thesis_selected): ?>
                                    <button onclick="$('.btn').attr('disabled', true); window.location.href = '<?php echo JRoute::_('index.php?option=com_emundus&controller=thesis&task=apply&id=' . $item->id, false, 2); ?>';"
                                            class="btn-xs btn-info glyphicon glyphicon-circle-arrow-right"
                                            type="button"> <?php echo JText::_('COM_EMUNDUS_THESIS_APPLY'); ?></button>
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
                    id="em-modal-actions-title"><?php echo JText::_('COM_EMUNDUS_THESIS_LOADING'); ?></h4>
            </div>
            <div class="modal-body">
                <img src="<?php echo JURI::base(); ?>media/com_emundus/images/icones/loader-line.gif">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-xs btn-danger"
                        data-dismiss="modal"><?php echo JText::_('COM_EMUNDUS_THESIS_CANCEL') ?></button>
            </div>
        </div>
    </div>
</div>


<script type="text/javascript">
    $(document).on('click', '.em-thesis', function (e) {
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

        $(".modal-body").append('<iframe src="' + url + '" style="width:' + window.getWidth() * 0.8 + 'px; height:' + window.getHeight() * 0.8 + 'px; border:none"></iframe>');
    });


    if (typeof jQuery == 'undefined') {
        var headTag = document.getElementsByTagName("head")[0];
        var jqTag = document.createElement('script');
        jqTag.type = 'text/javascript';
        //jqTag.src = '<?php echo JURI::base() . 'media/com_emundus/lib/jquery-1.10.2.min.js'; ?>';
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
                window.location.href = '<?php echo JRoute::_('index.php?option=com_emundus&task=thesisform.remove&id=', false, 2) ?>' + item_id;
            }
        });
    }

    $('.sourcecoast.sclogin-modal-links.sclogin').remove();
</script>
