<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2016 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 * @version   $Id$
 */

// Protect from unauthorized access
defined('_JEXEC') or die;

JLoader::import('joomla.application.component.model');

class AdmintoolsModelWcmaker extends F0FModel
{
	var $defaultConfig = array(
		// == System configuration ==
		// Host name for HTTPS requests (without https://)
		'httpshost'      => '',
		// Host name for HTTP requests (without http://)
		'httphost'       => '',
		// Base directory of your site (/ for domain's root)
		'rewritebase'    => '',

		// == Optimization and utility ==
		// Force index.php parsing before index.html
		'fileorder'      => 1,
		// Set default expiration time to 1 hour
		'exptime'        => 0,
		// Automatically compress static resources
		'autocompress'   => 0,
		// Redirect index.php to root
		'autoroot'       => 1,
		// Redirect www and non-www addresses
		'wwwredir'       => 0,
		// Redirect old to new domain
		'olddomain'      => '',
		// Force HTTPS for these URLs
		'httpsurls'      => array(),
		// HSTS Header (for HTTPS-only sites)
		'hstsheader'     => 0,
		// Disable HTTP methods TRACE and TRACK (protect against XST)
		'notracetrack'   => 0,
		// Cross-Origin Resource Sharing (CORS)
		'cors'     => 0,
		// Set UTF-8 charset as default
		'utf8charset'     => 1,
		// Send ETag
		'etagtype' => 'default',

		// == Basic security ==
		// Disable directory listings
		'nodirlists'     => 1,
		// Protect against common file injection attacks
		'fileinj'        => 1,
		// Disable PHP Easter Eggs
		'phpeaster'      => 1,
		// Block access from specific user agents
		'nohoggers'      => 0,
		// Block access to configuration.php-dist and web.config.txt
		'leftovers'      => 1,
		// Protect against clickjacking
		'clickjacking'   => 1,
		// Reduce MIME type security risks
	    'reducemimetyperisks' => 1,
	    // Reflected XSS prevention
	    'reflectedxss' => 1,
	    // Remove Apache and PHP version signature
	    'noserversignature' => 1,
		// Prevent content transformation
	    'notransform' => 1,
		// User agents to block (one per line)
		'hoggeragents'   => array(
			'WebBandit',
			'webbandit',
			'Acunetix',
			'binlar',
			'BlackWidow',
			'Bolt 0',
			'Bot mailto:craftbot@yahoo.com',
			'BOT for JCE',
			'casper',
			'checkprivacy',
			'ChinaClaw',
			'clshttp',
			'cmsworldmap',
			'comodo',
			'Custo',
			'Default Browser 0',
			'diavol',
			'DIIbot',
			'DISCo',
			'dotbot',
			'Download Demon',
			'eCatch',
			'EirGrabber',
			'EmailCollector',
			'EmailSiphon',
			'EmailWolf',
			'Express WebPictures',
			'extract',
			'ExtractorPro',
			'EyeNetIE',
			'feedfinder',
			'FHscan',
			'FlashGet',
			'flicky',
			'GetRight',
			'GetWeb!',
			'Go-Ahead-Got-It',
			'Go!Zilla',
			'grab',
			'GrabNet',
			'Grafula',
			'harvest',
			'HMView',
			'ia_archiver',
			'Image Stripper',
			'Image Sucker',
			'InterGET',
			'Internet Ninja',
			'InternetSeer.com',
			'jakarta',
			'Java',
			'JetCar',
			'JOC Web Spider',
			'kmccrew',
			'larbin',
			'LeechFTP',
			'libwww',
			'Mass Downloader',
			'Maxthon$',
			'microsoft.url',
			'MIDown tool',
			'miner',
			'Mister PiX',
			'NEWT',
			'MSFrontPage',
			'Navroad',
			'NearSite',
			'Net Vampire',
			'NetAnts',
			'NetSpider',
			'NetZIP',
			'nutch',
			'Octopus',
			'Offline Explorer',
			'Offline Navigator',
			'PageGrabber',
			'Papa Foto',
			'pavuk',
			'pcBrowser',
			'PeoplePal',
			'planetwork',
			'psbot',
			'purebot',
			'pycurl',
			'RealDownload',
			'ReGet',
			'Rippers 0',
			'SeaMonkey$',
			'sitecheck.internetseer.com',
			'SiteSnagger',
			'skygrid',
			'SmartDownload',
			'sucker',
			'SuperBot',
			'SuperHTTP',
			'Surfbot',
			'tAkeOut',
			'Teleport Pro',
			'Toata dragostea mea pentru diavola',
			'turnit',
			'vikspider',
			'VoidEYE',
			'Web Image Collector',
			'Web Sucker',
			'WebAuto',
			'WebCopier',
			'WebFetch',
			'WebGo IS',
			'WebLeacher',
			'WebReaper',
			'WebSauger',
			'Website eXtractor',
			'Website Quester',
			'WebStripper',
			'WebWhacker',
			'WebZIP',
			'Wget',
			'Widow',
			'WWW-Mechanize',
			'WWWOFFLE',
			'Xaldon WebSpider',
			'Yandex',
			'Zeus',
			'zmeu',
			'CazoodleBot',
			'discobot',
			'ecxi',
			'GT::WWW',
			'heritrix',
			'HTTP::Lite',
			'HTTrack',
			'ia_archiver',
			'id-search',
			'id-search.org',
			'IDBot',
			'Indy Library',
			'IRLbot',
			'ISC Systems iRc Search 2.1',
			'LinksManager.com_bot',
			'linkwalker',
			'lwp-trivial',
			'MFC_Tear_Sample',
			'Microsoft URL Control',
			'Missigua Locator',
			'panscient.com',
			'PECL::HTTP',
			'PHPCrawl',
			'PleaseCrawl',
			'SBIder',
			'Snoopy',
			'Steeler',
			'URI::Fetch',
			'urllib',
			'Web Sucker',
			'webalta',
			'WebCollage',
			'Wells Search II',
			'WEP Search',
			'zermelo',
			'ZyBorg',
			'Indy Library',
			'libwww-perl',
			'Go!Zilla',
			'TurnitinBot',
		),

		// == Server protection ==
		// -- Toggle protection
		// Back-end protection
		'backendprot'    => 1,
		// Back-end protection
		'frontendprot'   => 1,
		// -- Fine-tuning
		// Back-end directories where file type exceptions are allowed
		'bepexdirs'      => array('components', 'modules', 'templates', 'images', 'plugins'),
		// Back-end file types allowed in selected directories
		'bepextypes'     => array(
			'jpe', 'jpg', 'jpeg', 'jp2', 'jpe2', 'png', 'gif', 'bmp', 'css', 'js',
			'swf', 'html', 'mpg', 'mp3', 'mpeg', 'mp4', 'avi', 'wav', 'ogg', 'ogv',
			'xls', 'xlsx', 'doc', 'docx', 'ppt', 'pptx', 'zip', 'rar', 'pdf', 'xps',
			'txt', '7z', 'svg', 'odt', 'ods', 'odp', 'flv', 'mov', 'htm', 'ttf',
			'woff', 'woff2', 'eot',
			'JPG', 'JPEG', 'PNG', 'GIF', 'CSS', 'JS', 'TTF', 'WOFF', 'WOFF2', 'EOT'
		),
		// Front-end directories where file type exceptions are allowed
		'fepexdirs'      => array('components', 'modules', 'templates', 'images', 'plugins', 'media', 'libraries', 'media/jui/fonts'),
		// Front-end file types allowed in selected directories
		'fepextypes'     => array(
			'jpe', 'jpg', 'jpeg', 'jp2', 'jpe2', 'png', 'gif', 'bmp', 'css', 'js',
			'swf', 'html', 'mpg', 'mp3', 'mpeg', 'mp4', 'avi', 'wav', 'ogg', 'ogv',
			'xls', 'xlsx', 'doc', 'docx', 'ppt', 'pptx', 'zip', 'rar', 'pdf', 'xps',
			'txt', '7z', 'svg', 'odt', 'ods', 'odp', 'flv', 'mov', 'ico', 'htm',
			'ttf', 'woff', 'woff2', 'eot',
			'JPG', 'JPEG', 'PNG', 'GIF', 'CSS', 'JS', 'TTF', 'WOFF', 'WOFF2', 'EOT'
		),
		// -- Exceptions
		// Allow direct access to these files
		'exceptionfiles' => array(
			"administrator/components/com_akeeba/restore.php",
			"administrator/components/com_admintools/restore.php",
			"administrator/components/com_joomlaupdate/restore.php"
		),
		// Allow direct access, except .php files, to these directories
		'exceptiondirs'  => array(),
		// Allow direct access, including .php files, to these directories
		'fullaccessdirs' => array(
			"templates/your_template_name_here"
		),
	);

	private $config = null;

	public function  __construct($config = array())
	{
		parent::__construct($config);

		$myURI = JURI::getInstance();
		$path = $myURI->getPath();
		$path_parts = explode('/', $path);
		$path_parts = array_slice($path_parts, 0, count($path_parts) - 2);
		$path = implode('/', $path_parts);
		$myURI->setPath($path);
		// Unset any query parameters
		$myURI->setQuery('');

		$host = $myURI->toString();
		$host = substr($host, strpos($host, '://') + 3);

		$path = trim($path, '/');

		$this->defaultConfig['httphost'] = $host;
		$this->defaultConfig['httpshost'] = $host;
		$this->defaultConfig = (object)$this->defaultConfig;
	}

	public function loadConfiguration()
	{
		if (is_null($this->config))
		{

			if (interface_exists('JModel'))
			{
				$params = JModelLegacy::getInstance('Storage', 'AdmintoolsModel');
			}
			else
			{
				$params = JModel::getInstance('Storage', 'AdmintoolsModel');
			}
			$savedConfig = $params->getValue('wcconfig', '');
			if (!empty($savedConfig))
			{
				if (function_exists('base64_encode') && function_exists('base64_encode'))
				{
					$savedConfig = base64_decode($savedConfig);
				}
				$savedConfig = json_decode($savedConfig, true);
			}
			else
			{
				$savedConfig = array();
			}

			$config = $this->defaultConfig;
			if (!empty($savedConfig))
			{
				foreach ($savedConfig as $key => $value)
				{
					$config->$key = $value;
				}
			}

			$this->config = $config;
		}

		return $this->config;
	}

	public function saveConfiguration($data, $isConfigInput = false)
	{
		// Make sure we are called by an expected caller
		if (!class_exists('AdmintoolsHelperServertech'))
		{
			require_once JPATH_ADMINISTRATOR . '/components/com_admintools/helpers/servertech.php';
		}

		AdmintoolsHelperServertech::checkCaller(array(
			'AdmintoolsControllerWcmaker::apply',
			'AdmintoolsControllerWcmaker::save',
			'AdmintoolsModelQuickstart::applyHtmaker',
			'AdmintoolsModelQuickstart::applyWcmaker',
		));

		if ($isConfigInput)
		{
			$config = $data;
		}
		else
		{
			$config = $this->defaultConfig;
			if (!empty($data))
			{
				$ovars = get_object_vars($config);
				$okeys = array_keys($ovars);
				foreach ($data as $key => $value)
				{
					if (in_array($key, $okeys))
					{
						// Clean up array types coming from textareas
						if (in_array($key, array(
							'hoggeragents', 'bepexdirs',
							'bepextypes', 'fepexdirs', 'fepextypes',
							'exceptionfiles', 'exceptionfolders', 'exceptiondirs', 'fullaccessdirs',
							'httpsurls'
						))
						)
						{
							if (empty($value))
							{
								$value = array();
							}
							else
							{
								$value = trim($value);
								$value = explode("\n", $value);
								if (!empty($value))
								{
									$ret = array();
									foreach ($value as $v)
									{
										$vv = trim($v);
										if (!empty($vv))
										{
											$ret[] = $vv;
										}
									}
									if (!empty($ret))
									{
										$value = $ret;
									}
									else
									{
										$value = array();
									}
								}
							}
						}
						$config->$key = $value;
					}
				}
			}
		}

		// Make sure nobody tried to add the php extension to the list of allowed extension
		$disallowedExtensions = array('php', 'phP', 'pHp', 'pHP', 'Php', 'PhP', 'PHp', 'PHP');

		foreach ($disallowedExtensions as $ext)
		{
			$pos = array_search($ext, $config->bepextypes);

			if ($pos !== false)
			{
				unset($config->bepextypes[$pos]);
			}

			$pos = array_search($ext, $config->fepextypes);

			if ($pos !== false)
			{
				unset($config->fepextypes[$pos]);
			}
		}

		$this->config = $config;
		$config = json_encode($config);

		// This keeps JRegistry from hapily corrupting our data :@
		if (function_exists('base64_encode') && function_exists('base64_encode'))
		{
			$config = base64_encode($config);
		}

		if (interface_exists('JModel'))
		{
			$params = JModelLegacy::getInstance('Storage', 'AdmintoolsModel');
		}
		else
		{
			$params = JModel::getInstance('Storage', 'AdmintoolsModel');
		}

		$params->setValue('wcconfig', $config);
		$params->setValue('quickstart', 1);

		$params->save();
	}

	public function makeWebConfig()
	{
		// Make sure we are called by an expected caller
		if (!class_exists('AdmintoolsHelperServertech'))
		{
			require_once JPATH_ADMINISTRATOR . '/components/com_admintools/helpers/servertech.php';
		}

		AdmintoolsHelperServertech::checkCaller(array(
			'AdmintoolsControllerWcmaker::apply',
			'AdmintoolsModelWcmaker::writeWebConfig',
			'AdmintoolsViewWcmaker::onBrowse'
		));

		JLoader::import('joomla.utilities.date');
		$date = new JDate();
		$d = $date->format('Y-m-d H:i:s', true);
		$version = ADMINTOOLS_VERSION;

		$webConfig = <<< XML
<?xml version="1.0" encoding="utf-8"?>
<!--
	Security Enhanced & Highly Optimized .web.config File for Joomla!
	automatically generated by Admin Tools $version on $d GMT

	Admin Tools is Free Software, distributed under the terms of the GNU
	General Public License version 3 or, at your option, any later version
	published by the Free Software Foundation.

	!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! IMPORTANT !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
	!!                                                                       !!
	!!  If you get an Internal Server Error 500 or a blank page when trying  !!
	!!  to access your site, remove this file and try tweaking its settings  !!
	!!  in the back-end of the Admin Tools component.                        !!
	!!                                                                       !!
	!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
-->
<configuration>
    <system.webServer>

XML;

		$config = $this->loadConfiguration();

		if ($config->fileorder == 1)
		{
			$webConfig .= <<< XML
		<!-- File execution order -->
        <defaultDocument enabled="true">
            <files>
				<clear />
                <add value="index.php" />
				<add value="index.html" />
				<add value="index.htm" />
            </files>
        </defaultDocument>

XML;
		}

		if ($config->nodirlists == 1)
		{
			$webConfig .= <<< XML
		<!-- No directory listings -->
		<directoryBrowse enabled="false" />

XML;
		}

		if ($config->exptime == 1)
		{
			$setEtag = ($config->etagtype == 'none') ? 'setEtag="false"' : '';
			$eTagInfo = ($config->etagtype == 'none') ? '// Send ETag: false (IIS only supports true/false for ETags)' : '';

			$webConfig .= <<< XML
		<!-- Optimal default expiration time $eTagInfo -->
        <staticContent>
            <clientCache cacheControlMode="UseMaxAge" cacheControlMaxAge="01:00:00" $setEtag />
        </staticContent>

XML;
		}

		if ($config->autocompress == 1)
		{
			$webConfig .= <<<XML
		<urlCompression doStaticCompression="false" doDynamicCompression="true" />
        <httpCompression>
            <dynamicTypes>
                <clear />
                <add mimeType="text/*" enabled="true" />
                <add mimeType="message/*" enabled="true" />
                <add mimeType="application/javascript" enabled="true" />
                <add mimeType="application/x-javascript" enabled="true" />
                <add mimeType="application/xhtml+xml" enabled="true" />
                <add mimeType="*/*" enabled="false" />
            </dynamicTypes>
        </httpCompression>

XML;
		}


		$webConfig .= <<< XML
        <rewrite>
            <rules>
            	<clear />

XML;

		if (!empty($config->hoggeragents) && ($config->nohoggers == 1))
		{
			$conditions   = '';
			$patternCache = array();

			foreach ($config->hoggeragents as $agent)
			{
				$patternCache[] = $agent;

				if (count($agent) < 10)
				{
					continue;
				}

				$newPattern = implode('|', $patternCache);
				$conditions .= <<< XML
<add input="{HTTP_USER_AGENT}" patternCache="$newPattern" />
XML;
				$patternCache = array();
			}

			if (count($patternCache))
			{
				$newPattern = implode('|', $patternCache);
				$conditions .= <<< XML
                        <add input="{HTTP_USER_AGENT}" patternCache="$newPattern" />
XML;
			}

			$webConfig .= <<< XML
				<rule name="Common hacking tools and bandwidth hoggers block" stopProcessing="true">
                    <match url=".*" />
                    <conditions logicalGrouping="MatchAny" trackAllCaptures="false">
$conditions
                    </conditions>
                    <action type="CustomResponse" statusCode="403" statusReason="Forbidden: Access is denied." statusDescription="You do not have permission to view this directory or page using the credentials that you supplied." />
                </rule>
XML;
		}

		if ($config->autoroot)
		{
			$webConfig .= <<<XML
                <rule name="Redirect index.php to /" stopProcessing="true">
                    <match url="^index\.php$" ignoreCase="false" />
                    <conditions logicalGrouping="MatchAll">
                        <add input="{THE_REQUEST}" pattern="^POST" ignoreCase="false" negate="true" />
                        <add input="{THE_REQUEST}" pattern="^[A-Z]{3,9}\ /index\.php\ HTTP/" ignoreCase="false" />
                        <add input="{HTTPS}>s" pattern="^(1>(s)|0>s)$" ignoreCase="false" />
                    </conditions>
                    <action type="Redirect" url="http{C:2}://{HTTP_HOST}:{SERVER_PORT }/" redirectType="Permanent" />
                </rule>

XML;
		}

		switch ($config->wwwredir)
		{
			case 1:
                // If I have a rewriteBase condition, I have to append it here
                $subfolder = trim($config->rewritebase, '/') ? trim($config->rewritebase, '/').'/' : '';

				// non-www to www
				$webConfig .= <<<END
				<rule name="Redirect non-www to www" stopProcessing="true">
                    <match url="^(.*)$" ignoreCase="false" />
                    <conditions logicalGrouping="MatchAll">
                        <add input="{HTTP_HOST}" pattern="^www\." negate="true" />
                    </conditions>
                    <action type="Redirect" url="http://www.{HTTP_HOST}/$subfolder{R:1}" redirectType="Found" />
                </rule>

END;
				break;

			case 2:
				// www to non-www
				$webConfig .= <<<END
				<rule name="Redirect www to non-www" stopProcessing="true">
                    <match url="^(.*)$" ignoreCase="false" />
                    <conditions logicalGrouping="MatchAll">
                        <add input="{HTTP_HOST}" pattern="^www\.(.+)$" />
                    </conditions>
                    <action type="Redirect" url="http://{C:1}/{R:1}" redirectType="Found" />
                </rule>

END;
				break;
		}

		if (!empty($config->olddomain))
		{
			$domains = trim($config->olddomain);
			$domains = explode(',', $domains);
			$newdomain = $config->httphost;

			foreach ($domains as $olddomain)
			{
				$olddomain = trim($olddomain);
				$originalOldDomain = $olddomain;

				if (empty($olddomain))
				{
					continue;
				}

				$olddomain = $this->escape_string_for_regex($olddomain);

				$webConfig .= <<<END
                <rule name="Redirect old to new domain ($originalOldDomain)" stopProcessing="true">
                    <match url="(.*)" ignoreCase="false" />
                    <conditions logicalGrouping="MatchAll">
                        <add input="{HTTP_HOST}" pattern="^$olddomain" />
                    </conditions>
                    <action type="Redirect" url="http://$newdomain/{R:1}" redirectType="Found" />
                </rule>

END;
			}
		}

		if (!empty($config->httpsurls))
		{
			$webConfig .= "<!-- Force HTTPS for certain pages -->\n";
			foreach ($config->httpsurls as $url)
			{
				$urlesc = '^' . $this->escape_string_for_regex($url) . '$';
				$webConfig .= <<<END
                <rule name="Force HTTPS for $url" stopProcessing="true">
                    <match url="^$urlesc$" ignoreCase="false" />
                    <conditions logicalGrouping="MatchAny">
                        <add input="{HTTPS}" pattern="0" />
                    </conditions>
                    <action type="Redirect" url="https://{$config->httpshost}/$url" redirectType="Found" />
                </rule>

END;
			}
		}

		$webConfig .= <<<END
                <rule name="Block out some common exploits">
                    <match url=".*" ignoreCase="false" />
                    <conditions logicalGrouping="MatchAny" trackAllCaptures="false">
                        <add input="{QUERY_STRING}" pattern="proc/self/environ" ignoreCase="false" />
                        <add input="{QUERY_STRING}" pattern="mosConfig_[a-zA-Z_]{1,21}(=|\%3D)" ignoreCase="false" />
                        <add input="{QUERY_STRING}" pattern="base64_(en|de)code\(.*\)" ignoreCase="false" />
                        <add input="{QUERY_STRING}" pattern="(&lt;|%3C).*script.*(>|%3E)" />
                        <add input="{QUERY_STRING}" pattern="GLOBALS(=|\[|\%[0-9A-Z]{0,2})" ignoreCase="false" />
                        <add input="{QUERY_STRING}" pattern="_REQUEST(=|\[|\%[0-9A-Z]{0,2})" ignoreCase="false" />
                    </conditions>
                    <action type="CustomResponse" url="index.php" statusCode="403" statusReason="Forbidden" statusDescription="Forbidden" />
                </rule>

END;

		if ($config->fileinj == 1)
		{
			$webConfig .= <<<END
                <rule name="File injection protection" stopProcessing="true">
                    <match url=".*" ignoreCase="false" />
                    <conditions logicalGrouping="MatchAny" trackAllCaptures="false">
                        <add input="{QUERY_STRING}" pattern="[a-zA-Z0-9_]=http://" ignoreCase="false" />
                        <add input="{QUERY_STRING}" pattern="[a-zA-Z0-9_]=(\.\.//?)+" ignoreCase="false" />
                        <add input="{QUERY_STRING}" pattern="[a-zA-Z0-9_]=/([a-z0-9_.]//?)+" />
                    </conditions>
                    <action type="CustomResponse" statusCode="403" statusReason="Forbidden" statusDescription="Forbidden" />
                </rule>

END;
		}

		$webConfig .= "                <!-- Advanced server protection rules exceptions -->\n";

		if (!empty($config->exceptionfiles))
		{
			$ruleCounter = 0;

			foreach ($config->exceptionfiles as $file)
			{
				$ruleCounter++;
				$file = '^' . $this->escape_string_for_regex($file) . '$';
				$webConfig .= <<<END
                <rule name="Advanced server protection rules exception #$ruleCounter" stopProcessing="true">
                    <match url="$file" ignoreCase="false" />
                    <conditions logicalGrouping="MatchAll" trackAllCaptures="false" />
                    <action type="None" />
                </rule>

END;
			}
		}

		if (!empty($config->exceptiondirs))
		{
			$ruleCounter = 0;

			foreach ($config->exceptiondirs as $dir)
			{
				$ruleCounter++;
				$dir = trim($dir, '/');
				$dir = $this->escape_string_for_regex($dir);
				$webConfig .= <<<END
				<rule name="Allow access to folders except .php files #$ruleCounter" stopProcessing="true">
					<match url="^$dir/" ignoreCase="false" />
					<conditions logicalGrouping="MatchAll" trackAllCaptures="false">
						<add input="{REQUEST_FILENAME}" pattern="(\.php)$" ignoreCase="false" negate="true" />
						<add input="{REQUEST_FILENAME}" matchType="IsFile" ignoreCase="false"
					</conditions>
					<action type="None" />
				</rule>

END;
			}
		}

		if (!empty($config->fullaccessdirs))
		{
			$ruleCounter = 0;

			foreach ($config->fullaccessdirs as $dir)
			{
				$ruleCounter++;
				$dir = trim($dir, '/');
				$dir = $this->escape_string_for_regex($dir);
				$webConfig .= <<<END
				<rule name="Allow access to folders, including .php files #$ruleCounter" stopProcessing="true">
					<match url="^$dir/" ignoreCase="false" />
					<action type="None" />
				</rule>

END;
			}
		}

		if ($config->phpeaster == 1)
		{
			$webConfig .= <<<END
                <rule name="PHP Easter Egg protection">
                    <match url=".*" ignoreCase="false" />
                    <conditions logicalGrouping="MatchAll" trackAllCaptures="false">
                        <add input="{QUERY_STRING}" pattern="\=PHP[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}" />
                    </conditions>
                    <action type="CustomResponse" statusCode="403" statusReason="Forbidden" statusDescription="Forbidden" />
                </rule>

END;
		}

		if ($config->backendprot == 1)
		{
			$bedirs = implode('|', $config->bepexdirs);
			$betypes = implode('|', $config->bepextypes);
			$webConfig .= <<<END
                <rule name="Back-end protection - allow administrator login" stopProcessing="true">
                    <match url="^administrator/?$" ignoreCase="false" />
                    <conditions logicalGrouping="MatchAll" trackAllCaptures="false" />
                    <action type="None" />
                </rule>
                <rule name="Back-end protection - allow administrator login, alternate" stopProcessing="true">
                    <match url="^administrator/index\.(php|html?)$" ignoreCase="false" />
                    <conditions logicalGrouping="MatchAll" trackAllCaptures="false" />
                    <action type="None" />
                </rule>
                <rule name="Back-end protection - allow access to static media files" stopProcessing="true">
                    <match url="^administrator/($bedirs)/.*\.($betypes)$" ignoreCase="false" />
                    <conditions logicalGrouping="MatchAll" trackAllCaptures="false" />
                    <action type="None" />
                </rule>
                <rule name="Back-end protection - Catch all">
                    <match url="^administrator/" ignoreCase="false" />
                    <conditions logicalGrouping="MatchAll" trackAllCaptures="false" />
                    <action type="CustomResponse" statusCode="403" statusReason="Forbidden" statusDescription="Forbidden" />
                </rule>

END;
		}

		if ($config->frontendprot == 1)
		{
			$fedirs = implode('|', $config->fepexdirs);
			$fetypes = implode('|', $config->fepextypes);
			$webConfig .= <<<END
                <rule name="Front-end protection - allow access to static media files" stopProcessing="true">
                    <match url="^($fedirs)/.*\.($fetypes)$" ignoreCase="false" />
                    <conditions logicalGrouping="MatchAll" trackAllCaptures="false" />
                    <action type="None" />
                </rule>
                <rule name="Front-end protection - Do not block includes/js" stopProcessing="true">
                    <match url="^includes/js/" ignoreCase="false" />
                    <conditions logicalGrouping="MatchAll" trackAllCaptures="false" />
                    <action type="None" />
                </rule>
                <rule name="Front-end protection - Block access to certain folders">
                    <match url="^($fedirs)/" ignoreCase="false" />
                    <conditions logicalGrouping="MatchAll" trackAllCaptures="false" />
                    <action type="CustomResponse" statusCode="403" statusReason="Forbidden" statusDescription="Forbidden" />
                </rule>
                <rule name="Front-end protection - Block access to certain folders, part 2">
                    <match url="^(cache|includes|language|logs|log|tmp)/" ignoreCase="false" />
                    <conditions logicalGrouping="MatchAll" trackAllCaptures="false" />
                    <action type="CustomResponse" statusCode="403" statusReason="Forbidden" statusDescription="Forbidden" />
                </rule>
                <rule name="Front-end protection - Forbid access to leftover Joomla! files">
                    <match url="^(configuration\.php|CONTRIBUTING\.md|htaccess\.txt|joomla\.xml|LICENSE\.txt|phpunit\.xml|README\.txt|web\.config\.txt)" ignoreCase="false" />
                    <conditions logicalGrouping="MatchAll" trackAllCaptures="false" />
                    <action type="CustomResponse" statusCode="403" statusReason="Forbidden" statusDescription="Forbidden" />
                </rule>
                <rule name="Front-end protection - Block access to all PHP files except index.php">
                    <match url="(.*\.php)$" ignoreCase="false" />
                    <conditions logicalGrouping="MatchAll" trackAllCaptures="false">
                        <add input="{REQUEST_FILENAME}" pattern="(\.php)$" ignoreCase="false" />
                        <add input="{REQUEST_FILENAME}" pattern="(/index?\.php)$" ignoreCase="false" negate="true" />
                        <add input="{REQUEST_FILENAME}" matchType="IsFile" ignoreCase="false" />
                    </conditions>
                    <action type="CustomResponse" statusCode="403" statusReason="Forbidden" statusDescription="Forbidden" />
                </rule>

END;
		}

		if ($config->leftovers == 1)
		{
			$webConfig .= <<<END
                <rule name="Front-end protection - Block access to common server configuration files">
                    <match url="^(htaccess\.txt|configuration\.php-dist|php\.ini|.user\.ini|web\.config|web\.config\.txt)$" ignoreCase="false" />
                    <conditions logicalGrouping="MatchAll" trackAllCaptures="false" />
                    <action type="CustomResponse" statusCode="403" statusReason="Forbidden" statusDescription="Forbidden" />
                </rule>

END;
		}

		$webConfig .= <<< XML
                <rule name="Joomla! SEF Rule 1" stopProcessing="true">
                    <match url="^(.*)$" ignoreCase="false" />
                    <conditions logicalGrouping="MatchAny" trackAllCaptures="false">
                        <add input="{QUERY_STRING}" pattern="base64_encode[^(]*\([^)]*\)" ignoreCase="false" />
                        <add input="{QUERY_STRING}" pattern="(>|%3C)([^s]*s)+cript.*(&lt;|%3E)" />
                        <add input="{QUERY_STRING}" pattern="GLOBALS(=|\[|\%[0-9A-Z]{0,2})" ignoreCase="false" />
                        <add input="{QUERY_STRING}" pattern="_REQUEST(=|\[|\%[0-9A-Z]{0,2})" ignoreCase="false" />
                    </conditions>
                    <action type="CustomResponse" url="index.php" statusCode="403" statusReason="Forbidden" statusDescription="Forbidden" />
                </rule>
                <rule name="Joomla! SEF Rule 2">
                    <match url="(.*)" ignoreCase="false" />
                    <conditions logicalGrouping="MatchAll" trackAllCaptures="false">
                        <add input="{URL}" pattern="^/index.php" ignoreCase="true" negate="true" />
                        <add input="{REQUEST_FILENAME}" matchType="IsFile" ignoreCase="false" negate="true" />
                        <add input="{REQUEST_FILENAME}" matchType="IsDirectory" ignoreCase="false" negate="true" />
                    </conditions>
                    <action type="Rewrite" url="index.php" />
                </rule>

            </rules>

XML;

		if ($config->noserversignature == 1)
		{
			$webConfig .= <<< XML
		<!-- Remove IIS version signature -->
		<outboundRules>
		  <rule name="Remove RESPONSE_Server">
			<match serverVariable="RESPONSE_Server" pattern=".+" />
			<action type="Rewrite" value="MYOB" />
		  </rule>
		</outboundRules>

XML;
		}

		$webConfig .= <<< XML
        </rewrite>
        <httpProtocol>
        	<customHeaders>

XML;

		if ($config->clickjacking == 1)
		{
			$webConfig .= <<< ENDCONF
				<!-- Protect against clickjacking / Forbid displaying in FRAME -->
				<add name="X-Frame-Options" value="SAMEORIGIN" />

ENDCONF;
		}

		if ($config->reducemimetyperisks == 1)
		{
			$webConfig .= <<< XML
				<!-- Reduce MIME type security risks -->
				<add name="X-Content-Type-Options" value="nosniff" />

XML;
		}

		if ($config->reflectedxss == 1)
		{
			$webConfig .= <<< XML
				<!-- Reflected XSS prevention -->
				<add name="X-XSS-Protection" value="1; mode=block" />

XML;
		}

		if ($config->noserversignature == 1)
		{
			$webConfig .= <<< XML
				<!-- Remove IIS and PHP version signature -->
				<add name="X-Powered-By" value="MYOB" />

XML;

		}

		if ($config->notransform == 1)
		{
			$webConfig .= <<< XML
				<!-- Prevent content transformation -->
				<add name="Cache-Control" value="no-transform" />

XML;
		}

		if ($config->hstsheader == 1)
		{
			$webConfig .= <<<XML
				<!-- HSTS Header - See http://en.wikipedia.org/wiki/HTTP_Strict_Transport_Security -->
				<add name="Strict-Transport-Security" value="max-age=31536000" />

XML;
		}

		if ($config->cors == 1)
		{
			$action = version_compare($iisVersion, '2.0', 'ge') ? 'always set' : 'set';
			$webConfig .= <<<XML
				<!-- Cross-Origin Resource Sharing (CORS) - See http://enable-cors.org/ -->
				<add name="Access-Control-Allow-Origin" value="*" />
				<add name="Timing-Allow-Origin" value="*" />

XML;
		}

		$webConfig .= <<< XML
			</customHeaders>
        </httpProtocol>

XML;

		if ($config->notracetrack == 1)
		{
			$webConfig .= <<<XML
		<!-- Disable HTTP methods TRACE and TRACK (protect against XST) -->
        <security>
            <requestFiltering>
                <verbs>
                    <add verb="TRACE" allowed="false" />
                    <add verb="TRACK" allowed="false" />
                </verbs>
            </requestFiltering>
        </security>

XML;
		}

		$webConfig .= <<< XML
    </system.webServer>
</configuration>

XML;

		return $webConfig;
	}

	public function writeWebConfig()
	{
		// Make sure we are called by an expected caller
		if (!class_exists('AdmintoolsHelperServertech'))
		{
			require_once JPATH_ADMINISTRATOR . '/components/com_admintools/helpers/servertech.php';
		}

		AdmintoolsHelperServertech::checkCaller(array(
			'AdmintoolsControllerWcmaker::apply',
			'AdmintoolsModelQuickstart::applyHtmaker',
			'AdmintoolsModelQuickstart::applyWcmaker'
		));

		// Make and save the web.config file
		$webConfig = $this->makeWebConfig();

		JLoader::import('joomla.filesystem.file');

		if (@file_exists(JPATH_ROOT . '/web.config'))
		{
			JFile::copy('web.config', 'web.config.admintools', JPATH_ROOT);
		}

		return JFile::write(JPATH_ROOT . DIRECTORY_SEPARATOR . 'web.config', $webConfig);
	}

	private function escape_string_for_regex($str)
	{
		//All regex special chars (according to arkani at iol dot pt below):
		// \ ^ . $ | ( ) [ ]
		// * + ? { } , -

		$patterns = array(
			'/\//', '/\^/', '/\./', '/\$/', '/\|/',
			'/\(/', '/\)/', '/\[/', '/\]/', '/\*/', '/\+/',
			'/\?/', '/\{/', '/\}/', '/\,/', '/\-/'
		);
		$replace = array(
			'\/', '\^', '\.', '\$', '\|', '\(', '\)',
			'\[', '\]', '\*', '\+', '\?', '\{', '\}', '\,', '\-'
		);

		return preg_replace($patterns, $replace, $str);
	}
}