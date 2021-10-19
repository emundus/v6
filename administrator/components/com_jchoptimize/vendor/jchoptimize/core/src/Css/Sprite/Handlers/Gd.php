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

namespace JchOptimize\Core\Css\Sprite\Handlers;

defined( '_JCH_EXEC' ) or die( 'Restricted access' );


class Gd implements HandlerInterface
{
	protected $params;

	protected $obj;

	public $aSpriteFormats = array();


	public function __construct($params, $obj)
	{
		$this->obj    = $obj;
		$this->params = $params;
	}

	public function getSupportedFormats()
	{
		// get info about installed GD library to get image types (some versions of GD don't include GIF support)
		$oGD         = gd_info();
		$aImageTypes = array();
		// store supported formats for populating drop downs etc later
		if (isset($oGD['PNG Support']))
		{
			$aImageTypes[] = 'PNG';

			$this->aSpriteFormats[] = 'PNG';
		}

		if (isset($oGD['GIF Create Support']))
		{
			$aImageTypes[] = 'GIF';
		}

		if (isset($oGD['JPG Support']) || isset($oGD['JPEG Support']))
		{
			$aImageTypes[] = 'JPG';
		}

		return $aImageTypes;
	}

	public function createSprite($iSpriteWidth, $iSpriteHeight, $sBgColour, $sOutputFormat)
	{
		if ($this->obj->bTransparent && !empty($this->obj->aFormValues['background']))
		{
			$oSprite = imagecreate($iSpriteWidth, $iSpriteHeight);
		}
		else
		{
			$oSprite = imagecreatetruecolor($iSpriteWidth, $iSpriteHeight);
		}

		// check for transparency option
		if ($this->obj->bTransparent)
		{
			if ($sOutputFormat == "png")
			{
				imagealphablending($oSprite, false);
				$colorTransparent = imagecolorallocatealpha($oSprite, 0, 0, 0, 127);
				imagefill($oSprite, 0, 0, $colorTransparent);
				imagesavealpha($oSprite, true);
			}
			elseif ($sOutputFormat == "gif")
			{
				$iBgColour = imagecolorallocate($oSprite, 0, 0, 0);

				imagecolortransparent($oSprite, $iBgColour);
			}
		}
		else
		{
			if (empty($sBgColour))
			{
				$sBgColour = 'ffffff';
			}
			$iBgColour = hexdec($sBgColour);
			$iBgColour = imagecolorallocate(
				$oSprite, 0xFF & ($iBgColour >> 0x10), 0xFF & ($iBgColour >> 0x8), 0xFF & $iBgColour
			);
			imagefill($oSprite, 0, 0, $iBgColour);
		}

		return $oSprite;
	}

	public function createBlankImage($aFileInfo)
	{
		$oCurrentImage = imagecreatetruecolor($aFileInfo['original-width'], $aFileInfo['original-height']);
		imagecolorallocate($oCurrentImage, 255, 255, 255);

		return $oCurrentImage;
	}

	public function resizeImage($oSprite, $oCurrentImage, $aFileInfo)
	{
		imagecopyresampled(
			$oSprite, $oCurrentImage, $aFileInfo['x'], $aFileInfo['y'], 0, 0, $aFileInfo['width'], $aFileInfo['height'],
			$aFileInfo['original-width'], $aFileInfo['original-height']
		);
	}

	public function copyImageToSprite($oSprite, $oCurrentImage, $aFileInfo, $bResize)
	{
		// if already resized the image will have been copied as part of the resize
		if (!$bResize)
		{
			imagecopy(
				$oSprite, $oCurrentImage, $aFileInfo['x'], $aFileInfo['y'], 0, 0, $aFileInfo['width'], $aFileInfo['height']
			);
		}
	}

	public function destroy($oImage)
	{
		imagedestroy($oImage);
	}

	public function createImage($aFileInfo)
	{
		$sFile = $aFileInfo['path'];

		switch ($aFileInfo['ext'])
		{
			case 'jpg':
			case 'jpeg':
				$oImage = @imagecreatefromjpeg($sFile);
				break;
			case 'gif':
				$oImage = @imagecreatefromgif($sFile);
				break;
			case 'png':
				$oImage = @imagecreatefrompng($sFile);
				break;
			default:
				$oImage = @imagecreatefromstring($sFile);
		}

		return $oImage;
	}

	public function writeImage($oImage, $sExtension, $sFilename)
	{
		// check if we want to resample image to lower number of colours (to reduce file size)
		if (in_array($sExtension, array('gif', 'png')) && $this->obj->aFormValues['image-num-colours'] != 'true-colour')
		{
			imagetruecolortopalette($oImage, true, $this->obj->aFormValues['image-num-colours']);
		}

		switch ($sExtension)
		{
			case 'jpg':
			case 'jpeg':
				// GD takes quality setting in main creation function
				imagejpeg($oImage, $sFilename, $this->obj->aFormValues['image-quality']);
				break;
			case 'gif':
				// force colour palette to 256 colours if saving sprite image as GIF
				// this will happen anyway (as GIFs can't be more than 256 colours)
				// but the quality will be better if pre-forcing
				if (
					$this->obj->bTransparent && (
						$this->obj->aFormValues['image-num-colours'] == -1 || $this->obj->aFormValues['image-num-colours'] > 256 || $this->obj->aFormValues['image-num-colours'] == 'true-colour' )
				)
				{
					imagetruecolortopalette($oImage, true, 256);
				}
				imagegif($oImage, $sFilename);
				break;
			case 'png':
				imagepng($oImage, $sFilename, 0);
				break;
		}
	}
}

