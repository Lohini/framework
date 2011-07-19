<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Database\Doctrine\ORM\Mapping;

/**
 * @link http://www.doctrine-project.org/docs/orm/2.0/en/cookbook/sql-table-prefixes.html
 */
class TablePrefix
{
	/** @var string */
	protected $prefix='';


	/**
	 * @param string $prefix
	 */
	public function __construct($prefix)
	{
		$this->prefix=(string)$prefix;
	}

	/**
	 * @param \Doctrine\ORM\Event\LoadClassMetadataEventArgs $eventArgs
	 */
	public function loadClassMetadata(\Doctrine\ORM\Event\LoadClassMetadataEventArgs $eventArgs)
	{
		$classMetadata=$eventArgs->getClassMetadata();
		$classMetadata->setTableName($this->prefix.$classMetadata->getTableName());
		foreach ($classMetadata->getAssociationMappings() as $fieldName => $mapping) {
			if ($mapping['type']==\Doctrine\ORM\Mapping\ClassMetadataInfo::MANY_TO_MANY) {
				$mappedTableName=$classMetadata->associationMappings[$fieldName]['joinTable']['name'];
				$classMetadata->associationMappings[$fieldName]['joinTable']['name']=$this->prefix.$mappedTableName;
				}
			}
	}
}
