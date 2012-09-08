<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Utils;
/**
 * @author Filip Procházka <filip@prochazka.su>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 */
final class Filesystem
extends \Nette\Object
{
	/**
	 * Static class - cannot be instantiated.
	 */
	final public function __construct()
	{
		throw new \Nette\StaticClassException("Can't instantiate static class ".get_class($this));
	}

	/**
	 * @param string $file
	 * @param bool $need
	 * @return bool
	 * @throws \Lohini\FileNotWritableException
	 */
	public static function rm($file, $need=TRUE)
	{
		if (is_dir((string)$file)) {
			return static::rmDir($file, FALSE, $need);
			}

		if (FALSE===($result=@unlink((string)$file)) && $need) {
			throw new \Lohini\FileNotWritableException("Unable to delete file '$file'");
			}

		return $result;
	}

	/**
	 * @param string $dir
	 * @param bool $recursive
	 * @param bool $need
	 * @return bool
	 * @throws \Lohini\DirectoryNotWritableException
	 */
	public static function rmDir($dir, $recursive=TRUE, $need=TRUE)
	{
		$recursive && self::cleanDir($dir=(string)$dir, $need);
		if (is_dir($dir) && FALSE===($result=@rmdir($dir)) && $need) {
			throw new \Lohini\DirectoryNotWritableException("Unable to delete directory '$dir'.");
			}

		return isset($result)? $result : TRUE;
	}

	/**
	 * @param string $dir
	 * @param bool $need
	 * @return bool
	 */
	public static function cleanDir($dir, $need=TRUE)
	{
		if (!file_exists($dir)) {
			return TRUE;
			}

		foreach (\Nette\Utils\Finder::find('*')->from($dir)->childFirst() as $file) {
			if (!static::rm($file, $need)) {
				return FALSE;
				}
			}

		return TRUE;
	}

	/**
	 * @param string $dir
	 * @param bool $recursive
	 * @param int $chmod
	 * @param bool $need
	 * @throws \Nette\IOException
	 */
	public static function mkDir($dir, $recursive=TRUE, $chmod=0777, $need=TRUE)
	{
		$parentDir=$dir;
		while (!is_dir($parentDir)) {
			$parentDir=dirname($parentDir);
			}

		@umask(0000);
		if (!is_dir($dir) && FALSE===($result=@mkdir($dir, $chmod, $recursive)) && $need) {
			throw new \Nette\IOException('Unable to create directory '.$dir);
			}

		if ($dir!==$parentDir) {
			do {
				@umask(0000);
				@chmod($dir, $chmod);
				$dir=dirname($dir);
				}
			while ($dir!==$parentDir);
			}

		return isset($result)? $result : TRUE;
	}

	/**
	 * @param string $file
	 * @param string $contents
	 * @param bool $createDirectory
	 * @param int $chmod
	 * @param bool $need
	 * @return int
	 * @throws \Lohini\FileNotWritableException
	 */
	public static function write($file, $contents, $createDirectory=TRUE, $chmod=0777, $need=TRUE)
	{
		$createDirectory && static::mkDir(dirname($file), TRUE, $chmod);

		if (FALSE===($result=@file_put_contents($file, $contents)) && $need) {
			throw \Lohini\FileNotWritableException::fromFile($file);
			}
		@chmod($file, $chmod);

		return $result;
	}

	/**
	 * @param string $file
	 * @param bool $need
	 * @return string
	 * @throws FileNotFoundException
	 */
	public static function read($file, $need = TRUE)
	{
		if (FALSE === ($contents = @file_get_contents($file)) && $need) {
			throw new \Nette\FileNotFoundException("File '$file' is not readable.");
			}

		return $contents;
	}
}
