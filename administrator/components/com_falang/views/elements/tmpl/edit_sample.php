<?php
/**
 * @package     Falang for Joomla!
 * @author      Stéphane Bouey <stephane.bouey@faboba.com> - http://www.faboba.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright   Copyright (C) 2010-2017. Faboba.com All rights reserved.
 */

// No direct access to this file
defined('_JEXEC') or die;

$contentElement = $this->falangManager->getContentElement($this->element);
$db = JFactory::getDBO();
$contentTable = $contentElement->getTable();
?>

<table class="table table-striped ">
    <thead>
    <tr>
        <?php
        $sqlFields = "";
        foreach ($contentTable->Fields as $tableField) {
            if ($sqlFields != '') $sqlFields .= ',';
            $sqlFields .= '`' . $tableField->Name . '`';
            ?>
            <th nowrap><?php echo $tableField->Lable; ?></th>
            <?php
        }
        ?>
    </tr>
    </thead>
    <tbody>
    <?php
    $k = 0;
    $idname = $this->falangManager->getPrimaryKey($contentTable->Name);
    $sql = "SELECT $sqlFields"
        . "\nFROM #__" . $contentTable->Name
        . "\nORDER BY $idname limit 0,10";
    $db->setQuery($sql);
    $rows = $db->loadObjectList();
    if ($rows != null) {
        foreach ($rows as $row) {
            ?>
            <tr class="<?php echo "row$k"; ?>">
                <?php
                foreach ($contentTable->Fields as $tableField) {
                    $fieldName = $tableField->Name;
                    $fieldValue = $row->$fieldName;
                    if ($tableField->Type = 'htmltext') {
                        $fieldValue = htmlspecialchars($fieldValue);
                    }

                    if ($fieldValue == '') $fieldValue = "&nbsp;";
                    if (strlen($fieldValue) > 97) {
                        $fieldValue = substr($fieldValue, 0, 100) . '...';
                    }

                    ?>
                    <td><?php echo $fieldValue; ?></td>
                    <?php
                }
                ?>
            </tr>
            <?php
            $k = 1 - $k;
        }
    }
    ?>
    </tbody>
</table>
