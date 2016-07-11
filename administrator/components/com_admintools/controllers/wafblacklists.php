<?php
/**
 * @package   AdminTools
 * @copyright Copyright (c)2010-2016 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die;

class AdmintoolsControllerWafblacklists extends F0FController
{
	protected function onBeforeApplySave(&$data)
	{
		$data['option'] = $data['foption'];
		$data['view'] = $data['fview'];
		$data['task'] = $data['ftask'];
		$data['query'] = $data['fquery'];

		return $this->checkACL('admintools.security');
	}

	protected function onBeforeBrowse()
	{
		return $this->checkACL('admintools.security');
	}

	protected function onBeforeApply()
	{
		return $this->checkACL('admintools.security');
	}

	protected function onBeforeSave()
	{
		return $this->checkACL('admintools.security');
	}

	protected function onBeforePublish()
	{
		return $this->checkACL('admintools.security');
	}

	protected function onBeforeUnpublish()
	{
		return $this->checkACL('admintools.security');
	}

	protected function onBeforeRemove()
	{
		return $this->checkACL('admintools.security');
	}

	protected function onBeforeSavenew()
	{
		return $this->checkACL('admintools.security');
	}
}
