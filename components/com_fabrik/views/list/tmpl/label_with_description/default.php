<?php
/**
 * Bootstrap List Template - Default
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2016  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @since       3.1
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

$pageClass = $this->params->get('pageclass_sfx', '');

if ($pageClass !== '') :
    echo '<div class="' . $pageClass . '">';
endif;

if ($this->tablePicker != '') : ?>
    <div style="text-align:right"><?php echo FText::_('COM_FABRIK_LIST') ?>: <?php echo $this->tablePicker; ?></div>
<?php
endif;

if ($this->params->get('show_page_heading')) :
    echo '<h1>' . $this->params->get('page_heading') . '</h1>';
endif;

require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'helpers'.DS.'files.php');
require_once (JPATH_BASE.DS.'components'.DS.'com_emundus'.DS.'models'.DS.'users.php');

$m_users = new EmundusModelUsers;
$user = JFactory::getSession()->get('emundusUser');

$user_profile = $m_users->getCurrentUserProfile($user->id);

// Intro outside of form to allow for other lists/forms to be injected.
echo $this->table->intro;
?>
<form class="fabrikForm form-search" action="<?php echo $this->table->action;?>" method="post" id="<?php echo $this->formid;?>" name="fabrikList">


    <div class="fabrikDataContainer">

        <?php foreach ($this->pluginBeforeList as $c) :
            echo $c;

        endforeach;

        $data = array();
        $i = 0;


        if (!empty($this->rows)) {
            foreach ($this->rows as $row) {
	            foreach ($row as $k => $v) {
                    foreach ($this->headings as $key => $val) {
                        $raw = $key.'_raw';

                        if (property_exists($v->data, $raw)) {
                            $key = explode('___', $key)[1];
                            $data[$i][$key] = $v->data->$raw;
                        }
                    }
		            $i = $i + 1;
	            }
            }
        }
        ?>

        <div class="g-block size-100">
            <?php if ($this->navigation->total < 1) :?>
                <?php if($this->table->db_table_name == 'jos_emundus_entreprise') :?>
                    <?php echo JText::_("COM_EMUNDUS_NO_COMPANIES");?>
                <?php elseif ($this->table->db_table_name == 'jos_emundus_users') :?>
                    <?php echo JText::_("COM_EMUNDUS_NO_ASSOCIATES");?>
                <?php endif; ?>
            <?php else: ?>
            <ul>
                <?php foreach ($data as $d) :?>
                    <?php if ($d['published'] == '1') :?>
                        <li>
                            <p class="em-list-label"><?= $d['label'] ;?></p>
                            <p class="em-list-description"><?= $d['description'] ;?></p>
                            <?php if (array_key_exists('link', $d) && !empty($d['link'])) :?>
                                <a class="em-list-link" title="<?= $d['label'] ;?>" href="<?= $d['link'] ;?>" target="_blank"><?= $d['link'] ;?></a>
                            <?php endif; ?>
                        </li>
                    <?php endif; ?>

                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </div>
    </div>
</form>


