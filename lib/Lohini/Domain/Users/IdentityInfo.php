<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Domain\Users;
/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="users_info")
 */
class IdentityInfo
extends \Lohini\Database\Doctrine\Entities\IdentifiedEntity
{
	/** @var \Lohini\Security\Identity */
	private $identity;
	/** @ORM\Column(type="string", nullable=TRUE) */
	private $phone;
	/** @ORM\Column(type="array", nullable=TRUE) */
	private $data=array();


	/**
	 * @internal
	 * @param \Lohini\Security\Identity $identity
	 * @return \Lohini\Domain\Users\IdentityInfo
	 * @throws \Nette\InvalidArgumentException
	 */
	final public function setIdentity(\Lohini\Security\Identity $identity)
	{
		if ($identity->getInfo()!==$this) {
			throw new \Nette\InvalidArgumentException('Given identity does not own this info object.');
			}

		$this->identity=$identity;
		return $this;
	}

	/**
	 * @param string $name
	 * @return mixed
	 */
	public function &__get($name)
	{
		if (isset($this->{$name})) {
			return $this->{$name};
			}

		return $this->data[$name];
	}

	/**
	 * @param string $name
	 * @param mixed $value
	 * @throws \Nette\NotImplementedException
	 */
	public function __set($name, $value)
	{
		if (!\Lohini\Utils\Tools::isSerializable($value)) {
			throw new \Nette\NotImplementedException;
			}

		if (isset($this->{$name})) {
			return $this->{$name}=$value;
			}

		$this->data[$name]=$value;
	}

	/**
	 * @param string $name
	 * @return bool
	 */
	public function __isset($name)
	{
		if (isset($this->{$name})) {
			return TRUE;
			}

		return isset($this->data[$name]);
	}

	/**
	 * @param string $name
	 */
	public function __unset($name)
	{
		if (isset($this->{$name})) {
			return $this->{$name}=NULL;
			}

		unset($this->data[$name]);
	}
}
