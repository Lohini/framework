<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\WebLoader\Filters\Sass\Tree;
/**
 * SassNode exception classes.
 * @author Chris Yates <chris.l.yates@gmail.com>
 * @copyright Copyright (c) 2010 PBM Web Development
 * @license http://phamlp.googlecode.com/files/license.txt
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 * NodeException class
 */
class NodeException
extends \Lohini\WebLoader\Filters\Sass\Exception
{}

/**
 * ContextException class
 */
class ContextException
extends NodeException
{}

/**
 * CommentNodeException class
 */
class CommentNodeException
extends NodeException
{}

/**
 * DebugNodeException class
 */
class DebugNodeException
extends NodeException
{}

/**
 * DirectiveNodeException class
 */
class DirectiveNodeException
extends NodeException
{}

/**
 * EachNodeException class
 */
class EachNodeException
extends NodeException
{}

/**
 * ExtendNodeException class
 */
class ExtendNodeException
extends NodeException
{}

/**
 * ForNodeException class
 */
class ForNodeException
extends NodeException
{}

/**
 * IfNodeException class
 */
class IfNodeException
extends NodeException
{}

/**
 * ImportNodeException class
 */
class ImportNodeException
extends NodeException
{}

/**
 * MixinDefinitionNodeException class
 */
class MixinDefinitionNodeException
extends NodeException
{}

/**
 * MixinNodeException class
 */
class MixinNodeException
extends NodeException
{}

/**
 * PropertyNodeException class
 */
class PropertyNodeException
extends NodeException
{}

/**
 * RuleNodeException class
 */
class RuleNodeException
extends NodeException
{}

/**
 * VariableNodeException class
 */
class VariableNodeException
extends NodeException
{}

/**
 * WhileNodeException class
 */
class WhileNodeException
extends NodeException
{}

/**
 * FunctionNodeException class
 */
class FunctionNodeException
extends NodeException
{}
