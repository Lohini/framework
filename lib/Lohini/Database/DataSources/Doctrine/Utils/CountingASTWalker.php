<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Database\DataSources\Doctrine\Utils;
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Doctrine\ORM\Query\AST;

class CountingASTWalker
extends \Doctrine\ORM\Query\TreeWalkerAdapter
{
	/**
	 * @param AST\SelectStatement $ast
	 */
	public function walkSelectStatement(AST\SelectStatement $ast)
	{
		$parent=$parentName=NULL;
		foreach ($this->_getQueryComponents() as $dqlAlias => $qComp) {
			if (array_key_exists('parent', $qComp) && $qComp['parent']===NULL && $qComp['nestingLevel']==0) {
				$parent=$qComp;
				$parentName=$dqlAlias;
				break;
				}
			}

		$pathExpression=new AST\PathExpression(
				AST\PathExpression::TYPE_STATE_FIELD | AST\PathExpression::TYPE_SINGLE_VALUED_ASSOCIATION,
				$parentName,
				$parent['metadata']->getSingleIdentifierFieldName()
				);
		$pathExpression->type=AST\PathExpression::TYPE_STATE_FIELD;

		$ast->selectClause->selectExpressions=array(
			new AST\SelectExpression(
				new AST\AggregateExpression('count', $pathExpression, FALSE),
				NULL
				)
			);
		$ast->orderByClause=array(); //reset ORDER BY clause, it is not necessary
	}
}
