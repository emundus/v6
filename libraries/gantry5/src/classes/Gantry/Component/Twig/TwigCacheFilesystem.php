<?php

/**
 * @package   Gantry5
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2022 RocketTheme, LLC
 * @license   Dual License: MIT or GNU/GPLv2 and later
 *
 * http://opensource.org/licenses/MIT
 * http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Gantry Framework code that extends GPL code is considered GNU/GPLv2 and later
 */

namespace Gantry\Component\Twig;

use Twig\Cache\CacheInterface;

/**
 * Class TwigCacheFilesystem
 * @package Gantry\Component\Twig
 *
 * Replaces \Twig\FilesystemCache, needed for being able to change PHP versions on fly.
 */
class TwigCacheFilesystem implements CacheInterface
{
    const FORCE_BYTECODE_INVALIDATION = 1;

    /** @var string */
    private $directory;
    /** @var int */
    private $options;

    /**
     * @param string $directory The root cache directory
     * @param int    $options   A set of options
     */
    public function __construct($directory, $options = 0)
    {
        $this->directory = rtrim($directory, '\/').'/';
        $this->options = $options;
    }
    /**
     * {@inheritdoc}
     */
    public function generateKey($name, $className)
    {
        $hash = hash('sha256', $className . '-' . PHP_VERSION);

        return $this->directory . $hash[0] . $hash[1] . '/' . $hash . '.php';
    }
    /**
     * {@inheritdoc}
     */
    public function load($key)
    {
        if (file_exists($key)) {
            @include_once $key;
        }
    }
    /**
     * {@inheritdoc}
     */
    public function write($key, $content)
    {
        $dir = \dirname($key);
        if (!is_dir($dir)) {
            if (false === @mkdir($dir, 0777, true)) {
                clearstatcache(true, $dir);
                if (!is_dir($dir)) {
                    throw new \RuntimeException(sprintf('Unable to create the cache directory (%s).', $dir));
                }
            }
        } elseif (!is_writable($dir)) {
            throw new \RuntimeException(sprintf('Unable to write in the cache directory (%s).', $dir));
        }

        $tmpFile = tempnam($dir, basename($key));
        if (false !== @file_put_contents($tmpFile, $content) && @rename($tmpFile, $key)) {
            @chmod($key, 0666 & ~umask());

            if (self::FORCE_BYTECODE_INVALIDATION == ($this->options & self::FORCE_BYTECODE_INVALIDATION)) {
                // Compile cached file into bytecode cache
                if (\function_exists('opcache_invalidate') && filter_var(ini_get('opcache.enable'), FILTER_VALIDATE_BOOLEAN)) {
                    @opcache_invalidate($key, true);
                } elseif (\function_exists('apc_compile_file')) {
                    apc_compile_file($key);
                }
            }

            return;
        }

        throw new \RuntimeException(sprintf('Failed to write cache file "%s".', $key));
    }
    /**
     * {@inheritdoc}
     */
    public function getTimestamp($key)
    {
        if (!file_exists($key)) {
            return 0;
        }

        return (int) @filemtime($key);
    }
}
