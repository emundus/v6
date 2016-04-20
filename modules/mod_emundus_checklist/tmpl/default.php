<?php 
// no direct access
defined('_JEXEC') or die('Restricted access');

// FORMS
if ($show_forms == 1) {

  if (count($forms) > 0) {
    echo '<h3>'.JText::_($forms_title).'</h3>';
    echo '<ul>';
    foreach ($forms as $form) {
      $query = 'SELECT count(*) FROM '.$form->db_table_name.' WHERE user = '.$user->id. ' AND fnum like '.$db->Quote($user->fnum);
      $db->setQuery( $query );
      $cpt = $db->loadResult();
      $link   = '<a href="'.$form->link.'">';
      $active = $form->id==$menuid?' active':'';
      $need = $cpt==0?'need_missing':'need_ok';
      $class = $need.$active;
      $endlink= '</a>';
    ?>
        <li id="mlf<?php echo $form->id; ?>" class="em_module <?php echo $class; ?>"><div class="em_form"><?php echo $link.$form->title.$endlink; ?></div></li>
    <?php 
    }
    echo '</ul>';
  }
  unset($link);
  unset($endlink);
}

// MANDATORY DOCUMENTS
if ($show_mandatory_documents == 1) {

  if (count($mandatory_documents) > 0) {
    echo '<h3>'.JText::_($mandatory_documents_title).'</h3>';
    echo '<ul>';
    foreach ($mandatory_documents as $attachment) {
      $query = 'SELECT count(id) FROM #__emundus_uploads up
            WHERE up.user_id = '.$user->id.' AND up.attachment_id = '.$attachment->_id.' AND fnum like '.$db->Quote($user->fnum);
      $db->setQuery( $query );
      $cpt = $db->loadResult();
      $link   = '<a id="'.$attachment->_id.'" class="document" href="'.$itemid['link'].'&Itemid='.$itemid['id'].'#a'.$attachment->_id.'">';
      $active = '';
      $need = $cpt==0?'need_missing':'need_ok';
      $class = $need.$active;
      $endlink= '</a>';
    ?>
        <li id="ml<?php echo $attachment->_id; ?>" class="em_module <?php echo $class; ?>"><div class="em_form"><?php echo $link.$attachment->value.$endlink; ?></div></li>
    <?php 
    }
    echo '</ul>';
  }
  unset($link);
  unset($endlink);
}

// OPTIONAL DOCUMENTS
if ($show_optional_documents == 1) {

  if (count($optional_documents) > 0) {
    echo '<h3>'.JText::_($optional_documents_title).'</h3>';
    echo '<ul>';
    foreach ($optional_documents as $attachment) {
      $query = 'SELECT count(id) FROM #__emundus_uploads up
            WHERE up.user_id = '.$user->id.' AND up.attachment_id = '.$attachment->_id.' AND fnum like '.$db->Quote($user->fnum);
      $db->setQuery( $query );
      $cpt = $db->loadResult();
      $link   = '<a id="'.$attachment->_id.'" class="document" href="'.$itemid['link'].'&Itemid='.$itemid['id'].'#a'.$attachment->_id.'">';
      $active = '';
      $need = $cpt==0?'need_missing':'need_ok';
      $class = $need.$active;
      $endlink= '</a>';
    ?>
        <li id="ml<?php echo $attachment->_id; ?>" class="em_module <?php echo $class; ?>"><div class="em_form"><?php echo $link.$attachment->value.$endlink; ?></div></li>
    <?php 
    }
    echo '</ul>';
  }
  unset($link);
  unset($endlink);
}

?>