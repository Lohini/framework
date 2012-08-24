<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Application;
/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Nette\Utils\Strings,
	Nette\DI\Container,
	Nette\Reflection\ClassType;

/**
 */
class PresenterManager
extends \Nette\Application\PresenterFactory
implements \Nette\Application\IPresenterFactory
{
	/** @var Container */
	private $container;
	/** @var \Lohini\Packages\PackageManager */
	private $packageManager;


	/**
	 * @param string $appDir
	 * @param Container $container
	 * @param \Lohini\Packages\PackageManager $packageManager
	 */
	public function __construct($appDir, Container $container, \Lohini\Packages\PackageManager $packageManager)
	{
		parent::__construct($appDir, $container);

		$this->container=$container;
		$this->packageManager=$packageManager;
	}

	/**
	 * @param string presenter name
	 * @return string class name
	 * @throws InvalidPresenterException
	 */
	public function getPresenterClass(& $name)
	{
		if (!is_string($name) || !Strings::match($name, "#^[a-zA-Z\x7f-\xff][a-zA-Z0-9\x7f-\xff:]*$#")) {
			throw InvalidPresenterException::invalidName($name);
			}

		if (Strings::match($name, '~^[^:]+Package:[^:]+~i')) {
			$serviceName=$this->formatServiceNameFromPresenter($name);
			if (method_exists($this->container, $method='create'.ucfirst($serviceName))) {
				$factoryRefl=$this->container->getReflection()->getMethod($method);
				if ($returnType=$factoryRefl->getAnnotation('return')) {
					return $returnType; // todo: verify
					}
				}
			elseif ($this->container->hasService($serviceName)) {
				$reflection=new ClassType($this->container->getService($serviceName));
				return $reflection->getName();
				}

			list($package, $shortName)=explode(':', $name, 2);

			$class=$this->formatPackageClassFromPresenter($shortName, $this->packageManager->getPackage(substr($package, 0, -7)));
			if (!class_exists($class)) {
				throw InvalidPresenterException::notFound($shortName, $class);
				}

			$reflection=new ClassType($class);
			$class=$reflection->getName();

			if (!$reflection->implementsInterface('Nette\Application\IPresenter')) {
				throw InvalidPresenterException::notImplementor($name, $class);
				}

			if ($reflection->isAbstract()) {
				throw InvalidPresenterException::isAbstract($name, $class);
				}

			// canonicalize presenter name
			if ($name!==$realName=$this->formatPackagePresenterFromClass($class)) {
				if ($this->caseSensitive) {
					throw InvalidPresenterException::caseMismatch($name, $realName);
					}
				else {
					$name=$realName;
					}
				}
			return $class;
			}

		return parent::getPresenterClass($name);
	}

	/**
	 * Finds presenter service in DI Container, or creates new object
	 * @param string $name
	 * @return \Nette\Application\IPresenter
	 */
	public function createPresenter($name)
	{
		/** @var \Nette\Application\UI\Presenter $presenter */
		$serviceName=$this->formatServiceNameFromPresenter($name);
		if (method_exists($this->container, $method=Container::getMethodName($serviceName, FALSE))) {
			$presenter=$this->container->{$method}();
			}
		elseif ($this->container->hasService($serviceName)) {
			$presenter=$this->container->getService($serviceName);
			}
		else {
			$class=$this->getPresenterClass($name);
			$presenter=$this->container->createInstance($class);
		}

		foreach (array_reverse(get_class_methods($presenter)) as $method) {
			if (substr($method, 0, 6) === 'inject') {
				$this->container->callMethod(array($presenter, $method));
			}
		}

		if (method_exists($presenter, 'setTemplateConfigurator') && $this->container->hasService('lohini.templateConfigurator')) {
			$presenter->setTemplateConfigurator($this->container->lohini->templateConfigurator);
			}

		if (method_exists($presenter, 'setContext')) {
			$presenter->setContext($this->container);
			}

		return $presenter;
	}

	/**
	 * @param string $presenterClass
	 * @return \Lohini\Packages\Package
	 * @throws \Nette\InvalidArgumentException
	 */
	public function getPresenterPackage($presenterClass)
	{
		foreach ($this->packageManager->getPackages() as $package) {
			if (Strings::startsWith($presenterClass, $package->getNamespace())) {
				return $package;
				}
			}

		throw new \Nette\InvalidArgumentException("Presenter '$presenterClass' does not belong to any active package.");
	}

	/**
	 * Formats service name from it's presenter name
	 *
	 * 'Bar:Foo:FooBar' => 'bar_foo_fooBarPresenter'
	 *
	 * @param string $presenter
	 * @return string
	 */
	public function formatServiceNameFromPresenter($presenter)
	{
		return Strings::replace(
				$presenter,
				'/(^|:)+(.)/',
				function($match) {
					return (':'===$match[1]? '.' : '').strtolower($match[2]);
					}
				).'Presenter';
	}

	/**
	 * Formats presenter name from it's service name
	 *
	 * 'bar_foo_fooBarPresenter' => 'Bar:Foo:FooBar'
	 *
	 * @param string $name
	 * @return string
	 */
	public function formatPresenterFromServiceName($name)
	{
		return Strings::replace(
				substr($name, 0, -9),
				'/(^|\\.)+(.)/',
				function($match) {
					return ('.'===$match[1]? ':' : '').strtoupper($match[2]);
					}
				);
	}

	/**
	 * Formats presenter class to it's name
	 *
	 * 'Lohini\BarPackage\Presenter\FooFooPresenter' => 'Bar:FooFoo'
	 * 'Lohini\BarPackage\Presenter\FooModule\FooBarPresenter' => 'Bar:Foo:FooBar'
	 *
	 * @param string $class
	 * @return string
	 */
	public function formatPackagePresenterFromClass($class)
	{
		$package=$this->getPresenterPackage($class);
		return $package->getName().'Package:'.$this->unformatPresenterClass(substr($class, strlen($package->getNamespace().'\\Presenter\\')));
	}

	/**
	 * @param string $presenter
	 * @param \Lohini\Packages\Package $package
	 */
	public function formatPackageClassFromPresenter($presenter, \Lohini\Packages\Package $package)
	{
		return $package->getNamespace().'\\Presenter\\'.$this->formatPresenterClass($presenter);
	}
}
