<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Application;

use Nette\Utils\Strings;

/**
 * @author Lopo <lopo@lohini.net>
 */
class PresenterFactory
extends \Nette\Object
implements \Nette\Application\IPresenterFactory
{
	/** @var \Nette\DI\IContainer */
	private $container;
	/** @var array */
	public static $presenters=array(
		'plugin_module' => array(
			'prefix' => "LohiniPlugins\\",
			'format' => '{0}\{tmp}',
			'replace' => array(
				0 => NULL,
				':' => "Module\\",
				)
			),
		'plugin' => array(
			'prefix' => "LohiniPlugins\\",
			'format' => '{tmp}',
			'replace' => array(
				':' => "\\Presenters\\"
				)
			),
		'app' => array(
			'prefix' => "App\\",
			'format' => '{tmp}',
			'replace' => array(
				':' => "Module\\"
				)
			),
		'fw' => array(
			'prefix' => "Lohini\\Presenters\\",
			'format' => '{tmp}',
			'replace' => array(
				':' => "\\"
				)
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
	 * @return \Nette\Application\IPresenter
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
			throw InvalidPresenterException::invalidName($name);
			}
		$class=$this->formatPresenterClasses($name);
		$reflection=\Nette\Reflection\ClassType::from($class);
		$class=$reflection->getName();
		if (!$reflection->implementsInterface('Nette\Application\IPresenter')) {
			throw InvalidPresenterException::notImplementor($name, $class);
			}
		if ($reflection->isAbstract()) {
			throw InvalidPresenterException::isAbstract($name, $class);
			}

		// canonicalize presenter name
		$realName=$this->unformatPresenterClass($class);
		if ($name!==$realName) {
			throw InvalidPresenterException::caseMismatch($name, $realName);
			}
		$this->cache[$name]=array($class, $realName);
		return $class;
	}

	/**
	 * Formats presenter class name from its name.
	 * @param string $presenter
	 * @param string $type
	 * @return string
	 */
	public function formatPresenterClass($presenter, $type='app')
	{
		if (isset(static::$presenters[$type])) {
			$epni= $epn= explode(':', $presenter);
			foreach (static::$presenters[$type]['replace'] as $k => $v) {
				if (is_int($k)) {
					if ($v===NULL) {
						unset($epni[$k]);
						}
					else {
						$epni[$k]=$v;
						}
					}
				}
			$tmp=implode(':', $epni);
			$format=static::$presenters[$type]['format'];
			foreach (static::$presenters[$type]['replace'] as $search => $replace) {
				if (is_string($search)) {
					$tmp=str_replace($search, $replace, $tmp);
					}
				elseif (is_int($search)) {
					$format=str_replace('{'."$search}", $epn[$search], $format);
					}
				}
			$class=static::$presenters[$type]['prefix'].str_replace('{tmp}', $tmp, $format).'Presenter';
			return $class;
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
			if (Strings::startsWith($class, $presenter['prefix'])) {
				return $presenter;
				}
			};
		if (count($presenters=array_filter(static::$presenters, $mapper))) {
			$prefix=current($presenters);
			$presenter=substr($class, $class[0]=="\\"? (strlen($prefix['prefix'])+1) : strlen($prefix['prefix']), -9);
			foreach ($prefix['replace'] as $value => $search) {
				$presenter=str_replace($search, $value, $presenter);
				}
			if (!Strings::startsWith(key($presenters), 'plugin')) {
				return $presenter;
				}
			$epn=preg_split('/[:\\\\]/', $presenter);
			if (($i=array_search('Presenters', $epn))!==FALSE) {
				unset($epn[$i]);
				}
			$presenter=implode(':', $epn);
			return $presenter;
			}
		else {
			return str_replace("\\", ':', substr($class, $class[0]=="\\"? 1 : 0, -9)); // Module\\ ?
			}
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
		throw InvalidPresenterException::notFound($name, $this->formatPresenterClass($name));
	}
}
