<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2012 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Testing\Http;
/**
* @author Filip Procházka <filip@prochazka.su>
*/
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

/**
 */
class FakeSession
extends \Nette\Http\Session
{
	/** @var FakeSessionSection[] */
	private $sections=array();
	/** @var bool */
	private $started=FALSE;
	/** @var array */
	private $options=array();
	/** @var string */
	private $id;
	/** @var string */
	private $name='session_id';


	/**
	 * @param \Nette\Http\IRequest $request
	 * @param \Nette\Http\IResponse $response
	 */
	public function __construct(\Nette\Http\IRequest $request, \Nette\Http\IResponse $response)
	{
		$this->regenerateId();
	}

	/**
	 */
	public function start()
	{
		$this->started=TRUE;
	}

	/**
	 * @return bool
	 */
	public function isStarted()
	{
		return $this->started;
	}

	/**
	 */
	public function close()
	{
		$this->started=NULL;
	}

	/**
	 */
	public function destroy()
	{
		$this->sections=array();
		$this->close();
	}

	/**
	 * @return bool
	 */
	public function exists()
	{
		return TRUE;
	}

	/**
	 */
	public function regenerateId()
	{
		$this->id=md5((string)microtime(TRUE));
	}

	/**
	 * @return string
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param string $name
	 */
	public function setName($name)
	{
		$this->name=$name;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param string $section
	 * @param string $class
	 * @return FakeSessionSection
	 */
	public function getSection($section, $class='Lohini\Testing\Http\FakeSessionSection')
	{
		return $this->sections[$section]=new $class($this, $section);
	}

	/**
	 * @deprecated
	 * @param $section
	 * @throws \Nette\NotSupportedException
	 */
	public function getNamespace($section)
	{
		throw new \Nette\NotSupportedException;
	}

	/**
	 * @param string $section
	 * @return bool
	 */
	public function hasSection($section)
	{
		return isset($this->sections[$section]);
	}

	/**
	 * @return \ArrayIterator
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->sections);
	}

	/**
	 */
	public function clean()
	{
	}

	/**
	 * @param array $options
	 */
	public function setOptions(array $options)
	{
		$this->options=$options+$this->options;
	}

	/**
	 * @return array
	 */
	public function getOptions()
	{
		return $this->options;
	}

	/**
	 * @param int|string $time
	 */
	public function setExpiration($time)
	{
	}

	/**
	 * @return array
	 */
	public function getCookieParameters()
	{
		$keys=array('cookie_path', 'cookie_domain', 'cookie_secure');
		$empty=array_fill_keys($keys, NULL);

		return array_intersect_key($this->options, $empty)+$empty;
	}

	/**
	 * @param \Nette\Http\ISessionStorage $storage
	 */
	public function setStorage(\Nette\Http\ISessionStorage $storage)
	{
	}
}
