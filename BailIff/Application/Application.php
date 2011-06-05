<?php // vim: set ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace BailIff\Application;
/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 * @author Patrik Votoček
 * @author Filip Procházka
 */
/**
 * BailIff port
 * @author Lopo <lopo@losys.eu>
 */

/**
 * @property-read \Nette\DI\Container $context
 */
class Application
extends \Nette\Application\Application
{
	public function run()
	{
		$this->context->freeze();
		if (PHP_SAPI=='cli') {
			return $this->contex->console->run();
			}
		return parent::run();
	}
}
