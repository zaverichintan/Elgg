<?php

/**
 * Provides common Elgg services.
 *
 * We extend the container because it allows us to document properties in the PhpDoc, which assists
 * IDEs to auto-complete properties and understand the types returned. Extension allows us to keep
 * the container generic.
 *
 * @access private
 * 
 * @property-read ElggVolatileMetadataCache $metadataCache
 * @property-read Elgg_ActionsService $actions
 * @property-read ElggPluginHookService $hooks
 * @property-read ElggEventService $events
 * @property-read ElggViewService $views
 * @property-read ElggAutoP $autoP
 * @property-read ElggDatabase $db
 * @property-read ElggAutoloadManager $autoloadManager
 * @property-read ElggLogger $logger
 * @property-read Elgg_AmdConfig $amdConfig
 * @property-read ElggSession $session
 * 
 * @package Elgg.Core
 */
class Elgg_ServiceProvider extends Elgg_DIContainer {

	/**
	 * Get a value from service provider
	 * 
	 * @param string $name Name of the value
	 * @return mixed
	 * @throws RuntimeException
	 */
	public function __get($name) {
		if ($this->has($name)) {
			return $this->get($name);
		}
		throw new RuntimeException("Property '$name' does not exist");
	}

	/**
	 * Constructor
	 * 
	 * @param ElggAutoloadManager $autoload_manager Class autoloader
	 */
	public function __construct(ElggAutoloadManager $autoload_manager) {
		$this->setValue('autoloadManager', $autoload_manager);
		$this->setValue('actions', new Elgg_ActionsService());
		$this->setValue('hooks', new ElggPluginHookService());
		$this->setValue('events', new ElggEventService());

		$this->setFactory('views', array($this, 'getViews'));
		$this->setFactory('autoP', array($this, 'getAutoP'));
		$this->setFactory('logger', array($this, 'getLogger'));
		$this->setFactory('metadataCache', array($this, 'getMetadataCache'));
		$this->setFactory('db', array($this, 'getDb'));
		$this->setFactory('amdConfig', array($this, 'getAmdConfig'));
		$this->setFactory('session', array($this, 'getSession'));
	}

	/**
	 * Metadata cache factory
	 * 
	 * @param Elgg_DIContainer $c Dependency injection container
	 * @return ElggVolatileMetadataCache
	 */
	protected function getMetadataCache(Elgg_DIContainer $c) {
		return new ElggVolatileMetadataCache();
	}

	/**
	 * Database factory
	 * 
	 * @param Elgg_DIContainer $c Dependency injection container
	 * @return ElggDatabase
	 */
	protected function getDb(Elgg_DIContainer $c) {
		return new ElggDatabase();
	}

	/**
	 * Logger factory
	 * 
	 * @param Elgg_DIContainer $c Dependency injection container
	 * @return ElggLogger
	 */
	protected function getLogger(Elgg_DIContainer $c) {
		return new ElggLogger($c->get('hooks'));
	}

	/**
	 * Views service factory
	 * 
	 * @param Elgg_DIContainer $c Dependency injection container
	 * @return ElggViewService
	 */
	protected function getViews(Elgg_DIContainer $c) {
		return new ElggViewService($c->hooks, $c->logger);
	}

	/**
	 * Paragraph formatter factory
	 * 
	 * @param Elgg_DIContainer $c Dependency injection container
	 * @return ElggAutoP
	 */
	protected function getAutoP(Elgg_DIContainer $c) {
		return new ElggAutoP();
	}

	/**
	 * AMD Config factory
	 * 
	 * @param Elgg_DIContainer $c Dependency injection container
	 * @return Elgg_AmdConfig
	 */
	protected function getAmdConfig(Elgg_DIContainer $c) {
		$obj = new Elgg_AmdConfig();
		$obj->setBaseUrl(_elgg_get_simplecache_root() . "js/");
		return $obj;
	}

	/**
	 * Session factory
	 * 
	 * @param Elgg_DIContainer $c Dependency injection container
	 * @return ElggSession
	 */
	protected function getSession(Elgg_DIContainer $c) {
		$handler = new Elgg_Http_DatabaseSessionHandler($c->db);
		$storage = new Elgg_Http_NativeSessionStorage($handler);
		$session = new ElggSession($storage);
		$session->setName('Elgg');
		return $session;
	}
}
