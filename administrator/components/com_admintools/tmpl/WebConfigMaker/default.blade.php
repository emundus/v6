<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2022 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') || die;

/** @var $this Akeeba\AdminTools\Admin\View\WebConfigMaker\Html */

$config = $this->wcconfig;
?>
<div class="akeeba-block--info">
	<p>
		<strong>
			@lang('COM_ADMINTOOLS_LBL_WEBCONFIGMAKER_WILLTHISWORK')
		</strong>
	</p>
	<p>
		@if($this->isSupported == 0)
			@lang('COM_ADMINTOOLS_LBL_WEBCONFIGMAKER_WILLTHISWORK_NO')
		@elseif($this->isSupported == 1)
			@lang('COM_ADMINTOOLS_LBL_WEBCONFIGMAKER_WILLTHISWORK_YES')
		@else
			@lang('COM_ADMINTOOLS_LBL_WEBCONFIGMAKER_WILLTHISWORK_MAYBE')
		@endif
	</p>
</div>

<div class="akeeba-block--warning">
	<h3>@lang('COM_ADMINTOOLS_LBL_WEBCONFIGMAKER_WARNING')</h3>

	<p>@lang('COM_ADMINTOOLS_LBL_WEBCONFIGMAKER_WARNTEXT')</p>

	<p>@lang('COM_ADMINTOOLS_LBL_WEBCONFIGMAKER_TUNETEXT')</p>
</div>

<form name="adminForm" id="adminForm" action="index.php" method="post" class="akeeba-form--horizontal">
	<!-- ======================================================================= -->
	<div class="akeeba-panel--primary">
		<header class="akeeba-block-header">
			<h3>@lang('COM_ADMINTOOLS_LBL_HTACCESSMAKER_BASICSEC')</h3>
		</header>

		<div class="akeeba-form-group">
			<label for="nodirlists">@lang('COM_ADMINTOOLS_LBL_HTACCESSMAKER_NODIRLISTS')</label>

			@jhtml('FEFHelp.select.booleanswitch', 'nodirlists', $config->nodirlists)
		</div>

		<div class="akeeba-form-group">
			<label for="fileinj">@lang('COM_ADMINTOOLS_LBL_HTACCESSMAKER_FILEINJ')</label>

			@jhtml('FEFHelp.select.booleanswitch', 'fileinj', $config->fileinj)
		</div>

		<div class="akeeba-form-group">
			<label for="phpeaster">@lang('COM_ADMINTOOLS_LBL_HTACCESSMAKER_PHPEASTER')</label>

			@jhtml('FEFHelp.select.booleanswitch', 'phpeaster', $config->phpeaster)
		</div>

		<div class="akeeba-form-group">
			<label for="leftovers">@lang('COM_ADMINTOOLS_LBL_HTACCESSMAKER_LEFTOVERS')</label>

			@jhtml('FEFHelp.select.booleanswitch', 'leftovers', $config->leftovers)
		</div>

		<div class="akeeba-form-group">
			<label for="clickjacking">@lang('COM_ADMINTOOLS_LBL_HTACCESSMAKER_CLICKJACKING')</label>

			@jhtml('FEFHelp.select.booleanswitch', 'clickjacking', $config->clickjacking)
		</div>

		<div class="akeeba-form-group">
			<label for="reducemimetyperisks">@lang('COM_ADMINTOOLS_LBL_HTACCESSMAKER_REDUCEMIMETYPERISKS')</label>

			@jhtml('FEFHelp.select.booleanswitch', 'reducemimetyperisks', $config->reducemimetyperisks)
		</div>

		<div class="akeeba-form-group">
			<label for="reflectedxss">@lang('COM_ADMINTOOLS_LBL_HTACCESSMAKER_REFLECTEDXSS')</label>

			@jhtml('FEFHelp.select.booleanswitch', 'reflectedxss', $config->reflectedxss)
		</div>

		<div class="akeeba-form-group">
			<label for="noserversignature">@lang('COM_ADMINTOOLS_LBL_HTACCESSMAKER_NOSERVERSIGNATURE')</label>

			@jhtml('FEFHelp.select.booleanswitch', 'noserversignature', $config->noserversignature)
		</div>

		<div class="akeeba-form-group">
			<label for="notransform">@lang('COM_ADMINTOOLS_LBL_HTACCESSMAKER_NOTRANSFORM')</label>

			@jhtml('FEFHelp.select.booleanswitch', 'notransform', $config->notransform)
		</div>

		<div class="akeeba-form-group">
			<label for="nohoggers">@lang('COM_ADMINTOOLS_LBL_HTACCESSMAKER_NOHOGGERS')</label>

			@jhtml('FEFHelp.select.booleanswitch', 'nohoggers', $config->nohoggers)
		</div>

		<div class="akeeba-form-group">
			<label for="hoggeragents">@lang('COM_ADMINTOOLS_LBL_HTACCESSMAKER_HOGGERAGENTS')</label>

			<textarea cols="80" rows="10" name="hoggeragents" id="hoggeragents"
					  class="input-wide">{{{ implode("\n", $config->hoggeragents) }}}</textarea>
		</div>
	</div>
	<!-- ======================================================================= -->
	<div class="akeeba-panel--primary">
		<header class="akeeba-block-header">
			<h3>@lang('COM_ADMINTOOLS_LBL_HTACCESSMAKER_SERVERPROT')</h3>
		</header>

		<h3>@lang('COM_ADMINTOOLS_LBL_HTACCESSMAKER_SERVERPROT_TOGGLES')</h3>

		<div class="akeeba-form-group">
			<label for="backendprot">@lang('COM_ADMINTOOLS_LBL_HTACCESSMAKER_BACKENDPROT')</label>

			@jhtml('FEFHelp.select.booleanswitch', 'backendprot', $config->backendprot)
		</div>
		<div class="akeeba-form-group">
			<label for="frontendprot">@lang('COM_ADMINTOOLS_LBL_HTACCESSMAKER_FRONTENDPROT')</label>

			@jhtml('FEFHelp.select.booleanswitch', 'frontendprot', $config->frontendprot)
		</div>

		<h3>@lang('COM_ADMINTOOLS_LBL_HTACCESSMAKER_SERVERPROT_FINETUNE')</h3>

		<div class="akeeba-form-group">
			<label for="bepexdirs">@lang('COM_ADMINTOOLS_LBL_HTACCESSMAKER_BEPEXDIRS')</label>

			<textarea cols="80" rows="10" name="bepexdirs"
					  id="bepexdirs">{{{ implode("\n", $config->bepexdirs) }}}</textarea>
		</div>
		<div class="akeeba-form-group">
			<label for="bepextypes">@lang('COM_ADMINTOOLS_LBL_HTACCESSMAKER_BEPEXTYPES')</label>

			<textarea cols="80" rows="10" name="bepextypes"
					  id="bepextypes">{{{ implode("\n", $config->bepextypes) }}}</textarea>
		</div>
		<div class="akeeba-form-group">
			<label for="fepexdirs">@lang('COM_ADMINTOOLS_LBL_HTACCESSMAKER_FEPEXDIRS')</label>

			<textarea cols="80" rows="10" name="fepexdirs"
					  id="fepexdirs">{{{ implode("\n", $config->fepexdirs) }}}</textarea>
		</div>
		<div class="akeeba-form-group">
			<label for="fepextypes">@lang('COM_ADMINTOOLS_LBL_HTACCESSMAKER_FEPEXTYPES')</label>

			<textarea cols="80" rows="10" name="fepextypes"
					  id="fepextypes">{{{ implode("\n", $config->fepextypes) }}}</textarea>
		</div>

		<h3>@lang('COM_ADMINTOOLS_LBL_HTACCESSMAKER_SERVERPROT_EXCEPTIONS')</h3>

		<div class="akeeba-form-group">
			<label for="exceptionfiles">@lang('COM_ADMINTOOLS_LBL_HTACCESSMAKER_EXCEPTIONFILES')</label>

			<textarea cols="80" rows="10" name="exceptionfiles"
					  id="exceptionfiles">{{{ implode("\n", $config->exceptionfiles) }}}</textarea>
		</div>
		<div class="akeeba-form-group">
			<label for="exceptiondirs">@lang('COM_ADMINTOOLS_LBL_HTACCESSMAKER_EXCEPTIONDIRS')</label>

			<textarea cols="80" rows="10" name="exceptiondirs"
					  id="exceptiondirs">{{{ implode("\n", $config->exceptiondirs) }}}</textarea>
		</div>
		<div class="akeeba-form-group">
			<label for="fullaccessdirs">@lang('COM_ADMINTOOLS_LBL_HTACCESSMAKER_FULLACCESSDIRS')</label>

			<textarea cols="80" rows="10" name="fullaccessdirs"
					  id="fullaccessdirs">{{{ implode("\n", $config->fullaccessdirs) }}}</textarea>
		</div>
	</div>

	<!-- ======================================================================= -->
	<div class="akeeba-panel--primary">
		<header class="akeeba-block-header">
			<h3>@lang('COM_ADMINTOOLS_LBL_HTACCESSMAKER_OPTUTIL')</h3>
		</header>

		<div class="akeeba-form-group">
			<label for="fileorder">@lang('COM_ADMINTOOLS_LBL_HTACCESSMAKER_FILEORDER')</label>

			@jhtml('FEFHelp.select.booleanswitch', 'fileorder', $config->fileorder)
		</div>

		<div class="akeeba-form-group">
			<label for="exptime">@lang('COM_ADMINTOOLS_LBL_HTACCESSMAKER_EXPTIME')</label>

			{{ \Akeeba\AdminTools\Admin\Helper\Select::exptime('exptime', null, $config->exptime) }}
		</div>

		<div class="akeeba-form-group">
			<label for="autocompress">@lang('COM_ADMINTOOLS_LBL_HTACCESSMAKER_AUTOCOMPRESS')</label>

			@jhtml('FEFHelp.select.booleanswitch', 'autocompress', $config->autocompress)
		</div>

		<div class="akeeba-form-group">
			<label for="autoroot">@lang('COM_ADMINTOOLS_LBL_HTACCESSMAKER_AUTOROOT')</label>

			@jhtml('FEFHelp.select.booleanswitch', 'autoroot', $config->autoroot)
		</div>

		<div class="akeeba-form-group">
			<label for="wwwredir">@lang('COM_ADMINTOOLS_LBL_HTACCESSMAKER_WWWREDIR')</label>

			{{ \Akeeba\AdminTools\Admin\Helper\Select::wwwredirs(
				'wwwredir',
				$this->enableRedirects ? [] : ['disabled' => 'disabled'],
				$this->enableRedirects ? $config->wwwredir : 0
			) }}
		</div>

		<div class="akeeba-form-group">
			<label for="olddomain">@lang('COM_ADMINTOOLS_LBL_HTACCESSMAKER_OLDDOMAIN')</label>

			<input type="text" name="olddomain" id="olddomain" value="{{{ $config->olddomain }}}">
		</div>

		<div class="akeeba-form-group">
			<label for="httpsurls">@lang('COM_ADMINTOOLS_LBL_HTACCESSMAKER_HTTPSURLS')</label>

			<textarea cols="80" rows="10" name="httpsurls"
					  id="httpsurls">{{{ implode("\n", $config->httpsurls) }}}</textarea>
		</div>

		<div class="akeeba-form-group">
			<label for="hstsheader">@lang('COM_ADMINTOOLS_LBL_HTACCESSMAKER_HSTSHEADER')</label>

			@jhtml('FEFHelp.select.booleanswitch', 'hstsheader', $config->hstsheader)
		</div>

		<div class="akeeba-form-group">
			<label for="notracetrack">@lang('COM_ADMINTOOLS_LBL_HTACCESSMAKER_NOTRACETRACK')</label>

			@jhtml('FEFHelp.select.booleanswitch', 'notracetrack', $config->notracetrack)
		</div>

		<div class="akeeba-form-group">
			<label for="cors">@lang('COM_ADMINTOOLS_LBL_HTACCESSMAKER_CORS')</label>

			@jhtml('FEFHelp.select.genericlist', \Akeeba\AdminTools\Admin\Helper\Select::getCorsOptions(), 'cors', ['list.select' => $config->cors])
		</div>

		<div class="akeeba-form-group">
			<label for="etagtype">@lang('COM_ADMINTOOLS_LBL_HTACCESSMAKER_ETAGTYPE')</label>

			{{ \Akeeba\AdminTools\Admin\Helper\Select::etagtypeIIS('etagtype', ['class' => 'input-medium'], $config->etagtype) }}
		</div>

		<div class="akeeba-form-group">
			<label for="referrerpolicy">@lang('COM_ADMINTOOLS_LBL_HTACCESSMAKER_REFERERPOLICY')</label>

			{{ \Akeeba\AdminTools\Admin\Helper\Select::referrerpolicy('referrerpolicy', [], $config->referrerpolicy) }}
		</div>
	</div>


	<!-- ======================================================================= -->

	<div class="akeeba-panel--primary">
		<header class="akeeba-block-header">
			<h3>@lang('COM_ADMINTOOLS_LBL_HTACCESSMAKER_SYSCONF')</h3>
		</header>

		<div class="akeeba-form-group">
			<label for="httpshost">@lang('COM_ADMINTOOLS_LBL_HTACCESSMAKER_HTTPSHOST')</label>

			<input type="text" name="httpshost" id="httpshost" value="{{{ $config->httpshost }}}">
		</div>
		<div class="akeeba-form-group">
			<label for="httphost">@lang('COM_ADMINTOOLS_LBL_HTACCESSMAKER_HTTPHOST')</label>

			<input type="text" name="httphost" id="httphost" value="{{{ $config->httphost }}}">
		</div>
		<div class="akeeba-form-group">
			<label for="rewritebase">@lang('COM_ADMINTOOLS_LBL_HTACCESSMAKER_REWRITEBASE')</label>

			<input type="text" name="rewritebase" id="rewritebase"
				   value="{{{ $config->rewritebase }}}">
		</div>
	</div>

	<!-- ======================================================================= -->
	<input type="hidden" name="option" value="com_admintools" />
	<input type="hidden" name="view" value="WebConfigMaker" />
	<input type="hidden" name="task" value="save" />
	<input type="hidden" name="@token(true)" value="1" />
</form>
