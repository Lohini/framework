<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace BailIff\Database\Doctrine\ORM\Mapping\Driver;
/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 * @author Filip Procházka
 */
/**
 * BailIff port
 * @author Lopo <lopo@losys.eu>
 */

require_once __DIR__.'/DoctrineAnnotations.php';

class AnnotationDriver
extends \Doctrine\ORM\Mapping\Driver\AnnotationDriver
{
	const IGNORE_FOLDERS='noentities';


	/**
	 * @return \Nette\Utils\Finder
	 */
	private function getFilesIterator()
	{
		return \Nette\Utils\Finder::findFiles('*'.$this->_fileExtension)->from($this->_paths)->filter(function($directory) {
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
			throw \Doctrine\ORM\Mapping\MappingException::pathRequired();
			}
		$classes=array();
		$includedFiles=array();

		foreach ($this->_paths as $path) {
			if (!is_dir($path)) {
				throw \Doctrine\ORM\Mapping\MappingException::fileMappingDriversRequireConfiguredDirectoryPath($path);
				}
			}

		foreach ($this->getFilesIterator() as $file) {
			$sourceFile=realpath($file->getPathName());
			require_once $sourceFile;
			$includedFiles[]=$sourceFile;
			}

		foreach (get_declared_classes() as $className) {
			$rc=new \ReflectionClass($className);
			$sourceFile=$rc->getFileName();
			if (in_array($sourceFile, $includedFiles) && !$this->isTransient($className)) {
				$classes[]=$className;
				}
			}

		$this->_classNames=$classes;
		return $classes;
	}

	/**
	 * @param string
	 * @param \Doctrine\ORM\Mapping\ClassMetadataInfo
	 */
	public function loadMetadataForClass($className, \Doctrine\ORM\Mapping\ClassMetadataInfo $metadata)
	{
		parent::loadMetadataForClass($className, $metadata);

		if ($metadata instanceof \BailIff\Database\Doctrine\ORM\Mapping\ClassMetadata) {
			$class=\Nette\Reflection\ClassType::from($className);
			if ($class->hasAnnotation('service')) {
				$service=$class->getAnnotation('service');
				if (!isset($service['class'])) {
					throw new \Doctrine\ORM\Mapping\MappingException('Missing service class.');
					}
				$metadata->setServiceClass($service['class']);
				}
			}
	}
}
