<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Database\Models\Entities;

use Lohini\Plugins\PluginException,
	Lohini\Plugins\Plugin as APlugin,
	Doctrine\DBAL\Migrations;

/**
 * @entity(repositoryClass="Lohini\Database\Models\Repositories\Plugin")
 * @table(name="plugins")
 * @service(class="Lohini\Database\Models\Services\Plugins")
 *
 * @property-read string $name
 * @property int $state
 * @property string $iversion
 * @property \Lohini\Plugins\IPlugin $plugin
 */
class Plugin
extends \Lohini\Database\Doctrine\ORM\Entities\IdentifiedEntity
implements \Lohini\Database\Models\IEntity
{
	/**
	 * @column(type="string", length=50)
	 * @var string
	 */
	private $name;
	/**
	 * @column(type="string", length=20)
	 * @var string
	 */
	private $state=APlugin::STATE_REGISTERED;
	/**
	 * @column(type="string", length=7, nullable=true)
	 * @var string
	 */
	private $iversion=0;
	/** @var \Lohini\Plugins\IPlugin */
	private $plugin=NULL;


	public function __construct($name)
	{
		parent::__construct($name);
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
	 * @param string $state
	 * @return Plugin
	 */
	public function setState($state)
	{
		$this->state=$this->sanitizeString($state);
		return $this;
	}

	/**
	 * @return string
	 */
	public function getState()
	{
		return $this->state;
	}

	/**
	 * @return bool
	 */
	public function getInstalled()
	{
		return $this->state!=APlugin::STATE_REGISTERED
				&& $this->state!=APlugin::STATE_DELETED;
	}

	/**
	 * @param bool $enabled
	 */
	public function setEnabled($enabled=TRUE)
	{
		$enabled=(bool)$enabled;
		switch ($this->state) {
			case APlugin::STATE_REGISTERED:
				if ($enabled) {
					throw new \Nette\InvalidStateException("Can't enable not-installed plugin.");
					}
				return;
			case APlugin::STATE_INSTALLED:
				if (!$enabled) {
					return;
					}
				$class=$this->pluginClass;
				if ($class::VERSION!=$this->iversion) {
					throw new \Nette\InvalidStateException('Plugin source diffs with installed version - update first.');
					}
				$this->state=APlugin::STATE_ENABLED;
				return;
			case APlugin::STATE_ENABLED:
				if (!$enabled) {
					$this->state=APlugin::STATE_INSTALLED;
					}
				return;
			case APlugin::STATE_DELETED:
				if ($enabled) {
					throw new \Nette\InvalidStateException("Can't enable deleted plugin.");
					}
				return;
			}
	}

	/**
	 * @return bool
	 */
	public function getEnabled()
	{
		return $this->state===APlugin::STATE_ENABLED;
	}

	/**
	 * @return string
	 */
	public function getIversion()
	{
		return $this->iversion;
	}

	/**
	 * @param bool $iversion
	 * @return Plugin
	 */
	public function setIversion($iversion)
	{
		$this->iversion=$this->sanitizeString($iversion);
		return $this;
	}

	/**
	 * @return \Lohini\Plugins\IPlugin
	 */
	public function getPlugin()
	{
		if ($this->plugin===NULL) {
			$class=$this->getPluginClass();
			$this->plugin=new $class($this);
			}
		return $this->plugin;
	}

	/**
	 * @return string
	 */
	public function getPluginClass()
	{
		return "\\LohiniPlugins\\$this->name\\Plugin";
	}

	/**
	 * @return string
	 */
	public function getPluginNS()
	{
		return '\LohiniPlugins\\'.$this->name;
	}

	/**
	 * @return string
	 */
	public function getPluginPath()
	{
		return APP_DIR.'/Plugins/'.$this->name;
	}

	public function __call($name, $args)
	{
		try {
			return parent::__call($name, $args);
			}
		catch (\Nette\MemberAccessException $e) {
			$ref=new \Nette\Reflection\ClassType($this->getPluginClass());
			if (!$ref->hasMethod($name)) {
				throw $e;
				}
			return call_user_func_array(array($this->getPlugin(), $name), $args);
			}
	}
}
