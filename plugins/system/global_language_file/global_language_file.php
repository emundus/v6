<?php
/**
 * @package     eMundus.user_param_redirect
 *
 * @author      Hugo Moracchini
 * @copyright   Copyright (C) 2019 eMundus All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * Forces a user on a form for as long as he has a specific param in his account set to true.
 *
 * @package  eMundus.user_param_redirect
 */
class PlgSystemGlobal_language_file extends JPlugin {

	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  3.1
	 */
	protected $autoloadLanguage = true;
}