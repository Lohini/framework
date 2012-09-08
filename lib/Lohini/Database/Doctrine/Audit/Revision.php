<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Database\Doctrine\Audit;
/**
 * @author Benjamin Eberlei <eberlei@simplethings.de>
 * @author Filip Procházka <filip@prochazka.su>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(readOnly=TRUE)
 * @ORM\Table(name="db_audit_revisions", indexes={
 * @ORM\Index(name="entity_idx", columns={"className", "entityId"})
 * })
 */
class Revision
extends \Lohini\Database\Doctrine\Entities\BaseEntity
{
	const TYPE_INSERT='INS';
	const TYPE_UPDATE='UPD';
	const TYPE_DELETE='DEL';
	const TYPE_REVERT='REV';

	/**
	 * @ORM\Id
	 * @ORM\Column(type="bigint")
	 * @ORM\GeneratedValue
	 * @var int
	 */
	private $id;
	/**
	 * @ORM\Column(type="string", length=3)
	 * @var int
	 */
	protected $type=self::TYPE_INSERT;
	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	private $className;
	/**
	 * @ORM\Column(type="integer")
	 * @var integer
	 */
	private $entityId;
	/**
	 * @ORM\Column(type="text", nullable=TRUE)
	 * @var string
	 */
	private $comment;
	/**
	 * @ORM\Column(type="datetime")
	 * @var \Datetime
	 */
	private $createdAt;
	/**
	 * @ORM\Column(type="string", nullable=TRUE)
	 * @var string
	 */
	private $author;
	/**
	 * This field must be manually completed by hydrator.
	 * @var object
	 */
	private $entity;


	/**
	 * @param $className
	 * @param int $id
	 * @param int $type
	 * @param string $author
	 * @param string $comment
	 */
	public function __construct($className, $id, $type=self::TYPE_INSERT, $author=NULL, $comment=NULL)
	{
		$this->className=$className;
		$this->entityId=$id;
		$this->type=$type;
		$this->createdAt=new \DateTime;
		$this->author=$author;
		$this->comment=$comment;
	}

	/**
	 * @return string
	 */
	public function getAuthor()
	{
		return $this->author;
	}

	/**
	 * @return string
	 */
	public function getClassName()
	{
		return $this->className;
	}

	/**
	 * @return \Datetime
	 */
	public function getCreatedAt()
	{
		return clone $this->createdAt;
	}

	/**
	 * @return int
	 */
	public function getEntityId()
	{
		return $this->entityId;
	}

	/**
	 * @return string
	 */
	public function getComment()
	{
		return $this->comment;
	}

	/**
	 * @return int
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * @internal
	 *
	 * @param object $entity
	 * @throws \Nette\InvalidStateException
	 */
	public function injectEntity($entity)
	{
		if ($this->entity) {
			throw new \Nette\InvalidStateException('Entity is already injected.');
			}

		$this->entity=$entity;
	}

	/**
	 * @return object
	 */
	public function getEntity()
	{
		return $this->entity;
	}
}
