<?php // vim: set ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license GNU GPL v3
 */
namespace BailIff\Application;

use Nette\Utils\Strings;

/**
 * @author Lopo <lopo@losys.sk>
 */
class PresenterFactory
extends \Nette\Object
implements \Nette\Application\IPresenterFactory
{
	/** @var \Nette\DI\IContainer */
	private $container;
	/** @var array */
	public static $presenters=array(
		'app' => array(
			'prefix' => '',
			'replace' => "Module\\"
			),
		'fw' => array(
			'prefix' => "BailIff\\Presenters\\",
			'replace' => ''
			),
		);
	/** @var cache */
	private $cache=array();


	/**
	 * @param \Nette\DI\IContainer $container
	 */
	public function __construct(/*$baseDir, */\Nette\DI\IContainer $container)
	{
		$this->container=$container;
	}

	/**
	 * Creates new presenter instance
	 * @param  string  presenter name
	 * @return IPresenter
	 */
	public function createPresenter($name)
	{
		$class=$this->getPresenterClass($name);
		$presenter=new $class;
		$presenter->setContext($this->container);
		return $presenter;
	}

	/**
	 * @param string $name presenter name
	 * @return string class name
	 * @throws \Nette\Application\InvalidPresenterException
	 */
	public function getPresenterClass(& $name)
	{
		if (isset($this->cache[$name])) {
			list($class, $name)=$this->cache[$name];
			return $class;
			}
		if (!is_string($name) || !Strings::match($name, "#^[a-zA-Z\x7f-\xff][a-zA-Z0-9\x7f-\xff:]*$#")) {
			throw new \Nette\Application\InvalidPresenterException("Presenter name must be alphanumeric string, '$name' is invalid.");
			}

		$class=$this->formatPresenterClasses($name);
		$reflection=\Nette\Reflection\ClassType::from($class);
		$class=$reflection->getName();
		if (!$reflection->implementsInterface('Nette\Application\IPresenter')) {
			throw new \Nette\Application\InvalidPresenterException("Cannot load presenter '$name', class '$class' is not Nette\\Application\\IPresenter implementor.");
			}
		if ($reflection->isAbstract()) {
			throw new \Nette\Application\InvalidPresenterException("Cannot load presenter '$name', class '$class' is abstract.");
			}

		// canonicalize presenter name
		$realName=$this->unformatPresenterClass($class);
		if ($name!==$realName) {
			throw new \Nette\Application\InvalidPresenterException("Cannot load presenter '$name', case mismatch. Real name is '$realName'.");
			}
		$this->cache[$name]=array($class, $realName);
		return $class;
	}

	/**
	 * Formats presenter class name from its name.
	 * @param string $presenter
	 * @return string
	 */
	public function formatPresenterClass($presenter, $type='app')
	{
		if (isset(static::$presenters[$type])) {
			$epn=explode(':', $presenter);
			return static::$presenters[$type]['prefix'].str_replace(':', static::$presenters[$type]['replace'], $presenter.'Presenter');
			}
		else {
			return str_replace(':', "\\", $presenter).'Presenter';
			}
	}

	/**
	 * Formats presenter name from class name.
	 * @param string $class
	 * @return string
	 */
	public function unformatPresenterClass($class)
	{
		$mapper=function($presenter) use ($class) {
			if (Strings::startsWith($class, $presenter['prefix'])
				&& Strings::match($class, '/'.$presenter['replace'].(Strings::endsWith($presenter['replace'], "\\")? "\\" : '').'/')
				) {
				return $presenter;
				}
			};
		if (count($presenters=array_filter(static::$presenters, $mapper))) {
			$prefix=current($presenters);
			return str_replace($prefix['replace'], ':', substr($class, $class[0]=="\\"? (strlen($prefix['prefix'])+1) : strlen($prefix['prefix']), -9));
			}
		else {
			return str_replace("\\", ':', substr($class, $class[0]=="\\"? 1 : 0, -9)); // Module\\ ?
			}
	}

	/**
	 * Formats presenter class file name.
	 * @param string $presenter
	 * @return string
	 */
	public function formatPresenterFile($presenter)
	{
		$path='/'.str_replace(':', 'Module/', $presenter);
		return $this->baseDir.substr_replace($path, '/presenters', strrpos($path, '/'), 0).'Presenter.php';
	}

	/**
	 * Format presenter class with prefixes
	 * @param string $name
	 * @return string
	 * @throws \Nette\Application\InvalidPresenterException
	 */
	private function formatPresenterClasses($name)
	{
		$class=NULL;
		foreach (array_keys(static::$presenters) as $key) {
			$class=$this->formatPresenterClass($name, $key);
			if (class_exists($class)) {
				return $class;
				}
			}
		$class=$this->formatPresenterClass($name);
		throw new \Nette\Application\InvalidPresenterException("Cannot load presenter '$name', class '$class' was not found.");
	}
}
