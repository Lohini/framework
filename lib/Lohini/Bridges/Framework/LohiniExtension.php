<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini (http://lohini.net)
 *
 * @copyright (c) 2010, 2014 Lopo <lopo@lohini.net>
 */
namespace Lohini\Bridges\Framework;

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
	/** @var array */
	private $bridges=[
		'lohiniLatte' => 'Lohini\Bridges\Latte\Extension'
		];


	public function loadConfiguration()
	{
		if (PHP_VERSION_ID<50400) {
			throw new \Exception('Lohini Framework requires PHP 5.4 or newer.');
			}

		$builder=$this->getContainerBuilder();
		$config=$this->getConfig($this->defaults);

		foreach ($this->bridges as $name => $extension) {
			if (class_exists($extension)) {
				$this->compiler->addExtension($name, new $extension);
				}
			}

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
			$def->addSetup(
				'addDir',
				Validators::isNumericInt($dir)
					? [$priority]
					: [$container->expand($dir), $priority]
				);
			}
	}
}
