<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of Lohini
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@lohini.net>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace Lohini\DI;

use Nette\Application\UI\Presenter,
	Nette\Application\Routers\Route,
	Nette\Environment,
	Nette\Caching\Cache,
	Nette\Application\Routers\RouteList;

/**
 * Lohini Configurator
 * 
 * @author Lopo <lopo@lohini.net>
 * 
 * @property-read \Lohini\Application\Application $application
 * @property-read \Nette\Application\Routers\RouteList $router
 * @property-read \Lohini\Application\PresenterFactory $presenterFactory
 * @property-read \Lohini\Templating\TemplateFactory $templateFactory
 * @property-read \Nette\Latte\Engine $latteEngine
 * @property-read \Lohini\Localization\Translator $translator
 * @property-read \Lohini\Localization\Panel $translatorPanel
 * @property-read \Lohini\Database\Doctrine\ORM\Container $sqldb
 * @property-read \Lohini\Database\Doctrine\ODM\Container $couchdb
 * @property-read \Lohini\Database\Doctrine\Cache $doctrineCache
 * @property-read \Lohini\Database\Doctrine\Workspace $workspace
 * @property-read \Lohini\Plugins\Manager $pluginManager
 * @property-read \Lohini\Diagnostics\Panels\Callback $callbackPanel
 * @property-read \Lohini\Security\Authenticator $authenticator
 * @property-read \Lohini\Security\User $user
 * @property-read \Lohini\DI\TexyContainer $texy
 */
class Configurator
extends \Nette\Configurator
{
	/** @var array */
	public $onBeforeLoadConfig=array();
	/** @var array */
	public $onAfterLoadConfig=array();


	/**
	 * Gets initial instance of context
	 */
	public function __construct($containerClass='Lohini\DI\Container')
	{
		parent::__construct($containerClass);
//		self::$instance=$this;
		$container=$this->getContainer();
		// Back compatibility
		Environment::setConfigurator($this);
		Environment::setContext($container);


		defined('VAR_DIR') && $this->container->params['varDir']=realpath(VAR_DIR);
		defined('ROOT_DIR') && $this->container->params['rootDir']=realpath(ROOT_DIR);
		$this->container->params['lohiniDir']=realpath(LOHINI_DIR);
		$this->container->params['baseUrl']= $baseUrl= rtrim($container->httpRequest->getUrl()->getBaseUrl(), '/');
		$this->container->params['basePath']=preg_replace('#https?://[^/]+#A', '', $baseUrl);

		$this->onAfterLoadConfig[]=function(Container $container) {
			// Load panels
			if (!$container->params['consoleMode']) {
				$container->translatorPanel;
				$container->userPanel;
				}
			};
	}

	/**
	 * @param Container $container
	 * @param array $options
	 * @return \Nette\Application\Application
	 */
	public static function createServiceApplication(\Nette\DI\Container $container, array $options=NULL)
	{
		$context=new Container;
		$context->addService('httpRequest', $container->httpRequest);
		$context->addService('httpResponse', $container->httpResponse);
		$context->addService('session', $container->session);
		$context->addService('presenterFactory', $container->presenterFactory);
		$context->addService('router', $container->router);
//		$context->addService('console', $container->console);

		Presenter::$invalidLinkMode=
			$container->getParam('productionMode', TRUE)
				? Presenter::INVALID_LINK_SILENT
				: Presenter::INVALID_LINK_WARNING;

		$class= isset($options['class'])? $options['class'] : 'Lohini\Application\Application';
		$application=new $class($context);
		$application->catchExceptions=$container->getParam('productionMode', TRUE);

		if ($container->session->exists()) {
			$application->onStartup[]=function() use ($container) {
						$container->session->start(); // opens already started session
						};
			}

		return $application;
	}

	/**
	 * @return \Nette\Application\IPresenterFactory
	 */
	public static function createServicePresenterFactory(\Nette\DI\Container $container)
	{
		return new \Lohini\Application\PresenterFactory($container);
	}

	/**
	 * @param \Nette\DI\Container $container
	 * @return \Lohini\Templating\ITemplateFactory
	 */
	public static function createServiceTemplateFactory(Container $container)
	{
		return new \Lohini\Templating\TemplateFactory($container->latteEngine);
	}

	/**
	 * @param \Nette\DI\Container $container
	 * @return \Nette\Latte\Engine
	 */
	public static function createServiceLatteEngine(Container $container)
	{
		$engine=new \Nette\Latte\Engine;
		foreach ($container->getParam('macros', array()) as $macroSet) {
			call_user_func(callback($macroSet), $engine->parser);
			}
//		\Lohini\Latte\Macros\LohiniMacros::install($engine->parser);
		return $engine;
	}

	/**
	 * @param \Nette\DI\Container $container
	 * @return \Nette\Application\Routers\RouteList
	 */
	public static function createServiceRouter(\Nette\DI\Container $container)
	{
		$router=new RouteList;

		RouteList::extensionMethod('getRootLink', function() use ($router, $container) {
				return $router->constructUrl(
						$router->match(new \Nette\Http\Request(new \Nette\Http\UrlScript('/index.php'))),
						$container->httpRequest->getUrl()
						);
				});

		$router[]=new Route('WebLoader/<id>', 'WebLoader:default');

		$backend= $router[]= new RouteList('Backend');
		$backend[]=new Route('admin[/<lang=en [a-z]{2}>]/<presenter>[/<action>[/<id>]]', 'Default:default');

		$container->pluginManager->injectRoutes($router);

		return $router;
	}

	/**
	 * @param \Nette\DI\Container
	 * @return \Lohini\Diagnostics\Panels\Callback
	 */
	public static function createServiceCallbackPanel(Container $container)
	{
		return new \Lohini\Diagnostics\Panels\Callback($container);
	}

	/**
	 * @param \Nette\DI\Container
	 * @return \Lohini\Localization\ITranslator
	 */
	public static function createServiceTranslator(Container $container)
	{
		$translator=new \Lohini\Localization\Translator;
		$translator->addDictionary('Lohini', LOHINI_DIR.'/lang');
		$container->pluginManager->injectTranslations($translator);
		return $translator;
	}

	/**
	 * @param \Nette\DI\Container
	 * @return \Lohini\Localization\Panel
	 */
	public static function createServiceTranslatorPanel(Container $container)
	{
		return new \Lohini\Localization\Panel($container);
	}

	/**
	 * @param \Nette\DI\Container
	 * @return \Lohini\Security\Panel
	 */
	public static function createServiceUserPanel(Container $container)
	{
		return new \Lohini\Security\Panel($container->user->identity, $container->sqldb);
	}

	/**
	 * @param \Nette\DI\Container $container
	 * @return \Lohini\Plugins\IManager
	 */
	public static function createServicePluginManager(Container $container)
	{
		return new \Lohini\Plugins\Manager($container);
	}

	/**
	 * @param \Nette\DI\Container $container
	 * @return \Lohini\Database\Doctrine\Cache
	 */
	public static function createServiceDoctrineCache(Container $container)
	{
		return new \Lohini\Database\Doctrine\Cache($container->cacheStorage);
	}

	/**
	 * @param \Nette\DI\Container $container
	 * @return \Lohini\Database\Doctrine\ORM\Container
	 */
	public static function createServiceSqldb(Container $container)
	{
		return new \Lohini\Database\Doctrine\ORM\Container($container, $container->params['databases']['sqldb']);
	}

	/**
	 * @param \Nette\DI\Container $container
	 * @return \Lohini\Database\Doctrine\ODM\Container
	 */
	public static function createServiceCouchdb(Container $container)
	{
		return new \Lohini\Database\Doctrine\ODM\Container($container, $container->getParam('couchdb', array()));
	}

	/**
	 * @param \Nette\DI\Container $container
	 * @return \Lohini\Database\Doctrine\Workspace
	 */
	public static function createServiceWorkspace(Container $container)
	{
		$containers=array(
			'sqldb' => $container->sqldb,
			'couchdb' => $container->couchdb
			);

		$containers+=$container->getServiceNamesByTag('database');
		return new \Lohini\Database\Doctrine\Workspace($containers);
	}

	/**
	 * Merges 2nd config into 1st
	 *
	 * @param array $c1
	 * @param array $c2
	 * @return array
	 */
	public static function mergeConfigs(array $c1, array $c2)
	{
		foreach ($c2 as $k => $v) {
			if (array_key_exists($k, $c1) && $v!==NULL && (!is_scalar($v) || is_array($v))) {
				$c1[$k]=self::mergeConfigs($c1[$k], $c2[$k]);
				}
			else {
				$c1[$k]=$v;
				}
			}
		return $c1;
	}

	/**
	 * Loads configuration from file(s) and process it.
	 * @param array|string $file
	 * @return \Nette\DI\Container
	 * @throws \Nette\InvalidStateException
	 * @throws \Nette\InvalidArgumentException
	 */
	public function loadConfig($file=NULL, $section=NULL)
	{
		$this->onBeforeLoadConfig($container=$this->getContainer());
		if ($file===NULL) {
			$files=array($this->defaultConfigFile);
			}
		elseif (is_array($file)) {
			$files=$file;
			}
		elseif (is_string($file)) {
			$files=array($file);
			}
		else {
			throw new \Nette\InvalidArgumentException('Invalid type of file argument');
			}
		array_walk($files, function(&$file) use ($container) {
			$file=$container->expand($file);
			});
		if ($section===NULL) {
			if (PHP_SAPI==='cli') {
				$section=Environment::CONSOLE;
				}
			else {
				$section= $container->params['productionMode']? Environment::PRODUCTION : Environment::DEVELOPMENT;
				}
			}

		$cache=new Cache($container->templateCacheStorage, 'Lohini.Configurator');
		$cacheKey=array((array)$container->params, $files, $section);
		$cached=$cache->load($cacheKey);
		if ($cached) {
			require $cached['file'];
			fclose($cached['handle']);
			$this->onAfterLoadConfig($container);
			return $this->getContainer();
			}

		$config=array();
		foreach ($files as $file) {
			$config=$this->mergeConfigs($config, \Nette\Config\Config::fromFile($file, $section));
			}

		$code="<?php\n// source file(s) [".implode(', ', $files)."]\n\n";
/*
		// add expanded variables
		while (!empty($config['variables'])) {
			$old=$config['variables'];
			foreach ($config['variables'] as $key => $value) {
				try {
					$code.=$this->generateCode('$container->params[?] = ?', $key, $container->params[$key]=$container->expand($value));
					unset($config['variables'][$key]);
					}
				catch (\Nette\InvalidArgumentException $e) {}
				}
			if ($old===$config['variables']) {
				throw new \Nette\InvalidStateException('Unable to expand variables: '.implode(', ', array_keys($old)).'.');
				}
			}
		unset($config['variables']);
*/
		// process services
		if (isset($config['services'])) {
			foreach ($config['services'] as $key => & $def) {
				if (is_array($def)) {
					if (method_exists(get_called_class(), "createService$key") && !isset($def['factory']) && !isset($def['class'])) {
						$def['factory']=array(get_called_class(), "createService$key");
						}
					if (isset($def['option'])) {
						$def['arguments'][]=$def['option'];
						}
					if (!empty($def['run'])) {
						$def['tags']=array('run');
						}
					}
				}
			$builder=new \Nette\DI\ContainerBuilder;
			$code.=$builder->generateCode($config['services']);
			unset($config['services']);
			}

		// consolidate variables
		if (!isset($config['variables'])) {
			$config['variables']=array();
			}
		foreach ($config as $key => $value) {
			if (!in_array($key, array('variables', 'services', 'php', 'const', 'mode'))) {
				$config['variables'][$key]=$value;
				}
			}

		// pre-expand variables at compile-time
		$variables=$config['variables'];
		array_walk_recursive($config, function(&$val) use ($variables) {
					$val=Configurator::preExpand($val, $variables);
					});

		// add variables
		foreach ($config['variables'] as $key => $value) {
			$code.=$this->generateCode('$container->params[?] = ?', $key, $value);
			}

		// PHP settings
		if (isset($config['php'])) {
			foreach ($config['php'] as $key => $value) {
				if (is_array($value)) { // back compatibility - flatten INI dots
					foreach ($value as $k => $v) {
						$code.=$this->configurePhp("$key.$k", $v);
						}
					}
				else {
					$code.=$this->configurePhp($key, $value);
					}
				}
			unset($config['php']);
			}

		// define constants
		if (isset($config['const'])) {
			foreach ($config['const'] as $key => $value) {
				$code.=$this->generateCode('define', $key, $value);
				}
			unset($config['const']);
			}

		// pre-loading
		$code.=self::preloadEnvironment($container);

		// auto-start services
		$code.='foreach ($container->getServiceNamesByTag("run") as $name => $foo) { $container->getService($name); }'."\n";

		$cache->save($cacheKey, $code, array(
			Cache::FILES => $files,
			));

		\Nette\Utils\LimitedScope::evaluate($code, array('container' => $container));
		$this->onAfterLoadConfig($container);
		return $this->getContainer();
	}

	/**
	 * @param string $statement
	 * @return string
	 */
	private static function generateCode($statement)
	{
		$args=func_get_args();
		unset($args[0]);
		foreach ($args as &$arg) {
			$arg=var_export($arg, TRUE);
			$arg=preg_replace("#(?<!\\\)'%([\w-]+)%'#", '\$container->params[\'$1\']', $arg);
			$arg=preg_replace("#(?<!\\\)'(?:[^'\\\]|\\\.)*%(?:[^'\\\]|\\\.)*'#", '\$container->expand($0)', $arg);
			}
		if (strpos($statement, '?')===FALSE) {
			return $statement.='('.implode(', ', $args).");\n\n";
			}
		$a=strpos($statement, '?');
		$i=1;
		while ($a!==FALSE) {
			$statement=substr_replace($statement, $args[$i], $a, 1);
			$a=strpos($statement, '?', $a+strlen($args[$i]));
			$i++;
			}
		return "$statement;\n\n";
	}

	/**
	 * @param \Nette\DI\Container $container
	 * @return Lohini\Security\User
	 */
	public static function createServiceUser(\Nette\DI\Container $container)
	{
		$context=new Container;
		// copies services from $container and preserves lazy loading
		$context->lazyCopy('authenticator', $container);
		$context->lazyCopy('authorizator', $container);
		$context->lazyCopy('sqldb', $container);
		$context->addService('session', $container->session);

		return new \Lohini\Security\User($context);
	}

	/**
         * @param \Lohini\DI\Container $container
         * @return \Lohini\Security\Authenticator
         */
	public static function createServiceAuthenticator(Container $container)
	{
		return new \Lohini\Security\Authenticator($container->sqldb);
	}

	/**
	 * @param \Lohini\DI\Container $container
	 * @return \Lohini\Security\Authorizator
	 */
	public static function createServiceAuthorizator(Container $container)
	{
		return new \Lohini\Security\Authorizator($container->users);
	}

	/**
	 * @param Container $container
	 * @return \Lohini\Utils\TexyContainer
	 */
	public static function createServiceTexy(Container $container)
	{
		return new \Lohini\DI\TexyContainer($container);
	}
}
