<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/joomla-platform
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2020 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace JchOptimize\Platform;

defined( '_JEXEC' ) or die( 'Restricted access' );

use JchOptimize\Core\Interfaces\Utility as UtilityInterface;
use Joomla\Application\Web\WebClient;
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Crypt\Crypt;
use Joomla\CMS\Crypt\Key;
use Joomla\Input\Input;
use Joomla\CMS\Language\Text;

class Utility implements UtilityInterface
{

        /**
         *
         * @param   string  $text
         *
         * @return string
         */
        public static function translate( $text )
        {
                if ( strlen( $text ) > 20 )
                {
                        $text = substr( $text, 0, strpos( wordwrap( $text, 20 ), "\n" ) );
                }

                $text = 'COM_JCHOPTIMIZE_' . strtoupper( str_replace( ' ', '_', $text ) );

                return Text::_( $text );
        }

        /**
         *
         * @return int
         */
        public static function unixCurrentDate()
        {
                return Factory::getDate( 'now', 'GMT' )->toUnix();
        }

        /**
         *
         * @param   string  $url
         *
         * @return void
         */
        public static function loadAsync( $url )
        {
                return;
        }

        /**
         *
         * @param   string  $message
         * @param   string  $priority
         * @param   string  $filename
         */
        public static function log( $message, $priority, $filename )
        {
                Log::addLogger(
                        array(
                                'text_file' => 'com_jchoptimize.debug.php'
                        ), Log::ALL,
                        array( 'com_jchoptimize' )
                );
                Log::add( Text::_( $message ), constant( 'Joomla\CMS\Log\Log::' . $priority ), 'com_jchoptimize' );
        }

        /**
         *
         * @return string
         */
        public static function lnEnd()
        {
                $oDocument = Factory::getDocument();

                return $oDocument->_getLineEnd();
        }

        /**
         *
         * @return string
         */
        public static function tab()
        {
                $oDocument = Factory::getDocument();

                return $oDocument->_getTab();
        }

        /**
         *
         * @param   string  $value
         *
         * @return string|void
         */
        public static function decrypt( $value )
        {
                if ( empty( $value ) )
                {
                        return;
                }

                $crypt = self::getCrypt();

                return $crypt->decrypt( $value );
        }

        /**
         *
         * @return Crypt
         */
        private static function getCrypt()
        {
                static $crypt = null;

                if ( is_null( $crypt ) )
                {
                        $crypt = new Crypt();

                        if ( version_compare( JVERSION, '4.0', 'lt' ) )
                        {
                                //Default Cipher is SimpleCipher need to use secret word as key$conf  = JFactory::getConfig();

                                $secretword = Factory::getConfig()->get( 'secret' );
                                $key        = new Key( 'simple', $secretword, $secretword );

                                $crypt->setKey( $key );
                        }
                }

                return $crypt;
        }

        /**
         *
         * @param   string  $value
         *
         * @return string
         */
        public static function encrypt( $value )
        {
                if ( empty( $value ) )
                {
                        return false;
                }

                $crypt = self::getCrypt();

                return $crypt->encrypt( $value );
        }

        /**
         *
         * @return mixed
         */
        public static function getLogsPath()
        {
                $config = Factory::getConfig();

                return $config->get( 'log_path' );
        }

        /**
         *
         */
        public static function menuId()
        {
                return Utility::get( 'Itemid' );
        }

        /**
         *
         * @param   string  $value
         * @param   string  $default
         * @param   string  $filter
         * @param   string  $method
         *
         * @return mixed
         */
        public static function get( $value, $default = '', $filter = 'cmd', $method = 'request' )
        {
                $input = new Input;

                return $input->$method->get( $value, $default, $filter );
        }

        /**
         *
         */
        public static function isGuest()
        {
        }


        /**
         *
         * @param   array  $headers
         *
         * @throws \Exception
         */
        public static function sendHeaders( $headers )
        {
                if ( ! empty( $headers ) )
                {
                        $app = Factory::getApplication();

                        foreach ( $headers as $header => $value )
                        {
                                $app->setHeader( $header, $value, true );
                        }
                }
        }

        public static function userAgent( $userAgent )
        {
                $oWebClient = new WebClient( $userAgent );

                $oUA = new \stdClass();

                switch ( $oWebClient->browser )
                {
                        case $oWebClient::CHROME:
                                $oUA->browser = 'Chrome';
                                break;
                        case $oWebClient::FIREFOX:
                                $oUA->browser = 'Firefox';
                                break;
                        case $oWebClient::SAFARI:
                                $oUA->browser = 'Safari';
                                break;
                        case $oWebClient::EDGE:
                                $oUA->browser = 'Edge';
                                break;
                        case $oWebClient::IE:
                                $oUA->browser = 'Internet Explorer';
                                break;
                        case $oWebClient::OPERA:
                                $oUA->browser = 'Opera';
                                break;
                        default:
                                $oUA->browser = 'Unknown';
                                break;
                }

                $oUA->browserVersion = $oWebClient->browserVersion;

                switch ( $oWebClient->platform )
                {
                        case $oWebClient::ANDROID:
                        case $oWebClient::ANDROIDTABLET:
                                $oUA->os = 'Android';
                                break;
                        case $oWebClient::IPAD:
                        case $oWebClient::IPHONE:
                        case $oWebClient::IPOD:
                                $oUA->os = 'iOS';
                                break;
                        case $oWebClient::MAC:
                                $oUA->os = 'Mac';
                                break;
                        case $oWebClient::WINDOWS:
                        case $oWebClient::WINDOWS_CE:
                        case $oWebClient::WINDOWS_PHONE:
                                $oUA->os = 'Windows';
                                break;
                        case $oWebClient::LINUX:
                                $oUA->os = 'Linux';
                                break;
                        default:
                                $oUA->os = 'Unknown';
                                break;
                }

                return $oUA;
        }
}
