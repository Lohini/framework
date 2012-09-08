<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Package\Doctrine;
/**
 * @author Filip Procházka <filip@prochazka.su>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Doctrine\ORM\Tools\Console\Command as OrmCommand;

/**
 */
class Package
extends \Lohini\Packages\Package
{
	public function __construct()
	{
		$this->name='Doctrine';
	}

	/**
	 * Builds the Package. It is only ever called once when the cache is empty
	 *
	 * @param \Nette\Config\Configurator $config
	 * @param \Nette\Config\Compiler $compiler
	 * @param \Lohini\Packages\PackagesContainer $packages
	 */
	public function compile(\Nette\Config\Configurator $config, \Nette\Config\Compiler $compiler, \Lohini\Packages\PackagesContainer $packages)
	{
		$compiler->addExtension('annotation', new DI\AnnotationExtension);
		$compiler->addExtension('dbal', new DI\DbalExtension);
		$compiler->addExtension('orm', new DI\OrmExtension($packages));
		$compiler->addExtension('fixture', new DI\FixtureExtension);
		$compiler->addExtension('audit', new DI\AuditExtension);
		$compiler->addExtension('doctrine', new DI\DoctrineExtension);
	}

	/**
	 * Finds and registers Commands.
	 *
	 * @param \Symfony\Component\Console\Application $app
	 */
	public function registerCommands(\Symfony\Component\Console\Application $app)
	{
		parent::registerCommands($app);

		$app->addCommands(array(
			// ORM Commands
			new OrmCommand\GenerateProxiesCommand,
			new OrmCommand\ConvertMappingCommand,
			new OrmCommand\RunDqlCommand,
			new OrmCommand\ValidateSchemaCommand,
			new OrmCommand\InfoCommand,
			));
	}
}
