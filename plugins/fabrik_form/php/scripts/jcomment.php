<?php

   function my_jc_getObjectTitle($id)
    {
        $title = "Assistenza ".$id;
        return $title;
    }

    function my_jc_getObjectLink($id)
    {
        $link = JRoute::_('index.php?option=com_fabrik&amp;view=form&formid=8&rowid=' .$id. '&listid=8');
        return $link;
    }

    function my_jc_getObjectOwner($id)
    {
        $user =& JFactory::getUser();
        return $user->get('id');
    }

    function my_jc_getObjectInfo($object_id, $object_group, $language)
    {
    	$db = JFactory::getDbo();

    	$query = "SELECT * "
    			. " FROM `#__jcomments_objects`"
    					. " WHERE `object_id` = " . $db->Quote($object_id)
    					. " AND `object_group` = " . $db->Quote($object_group)
    					. " AND `lang` = " . $db->Quote($language)
    					;

    	$db->setQuery($query);
    	$info = $db->loadObject();

    	return empty($info) ? false : $info;
    }

    function my_jc_setObjectInfo($objectId, $info)
    {
    	$db = JFactory::getDbo();

    	if (!empty($objectId)) {
    		$query = "UPDATE #__jcomments_objects"
    				. " SET "
    						. "  `access` = " . (int) $info->access
    						. ", `userid` = " . (int) $info->userid
    						. ", `expired` = 0"
    								. ", `modified` = " . $db->Quote(JFactory::getDate()->toSql())
    								. (empty($info->title) ? "" : ", `title` = " . $db->Quote($info->title))
    								. (empty($info->link) ? "" : ", `link` = " . $db->Quote($info->link))
    								. (empty($info->category_id) ? "" : ", `category_id` = " . (int) $info->category_id)
    								. " WHERE `id` = " . (int) $objectId . ";"
    										;
    	} else {
    		$query = "INSERT INTO #__jcomments_objects"
    				. " SET "
    						. "  `object_id` = " . (int) $info->object_id
    						. ", `object_group` = " . $db->Quote($info->object_group)
    						. ", `category_id` = " . (int) $info->category_id
    						. ", `lang` = " . $db->Quote($info->lang)
    						. ", `title` = " . $db->Quote($info->title)
    						. ", `link` = " . $db->Quote($info->link)
    						. ", `access` = " . (int) $info->access
    						. ", `userid` = " . (int) $info->userid
    						. ", `expired` = 0"
    								. ", `modified` = " . $db->Quote(JFactory::getDate()->toSql())
    								;
    	}

    	$db->setQuery($query);
    	$db->execute();
    }

    $jObjectId = '';
    $object_id = $formModel->getRowId();
    $object_group = 'com_fabrik_8';
    $language = 'en-GB';

    $info = my_jc_getObjectInfo($object_id, $object_group, $language);
    if ($info === false) {
	    $info = new stdClass();
    }
    else {
    	$jObjectId = $info->id;
    }

    $info->title = my_jc_getObjectTitle($object_id, $language);
    $info->link = my_jc_getObjectLink($object_id, $language);
    $info->userid = my_jc_getObjectOwner($object_id, $language);
    $info->lang = $language;
    $info->object_id = $object_id;
    $info->object_group = $object_group;
    $info->access = 1;
    $info->category_id = '';
    $info->expired = 0;

    my_jc_setObjectInfo($jObjectId, $info)

?>