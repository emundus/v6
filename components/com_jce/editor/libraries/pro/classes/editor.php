<?php

/**
 * @copyright     Copyright (c) 2009-2021 Ryan Demmer. All rights reserved
 * @license       GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses
 */
defined('JPATH_PLATFORM') or die;

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

require_once __DIR__ . '/image/image.php';

class WFImageEditor extends JObject
{
    protected $prefer_imagick = true;

    protected $ftp = false;

    protected $edit = true;

    public function __construct($config = array())
    {
        // set properties
        $this->setProperties($config);
    }

    private function getOptions()
    {
        return array(
            'preferImagick' => $this->get('prefer_imagick', true),
            'removeExif' => $this->get('remove_exif', false),
            'resampleImage' => $this->get('resample_image', false),
        );
    }

    public function watermark($src, $settings)
    {
        $ext = strtolower(JFile::getExt($src));

        if (!empty($settings['image'])) {
            $settings['image'] = WFUtility::makePath(JPATH_SITE, $settings['image']);
        }

        if (!empty($settings['font_style'])) {
            $settings['font_style'] = WFUtility::makePath(JPATH_SITE, $settings['font_style']);
        }

        $options = $this->getOptions();

        $image = new WFImage($src, $options);

        if ($image->watermark($settings)) {
            if ($this->get('ftp', 0)) {
                @JFile::write($src, $image->toString($ext));
            } else {
                @$image->toFile($src);
            }
        }

        unset($image);

        return $src;
    }

    public function resize($src, $dest, $width, $height, $quality, $box = array())
    {
        $ext = strtolower(JFile::getExt($src));
        $data = @file_get_contents($src);

        if ($data) {
            $options = $this->getOptions();

            // resize original
            if (empty($dest)) {
                $dest = $src;
            }

            $image = new WFImage(null, $options);
            // set type
            $image->setType($ext);
            // load data
            $image->loadString($data);

            // cropped thumbnail
            if (!empty($box)) {
                $image->crop($box['sw'], $box['sh'], $box['sx'], $box['sy']);
            }

            // resize
            $image->resize($width, $height);

            switch ($ext) {
                case 'jpg':
                case 'jpeg':
                case 'webp':
                    $quality = intval($quality);
                    if ($this->get('ftp', 0)) {
                        @JFile::write($dest, $image->toString($ext, array('quality' => $quality)));
                    } else {
                        $image->toFile($dest, $ext, array('quality' => $quality));
                    }
                    break;
                default:
                    if ($this->get('ftp', 0)) {
                        @JFile::write($dest, $image->toString($ext, array('quality' => $quality)));
                    } else {
                        $image->toFile($dest, $ext, array('quality' => $quality));
                    }
                    break;
            }

            unset($image);
            unset($result);

            if (file_exists($dest)) {
                @JPath::setPermissions($dest);

                return $dest;
            }
        }

        return false;
    }

    public function rotate($file, $direction)
    {
        $ext = strtolower(JFile::getExt($file));
        $src = @file_get_contents($file);

        if ($src) {
            $options = $this->getOptions();

            $image = new WFImage(null, $options);
            // set type
            $image->setType($ext);
            // load data
            $image->loadString($src);

            // rotate
            $image->rotate($direction);

            switch ($ext) {
                case 'jpg':
                case 'jpeg':
                case 'webp':
                    if ($this->get('ftp', 0)) {
                        @JFile::write($file, $image->toString($ext, array('quality' => 100)));
                    } else {
                        $image->toFile($file, $ext, array('quality' => 100));
                    }
                    break;
                default:
                    if ($this->get('ftp', 0)) {
                        @JFile::write($file, $image->toString($ext, array('quality' => 0)));
                    } else {
                        $image->toFile($file, $ext, array('quality' => 0));
                    }
                    break;
            }

            unset($image);
        }

        return $file;
    }

    public function setQuality($file, $quality)
    {
        $ext = strtolower(JFile::getExt($file));
        $options = $this->getOptions();

        $image = new WFImage($file, $options);

        switch ($ext) {
            case 'jpg':
            case 'jpeg':
            case 'webp':
                $quality = intval($quality);

                if ($this->get('ftp', 0)) {
                    @JFile::write($file, $image->toString($ext, array('quality' => $quality)));
                } else {
                    $image->toFile($file, $ext, array('quality' => $quality));
                }

                break;
            default:
                if ($this->get('ftp', 0)) {
                    @JFile::write($file, $image->toString($ext, array('quality' => $quality)));
                } else {
                    $image->toFile($file, $ext, array('quality' => $quality));
                }
                break;
        }

        unset($image);

        return $file;
    }

    public function resample($file, $resolution = 72)
    {
        $ext = strtolower(JFile::getExt($file));
        $options = $this->getOptions();

        $image = new WFImage($file, $options);

        if ($image->resample($resolution)) {
            if ($this->get('ftp', 0)) {
                @JFile::write($file, $image->toString($ext));
            } else {
                @$image->toFile($file);
            }
        }

        unset($image);

        return $file;
    }
}
