<?php
/**
 * @package     ${NAMESPACE}
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */

use PHPUnit\Framework\TestCase;
ini_set( 'display_errors', false );
error_reporting(E_ALL);
define('_JEXEC', 1);
define('DS', DIRECTORY_SEPARATOR);
define('JPATH_BASE', dirname(__DIR__) . '/../../');

include_once ( JPATH_BASE . 'includes/defines.php' );
include_once ( JPATH_BASE . 'includes/framework.php' );
include_once(JPATH_SITE.'/components/com_emundus/unittest/helpers/samples.php');
include_once (JPATH_SITE . '/components/com_emundus/models/campaign.php');

jimport('joomla.user.helper');
jimport( 'joomla.application.application' );
jimport('joomla.plugin.helper');

// set global config --> initialize Joomla Application with default param 'site'
JFactory::getApplication('site');

// set false ini_get('session.use_cookies') and set false headers_sent
!ini_get('session.use_cookies') && !headers_sent($file, $line);

// activate session
session_start();

class EmundusModelCampaignTest extends TestCase
{
    private $m_campaign;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->m_campaign = new EmundusModelCampaign;
    }


    public function testCreateDocument()
    {
        $document = [
            'name' => [
                'fr' => ''
            ],
        ];
        $types = [''];

        $created = $this->m_campaign->createDocument($document, $types, null, 9);
        $this->assertFalse($created['status'], 'Assert impossible to create document with empty name');

        $document['name']['fr'] = 'Test';
        $created = $this->m_campaign->createDocument($document, $types, null, 9);
        $this->assertFalse($created['status'], 'Assert impossible to create document with empty types');
    }

    public function testCreateCampaign()
    {
        $new_campaign_id = $this->m_campaign->createCampaign([]);
        $this->assertEmpty($new_campaign_id, 'Assert can not create campaign without data');

        $new_campaign_id = $this->m_campaign->createCampaign(['limit_status' => 1, 'profile_id' => 9]);
        $this->assertEmpty($new_campaign_id, 'Assert can not create campaign without label');

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('code')
            ->from($db->quoteName('#__emundus_setup_programmes'));
        $db->setQuery($query);
        $programmes = $db->loadColumn();

        if (!empty($programmes)) {
            $start_date = new DateTime();
            $start_date->modify('-1 day');

            $end_date = new DateTime();
            $end_date->modify('+1 year');

            $inserting_datas = [
                'label' =>  json_encode(['fr' => 'Campagne test unitaire', 'en' => 'Campagne test unitaire']),
                'description' => 'Lorem ipsum',
                'short_description' => 'Lorem ipsum',
                'start_date' => $start_date->format('Y-m-d H:i:s'),
                'end_date' => $end_date->format('Y-m-d H:i:s'),
                'profile_id' => 9,
                'training' => $programmes[0],
                'year' => '2022-2023',
                'published' => 1
            ];

            $new_campaign_id = $this->m_campaign->createCampaign($inserting_datas);
            $this->assertGreaterThan(0, $new_campaign_id, 'Assert campaign creation works.');
        }
    }
}
