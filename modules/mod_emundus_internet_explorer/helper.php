<?php
defined('_JEXEC') or die('Access Denied');

class modEmundusInternetExplorerHelper
{
	/**
	 * Get current browser information
	 * @return array
	 */
	private static function getBrowser(): array
	{
		$u_agent  = $_SERVER['HTTP_USER_AGENT'];
		$bname    = 'Unknown';
		$platform = 'Unknown';
		$version  = 0;

		// First get the platform
		if (preg_match('/linux/i', $u_agent)) {
			$platform = 'linux';
		}
		elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
			$platform = 'mac';
		}
		elseif (preg_match('/windows|win32/i', $u_agent)) {
			$platform = 'windows';
		}

		// Next get the name of the useragent yes seperately and for good reason
		if (preg_match('/MSIE/i', $u_agent) && !preg_match('/Opera/i', $u_agent)) {
			$bname = 'Internet Explorer';
			$ub    = "MSIE";
		}
		elseif (preg_match('/Firefox/i', $u_agent)) {
			$bname = 'Firefox';
			$ub    = "Firefox";
		}
		elseif (preg_match('/OPR/i', $u_agent)) {
			$bname = 'Opera';
			$ub    = "Opera";
		}
		elseif (preg_match('/Chrome/i', $u_agent) && !preg_match('/Edge/i', $u_agent)) {
			$bname = 'Chrome';
			$ub    = "Chrome";
		}
		elseif (preg_match('/Safari/i', $u_agent) && !preg_match('/Edge/i', $u_agent)) {
			$bname = 'Safari';
			$ub    = "Safari";
		}
		elseif (preg_match('/Netscape/i', $u_agent)) {
			$bname = 'Netscape';
			$ub    = "Netscape";
		}
		elseif (preg_match('/Edge/i', $u_agent)) {
			$bname = 'Edge';
			$ub    = "Edge";
		}
		elseif (preg_match('/Trident/i', $u_agent)) {
			$bname = 'Internet Explorer';
			$ub    = "MSIE";
		}

		// Finally get the correct version number
		$known   = array('Version', $ub, 'other');
		$pattern = '#(?<browser>' . join('|', $known) .
			')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
		if (!preg_match_all($pattern, $u_agent, $matches)) {
			// we have no matching number just continue
		}
		// see how many we have
		$i = count($matches['browser']);
		if ($i != 1) {
			//we will have two since we are not using 'other' argument yet
			//see if version is before or after the name
			if (strripos($u_agent, "Version") < strripos($u_agent, $ub)) {
				$version = $matches['version'][0];
			}
			else {
				$version = $matches['version'][1];
			}
		}
		else {
			$version = $matches['version'][0];
		}

		// check if we have a number
		if ($version == null || $version == "") {
			$version = '?';
		}

		return array(
			'userAgent' => $u_agent,
			'name'      => $bname,
			'version'   => (int)$version,
			'platform'  => $platform,
			'pattern'   => $pattern
		);
	}

	/**
	 * Check if the browser is compatible with eMundus
	 * @return bool
	 */
	public static function isCompatible(): bool
	{
		$is_compatible = true;

		$browser = self::getBrowser();
		$min_versions = [
			'Chrome' => 88,
			'Firefox' => 85,
			'Edge' => 88,
			'Opera' => 74,
			'Safari' => 15
		];

		if((!empty($min_versions[$browser['name']]) && $browser['version'] < $min_versions[$browser['name']]) || $browser['name'] == 'Internet Explorer') {
			$is_compatible = false;
		}

		return $is_compatible;
	}
}