<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Components\TreeView\Renderers;
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
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 * TreeView renderer interface.
 */
interface IRenderer
{
	function render(\Lohini\Components\TreeView\TreeView $node);
}
