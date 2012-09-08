<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Config;
/**
 * @author Filip Procházka <filip@prochazka.su>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 * @method \Nette\DI\ContainerBuilder getContainerBuilder()
 */
class CompilerExtension
extends \Nette\Config\CompilerExtension
implements \Lohini\Packages\IPackageAware
{
	/** @var \Lohini\Packages\Package */
	private $package;


	/**
	 * @internal
	 * @param \Lohini\Packages\Package $package
	 */
	public function setPackage(\Lohini\Packages\Package $package)
	{
		$this->package=$package;
	}

	/**
	 * Tries to load default '<compilerExtName>.neon' configuration file
	 *
	 * @return \Nette\DI\ContainerBuilder
	 */
	public function loadConfiguration()
	{
		if (!$this->package) {
			return $this->getContainerBuilder();
			}

		$configDir=$this->package->getPath().'/Resources/config';
		if (file_exists($configFile=$configDir.'/'.$this->name.'.neon')) {
			$this->compiler->parseServices(
				$this->getContainerBuilder(),
				$this->loadFromFile($configFile)
				);
			}

		return $this->getContainerBuilder();
	}

	/**
	 * @param string $alias
	 * @param string $service
	 * @return \Nette\DI\ServiceDefinition
	 */
	public function addAlias($alias, $service)
	{
		$def=$this->getContainerBuilder()
			->addDefinition($alias);
		$def->setFactory('@'.$service);
		return $def;
	}

	/**
	 * Supply the name, and installer in format Class::install
	 * Installer method will receive Latter\Parser as first argument
	 *
	 * @param string $name
	 * @param string $installer
	 * @return \Nette\DI\ServiceDefinition
	 */
	public function addMacro($name, $installer)
	{
		$builder=$this->getContainerBuilder();

		$macro=$builder->addDefinition($name=$this->prefix($name))
			->setClass(substr($installer, 0, strpos($installer, '::')))
			->setFactory($installer, array('%compiler%'))
			->setParameters(array('compiler'))
			->addTag('latte.macro');

		$builder->getDefinition('nette.latte')
			->addSetup('$this->'.\Nette\DI\Container::getMethodName($name, FALSE).'(?->compiler)', array('@self'));
		return $macro;
	}

	/**
	 * Intersects the keys of defaults and given options and returns only not NULL values.
	 *
	 * @param array $given	   Configurations options
	 * @param array $defaults  Defaults
	 * @param bool $keepNull
	 * @return array
	 */
	public static function getOptions(array $given, array $defaults, $keepNull=FALSE)
	{
		$options=array_intersect_key($given, $defaults)+$defaults;

		if ($keepNull===TRUE) {
			return $options;
			}

		return array_filter(
			$options,
			function($value) { return $value!==NULL; }
			);
	}
}
