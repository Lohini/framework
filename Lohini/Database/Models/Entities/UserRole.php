<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Database\Models\Entities;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * User role entity
 *
 * @entity(repositoryClass="Lohini\Database\Models\Repositories\UserRole")
 * @table(name="acl_userroles")
 * @hasLifecycleCallbacks
 *
 * @property string $name
 * @property \Lohini\Database\Models\Entities\UserRole|NULL $parent
 * @property-read \Doctrine\Common\Collections\ArrayCollection $children
 * @property-read \Doctrine\Common\Collections\ArrayCollection $users
 */
class UserRole
extends \Lohini\Database\Doctrine\ORM\Entities\IdentifiedEntity
{
	/**
	 * @column(type="string", length=50, unique=true)
	 * @var string
	 */
	private $name;
	/**
	 * @column(nullable=true)
	 * @var string
	 */
	private $description;
	/**
	 * @manyToOne(targetEntity="UserRole", inversedBy="children")
	 * @joinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
	 * @var \Lohini\Database\Models\Entities\UserRole
	 */
	private $parent;
	/**
	 * @oneToMany(targetEntity="UserRole", mappedBy="parent", cascade={"all"})
	 * @var UserRole[]
	 */
	private $children;
	/**
	 * @oneToMany(targetEntity="User", mappedBy="role")
	 * @var User[]
	 */
	private $users;


	public function __construct()
	{
		parent::__construct();
		$this->users=new ArrayCollection;
		$this->children=new ArrayCollection;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param string $name
	 * @return \Lohini\Database\Models\Entities\UserRole (fluent)
	 */
	public function setName($name)
	{
		$this->name=$this->sanitizeString($name);
		return $this;
	}

	/**
	 * @return string
	 */
	public function getDescription()
	{
		return $this->description;
	}

	/**
	 * @param string $description
	 * @return \Lohini\Database\Models\Entities\UserRole (fluent)
	 */
	public function setDescription($description)
	{
		$this->description=$this->sanitizeString($description);
		return $this;
	}

	/**
	 * @return \Lohini\Database\Models\Entities\UserRole|NULL
	 */
	public function getParent()
	{
		return $this->parent;
	}

	/**
	 * @param \Lohini\Database\Models\Entities\UserRole $parent
	 * @return \Lohini\Database\Models\Entities\UserRole (fluent)
	 */
	public function setParent(UserRole $parent=NULL)
	{
		$this->parent=$parent;
		return $this;
	}

	/**
	 * @return \Doctrine\Common\Collections\ArrayCollection
	 */
	public function getChildren()
	{
		return $this->children;
	}

	/**
	 * @return \Doctrine\Common\Collections\ArrayCollection
	 */
	public function getUsers()
	{
		return $this->users;
	}
}
