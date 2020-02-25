<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\AdminTools\Admin\Helper;

defined('_JEXEC') or die;

use JAccess;
use JHtml;
use Joomla\Utilities\ArrayHelper;
use JText;

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
			$list[] = JHtml::_('FEFHelper.select.option', $k, $v);
		}

		return self::genericlist($list, $name, $attribs, $selected, $name);
	}

	public static function booleanlist($name, $attribs = null, $selected = null, $showEmpty = true)
	{
		$options = [];

		if ($showEmpty)
		{
			$options[] = JHtml::_('FEFHelper.select.option', '-1', '---');
		}

		$options[] = JHtml::_('FEFHelper.select.option', '0', JText::_('JNO'));
		$options[] = JHtml::_('FEFHelper.select.option', '1', JText::_('JYES'));

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	public static function csrflist($name, $attribs = null, $selected = null)
	{
		$options = [
			JHtml::_('FEFHelper.select.option', '-1', '---'),
			JHtml::_('FEFHelper.select.option', '0', JText::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_CSRFSHIELD_NO')),
			JHtml::_('FEFHelper.select.option', '1', JText::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_CSRFSHIELD_BASIC')),
			JHtml::_('FEFHelper.select.option', '2', JText::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_CSRFSHIELD_ADVANCED')),
		];

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	public static function autoroots($name, $attribs = null, $selected = null)
	{
		$options = [
			JHtml::_('FEFHelper.select.option', '-1', '---'),
			JHtml::_('FEFHelper.select.option', '0', JText::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_AUTOROOT_OFF')),
			JHtml::_('FEFHelper.select.option', '1', JText::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_AUTOROOT_STD')),
			JHtml::_('FEFHelper.select.option', '2', JText::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_AUTOROOT_ALT')),
		];

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	public static function published($selected = null, $id = 'enabled', $attribs = [])
	{
		$options   = [];
		$options[] = JHtml::_('FEFHelper.select.option', '', '- ' . JText::_('COM_ADMINTOOLS_LBL_COMMON_SELECTPUBLISHSTATE') . ' -');
		$options[] = JHtml::_('FEFHelper.select.option', 0, JText::_('JUNPUBLISHED'));
		$options[] = JHtml::_('FEFHelper.select.option', 1, JText::_('JPUBLISHED'));

		return self::genericlist($options, $id, $attribs, $selected, $id);
	}

	public static function reasons($id = 'reason', $selected = null, $attribs = [])
	{
		$options = [];

		$reasons = [
			'other', 'adminpw', 'ipwl', 'ipbl', 'sqlishield', 'antispam',
			'tmpl', 'template', 'muashield', 'sessionshield', 'csrfshield', 'rfishield', 'dfishield', 'uploadshield',
			'httpbl', 'loginfailure', 'external', 'awayschedule', 'admindir',
			'nonewadmins', 'nonewfrontendadmins', 'phpshield', '404shield', 'wafblacklist',
		];

		foreach ($reasons as $reason)
		{
			$options[] = JHtml::_('FEFHelper.select.option', $reason, JText::_('COM_ADMINTOOLS_LBL_SECURITYEXCEPTION_REASON_' . strtoupper($reason)));
		}

		// Enable miscellaneous reasons, for use in email templates
		if (isset($attribs['misc']))
		{
			$options[] = JHtml::_('FEFHelper.select.option', 'user-reactivate', JText::_('COM_ADMINTOOLS_WAFEMAILTEMPLATE_REASON_USERREACTIVATE'));
			$options[] = JHtml::_('FEFHelper.select.option', 'adminloginfail', JText::_('COM_ADMINTOOLS_WAFEMAILTEMPLATE_REASON_ADMINLOGINFAIL'));
			$options[] = JHtml::_('FEFHelper.select.option', 'adminloginsuccess', JText::_('COM_ADMINTOOLS_WAFEMAILTEMPLATE_REASON_ADMINLOGINSUCCESS'));
			$options[] = JHtml::_('FEFHelper.select.option', 'ipautoban', JText::_('COM_ADMINTOOLS_WAFEMAILTEMPLATE_REASON_IPAUTOBAN'));
			$options[] = JHtml::_('FEFHelper.select.option', 'configmonitor', JText::_('COM_ADMINTOOLS_WAFEMAILTEMPLATE_REASON_CONFIGMONITOR'));
			$options[] = JHtml::_('FEFHelper.select.option', 'rescueurl', JText::_('COM_ADMINTOOLS_WAFEMAILTEMPLATE_REASON_RESCUEURL'));
			$options[] = JHtml::_('FEFHelper.select.option', 'criticalfiles', JText::_('COM_ADMINTOOLS_WAFEMAILTEMPLATE_REASON_CRITICALFILES'));
			$options[] = JHtml::_('FEFHelper.select.option', 'criticalfiles_global', JText::_('COM_ADMINTOOLS_WAFEMAILTEMPLATE_REASON_CRITICALFILES_GLOBAL'));

			unset($attribs['misc']);
		}

		// Let's sort the list alphabetically
		if (class_exists('JArrayHelper'))
		{
			\JArrayHelper::sortObjects($options, 'text');
		}
		else
		{
			ArrayHelper::sortObjects($options, 'text');
		}

		if (isset($attribs['all']))
		{
			array_unshift($options, JHtml::_('FEFHelper.select.option', 'all', JText::_('COM_ADMINTOOLS_LBL_SECURITYEXCEPTION_REASON_ALL')));
			unset($attribs['all']);
		}

		if (!isset($attribs['hideEmpty']))
		{
			array_unshift($options, JHtml::_('FEFHelper.select.option', '', '- ' . JText::_('COM_ADMINTOOLS_LBL_SECURITYEXCEPTION_REASON_SELECT') . ' -'));
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
			JHtml::_('FEFHelper.select.option', '-1', '---'),
			JHtml::_('FEFHelper.select.option', '0', JText::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_WWWREDIR_NO')),
			JHtml::_('FEFHelper.select.option', '1', JText::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_WWWREDIR_WWW')),
			JHtml::_('FEFHelper.select.option', '2', JText::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_WWWREDIR_NONWWW')),
		];

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	public static function perms($name, $attribs = null, $selected = null)
	{
		$rawperms = [
			0400, 0440, 0444, 0600, 0640, 0644, 0660, 0664, 0700, 0740, 0744, 0750, 0754, 0755, 0757, 0770, 0775, 0777,
		];

		$options   = [];
		$options[] = JHtml::_('FEFHelper.select.option', '', '---');

		foreach ($rawperms as $perm)
		{
			$text      = decoct($perm);
			$options[] = JHtml::_('FEFHelper.select.option', '0' . $text, $text);
		}

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	public static function trsfreqlist($name, $attribs = null, $selected = null)
	{
		$freqs = ['second', 'minute', 'hour', 'day'];

		$options   = [];
		$options[] = JHtml::_('FEFHelper.select.option', '', '---');
		foreach ($freqs as $freq)
		{
			$text      = JText::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_FREQ' . strtoupper($freq));
			$options[] = JHtml::_('FEFHelper.select.option', $freq, $text);
		}

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	public static function deliverymethod($name, $attribs = null, $selected = null)
	{
		$options = [
			JHtml::_('FEFHelper.select.option', '-1', '---'),
			JHtml::_('FEFHelper.select.option', 'plugin', JText::_('COM_ADMINTOOLS_LBL_SEOANDLINKTOOLS_OPT_JSDELIVERY_PLUGIN')),
			JHtml::_('FEFHelper.select.option', 'direct', JText::_('COM_ADMINTOOLS_LBL_SEOANDLINKTOOLS_OPT_JSDELIVERY_DIRECT')),
		];

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	public static function httpschemes($name, $attribs = null, $selected = null)
	{
		$options = [
			JHtml::_('FEFHelper.select.option', 'http', JText::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_IPLOOKUPSCHEME_HTTP')),
			JHtml::_('FEFHelper.select.option', 'https', JText::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_IPLOOKUPSCHEME_HTTPS')),
		];

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	public static function scanresultstatus($name, $selected = null, $attribs = null)
	{
		$options = [
			JHtml::_('FEFHelper.select.option', '', '- ' . JText::_('COM_ADMINTOOLS_LBL_COMMON_SELECTPUBLISHSTATE') . ' -'),
			JHtml::_('FEFHelper.select.option', 'new', JText::_('COM_ADMINTOOLS_LBL_SCANALERTS_STATUS_NEW')),
			JHtml::_('FEFHelper.select.option', 'suspicious', JText::_('COM_ADMINTOOLS_LBL_SCANALERTS_STATUS_SUSPICIOUS')),
			JHtml::_('FEFHelper.select.option', 'modified', JText::_('COM_ADMINTOOLS_LBL_SCANALERTS_STATUS_MODIFIED')),
		];

		return self::genericlist($options, $name, $attribs, $selected, $name . '_id');
	}

	public static function markedsafe($name, $selected = null, $attribs = null)
	{
		$options = [];

		$options[] = JHtml::_('FEFHelper.select.option', '', '- ' . JText::_('COM_ADMINTOOLS_LBL_SCANALERTS_ACKNOWLEDGED') . ' -');
		$options[] = JHtml::_('FEFHelper.select.option', '0', JText::_('JNO'));
		$options[] = JHtml::_('FEFHelper.select.option', '1', JText::_('JYES'));

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	public static function symlinks($name, $attribs = null, $selected = null)
	{
		$options = [
			JHtml::_('FEFHelper.select.option', '0', JText::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_SYMLINKS_OFF')),
			JHtml::_('FEFHelper.select.option', '1', JText::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_SYMLINKS_FOLLOW')),
			JHtml::_('FEFHelper.select.option', '2', JText::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_SYMLINKS_IFOWNERMATCH')),
		];

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	public static function languages($id = 'language', $selected = null, $attribs = [])
	{
		\JLoader::import('joomla.language.helper');
		$languages = \JLanguageHelper::getLanguages('lang_code');
		$options   = [];

		if (isset($attribs['allow_empty']))
		{
			if ($attribs['allow_empty'])
			{
				$options[] = JHtml::_('FEFHelper.select.option', '', '- ' . JText::_('JALL_LANGUAGE') . ' -');
			}
		}

		$options[] = JHtml::_('FEFHelper.select.option', '*', JText::_('JALL_LANGUAGE'));
		if (!empty($languages))
		{
			foreach ($languages as $key => $lang)
			{
				$options[] = JHtml::_('FEFHelper.select.option', $key, $lang->title);
			}
		}

		return self::genericlist($options, $id, $attribs, $selected, $id);
	}

	public static function keepUrlParamsList($name, $attribs = null, $selected = null)
	{
		$options = [
			JHtml::_('FEFHelper.select.option', '', '- - -'),
			JHtml::_('FEFHelper.select.option', '0', JText::_('COM_ADMINTOOLS_REDIRECTION_KEEPURLPARAMS_LBL_OFF')),
			JHtml::_('FEFHelper.select.option', '1', JText::_('COM_ADMINTOOLS_REDIRECTION_KEEPURLPARAMS_LBL_ALL')),
			JHtml::_('FEFHelper.select.option', '2', JText::_('COM_ADMINTOOLS_REDIRECTION_KEEPURLPARAMS_LBL_ADD')),
		];

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	public static function httpVerbs($name = '', $attribs = null, $selected = null)
	{
		$options = [
			JHtml::_('FEFHelper.select.option', '', '- - -'),
			JHtml::_('FEFHelper.select.option', 'GET', 'GET'),
			JHtml::_('FEFHelper.select.option', 'POST', 'POST'),
			JHtml::_('FEFHelper.select.option', 'PUT', 'PUT'),
			JHtml::_('FEFHelper.select.option', 'DELETE', 'DELETE'),
			JHtml::_('FEFHelper.select.option', 'HEAD', 'HEAD'),
			JHtml::_('FEFHelper.select.option', 'TRACE', 'TRACE'),
		];

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	public static function wafApplication($name = '', $attribs = null, $selected = null)
	{
		$options = [
			JHtml::_('FEFHelper.select.option', 'site', JText::_('COM_ADMINTOOLS_LBL_WAFBLACKLISTEDREQUEST_APPLICATION_SITE')),
			JHtml::_('FEFHelper.select.option', 'admin', JText::_('COM_ADMINTOOLS_LBL_WAFBLACKLISTEDREQUEST_APPLICATION_ADMIN')),
			JHtml::_('FEFHelper.select.option', 'both', JText::_('COM_ADMINTOOLS_LBL_WAFBLACKLISTEDREQUEST_APPLICATION_BOTH')),
		];

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	public static function queryParamType($name = '', $attribs = null, $selected = null)
	{
		$options = [
			JHtml::_('FEFHelper.select.option', '', '- - -'),
			JHtml::_('FEFHelper.select.option', 'E', JText::_('COM_ADMINTOOLS_LBL_WAFBLACKLISTEDREQUEST_QUERY_CONTENT_EXACT')),
			JHtml::_('FEFHelper.select.option', 'P', JText::_('COM_ADMINTOOLS_LBL_WAFBLACKLISTEDREQUEST_QUERY_CONTENT_PARTIAL')),
			JHtml::_('FEFHelper.select.option', 'R', JText::_('COM_ADMINTOOLS_LBL_WAFBLACKLISTEDREQUEST_QUERY_CONTENT_REGEX')),
		];

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	public static function referrerpolicy($name, $attribs = null, $selected = null)
	{
		$options = [
			JHtml::_('FEFHelper.select.option', '-1', JText::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_REFERERPOLICY_DISABLED')),
			JHtml::_('FEFHelper.select.option', '', JText::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_REFERERPOLICY_EMPTY')),
			JHtml::_('FEFHelper.select.option', 'no-referrer', JText::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_REFERERPOLICY_NOREF')),
			JHtml::_('FEFHelper.select.option', 'no-referrer-when-downgrade', JText::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_REFERERPOLICY_NOREF_DOWNGRADE')),
			JHtml::_('FEFHelper.select.option', 'same-origin', JText::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_REFERERPOLICY_SAMEORIGIN')),
			JHtml::_('FEFHelper.select.option', 'origin', JText::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_REFERERPOLICY_ORIGIN')),
			JHtml::_('FEFHelper.select.option', 'strict-origin', JText::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_REFERERPOLICY_STRICTORIGIN')),
			JHtml::_('FEFHelper.select.option', 'origin-when-cross-origin', JText::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_REFERERPOLICY_ORIGINCROSS')),
			JHtml::_('FEFHelper.select.option', 'strict-origin-when-cross-origin', JText::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_REFERERPOLICY_STRICTORIGINGCROSS')),
			JHtml::_('FEFHelper.select.option', 'unsafe-url', JText::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_REFERERPOLICY_UNSAFE')),
		];

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	public static function etagtype($name, $attribs = null, $selected = null)
	{
		$options = [
			JHtml::_('FEFHelper.select.option', 'default', JText::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_ETAGTYPE_DEFAULT')),
			JHtml::_('FEFHelper.select.option', 'full', JText::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_ETAGTYPE_FULL')),
			JHtml::_('FEFHelper.select.option', 'sizetime', JText::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_ETAGTYPE_SIZETIME')),
			JHtml::_('FEFHelper.select.option', 'size', JText::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_ETAGTYPE_SIZE')),
			JHtml::_('FEFHelper.select.option', 'none', JText::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_ETAGTYPE_NONE')),
		];

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	public static function etagtypeIIS($name, $attribs = null, $selected = null)
	{
		$options = [
			JHtml::_('FEFHelper.select.option', 'default', JText::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_ETAGTYPE_DEFAULT')),
			JHtml::_('FEFHelper.select.option', 'none', JText::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_ETAGTYPE_NONE')),
		];

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	public static function etagtypeNginX($name, $attribs = null, $selected = null)
	{
		$options = [
			JHtml::_('FEFHelper.select.option', '-1', JText::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_ETAGTYPE_DEFAULT')),
			JHtml::_('FEFHelper.select.option', '1', JText::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_ETAGTYPE_FULL')),
			JHtml::_('FEFHelper.select.option', '0', JText::_('COM_ADMINTOOLS_LBL_HTACCESSMAKER_ETAGTYPE_NONE')),
		];

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	/**
	 * Drop down list of CSV delimiter preference
	 *
	 * @param   string $name     The field's name
	 * @param   int    $selected Pre-selected value
	 * @param   array  $attribs  Field attributes
	 *
	 * @return  string  The HTML of the drop-down
	 */
	public static function csvdelimiters($name = 'csvdelimiters', $selected = 1, $attribs = [])
	{
		$options   = [];
		$options[] = JHtml::_('FEFHelper.select.option', '1', 'abc, def');
		$options[] = JHtml::_('FEFHelper.select.option', '2', 'abc; def');
		$options[] = JHtml::_('FEFHelper.select.option', '3', '"abc"; "def"');
		$options[] = JHtml::_('FEFHelper.select.option', '-99', JText::_('COM_ADMINTOOLS_IMPORTANDEXPORT_DELIMITERS_CUSTOM'));

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	/**
	 * Creates a drop-down list with the possible configuration monitor actions
	 *
	 * @param   string $name     Field name
	 * @param   array  $attribs  Field attributes
	 * @param   string $selected Selected value
	 *
	 * @return  string  The HTML of the field
	 *
	 * @since   4.1.0
	 */
	public static function configMonitorAction($name, $attribs = null, $selected = null)
	{
		$options = [
			JHtml::_('FEFHelper.select.option', 'email', JText::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_CONFIGMONITORACTION_EMAIL')),
			JHtml::_('FEFHelper.select.option', 'block', JText::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_CONFIGMONITORACTION_BLOCK')),
		];

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	/**
	 * Creates a drop-down list with the possible forgotten users actions
	 *
	 * @param   string $name     Field name
	 * @param   array  $attribs  Field attributes
	 * @param   string $selected Selected value
	 *
	 * @return  string  The HTML of the field
	 *
	 * @since   5.3.0
	 */
	public static function disableObsoleteAdminsAction($name, $attribs = null, $selected = null)
	{
		$options = [
			JHtml::_('FEFHelper.select.option', 'reset', JText::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_DISABLEOBSOLETEADMINS_ACTION_RESET')),
			JHtml::_('FEFHelper.select.option', 'block', JText::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_DISABLEOBSOLETEADMINS_ACTION_BLOCK')),
		];

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	/**
	 * Creates a drop-down list with backend users. To make sure we won't get a memory error up to 100 users are shown.
	 *
	 * @param   string $name     Field name
	 * @param   array  $attribs  Field attributes
	 * @param   string $selected Selected value
	 *
	 * @return  string  The HTML of the field
	 *
	 * @since   5.3.0
	 */
	public static function backendUsers($name, $attribs = null, $selected = null)
	{
		// Get all groups
		$db    = \JFactory::getDbo();
		$query = $db->getQuery(true)
			->select([$db->qn('id')])
			->from($db->qn('#__usergroups'));

		$groups = array_filter($db->setQuery($query)->loadColumn(0), function ($group) {
			// First try to see if the group has explicit backend login privileges
			if (JAccess::checkGroup($group, 'core.login.admin', 1))
			{
				return true;
			}

			// If not, is it a Super Admin (ergo inherited privileges)?
			return (bool) JAccess::checkGroup($group, 'core.admin', 1);
		});

		// Get all applicable user IDs
		$users = [];

		foreach ($groups as $group)
		{
			$users = array_unique(array_merge($users, JAccess::getUsersByGroup($group)));
		}

		// Get all applicable admin users
		$query = $db->getQuery(true)
			->select([
				$db->qn('id', 'value'),
				$db->qn('username', 'text')
			])
			->from($db->qn('#__users'))
			->where($db->qn('id') . ' IN(' . implode(',', array_map([$db, 'q'], $users)) . ')')
			->order($db->qn('username') . ' ASC');

		return self::genericlist(array_map(function($data) {
			return JHtml::_('FEFHelper.select.option', $data['value'], $data['text']);
		}, $db->setQuery($query, 0, 100)->loadAssocList()), $name, $attribs, $selected, $name);
	}

	/**
	 * Creates a three state list to set the IP Workarounds value
	 *
	 * @param   string $name     Field name
	 * @param   array  $attribs  Field attributes
	 * @param   string $selected Selected value
	 *
	 * @return  string  The HTML of the field
	 *
	 * @since   5.5.0
	 */
	public static function ipworkarounds($name, $attribs = null, $selected = null)
	{
		if (empty($attribs))
		{
			$attribs = array('class' => 'akeeba-toggle');
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

		$html  = '<div '.$attribs.'>';
		$html .= 	'<input type="radio" class="" name="'.$name.'" '.$checked_2.'id="'.$name .'-2" value="1">';
		$html .=	'<label for="'.$name.'-2" class="primary">'.JText::_('JYES').'</label>';
		$html .=	'<input type="radio" class="" name="'.$name.'" '.$checked_1.'id="'.$name .'-1" value="0">';
		$html .= 	'<label for="'.$name.'-1" class="primary">'.JText::_('JNO').'</label>';
		$html .=	'<input type="radio" class="" name="'.$name.'" '.$checked_3.'id="'.$name .'-3" value="2">';
		$html .= 	'<label for="'.$name.'-3" class="primary">'.JText::_('COM_ADMINTOOLS_LBL_CONFIGUREWAF_OPT_IPWORKAROUNDS_AUTO').'</label>';
		$html .= '</div>';

		return $html;
	}

	protected static function genericlist($list, $name, $attribs, $selected, $idTag)
	{
		if (empty($attribs))
		{
			$attribs = null;
		}
		else
		{
			$temp = '';
			foreach ($attribs as $key => $value)
			{
				$temp .= $key . ' = "' . $value . '"';
			}
			$attribs = $temp;
		}

		return JHtml::_('FEFHelper.select.genericlist', $list, $name, $attribs, 'value', 'text', $selected, $idTag);
	}

}
