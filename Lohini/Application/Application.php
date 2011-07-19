<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Application;
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
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 * @property-read \Nette\DI\Container $context
 */
class Application
extends \Nette\Application\Application
{
	public function run()
	{
		$this->getContext()->freeze();
		if (PHP_SAPI=='cli') {
			return $this->getContext()->console->run();
			}
		return parent::run();
	}
}
