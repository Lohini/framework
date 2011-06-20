<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace BailIff\Localization;
/**
 * This file is part of the Nella Framework.
 *
 * Copyright (c) 2006, 2011 Patrik Votoček (http://patrik.votocek.cz)
 *
 * This source file is subject to the GNU Lesser General Public License. For more information please see http://nella-project.org
 * @author	Patrik Votoček
 */
/**
 * BailIff port
 * @author Lopo <lopo@losys.eu>
 */

/**
 * Localization storage interface
 */
interface IStorage
{
	/**
	 * @param \BailIff\Localization\Dictionary
	 * @param string
	 */
	public function save(Dictionary $dictionary, $lang);
	
	/**
	 * @param string
	 * @return \BailIff\Localization\Dictionary
	 */
	public function load($lang, Dictionary $dictionary);
}
