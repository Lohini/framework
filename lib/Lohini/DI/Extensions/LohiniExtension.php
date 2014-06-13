<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini (http://lohini.net)
 *
 * @copyright (c) 2010, 2014 Lopo <lopo@lohini.net>
 */
namespace Lohini\DI\Extensions;

use Nette\DI\ContainerBuilder,
	Nette\Utils\Validators;

/**
 * Lohini Framework extension
 * 
 * @author Lopo <lopo@lohini.net>
 */
class LohiniExtension
extends \Nette\DI\CompilerExtension
{
	const DEFAULT_NAME='lohini';

	/** @var array */
	public $defaults=[
		'templating' => [
			'dirs' => [
				'%appDir%' => 2
				],
			'skin' => NULL,
			'debugger' => TRUE
			]
		];


	public function loadConfiguration()
	{
		$builder=$this->getContainerBuilder();
		$config=$this->getConfig($this->defaults);

		$this->setupApplication($builder);
		$this->setupTemplating($builder, $config['templating']);
	}

	/**
	 * @param \Nette\DI\ContainerBuilder $container
	 * @param array $config
	 */
	private function setupApplication(ContainerBuilder $container)
	{
		if ($container->hasDefinition('nette.presenterFactory')) {
			$container->getDefinition('nette.presenterFactory')
				->addSetup('setMapping', [['Lohini' => 'LohiniModule\\*\\*Presenter']]);
			}
	}

	/**
	 * @param \Nette\DI\ContainerBuilder $container
	 * @param array $config
	 */
	private function setupTemplating(ContainerBuilder $container, array $config)
	{
		$def=$container->addDefinition($this->prefix('templateFilesFormatter'))
			->setClass('Lohini\Templating\TemplateFilesFormatter')
			->addSetup('$skin', [$config['skin']]);
		foreach ($config['dirs'] as $dir => $priority) {
			if (Validators::isNumericInt($dir)) {
				$def->addSetup('addDir', [$priority]);
				}
			else {
				$def->addSetup('addDir', [$container->expand($dir), $priority]);
				}
			}

		if ($container->hasDefinition('nette.latte')) {
			$container->getDefinition('nette.latte')
				->addSetup('Lohini\Latte\Macros\UIMacros::factory', ['@self']);
			}
	}
}
