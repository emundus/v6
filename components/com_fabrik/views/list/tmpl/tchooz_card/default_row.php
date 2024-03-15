<?php
/**
 * Fabrik List Template: Admin Row
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2020  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */
// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Session\Session;

$tags = [];
$title = '';
$description_fields = [];

$detail_url = $this->params->get('detailurl', '');
$detail_label = $this->params->get('detaillabel', JText::_('COM_FABRIK_DETAILSLABEL'));
$edit_url = $this->params->get('editurl', '');
$edit_label = $this->params->get('editlabel', JText::_('COM_FABRIK_EDITLABEL'));

$not_displayed_headings = ['fabrik_select', 'fabrik_actions'];

$show_delete_btn = strpos($this->_row->data->fabrik_actions, 'delete') !== false;
$show_edit_btn = strpos($this->_row->data->fabrik_actions, 'fabrik_edit') !== false;
$show_details_btn = strpos($this->_row->data->fabrik_actions, 'fabrik_view') !== false;

foreach ($this->headings as $heading => $label) {
    if (in_array($heading, $not_displayed_headings)) {
        continue;
    }

    if (!empty($this->cellClass[$heading]['class'])) {
        if (strpos($this->cellClass[$heading]['class'], 'list-tags') !== false) {
            $tags[] = $this->_row->data->$heading;
        } elseif (strpos($this->cellClass[$heading]['class'], 'list_material_icons') !== false) {
            $description_fields[] = $heading;
        } else {
            if (strpos($this->cellClass[$heading]['class'], 'list-title') !== false) {
                $title = $this->_row->data->$heading;
            } else {
                $description_fields[] = $heading;
            }
        }
    } else {
        $description_fields[] = $heading;
    }
}

?>
<div class="hover-and-tile-container">
	<?php if (!$this->showFilters) : ?><div id="tile-hover-offset-procedure"></div><?php endif; ?>
    <div id="<?php echo $this->_row->id; ?>"
         class="mod_emundus_campaign__list_content em-border-neutral-300 em-pointer em-flex-space-between" onclick="window.location.href='<?= $this->_row->data->fabrik_view_url; ?>'">
        <div id="background-shapes" alt="Fond formes"
             style="mask-image: url('/modules/mod_emundus_campaign/assets/fond-clair.svg');"></div>
        <div class="mod_emundus_campaign__list_content_container em-w-100">
          <?php if(!$this->showFilters) : ?>
            <p class="em-programme-tag" style="color: #0A53CC;"> <?php echo implode(', ', $tags); ?></p>
	        <?php endif; ?>
            <div class="em-flex-row em-flex-space-between em-mb-12">
                <h4 title="<?php echo $title; ?>"><?php echo $title; ?></h4>

	            <?php if(!$this->showFilters) : ?>
                <div class="all-actions-container">
                    <span onclick="toggleActions('<?= $this->_row->id; ?>')"
                          class="material-icons-outlined toggle-actions-btn">more_vert</span>
                </div>
	            <?php endif; ?>
            </div>

	        <?php if($this->showFilters) : ?>
                    <p title="<?php echo implode(', ', $tags); ?>" class="catalogue_tag"><?php echo implode(', ', $tags); ?></p>
	        <?php endif; ?>

            <?php if(!$this->showFilters) : if (!empty($tags) || !empty($title)) : ?>
                 <hr>
            <?php endif; endif; ?>


            <div class="em-text-neutral-600">
                <?php
                foreach ($this->headings as $heading => $label) {
                    if (in_array($heading, $description_fields)) {
                        $icon = '';
                        $style = empty($this->cellClass[$heading]['style']) ? '' : 'style="' . $this->cellClass[$heading]['style'] . '"';
                        if (preg_match('/list_material_icons-([a-z_]+)/', $this->cellClass[$heading]['class'], $matches)) {
                            $icon = $matches[1];
                        }

                        ?>
                        <div class="<?php echo $this->cellClass[$heading]['class'] ?> flex em-flex-align-start" <?php echo $style ?>>
                            <?php if (!empty($icon)) : ?>
                                <span class="material-icons-outlined mr-2"><?php echo $icon ?></span>
                            <?php endif; ?>

                            <p title="<?php echo isset($this->_row->data) ? $this->_row->data->$heading : ''; ?>" class="em-neutral-700-color"><?php if($this->showFilters && !empty($label)): ?><?php echo $label ?> <?php endif; ?><?php echo isset($this->_row->data) ? $this->_row->data->$heading : ''; ?></p>
                        </div>
                        <?php
                    }
                }
                ?>
            </div>
        </div>
        <div class="container-actions em-w-100">
            <?php if (!empty($this->_row->data->fabrik_view_url && !empty($detail_url))) : ?>
                <button type="button" class="btn btn-primary em-w-100 em-mt-8 em-applicant-default-font em-flex-column"
                   href="<?php echo $this->_row->data->fabrik_view_url ?>">
                    <span class="em-mb-8"><?php echo $detail_label; ?></span>
                </button>
            <?php endif; ?>

            <?php if(!$this->showFilters) : if (!empty($this->_row->data->fabrik_edit_url && !empty($edit_url))) : ?>
                <a class="btn btn-primary em-w-100 em-mt-8 em-applicant-default-font em-flex-column"
                   href="<?php echo $this->_row->data->fabrik_edit_url ?>">
                    <span class="em-mb-8"><?php echo $edit_label; ?></span>
                </a>
            <?php endif; endif; ?>
        </div>
    </div>
    <div id="actions-<?= $this->_row->id; ?>"
         class="modal-actions em-flex-column em-flex-align-start em-border-neutral-400 em-neutral-800-color hidden">
        <?php if ($show_edit_btn) : ?>
            <a href="<?= $this->_row->data->fabrik_edit_url; ?>"
               class="em-text-neutral-600 em-w-100 em-flex-row em-p-8">
                <span class="material-icons-outlined">edit</span>
                <span class="em-ml-8"><?php echo $edit_label; ?></span>
            </a>
        <?php endif; ?>
        <?php if ($show_details_btn) : ?>
            <a href="<?= $this->_row->data->fabrik_view_url; ?>"
               class="em-text-neutral-600 em-w-100 em-flex-row em-p-8">
                <span class="material-icons-outlined">visibility</span>
                <span class="em-ml-8"><?php echo $detail_label; ?></span>
            </a>
        <?php endif; ?>
        <?php if ($show_delete_btn) : ?>
            <a href="#"
               class="em-text-neutral-600 em-w-100 em-flex-row em-p-8 delete"
               data-listref="list_<?= $this->table->renderid ?>"
               data-rowid="<?= $this->_row->id; ?>"
               role="button"
               onclick="onClickDelete('<?= $this->_row->id; ?>')"
            >
                <span class="material-icons-outlined">delete</span>
                <span class="em-ml-8"><?php echo JText::_('COM_FABRIK_DELETE'); ?></span>
            </a>
        <?php endif; ?>
    </div>
</div>

<script>
    var token = "<?= Session::getFormToken(); ?>";

    window.addEventListener('click', function (e) {
        if (!e.target.classList.contains('toggle-actions-btn')) {
            // close all opened actions
            const openedActions = document.querySelectorAll('.modal-actions.opened');
            openedActions.forEach(function (action) {
                action.classList.add('hidden');
                action.classList.remove('opened');
            });
        }
    });

    function toggleActions(rowid) {
        const actions = document.getElementById('actions-' + rowid);
        if (actions) {
            actions.classList.toggle('hidden');
            actions.classList.toggle('opened')
        }
    }

    function onClickDelete(rowid) {
        rowid = rowid.split('_').pop();
        const listref = 'list_<?= $this->table->renderid ?>';
        const listid = '<?= $this->table->id ?>';
        const url = '/index.php?option=com_fabrik&task=list.delete';

        const form = new FormData();
        form.append('listref', [listref]);
        form.append('listid', listid);
        form.append('task', 'list.delete');
        form.append('format', 'raw');
        form.append('option', 'com_fabrik');
        form.append('view', 'list');
        form.append('setListRefFromRequest', '1');
        form.append('ids[' + rowid + ']', rowid);
        form.append(token, "1");

        fetch(url, {
            method: 'POST',
            body: form
        }).then(response => {
            if (response.ok) {
                window.location.reload();
            }
        });
    }
</script>