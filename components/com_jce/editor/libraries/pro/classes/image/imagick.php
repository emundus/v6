<?php
/**
 * @copyright    Copyright (c) 2009-2021 Ryan Demmer. All rights reserved
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved
 * @license    GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 *
 * Based on JImage library from Joomla.Platform 11.3
 */
defined('JPATH_PLATFORM') or die;

/**
 * Class to manipulate an image.
 */
class WFImageImagick
{
    /**
     * @var resource The image resource handle
     */
    protected $handle;

    /**
     * @var string The source image string or path
     */
    protected $source = null;

    /**
     * @var string File type, eg: jpg, gif, png
     */
    protected static $type;

    /**
     * Class constructor.
     *
     * @param mixed $source Either a file path for a source image or a GD resource handler for an image
     *
     * @throws RuntimeException
     */
    public function __construct($source = null)
    {
        // Verify that GD support for PHP is available.
        if (!extension_loaded('imagick')) {
            throw new RuntimeException('The Imagick extension for PHP is not available.');
        }

        // If the source input is a resource, set it as the image handle.
        if ($this->isValidResource($source)) {
            $this->handle = &$source;
        } elseif (!empty($source) && is_string($source)) {
            // If the source input is not empty, assume it is a path and populate the image handle.
            $this->loadFile($source);
        }
    }

    private function isValidResource($resource)
    {
        if (!is_object($resource) || ($resource instanceof Imagick === false)) {
            return false;
        }

        return true;
    }

    /**
     * Method to get the height of the image in pixels.
     *
     * @return int
     *
     * @throws LogicException
     */
    public function getHeight()
    {
        // Make sure the resource handle is valid.
        if (!$this->isLoaded()) {
            throw new LogicException('No valid image was loaded.');
        }

        return $this->handle->getImageHeight();
    }

    /**
     * Method to get the width of the image in pixels.
     *
     * @return int
     *
     * @throws LogicException
     */
    public function getWidth()
    {
        // Make sure the resource handle is valid.
        if (!$this->isLoaded()) {
            throw new LogicException('No valid image was loaded.');
        }

        return $this->handle->getImageWidth();
    }

    /**
     * Method to return the source path or string.
     *
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Method to determine whether or not an image has been loaded into the object.
     *
     * @return bool
     *
     * @since   11.3
     */
    public function isLoaded()
    {
        // Make sure the resource handle is valid.
        return $this->isValidResource($this->handle);
    }

    /**
     * Method to load a file into the JImage object as the resource.
     *
     * @param string $path The filesystem path to load as an image
     *
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function loadFile($path)
    {
        // Make sure the file exists.
        if (!file_exists($path)) {
            throw new InvalidArgumentException('The image file does not exist.');
        }

        $this->handle = new Imagick($path);

        // Set the filesystem path to the source image.
        $this->source = $path;

        // set type
        $this->setType(exif_imagetype($path));
    }

    /**
     * Method to load a file into the JImage object as the resource.
     *
     * @param string $path The filesystem path to load as an image
     *
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function loadString($string)
    {
        $this->handle = new Imagick();

        if ($this->isLoaded()) {
            $this->handle->readImageBlob($string);

            $this->source = $string;
        } else {
            $this->destroy();
            throw new RuntimeException('Attempting to load an image of unsupported type.');
        }
    }

    private function watermarkText($options)
    {
        if (is_file($options->font_style)) {
            $watermark = new ImagickDraw();
            $watermark->setFontSize((int) $options->font_size);

            $options->font_color = '#' . preg_replace('#[^a-z0-9]+#i', '', $options->font_color);

            if ($options->opacity > 1) {
                $options->opacity = $options->opacity / 100;
            }

            switch ($options->position) {
                default:
                case 'center':
                    $watermark->setGravity(Imagick::GRAVITY_CENTER);
                    break;
                case 'top-left':
                    $watermark->setGravity(Imagick::GRAVITY_NORTHEAST);

                    break;
                case 'top-right':
                    $watermark->setGravity(Imagick::GRAVITY_NORTHEAST);

                    break;
                case 'center-left':
                    $watermark->setGravity(Imagick::GRAVITY_WEST);

                    break;
                case 'center-right':
                    $watermark->setGravity(Imagick::GRAVITY_EAST);

                    break;
                case 'top-center':
                    $watermark->setGravity(Imagick::GRAVITY_NORTH);

                    break;
                case 'bottom-center':
                    $watermark->setGravity(Imagick::GRAVITY_SOUTH);
                    break;
                case 'bottom-left':
                    $watermark->setGravity(Imagick::GRAVITY_SOUTHWEST);

                    break;
                case 'bottom-right':
                    $watermark->setGravity(Imagick::GRAVITY_SOUTHEAST);

                    break;
            }

            $watermark->setFillColor($options->font_color);
            $watermark->setFillOpacity((float) $options->opacity);
            $watermark->setFont($options->font_style);

            $this->handle->annotateImage($watermark, (int) $options->margin, (int) $options->margin, 0, $options->text);
        }
    }

    public function watermark($options)
    {
        // Make sure the resource handle is valid.
        if (!$this->isLoaded()) {
            throw new LogicException('No valid image was loaded.');
        }

        if ($options->opacity > 1) {
            $options->opacity = $options->opacity / 100;
        }

        if ($options->type == 'text' && isset($options->text)) {
            $this->watermarkText($options);
        } elseif (isset($options->image)) {
            $watermark = new Imagick($options->image);

            $width = $this->getWidth();
            $height = $this->getHeight();

            $mw = $watermark->getImageWidth();
            $mh = $watermark->getImageHeight();

            switch ($options->position) {
                default:
                case 'center':
                    $x = floor(($width - $mw) / 2);
                    $y = floor(($height - $mh) / 2);
                    break;
                case 'top-left':
                    $x = $options->margin;
                    $y = floor($mh / 2) + $options->margin;
                    break;
                case 'top-right':
                    $x = $width - $mw - $options->margin;
                    $y = floor($mh / 2) + $options->margin;
                    break;
                case 'center-left':
                    $x = 0 + $options->margin;
                    $y = floor(($height - $mh) / 2);

                    break;
                case 'center-right':
                    $x = $width - $mw - $options->margin;
                    $y = floor(($height - $mh) / 2);

                    break;
                case 'top-center':
                    $x = floor(($width - $mw) / 2);
                    $y = floor($mh / 2) + $options->margin;
                    break;
                case 'bottom-center':
                    $x = floor(($width - $mw) / 2);
                    $y = $height - $mh - $options->margin;
                    break;
                case 'bottom-left':
                    $x = 0 + $options->margin;
                    $y = $height - $mh - $options->margin;
                    break;
                case 'bottom-right':
                    $x = $width - $mw - $options->margin;
                    $y = $height - $mh - $options->margin;
                    break;
            }

            if ($watermark->getImageFormat() == 'PNG') {
                $watermark->evaluateImage(Imagick::EVALUATE_MULTIPLY, (float) $options->opacity, Imagick::CHANNEL_ALPHA);
            } else {
                $watermark->setImageOpacity((float) $options->opacity);
            }
            $this->handle->compositeImage($watermark, imagick::COMPOSITE_OVER, $x, $y);
        }

        return $this;
    }

    /**
     * Set image resolution.
     *
     * @param type $resolution
     *
     * @return \WFImageImagick
     */
    public function resample($resolution)
    {
        // Make sure the resource handle is valid.
        if (!$this->isLoaded()) {
            throw new LogicException('No valid image was loaded.');
        }

        return $this->handle->setImageResolution((float) $resolution, (float) $resolution);
    }

    /**
     * Method to resize the current image.
     *
     * @param mixed $width       The width of the resized image in pixels or a percentage
     * @param mixed $height      The height of the resized image in pixels or a percentage
     * @param bool  $createNew   If true the current image will be cloned, resized and returned; else
     *                           the current image will be resized and returned
     * @param int   $scaleMethod Which method to use for scaling
     *
     * @return WFImage
     *
     * @throws LogicException
     */
    public function resize($width, $height, $createNew = false)
    {
        // Make sure the resource handle is valid.
        if (!$this->isLoaded()) {
            throw new LogicException('No valid image was loaded.');
        }

        // If we are resizing to a new image, create a new Imagick object.
        if ($createNew) {
            $this->handle = clone $this->handle;
        }

        return $this->handle->resizeImage($width, $height, imagick::FILTER_LANCZOS, 1);
    }

    /**
     * Method to crop the current image.
     *
     * @param mixed $width     The width of the image section to crop in pixels or a percentage
     * @param mixed $height    The height of the image section to crop in pixels or a percentage
     * @param int   $left      The number of pixels from the left to start cropping
     * @param int   $top       The number of pixels from the top to start cropping
     * @param bool  $createNew If true the current image will be cloned, cropped and returned; else
     *                         the current image will be cropped and returned
     *
     * @return WFImage
     *
     * @throws LogicException
     */
    public function crop($width, $height, $left, $top, $createNew = false)
    {
        // Make sure the resource handle is valid.
        if (!$this->isLoaded()) {
            throw new LogicException('No valid image was loaded.');
        }

        // If we are cropping to a new image, create a new JImage object.
        if ($createNew) {
            $this->handle = clone $this->handle;
            // @codeCoverageIgnoreEnd
        }

        // Create the new truecolor image handle.
        $this->handle->cropImage($width, $height, $left, $top);
    }

    /**
     * Method to rotate the current image.
     *
     * @param mixed $angle      The angle of rotation for the image
     * @param int   $background The background color to use when areas are added due to rotation
     * @param bool  $createNew  If true the current image will be cloned, rotated and returned; else
     *                          the current image will be rotated and returned
     *
     * @return WFImage
     *
     * @throws LogicException
     */
    public function rotate($angle, $createNew = false)
    {
        // Make sure the resource handle is valid.
        if (!$this->isLoaded()) {
            throw new LogicException('No valid image was loaded.');
        }

        if ($createNew) {
            $this->handle = clone $this->handle;
        }

        $this->handle->rotateImage(new ImagickPixel(), $angle);
    }

    /**
     * Method to rotate the current image.
     *
     * @param mixed $angle      The angle of rotation for the image
     * @param int   $background The background color to use when areas are added due to rotation
     * @param bool  $createNew  If true the current image will be cloned, rotated and returned; else
     *                          the current image will be rotated and returned
     *
     * @return WFImage
     *
     * @throws LogicException
     */
    public function flip($mode, $createNew = false)
    {
        // Make sure the resource handle is valid.
        if (!$this->isLoaded()) {
            throw new LogicException('No valid image was loaded.');
        }

        if ($createNew) {
            $this->handle = $this->handle->clone();
        }

        switch ((int) $mode) {

            case IMAGE_FLIP_HORIZONTAL:
                $this->handle->flopImage();
                break;

            case IMAGE_FLIP_VERTICAL:
                $this->handle->flipImage();
                break;

            case IMAGE_FLIP_BOTH:
                $this->handle->flipImage();
                $this->handle->flopImage();
                break;
        }
    }

    public function orientate()
    {
        $rotate = 0;
        $orientation = $this->handle->getImageOrientation();

        // Fix Orientation
        switch ($orientation) {
            case imagick::ORIENTATION_BOTTOMRIGHT:
                $rotate = 180;
                break;
            case imagick::ORIENTATION_RIGHTTOP:
                $rotate = 90;
                break;
            case imagick::ORIENTATION_LEFTBOTTOM:
                $rotate = 270;
                break;
        }

        if ($rotate) {
            if ($this->handle->rotateImage(new ImagickPixel(), $rotate)) {
                $this->handle = clone $this->handle;

                return true;
            }
        }

        return false;
    }

    public function removeExif()
    {
        $this->handle->stripImage();
    }

    /**
     * Method to write the current image out to a file.
     *
     * @param string $path    The filesystem path to save the image
     * @param int    $type    The image type to save the file as
     * @param array  $options The image type options to use in saving the file
     *
     * @throws LogicException
     */
    public function toFile($path, $type = 'jpeg', array $options = array())
    {
        // Make sure the resource handle is valid.
        if (!$this->isLoaded()) {
            throw new LogicException('No valid image was loaded.');
        }

        // resample
        if (!empty($options['resampleImage'])) {
            $this->handle->setImageResolution(72, 72);
        }

        // set format
        $this->handle->setImageFormat($type);

        // remove exif data
        if (!empty($options['removeExif'])) {
            $this->removeExif();
        }

        switch ($type) {
            case 'png':
                $this->handle->setImageCompression(imagick::COMPRESSION_ZIP);

                $quality = (array_key_exists('quality', $options)) ? $options['quality'] : 0;

                // get as value from 0-9
                if ($quality) {
                    // png compression is a value from 0 (none) to 9 (max)
                    $quality = 100 - $quality;
                    // convert to value between 0 - 9
                    $quality = min(floor($quality / 10), 9);
                }

                $this->handle->setImageCompressionQuality($quality);
                break;

            case 'jpeg':
            case 'jpg':
                $this->handle->setImageCompression(Imagick::COMPRESSION_JPEG);
                $this->handle->setImageCompressionQuality((array_key_exists('quality', $options)) ? $options['quality'] : 100);
        }
        $result = $this->handle->writeImage($path);
        $this->destroy();

        return $result;
    }

    /**
     * Method to write the current image out to a file.
     *
     * @param string $path    The filesystem path to save the image
     * @param int    $type    The image type to save the file as
     * @param array  $options The image type options to use in saving the file
     *
     * @throws LogicException
     */
    public function toString($type = 'jpeg', array $options = array())
    {
        // Make sure the resource handle is valid.
        if (!$this->isLoaded()) {
            throw new LogicException('No valid image was loaded');
        }

        // resample
        if (!empty($options['resampleImage'])) {
            $this->handle->setImageResolution(72, 72);
        }

        // set format
        $this->handle->setImageFormat($type);
        $this->handle->setFormat($type);

        // remove exif data
        if (!empty($options['removeExif'])) {
            $this->removeExif();
        }

        // convert type to imagetype constant
        if (is_string($type)) {
            $type = WFImage::getImageType($type);
        }

        switch ($type) {
            case IMAGETYPE_PNG:
                $this->handle->setImageCompression(Imagick::COMPRESSION_ZIP);
                $this->handle->setCompression(Imagick::COMPRESSION_ZIP);

                $quality = (array_key_exists('quality', $options)) ? $options['quality'] : 0;

                // get as value from 0-9
                if ($quality) {
                    // png compression is a value from 0 (none) to 9 (max)
                    $quality = 100 - $quality;
                    // convert to value between 0 - 9
                    $quality = min(floor($quality / 10), 9);
                }

                $this->handle->setImageCompressionQuality($quality);
                break;

            case IMAGETYPE_WEBP:
                $this->handle->setImageCompressionQuality((array_key_exists('quality', $options)) ? $options['quality'] : 100);
                break;

            case IMAGETYPE_JPEG:
            default:
                $this->handle->setImageCompression(Imagick::COMPRESSION_JPEG);
                $this->handle->setImageCompressionQuality((array_key_exists('quality', $options)) ? $options['quality'] : 100);
                break;
        }

        echo $this->handle->getImageBlob();

        $this->destroy();
    }

    public function getType()
    {
        return self::$type;
    }

    public function setType($type)
    {
        self::$type = $type;
    }

    public function backup()
    {
        return clone $this->handle;
    }

    public function restore($resource)
    {
        if (!is_object($resource) || ($resource instanceof Imagick === false)) {
            throw new LogicException('Invalid image resource');
        }

        $this->handle->clear();
        $this->handle = clone $resource;
    }

    public function destroy()
    {
        $this->handle->clear();
        $this->handle->destroy();
    }
}
