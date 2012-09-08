<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Database\Doctrine\Mapping;
/**
 * @author Filip Procházka <filip@prochazka.su>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 */
class ClassMetadata
extends \Doctrine\ORM\Mapping\ClassMetadata
{
	/** @var string */
	public $customRepositoryClassName='Lohini\Database\Doctrine\Dao';
	/** @var bool */
	public $auditChanges=FALSE;
	/** @var array */
	public $auditRelations=array();


	/**
	 * @return bool
	 */
	public function isAudited()
	{
		return $this->auditChanges;
	}

	/**
	 * @param bool $audited
	 */
	public function setAudited($audited=TRUE)
	{
		$this->auditChanges=$audited;
	}

	/**
	 * @return array
	 */
	public function __sleep()
	{
		return array_merge(
			parent::__sleep(),
			array('auditChanges', 'auditRelations')
			);
	}
}
