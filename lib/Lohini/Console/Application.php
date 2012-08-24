<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Console;
/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Lohini\Core,
	Symfony\Component\Console;

/**
 */
class Application
extends \Lohini\Application\Application
{
	/** @var Console\Input\ArgvInput */
	private $consoleInput;
	/** @var Console\Output\ConsoleOutput */
	private $consoleOutput;


	/**
	 * @return int
	 */
	public function run()
	{
		$this->consoleInput=new Console\Input\ArgvInput;
		$this->consoleOutput=new Console\Output\ConsoleOutput;
		$this->onStartup($this);

		// package errors should not be handled by console life-cycle
		$cli=$this->createApplication();

		$exitCode=1;
		try {
			// run the console
			$exitCode=$cli->run($this->consoleInput, $this->consoleOutput);
			}
		catch (\Exception $e) {
			// fault barrier
			$this->onError($this, $e);
			$this->onShutdown($this, $e);

			// log
			\Nette\Diagnostics\Debugger::log($e, 'console');
			\Lohini\Diagnostics\ConsoleDebugger::_exceptionHandler($e);

			// render exception
			$cli->renderException($e, $this->consoleOutput);
			return $exitCode;
			}

		$this->onShutdown($this, isset($e)? $e : NULL);
		return $exitCode;
	}

	/**
	 * @return Console\Application
	 */
	protected function createApplication()
	{
		$container=$this->getConfigurator()->getContainer();

		// create
		$cli=new Console\Application(
			Core::NAME.' Command Line Interface',
			Core::VERSION
			);

		// override error handling
		$cli->setCatchExceptions(FALSE);
		$cli->setAutoExit(FALSE);

		// set helpers
		$cli->setHelperSet($container->lohini->{'console.helpers'});

		// register packages
		$this->getConfigurator()
				->getPackages()->registerCommands($cli);

		return $cli;
	}
}
