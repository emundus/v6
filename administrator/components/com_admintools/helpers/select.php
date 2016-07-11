<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2016 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') or die;

class AdmintoolsHelperSelect
{
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

		return JHTML::_('select.genericlist', $list, $name, $attribs, 'value', 'text', $selected, $idTag);
	}

	public static function valuelist($options, $name, $attribs = null, $selected = null, $ignoreKey = false)
	{
		$list = array();
		foreach ($options as $k => $v)
		{
			if ($ignoreKey)
			{
				$k = $v;
			}
			$list[] = JHTML::_('select.option', $k, $v);
		}

		return self::genericlist($list, $name, $attribs, $selected, $name);
	}

	public static function booleanlist($name, $attribs = null, $selected = null)
	{
		$options = array(
			JHTML::_('select.option', '-1', '---'),
			JHTML::_('select.option', '0', JText::_('JNO')),
			JHTML::_('select.option', '1', JText::_('JYES'))
		);

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	public static function csrflist($name, $attribs = null, $selected = null)
	{
		$options = array(
			JHTML::_('select.option', '-1', '---'),
			JHTML::_('select.option', '0', JText::_('ATOOLS_LBL_WAF_OPT_CSRFSHIELD_NO')),
			JHTML::_('select.option', '1', JText::_('ATOOLS_LBL_WAF_OPT_CSRFSHIELD_BASIC')),
			JHTML::_('select.option', '2', JText::_('ATOOLS_LBL_WAF_OPT_CSRFSHIELD_ADVANCED'))
		);

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	public static function autoroots($name, $attribs = null, $selected = null)
	{
		$options = array(
			JHTML::_('select.option', '-1', '---'),
			JHTML::_('select.option', '0', JText::_('ATOOLS_LBL_HTMAKER_AUTOROOT_OFF')),
			JHTML::_('select.option', '1', JText::_('ATOOLS_LBL_HTMAKER_AUTOROOT_STD')),
			JHTML::_('select.option', '2', JText::_('ATOOLS_LBL_HTMAKER_AUTOROOT_ALT'))
		);

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	public static function published($selected = null, $id = 'enabled', $attribs = array())
	{
		$options = array();
		$options[] = JHTML::_('select.option', '', '- ' . JText::_('ATOOLS_LBL_SELECT_STATE') . ' -');
		$options[] = JHTML::_('select.option', 0, JText::_('UNPUBLISHED'));
		$options[] = JHTML::_('select.option', 1, JText::_('PUBLISHED'));

		return self::genericlist($options, $id, $attribs, $selected, $id);
	}

	public static function reasons($selected = null, $id = 'reason', $attribs = array())
	{
		$reasons = array(
			'other', 'adminpw', 'ipwl', 'ipbl', 'sqlishield', 'antispam',
			'tpone', 'tmpl', 'template', 'muashield', 'csrfshield', 'badbehaviour',
			'geoblocking', 'rfishield', 'dfishield', 'uploadshield', 'sessionshield',
			'httpbl', 'loginfailure', 'securitycode', 'external', 'awayschedule', 'admindir'
		);

		$options = array();

		foreach ($reasons as $reason)
		{
			$options[] = JHTML::_('select.option', $reason, JText::_('ATOOLS_LBL_REASON_' . strtoupper($reason)));
		}

		// Enable miscellaneous reasons, for use in email templates
        if (isset($attribs['misc']))
        {
            $options[] = JHTML::_('select.option', 'user-reactivate', JText::_('ATOOLS_LBL_USER_REACTIVATE'));
            $options[] = JHTML::_('select.option', 'adminloginfail', JText::_('COM_ADMINTOOLS_EMAILTEMPLATE_REASON_ADMINLOGINFAIL'));
            $options[] = JHTML::_('select.option', 'adminloginsuccess', JText::_('COM_ADMINTOOLS_EMAILTEMPLATE_REASON_ADMINLOGINSUCCESS'));
            $options[] = JHTML::_('select.option', 'ipautoban', JText::_('COM_ADMINTOOLS_EMAILTEMPLATE_REASON_IPAUTOBAN'));
            unset($attribs['misc']);
        }

		// Let's sort the list alphabetically
		JArrayHelper::sortObjects($options, 'text');

		if (isset($attribs['all']))
		{
			array_unshift($options, JHTML::_('select.option', 'all', JText::_('ATOOLS_LBL_REASON_ALL')));
			unset($attribs['all']);
		}

		if (!isset($attribs['hideEmpty']))
		{
			array_unshift($options, JHTML::_('select.option', '', '- ' . JText::_('ATOOLS_LBL_REASON_SELECT') . ' -'));
		}
		else
		{
			unset($attribs['hideEmpty']);
		}

		return self::genericlist($options, $id, $attribs, $selected, $id);
	}

	public static function wwwredirs($name, $attribs = null, $selected = null)
	{
		$options = array(
			JHTML::_('select.option', '-1', '---'),
			JHTML::_('select.option', '0', JText::_('ATOOLS_LBL_HTMAKER_WWWREDIR_NO')),
			JHTML::_('select.option', '1', JText::_('ATOOLS_LBL_HTMAKER_WWWREDIR_WWW')),
			JHTML::_('select.option', '2', JText::_('ATOOLS_LBL_HTMAKER_WWWREDIR_NONWWW'))
		);

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	public static function perms($name, $attribs = null, $selected = null)
	{
		$rawperms = array(0400, 0440, 0444, 0600, 0640, 0644, 0660, 0664, 0700, 0740, 0744, 0750, 0754, 0755, 0757, 0770, 0775, 0777);

		$options = array();
		$options[] = JHTML::_('select.option', '', '---');

		foreach ($rawperms as $perm)
		{
			$text = decoct($perm);
			$options[] = JHTML::_('select.option', '0' . $text, $text);
		}

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	public static function trsfreqlist($name, $attribs = null, $selected = null)
	{
		$freqs = array('second', 'minute', 'hour', 'day');

		$options = array();
		$options[] = JHTML::_('select.option', '', '---');
		foreach ($freqs as $freq)
		{
			$text = JText::_('ATOOLS_LBL_WAF_LBL_FREQ' . strtoupper($freq));
			$options[] = JHTML::_('select.option', $freq, $text);
		}

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	public static function deliverymethod($name, $attribs = null, $selected = null)
	{
		$options = array(
			JHTML::_('select.option', '-1', '---'),
			JHTML::_('select.option', 'plugin', JText::_('ATOOLS_LBL_SEOANDLINK_OPT_JSDELIVERY_PLUGIN')),
			JHTML::_('select.option', 'direct', JText::_('ATOOLS_LBL_SEOANDLINK_OPT_JSDELIVERY_DIRECT'))
		);

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	public static function httpschemes($name, $attribs = null, $selected = null)
	{
		$options = array(
			JHTML::_('select.option', 'http', JText::_('ATOOLS_LBL_WAFCONFIG_IPLOOKUPSCHEME_HTTP')),
			JHTML::_('select.option', 'https', JText::_('ATOOLS_LBL_WAFCONFIG_IPLOOKUPSCHEME_HTTPS'))
		);

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	public static function scanresultstatus($name, $attribs = null, $selected = null)
	{
		$options = array(
			JHTML::_('select.option', '', '- ' . JText::_('ATOOLS_LBL_SELECT_STATE') . ' -'),
			JHTML::_('select.option', 'new', JText::_('COM_ADMINTOOLS_LBL_SCANALERTS_STATUS_NEW')),
			JHTML::_('select.option', 'suspicious', JText::_('COM_ADMINTOOLS_LBL_SCANALERTS_STATUS_SUSPICIOUS')),
			JHTML::_('select.option', 'modified', JText::_('COM_ADMINTOOLS_LBL_SCANALERTS_STATUS_MODIFIED')),
		);

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	public static function symlinks($name, $attribs = null, $selected = null)
	{
		$options = array(
			JHTML::_('select.option', '0', JText::_('COM_ADMINTOOLS_LBL_HTMAKER_SYMLINKS_OFF')),
			JHTML::_('select.option', '1', JText::_('COM_ADMINTOOLS_LBL_HTMAKER_SYMLINKS_FOLLOW')),
			JHTML::_('select.option', '2', JText::_('COM_ADMINTOOLS_LBL_HTMAKER_SYMLINKS_IFOWNERMATCH')),
		);

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	public static function languages($selected = null, $id = 'language', $attribs = array())
	{
		JLoader::import('joomla.language.helper');
		$languages = JLanguageHelper::getLanguages('lang_code');
		$options = array();

		if (isset($attribs['allow_empty']))
		{
			if ($attribs['allow_empty'])
			{
				$options[] = JHTML::_('select.option', '', '- ' . JText::_('JALL_LANGUAGE') . ' -');
			}
		}

		$options[] = JHTML::_('select.option', '*', JText::_('JALL_LANGUAGE'));
		if (!empty($languages))
		{
			foreach ($languages as $key => $lang)
			{
				$options[] = JHTML::_('select.option', $key, $lang->title);
			}
		}

		return self::genericlist($options, $id, $attribs, $selected, $id);
	}

	public static function keepUrlParamsList($name, $attribs = null, $selected = null)
	{
		$options = array(
			JHTML::_('select.option', '', '- - -'),
			JHTML::_('select.option', '0', JText::_('COM_ADMINTOOLS_LBL_KEEPURLPARAMS_OFF')),
			JHTML::_('select.option', '1', JText::_('COM_ADMINTOOLS_LBL_KEEPURLPARAMS_ALL')),
			JHTML::_('select.option', '2', JText::_('COM_ADMINTOOLS_LBL_KEEPURLPARAMS_ADD')),
		);

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

    public static function httpVerbs($name, $attribs = null, $selected = null)
    {
        $options = array(
            JHTML::_('select.option', '', '- - -'),
            JHTML::_('select.option', 'GET', 'GET'),
            JHTML::_('select.option', 'POST', 'POST'),
            JHTML::_('select.option', 'PUT', 'PUT'),
            JHTML::_('select.option', 'DELETE', 'DELETE'),
            JHTML::_('select.option', 'HEAD', 'HEAD'),
            JHTML::_('select.option', 'TRACE', 'TRACE'),
        );

        return self::genericlist($options, $name, $attribs, $selected, $name);
    }

    public static function queryParamType($name, $attribs = null, $selected = null)
    {
        $options = array(
            JHTML::_('select.option', '', '- - -'),
            JHTML::_('select.option', 'E', JText::_('ATOOLS_LBL_WAFBLACKLISTS_QUERY_CONTENT_EXACT')),
            JHTML::_('select.option', 'P', JText::_('ATOOLS_LBL_WAFBLACKLISTS_QUERY_CONTENT_PARTIAL')),
            JHTML::_('select.option', 'R', JText::_('ATOOLS_LBL_WAFBLACKLISTS_QUERY_CONTENT_REGEX')),
        );

        return self::genericlist($options, $name, $attribs, $selected, $name);
    }

	public static function etagtype($name, $attribs = null, $selected = null)
	{
		$options = array(
			JHTML::_('select.option', 'default', JText::_('ATOOLS_LBL_HTMAKER_ETAGTYPE_DEFAULT')),
			JHTML::_('select.option', 'full', JText::_('ATOOLS_LBL_HTMAKER_ETAGTYPE_FULL')),
			JHTML::_('select.option', 'sizetime', JText::_('ATOOLS_LBL_HTMAKER_ETAGTYPE_SIZETIME')),
			JHTML::_('select.option', 'size', JText::_('ATOOLS_LBL_HTMAKER_ETAGTYPE_SIZE')),
			JHTML::_('select.option', 'none', JText::_('ATOOLS_LBL_HTMAKER_ETAGTYPE_NONE')),
		);

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	public static function etagtypeIIS($name, $attribs = null, $selected = null)
	{
		$options = array(
			JHTML::_('select.option', 'default', JText::_('ATOOLS_LBL_HTMAKER_ETAGTYPE_DEFAULT')),
			JHTML::_('select.option', 'none', JText::_('ATOOLS_LBL_HTMAKER_ETAGTYPE_NONE')),
		);

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}

	public static function etagtypeNginX($name, $attribs = null, $selected = null)
	{
		$options = array(
			JHTML::_('select.option', '-1', JText::_('ATOOLS_LBL_HTMAKER_ETAGTYPE_DEFAULT')),
			JHTML::_('select.option', '1', JText::_('ATOOLS_LBL_HTMAKER_ETAGTYPE_FULL')),
			JHTML::_('select.option', '0', JText::_('ATOOLS_LBL_HTMAKER_ETAGTYPE_NONE')),
		);

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
    public static function csvdelimiters($name = 'csvdelimiters', $selected = 1, $attribs = array())
    {
        $options   = array();
        $options[] = JHtml::_('select.option', '1', 'abc, def');
        $options[] = JHtml::_('select.option', '2', 'abc; def');
        $options[] = JHtml::_('select.option', '3', '"abc"; "def"');
        $options[] = JHtml::_('select.option', '-99', JText::_('COM_ADMINTOOLS_IMPORT_DELIMITERS_CUSTOM'));

        return self::genericlist($options, $name, $attribs, $selected, $name);
    }
}