<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Database\Models\Entities;
/**
 * This file is part of the Nella Framework.
 *
 * Copyright (c) 2006, 2011 Patrik Votoček (http://patrik.votocek.cz)
 *
 * This source file is subject to the GNU Lesser General Public License. For more information please see http://nella-project.org
 * @author	Patrik Votoček
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 * Identity entity
 *
 * @entity
 * @table(name="acl_useridentities")
 * @service(class="Lohini\Database\Models\Services\Identities")
 * @hasLifecycleCallbacks
 *
 * @property UserRole $role
 * @property string $lang
 * @property string $displayName
 * @property string $skin
 * @property DateTime $lastIn
 */
class Identity
extends \Lohini\Database\Doctrine\ORM\Entities\BaseEntity
implements \Lohini\Database\Models\IEntity, \Nette\Security\IIdentity, \Serializable
{
	/**
	 * @id @generatedValue
	 * @column(type="integer")
	 * @var int
	 */
	private $id;
	/**
	 * @manyToOne(targetEntity="UserRole", fetch="EAGER")
	 * @joinColumn(name="role_id", referencedColumnName="id", nullable=false)
	 * @var UserRole
	 */
	private $role;
	/**
	 * @column(type="string", length=5, nullable=true)
	 * @var string
	 */
	private $lang;
	/**
	 * @column(name="displayname", type="string", length=50, nullable=true, unique=true)
	 * @var string
	 */
	private $displayName;
	/**
	 * @internal
	 * @var bool
	 */
	private $loaded=FALSE;
	/**
	 * @column(type="string", length=50, nullable=true)
	 * @var string
	 */
	private $skin;
	/**
	 * @column(name="lastin", type="datetime", nullable=true)
	 * @var DateTime
	 */
	private $lastIn;


	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @return \Lohini\Database\Models\Entities\User
	 */
	public function getUser()
	{
		return $this->user;
	}

	/**
	 * @return UserRole
	 */
	public function getRole()
	{
		return $this->role;
	}

	/**
	 * @param UserRole
	 * @return Identity (fluent)
	 */
	public function setRole(UserRole $role)
	{
		$this->role=$role;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getLang()
	{
		return $this->lang;
	}

	/**
	 * @param string
	 * @return Identity (fluent)
	 */
	public function setLang($lang)
	{
		$this->lang=$this->sanitizeString($lang);
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getDisplayName()
	{
		return $this->displayName;
	}
	
	/**
	 * @param string
	 * @return Identity (fluent)
	 */
	public function setDisplayName($displayName)
	{
		$this->displayName=$this->sanitizeString($displayName);
		return $this;
	}

	/**
	 * @return string
	 */
	public function getSkin()
	{
		return $this->skin;
	}

	/**
	 *
	 * @param type $skin
	 * @return \Lohini\Database\Models\Entities\User (fluent)
	 */
	public function setSkin($skin)
	{
		$this->skin=$this->sanitizeString($email);
		return $this;
	}

	/**
	 * @return DateTime
	 */
	public function getLastIn()
	{
		return \Nette\DateTime::from($this->lastIn);
	}

	/**
	 * @param type $time
	 * @return \Lohini\Database\Models\Entities\User (fluent)
	 */
	public function setLastIn($time)
	{
		$this->lastIn=\Nette\DateTime::from($time);
		return $this;
	}

	/** serialization */
	/**
	 * @return string
	 */
	public function serialize()
	{
		return serialize($this->getId());
	}

	/**
	 * @param string
	 */
	public function unserialize($serialized)
	{
		$this->id=unserialize($serialized);
		$this->loaded=FALSE;
	}

	/**
	 * @param \Nella\Doctrine\Container
	 * @return IdentityEntity
	 */
	public function load(\Lohini\Database\Doctrine\ORM\Container $container)
	{
		if (!$this->loaded) {
			$entity=$container->getModelService(__CLASS__)->repository->find($this->getId());
			$entity->loaded=TRUE;
			return $entity;
			}
		return $this;
	}

	/** IIdentity implementation */
	/**
	 * @internal
	 * @return array
	 */
	public function getRoles()
	{
		return array($this->getRole());
	}
}
