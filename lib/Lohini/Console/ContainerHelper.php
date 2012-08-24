<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Console;
/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 */
class ContainerHelper
extends \Symfony\Component\Console\Helper\Helper
{
	/** @var \Nette\DI\Container */
	protected $container;


	/**
	 * @param \Nette\DI\Container $container
	 */
	public function __construct(\Nette\DI\Container $container)
	{
		$this->container=$container;
	}

	/**
	 * @return \Nette\DI\Container
	 */
	public function getContainer()
	{
		return $this->container;
	}

	/**
	 * @see \Symfony\Component\Console\Helper\Helper::getSelector()
	 */
	public function getName()
	{
		return 'diContainer';
	}
}
