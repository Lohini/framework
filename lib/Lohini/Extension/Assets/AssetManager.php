<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Extension\Assets;
/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip@prochazka.su)
 *
 * @license http://www.kdyby.org/license
 * @author Filip Procházka <filip@prochazka.su>
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Assetic\Asset\AssetInterface;

require_once __DIR__.'/exceptions.php';
/**
 */
class AssetManager
extends \Assetic\AssetManager
{
	/** @var array */
	private $options=array();
	/** @var array */
	private $filters=array();
	/** @var array */
	private $outputs=array();


	/**
	 * @param \Assetic\Asset\AssetInterface $asset
	 * @param array $filters
	 * @param array $options
	 * @return string
	 */
	public function add(AssetInterface $asset, $filters=array(), $options=array())
	{
		parent::set($name=(count($this->getNames())+1), $asset);

		$this->options[$name]=$options;
		$this->filters[$name]=$filters;
		$this->outputs[$asset->getTargetPath()]=$name;

		return $name;
	}

	/**
	 * @param \Assetic\Asset\AssetInterface $asset
	 * @return mixed
	 * @throws AssetNotFoundException
	 */
	public function getAssetName(AssetInterface $asset)
	{
		foreach ($this->getNames() as $name) {
			if ($this->get($name)===$asset) {
				return $name;
				}
			}

		throw new AssetNotFoundException('Asset is not registered.');
	}

	/**
	 * @param string $output
	 * @return \Assetic\Asset\AssetInterface
	 */
	public function getOutputAsset($output)
	{
		return $this->get($this->outputs[$output]);
	}

	/**
	 * @param string $name
	 * @return mixed
	 */
	public function getOptions($name)
	{
		$this->get($name);
		return $this->options[$name];
	}

	/**
	 * @param string $name
	 * @return array
	 */
	public function getFilters($name)
	{
		$this->get($name);
		return $this->filters[$name];
	}
}
