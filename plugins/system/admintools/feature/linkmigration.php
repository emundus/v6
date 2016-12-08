<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2016 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') or die;

class AtsystemFeatureLinkmigration extends AtsystemFeatureAbstract
{
	/** @var null|array The domains to migrate from */
	protected $oldDomains = null;

	/** @var null|string The domain of this site */
	protected $myDomain = null;

	/**
	 * Is this feature enabled?
	 *
	 * @return bool
	 */
	public function isEnabled()
	{
		// We only use this feature in the front-end
		if ($this->helper->isBackend())
		{
			return false;
		}

		// The feature must be enabled
		if ($this->cparams->getValue('linkmigration', 0) != 1)
		{
			return false;
		}

		// Populate the old domains array
		$this->populateOldDomains();

		// If there are no old domains to migrate from, what exactly am I doing here?
		if (empty($this->oldDomains))
		{
			return false;
		}

		return true;
	}

	/**
	 * Provides link migration services. All absolute links pointing to any of the old domain names
	 * are being rewritten to point to the current domain name. This runs a full page replacement
	 * using Regular Expressions, so even menus with absolute URLs will be migrated!
	 */
	public function onAfterRenderLatebound()
	{
		$this->populateOldDomains();

		if (empty($this->oldDomains))
		{
			// If there are no old domains to migrate from, what exactly am I doing here?
			return;
		}

		$this->populateMyDomain();

		if (method_exists($this->app, 'getBody'))
		{
			$buffer = $this->app->getBody();
		}
		else
		{
			$buffer = JResponse::getBody();
		}

		$pattern = '/(href|src)=\"([^"]*)\"/i';
		$number_of_matches = preg_match_all($pattern, $buffer, $matches, PREG_OFFSET_CAPTURE);

		if ($number_of_matches > 0)
		{
			$substitutions = $matches[2];
			$last_position = 0;
			$temp = '';

			// Loop all URLs
			foreach ($substitutions as &$entry)
			{
				// Copy unchanged part, if it exists
				if ($entry[1] > 0)
				{
					$temp .= substr($buffer, $last_position, $entry[1] - $last_position);
				}

				// Add the new URL
				$temp .= $this->replaceDomain($entry[0]);

				// Calculate next starting offset
				$last_position = $entry[1] + strlen($entry[0]);
			}

			// Do we have any remaining part of the string we have to copy?
			if ($last_position < strlen($buffer))
			{
				$temp .= substr($buffer, $last_position);
			}

			// Replace content with the processed one
			unset($buffer);

			if (method_exists($this->app, 'setBody'))
			{
				$this->app->setBody($temp);
			}
			else
			{
				JResponse::setBody($temp);
			}

			unset($temp);
		}
	}

	/**
	 * Replaces a URL's domain name (if it is in the substitution list) with the
	 * current site's domain name
	 *
	 * @param $url string The URL to process
	 *
	 * @return string The processed URL
	 */
	protected function replaceDomain($url)
	{
		foreach ($this->oldDomains as $domain)
		{
			if (substr($url, 0, strlen($domain)) == $domain)
			{
				return $this->myDomain . substr($url, strlen($domain));
			}
			elseif (substr($url, 0, strlen($domain) + 7) == 'http://' . $domain)
			{
				return 'http://' . $this->myDomain . substr($url, strlen($domain) + 7);
			}
			elseif (substr($url, 0, strlen($domain) + 8) == 'https://' . $domain)
			{
				return 'https://' . $this->myDomain . substr($url, strlen($domain) + 8);
			}
		}

		return $url;
	}

	/**
	 * Populates the oldDomains array
	 *
	 * @return  void
	 */
	protected function populateOldDomains()
	{
		$this->oldDomains = array();

		$temp = explode("\n", $this->cparams->getValue('migratelist', ''));

		if (!empty($temp))
		{
			foreach ($temp as $entry)
			{
				if (substr($entry, -1) == '/')
				{
					$entry = substr($entry, 0, -1);
				}

				if (substr($entry, 0, 7) == 'http://')
				{
					$entry = substr($entry, 7);
				}

				if (substr($entry, 0, 8) == 'https://')
				{
					$entry = substr($entry, 8);
				}

				$this->oldDomains[] = $entry;
			}
		}
	}

	/**
	 * Populates the myDomain variable
	 *
	 * @return  void
	 */
	protected function populateMyDomain()
	{
		$this->myDomain = JUri::base(false);

		if (substr($this->myDomain, -1) == '/')
		{
			$this->myDomain = substr($this->myDomain, 0, -1);
		}

		if (substr($this->myDomain, 0, 7) == 'http://')
		{
			$this->myDomain = substr($this->myDomain, 7);
		}

		if (substr($this->myDomain, 0, 8) == 'https://')
		{
			$this->myDomain = substr($this->myDomain, 8);
		}
	}
}