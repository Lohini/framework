<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace BailIff;

use Nette\DI\IContainer,
	BailIff\DI\Container,
	Nette\Application\UI\Presenter,
	Nette\Application\Routers\Route,
	Nette\Environment as NEnvironment,
	Nette\Caching\Cache,
	Nette\Application\Routers\RouteList;

/**
 * BailIff Configurator
 * 
 * @author Lopo <lopo@losys.eu>
 * 
 * @property-read \BailIff\Application\Application $application
 * @property-read \Nette\Application\Routers\RouteList $router
 * @property-read \BailIff\Application\PresenterFactory $presenterFactory
 * @property-read \BailIff\Templating\TemplateFactory $templateFactory
 * @property-read \Nette\Latte\Engine $latteEngine
 * @property-read \BailIff\Localization\Translator $translator
 * @property-read \BailIff\Localization\Panel $translatorPanel
 * @property-read \BailIff\Database\Doctrine\ORM\Container $sqldb
 * @property-read \BailIff\Database\Doctrine\ODM\Container $couchdb
 * @property-read \BailIff\Database\Doctrine\Cache $doctrineCache
 * @property-read \BailIff\Database\Doctrine\Workspace $workspace
 * @property-read \BailIff\Diagnostics\Panels\Callback $callbackPanel
 * @property-read \BailIff\Security\Authenticator $authenticator
 * @property-read \BailIff\Security\User $user
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
	public function __construct($containerClass='BailIff\DI\Container')
	{
		parent::__construct($containerClass);
//		self::$instance=$this;
		$container=$this->getContainer();
		// Back compatibility
		NEnvironment::setConfigurator($this);
		NEnvironment::setContext($container);


		defined('VAR_DIR') && $this->container->params['varDir']=realpath(VAR_DIR);
		defined('ROOT_DIR') && $this->container->params['rootDir']=realpath(ROOT_DIR);
		$this->container->params['bailiffDir']=realpath(BAILIFF_DIR);
		$this->container->params['baseUrl']= $baseUrl= rtrim($container->httpRequest->getUrl()->getBaseUrl(), '/');
		$this->container->params['basePath']=preg_replace('#https?://[^/]+#A', '', $baseUrl);

		$this->onAfterLoadConfig[]=function(Container $container) {
			// Load panels
			if (!$container->params['consoleMode'] && !$container->params['productionMode']) {
				$container->translatorPanel;
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

		$class= isset($options['class'])? $options['class'] : 'BailIff\Application\Application';
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
		return new \BailIff\Application\PresenterFactory($container);
	}

	/**
	 * @param \Nette\DI\Container $container
	 * @return \BailIff\Templating\ITemplateFactory
	 */
	public static function createServiceTemplateFactory(Container $container)
	{
		return new \BailIff\Templating\TemplateFactory($container->latteEngine);
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
//		\BailIff\Latte\Macros\BailIffMacros::install($engine->parser);
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

		$router[]=new Route('WebLoader/<id>', array(
					'presenter' => 'WebLoader',
					'action' => 'default'
					));

		$router[]= $backend= new RouteList('Backend');
		$backend[]=new Route('admin/[<lang [a-z]{2}>/]<presenter>[/<action>[/<id>]]', array(
					'lang' => NEnvironment::getVariable('lang', 'en'),
					'presenter' => 'Default',
					'action' => 'default',
					'id' => NULL
					));

		return $router;
	}

	/**
	 * @param \Nette\DI\IContainer
	 * @return \BailIff\Diagnostics\Panels\Callback
	 */
	public static function createServiceCallbackPanel(IContainer $container)
	{
		return new \BailIff\Diagnostics\Panels\Callback($container);
	}

	/**
	 * @param \Nette\DI\IContainer
	 * @return \BailIff\Localization\ITranslator
	 */
	public static function createServiceTranslator(IContainer $container)
	{
		$translator=new \BailIff\Localization\Translator;
		$translator->addDictionary('BailIff', $container->expand(BAILIFF_DIR.'/lang'));
		$translator->addDictionary('Application', $container->expand(APP_DIR.'/lang'));
		return $translator;
	}

	/**
	 * @param \Nette\DI\IContainer
	 * @return \BailIff\Localization\Panel
	 */
	public static function createServiceTranslatorPanel(IContainer $container)
	{
		return new \BailIff\Localization\Panel($container);
	}

	/**
	 * @param \Nette\DI\Container $container
	 * @return \BailIff\Database\Doctrine\Cache
	 */
	public static function createServiceDoctrineCache(Container $container)
	{
		return new \BailIff\Database\Doctrine\Cache($container->cacheStorage);
	}

	/**
	 * @param \Nette\DI\Container $container
	 * @return \BailIff\Database\Doctrine\ORM\Container
	 */
	public static function createServiceSqldb(Container $container)
	{
		return new \BailIff\Database\Doctrine\ORM\Container($container, $container->params['databases']['sqldb']);
	}

	/**
	 * @param \Nette\DI\Container $container
	 * @return \BailIff\Database\Doctrine\ODM\Container
	 */
	public static function createServiceCouchdb(Container $container)
	{
		return new \BailIff\Database\Doctrine\ODM\Container($container, $container->getParam('couchdb', array()));
	}

	/**
	 * @param \Nette\DI\Container $container
	 * @return \BailIff\Database\Doctrine\Workspace
	 */
	public static function createServiceWorkspace(Container $container)
	{
		$containers=array(
			'sqldb' => $container->sqldb,
			'couchdb' => $container->couchdb
			);

		$containers+=$container->getServiceNamesByTag('database');
		return new \BailIff\Database\Doctrine\Workspace($containers);
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
	 * @return \Nette\DI\Container
	 * @throws \Nette\InvalidStateException
	 */
	public function loadConfig($file, $section=NULL)
	{
		$this->onBeforeLoadConfig($container=$this->getContainer());
		$files= $file= $file===NULL? array($this->defaultConfigFile) : $file;
		array_walk($files, function(&$file) use ($container) {
			$file=$container->expand($file);
			});
		if ($section===NULL) {
			if (PHP_SAPI==='cli') {
				$section=NEnvironment::CONSOLE;
				}
			else {
				$section= $container->params['productionMode']? NEnvironment::PRODUCTION : NEnvironment::DEVELOPMENT;
				}
			}

		$cache=new Cache($container->templateCacheStorage, 'BailIff.Configurator');
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
	 * @return Kdyby\Http\User
	 */
	public static function createServiceUser(\Nette\DI\Container $container)
	{
		$context=new Container;
		// copies services from $container and preserves lazy loading
		$context->lazyCopy('authenticator', $container);
		$context->lazyCopy('authorizator', $container);
		$context->lazyCopy('sqldb', $container);
		$context->addService('session', $container->session);

		return new \BailIff\Security\User($context);
	}

	/**
         * @param \BailIff\DI\Container $container
         * @return \BailIff\Security\Authenticator
         */
	public static function createServiceAuthenticator(Container $container)
	{
		return new \BailIff\Security\Authenticator($container->sqldb);
	}
}
