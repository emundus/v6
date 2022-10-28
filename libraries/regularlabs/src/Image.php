<?php
/**
 * @package         Regular Labs Library
 * @version         22.4.18687
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://regularlabs.com
 * @copyright       Copyright © 2022 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace RegularLabs\Library;

defined('_JEXEC') or die;

use Exception;
use Joomla\CMS\Filesystem\Folder as JFolder;
use Joomla\CMS\Uri\Uri as JUri;
use Joomla\Image\Image as JImage;

class Image
{
	public static function getHeight($source)
	{
		$dimensions = self::getDimensions($source);

		return $dimensions->height;
	}

	public static function getDimensions($source)
	{
		$empty = (object) [
			'width'  => 0,
			'height' => 0,
		];

		$image = self::getImageObject(JPATH_SITE . '/' . $source);

		if (is_null($image))
		{
			return $empty;
		}

		return (object) [
			'width'  => $image->getWidth(),
			'height' => $image->getHeight(),
		];
	}

	public static function getImageObject($source)
	{
		if (File::isExternal($source))
		{
			return null;
		}

		if ( ! file_exists($source))
		{
			return null;
		}

		if ( ! getimagesize($source))
		{
			return null;
		}

		try
		{
			$image = new JImage($source);
		}
		catch (Exception $e)
		{
			return null;
		}

		if ( ! ($image instanceof JImage))
		{
			return null;
		}

		if ( ! $image->isLoaded())
		{
			return null;
		}

		return $image;
	}

	public static function getUrls($source, $width, $height, $folder = 'resized', $resize = true, $quality = 'medium', $possible_suffix = '')
	{
		$image = self::isResized($source, $folder, $possible_suffix);

		if ($image)
		{
			$source = $image;
		}

		$original = $source;
		$resized  = self::getResize($source, $width, $height, $folder, $resize, $quality);

		return (object) compact('original', 'resized');
	}

	public static function isResized($file, $folder = 'resized', $possible_suffix = '')
	{
		if (File::isExternal($file))
		{
			return false;
		}

		if ( ! file_exists($file))
		{
			return false;
		}

		$main_image = self::isResizedWithFolder($file, $folder);

		if ($main_image)
		{
			return $main_image;
		}

		if ( ! $possible_suffix)
		{
			return false;
		}

		return (bool) self::isResizedWithSuffix($file, $possible_suffix);
	}

	public static function getResize($source, $width, $height, $folder = 'resized', $resize = true, $quality = 'medium')
	{
		$destination_folder = File::getDirName($source) . '/' . $folder;

		$override = File::getDirName($source) . '/' . $folder . '/' . File::getBaseName($source);

		if (file_exists(JPATH_SITE . '/' . $override))
		{
			$source = $override;
		}

		if ( ! self::setNewDimensions($source, $width, $height))
		{
			return $source;
		}

		if ( ! $width && ! $height)
		{
			return $source;
		}

		$destination = self::getNewPath(
			$source,
			$width,
			$height,
			$destination_folder
		);

		if ( ! file_exists(JPATH_SITE . '/' . $destination) && $resize)
		{
			// Create new resized image
			$destination = self::resize(
				$source,
				$width,
				$height,
				$destination_folder,
				$quality
			);
		}

		if ( ! file_exists(JPATH_SITE . '/' . $destination))
		{
			return $source;
		}

		return $destination;
	}

	private static function isResizedWithFolder($file, $resize_folder = 'resized')
	{
		$folder             = File::getDirName($file);
		$file               = File::getBaseName($file);
		$parent_folder_name = File::getBaseName($folder);
		$parent_folder      = File::getDirName($folder);

		// Image is not inside the resize folder
		if ($parent_folder_name != $resize_folder)
		{
			return false;
		}

		// Check if image with same name exists in parent folder
		if (file_exists(JPATH_SITE . '/' . $parent_folder . '/' . utf8_decode($file)))
		{
			return $parent_folder . '/' . $file;
		}

		// Remove any dimensions from the file
		// image_300x200.jpg => image.jpg
		$file = RegEx::replace(
			'_[0-9]+x[0-9]*(\.[^.]+)$',
			'\1',
			$file
		);

		// Check again if image with same name (but without dimensions) exists in parent folder
		if (file_exists(JPATH_SITE . '/' . $parent_folder . '/' . utf8_decode($file)))
		{
			return $parent_folder . '/' . $file;
		}

		return false;
	}

	public static function isResizedWithSuffix($file, $suffix = '_t')
	{
		// Remove the suffix from the file
		// image_t.jpg => image.jpg
		$main_file = RegEx::replace(
			RegEx::quote($suffix) . '(\.[^.]+)$',
			'\1',
			$file
		);

		// Nothing removed, so not a resized image
		if ($main_file == $file)
		{
			return false;
		}

		if ( ! file_exists(JPATH_SITE . '/' . utf8_decode($main_file)))
		{
			return false;
		}

		return $main_file;
	}

	public static function setNewDimensions($source, &$width, &$height)
	{
		if ( ! $width && ! $height)
		{
			return false;
		}

		if (File::isExternal($source))
		{
			return false;
		}

		$clean_source = self::cleanPath($source);
		$source_path  = JPATH_SITE . '/' . $clean_source;

		$image = self::getImageObject($source_path);

		if (is_null($image))
		{
			return false;
		}

		return self::setNewDimensionsByImageObject($image, $width, $height);
	}

	public static function getNewPath($source, $width, $height, $destination_folder = '')
	{
		$clean_source = self::cleanPath($source);

		$source_parts = pathinfo($clean_source);

		$destination_folder = ltrim($destination_folder ?: File::getDirName($clean_source));
		$destination_file   = File::getFileName($clean_source) . '_' . $width . 'x' . $height . '.' . $source_parts['extension'];

		JFolder::create(JPATH_SITE . '/' . $destination_folder);

		return ltrim($destination_folder . '/' . $destination_file);
	}

	public static function resize($source, &$width, &$height, $destination_folder = '', $quality = 'medium', $overwrite = false)
	{
		if (File::isExternal($source))
		{
			return $source;
		}

		$clean_source = self::cleanPath($source);
		$source_path  = JPATH_SITE . '/' . $clean_source;

		$destination_folder = ltrim($destination_folder ?: File::getDirName($clean_source));
		$destination_folder = self::cleanPath($destination_folder);

		$image = self::getImageObject($source_path);

		if (is_null($image))
		{
			return $source;
		}

		if ( ! self::setNewDimensionsByImageObject($image, $width, $height))
		{
			return $source;
		}

		if ( ! $width && ! $height)
		{
			return $source;
		}

		$destination      = self::getNewPath($source, $width, $height, $destination_folder);
		$destination_path = JPATH_SITE . '/' . $destination;

		if (file_exists($destination_path) && ! $overwrite)
		{
			return $destination;
		}

		JFolder::create(JPATH_SITE . '/' . $destination_folder);

		try
		{
			$info = JImage::getImageFileProperties($source_path);

			$options = ['quality' => self::getQuality($info->type, $quality)];

			$image->cropResize($width, $height, false)
				->toFile($destination_path, $info->type, $options);

			return $destination;
		}
		catch (Exception $e)
		{
			return $source;
		}
	}

	public static function cleanPath($source)
	{
		$source = ltrim(str_replace(JUri::root(), '', $source), '/');
		$source = strtok($source, '?');

		return $source;
	}

	public static function setNewDimensionsByImageObject($image, &$width, &$height)
	{
		if ( ! ($image instanceof JImage) || ! $image->isLoaded())
		{
			return false;
		}

		if ( ! $width && ! $height)
		{
			return false;
		}

		try
		{
			$original_width  = $image->getWidth();
			$original_height = $image->getHeight();

			$width  = $width ?: round($original_width / $original_height * $height);
			$height = $height ?: round($original_height / $original_width * $width);
		}
		catch (Exception $e)
		{
			return false;
		}

		if ($width == $original_width && $height == $original_height)
		{
			return false;
		}

		return true;
	}

	public static function getQuality($type, $quality = 'medium')
	{
		switch ($type)
		{
			case IMAGETYPE_JPEG:
				return min(max(self::getJpgQuality($quality), 0), 100);

			case IMAGETYPE_PNG:
				return 9;

			default:
				return '';
		}
	}

	public static function getJpgQuality($quality = 'medium')
	{
		switch ($quality)
		{
			case 'low':
				return 50;

			case 'high':
				return 90;

			case 'medium':
			default:
				return 70;
		}
	}

	public static function getWidth($source)
	{
		$dimensions = self::getDimensions($source);

		return $dimensions->width;
	}
}
