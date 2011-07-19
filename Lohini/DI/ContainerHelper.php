<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\DI;
/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 * @author Patrik Votocek
 * @author Filip Procházka
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

class ContainerHelper
extends \Symfony\Component\Console\Helper\Helper
{
	/** @var \Lohini\DI\Container */
    protected $container;


    /**
     * @param \Lohini\DI\Container
     */
    public function __construct(Container $container)
    {
        $this->container=$container;
    }

	/**
     * @return \Lohini\DI\Container
     */
    public function getContainer()
    {
        return $this->container;
    }

	/**
     * @see Helper
     */
    public function getName()
    {
        return 'diContainer';
    }
}
