<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Testing;
/**
* @author Filip Procházka <filip.prochazka@kdyby.org>
*/
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 */
abstract class LatteTestCase
extends TestCase
{
	/** @var \Nette\Latte\Engine */
	private $engine;
	/** @var \Lohini\Testing\Tools\LatteTemplateOutput */
	private $outputTemplate;

	/**
	 * @param string $name
	 * @param array $data
	 * @param string $dataName
	 */
	public function __construct($name=NULL, array $data=array(), $dataName='')
	{
		$this->engine=new Tools\LatteEngine;
		parent::__construct($name, $data, $dataName);
	}

	/**
	 * @param string $installer
	 * @return \Nette\Latte\IMacro
	 */
	protected function installMacro($installer)
	{
		$installer=callback($installer);
		return $installer($this->engine->getCompiler());
	}

	/**
	 * @param string $latte
	 * @throws \Nette\InvalidStateException
	 */
	protected function parse($latte)
	{
		if (file_exists($latte)) {
			$latte=file_get_contents($latte);
			}

		if ($this->outputTemplate!==NULL) {
			throw new \Nette\InvalidStateException('Please split the test method into more parts. Cannot parse repeatedly.');
			}

		$latteTemplate=new Tools\LatteTemplateOutput($this->engine, $this->getContext()->expand('%tempDir%'));
		$this->outputTemplate=$latteTemplate->parse($latte);
	}

	/**
	 * @param string $expected
	 * @param string $message
	 * @throws \Nette\InvalidStateException
	 */
	public function assertLatteMacroEquals($expected, $message=NULL)
	{
		if (file_exists($expected)) {
			$expected=file_get_contents($expected);
			}

		if ($this->outputTemplate===NULL) {
			throw new \Nette\InvalidStateException('Call '.get_called_class().'::parse($latte) first.');
			}

		$this->assertEquals($expected, $this->outputTemplate->macro, $message);
	}

	/**
	 * @param string $expected
	 * @param string $message
	 * @throws \Nette\InvalidStateException
	 */
	public function assertLatteEpilogEquals($expected, $message=NULL)
	{
		if (file_exists($expected)) {
			$expected=file_get_contents($expected);
			}

		if ($this->outputTemplate===NULL) {
			throw new \Nette\InvalidStateException('Call '.get_called_class().'::parse($latte) first.');
			}

		$this->assertEquals($expected, $this->outputTemplate->epilog, $message);
	}

	/**
	 * @param string $expected
	 * @param string $message
	 * @throws \Nette\InvalidStateException
	 */
	public function assertLattePrologEquals($expected, $message=NULL)
	{
		if (file_exists($expected)) {
			$expected=file_get_contents($expected);
			}

		if ($this->outputTemplate===NULL) {
			throw new \Nette\InvalidStateException('Call '.get_called_class().'::parse($latte) first.');
			}

		$this->assertEquals($expected, $this->outputTemplate->prolog, $message);
	}
}
