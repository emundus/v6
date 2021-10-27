<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/core
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2020 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace JchOptimize\Core;

defined( '_JCH_EXEC' ) or die( 'Restricted access' );

use JchOptimize\Platform\Utility;

class Browser
{

        //Based on response from https://fonts.googleapis.com/css?family=Racing+Sans+One
        protected static $instances = array();
        protected $fontHash = '';
        protected $oClient;

        public function __construct( $userAgent )
        {

                $this->oClient = Utility::userAgent( $userAgent );
                $this->calculateFontHash();
        }

        protected function calculateFontHash()
        {
                $this->fontHash .= $this->oClient->os . '/';

                $sVersion = $this->oClient->browserVersion;

                switch ( $this->oClient->browser )
                {
                        case 'Chrome':

                                if ( version_compare( $sVersion, '40', '>=' ) || $sVersion == 'Unknown' )
                                {
                                        $this->fontHash .= 'woff2/unicode';
                                }
                                elseif ( version_compare( $sVersion, '22', '>=' ) )
                                {
                                        $this->fontHash .= 'woff';
                                }

                                break;

                        case 'Firefox':

                                if ( version_compare( $sVersion, '44', '>=' ) || $sVersion == 'Unknown' )
                                {
                                        $this->fontHash .= 'woff2/unicode';
                                }
                                elseif ( version_compare( $sVersion, '39', '>=' ) )
                                {
                                        $this->fontHash .= 'woff2';
                                }
                                elseif ( version_compare( $sVersion, '11', '>=' ) )
                                {
                                        $this->fontHash .= 'woff';
                                }

                                break;

                        case 'Edge':

                                if ( version_compare( $sVersion, '17', '>=' ) || $sVersion == 'Unknown' )
                                {
                                        $this->fontHash .= 'woff2/unicode';
                                }
                                elseif ( version_compare( $sVersion, '15', '>=' ) )
                                {
                                        $this->fontHash .= 'woff2';
                                }

                                break;

                        case 'Internet Explorer':

                                if ( version_compare( $sVersion, '9', '>=' ) || $sVersion == 'Unknown' )
                                {
                                        $this->fontHash .= 'woff';
                                }
                                elseif ( version_compare( $sVersion, '7', '>=' ) )
                                {
                                        $this->fontHash .= 'eot';
                                }

                                break;

                        case 'Opera':

                                if ( version_compare( $sVersion, '20', '>=' ) || $sVersion == 'Unknown' )
                                {
                                        $this->fontHash .= 'woff2/unicode';
                                }
                                elseif ( version_compare( $sVersion, '11.1', '>=' ) )
                                {
                                        $this->fontHash .= 'woff';
                                }
                                elseif ( version_compare( $sVersion, '10.6', '>=' ) )
                                {
                                        $this->fontHash .= 'ttf';
                                }

                                break;

                        case 'Safari':

                                if ( version_compare( $sVersion, '10.1', '>=' ) || $sVersion == 'Unknown' )
                                {
                                        $this->fontHash .= 'woff2/unicode';
                                }
                                elseif ( version_compare( $sVersion, '5.1', '>=' ) )
                                {
                                        $this->fontHash .= 'woff';
                                }
                                elseif ( version_compare( $sVersion, '4', '>=' ) )
                                {
                                        $this->fontHash .= 'ttf';
                                }

                                break;

                        default:
                                break;
                }
        }

        public static function getInstance( $userAgent = '' )
        {
                if ( $userAgent == '' && isset( $_SERVER[ 'HTTP_USER_AGENT' ] ) )
                {
                        $userAgent = trim( $_SERVER[ 'HTTP_USER_AGENT' ] );
                }

                $signature = md5( $userAgent );

                if ( ! isset( self::$instances[ $signature ] ) )
                {
                        self::$instances[ $signature ] = new Browser( $userAgent );
                }

                return self::$instances[ $signature ];
        }

        public function getBrowser()
        {
                return $this->oClient->browser;
        }

        public function getFontHash()
        {
                return $this->fontHash;
        }

        public function getVersion()
        {
                return $this->oClient->browserVersion;
        }

}
