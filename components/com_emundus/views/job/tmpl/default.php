<?php
/**
 * @version     1.0.0
 * @package     com_emundus
 * @copyright   Copyright (C) 2019. Tous droits réservés.
 * @license     GNU General Public License version 2 ou version ultérieure ; Voir LICENSE.txt
 * @author      emundus <dev@emundus.fr> - http://www.emundus.fr
 */
// no direct access
defined('_JEXEC') or die;

jimport( 'joomla.html.parameter' );

require_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'list.php');
include_once(JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'application.php');

$doc = JFactory::getDocument();
$user = JFactory::getUser();
$application = new EmundusModelApplication;

//$doc->addStyleSheet('components/com_emundus/assets/css/item.css');
//$doc->addStyleSheet('components/com_emundus/assets/css/list.css');

$eMConfig = JComponentHelper::getParams('com_emundus');
$fabrik_elements_title = $eMConfig->get('fabrik_elements_title', '2113');
$fabrik_elements_html = $eMConfig->get('fabrik_elements_html', '2111, 2113, 2134, 2135, 2112, 2116, 2114, 2117, 2128, 2121, 2133, 2124, 2125, 2126');

// Element Fabrik ID list to display 
$title = array($fabrik_elements_title);
$elts = array($fabrik_elements_html);
$rowid = JFactory::getApplication()->input->get('id');
$options = array('show_list_label' => 0, 'show_form_label' => 0, 'show_group_label' => 0, 'rowid' => $rowid, 'profile_id' => '13');
$checklevel = false;

$offer_title = $application->getFormsPDFElts(62, $title, $options, $checklevel);
$offer = $application->getFormsPDFElts(62, $elts, $options, $checklevel);

$offset = JFactory::getConfig()->get('offset');

$canEdit = $user->authorise('core.edit', 'com_emundus.' . $this->item->id);
if (!$canEdit && $user->authorise('core.edit.own', 'com_emundus' . $this->item->id)) {
    $canEdit = $user->id == $this->item->created_by;
}
?>
<?php if ($user->guest): ?>
    <div class="alert alert-error">
        <b><?php echo JText::_('COM_EMUNDUS_JOBS_PLEASE_CONNECT_OR_LOGIN_TO_APPLY'); ?>
    </div>
<?php endif; ?>
<?php if ($this->item) : ?>
    <h1 class="offer-title"><?php echo $offer_title; ?></h1>
    <div class="offer item_fields">
        <?php echo $offer; ?>
    </div>

<?php
else:
    echo JText::_('COM_EMUNDUS_ITEM_NOT_LOADED');
endif;
?>
<script type="text/javascript">
    $('rt-top-surround').remove();
    $('rt-header').remove();
    $('rt-footer').remove();
    $('footer').remove();
    $('gf-menu-toggle').remove();
</script>