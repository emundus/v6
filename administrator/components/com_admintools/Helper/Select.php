<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\AdminTools\Admin\Helper;

defined('_JEXEC') || die;

use Joomla\CMS\Access\Access;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Text;
use Joomla\Utilities\ArrayHelper;

class Select
{
	public static function valuelist($options, $name, $attribs = null, $selected = null, $ignoreKey = false)
	{
		$list = [];
		foreach ($options as $k => $v)
		{
			if ($ignoreKey)
			{
				$k = $v;
			}
			$list[] = HTMLHelper::_('FEFHelp.select.option', $k, $v);
		}

		return self::genericlist($list, $name, $attribs, $selected, $name);
	}

	public static function booleanlist($name, $attribs = null, $selected = null, $showEmpty = true)
	{
		$options = [];

		if ($showEmpty)
		{
			$options[] = HTMLHelper::_('FEFHelp.select.option', '-1', '---');
		}

		$options[] = HTMLHelper::_('FEFHelp.select.option', '0', Text::_('JNO'));
		$options[] = HTMLHelper::_('FEFHelp.select.option', '1', Text::_('JYES'));

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	public static function autoroots($name, $attribs = null, $selected = null)
	{
		$options = [
			HTMLHelper::_('FEFHelp.select.option', '-1', '---'),
			HTMLHelper::_('FEFHelp.select.option', '0', Text::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_AUTOROOT_OFF')),
			HTMLHelper::_('FEFHelp.select.option', '1', Text::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_AUTOROOT_STD')),
			HTMLHelper::_('FEFHelp.select.option', '2', Text::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_AUTOROOT_ALT')),
		];

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	public static function published($selected = null, $id = 'enabled', $attribs = [])
	{
		$options   = [];
		$options[] = HTMLHelper::_('FEFHelp.select.option', '', '- ' . Text::_('COM_ADMINTOOLS_LBL_COMMON_SELECTPUBLISHSTATE') . ' -');
		$options[] = HTMLHelper::_('FEFHelp.select.option', 0, Text::_('JUNPUBLISHED'));
		$options[] = HTMLHelper::_('FEFHelp.select.option', 1, Text::_('JPUBLISHED'));

		return self::genericlist($options, $id, $attribs, $selected, $id);
	}

	public static function reasons($id = 'reason', $selected = null, $attribs = [])
	{
		$options = [];

		$reasons = [
			'other', 'adminpw', 'ipwl', 'ipbl', 'sqlishield', 'antispam', 'superuserslist',
			'tmpl', 'template', 'muashield', 'sessionshield', 'csrfshield', 'rfishield', 'dfishield', 'uploadshield',
			'httpbl', 'loginfailure', 'external', 'awayschedule', 'admindir',
			'nonewadmins', 'nonewfrontendadmins', 'phpshield', '404shield', 'wafblacklist',
		];

		foreach ($reasons as $reason)
		{
			$options[] = HTMLHelper::_('FEFHelp.select.option', $reason, Text::_('COM_ADMINTOOLS_LBL_SECURITYEXCEPTION_REASON_' . strtoupper($reason)));
		}

		// Enable miscellaneous reasons, for use in email templates
		if (isset($attribs['misc']))
		{
			$options[] = HTMLHelper::_('FEFHelp.select.option', 'user-reactivate', Text::_('COM_ADMINTOOLS_WAFEMAILTEMPLATE_REASON_USERREACTIVATE'));
			$options[] = HTMLHelper::_('FEFHelp.select.option', 'adminloginfail', Text::_('COM_ADMINTOOLS_WAFEMAILTEMPLATE_REASON_ADMINLOGINFAIL'));
			$options[] = HTMLHelper::_('FEFHelp.select.option', 'adminloginsuccess', Text::_('COM_ADMINTOOLS_WAFEMAILTEMPLATE_REASON_ADMINLOGINSUCCESS'));
			$options[] = HTMLHelper::_('FEFHelp.select.option', 'ipautoban', Text::_('COM_ADMINTOOLS_WAFEMAILTEMPLATE_REASON_IPAUTOBAN'));
			$options[] = HTMLHelper::_('FEFHelp.select.option', 'configmonitor', Text::_('COM_ADMINTOOLS_WAFEMAILTEMPLATE_REASON_CONFIGMONITOR'));
			$options[] = HTMLHelper::_('FEFHelp.select.option', 'rescueurl', Text::_('COM_ADMINTOOLS_WAFEMAILTEMPLATE_REASON_RESCUEURL'));
			$options[] = HTMLHelper::_('FEFHelp.select.option', 'criticalfiles', Text::_('COM_ADMINTOOLS_WAFEMAILTEMPLATE_REASON_CRITICALFILES'));
			$options[] = HTMLHelper::_('FEFHelp.select.option', 'criticalfiles_global', Text::_('COM_ADMINTOOLS_WAFEMAILTEMPLATE_REASON_CRITICALFILES_GLOBAL'));

			unset($attribs['misc']);
		}

		// Let's sort the list alphabetically
		ArrayHelper::sortObjects($options, 'text');

		if (isset($attribs['all']))
		{
			array_unshift($options, HTMLHelper::_('FEFHelp.select.option', 'all', Text::_('COM_ADMINTOOLS_LBL_SECURITYEXCEPTION_REASON_ALL')));
			unset($attribs['all']);
		}

		if (!isset($attribs['hideEmpty']))
		{
			array_unshift($options, HTMLHelper::_('FEFHelp.select.option', '', '- ' . Text::_('COM_ADMINTOOLS_LBL_SECURITYEXCEPTION_REASON_SELECT') . ' -'));
		}
		else
		{
			unset($attribs['hideEmpty']);
		}

		return self::genericlist($options, $id, $attribs, $selected, $id);
	}

	public static function wwwredirs($name, $attribs = null, $selected = null)
	{
		$options = [
			HTMLHelper::_('FEFHelp.select.option', '-1', '---'),
			HTMLHelper::_('FEFHelp.select.option', '0', Text::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_WWWREDIR_NO')),
			HTMLHelper::_('FEFHelp.select.option', '1', Text::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_WWWREDIR_WWW')),
			HTMLHelper::_('FEFHelp.select.option', '2', Text::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_WWWREDIR_NONWWW')),
		];

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	public static function exptime($name, $attribs = null, $selected = null)
	{
		$options = [
			HTMLHelper::_('FEFHelp.select.option', '0', Text::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_EXPTIME_NO')),
			HTMLHelper::_('FEFHelp.select.option', '1', Text::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_EXPTIME_VARIES')),
			HTMLHelper::_('FEFHelp.select.option', '2', Text::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_EXPTIME_YEAR')),
		];

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	public static function perms($name, $attribs = null, $selected = null)
	{
		$rawperms = [
			0400, 0440, 0444, 0600, 0640, 0644, 0660, 0664, 0700, 0740, 0744, 0750, 0754, 0755, 0757, 0770, 0775, 0777,
		];

		$options   = [];
		$options[] = HTMLHelper::_('FEFHelp.select.option', '', '---');

		foreach ($rawperms as $perm)
		{
			$text      = decoct($perm);
			$options[] = HTMLHelper::_('FEFHelp.select.option', '0' . $text, $text);
		}

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	public static function trsfreqlist($name, $attribs = null, $selected = null)
	{
		$freqs = ['second', 'minute', 'hour', 'day'];

		$options   = [];
		$options[] = HTMLHelper::_('FEFHelp.select.option', '', '---');
		foreach ($freqs as $freq)
		{
			$text      = Text::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_FREQ' . strtoupper($freq));
			$options[] = HTMLHelper::_('FEFHelp.select.option', $freq, $text);
		}

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	public static function deliverymethod($name, $attribs = null, $selected = null)
	{
		$options = [
			HTMLHelper::_('FEFHelp.select.option', '-1', '---'),
			HTMLHelper::_('FEFHelp.select.option', 'plugin', Text::_('COM_ADMINTOOLS_LBL_SEOANDLINKTOOLS_OPT_JSDELIVERY_PLUGIN')),
			HTMLHelper::_('FEFHelp.select.option', 'direct', Text::_('COM_ADMINTOOLS_LBL_SEOANDLINKTOOLS_OPT_JSDELIVERY_DIRECT')),
		];

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	public static function httpschemes($name, $attribs = null, $selected = null)
	{
		$options = [
			HTMLHelper::_('FEFHelp.select.option', 'http', Text::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_IPLOOKUPSCHEME_HTTP')),
			HTMLHelper::_('FEFHelp.select.option', 'https', Text::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_IPLOOKUPSCHEME_HTTPS')),
		];

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	public static function scanresultstatus($name, $selected = null, $attribs = null)
	{
		$options = [
			HTMLHelper::_('FEFHelp.select.option', '', '- ' . Text::_('COM_ADMINTOOLS_LBL_COMMON_SELECTPUBLISHSTATE') . ' -'),
			HTMLHelper::_('FEFHelp.select.option', 'new', Text::_('COM_ADMINTOOLS_LBL_SCANALERTS_STATUS_NEW')),
			HTMLHelper::_('FEFHelp.select.option', 'suspicious', Text::_('COM_ADMINTOOLS_LBL_SCANALERTS_STATUS_SUSPICIOUS')),
			HTMLHelper::_('FEFHelp.select.option', 'modified', Text::_('COM_ADMINTOOLS_LBL_SCANALERTS_STATUS_MODIFIED')),
		];

		return self::genericlist($options, $name, $attribs, $selected, $name . '_id');
	}

	public static function markedsafe($name, $selected = null, $attribs = null)
	{
		$options = [];

		$options[] = HTMLHelper::_('FEFHelp.select.option', '', '- ' . Text::_('COM_ADMINTOOLS_LBL_SCANALERTS_ACKNOWLEDGED') . ' -');
		$options[] = HTMLHelper::_('FEFHelp.select.option', '0', Text::_('JNO'));
		$options[] = HTMLHelper::_('FEFHelp.select.option', '1', Text::_('JYES'));

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	public static function symlinks($name, $attribs = null, $selected = null)
	{
		$options = [
			HTMLHelper::_('FEFHelp.select.option', '0', Text::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_SYMLINKS_OFF')),
			HTMLHelper::_('FEFHelp.select.option', '1', Text::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_SYMLINKS_FOLLOW')),
			HTMLHelper::_('FEFHelp.select.option', '2', Text::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_SYMLINKS_IFOWNERMATCH')),
		];

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	public static function languages($id = 'language', $selected = null, $attribs = [])
	{
		$languages = LanguageHelper::getLanguages('lang_code');
		$options   = [];

		if (isset($attribs['allow_empty']))
		{
			if ($attribs['allow_empty'])
			{
				$options[] = HTMLHelper::_('FEFHelp.select.option', '', '- ' . Text::_('JALL_LANGUAGE') . ' -');
			}
		}

		$options[] = HTMLHelper::_('FEFHelp.select.option', '*', Text::_('JALL_LANGUAGE'));
		if (!empty($languages))
		{
			foreach ($languages as $key => $lang)
			{
				$options[] = HTMLHelper::_('FEFHelp.select.option', $key, $lang->title);
			}
		}

		return self::genericlist($options, $id, $attribs, $selected, $id);
	}

	public static function keepUrlParamsList($name, $attribs = null, $selected = null)
	{
		$options = [
			HTMLHelper::_('FEFHelp.select.option', '', '- - -'),
			HTMLHelper::_('FEFHelp.select.option', '0', Text::_('COM_ADMINTOOLS_REDIRECTION_KEEPURLPARAMS_LBL_OFF')),
			HTMLHelper::_('FEFHelp.select.option', '1', Text::_('COM_ADMINTOOLS_REDIRECTION_KEEPURLPARAMS_LBL_ALL')),
			HTMLHelper::_('FEFHelp.select.option', '2', Text::_('COM_ADMINTOOLS_REDIRECTION_KEEPURLPARAMS_LBL_ADD')),
		];

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	public static function httpVerbs($name = '', $attribs = null, $selected = null)
	{
		$options = [
			HTMLHelper::_('FEFHelp.select.option', '', '- - -'),
			HTMLHelper::_('FEFHelp.select.option', 'GET', 'GET'),
			HTMLHelper::_('FEFHelp.select.option', 'POST', 'POST'),
			HTMLHelper::_('FEFHelp.select.option', 'PUT', 'PUT'),
			HTMLHelper::_('FEFHelp.select.option', 'DELETE', 'DELETE'),
			HTMLHelper::_('FEFHelp.select.option', 'HEAD', 'HEAD'),
			HTMLHelper::_('FEFHelp.select.option', 'TRACE', 'TRACE'),
		];

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	public static function wafApplication($name = '', $attribs = null, $selected = null)
	{
		$options = [
			HTMLHelper::_('FEFHelp.select.option', 'site', Text::_('COM_ADMINTOOLS_LBL_WAFBLACKLISTEDREQUEST_APPLICATION_SITE')),
			HTMLHelper::_('FEFHelp.select.option', 'admin', Text::_('COM_ADMINTOOLS_LBL_WAFBLACKLISTEDREQUEST_APPLICATION_ADMIN')),
			HTMLHelper::_('FEFHelp.select.option', 'both', Text::_('COM_ADMINTOOLS_LBL_WAFBLACKLISTEDREQUEST_APPLICATION_BOTH')),
		];

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	public static function queryParamType($name = '', $attribs = null, $selected = null)
	{
		$options = [
			HTMLHelper::_('FEFHelp.select.option', '', '- - -'),
			HTMLHelper::_('FEFHelp.select.option', 'E', Text::_('COM_ADMINTOOLS_LBL_WAFBLACKLISTEDREQUEST_QUERY_CONTENT_EXACT')),
			HTMLHelper::_('FEFHelp.select.option', 'P', Text::_('COM_ADMINTOOLS_LBL_WAFBLACKLISTEDREQUEST_QUERY_CONTENT_PARTIAL')),
			HTMLHelper::_('FEFHelp.select.option', 'R', Text::_('COM_ADMINTOOLS_LBL_WAFBLACKLISTEDREQUEST_QUERY_CONTENT_REGEX')),
		];

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	public static function referrerpolicy($name, $attribs = null, $selected = null)
	{
		$options = [
			HTMLHelper::_('FEFHelp.select.option', '-1', Text::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_REFERERPOLICY_DISABLED')),
			HTMLHelper::_('FEFHelp.select.option', '', Text::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_REFERERPOLICY_EMPTY')),
			HTMLHelper::_('FEFHelp.select.option', 'no-referrer', Text::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_REFERERPOLICY_NOREF')),
			HTMLHelper::_('FEFHelp.select.option', 'no-referrer-when-downgrade', Text::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_REFERERPOLICY_NOREF_DOWNGRADE')),
			HTMLHelper::_('FEFHelp.select.option', 'same-origin', Text::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_REFERERPOLICY_SAMEORIGIN')),
			HTMLHelper::_('FEFHelp.select.option', 'origin', Text::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_REFERERPOLICY_ORIGIN')),
			HTMLHelper::_('FEFHelp.select.option', 'strict-origin', Text::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_REFERERPOLICY_STRICTORIGIN')),
			HTMLHelper::_('FEFHelp.select.option', 'origin-when-cross-origin', Text::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_REFERERPOLICY_ORIGINCROSS')),
			HTMLHelper::_('FEFHelp.select.option', 'strict-origin-when-cross-origin', Text::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_REFERERPOLICY_STRICTORIGINGCROSS')),
			HTMLHelper::_('FEFHelp.select.option', 'unsafe-url', Text::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_REFERERPOLICY_UNSAFE')),
		];

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	public static function etagtype($name, $attribs = null, $selected = null)
	{
		$options = [
			HTMLHelper::_('FEFHelp.select.option', 'default', Text::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_ETAGTYPE_DEFAULT')),
			HTMLHelper::_('FEFHelp.select.option', 'full', Text::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_ETAGTYPE_FULL')),
			HTMLHelper::_('FEFHelp.select.option', 'sizetime', Text::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_ETAGTYPE_SIZETIME')),
			HTMLHelper::_('FEFHelp.select.option', 'size', Text::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_ETAGTYPE_SIZE')),
			HTMLHelper::_('FEFHelp.select.option', 'none', Text::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_ETAGTYPE_NONE')),
		];

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	public static function etagtypeIIS($name, $attribs = null, $selected = null)
	{
		$options = [
			HTMLHelper::_('FEFHelp.select.option', 'default', Text::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_ETAGTYPE_DEFAULT')),
			HTMLHelper::_('FEFHelp.select.option', 'none', Text::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_ETAGTYPE_NONE')),
		];

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	public static function etagtypeNginX($name, $attribs = null, $selected = null)
	{
		$options = [
			HTMLHelper::_('FEFHelp.select.option', '-1', Text::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_ETAGTYPE_DEFAULT')),
			HTMLHelper::_('FEFHelp.select.option', '1', Text::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_ETAGTYPE_FULL')),
			HTMLHelper::_('FEFHelp.select.option', '0', Text::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_ETAGTYPE_NONE')),
		];

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	/**
	 * Drop down list of CSV delimiter preference
	 *
	 * @param   string  $name      The field's name
	 * @param   int     $selected  Pre-selected value
	 * @param   array   $attribs   Field attributes
	 *
	 * @return  string  The HTML of the drop-down
	 */
	public static function csvdelimiters($name = 'csvdelimiters', $selected = 1, $attribs = [])
	{
		$options   = [];
		$options[] = HTMLHelper::_('FEFHelp.select.option', '1', 'abc, def');
		$options[] = HTMLHelper::_('FEFHelp.select.option', '2', 'abc; def');
		$options[] = HTMLHelper::_('FEFHelp.select.option', '3', '"abc"; "def"');
		$options[] = HTMLHelper::_('FEFHelp.select.option', '-99', Text::_('COM_ADMINTOOLS_IMPORTANDEXPORT_DELIMITERS_CUSTOM'));

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	/**
	 * Creates a drop-down list with the possible configuration monitor actions
	 *
	 * @param   string  $name      Field name
	 * @param   array   $attribs   Field attributes
	 * @param   string  $selected  Selected value
	 *
	 * @return  string  The HTML of the field
	 *
	 * @since   4.1.0
	 */
	public static function configMonitorAction($name, $attribs = null, $selected = null)
	{
		$options = [
			HTMLHelper::_('FEFHelp.select.option', 'email', Text::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_CONFIGMONITORACTION_EMAIL')),
			HTMLHelper::_('FEFHelp.select.option', 'block', Text::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_CONFIGMONITORACTION_BLOCK')),
		];

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	/**
	 * Creates a drop-down list with the possible forgotten users actions
	 *
	 * @param   string  $name      Field name
	 * @param   array   $attribs   Field attributes
	 * @param   string  $selected  Selected value
	 *
	 * @return  string  The HTML of the field
	 *
	 * @since   5.3.0
	 */
	public static function disableObsoleteAdminsAction($name, $attribs = null, $selected = null)
	{
		$options = [
			HTMLHelper::_('FEFHelp.select.option', 'reset', Text::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_DISABLEOBSOLETEADMINS_ACTION_RESET')),
			HTMLHelper::_('FEFHelp.select.option', 'block', Text::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_DISABLEOBSOLETEADMINS_ACTION_BLOCK')),
		];

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	/**
	 * Creates a drop-down list with backend users. To make sure we won't get a memory error up to 100 users are shown.
	 *
	 * @param   string  $name      Field name
	 * @param   array   $attribs   Field attributes
	 * @param   string  $selected  Selected value
	 *
	 * @return  string  The HTML of the field
	 *
	 * @since   5.3.0
	 */
	public static function backendUsers($name, $attribs = null, $selected = null)
	{
		// Get all groups
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select([$db->qn('id')])
			->from($db->qn('#__usergroups'));

		$groups = array_filter($db->setQuery($query)->loadColumn(0), function ($group) {
			// First try to see if the group has explicit backend login privileges
			if (Access::checkGroup($group, 'core.login.admin', 1))
			{
				return true;
			}

			// If not, is it a Super Admin (ergo inherited privileges)?
			return (bool) Access::checkGroup($group, 'core.admin', 1);
		});

		// Get all applicable user IDs
		$users = [];

		foreach ($groups as $group)
		{
			$users = array_unique(array_merge($users, Access::getUsersByGroup($group)));
		}

		// Get all applicable admin users
		$query = $db->getQuery(true)
			->select([
				$db->qn('id', 'value'),
				$db->qn('username', 'text'),
			])
			->from($db->qn('#__users'))
			->where($db->qn('id') . ' IN(' . implode(',', array_map([$db, 'q'], $users)) . ')')
			->order($db->qn('username') . ' ASC');

		return self::genericlist(array_map(function ($data) {
			return HTMLHelper::_('FEFHelp.select.option', $data['value'], $data['text']);
		}, $db->setQuery($query, 0, 100)->loadAssocList()), $name, $attribs, $selected, $name);
	}

	/**
	 * Creates a three state list to set the IP Workarounds value
	 *
	 * @param   string  $name      Field name
	 * @param   array   $attribs   Field attributes
	 * @param   string  $selected  Selected value
	 *
	 * @return  string  The HTML of the field
	 *
	 * @since   5.5.0
	 */
	public static function ipworkarounds($name, $attribs = null, $selected = null)
	{
		if (empty($attribs))
		{
			$attribs = ['class' => 'akeeba-toggle'];
		}
		else
		{
			if (isset($attribs['class']))
			{
				$attribs['class'] .= ' akeeba-toggle';
			}
			else
			{
				$attribs['class'] = 'akeeba-toggle';
			}
		}

		$temp = '';

		foreach ($attribs as $key => $value)
		{
			$temp .= $key . ' = "' . $value . '"';
		}

		$attribs = $temp;

		$checked_1 = $selected == '0' ? 'checked ' : '';
		$checked_2 = $selected == '1' ? 'checked ' : '';
		$checked_3 = $selected == '2' ? 'checked ' : '';

		$html = '<div ' . $attribs . '>';
		$html .= '<input type="radio" class="" name="' . $name . '" ' . $checked_2 . 'id="' . $name . '-2" value="1">';
		$html .= '<label for="' . $name . '-2" class="green">' . Text::_('JYES') . '</label>';
		$html .= '<input type="radio" class="" name="' . $name . '" ' . $checked_1 . 'id="' . $name . '-1" value="0">';
		$html .= '<label for="' . $name . '-1" class="red">' . Text::_('JNO') . '</label>';
		$html .= '<input type="radio" class="" name="' . $name . '" ' . $checked_3 . 'id="' . $name . '-3" value="2">';
		$html .= '<label for="' . $name . '-3" class="primary">' . Text::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_IPWORKAROUNDS_AUTO') . '</label>';
		$html .= '</div>';

		return $html;
	}

	public static function getCorsOptions()
	{
		return [
			HTMLHelper::_('FEFHelp.select.option', '-1', Text::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_CORS_OPT_SAMEORIGIN')),
			HTMLHelper::_('FEFHelp.select.option', '0', Text::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_CORS_OPT_UNSET')),
			HTMLHelper::_('FEFHelp.select.option', '1', Text::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_CORS_OPT_ENABLE')),
		];
	}

	protected static function genericlist($list, $name, $attribs, $selected, $idTag)
	{
		$chosen = strpos($attribs['class'] ?? '', 'advancedSelect') !== false;

		if ($chosen) {
			HTMLHelper::_('formbehavior.chosen');
		}

		if (empty($attribs))
		{
			$attribs = [];
		}

		return HTMLHelper::_('FEFHelp.select.genericlist', $list, $name, $attribs, 'value', 'text', $selected, $idTag);
	}

}
