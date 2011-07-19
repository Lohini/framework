<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license GNU GPL v3
 */
namespace BailIff\WebLoader\Filters\Sass;
/**
 * SassNode exception classes.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 */
/**
 * BailIff port
 * @author Lopo <lopo@losys.eu>
 */

/**
 * NodeException class
 */
class NodeException
extends \BailIff\WebLoader\Filters\Sass\Exception
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
