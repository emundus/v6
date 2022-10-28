<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/core
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2020 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace JchOptimize\Core\Css\Sprite;

defined( '_JCH_EXEC' ) or die( 'Restricted access' );

use JchOptimize\Core\Helper;
use JchOptimize\Core\Admin\Helper as AdminHelper;
use JchOptimize\Core\Logger;
use JchOptimize\Core\Css\Sprite\Handlers\HandlerInterface;
use JchOptimize\Platform\FileSystem;
use JchOptimize\Platform\Settings;
use JchOptimize\Platform\Profiler;
use JchOptimize\Platform\Paths;

class CssSpriteGen
{

	public $aFormValues = array();
	public $bTransparent;
	protected $aImageTypes = array();
	protected $aFormErrors = array();
	protected $sZipFolder = '';
	protected $sCss;
	protected $sTempSpriteName;
	protected $bValidImages;
	protected $aBackground = array();
	protected $aPosition = array();
	/** @var HandlerInterface $oImageHandler */
	protected $oImageHandler;
	protected $params;
	protected $bBackend = false;
	/** @var array To store CSS rules */
	private $aCss = array();

	/**
	 * CssSpriteGen constructor.
	 *
	 * @param             $ImageLibrary
	 * @param   Settings  $params
	 * @param   bool      $bBackend
	 */
	public function __construct( $ImageLibrary, $params, $bBackend = false )
	{
		$this->bBackend = $bBackend;
		$this->params   = $params;

		$class = 'JchOptimize\Core\Css\Sprite\Handlers\\' . ucfirst( $ImageLibrary );

		$this->oImageHandler = new $class( $this->params, $this );

		$this->aImageTypes = $this->oImageHandler->getSupportedFormats();
		$this->aFormValues = array(
			'path'                    => '',
			'sub'                     => '',
			'file-regex'              => '',
			'wrap-columns'            => $this->params->get( 'csg_wrap_images', 'off' ),
			'build-direction'         => $this->params->get( 'csg_direction', 'vertical' ),
			'use-transparency'        => 'on',
			'use-optipng'             => '',
			'vertical-offset'         => 50,
			'horizontal-offset'       => 50,
			'background'              => '',
			'image-output'            => 'PNG', //$this->params->get('csg_file_output'),
			'image-num-colours'       => 'true-colour',
			'image-quality'           => 100,
			'width-resize'            => 100,
			'height-resize'           => 100,
			'ignore-duplicates'       => 'merge',
			'class-prefix'            => '',
			'selector-prefix'         => '',
			'selector-suffix'         => '',
			'add-width-height-to-css' => 'off',
			'sprite-path'             => $this->params->get( 'sprite-path' )
		);
	}

	public function GetImageTypes()
	{
		return $this->aImageTypes;
	}

	public function GetSpriteFormats()
	{
		return $this->oImageHandler->aSpriteFormats;
	}

	public function CreateSprite( $aFilePaths )
	{
		// set up variable defaults used when calculating offsets etc
		$aFilesInfo   = array();
		$aFilesMD5    = array();
		$bResize      = false;
		$aValidImages = array();

		//$this->aFormValues['build-direction'] = 'horizontal'
		$iRowCount     = 1;
		$aMaxRowHeight = array();
		$iMaxVOffset   = 0;

		//$this->aFormValues['build-direction'] = 'vertical'
		$iColumnCount    = 1;
		$aMaxColumnWidth = array();
		$iMaxHOffset     = 0;

		$iTotalWidth   = 0;
		$iTotalHeight  = 0;
		$iMaxWidth     = 0;
		$iMaxHeight    = 0;
		$i             = 0;
		$k             = 0;
		$bValidImages  = false;
		$sOutputFormat = strtolower( $this->aFormValues['image-output'] );

		$optimize = false;

		/*                 * **************************************** */
		/* this section calculates all offsets etc */
		/*                 * **************************************** */

		foreach ( $aFilePaths as $sFile )
		{
			JCH_DEBUG ? Profiler::start( 'CalculateSprite' ) : null;

			//Remove CDN domains if present
			$aCdns     = array_keys( Helper::cookieLessDomain( $this->params, '', '', true ) );
			$sFilePath = str_replace( $aCdns, '', $sFile );
			$sFilePath = Helper::getFilepath( $sFilePath );

			$bFileExists = true;

			if ( @file_exists( $sFilePath ) )
			{

				// do we want to scale down the source images
				// scaling up isn't supported as that would result in poorer quality images
				$bResize = ( $this->aFormValues['width-resize'] != 100 && $this->aFormValues['height-resize'] != 100 );

				// grab path information
				//$sFilePath = $sFolderMD5.$sFile;
				$aPathParts    = pathinfo( $sFilePath );
				$sFileBaseName = $aPathParts['basename'];

				$aImageInfo = @getimagesize( $sFilePath );

				if ( $aImageInfo )
				{
					$iWidth     = $aImageInfo[0];
					$iHeight    = $aImageInfo[1];
					$iImageType = $aImageInfo[2];


					// are we matching filenames against a regular expression
					// if so it's likely not all images from the ZIP file will end up in the generated sprite image
					if ( ! empty( $this->aFormValues['file-regex'] ) )
					{
						// forward slashes should be escaped - it's likely not doing this might be a security risk also
						// one might be able to break out and change the modifiers (to for example run PHP code)
						$this->aFormValues['file-regex'] = str_replace( '/', '\/', $this->aFormValues['file-regex'] );

						// if the regular expression matches grab the first match and store for use as the class name
						if ( preg_match( '/^' . $this->aFormValues['file-regex'] . '$/i', $sFileBaseName, $aMatches ) )
						{
							$sFileClass = $aMatches[1];
						}
						else
						{
							$sFileClass = '';
						}
					}
					else
					{ // not using regular expressions - set the class name to the base part of the filename (excluding extension)
						$sFileClass = $aPathParts['basename'];
					}

					// format the class name - it should only contain certain characters
					// this strips out any which aren't
					$sFileClass = $this->FormatClassName( $sFileClass );
				}
				else
				{
					$bFileExists = false;
				}
			}
			else
			{
				$bFileExists = false;
			}

			// the file also isn't valid if its extension doesn't match one of the image formats supported by the tool
			//discard images whose height or width is greater than 50px
			if (
				$bFileExists && ! empty( $sFileClass ) && in_array( strtoupper( $aPathParts['extension'] ), $this->aImageTypes )
				&& in_array( $iImageType, array( IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG ) )
				&& substr( $sFileBaseName, 0, 1 ) != '.'
				&& $iWidth < 50 && $iHeight < 50 && $iWidth > 0 && $iHeight > 0
			)
			{
				// grab the file extension
				$sExtension = $aPathParts['extension'];

				// get MD5 of file (this can be used to compare if a file's content is exactly the same as another's)
				$sFileMD5 = md5( file_get_contents( $sFilePath ) );

				// check if this file's MD5 already exists in array of MD5s recorded so far
				// if so it's a duplicate of another file in the ZIP
				if ( ( $sKey = array_search( $sFileMD5, $aFilesMD5 ) ) !== false )
				{
					// do we want to drop duplicate files and merge CSS rules
					// if so CSS will end up like .filename1, .filename2 { }
					if ( $this->aFormValues['ignore-duplicates'] == 'merge' )
					{

						if ( isset( $aFilesInfo[$sKey]['class'] ) )
						{
							$aFilesInfo[$sKey]['class'] = $aFilesInfo[$sKey]['class'] .
							                              $this->aFormValues['selector-suffix'] . ', ' .
							                              $this->aFormValues['selector-prefix'] . '.' .
							                              $this->aFormValues['class-prefix'] . $sFileClass;
						}

						$this->aBackground[$k] = $sKey;
						$k ++;

						continue;
					}
				}
				else
				{
					$this->aBackground[$k] = $i;
					$k ++;
				}

				// add MD5 to array to check future files against
				$aFilesMD5[$i] = $sFileMD5;
				// store generated class selector details
				//$aFilesInfo[$i]['class'] = ".{$this->aFormValues['class-prefix']}$sFileClass";
				// store file path information and extension
				$aFilesInfo[$i]['path'] = $sFilePath;
				$aFilesInfo[$i]['ext']  = $sExtension;


				if ( $this->aFormValues['build-direction'] == 'horizontal' )
				{
					// get the current width of the sprite image - after images processed so far
					$iCurrentWidth = $iTotalWidth + $this->aFormValues['horizontal-offset'] + $iWidth;

					// store the maximum width reached so far
					// if we're on a new column current height might be less than the maximum
					if ( $iMaxWidth < $iCurrentWidth )
					{
						$iMaxWidth = $iCurrentWidth;
					}
				}
				else
				{
					// get the current height of the sprite image - after images processed so far
					$iCurrentHeight = $iTotalHeight + $this->aFormValues['vertical-offset'] + $iHeight;

					// store the maximum height reached so far
					// if we're on a new column current height might be less than the maximum
					if ( $iMaxHeight < $iCurrentHeight )
					{
						$iMaxHeight = $iCurrentHeight;
					}
				}

				// store the original width and height of the image
				// we'll need this later if the image is to be resized
				$aFilesInfo[$i]['original-width']  = $iWidth;
				$aFilesInfo[$i]['original-height'] = $iHeight;

				// store the width and height of the image
				// if we're resizing they'll be less than the original
				$aFilesInfo[$i]['width']  = $bResize ? round( ( $iWidth / 100 ) * $this->aFormValues['width-resize'] ) : $iWidth;
				$aFilesInfo[$i]['height'] = $bResize ? round( ( $iHeight / 100 ) * $this->aFormValues['height-resize'] ) : $iHeight;

				if ( $this->aFormValues['build-direction'] == 'horizontal' )
				{
					// opera (9.0 and below) has a bug which prevents it recognising  offsets of less than -2042px
					// all subsequent values are treated as -2042px
					// if we've hit 2000 pixels and we care about this (as set in the interface) then wrap to a new row
					// increment row count and reset current height
					if (
						( $iTotalWidth + $this->aFormValues['horizontal-offset'] ) >= 2000 && ! empty( $this->aFormValues['wrap-columns'] )
					)
					{
						$iRowCount ++;
						$iTotalWidth = 0;
					}

					// if the current image is higher than any other in the current row then set the maximum height to that
					// it will be used to set the height of the current row
					if ( $aFilesInfo[$i]['height'] > $iMaxHeight )
					{
						$iMaxHeight = $aFilesInfo[$i]['height'];
					}

					// keep track of the height of rows added so far
					$aMaxRowHeight[$iRowCount] = $iMaxHeight;
					// calculate the current maximum vertical offset so far
					$iMaxVOffset = $this->aFormValues['vertical-offset'] * ( $iRowCount - 1 );

					// get the x position of current image in overall sprite
					$aFilesInfo[$i]['x'] = $iTotalWidth;
					$iTotalWidth         += ( $aFilesInfo[$i]['width'] + $this->aFormValues['horizontal-offset'] );
					// get the y position of current image in overall sprite
					if ( $iRowCount == 1 )
					{
						$aFilesInfo[$i]['y'] = 0;
					}
					else
					{
						$aFilesInfo[$i]['y'] = (
							$this->aFormValues['vertical-offset'] * ( $iRowCount - 1 ) + ( array_sum( $aMaxRowHeight ) - $aMaxRowHeight[$iRowCount] )
						);
					}
					$aFilesInfo[$i]['currentCombinedWidth'] = $iTotalWidth;
					$aFilesInfo[$i]['rowNumber']            = $iRowCount;
				}
				else
				{
					if (
						// opera (9.0 and below) has a bug which prevents it recognising  offsets of less than -2042px
						// all subsequent values are treated as -2042px
						// if we've hit 2000 pixels and we care about this (as set in the interface) then wrap to a new column
						// increment column count and reset current height
						( $iTotalHeight + $this->aFormValues['vertical-offset'] ) >= 2000 && ! empty( $this->aFormValues['wrap-columns'] )
					)
					{
						$iColumnCount ++;
						$iTotalHeight = 0;
					}

					// if the current image is wider than any other in the current column then set the maximum width to that
					// it will be used to set the width of the current column
					if ( $aFilesInfo[$i]['width'] > $iMaxWidth )
					{
						$iMaxWidth = $aFilesInfo[$i]['width'];
					}

					// keep track of the width of columns added so far
					$aMaxColumnWidth[$iColumnCount] = $iMaxWidth;
					// calculate the current maximum horizontal offset so far
					$iMaxHOffset = $this->aFormValues['horizontal-offset'] * ( $iColumnCount - 1 );

					// get the y position of current image in overall sprite
					$aFilesInfo[$i]['y'] = $iTotalHeight;
					$iTotalHeight        += ( $aFilesInfo[$i]['height'] + $this->aFormValues['vertical-offset'] );
					// get the x position of current image in overall sprite
					if ( $iColumnCount == 1 )
					{
						$aFilesInfo[$i]['x'] = 0;
					}
					else
					{
						$aFilesInfo[$i]['x'] = (
							$this->aFormValues['horizontal-offset'] * ( $iColumnCount - 1 ) + ( array_sum( $aMaxColumnWidth ) -
							                                                                    $aMaxColumnWidth[$iColumnCount] )
						);
					}
					$aFilesInfo[$i]['currentCombinedHeight'] = $iTotalHeight;
					$aFilesInfo[$i]['columnNumber']          = $iColumnCount;
				}

				$i ++;

				$aValidImages[] = $sFile;
			}
			else
			{
				$this->aBackground[$k] = null;
				$k ++;
			}

			if ( $i > 30 )
			{
				break;
			}
		}

		JCH_DEBUG ? Profiler::stop( 'CalculateSprite', true ) : null;

		if ( $this->bBackend )
		{
			return $aValidImages;
		}

		JCH_DEBUG ? Profiler::start( 'CreateSprite' ) : null;


		/*                 * **************************************** */
		/* this section generates the sprite image */
		/* and CSS rules                           */
		/*                 * **************************************** */
		// if $i is greater than 1 then we managed to generate enough info to create a sprite
		if ( count( $aFilesInfo ) > 1 )
		{

			// if Imagick throws an exception we want the script to terminate cleanly so that
			// temporary files are cleaned up
			try
			{
				// get the sprite width and height
				if ( $this->aFormValues['build-direction'] == 'horizontal' )
				{
					$iSpriteWidth  = $iMaxWidth - $this->aFormValues['horizontal-offset'];
					$iSpriteHeight = array_sum( $aMaxRowHeight ) + $iMaxVOffset;
				}
				else
				{
					$iSpriteHeight = $iMaxHeight - $this->aFormValues['vertical-offset'];
					$iSpriteWidth  = array_sum( $aMaxColumnWidth ) + $iMaxHOffset;
				}

				// get background colour - remove # if added
				$sBgColour = str_replace( '#', '', $this->aFormValues['background'] );
				// convert 3 digit hex values to 6 digit equivalent
				if ( strlen( $sBgColour ) == 3 )
				{
					$sBgColour = substr( $sBgColour, 0, 1 ) .
					             substr( $sBgColour, 0, 1 ) .
					             substr( $sBgColour, 1, 1 ) .
					             substr( $sBgColour, 1, 1 ) .
					             substr( $sBgColour, 2, 1 ) .
					             substr( $sBgColour, 2, 1 );
				}
				// should the image be transparent
				$this->bTransparent = (
					! empty( $this->aFormValues['use-transparency'] ) && in_array( $this->aFormValues['image-output'],
						array( 'GIF', 'PNG' ) )
				);

				$oSprite = $this->oImageHandler->createSprite( $iSpriteWidth, $iSpriteHeight, $sBgColour, $sOutputFormat );

				// loop through file info for valid images
				for ( $i = 0; $i < count( $aFilesInfo ); $i ++ )
				{
					// create a new image object for current file
					if ( ! $oCurrentImage = $this->oImageHandler->createImage( $aFilesInfo[$i] ) )
					{
						// if we've got here then a valid but corrupt image was found
						// at this stage we've already allocated space for the image so create
						// a blank one to fill the space instead
						// this should happen very rarely
						$oCurrentImage = $this->oImageHandler->createBlankImage( $aFilesInfo[$i] );
					}

					// if resizing get image width and height and resample to new dimensions (percentage of original)
					// and copy to sprite image
					if ( $bResize )
					{
						$this->oImageHandler->resizeImage( $oSprite, $oCurrentImage, $aFilesInfo[$i] );
					}

					// copy image to sprite
					$this->oImageHandler->copyImageToSprite( $oSprite, $oCurrentImage, $aFilesInfo[$i], $bResize );

					// get CSS x & y values
					$iX                  = $aFilesInfo[$i]['x'] != 0 ? '-' . $aFilesInfo[$i]['x'] . 'px' : '0';
					$iY                  = $aFilesInfo[$i]['y'] != 0 ? '-' . $aFilesInfo[$i]['y'] . 'px' : '0';
					$this->aPosition[$i] = $iX . ' ' . $iY;
					// create CSS rules and append to overall CSS rules
//                                        $this->sCss .= "{$this->aFormValues['selector-prefix']}{$aFilesInfo[$i]['class']} "
//                                                . "{$this->aFormValues['selector-suffix']}{ background-position: $iX $iY; ";
//
//                                        // If add widths and heights the sprite image width and height are added to the CSS
//                                        if ($this->aFormValues['add-width-height-to-css'] == 'on')
//                                        {
//                                                $this->sCss .= "width: {$aFilesInfo[$i]['width']}px; height: {$aFilesInfo[$i]['height']}px;";
//                                        }
//
//                                        $this->sCss .= " } \n";
					// destroy object created for current image to save memory
					$this->oImageHandler->destroy( $oCurrentImage );
				}


				$path = $this->aFormValues['sprite-path'];
				//See if image already exists
				//
				// create a unqiue filename for sprite image
				$sSpriteMD5            = md5( implode( $aFilesMD5 ) . implode( $this->aFormValues ) );
				$this->sTempSpriteName = $path . DIRECTORY_SEPARATOR . 'csg-' . $sSpriteMD5 . ".$sOutputFormat";

				if ( ! file_exists( $path ) )
				{
					FileSystem::createFolder( $path );
				}

				// write image to file
				if ( ! file_exists( $this->sTempSpriteName ) )
				{
					$this->oImageHandler->writeImage( $oSprite, $sOutputFormat, $this->sTempSpriteName );

					$optimize = true;
				}


				// destroy object created for sprite image to save memory
				$this->oImageHandler->destroy( $oSprite );

				// set flag to indicate valid images created
				$this->bValidImages = true;
			}
			catch ( \Exception $e )
			{
				Logger::log( $e->getMessage(), $this->params );
			}

			JCH_DEBUG ? Profiler::stop( 'CreateSprite', true ) : null;
		}
	}

	protected function FormatClassName( $sClassName )
	{
		$aExtensions = array();

		foreach ( $this->aImageTypes as $sType )
		{
			$aExtensions[] = ".$sType";
		}

		return preg_replace( "/[^a-z0-9_-]+/i", '', str_ireplace( $aExtensions, '', $sClassName ) );
	}

	public function ValidImages()
	{
		return $this->bValidImages;
	}

	public function GetSpriteFilename()
	{
		$aFileParts = pathinfo( $this->sTempSpriteName );

		return $aFileParts['basename'];
	}

	public function GetSpriteHash()
	{
		//return md5($this->GetSpriteFilename().ConfigHelper::Get('/checksum'));
	}

	public function GetCss()
	{
		return $this->aCss;
	}

	public function GetAllErrors()
	{
		return $this->aFormErrors;
	}

	public function GetZipFolder()
	{
		return $this->sZipFolder;
	}

	public function GetCssBackground()
	{
		$aCssBackground = array();

		foreach ( $this->aBackground as $background )
		{
			//if(!empty($background))
			//{
			$aCssBackground[] = @$this->aPosition[$background];
			//}
		}

		return $aCssBackground;
	}

}





