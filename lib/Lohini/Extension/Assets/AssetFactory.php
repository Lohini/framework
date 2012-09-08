<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Extension\Assets;
/**
 * @author Filip Procházka <filip@prochazka.su>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 */
class AssetFactory
extends \Assetic\Factory\AssetFactory
{
	/** @var \SystemContainer|\Nette\DI\Container */
	private $container;
	/** @var array|\Lohini\Extension\Assets\IResourceResolver */
	private $resolvers=array();


	/**
	 * @param \Nette\DI\Container $container
	 * @param string $baseDir
	 */
	public function __construct(\Nette\DI\Container $container, $baseDir)
	{
		$this->container=$container;
		parent::__construct($baseDir, FALSE);
	}

	/**
	 * @param IResourceResolver $resolver 
	 */
	public function addResolver(IResourceResolver $resolver)
	{
		$this->resolvers[]=$resolver;
	}

	/**
	 * Adds support parameter placeholders and resource resolvers.
	 *
	 * @param string $input
	 * @param array $options
	 * @return \Assetic\Asset\AssetInterface
	 */
	protected function parseInput($input, array $options=array())
	{
		$input=$this->container->expand($input);

		foreach ($this->resolvers as $resolver) {
			/** @var IResourceResolver $resolver */
			if (($resolved=$resolver->locateResource($input, $options))!==FALSE) {
				$input=$resolved;
				break;
				}
			}

		return parent::parseInput($input, $options);
	}
}
