<?php
/**
 * @package     Falang for Joomla!
 * @author      Stéphane Bouey <stephane.bouey@faboba.com> - http://www.faboba.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright   Copyright (C) 2010-2017. Faboba.com All rights reserved.
 */


defined('_JEXEC') or die;

class pkg_falangInstallerScript
{
	/**
	 * The minimum PHP version required to install this extension
	 *
	 * @var   string
	 */
	protected $minimumPHPVersion = '7.0.0';
	
	/**
	 * The minimum Joomla! version required to install this extension
	 *
	 * @var   string
	 */
	protected $minimumJoomlaVersion = '3.9.0';

	/**
	 * The maximum Joomla! version required to install this extension
	 *
	 * @var   string
	 */

	/**
	 * The maximum Joomla! version required to install this extension
	 *
	 * @var   string
	 */
	protected $maximumJoomlaVersion = '3.10.99';
	
	 
    public function install($parent) {}

	public function uninstall($parent)
    {
    }

	public function update($parent) {}

	function preflight($type, $parent)
	{

		// Check the minimum PHP version. Issue a very stern warning if it's not met.
		if (!empty($this->minimumPHPVersion))
		{
			if (defined('PHP_VERSION'))
			{
				$version = PHP_VERSION;
			}
			elseif (function_exists('phpversion'))
			{
				$version = phpversion();
			}
			else
			{
				$version = '5.0.0'; // all bets are off!
			}

			if (!version_compare($version, $this->minimumPHPVersion, 'ge'))
			{
				$msg = "<h1>Your PHP version is too old</h1>";
				$msg .= "<p>You need PHP $this->minimumPHPVersion or later to install this component. Support for PHP 5.3.3 and earlier versions has been discontinued by our company as we publicly announced in February 2013.</p>";
				$msg .= "<p>You are using PHP $version which is an extremely old version, released more than four years ago. This version contains known functional and security issues. The functional issues do not allow you to run Akeeba Backup and cannot be worked around. The security issues mean that your site <b>can be easily hacked</b> since that these security issues are well known for over four years.</p>";
				$msg .= "<p>You have to ask your host to immediately update your site to PHP $this->minimumPHPVersion or later, ideally the latest available version of PHP 5.4. If your host won't do that you are advised to switch to a better host to ensure the security of your site. If you have to stay with your current host for reasons beyond your control you can use Akeeba Backup 4.0.5 or earlier, available from our downloads page.</p>";

				JLog::add($msg, JLog::WARNING, 'jerror');

				return false;
			}
		}

		// abort if the current Joomla release is older
		if( version_compare(JVERSION, $this->minimumJoomlaVersion, 'lt') ) {
			$application = JFactory::getApplication();
			$application->enqueueMessage(JText::_('Cannot install Falang in a Joomla release prior to 3.8.0'), 'notice');
			return false;
		}
		
		if( version_compare(JVERSION, $this->maximumJoomlaVersion, 'gt') ) {
			$application = JFactory::getApplication();
			$application->enqueueMessage(JText::_('This Falang version is not compatible with this Joomla version'), 'notice');
			return false;
		}

	}

    public function postflight($type, $parent) {
		if ($type == 'uninstall')
        {
            return true;
        } 
        ?>		
 <style type="text/css">
            .faboba-installation-wrap{
                padding: 40px;
                overflow: hidden;
                background-color: #0080FE;
                color: #fff;
                font-size: 16px;
                line-height: 26px;
                box-sizing: border-box;
                margin-bottom: 30px;
            }
            
            .faboba-installation-wrap .faboba-installation-left {
                float: left;
                width: 128px;
                height: 128px;
                line-height: 128px;
                text-align: center;
                margin-right: 15px;
                background-color: #fff;
                border-radius: 3px;
                box-shadow: 0 0 5px rgba(0,0,0,0.15);
            }
            .faboba-installation-wrap .faboba-installation-left img{
                display: inline-block;
            }
            .faboba-installation-wrap .faboba-installation-texts p{
                margin-bottom: 26px;
            }
            .faboba-installation-wrap .faboba-installation-texts h2{
                font-size: 24px;
                vertical-align: middle;
            }
            .faboba-installation-wrap .faboba-installation-texts h2 span{
                font-size: 16px;
                color: rgba(255,255,255,0.88);
                border-left: 1px solid rgba(255,255,255, 0.45);
                padding-left: 20px;
                margin-left: 20px;
                vertical-align: middle;
            }
            .faboba-installation-wrap .faboba-installation-footer{
                margin-top: 60px;
            }

            .faboba-installation-wrap .faboba-installation-footer a{
                margin-right: 10px;
            }


            .faboba-installation-wrap .btn-faboba-custom{
                background-color: #fff;
                border: none;
                font-size: 14px;
                padding: 10px 15px;
                color: #0080FE;
                font-weight: 500;
                border-radius: 3px;
            }

            .faboba-installation-wrap .falang-social-links{
                margin-top: 30px;
            }

            .faboba-installation-wrap .falang-social-links a{
                color: #fff;
                font-size: 14px;
                text-decoration: none;
                margin-right: 20px;
            }            
 </style>
<div class="faboba-installation-wrap row-fluid">
			<div class="span4 faboba-installation-left span2">
                   <!-- image -->   
				     <img src="../administrator/components/com_falang/assets/images/logo-80.png" alt="Falang" />
            </div> 
             <div class="faboba-installation-right span8">
                <div class="faboba-installation-texts">
	                <h2>Falang basic<span>3.10.4</span></h2>
	                <p>The easiest way to build a multilanguage site on Joomla</p>
                </div>
                <div class="faboba-installation-footer">
                <div class="falang-links">
                   <!-- links -->   
                    <a class="btn btn-sppb-custom" href="https://www.faboba.com/composants/falang/documentation.html" target="_blank"><span class="fa fa-book"></span> Documentation</a>
				</div>
                <div class="falang-social-links">
                   <!-- Social links -->   
                </div> 
                </div>
                               
			</div>
</div>	 		
<?php
    }
}