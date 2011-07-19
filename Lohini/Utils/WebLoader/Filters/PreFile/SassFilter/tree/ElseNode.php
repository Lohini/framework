<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\WebLoader\Filters\Sass;
/**
 * SassElseNode class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 * ElseNode class.
 * Represents Sass Else If and Else statements.
 * Else If and Else statement nodes are chained below the If statement node.
 */
class ElseNode
extends IfNode
{
	/**
	 * @param object $token source token
	 */
	public function __construct($token)
	{
		parent::__construct($token, FALSE);
	}
}
