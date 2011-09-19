<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Database\Models\Entities;

/**
 * Upload entity
 *
 * @author Lopo <lopo@lohini.net>
 * 
 * @entity(repositoryClass="Lohini\Database\Models\Repositories\Upload")
 * @table(name="uploads")
 * @service(class="Lohini\Database\Models\Services\Uploads")
 */
class Upload
extends \Lohini\Database\Doctrine\ORM\Entities\IdentifiedEntity
implements \Lohini\Database\Models\IEntity
{
	/**
	 * @column(type="string")
	 * @var string
	 */
	private $name;
	/**
	 * @column(type="string", unique=true)
	 * @var string
	 */
	private $filename;
	/**
	 * @column(type="integer")
	 * @var int
	 */
	private $size=0;
	/**
	 * @column(type="datetime")
	 * @var DateTime
	 */
	private $created;
	/**
	 * @manyToOne(targetEntity="\Lohini\Database\Models\Entities\User")
	 * @joinColumn(name="user_id", referencedColumnName="id", nullable=false)
	 * @var \Lohini\Database\Models\Entities\User
	 */
	private $user;
	/**
	 * @column(type="string")
	 * @var string
	 */
	private $etag;
	/**
	 * @column(type="integer")
	 * @var int
	 */
	private $cntDownload=0;
	/**
	 * @column(type="string")
	 * @var string
	 */
	private $mimetype;


	/**
	 * @param string $name
	 * @return Upload (fluent)
	 */
	public function setName($name)
	{
		$this->name=$this->sanitizeString($name);
		return $this;
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
	 * @return Upload (fluent)
	 */
	public function setFilename($name)
	{
		$this->filename=$this->sanitizeString($name);
		return $this;
	}

	/**
	 * @return string
	 */
	public function getFilename()
	{
		return $this->filename;
	}

	/**
	 * @param int $size
	 * @return Upload (fluent)
	 */
	public function setSize($size)
	{
		$this->size=intval($size);
		return $this;
	}

	/**
	 * @return int
	 */
	public function getSize()
	{
		return $this->size;
	}

	/**
	 * @param DateTime
	 * @return Upload (fluent)
	 */
	public function setCreated($datetime)
	{
		$this->created=\Nette\DateTime::from($datetime);
		return $this;
	}

	/**
	 * @return DateTime
	 */
	public function getCreated()
	{
		return $this->created;
	}

	/**
	 * @param \Lohini\Database\Entities\User $user
	 * @return Upload (fluent)
	 */
	public function setUser($user)
	{
		$this->user=$user;
		return $this;
	}

	/**
	 * @return \Lohini\Database\Entities\User
	 */
	public function getUser()
	{
		return $this->user;
	}

	/**
	 * @param string $etag
	 * @return Upload (fluent)
	 */
	public function setEtag($etag)
	{
		$this->etag=$this->sanitizeString($etag);
		return $this;
	}

	/**
	 * @return string
	 */
	public function getEtag()
	{
		return $this->etag;
	}

	/**
	 * @param int $cnt
	 * @return Upload (fluent)
	 */
	public function setCntDownload($cnt)
	{
		$this->cntDownload=intval($cnt);
		return $this;
	}

	/**
	 * @return int
	 */
	public function getCntDownload()
	{
		return $this->cntDownload;
	}

	/**
	 * @param string $mimetype
	 * @return Upload (fluent)
	 */
	public function setMimetype($mimetype)
	{
		$this->mimetype=$this->sanitizeString($mimetype);
		return $this;
	}

	/**
	 * @return string
	 */
	public function getMimetype()
	{
		return $this->mimetype;
	}
}