<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Extension\DI;
/**
 * @author Filip Procházka <filip@prochazka.su>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Nette\DI\ServiceDefinition,
	Nette\PhpGenerator;

/**
 */
class FactoryGeneratorExtension
extends \Nette\Config\CompilerExtension
{
	/** @var string */
	private $classesFile;


	/**
	 * Generates all the factory classes for non-shared services
	 */
	public function beforeCompile()
	{
		$interfaces= $code= array();
		$builder=$this->getContainerBuilder();

		/** @var \Nette\DI\ServiceDefinition $def */
		foreach ($builder->getDefinitions() as $name => $def) {
			$factoryName=$name.'Factory';
			if ($def->shared || $def->internal) {
				continue;
				}

			if ($builder->hasDefinition($factoryName)) {
				if ($builder->getDefinition($factoryName)->class==='Nette\Callback') {
					$builder->removeDefinition($factoryName);
					}
				else {
					continue;
					}
				}

			/** @var PhpGenerator\ClassType $class */
			/** @var PhpGenerator\ClassType $interface */
			list($class, $interface)=$this->createServiceFactory($def, $name);

			// only if class can be resolved
			if ($class===NULL) {
				continue;
				}
			$code[]=$class;

			// interfaces must be unique
			if (!in_array($interface->name, $interfaces, TRUE)) {
				$code[]=$interface;
				$interfaces[]=$interface->name;
				}

			$builder->addDefinition($factoryName)
				->setClass($interface->name)
				->setFactory($class->name)
				->tags=$def->tags;
			}

		// generate and save
		$cache=$this->getCache();
		$cache->save($interfaces, $this->generateCode($this->namespaceClasses($code)));

		// find the file
		$cached=$cache->load($interfaces);
		$this->classesFile=$cached['file'];
		\Nette\Utils\LimitedScope::load($this->classesFile, TRUE);

		// add as dependency
		$this->getContainerBuilder()->addDependency($this->classesFile);
	}

	/**
	 * @param PhpGenerator\ClassType $class
	 */
	public function afterCompile(PhpGenerator\ClassType $class)
	{
		/** @var PhpGenerator\Method $init */
		$init=$class->methods['initialize'];
		$init->addBody('include_once ?;', array($this->classesFile));
	}

	/**
	 * @param \Nette\DI\ServiceDefinition $def
	 * @param string $serviceName
	 * @return array
	 */
	private function createServiceFactory(ServiceDefinition $def, $serviceName)
	{
		$builder=$this->getContainerBuilder();

		if ($def->class) {
			$className=$builder->expand($def->class);
			}
		elseif ($def->factory) {
			$className=$builder->expand($def->factory->entity); // todo
		}

		if (!isset($className) || !class_exists($className)) {
			return array(NULL, NULL);
			}

		// naming
		$factoryName=$className.'Factory';
		$interfaceClass=substr_replace($factoryName, 'I', strrpos($factoryName, '\\')+1, 0);
		$factoryClass=str_replace('\\', '_', $factoryName).'_'
			.str_replace('.', '_', $serviceName).'_'.\Nette\Utils\Strings::random(5);

		// interface
		$interface=new PhpGenerator\ClassType($interfaceClass);
		$interface->setType('interface');
		$this->generateCreateMethod($className, $def, $interface);
		$interface->addDocument('Creates instance of '.$className);

		// class
		$class=new PhpGenerator\ClassType($factoryClass);
		$class->setFinal(TRUE);
		$class->addExtend('\Nette\Object');
		$class->addImplement('\\'.$interface->name);
		$class->addDocument('@internal');

		// pass in the container
		$class->addProperty('container');
		$constructImpl=$class->addMethod('__construct');
		$containerParam=$constructImpl->addParameter('container');
		$containerParam->setTypeHint('\Nette\DI\Container');
		$constructImpl->addBody('$this->container=$container;');

		// factory implementation
		$createImpl=$this->generateCreateMethod($className, $def, $class);
		$createImpl->setVisibility('public');
		$createImpl->addBody('return callback($this->container, ?)->invokeArgs(func_get_args());', array(
			\Nette\DI\Container::getMethodName($serviceName, FALSE),
			));

		// invocation of factory
		$class->methods['__invoke']= $invokeImpl= clone $createImpl;
		$invokeImpl->name='__invoke';

		return array($class, $interface);
	}

	/**
	 * @param string $returnType
	 * @param \Nette\DI\ServiceDefinition $def
	 * @param PhpGenerator\ClassType $type
	 * @return PhpGenerator\Method
	 */
	private static function generateCreateMethod($returnType, ServiceDefinition $def, PhpGenerator\ClassType $type)
	{
		$create=$type->addMethod('create');
		foreach ($def->parameters as $k => $v) {
			$tmp=explode(' ', is_int($k)? $v : $k);
			$paramName=end($tmp);
			$defaultValue=is_int($k)? NULL : $v;

			$create->addParameter($paramName, $defaultValue);
			$create->addDocument('@param $'.$paramName);
			}

		$create->addDocument('@return \\'.$returnType);

		return $create;
	}

	/**
	 * @param array|PhpGenerator\ClassType $classes
	 * @return PhpGenerator\ClassType
	 */
	private static function namespaceClasses(array $classes)
	{
		$namespaced=array();
		foreach ($classes as $type) {
			$namespace=NULL;

			/** @var PhpGenerator\ClassType $type */
			if (($pos=strrpos($type->name, '\\'))!==FALSE) {
				$namespace=substr($type->name, 0, $pos);
				$type->setName(substr($type->name, $pos+1));
				}

			$namespaced[$namespace][]=$type;
			}

		ksort($namespaced);
		return array_reverse($namespaced, TRUE);
	}

	/**
	 * @param array|PhpGenerator\ClassType $namespaced
	 * @return string
	 */
	private static function generateCode(array $namespaced)
	{
		$code=array();
		foreach ($namespaced as $namespace => $classes) {
			$code[]='namespace '.$namespace.' {';
			$code=array_merge($code, $classes);
			$code[]='}';
			}

		return "<?php\n\n".implode("\n\n\n", $code);
	}

	/**
	 * @return \Nette\Caching\Cache
	 */
	private function getCache()
	{
		$cacheDir=$this->getContainerBuilder()->expand('%tempDir%/cache');
		return new \Nette\Caching\Cache(new \Nette\Caching\Storages\PhpFileStorage($cacheDir), 'Nette.DicFactory');
	}
}
