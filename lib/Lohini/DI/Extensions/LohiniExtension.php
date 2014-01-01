<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini (http://lohini.net)
 *
 * @copyright (c) 2010, 2014 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
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
		'application' => [
			],
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

		$this->setupApplication($builder, $config['application']);
		$this->setupTemplating($builder, $config['templating']);
	}

	/**
	 * @param \Nette\DI\ContainerBuilder $container
	 * @param array $config
	 */
	private function setupApplication(ContainerBuilder $container, array $config)
	{
		if ($container->hasDefinition('nette.presenterFactory')) {
			$container->getDefinition('nette.presenterFactory')
				->addSetup('setMapping', [['Lohini' => 'LohiniModule\\*\\*Presenter']]);
			}
		$container->getDefinition('application')
			->addSetup('!headers_sent() && header(?, TRUE);', ['X-Powered-By: Nette Framework with Lohini Framework']);
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
