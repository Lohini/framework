<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Templating;
/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 */
class TemplateConfigurator
extends \Nette\Object
{
	/** @var array */
	private $macroFactories=array();
	/** @var \SystemContainer|\Nette\DI\Container */
	private $container;
	/** @var \Nette\Latte\Engine */
	private $latte;


	/**
	 * @param \Nette\DI\Container $container
	 */
	public function __construct(\Nette\DI\Container $container)
	{
		$this->container=$container;
	}

	/**
	 * @param string $factory
	 */
	public function addFactory($factory)
	{
		$this->macroFactories[]=$factory;
	}

	/**
	 * @param \Nette\Templating\Template $template
	 */
	public function configure(\Nette\Templating\Template $template)
	{
		$template->registerHelperLoader('Lohini\Templating\Helpers::loader');

		if ($this->container->hasService('localization.translator')) {
			$template->setTranslator($this->container->getService('localization.translator'));
			}
	}

	/**
	 * @param \Nette\Latte\Engine $template
	 */
	public function prepareFilters(\Nette\Latte\Engine $latte)
	{
		$this->latte=$latte;
		foreach ($this->macroFactories as $factory) {
			if (!$this->container->hasService($factory)) {
				continue;
				}

			$this->container->$factory->invoke($this->latte->getCompiler());
			}
	}

	/**
	 * Returns Latter parser for the last prepareFilters call.
	 *
	 * @return \Nette\Latte\Engine
	 */
	public function getLatte()
	{
		return $this->latte;
	}
}
