<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/joomla-platform
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2020 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

defined( '_JEXEC' ) or die( 'Restricted Access' );

use JchOptimize\Platform\Paths;
use JchOptimize\Core\Admin\Helper as AdminHelper;

/** @var \JchOptimize\Component\Admin\Model\Ajax $oModel */
$oModel = $this->getModel();
$root   = Paths::rootPath();

$oModel->savestate( false );
$dir     = $oModel->getState( 'dir', '' ) . '/';
$view    = $oModel->getState( 'jchview', '' );
$initial = $oModel->getState( 'initial', '0' );
$oModel->clearState();

$files = array_diff( scandir( $root . $dir ), array( '..', '.' ) );

$directories = array();
$imagefiles  = array();

$i = 0;
$j = 0;

foreach ( $files as $file )
{
	if ( is_dir( $root . $dir . $file ) && $file != 'jch_optimize_backup_images' && $file != '.jch' )
	{
		if ( $i > 500 )
		{

			if ( $j > 1000 )
			{
				break;
			}

			continue;
		}

		$directories[ $i ]['name']      = $file;
		$directories[ $i ]['file_path'] = $dir . $file;

		$i++;
	}
        elseif ( $view != 'tree' && preg_match( '#\.(?:gif|jpe?g|png)$#i', $file ) && @file_exists( $root . $dir . $file ) )
	{
		if ( $j > 1000 )
		{
			if ( $i > 500 )
			{
				break;
			}

			continue;
		}

		$imagefiles[ $j ]['ext']       = preg_replace( '/^.*\./', '', $file );
		$imagefiles[ $j ]['name']      = $file;
		$imagefiles[ $j ]['file_path'] = $dir . $file;
		$imagefiles[ $j ]['optimized'] = in_array( $root . $dir . $file, AdminHelper::getOptimizedFiles() ) ? true : false;

		$j++;
	}
}
?>

@section('subdirectory-header')
    <div id="files-container-header">
        <ul class="jqueryFileTree">
            <li class="check-all">
                <span><input type="checkbox"></span><span><em>Check all</em></span>
                <span><em>@lang( 'JCH_WIDTH' )</em></span>
                <span><em>@lang( 'JCH_HEIGHT' )</em></span>
            </li>
        </ul>
    </div>
@stop

@section('file-tree')

    <ul class="jqueryFileTree">

        @foreach($directories as $directory)

            <li class="directory collapsed">

                @if ($view != 'tree')
                    <span><input type="checkbox" value="{{{$directory['file_path']}}}"></span>
                @endif
                <a href="#" data-url="{{{$directory['file_path']}}}">{{{htmlentities($directory['name'])}}}</a>
            </li>

        @endforeach

        @if ($view != 'tree')
            @foreach($imagefiles as $image)

                <li class="file ext_{{{$image['ext']}}}">
                    <span><input type="checkbox" value="{{{$image['file_path']}}}"></span>
                    <span {{{$image['optimized'] ? 'style="color:blue; font-style: italic;"' : ''}}}>
                        <a href="#" data-url="{{{$image['file_path']}}}">{{{htmlentities($image['name'])}}}</a>
                    </span>
                    <span><input type="text" size="10" maxlength="5" name="width"></span>
                    <span><input type="text" size="10" maxlength="5" name="height"></span>
                </li>

            @endforeach
        @endif

    </ul>

@stop


@if ($view != 'tree')
    @yield('subdirectory-header')
@endif


@if($initial && $view == 'tree')

    <div class="files-content">
        <ul class="jqueryFileTree">
            <li class="directory expanded root"><a href="#" data-root="{{{$root}}}" data-url="">&lt;root&gt;</a>

                @yield('file-tree')

            </li>
        </ul>
    </div>

@elseif($view != 'tree')

    <div class="files-content">

        @yield('file-tree')

    </div>

@else

    @yield('file-tree')

@endif


