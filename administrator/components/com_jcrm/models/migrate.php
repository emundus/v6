<?php

/**
 * @version     1.0.0
 * @package     com_jcrm
 * @copyright   Copyright (C) 2014. Tous droits réservés.
 * @license     GNU General Public License version 2 ou version ultérieure ; Voir LICENSE.txt
 * @author      Décision Publique <dev@emundus.fr> - http://www.emundus.fr
 */
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * Methods supporting a list of Jcrm records.
 */
class JcrmModelMigrate extends JModelList {

    /**
     * Constructor.
     *
     * @param    array    An optional associative array of configuration settings.
     * @see        JController
     * @since    1.6
     */
    public function __construct($config = array()) {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                
            );
        }

        parent::__construct($config);
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     */
    protected function populateState($ordering = null, $direction = null) {
        // Initialise variables.
        $app = JFactory::getApplication('administrator');

        // Load the filter state.
        $search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        $published = $app->getUserStateFromRequest($this->context . '.filter.state', 'filter_published', '', 'string');
        $this->setState('filter.state', $published);

        

        // Load the parameters.
        $params = JComponentHelper::getParams('com_jcrm');
        $this->setState('params', $params);

        // List state information.
        parent::populateState('a.id', 'asc');
    }

    /**
     * Method to get a store id based on model configuration state.
     *
     * This is necessary because the model is used by the component and
     * different modules that might need different sets of data or different
     * ordering requirements.
     *
     * @param	string		$id	A prefix for the store id.
     * @return	string		A store id.
     * @since	1.6
     */
    protected function getStoreId($id = '') {
        // Compile the store id.
        $id.= ':' . $this->getState('filter.search');
        $id.= ':' . $this->getState('filter.state');

        return parent::getStoreId($id);
    }

    /**
     * Build an SQL query to load the list data.
     *
     * @return	JDatabaseQuery
     * @since	1.6
     */
    protected function getListQuery() {
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);
		return $query;
	}


	public function getItems() {
        $items = parent::getItems();
        
        return $items;
    }


    public function getOldOrganisation()
    {
        $dbo = $this->getDbo();
        $query = "select * from #__jcrm_accounts";
        try
        {
            $dbo->setQuery($query);
            return $dbo->loadObjectList();
        }
        catch(Exception $e)
        {
            throw $e;
        }
    }

    public function getOldContacts()
    {
        $dbo = $this->getDbo();
        $query = "select cbk.*, acc.name as orga  from jos_jcrm_contacts_bk as cbk left join jos_jcrm_accounts as acc on acc.id = cbk.account_id";
        try
        {
            $dbo->setQuery($query);
            return $dbo->loadObjectList();
        }
        catch(Exception $e)
        {
            throw $e;
        }
    }

    public function getOrgs()
    {
        $dbo = $this->getDbo();
        $query = "select * from #__jcrm_contacts where `type` = 1";
        try
        {
            $dbo->setQuery($query);
            return $dbo->loadObjectList();
        }
        catch(Exception $e)
        {
            throw $e;
        }
    }

    public function getContactByOrgName($organisation)
    {
        $dbo = $this->getDbo();
        $query = "select * from #__jcrm_contacts where `type` = 0 and `organisation` like '" . $organisation."'";
        try
        {
            $dbo->setQuery($query);
            return $dbo->loadObjectList();
        }
        catch(Exception $e)
        {
            throw $e;
        }
    }

    public function addContactToOrg($id, $orgContacts)
    {
        $dbo = $this->getDbo();
        $query = 'insert into #__jcrm_contact_orga (contact_id, org_id) value ';

        foreach($orgContacts as $contact)
        {
            $query .= ' ('.$contact->id.','.$id.'), ';
        }

        $query = substr($query, 0, -2);
        $dbo->setQuery($query);
        try
        {
            $res = $dbo->execute();
            return $res;
        }
        catch(Exception $e)
        {
            throw $e;
        }
    }

    public function emptyContacts()
    {
        $dbo = $this->getDbo();
        $query = "delete from #__jcrm_contacts where 1";
        $dbo->setQuery($query);
        $dbo->execute();
    }

    public function renameTable()
    {
        $dbo = $this->getDbo();
        $query = "rename table #__jcrm_contacts_bk to #__jcrm_contacts_bk_migrated, #__jcrm_accounts to #__jcrm_accounts_migrated ";
        $dbo->setQuery($query);
        $dbo->execute();
        return $dbo->execute();
    }

    public function canMigrate()
    {
        $dbo = $this->getDbo();

        $query = "show tables like 'jos_jcrm_contacts'";
        try
        {
            $dbo->setQuery($query);
            $res = $dbo->loadAssoc();
            $tableNotHere = array();
            if ($res === null)
            {
                $tableNotHere[] = 'jos_jcrm_contact';
            }
            $query = "show tables like 'jos_jcrm_accounts'";
            $dbo->setQuery($query);
            $res = $dbo->loadAssoc();
            if ($res === null)
            {
                $tableNotHere[] = 'jos_jcrm_accounts';
            }
            $query = "show tables like 'jos_jcrm_contacts_bk'";
            $dbo->setQuery($query);
            $res = $dbo->loadAssoc();
            if ($res === null)
            {
                $tableNotHere[] = 'jos_jcrm_contact_bk';
            }
            return $tableNotHere;
        }
        catch(Exception $e)
        {
            throw $e;
        }
    }

    public function getNbOrgs()
    {
        $dbo = $this->getDbo();
        $query = "select count(*) from #__jcrm_contacts where `type` = 1";
        try
        {
            $dbo->setQuery($query);
            return $dbo->loadResult();
        }
        catch(Exception $e)
        {
            throw $e;
        }
    }

    public function getNbCont()
    {
        $dbo = $this->getDbo();
        $query = "select count(*) from #__jcrm_contacts where `type` = 0";
        try
        {
            $dbo->setQuery($query);
            return $dbo->loadResult();
        }
        catch(Exception $e)
        {
            throw $e;
        }
    }

    public function addOrgs($contacts)
    {
        $dbo = $this->getDbo();
            $query = $queryInsert = "insert into #__jcrm_contacts (`id`, `state`, `checked_out`, `created_by`,  `last_name`, `first_name`, `organisation`, `email`, `phone`, `jcard`, `type`, `full_name`) values ";
        try
        {
            foreach ($contacts as $k =>$contact)
            {
                if(($k > 1) && ($k % 500 == 0))
                {
                    $query = substr($query, 0, -2);
                    $dbo->setQuery($query);
                    $dbo->execute();
                    $query = $queryInsert;
                }
                $email = (isset($contact->jcard['email']))?$contact->jcard['email'][0]->uri:null;
                $phone = (isset($contact->jcard['phone']))?$contact->jcard['phone'][0]->tel:null;
                    $query .= " ($contact->id, 1, ".JFactory::getUser()->id.",".JFactory::getUser()->id.",'".$contact->last_name."','".$contact->first_name."','".$contact->organisation."','".$email."','".$phone."','". addcslashes(json_encode((object)$contact->jcard), '\\:\'')."', ".$contact->type.", '".$contact->jcard['fn']."'), ";
            }
            $query = substr($query, 0, -2);
            $dbo->setQuery($query);
            return  $dbo->execute();
            ;
        }
        catch(Exception $e)
        {
            return $e->getMessage();
        }
    }

    public function addContacts($contacts)
    {
        $dbo = $this->getDbo();
        $query = $queryInsert = "insert into #__jcrm_contacts (`state`, `checked_out`, `created_by`,  `last_name`, `first_name`, `organisation`, `email`, `phone`, `jcard`, `type`, `full_name`) values ";
        try
        {
            foreach ($contacts as $k =>$contact)
            {
                if(($k > 1) && ($k % 500 == 0))
                {
                    $query = substr($query, 0, -2);
                    $dbo->setQuery($query);
                    $dbo->execute();
                    $query = $queryInsert;
                }
                $email = (isset($contact->jcard['email']))?$contact->jcard['email'][0]->uri:null;
                $phone = (isset($contact->jcard['phone']))?$contact->jcard['phone'][0]->tel:null;
                $query .= " (1, ".JFactory::getUser()->id.",".JFactory::getUser()->id.",'".$contact->last_name."','".$contact->first_name."','".$contact->organisation."','".$email."','".$phone."','". addcslashes(json_encode((object)$contact->jcard), '\\:\'')."', ".$contact->type.", '".$contact->jcard['fn']."'), ";
            }
            $query = substr($query, 0, -2);
            $dbo->setQuery($query);
            return  $dbo->execute();
            ;
        }
        catch(Exception $e)
        {
            return $e->getMessage();
        }
    }
}
