<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace BailIff\Database\Models\Entities;

/**
 * Identity credentials entity
 *
 * @entity(repositoryClass="BailIff\Database\Models\Repositories\User")
 * @table(name="users")
 * @service(class="BailIff\Database\Models\Services\Users")
 *
 * @property \BailIff\Database\Models\Entities\Identity $identity
 * @property string $username
 * @property string $password
 * @property string $email
 */
class User
extends \BailIff\Database\Doctrine\ORM\Entities\IdentifiedEntity
implements \BailIff\Database\Models\IEntity
{
	/**
	 * @oneToOne(targetEntity="Identity", fetch="EAGER")
	 * @joinColumn(name="user_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
	 * @var \BailIff\Database\Models\Entities\Identity
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
	 * @var \BailIff\Database\Models\Entities\AuthConnection
	 */
	private $authconnection;


	/**
	 * @return \BailIff\Database\Models\Entities\Identity
	 */
	public function getIdentity()
	{
		return $this->identity;
	}

	/**
	 * @param \BailIff\Database\Models\Entities\Identity
	 * @return \BailIff\Database\Models\Entities\User (fluent)
	 */
	public function setIdentity(\BailIff\Database\Models\Entities\Identity $identity)
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
	 * @return \BailIff\Database\Models\Entities\User (fluent)
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
	 * @return \BailIff\Database\Models\Entities\User (fluent)
	 */
	public function setPassword($password)
	{
		$this->password=$password;
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
	 * @return \BailIff\Database\Models\Entities\User (fluent)
	 */
	public function setEmail($email)
	{
		$this->email=$this->sanitizeString($email);
		return $this;
	}

	/**
	 * @return \BailIff\Database\Models\Entities\AuthConnection
	 */
	public function getAuthconnection()
	{
		return $this->authconnection;
	}

	/**
	 * @param \BailIff\Database\Models\Entities\AuthConnection $aconn
	 * @return \BailIff\Database\Models\Entities\User (fluent)
	 */
	public function setAuthconnection($aconn)
	{
		$this->authconnection=$aconn;
		return $this;
	}
}
