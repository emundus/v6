<?php
/**
 * @package         SCLogin - 2FA login check. Return true if login credentials are correct and 2FA screen should be shown.
 * @copyright (c)   2009-2014 by SourceCoast - All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @version         Release v4.3.0
 * @build-date      2015/03/19
 */

// We are a valid entry point.
const _JEXEC = 1;

define('JPATH_BASE', dirname(__DIR__) . '/../../');
require_once JPATH_BASE . '/includes/defines.php';

// Get the framework.
require_once JPATH_LIBRARIES . '/import.legacy.php';

// Bootstrap the CMS libraries.
require_once JPATH_LIBRARIES . '/cms.php';

// Configure error reporting to maximum for CLI output.
error_reporting(0); // We can do this since this is our own (and only) entry-point
ini_set('display_errors', 0);

/**
 * A command line cron job to attempt to remove files that should have been deleted at update.
 *
 * @package  Joomla.CLI
 * @since    3.0
 */
class SourcecoastTfaCheckWeb extends JApplicationWeb
{
    /**
     * Entry point for CLI script
     *
     * @return  void
     *
     * @since   3.0
     */
    public function doExecute()
    {
        $response = new stdClass();
        $response->needsOtp = 'false';
        $response->form = "";
        // Session check
        if (JSession::checkToken('POST'))
        {
            $db = JFactory::getDbo();
            // Check if TFA is enabled. If not, just return false
            $query = $db->getQuery(true)
                    ->select('COUNT(*)')
                    ->from('#__extensions')
                    ->where('enabled=' . $db->q(1))
                    ->where('folder=' . $db->q('twofactorauth'));
            $db->setQuery($query);
            $tfaCount = $db->loadResult();

            if ($tfaCount > 0)
            {
                $username = JRequest::getVar('u', '', 'POST', 'username');

                $query = $db->getQuery(true)
                        ->select('id, password, otpKey')
                        ->from('#__users')
                        ->where('username=' . $db->q($username));

                $db->setQuery($query);
                $result = $db->loadObject();

                if ($result && $result->otpKey != '')
                {
                    //jimport('sourcecoast.utilities');
                    //SCStringUtilities::loadLanguage('mod_sclogin');
                    JFactory::getLanguage()->load('mod_sclogin');

                    //$password = JRequest::getString('p', '', 'POST', JREQUEST_ALLOWRAW);
                    //if (JUserHelper::verifyPassword($password, $result->password, $result->id))
                    //{
                    $response->needsOtp = 'true';
                    ob_start();
                    require(JModuleHelper::getLayoutPath('mod_sclogin', 'otp'));
                    $response->form = ob_get_clean();
                    //}
                }
            }
        }

        echo json_encode($response);
        exit;
    }
}

// Instantiate the application object, passing the class name to JCli::getInstance
// and use chaining to execute the application.
$app = JApplicationWeb::getInstance('SourcecoastTfaCheckWeb');
// Loading the 'site' application to make sure our session (and other) data is from the 'site'.. sounds obvious, but documenting it, because it won't be the next time I look at this.
JFactory::getApplication('site');
$app->execute();