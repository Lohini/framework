<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Localization;
/**
 * This file is part of the Nella Framework.
 *
 * Copyright (c) 2006, 2011 Patrik Votoček (http://patrik.votocek.cz)
 *
 * This source file is subject to the GNU Lesser General Public License. For more information please see http://nella-project.org
 * @author	Patrik Votocek
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 * Translator adapter
 */
interface ITranslator
extends \Nette\Localization\ITranslator
{
	/**
	 * @return array
	 */
	public function getDictionaries();
}
