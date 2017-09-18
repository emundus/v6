<?php
/**
 * @package    DPCalendar
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2007 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

JLoader::import('joomla.application.component.helper');

class JHtmlShare
{

	public static $fbLanguages = array(
			'ca_ES',
			'cs_CZ',
			'cy_GB',
			'da_DK',
			'de_DE',
			'eu_ES',
			'en_PI',
			'en_UD',
			'ck_US',
			'en_US',
			'es_LA',
			'es_CL',
			'es_CO',
			'es_ES',
			'es_MX',
			'es_VE',
			'fb_FI',
			'fi_FI',
			'fr_FR',
			'gl_ES',
			'hu_HU',
			'it_IT',
			'ja_JP',
			'ko_KR',
			'nb_NO',
			'nn_NO',
			'nl_NL',
			'pl_PL',
			'pt_BR',
			'pt_PT',
			'ro_RO',
			'ru_RU',
			'sk_SK',
			'sl_SI',
			'sv_SE',
			'th_TH',
			'tr_TR',
			'ku_TR',
			'zh_CN',
			'zh_HK',
			'zh_TW',
			'fb_LT',
			'af_ZA',
			'sq_AL',
			'hy_AM',
			'az_AZ',
			'be_BY',
			'bn_IN',
			'bs_BA',
			'bg_BG',
			'hr_HR',
			'nl_BE',
			'en_GB',
			'eo_EO',
			'et_EE',
			'fo_FO',
			'fr_CA',
			'ka_GE',
			'el_GR',
			'gu_IN',
			'hi_IN',
			'is_IS',
			'id_ID',
			'ga_IE',
			'jv_ID',
			'kn_IN',
			'kk_KZ',
			'la_VA',
			'lv_LV',
			'li_NL',
			'lt_LT',
			'mk_MK',
			'mg_MG',
			'ms_MY',
			'mt_MT',
			'mr_IN',
			'mn_MN',
			'ne_NP',
			'pa_IN',
			'rm_CH',
			'sa_IN',
			'sr_RS',
			'so_SO"',
			'sw_KE',
			'tl_PH',
			'ta_IN',
			'tt_RU',
			'te_IN',
			'ml_IN',
			'uk_UA',
			'uz_UZ',
			'vi_VN',
			'xh_ZA',
			'zu_ZA',
			'km_KH',
			'tg_TJ',
			'ar_AR',
			'he_IL',
			'ur_PK',
			'fa_IR',
			'sy_SY',
			'yi_DE',
			'gn_PY',
			'qu_PE',
			'ay_BO',
			'se_NO',
			'ps_AF',
			'tl_ST'
	);

	private static $googleLanguages = array(
			'ar',
			'bg',
			'ca',
			'zh-CN',
			'zh-TW',
			'hr',
			'cs',
			'da',
			'nl',
			'en-GB',
			'en-US',
			'et',
			'fil',
			'fi',
			'fr',
			'de',
			'el',
			'iw',
			'hi',
			'hu',
			'id',
			'it',
			'ja',
			'ko',
			'lv',
			'lt',
			'ms',
			'no',
			'fa',
			'pl',
			'pt-BR',
			'pt-PT',
			'ro',
			'ru',
			'sr',
			'sk',
			'sl',
			'es',
			'es-419',
			'sv',
			'th',
			'tr',
			'uk',
			'vi'
	);

	private static $twitterLanguages = array(
			'ko',
			'fr',
			'ja',
			'it',
			'id',
			'en',
			'nl',
			'pt',
			'ru',
			'es',
			'de',
			'tr'
	);

	private static $linkedinLanguages = array(
			'en' => 'en_US',
			'fr' => 'fr_FR',
			'es' => 'es_ES',
			'ru' => 'ru_RU',
			'de' => 'de_DE',
			'it' => 'it_IT',
			'pt' => 'pt_BR',
			'ro' => 'ro_RO',
			'tr' => 'tr_TR',
			'ja' => 'ja_JP',
			'in' => 'in_ID',
			'ms' => 'ms_MY',
			'ko' => 'ko_KR',
			'sv' => 'sv_SE',
			'cs' => 'cs_CZ',
			'nl' => 'nl_NL',
			'pl' => 'pl_PL',
			'no' => 'no_NO',
			'da' => 'da_DK'
	);

	private static $xingLanguages = array(
			'de' => 'de_DE'
	);

	public static function twitter ($params)
	{
		if (! $params->get('enable_twitter', 1))
		{
			return '';
		}
		$document = JFactory::getDocument();
		$document->addScript((JBrowser::getInstance()->isSSLConnection() ? "https" : "http") . "://platform.twitter.com/widgets.js");

		$url = self::getUrl();

		$language = DPCalendarHelper::getFrLanguage();
		$language_twitter = substr($language, 0, strpos($language, '-'));
		if (! in_array($language_twitter, self::$twitterLanguages))
		{
			$language_twitter = 'en';
		}

		$data_via_twitter = $params->get('data_via_twitter');
		$data_related_twitter = $params->get('data_related_twitter');
		$show_count_twitter = $params->get('show_count_twitter', 'horizontal');
		if ($language_twitter != "en")
		{
			$language_twitter = "data-lang=\"$language_twitter\"";
		}
		else
		{
			$language_twitter = '';
		}
		if ($data_via_twitter != "")
		{
			$data_via_twitter = "data-via=\"$data_via_twitter\"";
		}
		else
		{
			$data_via_twitter = '';
		}
		if ($data_related_twitter != "")
		{
			$data_related_twitter = "data-related=\"$data_related_twitter\"";
		}
		else
		{
			$data_related_twitter = '';
		}
		$title = htmlspecialchars($document->getTitle(), ENT_QUOTES, "UTF-8");
		$tmp = "<div><a href=\"" . (JBrowser::getInstance()->isSSLConnection() ? "https" : "http") . "://twitter.com/share\" class=\"twitter-share-button\"
		$language_twitter $data_via_twitter $data_related_twitter
		data-url=\"$url\"
		data-text=\"$title\"
		data-count=\"$show_count_twitter\">Tweet</a></div>";

		return '<div class="dp-share-button">' . $tmp . '</div>';
	}

	public static function like ($params)
	{
		if (! $params->get('enable_like', 1))
		{
			return '';
		}

		$url = self::getUrl();
		$language = DPCalendarHelper::getFrLanguage();

		$tmpLanguage = $language;
		$tmpLanguage = str_replace('-', '_', $tmpLanguage);
		if (! in_array($tmpLanguage, self::$fbLanguages))
		{
			$tmpLanguage = 'en_US';
		}
		$document = JFactory::getDocument();
		$document->addScript((JBrowser::getInstance()->isSSLConnection() ? "https" : "http") . "://connect.facebook.net/$tmpLanguage/all.js#xfbml=1");

		if ($params->get('comment_fb_og_url', 1))
		{
			$document->addCustomTag('<meta property="og:url" content="' . $url . '"/>');
		}

		// Is needed to make the popup width enough great
		$document->addStyleDeclaration('.fb_iframe_widget {max-width: none;z-index:10} .fb_iframe_widget_lift  {max-width: none;}');

		$layout_style = $params->get('layout_style', 'button_count');
		$show_faces = $params->get('show_faces');
		if ($show_faces == 1)
		{
			$show_faces = "true";
		}
		else
		{
			$show_faces = "false";
		}
		$width_like = $params->get('width_like');
		$send = $params->get('send', '0');
		if ($send == 2)
		{
			$standalone = 1;
		}
		else
		{
			$standalone = 0;
			if ($send == 1)
			{
				$send = "true";
			}
			else
			{
				$send = "false";
			}
		}
		$verb_to_display = $params->get('verb_to_display', '1');
		if ($verb_to_display == 1)
		{
			$verb_to_display = "like";
		}
		else
		{
			$verb_to_display = "recommend";
		}
		$font = $params->get('font');
		$color_scheme = $params->get('color_scheme', 'light');
		$code_like = "";
		if ($standalone == 1)
		{
			$tmp = "<fb:send href=\"$url\" font=\"$font\" colorscheme=\"$color_scheme\"></fb:send>";
			$code_like .= "<div>$tmp</div>";
		}
		$code_like .= "<fb:like href=\"$url\"
			layout=\"$layout_style\" show_faces=\"$show_faces\"
			send=\"$send\" width=\"$width_like\" action=\"$verb_to_display\"
			font=\"$font\" colorscheme=\"$color_scheme\"></fb:like> \n";
		return '<div class="dp-share-button">' . $code_like . '</div>';
	}

	public static function linkedin ($params)
	{
		if (! $params->get('enable_linkedin', 1))
		{
			return '';
		}
		$document = JFactory::getDocument();
		$url = self::getUrl();

		$language = DPCalendarHelper::getFrLanguage();
		$language_linkedin = substr($language, 0, strpos($language, '-'));
		if (! array_key_exists($language_linkedin, self::$linkedinLanguages))
		{
			$language_linkedin = 'en_US';
		}
		else
		{
			$language_linkedin = self::$linkedinLanguages[$language_linkedin];
		}
		$show_count_linkedin = 'data-counter="right"';
		if ($params->get('show_count_linkedin', '') == 'vertical')
		{
			$show_count_linkedin = 'data-counter="top"';
		}
		if ($params->get('show_count_linkedin', '') == 'none')
		{
			$show_count_linkedin = '';
		}
		$title = htmlspecialchars($document->getTitle(), ENT_QUOTES, "UTF-8");
		$tmp = '<div><script src="' . (JBrowser::getInstance()->isSSLConnection() ? "https" : "http") .
				 '://platform.linkedin.com/in.js" type="text/javascript">lang: ' . $language_linkedin . '</script><script type="IN/Share" data-url="' .
				 $url . '" ' . $show_count_linkedin . ' data-showzero="true"></script></div>';
		return '<div class="dp-share-button">' . $tmp . '</div>';
	}

	public static function xing ($params)
	{
		if (! $params->get('enable_xing', 0))
		{
			return '';
		}
		$document = JFactory::getDocument();
		$url = self::getUrl();

		$language = DPCalendarHelper::getFrLanguage();
		$languageXing = substr($language, 0, strpos($language, '-'));
		if (! array_key_exists($languageXing, self::$xingLanguages))
		{
			$languageXing = '';
		}
		else
		{
			$languageXing = self::$xingLanguages[$languageXing];
		}
		$showCountXing = 'data-counter="right"';
		if ($params->get('show_count_xing', '') == 'vertical')
		{
			$showCountXing = 'data-counter="top"';
		}
		if ($params->get('show_count_xing', '') == 'none')
		{
			$showCountXing = '';
		}
		$title = htmlspecialchars($document->getTitle(), ENT_QUOTES, "UTF-8");
		$tmp = '<div data-type="xing/share" ' . $showCountXing . '></div>';
		$tmp .= '<script>;(function (d, s) {
    var x = d.createElement(s),
      s = d.getElementsByTagName(s)[0];
      x.src = "https://www.xing-share.com/plugins/share.js";
      s.parentNode.insertBefore(x, s);
  })(document, "script");</script>';
		return '<div class="dp-share-button">' . $tmp . '</div>';
	}

	public static function google ($params)
	{
		if (! $params->get('enable_google', 1))
		{
			return '';
		}

		$url = self::getUrl();
		$language = DPCalendarHelper::getFrLanguage();

		$language_google = $language;
		if (! in_array($language_google, self::$googleLanguages))
		{
			$language_google = substr($language, 0, strpos($language, '-'));
		}
		if (! in_array($language_google, self::$googleLanguages))
		{
			$language_google = 'en';
		}

		$size_google = $params->get('size_google', 'standard');
		$show_count_google = $params->get('show_count_google', '1');

		$tmp = "<script type=\"text/javascript\"
				src=\"" .
				 (JBrowser::getInstance()->isSSLConnection() ? "https" : "http") . "://apis.google.com/js/plusone.js\">
				{lang: '" . $language_google . "'}
				</script>";
		$tmp .= "<g:plusone size=\"$size_google\" href=\"$url\" count=\"$show_count_google\"></g:plusone>";
		return '<div class="dp-share-button">' . $tmp . '</div>';
	}

	public static function comment ($params, $event = null)
	{
		if ($params->get('comment_system', 0) == 'facebook')
		{
			$width = $params->get('comment_fb_width', 700);
			$num_posts = $params->get('comment_fb_num_posts', 10);
			$app_id = $params->get('comment_fb_app_id', '');
			$admin_id = $params->get('comment_fb_admin_id', '');
			$colorscheme = $params->get('comment_fb_colorscheme', 'light');
			$og_url = $params->get('comment_fb_og_url', '1');
			$og_type = $params->get('comment_fb_og_type', 'article');
			$og_image = $params->get('comment_fb_og_image', '');

			$url = self::getUrl();

			$language = DPCalendarHelper::getFrLanguage();

			$tmpLanguage = $language;
			$tmpLanguage = str_replace('-', '_', $tmpLanguage);
			if (! in_array($tmpLanguage, self::$fbLanguages))
			{
				$tmpLanguage = 'en_US';
			}

			$doc = JFactory::getDocument();
			$doc->addScript((JBrowser::getInstance()->isSSLConnection() ? "https" : "http") . "://connect.facebook.net/$tmpLanguage/all.js#xfbml=1");
			if ($app_id != "")
			{
				$doc->addCustomTag('<meta property="fb:app_id" content="' . $app_id . '"/>');
			}
			if ($og_url == "1")
			{
				$doc->addCustomTag('<meta property="fb:admins" content="' . $admin_id . '"/>');
				$doc->addCustomTag('<meta property="og:type" content="' . $og_type . '"/>');
				$doc->addCustomTag('<meta property="og:url" content="' . $url . '"/>');
				$doc->addCustomTag('<meta property="og:site_name" content="' . JFactory::getConfig()->get('config.sitename') . '"/>');
				$doc->addCustomTag('<meta property="og:locale" content="' . $tmpLanguage . '"/>');
				$doc->addCustomTag('<meta property="og:title" content="' . $doc->getTitle() . '"/>');
			}
			if ($og_image != "")
			{
				$doc->addCustomTag('<meta property="og:image" content="' . $og_image . '"/>');
			}
			$url = str_replace("/?option", "/index.php?option", $url);
			$pos = strpos($url, "&fb_comment_id");
			if ($pos)
			{
				$url = substr($url, 0, $pos);
			}
			$pos = strpos($url, "?fb_comment_id");
			if ($pos)
			{
				$url = substr($url, 0, $pos);
			}
			$html = "<fb:comments width=\"" . $width . "\" num_posts=\"" . $num_posts . "\" href=\"" . $url . "\" colorscheme=\"" . $colorscheme .
					 "\"></fb:comments>";

			return '<div class="dpcal-fb-comments-box">' . $html . '</div>';
		}
		if ($params->get('comment_system', 0) == 'googleplus')
		{
			$doc = JFactory::getDocument();
			$doc->addScript((JBrowser::getInstance()->isSSLConnection() ? "https" : "http") . "://apis.google.com/js/plusone.js");
			return '<div class="g-comments"
				data-href="' . JFactory::getURI()->toString() . '" data-width="' .
					 $params->get('comment_gp_width', 700) . '"
    			data-first_party_property="BLOGGER" data-view_type="FILTERED_POSTMOD"></div>';
		}
		if ($params->get('comment_system', 0) == 'jcomments' && $event != null && is_numeric($event->id))
		{
			$comments = JPATH_SITE . DS . 'components' . DS . 'com_jcomments' . DS . 'jcomments.php';
			if (JFile::exists($comments))
			{
				require_once $comments;
				return JComments::showComments($event->id, 'com_dpcalendar', $event->title);
			}
		}
	}

	private static function getUrl ()
	{
		$url = JFactory::getURI()->toString();
		$url = htmlspecialchars($url);
		$url = str_replace('?tmpl=component', '', $url);
		$url = str_replace('&tmpl=component', '', $url);
		return $url;
	}
}
