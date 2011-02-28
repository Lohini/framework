<?php // vim: set ts=4 sw=4 ai:
namespace BailIff\Application;

use Nette\Environment as NEnvironment,
	Nette\String,
	Nette\Loaders\LimitedScope,
	Nette\Reflection\ClassReflection,
	Nette\Application\InvalidPresenterException;

class PresenterFactory
extends \Nette\Application\PresenterFactory
{
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


	public function __construct()
	{
		parent::__construct(APP_DIR, NEnvironment::getApplication()->getContext());
	}

	/**
	 * @param string $name presenter name
	 * @return string class name
	 * @throws InvalidPresenterException
	 */
	public function getPresenterClass(& $name)
	{
		if (!is_string($name) || !String::match($name, "#^[a-zA-Z\x7f-\xff][a-zA-Z0-9\x7f-\xff:]*$#")) {
			throw new InvalidPresenterException("Presenter name must be alphanumeric string, '$name' is invalid.");
			}

		$class=$this->formatPresenterClasses($name);
		$reflection=new ClassReflection($class);
		$class=$reflection->getName();
		if (!$reflection->implementsInterface('Nette\Application\IPresenter')) {
			throw new InvalidPresenterException("Cannot load presenter '$name', class '$class' is not Nette\\Application\\IPresenter implementor.");
			}
		if ($reflection->isAbstract()) {
			throw new InvalidPresenterException("Cannot load presenter '$name', class '$class' is abstract.");
			}

		// canonicalize presenter name
		$realName=$this->unformatPresenterClass($class);
		if ($name!==$realName) {
			if ($this->caseSensitive) {
				throw new InvalidPresenterException("Cannot load presenter '$name', case mismatch. Real name is '$realName'.");
				}
			}
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
			if (String::startsWith($class, $presenter['prefix'])
				&& String::match($class, '/'.$presenter['replace'].(String::endsWith($presenter['replace'], "\\")? "\\" : '').'/')
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
	 * @param string
	 * @return string
	 * @throws InvalidPresenterException
	 */
	private function formatPresenterClasses($name)
	{
		$class=NULL;
		foreach (array_keys(static::$presenters) as $key) {
			$class=$this->formatPresenterClass($name, $key);
			if (class_exists($class)) {
				break;
				}
			}
		if (!class_exists($class)) {
			$class=$this->formatPresenterClass($name);
			throw new InvalidPresenterException("Cannot load presenter '$name', class '$class' was not found.");
			}
		return $class;
	}
}
