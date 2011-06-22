<?php // vim: ts=4 sw=4 ai:
/**
 * This file is part of BailIff
 *
 * @copyright (c) 2010, 2011 Lopo <lopo@losys.eu>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License Version 3
 */
namespace BailIff;

use Nette\DI\IContainer,
	Nette\DI\Container,
	Nette\Application\UI\Presenter,
	Nette\Application\Routers\Route,
	Nette\Environment as NEnvironment,
	Nette\Caching\Cache;

/**
 * BailIff Configurator
 * 
 * @author Lopo <lopo@losys.eu>
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
	public function __construct($containerClass='\BailIff\DI\Container')
	{
		parent::__construct($containerClass);
		self::$instance=$this;
		$container=$this->getContainer();
		// Back compatibility
		NEnvironment::setConfigurator($this);
		NEnvironment::setContext($container);


		defined('VAR_DIR') && $this->container->params['varDir']=realpath(VAR_DIR);
		defined('ROOT_DIR') && $this->container->params['rootDir']=realpath(ROOT_DIR);
		defined('BAILIFF_DIR') && $this->container->params['bailiffDir']=realpath(BAILIFF_DIR);

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
	public static function createServiceApplication(Container $container, array $options=NULL)
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

		$container->params['baseUrl']= $baseUrl= rtrim($container->httpRequest->getUrl()->getBaseUrl(), '/');
		$container->params['basePath']=preg_replace('#https?://[^/]+#A', '', $baseUrl);

		return $application;
	}

	/**
	 * @return \Nette\Application\IPresenterFactory
	 */
	public static function createServicePresenterFactory(Container $container)
	{
		return new \BailIff\Application\PresenterFactory(/*$container->params['appDir'], */$container);
	}

	/**
	 * @param \Nette\DI\Container $container
	 * @return Kdyby\Templates\ITemplateFactory
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
	public static function createServiceRouter(Container $container)
	{
		$router=new \Nette\Application\Routers\RouteList;

		$router[]=new Route('WebLoader/<id>', array(
					'presenter' => 'WebLoader',
					'action' => 'default'
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
		$files= $file===NULL? array($this->defaultConfigFile) : $file;
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
		$cacheKey=array($files, $section);
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

		// process services
		if (isset($config['services'])) {
			foreach ($config['services'] as $key => & $def) {
				if (is_scalar($def)) {
					$def=array('class' => $def);
					}

				if (method_exists(get_called_class(), "createService$key")) {
					$container->removeService($key);
					if (!isset($def['factory']) && !isset($def['class'])) {
						$def['factory']=array(get_called_class(), "createService$key");
						}
					}

				if (isset($def['option'])) {
					$def['arguments'][]=$def['option'];
					}

				if (!empty($def['run'])) {
					$def['tags']=array('run');
					}
				}
			$builder=new \Nette\DI\ContainerBuilder;
			$code.=$builder->generateCode($config['services']);
			unset($config['services']);
			}

		// expand variables
		array_walk_recursive($config, function(&$val) use ($container) {
			$val=$container->expand($val);
			});

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

		// other
		foreach ($config as $key => $value) {
			$code.=$this->generateCode('$container->params[?]= '.(is_array($value)? 'Nette\ArrayHash::from(?)' : '?'), $key, $value);
			}

		// pre-loading
		$code.=self::preloadEnvironment($container);

		// auto-start services
		$code.='foreach ($container->getServiceNamesByTag("run") as $name => $foo) { $container->getService($name); }'."\n";

		$cache->save($files, $code, array(
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
}
