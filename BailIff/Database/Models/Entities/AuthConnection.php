<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace BailIff\Database\Models\Entities;

/**
 * Auth connections
 *
 * @author Lopo <lopo@losys.eu>
 *
 * @entity
 * @table(name="authconnections")
 */
class AuthConnection
extends \BailIff\Database\Doctrine\ORM\Entities\IdentifiedEntity
{
	/**
	 * @column(length=20, unique=true)
	 * @var string
	 */
	private $name;
	/**
	 * @column(length=20)
	 * @var string
	 */
	private $authenticator;
	/**
	 * @column
	 * @var string
	 */
	private $address;
	/**
	 * @column(nullable=true)
	 * @var string
	 */
	private $adminUsername;
	/**
	 * @column(nullable=true)
	 * @var string
	 */
	private $adminPassword;
	/**
	 * @column
	 * @var string
	 */
	private $storage;
	/**
	 * @column
	 * @var string
	 */
	private $cellUsername;
	/**
	 * @column
	 * @var string
	 */
	private $cellPassword;
	/**
	 * @column(type="text", nullable=true)
	 * @var string
	 */
	private $passwordCallback;
	/**
	 * @column(nullable=true)
	 * @var string
	 */
	private $description;
	/**
	 * @column
	 * @var string
	 */
	private $driver;


	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param string $name
	 * @return \BailIff\Database\Models\Entities\AuthConnection (fluent)
	 */
	public function setName($name)
	{
		$this->name=$this->sanitizeString($name);
		return $this;
	}

	/**
	 * @return string
	 */
	public function getAuthenticator()
	{
		return $this->authenticator;
	}

	/**
	 * @param string $authenticator
	 * @return \BailIff\Database\Models\Entities\AuthConnection (fluent)
	 */
	public function setAuthenticator($authenticator)
	{
		$this->authenticator=$this->sanitizeString($authenticator);
		return $this;
	}

	/**
	 * @return string
	 */
	public function getAddress()
	{
		return $this->address;
	}

	/**
	 * @param string $address
	 * @return \BailIff\Database\Models\Entities\AuthConnection (fluent)
	 */
	public function setAddress($address)
	{
		$this->address=$this->sanitizeString($address);
		return $this;
	}

	/**
	 * @return string
	 */
	public function getAdminUsername()
	{
		return $this->adminUsername;
	}

	/**
	 * @param string $username
	 * @return \BailIff\Database\Models\Entities\AuthConnection (fluent)
	 */
	public function setAdminUsername($username)
	{
		$this->adminUsername=$this->sanitizeString($username);
		return $this;
	}

	/**
	 * @return string
	 */
	public function getAdminPassword()
	{
		return $this->adminPassword;
	}

	/**
	 * @param string $pswd
	 * @return \BailIff\Database\Models\Entities\AuthConnection (fluent)
	 */
	public function setAdminPassword($pswd)
	{
		$this->adminPassword=$this->sanitizeString($pswd);
		return $this;
	}

	/**
	 * @return string
	 */
	public function getStorage() { return $this->storage;
	}

	/**
	 * @param string $pswd
	 * @return \BailIff\Database\Models\Entities\AuthConnection (fluent)
	 */
	public function setStorage($storage)
	{
		$this->storage=$this->sanitizeString($storage);
		return $this;
	}

	/**
	 * @return string
	 */
	public function getCellUsername()
	{
		return $this->cellUsername;
	}

	/**
	 * @param string $cell
	 * @return \BailIff\Database\Models\Entities\AuthConnection (fluent)
	 */
	public function setCellUsername($cell)
	{
		$this->cellUsername=$this->sanitizeString($cell);
		return $this;
	}

	/**
	 * @return string
	 */
	public function getCellPassword()
	{
		return $this->cellPassword;
	}

	/**
	 * @param string $cell
	 * @return \BailIff\Database\Models\Entities\AuthConnection (fluent)
	 */
	public function setCellPassword($cell)
	{
		$this->cellPassword=$this->sanitizeString($cell);
		return $this;
	}

	/**
	 * @return string
	 */
	public function getPasswordCallback()
	{
		return $this->passwordCallback;
	}

	/**
	 * @param string $callback
	 * @return \BailIff\Database\Models\Entities\AuthConnection (fluent)
	 */
	public function setPasswordCallback($callback)
	{
		$this->passwordCallback=$this->sanitizeString($callback);
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
	 * @param string $desc
	 * @return \BailIff\Database\Models\Entities\AuthConnection (fluent)
	 */
	public function setDescription($desc)
	{
		$this->description=$this->sanitizeString($desc);
		return $this;
	}

	/**
	 * @return string
	 */
	public function getDriver()
	{
		return $this->driver;
	}

	/**
	 * @param string $driver
	 * @return \BailIff\Database\Models\Entities\AuthConnection (fluent)
	 */
	public function setDriver($driver)
	{
		$this->driver=$this->sanitizeString($driver);
		return $this;
	}
}
