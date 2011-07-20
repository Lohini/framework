<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Database\Doctrine\ODM\Mapping\Driver;
/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 * @author Filip Procházka
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Doctrine\ODM\CouchDB\Mapping\MappingException;

class AnnotationDriver
extends \Doctrine\ODM\CouchDB\Mapping\Driver\AnnotationDriver
{
	const IGNORE_FOLDERS='noentities';


	/**
	 * @return \Nette\Utils\Finder
	 */
	private function getFilesIterator()
	{
		return \Nette\Utils\Finder::findFiles('*'.$this->_fileExtension)
				->from($this->_paths)
				->filter(function ($directory) {
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

		foreach ($this->getFilesIterator() as $file) {
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
