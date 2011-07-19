<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Database\Doctrine\ORM\Entities;
/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 * @author Filip Procházka
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */


/**
 * In descendants requires to set Entity annotation like:
 * Entity(repositoryClass="Kdyby\Doctrine\ORM\Repositories\NestedTreeRepository")
 *
 * @MappedSuperclass
 * @gedmo:Tree(type="nested")
 */
abstract class NestedNode
extends IdentifiedEntity
implements \Gedmo\Tree\Node
{
    /**
     * @gedmo:TreeLeft
     * @Column(name="node_lft", type="integer")
	 * @var int
     */
    private $nodeLft;
    /**
     * @gedmo:TreeLevel
     * @Column(name="node_lvl", type="integer")
	 * @var int
     */
    private $nodeLvl;
    /**
     * @gedmo:TreeRight
     * @Column(name="node_rgt", type="integer")
	 * @var int
     */
    private $nodeRgt;
    /**
     * @gedmo:TreeRoot
     * @Column(name="node_root", type="integer", nullable=true)
	 * @var int
     */
    private $nodeRoot=0;

//	/**
//	 * @gedmo:TreeParent
//	 * @ManyToOne(targetEntity="Category", inversedBy="children")
//	 */
//	abstract private $parent;

//	/**
//	 * @OneToMany(targetEntity="Category", mappedBy="parent")
//	 * @OrderBy({"lft" = "ASC"})
//	 */
//	abstract private $children;

	/**
	 * @Column(type="boolean")
	 * @var bool
	 */
	private $useRoot=FALSE;


	/**
	 * @return bool
	 */
    public function isRoot()
	{
		return (bool)$this->nodeRoot;
	}

	/**
	 * @return self|NULL
	 */
    abstract public function getParent();

	/**
	 * @return array of self
	 */
    abstract public function getChildren();

	/**
	 * @return bool
	 */
	public function getUseRoot()
	{
		return $this->useRoot;
	}

	/**
	 * @param bool $useRoot
	 * @throws \Nette\InvalidStateException
	 */
	public function setUseRoot($useRoot)
	{
		if (!$this->isRoot()) {
			throw new \Nette\InvalidStateException("Whether or not to 'use root' can be set only on root node.");
			}

		$this->useRoot=(bool)$useRoot;
	}
}
