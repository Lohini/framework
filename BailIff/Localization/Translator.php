<?php // vim: set ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license GNU GPL v3
 */
namespace BailIff\Localization;
/*
 * Copyright (c) 2010 Patrik Votoček <patrik@votocek.cz>
 *
 * Permission is hereby granted, free of charge, to any person
 * obtaining a copy of this software and associated documentation
 * files (the "Software"), to deal in the Software without
 * restriction, including without limitation the rights to use,
 * copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following
 * conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.

 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 * OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 * OTHER DEALINGS IN THE SOFTWARE.
 *
 */

use Nette\Object,
	Nette\Environment as NEnvironment,
	Nette\Utils\Strings,
	Nette\Caching\Cache,
	Nette\DirectoryNotFoundException,
	Nette\InvalidStateException,
	Nette\DI\IContainer,
	BailIff\Environment,
	BailIff\Localization\IEditable,
	BailIff\Localization\PluralForms;

/**
 * Gettext translator.
 * Based on Vrtak-CZ's NetteTranslator, gmvasek's port
 * @author Lopo <lopo@losys.eu>
 * @license New BSD License
 */
class Translator
extends Object
implements IEditable
{
	const SESSION_NAMESPACE='BailIffTranslator';
	const CACHE_ENABLE=TRUE;
	const CACHE_DISABLE=FALSE;
	/** @var array */
	protected $files=array();
	/** @var string */
	protected $lang='en';
	/** @var array */
	private $metadata;
	/** @var array<string|array> */
	protected $dictionary=array();
	/** @var bool */
	private $loaded=FALSE;
	/** @var bool */
	public static $cache=self::CACHE_DISABLE;


	/**
	 * Constructor
	 * @param array $files
	 * @param string $lang
	 * @throws InvalidArgumentException
	 */
	public function __construct(array $files=NULL, $lang=NULL)
	{
		if (count($files)>0) {
			$this->files=$files;
			}
		$this->lang=$lang;
		if (empty($lang)) {
			$this->lang=NEnvironment::getVariable('lang');
			}
		if (empty($this->lang)) {
			throw new \InvalidArgumentException('Language must be defined');
			}
		$storage=NEnvironment::getSession(self::SESSION_NAMESPACE);
		if (!isset($storage->newStrings) || !is_array($storage->newStrings)) {
			$storage->newStrings=array();
			}
	}

	/**
	 * Adds a file to parse
	 * @param string $dir
	 * @param string $identifier
	 * @throws InvalidArgumentException
	 * @throws DirectoryNotFoundException
	 */
	public function addFile($dir, $identifier)
	{
		if (strpos($dir, '%')!==FALSE) {
			$dir=NEnvironment::expand($dir);
			}
		if (isset($this->files[$identifier])) {
			throw new \InvalidArgumentException("Language file identified '$identifier' is already registered.");
			}
		$dir=realpath($dir);
		if (is_dir($dir)) {
			$this->files[$identifier]=$dir;
			}
		else {
			throw new DirectoryNotFoundException("Directory '$dir' doesn't exist.");
			}
	}

	/**
	 * Loads data
	 * @throws InvalidStateException
	 */
	protected function loadDictonary()
	{
		if (!$this->loaded) {
			if (empty($this->files)) {
				throw new InvalidStateException('Language file(s) must be defined.');
				}
			$cache=Environment::getCache(self::SESSION_NAMESPACE);
			if (self::$cache && isset($cache['dictionary-'.$this->lang])) {
				$this->dictionary=$cache['dictionary-'.$this->lang];
				}
			else {
				$files=array();
				foreach ($this->files as $identifier => $dir) {
					$path="$dir/$this->lang.$identifier.mo";
					if (file_exists($path)) {
						$this->parseFile($path, $identifier);
						$files[]=$path;
						}
					}
				if (self::$cache) {
					$cache->save("dictionary-$this->lang", $this->dictionary, array(
						'expire' => time()*60*60*2,
						'files' => $files,
						'tags' => array("dictionary-$this->lang")
						));
					}
				}
			$this->loaded=TRUE;
			}
	}

	/**
	 * Parses dictionary file
	 * @param string $file file path
	 * @param string $identifier
	 * @throws InvalidArgumentException
	 */
	protected function parseFile($file, $identifier)
	{
		$f=@fopen($file, 'rb');
		if (@filesize($file)<10) {
			\InvalidArgumentException("'$file' is not a gettext file.");
			}
		$endian=FALSE;

		$read=function($bytes) use ($f, $endian)
		{
			$data=fread($f, 4*$bytes);
			return $endian===FALSE? unpack("V$bytes", $data) : unpack("N$bytes", $data);
		};

		$input=$read(1);
		if (Strings::lower(substr(dechex($input[1]), -8))=='950412de') {
			$endian=FALSE;
			}
		elseif (Strings::lower(substr(dechex($input[1]), -8))=='de120495') {
			$endian=TRUE;
			}
		else {
			throw new \InvalidArgumentException("'$file' is not a gettext file.");
			}

		$input=$read(1);

		$input=$read(1);
		$total=$input[1];

		$input=$read(1);
		$originalOffset=$input[1];

		$input=$read(1);
		$translationOffset=$input[1];

		fseek($f, $originalOffset);
		$orignalTmp=$read(2*$total);
		fseek($f, $translationOffset);
		$translationTmp=$read(2*$total);

		for ($i=0; $i<$total; ++$i) {
			if ($orignalTmp[$i*2+1]!=0) {
				fseek($f, $orignalTmp[$i*2+2]);
				$original=@fread($f, $orignalTmp[$i*2+1]);
				}
			else {
				$original='';
				}

			if ($translationTmp[$i*2+1]!=0) {
				fseek($f, $translationTmp[$i*2+2]);
				$translation=fread($f, $translationTmp[$i*2+1]);
				if ($original==='') {
					$this->parseMetadata($translation, $identifier);
					continue;
					}
				$original=explode(Strings::chr(0x00), $original);
				$translation=explode(Strings::chr(0x00), $translation);
				$this->dictionary[is_array($original)? $original[0] : $original]['original']=$original;
				$this->dictionary[is_array($original)? $original[0] : $original]['translation']=$translation;
				$this->dictionary[is_array($original)? $original[0] : $original]['file']=$identifier;
				}
			}
	}

	/**
	 * Metadata parser
	 * @param string $input
	 * @param string $identifier
	 */
	private function parseMetadata($input, $identifier)
	{
		$input=preg_split('/[\n,]+/', trim($input));
		foreach ($input as $metadata) {
			$pattern=': ';
			$tmp=preg_split("($pattern)", $metadata);
			$this->metadata[$identifier][trim($tmp[0])]= count($tmp)>2? ltrim(strstr($metadata, $pattern), $pattern) : $tmp[1];
			}
	}

	/**
	 * Translates the given string.
	 * @param string $message
	 * @param int $form plural form (positive number)
	 * @return string
	 */
	public function translate($message, $form=1)
	{
		$this->loadDictonary();
		$files=array_keys($this->files);

		$message=(string) $message;
		$message_plural=NULL;
		if (is_array($form) && $form!==NULL) {
			$message_plural=current($form);
			$form=(int)end($form);
			}
		if (!is_int($form) || $form===NULL) {
			$form=1;
			}

		if (!empty($message) && isset($this->dictionary[$message])) {
			$tmp=preg_replace('/([a-z]+)/', '$$1', "n=$form;".$this->metadata[$files[0]]['Plural-Forms']);
			eval($tmp);

			$message=$this->dictionary[$message]['translation'];
			if (!empty($message)) {
				$message=(is_array($message) && $plural!==NULL && isset($message[$plural])) ? $message[$plural] : $message;
				}
			}
		else {
			if (!NEnvironment::getHttpResponse()->isSent() || NEnvironment::getSession()->isStarted()) {
				$space=NEnvironment::getSession(self::SESSION_NAMESPACE);
				if (!isset($space->newStrings[$this->lang])) {
					$space->newStrings[$this->lang]=array();
					}
				$space->newStrings[$this->lang][$message]= empty($message_plural)? array($message) : array($message, $message_plural);
				}
			if ($form>1 && !empty($message_plural)) {
				$message=$message_plural;
				}
			}

		if (is_array($message)) {
			$message=current($message);
			}

		$args=func_get_args();
		if (count($args)>1) {
			array_shift($args);
			if (is_array(current($args)) || current($args)===NULL) {
				array_shift($args);
				}
			if (count($args)==1 && is_array(current($args))) {
				$args=current($args);
				}
			$message=str_replace(array('%label', '%name', '%value'), array('#label', '#name', '#value'), $message);
			if (count($args)>0 && $args!=NULL); // XXX: ??? ';'
				$message=vsprintf($message, $args);
			$message=str_replace(array('#label', '#name', '#value'), array('%label', '%name', '%value'), $message);
			}
		return $message;
	}

	/**
	 * Get count of plural forms
	 * @return int
	 */
	public function getVariantsCount()
	{
		$this->loadDictonary();
		$files=array_keys($this->files);

		if (isset($this->metadata[$files[0]]['Plural-Forms'])) {
			return (int)substr($this->metadata[$files[0]]['Plural-Forms'], 9, 1);
			}
		return 1;
	}

	/**
	 * Get translations strings
	 * @return array
	 */
	public function getStrings($file = NULL)
	{
		$this->loadDictonary();

		$newStrings=array();
		$result=array();

		$storage=NEnvironment::getSession(self::SESSION_NAMESPACE);
		if (isset($storage->newStrings[$this->lang])) {
			foreach (array_keys($storage->newStrings[$this->lang]) as $original) {
				if (trim($original)!='') {
					$newStrings[$original]=FALSE;
					}
				}
			}

		foreach ($this->dictionary as $original => $data) {
			if (trim($original)!='') {
				if($file && $data['file']===$file) {
					$result[$original]=$data['translation'];
					}
				else {
					$result[$data['file']][$original]=$data['translation'];
					}
				}
			}

		if ($file) {
			return array_merge($newStrings, $result);
			}
		else {
			foreach ($this->getFiles() as $identifier => $path) {
				if (!isset($result[$identifier])) {
					$result[$identifier]=array();
					}
				}
			return array('newStrings' => $newStrings)+$result;
			}
	}

	/**
	 * Get loaded files
	 * @return array
	 */
	public function getFiles()
	{
		$this->loadDictonary();
		return $this->files;
	}

	/**
	 * Set translation string(s)
	 * @param string|array $message original string(s)
	 * @param string|array $string translation string(s)
	 * @param string $file
	 */
	public function setTranslation($message, $string, $file)
	{
		$this->loadDictonary();
		$space=NEnvironment::getSession(self::SESSION_NAMESPACE);
		if (isset($space->newStrings[$this->lang]) && array_key_exists($message, $space->newStrings[$this->lang])) {
			$message=$space->newStrings[$this->lang][$message];
			}

		$this->dictionary[is_array($message)? $message[0] : $message]['original']=(array)$message;
		$this->dictionary[is_array($message)? $message[0] : $message]['translation']=(array)$string;
		$this->dictionary[is_array($message)? $message[0] : $message]['file']=$file;
	}

	/**
	 * Save dictionary
	 * @param string $file
	 * @throws InvalidStateException
	 * @throws InvalidArgumentException
	 */
	public function save($file)
	{
		if (!$this->loaded) {
			throw new InvalidStateException('Nothing to save, translations are not loaded.');
			}
		if (!isset($this->files[$file])) {
			throw new \InvalidArgumentException("Gettext file identified as '$file' does not exist.");
			}

		$dir=$this->files[$file];
		$path="$dir/$this->lang.$file";

		$this->buildMOFile("$path.mo", $file);
		$this->buildPOFile("$path.po", $file);

		$storage=NEnvironment::getSession(self::SESSION_NAMESPACE);
		if (isset($storage->newStrings[$this->lang])) {
			unset($storage->newStrings[$this->lang]);
			}
		if (self::$cache) {
			$cache=Environment::getCache(self::SESSION_NAMESPACE)
				->clean(array(Cache::TAGS => "dictionary-$this->lang"));
			}
	}

	/**
	 * Generates gettext metadata array
	 * @param string $identifier
	 * @return array
	 */
	private function generateMetadata($identifier)
	{
		$result=array();
		if (isset($this->metadata[$identifier]['Project-Id-Version'])) {
			$result[]='Project-Id-Version: '.$this->metadata[$identifier]['Project-Id-Version'];
			}
		else {
			$result[]='Project-Id-Version: ';
			}
		if (isset($this->metadata[$identifier]['Report-Msgid-Bugs-To'])) {
			$result[]='Report-Msgid-Bugs-To: '.$this->metadata[$identifier]['Report-Msgid-Bugs-To'];
			}
		if (isset($this->metadata[$identifier]['POT-Creation-Date'])) {
			$result[]='POT-Creation-Date: '.$this->metadata[$identifier]['POT-Creation-Date'];
			}
		else {
			$result[]='POT-Creation-Date: ';
			}
		$result[]='PO-Revision-Date: '.date('Y-m-d H:iO');
		if (isset($this->metadata[$identifier]['Last-Translator'])) {
			$result[]='Language-Team: '.$this->metadata[$identifier]['Language-Team'];
			}
		else {
			$result[]='Language-Team: ';
			}
		if (isset($this->metadata[$identifier]['MIME-Version'])) {
			$result[]='MIME-Version: '.$this->metadata[$identifier]['MIME-Version'];
			}
		else {
			$result[]='MIME-Version: 1.0';
			}
		if (isset($this->metadata[$identifier]['Content-Type'])) {
			$result[]='Content-Type: '.$this->metadata[$identifier]['Content-Type'];
			}
		else {
			$result[]='Content-Type: text/plain; charset=UTF-8';
			}
		if (isset($this->metadata[$identifier]['Content-Transfer-Encoding'])) {
			$result[]='Content-Transfer-Encoding: '.$this->metadata[$identifier]['Content-Transfer-Encoding'];
			}
		else {
			$result[]='Content-Transfer-Encoding: 8bit';
			}
		if (isset($this->metadata[$identifier]['Plural-Forms'])) {
			$result[]='Plural-Forms: '.$this->metadata[$identifier]['Plural-Forms'];
			}
		else {
			$pform=PluralForms::ruleByLanguage($this->lang);
			$result[]="Plural-Forms: nplurals={$pform['nplurals']}; {$pform['rule']};";
			}
		if (isset($this->metadata[$identifier]['X-Poedit-Language'])) {
			$result[]='X-Poedit-Language: '.$this->metadata[$identifier]['X-Poedit-Language'];
			}
		if (isset($this->metadata[$identifier]['X-Poedit-Country'])) {
			$result[]='X-Poedit-Country: '.$this->metadata[$identifier]['X-Poedit-Country'];
			}
		if (isset($this->metadata[$identifier]['X-Poedit-SourceCharset'])) {
			$result[]='X-Poedit-SourceCharset: '.$this->metadata[$identifier]['X-Poedit-SourceCharset'];
			}
		if (isset($this->metadata[$identifier]['X-Poedit-KeywordsList'])) {
			$result[]='X-Poedit-KeywordsList: '.$this->metadata[$identifier]['X-Poedit-KeywordsList'];
			}

		return $result;
	}

	/**
	 * Builds gettext MO file
	 * @param string $file
	 * @param string $identifier
	 */
	private function buildPOFile($file, $identifier)
	{
		$po="# Gettext keys exported by Translator and Translation Panel\n"
			."# Created: ".date('Y-m-d H:i:s')."\n".'msgid ""'."\n".'msgstr ""'."\n";
		$po.='"'.implode('\n"'."\n".'"', $this->generateMetadata($identifier)).'\n"'."\n\n\n";
		foreach ($this->dictionary as $message => $data) {
			if ($data['file']!==$identifier) {
				continue;
				}
			$po.='msgid "'.str_replace(array('"', "'"), array('\"', "\\'"), $message).'"'."\n";
			if (is_array($data['original']) && count($data['original'])>1) {
				$po.='msgid_plural "'.str_replace(array('"', "'"), array('\"', "\\'"), end($data['original'])).'"'."\n";
				}
			if (!is_array($data['translation'])) {
				$po.='msgstr "'.str_replace(array('"', "'"), array('\"', "\\'"), $data['translation']).'"'."\n";
				}
			elseif (count($data['translation'])<2) {
				$po.='msgstr "'.str_replace(array('"', "'"), array('\"', "\\'"), current($data['translation'])).'"'."\n";
				}
			else {
				$i=0;
				foreach ($data['translation'] as $string) {
					$po.='msgstr['.$i.'] "'.str_replace(array('"', "'"), array('\"', "\\'"), $string).'"'."\n";
					$i++;
					}
				}
			$po.="\n";
			}

		$storage=NEnvironment::getSession(self::SESSION_NAMESPACE);
		if (isset($storage->newStrings[$this->lang])) {
			foreach ($storage->newStrings[$this->lang] as $original) {
				if (trim(current($original))!="" && !\array_key_exists(current($original), $this->dictionary)) {
					$po.='msgid "'.str_replace(array('"', "'"), array('\"', "\\'"), current($original)).'"'."\n";
					if (count($original)>1) {
						$po.='msgid_plural "'.str_replace(array('"', "'"), array('\"', "\\'"), end($original)).'"'."\n";
						}
					$po.="\n";
					}
				}
			}
		file_put_contents($file, $po);
	}

	/**
	 * Builds gettext MO file
	 * @param string $file
	 * @param string $identifier
	 */
	private function buildMOFile($file, $identifier)
	{
		$dictionary=array_filter(
			$this->dictionary,
			function($data) use($identifier)
			{
				return $data['file']===$identifier;
			}
			);

		ksort($dictionary);

		$metadata=implode("\n", $this->generateMetadata($identifier));
		$items=count($dictionary)+1;
		$ids=Strings::chr(0x00);
		$strings=$metadata.Strings::chr(0x00);
		$idsOffsets=array(0, 28+$items*16);
		$stringsOffsets=array(array(0, strlen($metadata)));

		foreach ($dictionary as $key => $value) {
			$id=$key;
			if (is_array($value['original']) && count($value['original'])>1) {
				$id.=Strings::chr(0x00).end($value['original']);
				}
			$string=implode(Strings::chr(0x00), $value['translation']);
			$idsOffsets[]=strlen($id);
			$idsOffsets[]=strlen($ids)+28+$items*16;
			$stringsOffsets[]=array(strlen($strings), strlen($string));
			$ids.=$id.Strings::chr(0x00);
			$strings.=$string.Strings::chr(0x00);
			}

		$valuesOffsets=array();
		foreach ($stringsOffsets as $offset) {
			list ($all, $one)=$offset;
			$valuesOffsets[]=$one;
			$valuesOffsets[]=$all+strlen($ids)+28+$items*16;
			}
		$offsets=array_merge($idsOffsets, $valuesOffsets);

		$mo=pack('Iiiiiii', 0x950412de, 0, $items, 28, 28+$items*8, 0, 28+$items*16);
		foreach ($offsets as $offset) {
			$mo.=pack('i', $offset);
			}
		file_put_contents($file, $mo.$ids.$strings);
	}

	/**
	 * Get translator
	 * @param array $options
	 * @return Translator
	 */
	public static function getTranslator(IContainer $container, $options)
	{
		return new static(isset($options['files'])? (array)$options['files'] : NULL, NEnvironment::getVariable('lang', 'en'));
	}

	/**
	 * Returns current language
	 */
	public function getLang()
	{
		return $this->lang;
	}

	/**
	 * Sets a new language
	 * @return Translator (fluent)
	 */
	public function setLang($lang)
	{
		if ($this->lang===$lang) {
			return $this;
			}

		$this->lang=$lang;
		$this->dictionary=array();
		$this->loaded=FALSE;

		// Lazy load
		// $this->loadDictonary();
		return $this;
	}
}
