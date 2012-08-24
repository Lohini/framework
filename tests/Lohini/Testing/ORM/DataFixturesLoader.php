<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Testing\ORM;
/**
* @author Filip Procházka <filip.prochazka@kdyby.org>
*/
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Doctrine\Common\DataFixtures,
	Lohini\Testing\OrmTestCase;

/**
 */
class DataFixturesLoader
extends \Nette\Object
{
	/** @var \Doctrine\Common\DataFixtures\Loader */
	private $loader;
	/** @var \Doctrine\Common\DataFixtures\Executor\AbstractExecutor */
	private $executor;


	/**
	 * @param DataFixtures\Loader $loader
	 * @param DataFixtures\Executor\AbstractExecutor $executor
	 */
	public function __construct(DataFixtures\Loader $loader, DataFixtures\Executor\AbstractExecutor $executor)
	{
		$this->loader=$loader;
		$this->executor=$executor;
	}

	/**
	 * Appends Data Fixtures to current database DataSet
	 *
	 * @param OrmTestCase $testCase
	 */
	public function loadFixtures(OrmTestCase $testCase)
	{
		$this->addFixtureClasses($this->getTestFixtureClasses($testCase));
		$this->executor->execute($this->loader->getFixtures(), TRUE);
	}

	/**
	 * @param array $classes
	 * @param array $visited
	 */
	private function addFixtureClasses(array $classes, &$visited=array())
	{
		if (!$classes) {
			return;
			}

		$fixtures=array();
		foreach ($classes as $class) {
			if (in_array($class, $visited)) {
				continue;
				}

			$fixtures[]= $fixture= new $class;
			$this->loader->addFixture($fixture);
			$visited[]=$class;
			}

		foreach ($fixtures as $fixture) {
			if (!$fixture instanceof DataFixtures\DependentFixtureInterface) {
				continue;
				}

			$this->addFixtureClasses(array_diff($fixture->getDependencies(), $visited), $visited);
		}
	}

	/**
	 * @param OrmTestCase $testCase
	 * @return array
	 */
	private function getTestFixtureClasses(OrmTestCase $testCase)
	{
		$method=$testCase->getReflection()->getMethod($testCase->getName(FALSE));
		$annotations=$method->getAnnotations();

		return array_map(
			function($class) use ($method) {
				if (class_exists($class)) {
					return $class;
					}

				$testCaseNs=$method->getDeclaringClass()->getNamespaceName();
				if (class_exists($prefixed=$testCaseNs.'\\'.$class)) {
					return $prefixed;
					}

				if (class_exists($prefixed=$testCaseNs.'\\Fixture\\'.$class)) {
					return $prefixed;
					}

				throw new \Nette\InvalidStateException("Fixtures $class for test $method could not be loaded.");
				},
			isset($annotations['Fixture'])? $annotations['Fixture'] : array()
			);
	}
}
