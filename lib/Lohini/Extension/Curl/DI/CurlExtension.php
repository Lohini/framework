<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Extension\Curl\DI;
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

/**
 */
class CurlExtension
extends \Nette\Config\CompilerExtension
{

	public function loadConfiguration()
	{
		$builder=$this->getContainerBuilder();

		$builder->addDefinition($this->prefix('curl'))
			->setClass('Lohini\Extension\Curl\CurlSender');

		$builder->addDefinition($this->prefix('curl.panel'))
			->setFactory('Lohini\Extension\Curl\Diagnostics\Panel::register')
			->addTag('run', TRUE);
	}
}
