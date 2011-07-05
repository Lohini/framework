<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace BailIff\Components\TreeView\Renderers;
/**
 * TreeView control
 *
 * Copyright (c) 2009, 2010 Roman Novák
 *
 * This source file is subject to the New-BSD licence.
 *
 * For more information please see http://nettephp.com
 *
 * @copyright  Copyright (c) 2009, 2010 Roman Novák
 * @license    New-BSD
 */
/**
 * @author     Roman Novák
 * @copyright  Copyright (c) 2009, 2010 Roman Novák
 */
/**
 * BailIff port
 * @author Lopo <lopo@losys.eu>
 */

/**
 * TreeView renderer interface.
 */
interface IRenderer
{
	function render(\BailIff\Components\TreeView\TreeView $node);
}
