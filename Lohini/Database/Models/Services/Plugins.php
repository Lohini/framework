<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\Database\Models\Services;
/**
 * This file is part of the Nella Framework.
 *
 * Copyright (c) 2006, 2011 Patrik Votoček (http://patrik.votocek.cz)
 *
 * This source file is subject to the GNU Lesser General Public License. For more information please see http://nellacms.com
 * @author Patrik Votoček
 */
/**
 * Lohini port
 * @author Lopo <lopo@lohini.net>
 */

use Lohini\Plugins\Plugin as APlugin,
	Lohini\Plugins\PluginException;

/**
 * Plugins model service
 */
class Plugins
extends \Lohini\Database\Doctrine\ORM\BaseService
{
	/**
	 * @param array $values
	 * @param bool $withoutFlush
	 * @return Entity
	 */
	public function create($values, $withoutFlush=FALSE)
	{
		try {
			$class=$this->getEntityClass();
			$entity=new $class($values['name']);
			$this->fillData($entity, $values);
			$em=$this->getEntityManager();
			$em->persist($entity);
			if (!$withoutFlush) {
				$em->flush();
				}
			return $entity;
			}
		catch (\PDOException $e) {
			$this->processPDOException($e);
			}
		catch (\Exception $e) {
			$x;
			}
	}

	public function disableUpdatedSources($withoutFlush=FALSE)
	{
		foreach ($this->getRepository()->findAll() as $plugin) {
			if (!$plugin->enabled) {
				continue;
				}
			$class=$plugin->pluginClass;
			if ($class::VERSION!=$plugin->iversion) {
				$plugin->enabled=FALSE;
				}
			}
		if (!$withoutFlush) {
			$this->getEntityManager()->flush();
			}
	}

	/**
	 * @param string $name
	 * @return bool
	 */
	public function installPlugin($name)
	{
		if (($entity=$this->getRepository()->findOneByName($name))===NULL) {
			return PluginException::notFound($name);
			}
		if ($entity->installed) {
			return TRUE;
			}
		if ($entity->state!=APlugin::STATE_REGISTERED) {
			return PluginException::installError("Plugin isn't registered");
			}
		try {
			$plugin=$entity->plugin;
			$container=$this->getContainer();

			$plugin->preInstall();
			$plugin->install();
			$plugin->postInstall();

			$entity->iversion=$plugin::VERSION;
			$entity->setState(APlugin::STATE_INSTALLED);
			$this->getEntityManager()->flush();

			return TRUE;
			}
		catch (PluginException $e) {
			return $e;
			}
	}

	/**
	 * @param string $name
	 * @return bool
	 */
	public function updatePlugin($name)
	{
		if (($entity=$this->getRepository()->findOneByName($name))===NULL) {
			return PluginException::notFound($name);
			}
		$plugin=$entity->plugin;
		if (!$entity->installed || $entity->iversion==$plugin::VERSION) {
			return TRUE;
			}
		try {
			$container=$this->getContainer();

			$plugin->preUpdate();
			$plugin->update();
			$plugin->postUpdate();

			$entity->iversion=$plugin::VERSION;
			$this->getEntityManager()->flush();

			return TRUE;
			}
		catch (PluginException $e) {
			return $e;
			}
	}

	/**
	 * @param string $name
	 * @return bool
	 */
	public function uninstallPlugin($name)
	{
		if (($entity=$this->getRepository()->findOneByName($name))===NULL) {
			return PluginException::notFound($name);
			}
		if (!$entity->installed) {
			return TRUE;
			}
		try {
			$plugin=$entity->plugin;
			$container=$this->getContainer();

			$plugin->preUninstall();
			$plugin->uninstall();
			$plugin->postUninstall();

			$entity->iversion=0;
			$entity->setState(APlugin::STATE_REGISTERED);
			$this->getEntityManager()->flush();

			return TRUE;
			}
		catch (PluginException $e) {
			return $e;
			}
	}

	/**
	 * @param string $name
	 * @return bool
	 */
	public function enablePlugin($name)
	{
		if (($entity=$this->getRepository()->findOneByName($name))===NULL) {
			return PluginException::notFound($name);
			}
		if ($entity->enabled) {
			return TRUE;
			}
		try {
			$entity->setEnabled();
			$this->getEntityManager()->flush();

			return TRUE;
			}
		catch (PluginException $e) {
			return $e;
			}
	}

	/**
	 * @param string $name
	 * @return bool
	 */
	public function disablePlugin($name)
	{
		if (($entity=$this->getRepository()->findOneByName($name))===NULL) {
			return PluginException::notFound($name);
			}
		if (!$entity->enabled) {
			return TRUE;
			}
		try {
			$entity->setEnabled(FALSE);
			$this->getEntityManager()->flush();

			return TRUE;
			}
		catch (PluginException $e) {
			return $e;
			}
	}

	/**
	 * @param string $name
	 * @return bool
	 */
	public function removePlugin($name)
	{
		if (($entity=$this->getRepository()->findOneByName($name))===NULL) {
			return PluginException::notFound($name);
			}
		if ($entity->installed) {
			return new \Nette\InvalidStateException('Plugin installed, uninstall first.');
			}
		try {
			$this->delete($entity);

			return TRUE;
			}
		catch (PluginException $e) {
			return $e;
			}
	}
}
