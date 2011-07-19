<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Database\Models\Entities;

/**
 * Identity credentials entity
 *
 * @entity(repositoryClass="Lohini\Database\Models\Repositories\User")
 * @table(name="users")
 * @service(class="Lohini\Database\Models\Services\Users")
 *
 * @property \Lohini\Database\Models\Entities\Identity $identity
 * @property string $username
 * @property string $password
 * @property string $email
 */
class User
extends \Lohini\Database\Doctrine\ORM\Entities\IdentifiedEntity
implements \Lohini\Database\Models\IEntity
{
	/**
	 * @oneToOne(targetEntity="Identity", fetch="EAGER")
	 * @joinColumn(name="user_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
	 * @var \Lohini\Database\Models\Entities\Identity
	 */
	private $identity;
	/**
	 * @column(length=128, unique=true, nullable=false)
	 * @var string
	 */
	private $username;
	/**
	 * @column(type="string", length=40)
	 * @var string
	 */
	private $password;
	/**
	 * @column(length=256)
	 * @var string
	 */
	private $email;
	/**
	 * @manyToOne(targetEntity="AuthConnection")
	 * @var \Lohini\Database\Models\Entities\AuthConnection
	 */
	private $authconnection;


	/**
	 * @return \Lohini\Database\Models\Entities\Identity
	 */
	public function getIdentity()
	{
		return $this->identity;
	}

	/**
	 * @param \Lohini\Database\Models\Entities\Identity
	 * @return \Lohini\Database\Models\Entities\User (fluent)
	 */
	public function setIdentity(\Lohini\Database\Models\Entities\Identity $identity)
	{
		$this->identity=$identity;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getUsername()
	{
		return $this->username;
	}

	/**
	 * @param string
	 * @return \Lohini\Database\Models\Entities\User (fluent)
	 */
	public function setUsername($username)
	{
		$this->username=$this->sanitizeString($username);
		return $this;
	}

	/**
	 * @return string
	 */
	public function getPassword()
	{
		return $this->password;
	}

	/**
	 * @param string $password
	 * @return \Lohini\Database\Models\Entities\User (fluent)
	 */
	public function setPassword($password)
	{
		$this->password=$password;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getEmail()
	{
		return $this->email;
	}

	/**
	 * @param string
	 * @return \Lohini\Database\Models\Entities\User (fluent)
	 */
	public function setEmail($email)
	{
		$this->email=$this->sanitizeString($email);
		return $this;
	}

	/**
	 * @return \Lohini\Database\Models\Entities\AuthConnection
	 */
	public function getAuthconnection()
	{
		return $this->authconnection;
	}

	/**
	 * @param \Lohini\Database\Models\Entities\AuthConnection $aconn
	 * @return \Lohini\Database\Models\Entities\User (fluent)
	 */
	public function setAuthconnection($aconn)
	{
		$this->authconnection=$aconn;
		return $this;
	}
}
