<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Testing\Tools;
/**
* @author Filip Procházka <filip@prochazka.su>
*/
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Lohini\Testing\TestCase;

/**
 */
class DataSetFilenameResolver
extends \Nette\Object
{
	/** @var TestCase */
	private $testCase;


	/**
	 * @param TestCase $testCase
	 */
	public function __construct(TestCase $testCase)
	{
		$this->testCase=$testCase;
	}

	/**
	 * @return string
	 * @throws \Lohini\FileNotFoundException
	 */
	public function resolve()
	{
		$filenamePart=$this->getTestDirectory().DIRECTORY_SEPARATOR
				.$this->getTestCaseName().'.'.$this->getTestName();

		foreach (array('xml', 'yaml', 'csv', 'neon') as $extension) {
			if (file_exists($file=$filenamePart.'.'.$extension)) {
				return $file;
				}
			}

		throw new \Lohini\FileNotFoundException("File '$file' not found.");
	}

	/**
	 * @return string
	 */
	private function getTestDirectory()
	{
		$class=$this->testCase->getReflection()
			->getMethod($this->testCase->getName(FALSE))->getDeclaringClass();

		return dirname($class->getFileName());
	}

	/**
	 * @return string
	 */
	private function getTestCaseName()
	{
		$className=get_class($this->testCase);
		return str_replace('Test', '', substr($className, strrpos($className, '\\')+1));
	}

	/**
	 * @return string
	 */
	private function getTestName()
	{
		return lcFirst(str_replace('test', '', $this->testCase->getName(FALSE)));
	}
}
