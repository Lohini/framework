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

/**
 */
class FilesPairsFinder
extends \Nette\Object
{
	/** @var \Lohini\Testing\TestCase */
	private $test;
	/** @var string */
	private $inputSuffix;
	/** @var string */
	private $outputSuffix;


	/**
	 * @param \Lohini\Testing\TestCase $test
	 */
	public function __construct(\Lohini\Testing\TestCase $test)
	{
		$this->test=$test;
	}

	/**
	 * @param string $inputMask
	 * @param string $outputMask
	 * @return array[]
	 */
	public function find($inputMask, $outputMask)
	{
		list(, $this->inputSuffix)=explode('*', $inputMask, 2);
		list(, $this->outputSuffix)=explode('*', $outputMask, 2);

		$inputs=$this->findFiles($this->absoluteDir($inputMask), basename($inputMask));
		$outputs=$this->findFiles($this->absoluteDir($outputMask), basename($outputMask));
		$this->assertCorresponds($inputs, $outputs);

		$data=array();
		foreach ($inputs as $inputFile) {
			foreach ($outputs as $outputFile) {
				$inputBase=$inputFile->getBasename($this->inputSuffix);
				if ($inputBase===$outputFile->getBasename($this->outputSuffix)) {
					$data[$inputBase] = array($inputFile->getRealPath(), $outputFile->getRealPath());
					break;
					}
				}
			}

		return $data;
	}

	/**
	 * @param string $dir
	 * @return string
	 */
	private function absoluteDir($dir)
	{
		$dir=dirname($dir);
		if ($dir[0]!=='/') {
			$dir=dirname($this->test->getReflection()->getFileName()).'/'.$dir;
			}
		return $dir;
	}

	/**
	 * @param \SplFileInfo[] $inputs
	 * @param \SplFileInfo[] $outputs
	 * @throws \Lohini\FileNotFoundException
	 */
	private function assertCorresponds($inputs, $outputs)
	{
		$inputSuffix=$this->inputSuffix;
		$inputs=array_map(
			function(\SplFileInfo $file) use ($inputSuffix) { return $file->getBasename($inputSuffix); },
			$inputs
			);

		$outputSuffix=$this->outputSuffix;
		$outputs=array_map(
			function(\SplFileInfo $file) use ($outputSuffix) { return $file->getBasename($outputSuffix); },
			$outputs
			);

		if ($missingOutputs=array_diff($inputs, $outputs)) {
			$list=implode("', '", $missingOutputs);
			if (count($missingOutputs)>1){
				throw new \Lohini\FileNotFoundException("There are no output files for '$list'.");
				}
			throw new \Lohini\FileNotFoundException("There is no output file for '$list'.");
			}
		if ($missingInputs=array_diff($outputs, $inputs)) {
			$list=implode("', '", $missingInputs);
			if (count($missingInputs)>1) {
				throw new \Lohini\FileNotFoundException("There are no input files for '$list'.");
				}
			throw new \Lohini\FileNotFoundException("There is no input file for '$list'.");
			}
	}

	/**
	 * @param string $dir
	 * @param string $mask
	 * @return \SplFileInfo[]
	 */
	private function findFiles($dir, $mask)
	{
		return iterator_to_array(\Nette\Utils\Finder::findFiles($mask)->in($dir));
	}
}
