<?php


namespace Core;


use API\ApiObject;
use API\ApiRequest;
use API\ApiRouter;
use API\ControllerInterface;
use AppConfig\Config;
use Log\Logger;
use RuntimeException;
use SimpleCache\FileCache;

class AppContainer
{
    private $parameters = [];
    private $logger = null;
    private $config = null;
    private $cache = null;
    private $router = null;

    public function __construct(array $parameters)
    {
        if (!isset($parameters['document_root'])) {
            throw new RuntimeException('AppContainer: document_root parameter is required');
        }
        $this->parameters = $parameters;
    }

    public function getLogger(): Logger
    {
        if ($this->logger === null) {
            $this->logger = new Logger();
        }
        return $this->logger;
    }

    public function getConfig(): Config
    {
        if ($this->config === null) {
            $logger = $this->getLogger();
            $this->config = new Config($logger, $this->parameters['document_root'] . 'app_config.json');
        }
        return $this->config;
    }

    public function getCache(): FileCache
    {
        if ($this->cache === null) {
            $config = $this->getConfig();
            $this->cache = new FileCache($this->parameters['document_root'] . $config->getCachePath(),
                $config->getCacheDefaultTtl());
        }
        return $this->cache;
    }

    public function getRouter(): ApiRouter
    {
        if ($this->router === null) {
            $this->router = new ApiRouter(new ApiRequest(), $this->parameters['allowed_api_methods']);
        }
        return $this->router;
    }

    public function getController(string $className): ControllerInterface
    {
        if (class_exists($className)) {
            return new $className($this->getLogger(), $this->getCache(), $this->getConfig(), $this->getRouter()->getResponse());
        } else {
            throw new RuntimeException("AppContainer: Class $className not exist!");
        }
    }

    public function getApiObject(string $className, string $resourceName): ApiObject
    {
        return new ApiObject($resourceName, $this->getController($className), $this->getRouter());
    }
}