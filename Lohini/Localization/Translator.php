<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Localization;
/**
 * This file is part of the Nella Framework.
 *
 * Copyright (c) 2006, 2011 Patrik VotoÄŤek (http://patrik.votocek.cz)
 *
 * This source file is subject to the GNU Lesser General Public License. For more information please see http://nella-project.org
 * @author	Patrik VotoÄŤek
 */

/**
 * Translator
 *
 * @property string $lang
 * @property-read array $dictionaries
 */
class Translator
extends \Nette\Utils\FreezableObject
implements ITranslator
{
	/** @var array */
	protected $dictionaries=array();
	/** @var \Lohini\Localization\IStorage */
	private $storage;
	/** @var \Nette\Caching\Cache */
	private $cache=NULL;
	/** @var string */
	private $lang='en';
	

	/**
	 * @param \Nette\Caching\IStorage
	 */
	public function __construct(\Nette\Caching\IStorage $cacheStorage=NULL)
	{
		if ($cacheStorage) {
			$this->cache=new \Nette\Caching\Cache($cacheStorage, "Nella.Translator");
			}
	}
	
	/**
	 * @return \Lohini\Localization\IStorage
	 */
	protected function getStorage()
	{
		if (!$this->storage) {
			$this->storage=new \Lohini\Localization\Storages\Gettext; // default storage
		}
		return $this->storage;
	}
	
	/**
	 * @param \Lohini\Localization\IStorage
	 * @return \Lohini\Localization\Translator
	 */
	public function setStorage(IStorage $storage)
	{
		$this->updating();
		$this->storage=$storage;
		return $this;
	}
	
	/**
	 * @param string $name
	 * @param string $dir
	 * @param \Lohini\Localization\IStorage $storage
	 * @throws \Nette\InvalidArgumentException
	 */
	public function addDictionary($name, $dir, IStorage $storage=NULL)
	{
		if (!file_exists($dir)) {
			throw new \Nette\InvalidArgumentException("Directory '$dir' not exist");
			}
		
		$dir=realpath($dir);
		
		$storage=$storage ?: $this->getStorage();
		$this->dictionaries[$name]=new Dictionary($dir, $storage);
		return $this;
	}

	/**
	 * @internal
	 * @return array
	 */
	public function getDictionaries()
	{
		return $this->dictionaries;
	}

	/**
	 * @return string
	 */
	public function getLang()
	{
		return $this->lang;
	}

	/**
	 * @param string
	 * @return \Lohini\Localization\Translator
	 * @throws \Nette\InvalidStateException
	 */
	public function setLang($lang)
	{
		$this->updating();

		$this->lang=$lang;
		return $this;
	}

	public function init()
	{
		$this->updating();
		
		foreach ($this->dictionaries as $dictionary) {
			$dictionary->init($this->lang);
			}
		
		$this->freeze();
	}

	/**
	 * @param string
	 * @param int
	 * @return string
	 */
	public function translate($message, $count=NULL)
	{
		if (!$this->isFrozen()) {
			$this->init();
			}

		$messages=(array) $message;
		$args=(array)$count;
		$form= $args? reset($args) : NULL;
		$form= $form===NULL? 1 : (is_int($form)? $form : 0);
		$plural= $form==1? 0 : 1;
		
		$message= isset($messages[$plural])? $messages[$plural] : $messages[0];
		foreach ($this->dictionaries as $dictionary) {
			if (($tmp=$dictionary->translate(reset($messages), $form))!==NULL) {
				$message=$tmp;
				break;
				}
			}

		if (count($args)>0 && reset($args)!==NULL) {
			$message=str_replace(array('%label', '%name', '%value'), array('%%label', '%%name', '%%value'), $message);
			$message=vsprintf($message, $args);
			}

		return $message;
	}
}
