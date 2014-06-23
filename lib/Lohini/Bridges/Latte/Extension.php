<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini (http://lohini.net)
 *
 * @copyright (c) 2010, 2014 Lopo <lopo@lohini.net>
 */
namespace Lohini\Bridges\Latte;


class Extension
extends \Nette\DI\CompilerExtension
{
	public function loadConfiguration()
	{
		$builder=$this->getContainerBuilder();

		$latte= $builder->hasDefinition('nette.latteFactory')
			? $builder->getDefinition('nette.latteFactory')
			: $builder->getDefinition('nette.latte');
		$latte->addSetup('Lohini\Latte\Macros\CoreMacros::install(?->getCompiler())', ['@self']);
		foreach (\Nette\Reflection\ClassType::from('Lohini\Latte\Filters')->getMethods() as $method) {
			$latte->addSetup('addFilter', [$method->name, ['Lohini\Latte\Filters', $method->name]]);
			}
	}
}
