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

use Nette\PhpGenerator;

/**
 */
class ClosureExtractor
extends \Nette\Object
{
	/** @var \Nette\Reflection\GlobalFunction */
	private $closure;


	/**
	 * @param \Closure $closure
	 */
	public function __construct(\Closure $closure)
	{
		$this->closure=new \Nette\Reflection\GlobalFunction($closure);
	}

	/**
	 * @param \ReflectionClass $class
	 * @return string
	 */
	public function buildScript(\ReflectionClass $class=NULL)
	{
		$uses=new \Lohini\Reflection\NamespaceUses($class);
		$codeParser=new \Lohini\Reflection\FunctionCode($this->closure);

		$code='<?php'."\n\n";

		if ($class) {
			$code.="namespace {$class->getNamespaceName()};\n\n";
			$code.='use '.implode(";\nuse ", $uses->parse()).";\n";
			}

		$code.="\n";

		// bootstrap
		if (!empty($GLOBALS['__PHPUNIT_BOOTSTRAP'])) {
			$code.=PhpGenerator\Helpers::formatArgs('require_once ?;', array($GLOBALS['__PHPUNIT_BOOTSTRAP']))."\n";
			}

		// debugging
		$code.=__CLASS__ . '::errorHandlers();' . "\n\n\n";

		// script
		$code.=PhpGenerator\Helpers::formatArgs('extract(?);', array($this->closure->getStaticVariables()))."\n";
		$code.=$codeParser->parse()."\n\n\n";

		// close session
		$code.='Lohini\Testing\Configurator::getTestsContainer()->session->close();'."\n\n";

		return $code;
	}

	public static function errorHandlers()
	{
		$container=\Lohini\Testing\Configurator::getTestsContainer();
		$response=$container->httpResponse;
		$response->setHeader('Content-Type', 'text/plain');

		\Nette\Diagnostics\Debugger::$onFatalError[]=function(\Exception $e) use ($response) {
			$response->setHeader('X-Nette-Error-Type', get_class($e));
			$response->setHeader('X-Nette-Error-Message', $e->getMessage());
			exit(Process::CODE_ERROR);
			};
	}
}
