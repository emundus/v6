<?php

defined('_JEXEC') or die('Restricted access');


class EmundusHelperTags
{
    function getTags($tags = [], $published = 1) {
        $return = [];

        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $query->select('*')
            ->from('#__emundus_setup_tags')
            ->where('published = ' . $db->quote($published));


        if (!empty($tags)) {
            $query->andWhere('tag IN ('.implode(',', $tags).')');
        }

        $db->setQuery($query);

        try {
            $return = $db->loadObjectList();
        } catch (Exception $e) {
            JLog::add('Error getting tags : '.$e->getMessage(), JLog::ERROR, 'com_emundus');
        }

        return $return;
    }
}