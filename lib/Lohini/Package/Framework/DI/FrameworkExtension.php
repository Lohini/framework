<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Package\Framework\DI;
/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip@prochazka.su)
 *
 * @license http://www.kdyby.org/license
 * @author Filip Procházka <filip@prochazka.su>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Nette\DI\ContainerBuilder,
	Nette\Reflection\ClassType,
	Nette\Utils\PhpGenerator;

/**
 */
class FrameworkExtension
extends \Lohini\Config\CompilerExtension
{
	public function loadConfiguration()
	{
		parent::loadConfiguration();

		$builder=$this->getContainerBuilder();

		// watch for package files to change
		foreach ($builder->parameters['lohini']['packages'] as $packageClass) {
			$builder->addDependency(ClassType::from($packageClass)->getFileName());
			}

		foreach ($this->compiler->getExtensions() as $extension) {
			$builder->addDependency(ClassType::from($extension)->getFileName());
			}

		// macros
		$this->addMacro('macros.core', 'Lohini\Templating\CoreMacros::install');
	}

	public function beforeCompile()
	{
		$builder=$this->getContainerBuilder();

		$this->registerConsoleHelpers($builder);
		$this->unifyComponents($builder);

		$routes=array();
		foreach ($builder->findByTag('route') as $route => $meta) {
			$priority= isset($meta['priority'])? $meta['priority'] : (int)$meta;
			$routes[$priority][]=$route;
			}

		krsort($routes);
		$router=$builder->getDefinition('router');
		foreach (\Lohini\Utils\Arrays::flatMap($routes) as $route) {
			$router->addSetup('$service[] = $this->getService(?)', array($route));
			}

		$this->registerEventSubscribers($builder);
	}

	/**
	 * @param ContainerBuilder $builder
	 */
	protected function registerConsoleHelpers(ContainerBuilder $builder)
	{
		/** @var \Nette\DI\ServiceDefinition $helpers */
		$helpers=$builder->getDefinition($this->prefix('console.helpers'));

		foreach ($builder->findByTag('console.helper') as $helper => $meta) {
			$alias= isset($meta['alias'])? $meta['alias'] : NULL;
			$helpers->addSetup('set', array('@'.$helper, $alias));
			}
	}

	/**
	 * Unifies component & presenter definitions using tags.
	 *
	 * @param ContainerBuilder $builder
	 */
	protected function unifyComponents(ContainerBuilder $builder)
	{
		foreach ($builder->findByTag('component') as $name => $meta) {
			/** @var \Nette\DI\ServiceDefinition $component */
			$component=$builder->getDefinition($name);

			if (!$component->parameters) {
				$component->setParameters(array());
				}
			else {
				$component->setAutowired(FALSE)->setShared(FALSE);
				}

			if ($this->componentHasTemplate($meta) && !$this->hasTemplateConfigurator($component)) {
				$component->addSetup('setTemplateConfigurator');
				}
			}
	}

	/**
	 * @param array $meta
	 * @return bool
	 */
	private function componentHasTemplate($meta)
	{
		return !isset($meta['template'])
			|| (isset($meta['template']) && $meta['template']===TRUE);
	}

	/**
	 * @param \Nette\DI\ServiceDefinition $def
	 * @return bool
	 */
	private function hasTemplateConfigurator(\Nette\DI\ServiceDefinition $def)
	{
		foreach ($def->setup as $setup) {
			if ($setup->entity==='setTemplateConfigurator') {
				return TRUE;
				}
			}
		return FALSE;
	}

	/**
	 * @param ContainerBuilder $builder 
	 */
	protected function registerEventSubscribers(ContainerBuilder $builder)
	{
		$evm=$builder->getDefinition($this->prefix('eventManager'));
		foreach ($builder->findByTag('lohini.eventSubscriber') as $listener => $meta) {
			$evm->addSetup('addEventSubscriber', array('@'.$listener));
			}
	}

	/**
	 * @param \Nette\Utils\PhpGenerator\ClassType $class
	 */
	public function afterCompile(PhpGenerator\ClassType $class)
	{
		$this->compileConfigurator($class);
		/** @var \Nette\Utils\PhpGenerator\Method $init */
		$init=$class->methods['initialize'];

		$config=$this->getConfig();
		if (!empty($config['debugger']['browser'])) {
			$init->addBody(
				'Lohini\Diagnostics\ConsoleDebugger::enable(?);',
				array($config['debugger']['browser'])
				);
			}
	}

	/**
	 * @param PhpGenerator\ClassType $class
	 */
	protected function compileConfigurator(PhpGenerator\ClassType $class)
	{
		$builder=$this->getContainerBuilder();
		/** @var \Nette\DI\ServiceDefinition $def */
		foreach ($builder->getDefinitions() as $name => $def) {
			if ($def->class=='Nette\DI\NestedAccessor' || $def->class==='Nette\Callback' || $name==='container' || !$def->shared) {
				continue;
				}

			$createBody=$class->methods[\Nette\DI\Container::getMethodName($name)]->body;
			if ($lines=\Nette\Utils\Strings::split($createBody, '~;[\n\r]*~mi')) {
				array_shift($lines); // naive: first line is creation

				/** @var \Nette\Utils\PhpGenerator\Method $configure */
				$configure = $class->addMethod('configure'.ucfirst(strtr($name, '.', '_')));
				$configure->visibility='private';
				$configure->addParameter('service')->typeHint=$def->class;
				$configure->setBody(implode(";\n", $lines));
				}
			}

		/** @var \Nette\Utils\PhpGenerator\Method $configure */
		$configure=$class->addMethod('configureService');
		$configure->addParameter('name');
		$configure->addParameter('service');
		$configure->setBody(
			'$this->{"configure".ucfirst(strtr($name, ".", "_"))}($service);'."\n"
			.'if ($this->hasService($name)) {'."\n"
				.'	$this->removeService($name);'."\n"
				.'}'."\n" .
			'$this->addService($name, $service);'
			);
	}
}
