<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace LohiniTesting\Tests;
/**
* @author Filip Procházka <filip.prochazka@kdyby.org>
*/
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Nette\Application\UI,
	Nette\ObjectMixin;

/**
 */
abstract class TestCase
extends \PHPUnit_Framework_TestCase
{
	/** @var \SystemContainer|\Nette\DI\Container */
	private $context;
	/** @var Tools\TempClassGenerator */
	private $tempClassGenerator;


	/**
	 * @param string $name
	 * @param array $data
	 * @param string $dataName
	 */
	public function __construct($name=NULL, array $data=array(), $dataName='')
	{
		$this->context=\LohiniTesting\Configurator::getTestsContainer();
		$this->tempClassGenerator=new Tools\TempClassGenerator($this->getContext()->expand('%tempDir%'));

		parent::__construct($name, $data, $dataName);
	}

	/**
	 * @return \SystemContainer|\Nette\DI\Container
	 */
	public function getContext()
	{
		return $this->context;
	}

	/**
	 * Skip test if domain lohini.net is unreachable
	 */
	protected function skipIfNoInternet()
	{
		if ('pong'!==@file_get_contents('http://www.lohini.net/ping')) {
			$this->markTestSkipped('No internet connection');
			}
	}

	/********************* Asserts *********************/
	/**
	 * @param array|\Nette\Callback|\Closure $callback
	 * @param \Nette\Object $object
	 * @param string $eventName
	 * @param int|NULL $count
	 */
	public function assertEventHasCallback($callback, $object, $eventName, $count=NULL)
	{
		$this->assertCallable($callback);

		self::assertThat($callback, new Constraint\EventHasCallbackConstraint($object, $eventName, $count), NULL);
	}

	/**
	 * @param array $collection
	 * @param array $lists
	 * @param array $mappers
	 * @param bool $allowOnlyMentioned
	 * @param bool $allowDuplications
	 */
	public function assertContainsCombinations($collection, array $lists, array $mappers, $allowOnlyMentioned=TRUE, $allowDuplications=FALSE)
	{
		$constraint=new Constraint\ContainsCombinationConstraint($lists, $mappers);
		$constraint->allowDuplications=$allowDuplications;
		$constraint->allowOnlyMentioned=$allowOnlyMentioned;
		self::assertThat($collection, $constraint, NULL);
	}

	/**
	 * Given callback must return TRUE, when the condition is met, FALSE otherwise
	 *
	 * @param array $collection
	 * @param callable $callback
	 */
	public function assertItemsMatchesCondition($collection, $callback)
	{
		$callback=callback($callback);
		$i=0;
		foreach ($collection as $item) {
			$this->assertTrue($callback($item), "Item #$i matches the conditions from callback.");
			$i++;
			}
	}

	/**
	 * @param callable $callback
	 * @param string $message
	 */
	public function assertCallable($callback, $message=NULL)
	{
		$constraint=new Constraint\IsCallableConstraint;
		self::assertThat($callback, $constraint, $message);
	}

	/********************* Mocking *********************/
	/**
	 * @return \PHPUnit_Framework_MockObject_MockObject|\Closure
	 */
	public function getCallbackMock()
	{
		return $this->getMockBuilder('LohiniTesting\Tests\Tools\Callback')
			->disableOriginalConstructor()
			->getMock();
	}

	/**
	 * @param \Nette\ComponentModel\IComponent $component
	 * @param array $methods
	 * @param string $name
	 * @return \PHPUnit_Framework_MockObject_MockObject|\Lohini\Application\UI\Presenter
	 */
	public function attachToPresenter(\Nette\ComponentModel\IComponent $component, $methods=array(), $name='component')
	{
		/** @var \PHPUnit_Framework_MockObject_MockObject|\Lohini\Application\UI\Presenter $presenter */
		$presenter=$this->getMock('Lohini\Application\UI\Presenter', (array)$methods, array());
		$presenter->setContext($this->getContext());
		$component->setParent($presenter, $name);
		return $presenter;
	}

	/********************* DataProvider *********************/
	/**
	 * @param string $inputMask
	 * @param string $outputMask
	 * @return array[]
	 */
	protected function findInputOutput($inputMask, $outputMask)
	{
		$finder=new Tools\FilesPairsFinder($this);
		return $finder->find($inputMask, $outputMask);
	}

	/********************* Nette Forms *********************/
	/**
	 * @param UI\Form $form
	 * @param array $values
	 */
	public function submitForm(UI\Form $form, array $values=array())
	{
		$get= $form->getMethod()!==UI\Form::POST? $values : array();
		$post= $form->getMethod()===UI\Form::POST? $values : array();
		list($post, $files)=$this->separateFilesFromPost($post);

		$presenter=new Tools\UIFormTestingPresenter($this->getContext(), $form);
		return $presenter->run(new \Nette\Application\Request(
			'presenter',
			strtoupper($form->getMethod()),
			array('do' => 'form-submit', 'action' => 'default')+$get,
			$post,
			$files
			));
	}

	/**
	 * @param array $post
	 * @param array $files
	 * @return array
	 */
	private function separateFilesFromPost(array $post, array $files=array())
	{
		foreach ($post as $key => $value) {
			if (is_array($value)) {
				list($pPost, $pFiles)=$this->separateFilesFromPost($value);
				unset($post[$key]);

				if ($pPost) {
					$post[$key]=$pPost;
					}
				if ($pFiles) {
					$files[$key]=$pFiles;
					}
				}

			if ($value instanceof \Nette\Http\FileUpload) {
				$files[$key] = $value;
				unset($post[$key]);
				}
			}

		return array($post, $files);
	}

	/********************* TempClassGenerator *********************/
	/**
	 * @return Tools\TempClassGenerator
	 */
	private function getTempClassGenerator()
	{
		return $this->tempClassGenerator;
	}

	/**
	 * @param string $class
	 * @return string
	 */
	protected function touchTempClass($class=NULL)
	{
		return $this->getTempClassGenerator()->generate($class);
	}

	/**
	 * @param string $class
	 * @return string
	 */
	protected function resolveTempClassFilename($class)
	{
		return $this->getTempClassGenerator()->resolveFilename($class);
	}

	/********************* Exceptions handling *********************/
	/**
	 * This method is called when a test method did not execute successfully.
	 *
	 * @param \Exception $e
	 */
	protected function onNotSuccessfulTest(\Exception $e)
	{
		if (!$e instanceof \PHPUnit_Framework_AssertionFailedError) {
			\Nette\Diagnostics\Debugger::log($e);
			\Lohini\Diagnostics\ConsoleDebugger::_exceptionHandler($e);
			}

		parent::onNotSuccessfulTest($e);
	}

	/********************* Nette\Object behaviour ****************d*g**/
	/**
	 * @return \Nette\Reflection\ClassType
	 */
	public static function getReflection()
	{
		return new \Nette\Reflection\ClassType(get_called_class());
	}

	/**
	 * @param string $name
	 * @return mixed
	 */
	public function &__get($name)
	{
		return ObjectMixin::get($this, $name);
	}

	/**
	 * @param string $name
	 * @param mixed $value
	 */
	public function __set($name, $value)
	{
		ObjectMixin::set($this, $name, $value);
	}

	/**
	 * @param string $name
	 *
	 * @return bool
	 */
	public function __isset($name)
	{
		return ObjectMixin::has($this, $name);
	}

	/**
	 * @param string $name
	 */
	public function __unset($name)
	{
		ObjectMixin::remove($this, $name);
	}
}
