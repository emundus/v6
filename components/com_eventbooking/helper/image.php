<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2021 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

class EventbookingHelperImage
{
	public static function resize($sourceImage, $destinationImage, $width = 0, $height = 0, $proportional = false, $deleteOriginal = true)
	{
		if ($height <= 0 && $width <= 0)
		{
			return false;
		}
		# Setting defaults and meta
		$info        = getimagesize($sourceImage);
		$image       = '';
		$finalWidth  = 0;
		$finalHeight = 0;
		list($widthOld, $heightOld) = $info;

		# Calculating proportionality
		if ($proportional)
		{
			if ($width == 0)
			{
				$factor = $height / $heightOld;
			}
			elseif ($height == 0)
			{
				$factor = $width / $widthOld;
			}
			else
			{
				$factor = min($width / $widthOld, $height / $heightOld);
			}
			$finalWidth  = round($widthOld * $factor);
			$finalHeight = round($heightOld * $factor);
		}
		else
		{
			$finalWidth  = ($width <= 0) ? $widthOld : $width;
			$finalHeight = ($height <= 0) ? $heightOld : $height;
		}

		# Loading image to memory according to type
		switch ($info[2])
		{
			case IMAGETYPE_GIF:
				$image = imagecreatefromgif($sourceImage);
				break;
			case IMAGETYPE_JPEG:
				$image = imagecreatefromjpeg($sourceImage);
				break;
			case IMAGETYPE_PNG:
				$image = imagecreatefrompng($sourceImage);
				break;
			default:
				return false;
		}

		# This is the resizing/resampling/transparency-preserving magic
		$imageResized = imagecreatetruecolor($finalWidth, $finalHeight);
		if (($info[2] == IMAGETYPE_GIF) || ($info[2] == IMAGETYPE_PNG))
		{
			$trnprt_indx = imagecolortransparent($image);

			if ($trnprt_indx >= 0)
			{
				$trnprt_color = imagecolorsforindex($image, $trnprt_indx);
				$transparency = imagecolorallocate($imageResized, $trnprt_color['red'], $trnprt_color['green'], $trnprt_color['blue']);
				imagefill($imageResized, 0, 0, $transparency);
				imagecolortransparent($imageResized, $transparency);
			}
			elseif ($info[2] == IMAGETYPE_PNG)
			{
				imagealphablending($imageResized, false);
				$color = imagecolorallocatealpha($imageResized, 0, 0, 0, 127);
				imagefill($imageResized, 0, 0, $color);
				imagesavealpha($imageResized, true);
			}
		}
		imagecopyresampled($imageResized, $image, 0, 0, 0, 0, $finalWidth, $finalHeight, $widthOld, $heightOld);
		# Taking care of original, if needed
		if ($deleteOriginal)
		{
			@unlink($sourceImage);
		}
		# Writing image according to type to the output destination
		switch ($info[2])
		{
			case IMAGETYPE_GIF:
				imagegif($imageResized, $destinationImage);
				break;
			case IMAGETYPE_JPEG:
				imagejpeg($imageResized, $destinationImage);
				break;
			case IMAGETYPE_PNG:
				imagepng($imageResized, $destinationImage);
				break;
			default:
				return false;
		}

		return true;
	}
}