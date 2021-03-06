<?php

namespace Oforge\Engine\Modules\Core\Helper;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * FileSystemHelper
 *
 * @package Oforge\Engine\Modules\Core\Helper
 */
class FileSystemHelper {
    public const OMIT = ['.', '..', '.git', 'var', 'vendor', 'node_modules'];
    /**
     * Caching of findFiles results.
     *
     * @var array $findCache
     */
    private static $findCache = [];

    /**
     * Prevent instance.
     */
    private function __construct() {
    }

    /**
     * Delete single file or directory.
     * Directories can optionally be recursively deleted and empty directories will not be deleted.
     *
     * @param string $path Path to file or directory
     * @param bool $recursive Delete directory recursive?
     * @param bool $deleteEmptyDirs Delete empty directories?
     *
     * @return bool
     */
    public static function delete(string $path, bool $recursive = false, bool $deleteEmptyDirs = true) : bool {
        if (empty($path)) {
            return false;
        }
        $path = realpath($path);
        if (is_dir($path)) {
            $filenames = array_diff(scandir($path), ['.', '..']);
            $success   = true;
            foreach ($filenames as $filename) {
                if ($recursive) {
                    $success &= self::delete($path . DIRECTORY_SEPARATOR . $filename, $recursive, $deleteEmptyDirs);
                }
            }
            if ($deleteEmptyDirs) {
                $tmp = @rmdir($path);
                if (!$tmp) {
                    Oforge()->Logger()->get()->warning('Could not delete directory: ' . $path);
                }
                $success &= $tmp;
            }

            return $success;
        }
        if (@unlink($path)) {
            return true;
        }
        Oforge()->Logger()->get()->warning('Could not delete file: ' . $path);

        return false;
    }

    /**
     * Search recursive for for files with name inside a path.
     *
     * @param string $path string Directory or file path.
     * @param string $searchFileName Search file name.
     *
     * @return string[] Array with full path to files.
     */
    public static function findFiles(string $path, string $searchFileName) {
        $omits  = array_fill_keys(self::OMIT, true);
        $path   = realpath($path);
        $result = [];

        $recursiveDirectoryIterator = new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::FOLLOW_SYMLINKS);
        $recursiveFilterIterator    = new \RecursiveCallbackFilterIterator($recursiveDirectoryIterator,
            function ($file, $key, $iterator) use ($omits, $searchFileName) {
                return !isset($omits[$file->getFileName()]);
            });
        $recursiveIteratorIterator  = new RecursiveIteratorIterator($recursiveFilterIterator);
        foreach ($recursiveIteratorIterator as $file) {
            if (strtolower($file->getFileName()) === $searchFileName) {
                $result[] = $file->getPath() . DIRECTORY_SEPARATOR . $file->getFileName();
            }
        }

        return $result;
    }

    /**
     * Get all Bootstrap.php files inside a defined path
     *
     * @param string $path
     *
     * @return string[]
     */
    public static function getBootstrapFiles(string $path) {
        return self::getCachedOrFind($path, 'bootstrap.php');
    }

    /**
     * Get all template files inside a defined path
     *
     * @param string $path
     *
     * @return string[]
     */
    public static function getThemeBootstrapFiles(string $path) {
        return self::getCachedOrFind($path, 'template.php');
    }

    /**
     * Get all sub directories recursive based on the defined path, except the folders to oforge omit.
     *
     * @param string $path
     *
     * @return string[]
     */
    public static function getSubDirectories(string $path) {
        $result = [];
        if (!is_dir($path)) {
            return $result;
        }
        $fileNames = scandir($path);

        foreach ($fileNames as $fileName) {
            $filePath = $path . DIRECTORY_SEPARATOR . $fileName;

            if (is_dir($filePath) && !in_array($fileName, self::OMIT)) {
                $result[] = $filePath;
                $result   = array_merge($result, self::getSubDirectories($filePath));
            }
        }

        return $result;
    }

    /**
     * Helper method for call mkdir.
     *
     * @param string $path
     * @param bool $recursive
     * @param int $mode
     * @param resource|null $context
     */
    public static function mkdir(string $path, bool $recursive = true, int $mode = 0755, $context = null) {
        if (isset($context)) {
            @mkdir($path, $mode, $recursive, $context);
        } else {
            @mkdir($path, $mode, $recursive);
        }
    }

    /**
     * Get cached data or find files with filename in path and cache it.
     *
     * @param string $context
     * @param string $path
     * @param string $filename
     *
     * @return mixed|string[]
     */
    private static function getCachedOrFind(string $path, string $filename) {
        $context = basename($path);
        if (isset(self::$findCache[$context])) {
            return self::$findCache[$context];
        }
        $cacheFile = ROOT_PATH . Statics::CACHE_DIR . DIRECTORY_SEPARATOR . $context . '.cache.php';

        if (file_exists($cacheFile) && Oforge()->Settings()->isProductionMode()) {
            $result = ArrayPhpFileStorage::load($cacheFile);
        } else {
            $result = self::findFiles($path, $filename);
            ArrayPhpFileStorage::write($cacheFile, $result);
        }
        self::$findCache[$filename] = $result;

        return $result;
    }

}
