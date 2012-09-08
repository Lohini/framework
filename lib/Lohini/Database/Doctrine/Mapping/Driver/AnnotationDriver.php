<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Database\Doctrine\Mapping\Driver;
/**
 * @author Filip Procházka <filip@prochazka.su>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Doctrine\ORM\Mapping\MappingException;

/**
 */
class AnnotationDriver
extends \Doctrine\ORM\Mapping\Driver\AnnotationDriver
{
	const IGNORE_FOLDERS='.noentities';


	/**
	 * {@inheritdoc}
	 *
	 * @param \Doctrine\Common\Annotations\AnnotationReader $reader The AnnotationReader to use, duck-typed.
	 * @param string|array $paths One or multiple paths where mapping classes can be found.
	 */
	public function __construct(\Doctrine\Common\Annotations\Reader $reader, $paths=NULL)
	{
		parent::__construct($reader, $paths);
	}

	/**
	 * @param array $classNames
	 */
	public function setClassNames(array $classNames)
	{
		$this->_classNames=$classNames;
	}

	/**
	 * @return \Nette\Utils\Finder
	 */
	private function createFilesIterator()
	{
		return \Nette\Utils\Finder::findFiles('*'.$this->_fileExtension)->from($this->_paths)->filter(
				function($directory) {
					if (!$directory->isDir()) {
						return FALSE;
						}

					if (glob($directory->getPathname().'/'.AnnotationDriver::IGNORE_FOLDERS)) {
						return FALSE;
						}

					return TRUE;
				});
	}

	/**
	 * {@inheritDoc}
	 */
	public function getAllClassNames()
	{
		if ($this->_classNames!==NULL) {
			return $this->_classNames;
			}

		if (!$this->_paths) {
			throw MappingException::pathRequired();
			}

		$classes=array();
		$includedFiles=array();

		foreach ($this->_paths as $path) {
			if (!is_dir($path)) {
				throw MappingException::fileMappingDriversRequireConfiguredDirectoryPath($path);
				}
			}

		foreach ($this->createFilesIterator() as $file) {
			$sourceFile=realpath($file->getPathName());
			require_once $sourceFile;
			$includedFiles[]=$sourceFile;
			}

		$declared=get_declared_classes();
		foreach ($declared as $className) {
			$rc=new \ReflectionClass($className);
			$sourceFile=$rc->getFileName();
			if (in_array($sourceFile, $includedFiles) && !$this->isTransient($className)) {
				$classes[]=$className;
				}
			}

		$this->_classNames=$classes;

		return $classes;
	}
}
