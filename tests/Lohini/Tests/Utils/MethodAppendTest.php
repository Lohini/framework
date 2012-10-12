<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Tests\Utils;
/**
* @author Filip Procházka <filip@prochazka.su>
*/
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Lohini\Utils\MethodAppend;

/**
 */
class MethodAppendTest
extends \Lohini\Testing\TestCase
{
	/**
	 * @return \Nette\Reflection\ClassType
	 */
	private function prepareClass()
	{
		$tempDir=$this->getContext()->expand('%tempDir%/classes');
		\Lohini\Utils\Filesystem::mkDir($tempDir);

		$class=new \Nette\Utils\PhpGenerator\ClassType('MyClass_'.\Nette\Utils\Strings::random());
		$foo=$class->addMethod('foo')
			->addBody('$c = $a + $b;');
		$foo->addParameter('a');
		$foo->addParameter('b');

		$class->addMethod('bar')
			->addBody('return $this->foo(1, 2);');

		$file=$tempDir.'/'.$class->name.'.class.php';
		file_put_contents($file, '<?php'."\n\n".(string)$class);
		require_once $file;

		return \Nette\Reflection\ClassType::from($class->name);
	}

	/**
	 * @param string $name
	 * @param string $className
	 * @return string
	 */
	private function expected($name, $className)
	{
		$expected=file_get_contents(__DIR__."/Fixtures/MethodAppendTest.$name.expected");
		return strtr(
			$expected,
			array('<generated_class_name>' => $className)
			);
	}

	public function testAppend()
	{
		$class=$this->prepareClass();
		$method=new MethodAppend($class->getMethod('foo'));
		$method->append('$myCode = "lipsum";');

		$result=file_get_contents($class->getFileName());
		$this->assertEquals($this->expected('functional1', $class->name), $result);
	}

	public function testAppend_Multiple()
	{
		$class=$this->prepareClass();
		$method=new MethodAppend($class->getMethod('foo'));
		$method->append('$myCode = "lipsum";');
		$method->append('$myCode = "dolor";');
		$method->append('$myCode = "sit amet";');

		$result=file_get_contents($class->getFileName());
		$this->assertEquals($this->expected('functional2', $class->name), $result);
	}
}
