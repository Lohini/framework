<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Extension\Curl\Diagnostics;
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

use Nette\PhpGenerator,
	Lohini\Extension\Curl;

/**
 */
class FileLogger
extends \Nette\Object
implements Curl\IRequestLogger
{
	/** @var string */
	private $logDir;
	/** @var \Nette\Callback[] */
	private $formatters=array();


	/**
	 * @param string $logDir
	 */
	public function __construct($logDir=NULL)
	{
		$this->logDir= $logDir ?: \Nette\Diagnostics\Debugger::$logDirectory;
	}

	/**
	 * @param callable $callback
	 */
	public function addFormatter($callback)
	{
		$this->formatters[]=callback($callback);
	}

	/**
	 * @param Curl\Request $request
	 */
	public function request(Curl\Request $request)
	{
		$id=md5(serialize($request));

		$content=array($request->method.' '.$request->getUrl());
		foreach ($request->headers as $name => $value) {
			$content[]="$name: $value";
			}

		$content='> '.implode("\n> ", $content)."\n";
		\Lohini\Tools\Arrays::flatMapAssoc(
			$request->post+$request->files,
			function($val, $keys) use (&$content) {
				$content.=implode('][', $keys).': '.PhpGenerator\Helpers::dump($val)."\n";
				}
			);

		$this->write($content."\n", $id);

		return $id;
	}

	/**
	 * @param Curl\Response $response
	 * @param string $id
	 */
	public function response(Curl\Response $response, $id)
	{
		$content=array();
		foreach ($response->getHeaders() as $name => $value) {
			$content[]="$name: $value";
			}

		$content='< '.implode("\n< ", $content);
		$this->write($content."\n\n", $id);

		$body=$response->getResponse();
		foreach ($this->formatters as $formatter) {
			if ($formatted=$formatter($body, $response)) {
				$body=$formatted;
				}
			}
		$this->write($body, $id);
	}

	/**
	 * @param string $content
	 * @param string $id
	 */
	protected function write($content, $id)
	{
		$content= is_string($content)? $content : PhpGenerator\Helpers::dump($content);

		$file=$this->logDir.'/curl_'.@date('Y-m-d-H-i-s')."_$id.dat";
		foreach (Nette\Utils\Finder::findFiles("curl_*_$id.dat")->in($this->logDir) as $item) {
			/** @var \SplFileInfo $item */
			$file=$item->getRealpath();
			}

		if (!@file_put_contents($file, $content, FILE_APPEND)) {
			\Nette\Diagnostics\Debugger::log("Logging to $file failed.");
			}
	}
}
