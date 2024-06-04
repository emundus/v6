<?php
/**
 * @package    Joomla.Cli
 *
 * @copyright  (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * A command line cron job to import language Tags to jo_emundus_setup_languages table
 */

// Initialize Joomla framework
use Joomla\CMS\Factory;

const _JEXEC = 1;

// Load system defines
if (file_exists(dirname(__DIR__) . '/defines.php'))
{
    require_once dirname(__DIR__) . '/defines.php';
}

if (!defined('_JDEFINES')) {
    define('JPATH_BASE', dirname(__DIR__));
    require_once JPATH_BASE . '/includes/defines.php';
}

// Get the framework.
require_once JPATH_LIBRARIES . '/import.legacy.php';

// Bootstrap the CMS libraries.
require_once JPATH_LIBRARIES . '/cms.php';

require_once JPATH_BASE . '/components/com_emundus/helpers/fabrik.php';
/**
 * Cron job to trash expired cache data.
 *
 * @since  2.5
 */
class MigrateEncryptDatas extends JApplicationCli {


    /**
     * Entry point for the script
     *
     * @return  void
     *
     * @since   2.5
     */
    public function doExecute() {

        $db = Factory::getDbo();
        $query = $db->getQuery(true);

		// Get IBAN elements with encrypt_datas enabled
	    $query->clear()
		    ->select('fe.id,fe.name,fe.plugin,fe.group_id,fl.db_table_name')
		    ->from($db->quoteName('#__fabrik_elements','fe'))
		    ->leftJoin($db->quoteName('#__fabrik_formgroup','ffg').' ON '.$db->quoteName('ffg.group_id').' = '.$db->quoteName('fe.group_id'))
		    ->leftJoin($db->quoteName('#__fabrik_lists','fl').' ON '.$db->quoteName('fl.form_id').' = '.$db->quoteName('ffg.form_id'))
		    ->where($db->quoteName('fe.plugin') . ' LIKE ' . $db->quote('iban'))
		    ->where('JSON_EXTRACT('.$db->quoteName('fe.params').',"$.encrypt_datas") = ' . $db->quote(1))
		    ->group($db->quoteName('fl.db_table_name'));
	    $db->setQuery($query);
	    $encrypted_elements = $db->loadObjectList();
		
		// Get elements of forms with emundusencryptdatas plugin
	    $query->clear()
		    ->select('fe.id,fe.name,fe.plugin,fe.group_id,fl.db_table_name')
		    ->from($db->quoteName('#__fabrik_forms','ff'))
		    ->leftJoin($db->quoteName('#__fabrik_formgroup','ffg').' ON '.$db->quoteName('ff.id').' = '.$db->quoteName('ffg.form_id'))
		    ->leftJoin($db->quoteName('#__fabrik_elements','fe').' ON '.$db->quoteName('ffg.group_id').' = '.$db->quoteName('fe.group_id'))
		    ->leftJoin($db->quoteName('#__fabrik_lists','fl').' ON '.$db->quoteName('ff.id').' = '.$db->quoteName('fl.form_id'))
		    ->where($db->quoteName('ff.params') . ' LIKE ' . $db->quote('%emundusencryptdatas%'))
		    ->where($db->quoteName('fe.plugin') . ' <> ' . $db->quote('internalid'))
		    ->where($db->quoteName('fe.name') . ' NOT IN (' . implode(',',$db->quote(['id','time_date','user','fnum'])) .')')
		    ->group([$db->quoteName('fe.name'),$db->quoteName('fl.db_table_name')]);
	    $db->setQuery($query);
	    $encrypted_elements_via_forms = $db->loadObjectList();
		$encrypted_elements = array_merge($encrypted_elements,$encrypted_elements_via_forms);
		
	    foreach ($encrypted_elements as $element) {
		    $query->clear()
			    ->select([$db->quoteName('id'),$db->quoteName($element->name,'value')])
			    ->from($db->quoteName($element->db_table_name));
		    $db->setQuery($query);
		    $old_encrypted_datas = $db->loadAssocList();
			$old_encrypted_datas = array_map(function($data) use ($element) {
			    $data['plugin'] = $element->plugin;
			    return $data;
		    }, $old_encrypted_datas);

		    $cipher = 'aes-128-cbc';
		    $key = Factory::getConfig()->get('secret');
		    $new_encrypted_datas = EmundusHelperFabrik::migrateEncryptDatas($cipher,$cipher,$key,$key,$old_encrypted_datas);

		    // Update rows with new encrypted datas
		    foreach ($new_encrypted_datas as $new_encrypted_data) {
			    $query->clear()
				    ->update($db->quoteName($element->db_table_name))
				    ->set($db->quoteName($element->name) . ' = ' . $db->quote($new_encrypted_data['value']))
				    ->where($db->quoteName('id') . ' = ' . $db->quote($new_encrypted_data['id']));
			    $db->setQuery($query);
			    $db->execute();
		    }
	    }
    }
}

JApplicationCli::getInstance('MigrateEncryptDatas')->execute();
