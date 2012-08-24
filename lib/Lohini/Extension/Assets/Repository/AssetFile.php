<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Extension\Assets\Repository;
/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Lohini\Extension\Assets;
/**
 */
class AssetFile
extends \Nette\Object
{
	/** @var string */
	public $input;
	/** @var string */
	public $type;
	/** @var array */
	public $options=array();
	/** @var array */
	public $filters=array();
	/** @var string */
	public $serialized;


	/**
	 * @param string $input
	 * @param string $type
	 * @param array $options
	 * @param array $filters
	 */
	public function __construct($input, $type=Assets\FormulaeManager::TYPE_JAVASCRIPT, array $options=array(), array $filters=array())
	{
		$this->input=$input;
		$this->type=$type;
		$this->options=$options;
		$this->filters=$filters;
	}

	/**
	 * @param Assets\AssetFactory $factory
	 * @return \Assetic\Asset\AssetCollection
	 * @throws \Lohini\FileNotFoundException
	 */
	public function createAsset(Assets\AssetFactory $factory)
	{
		/** @var \Assetic\Asset\AssetCollection $asset */
		foreach ($asset=$factory->createAsset($this->input, $this->filters, $this->options) as $leaf) {
			if (!$leaf instanceof \Assetic\Asset\FileAsset) {
				continue;
				}

			/** @var \Assetic\Asset\FileAsset $leaf */
			if (!file_exists($file=$leaf->getSourceRoot().'/'.$leaf->getSourcePath())) {
				throw new \Lohini\FileNotFoundException("Assetic wasn't able to process your input, file '$file' doesn't exists.");
				}
			}

		if ($asset instanceof \Assetic\Asset\AssetInterface && !isset($this->options['output'])) {
			$this->options['output']=$asset->getTargetPath();
			}

		return $asset;
	}

	/**
	 * @param Assets\AssetFactory $factory
	 * @return string
	 */
	public function serialize(Assets\AssetFactory $factory)
	{
		$assets=array();
		foreach ($asset=$this->createAsset($factory) as $leaf) {
			$assets[]=\Nette\Utils\PhpGenerator\Helpers::formatArgs('unserialize(?)', array(serialize($leaf)));
			}

		if (count($assets)===1) {
			return reset($assets);
			}

		$assets="array(\n\t".implode(",\n\t", $assets)."\n)";
		return 'new Assetic\Asset\AssetCollection('.$assets.')';
	}
}
