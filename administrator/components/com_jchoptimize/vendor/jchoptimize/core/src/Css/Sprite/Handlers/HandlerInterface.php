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


interface HandlerInterface
{

	public function getSupportedFormats();

	public function createSprite($iSpriteWidth, $iSpriteHeight, $sBgColour, $sOutputFormat);

	public function createBlankImage($aFileInfo);

	public function resizeImage($oSprite, $oCurrentImage, $aFileInfo);

	public function copyImageToSprite($oSprite, $oCurrentImage, $aFileInfo, $bResize);

	public function destroy($oImage);

	public function createImage($aFileInfo);

	public function writeImage($oImage, $sExtension, $sFilename);
}

