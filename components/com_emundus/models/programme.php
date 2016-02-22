<?php
/**
 * Users Model for eMundus Component
 *
 * @package    eMundus
 * @subpackage Components
 *             components/com_emundus/emundus.php
 * @link       http://www.decisionpublique.fr
 * @license    GNU/GPL
 */

// No direct access

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.model' );

class EmundusModelProgramme extends JModelList
{
    /**
     * Method to get article data.
     *
     * @param   integer $pk The id of the article.
     *
     * @return  mixed  Menu item data object on success, false on failure.
     */
    public function getCampaign($id = 0)
    {
        $db = JFactory::getDbo();
        $query	= $db->getQuery(true);
        $query->select('pr.url,ca.*, pr.notes, pr.code, pr.apply_online');
        $query->from('#__emundus_setup_programmes as pr,#__emundus_setup_campaigns as ca');
        $query->where('ca.training = pr.code AND ca.published=1 AND ca.id='.$id);
        $db->setQuery($query);
        return $db->loadAssoc();
    }

    public function getParams($id = 0)
    {
        $db = JFactory::getDbo();
        $query	= $db->getQuery(true);
        $query->select('params');
        $query->from('#__menu');
        $query->where('id='.$id);
        $db->setQuery($query);
        return json_decode($db->loadResult(), true);
    }

    /**
     * @param $user
     * @return array
     * get list of programmes for associated files
     */
    public function getAssociatedProgrammes($user)
    {
        $query = 'select DISTINCT sc.training
                  from #__emundus_users_assoc as ua
                  LEFT JOIN #__emundus_campaign_candidature as cc ON cc.fnum=ua.fnum
                  left join #__emundus_setup_campaigns as sc on sc.id = cc.campaign_id
                  where ua.user_id='.$user;
        try
        {
            $db = $this->getDbo();
            $db->setQuery($query);
            return $db->loadColumn();
        }
        catch(Exception $e)
        {
            error_log($e->getMessage(), 0);
            return false;
        }
    }

}
?>