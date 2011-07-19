<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Database\Doctrine\ODM;
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
 * Doctrine CLI Connection Helper.
 */
class CouchDBHelper
extends \Symfony\Component\Console\Helper\Helper
{
	/** @var \Doctrine\ODM\CouchDB\DocumentManager */
	protected $dm;
	/** @var \Doctrine\CouchDB\CouchDBClient */
	protected $couchDBClient;


	/**
	 * @param \Lohini\Database\Doctrine\ODM\Container $container
	 */
	public function __construct(Container $container)
	{
		$this->dm=$container->getDocumentManager();
		$this->couchDBClient=$this->dm->getCouchDBClient();
	}

	/**
	 * @return \Doctrine\ODM\CouchDB\DocumentManager
	 */
	public function getDocumentManager()
	{
		return $this->dm;
	}

	/**
	 * @return \Doctrine\CouchDB\CouchDBClient
	 */
	public function getCouchDBClient()
	{
		return $this->couchDBClient;
	}

	/**
	 * @see Helper
	 */
	public function getName()
	{
		return 'couchdb';
	}
}
