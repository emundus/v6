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

use ImagickException;
use ImagickPixel;
use JchOptimize\Core\Logger;

class Imagick implements HandlerInterface
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
		$aImageTypes = array();

		try
		{
			$oImagick      = new \Imagick();
			$aImageFormats = $oImagick->queryFormats();
		}
		catch (ImagickException $e)
		{
			Logger::log($e->getMessage(), $this->params);
		}

		// store supported formats for populating drop downs etc later
		if (in_array('PNG', $aImageFormats))
		{
			$aImageTypes[] = 'PNG';

			$this->aSpriteFormats[] = 'PNG';
		}
		if (in_array('GIF', $aImageFormats))
		{
			$aImageTypes[] = 'GIF';

			$this->aSpriteFormats[] = 'GIF';
		}
		if (in_array('JPG', $aImageFormats) || in_array('JPEG', $aImageFormats))
		{
			$aImageTypes[] = 'JPG';
		}

		return $aImageTypes;
	}

	public function createSprite($iSpriteWidth, $iSpriteHeight, $sBgColour, $sOutputFormat)
	{
		$oSprite = new \Imagick();
		// create a new image - set background according to transparency
		if (!empty($this->obj->aFormValues['background']))
		{
			$oSprite->newImage($iSpriteWidth, $iSpriteHeight, new ImagickPixel("#$sBgColour"), $sOutputFormat);
		}
		else
		{
			if ($this->obj->bTransparent)
			{
				$oSprite->newImage($iSpriteWidth, $iSpriteHeight, new ImagickPixel('#000000'), $sOutputFormat);
			}
			else
			{
				$oSprite->newImage($iSpriteWidth, $iSpriteHeight, new ImagickPixel('#ffffff'), $sOutputFormat);
			}
		}

		// check for transparency option
		if ($this->obj->bTransparent)
		{
			// set background colour to transparent
			// if no background colour use black
			if (!empty($this->obj->aFormValues['background']))
			{
				$oSprite->transparentPaintImage(new ImagickPixel("#$sBgColour"), 0.0, 0, false);
			}
			else
			{
				$oSprite->transparentPaintImage(new ImagickPixel("#000000"), 0.0, 0, false);
			}
		}

		return $oSprite;
	}

	public function createBlankImage($aFileInfo)
	{
		$oCurrentImage = new \Imagick();

		$oCurrentImage->newImage(
			$aFileInfo['original-width'], $aFileInfo['original-height'], new ImagickPixel('#ffffff')
		);

		return $oCurrentImage;
	}

	/**
	 * @param            $oSprite
	 * @param   \Imagick  $oCurrentImage
	 * @param            $aFileInfo
	 *
	 *
	 * @since version
	 */
	public function resizeImage($oSprite, $oCurrentImage, $aFileInfo)
	{
		$oCurrentImage->thumbnailImage($aFileInfo['width'], $aFileInfo['height']);
	}

	/**
	 * @param   \Imagick  $oSprite
	 * @param   \Imagick  $oCurrentImage
	 * @param            $aFileInfo
	 * @param            $bResize
	 */
	public function copyImageToSprite($oSprite, $oCurrentImage, $aFileInfo, $bResize)
	{
		$oSprite->compositeImage(
			$oCurrentImage, $oCurrentImage->getImageCompose(), $aFileInfo['x'], $aFileInfo['y']
		);
	}

	/**
	 * @param   \Imagick  $oImage
	 *
	 *
	 * @since version
	 */
	public function destroy($oImage)
	{
		$oImage->destroy();
	}

	public function createImage($aFileInfo)
	{
		// Imagick auto detects file extension when creating object from image
		$oImage = new \Imagick();
		$oImage->readImage($aFileInfo['path']);

		return $oImage;
	}

	/**
	 * @param   \Imagick  $oImage
	 * @param   string   $sExtension
	 * @param   string   $sFilename
	 */
	public function writeImage($oImage, $sExtension, $sFilename)
	{

		// check if we want to resample image to lower number of colours (to reduce file size)
		if (in_array($sExtension, array('gif', 'png')) && $this->obj->aFormValues['image-num-colours'] != 'true-colour')
		{
			$oImage->quantizeImage($this->obj->aFormValues['image-num-colours'], \Imagick::COLORSPACE_RGB, 0, false, false);
		}
		// if we're creating a JEPG set image quality - 0% - 100%
		if (in_array($sExtension, array('jpg', 'jpeg')))
		{
			$oImage->setCompression(\Imagick::COMPRESSION_JPEG);
			$oImage->SetCompressionQuality($this->obj->aFormValues['image-quality']);
		}
		// write out image to file
		$oImage->writeImage($sFilename);
	}

}
