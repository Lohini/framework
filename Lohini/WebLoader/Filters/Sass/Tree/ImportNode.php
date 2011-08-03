<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\WebLoader\Filters\Sass\Tree;
/**
 * SassImportNode class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Lohini\WebLoader\Filters\Sass;

/**
 * ImportNode class.
 * Represents a CSS Import.
 */
class ImportNode
extends Node
{
	const IDENTIFIER='@';
	const MATCH='/^@import\s+(.+)/i';
	const MATCH_CSS='/^(.+\.css|url\(.+\)|.+" \w+|"http)/im';
	const FILES=1;

	/** @var array files to import */
	private $files=array();


	/**
	 * @param object $token source token
	 */
	public function __construct($token)
	{
		parent::__construct($token);
		preg_match(self::MATCH, $token->source, $matches);
		foreach (explode(',', $matches[self::FILES]) as $file) {
			$this->files[]=trim($file);
			}
	}

	/**
	 * Parse this node.
	 * If the node is a CSS import return the CSS import rule.
	 * Else returns the rendered tree for the file.
	 * @param Context $context the context in which this node is parsed
	 * @return array the parsed node
	 * @throws ImportNodeException
	 */
	public function parse($context)
	{
		$imported=array();
		foreach ($this->files as $file) {
			if (preg_match(self::MATCH_CSS, $file)) {
				return "@import $file";
				}
			else {
				$file=trim($file, '\'"');
				$tree=Sass\File::getTree(Sass\File::getFile($file, $this->parser), $this->parser);
				if (empty($tree)) {
					throw new ImportNodeException("Unable to create document tree for $file", $this);
					}
				else {
					$imported=array_merge($imported, $tree->parse($context)->children);
					}
				}
			}
		return $imported;
	}
}
