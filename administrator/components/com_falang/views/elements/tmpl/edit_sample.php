<?php
/**
 * Created by JetBrains PhpStorm.
 * User: stephane
 * Date: 04/12/12
 * Time: 10:54
 * To change this template use File | Settings | File Templates.
 */

$contentElement = $this->falangManager->getContentElement( $this->id );
$db = JFactory::getDBO();
$contentTable = $contentElement->getTable();
?>

<table class="adminList" cellspacing="1">
    <tr align="center" valign="middle">
        <td width="100%" align="center" valign="top">
            <table cellpadding="4" cellspacing="0" border="0" width="100%" class="adminlist">
                <tr>
                    <?php
                    $sqlFields = "";
                    foreach( $contentTable->Fields as $tableField ) {
                        if( $sqlFields!='' ) $sqlFields .= ',';
                        $sqlFields .= '`' .$tableField->Name. '`';
                        ?>
                        <th nowrap><?php echo $tableField->Lable;?></th>
                        <?php
                    }
                    ?>
                </tr>
                <?php
                $k=0;
                $idname = $this->falangManager->getPrimaryKey($contentTable->Name);
                $sql = "SELECT $sqlFields"
                    . "\nFROM #__" .$contentTable->Name
                    . "\nORDER BY $idname limit 0,10";
                $db->setQuery( $sql	);
                $rows = $db->loadObjectList();
                if( $rows != null ) {
                    foreach ($rows as $row) {
                        ?>
                        <tr class="<?php echo "row$k"; ?>">
                            <?php
                            foreach( $contentTable->Fields as $tableField ) {
                                $fieldName = $tableField->Name;
                                $fieldValue = $row->$fieldName;
                                if( $tableField->Type='htmltext' ) {
                                    $fieldValue = htmlspecialchars( $fieldValue );
                                }

                                if( $fieldValue=='' ) $fieldValue="&nbsp;";
                                if( strlen($fieldValue) > 97 ) {
                                    $fieldValue = substr( $fieldValue, 0, 100) . '...';
                                }

                                ?>
                                <td valign="top"><?php echo $fieldValue;?></td>
                                <?php
                            }
                            ?>
                        </tr>
                        <?php
                        $k=1-$k;
                    }
                }
                ?>
            </table>
            <?php
            ?>
        </td>
    </tr>
</table>
