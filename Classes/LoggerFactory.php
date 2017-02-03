<?php
namespace Flowpack\Monolog;

use Monolog\Handler\HandlerInterface;
use Neos\Flow\Annotations as Flow;
use Monolog\Logger;
use Neos\Flow\Configuration\Exception\InvalidConfigurationException;
use Neos\Utility\PositionalArraySorter;

/**
 * Class LoggerFactory
 *
 * @Flow\Scope("singleton")
 */
class LoggerFactory {

	protected static $instance;

	/**
	 * @var array
	 */
	protected $loggerInstances = [];

	/**
	 * @var array
	 */
	protected $handlerInstances = [];

	/**
	 * @var array
	 */
	protected $configuration;

	/**
	 * self made singleton
	 */
	protected function __construct() {}

	/**
	 * @param array $configuration
	 */
	public function injectConfiguration(array $configuration) {
		$this->configuration = $configuration;
	}

	/**
	 * @param string $identifier
	 * @return Logger
	 * @throws InvalidConfigurationException
	 * @api
	 */
	public function create($identifier) {
		if (!isset($this->configuration['logger'][$identifier])) {
			throw new InvalidConfigurationException(sprintf('The required monolog configuration for the given identifier "%s" was not found. Please configure a logger with this identifier in "Flowpack.Monolog.logger"', $identifier), 1435842118);
		}

		return $this->createFromConfiguation($identifier, $this->configuration['logger'][$identifier]);
	}

	/**
	 * Creates a monolog instance.
	 *
	 * @param string $identifier An identifier for the logger
	 * @param array $configuration
	 * @return Logger
	 */
	public function createFromConfiguation($identifier, array $configuration) {
		if (isset($this->loggerInstances[$identifier])) {
			return $this->loggerInstances[$identifier];
		}

		$logger = new Logger($identifier);

		$handlerSorter = new PositionalArraySorter($configuration['handler']);
		foreach ($handlerSorter->toArray() as $index => $handlerConfiguration) {
			if (is_string($handlerConfiguration)) {
				$handler = $this->getConfiguredHandler($handlerConfiguration);
			}

			if (is_array($handlerConfiguration)) {
				$handlerIdentifier = $identifier . md5(json_encode($handlerConfiguration));
				$handler = $this->instanciateHandler($handlerIdentifier, $handlerConfiguration);
			}

			if ($handler !== NULL) {
				$logger->pushHandler($handler);
			}
		}

		$this->loggerInstances[$identifier] = $logger;
		return $logger;
	}

	/**
	 * @param $identifier
	 * @return HandlerInterface
	 * @throws InvalidConfigurationException
	 * @api
	 */
	public function getConfiguredHandler($identifier) {
		if (!isset($this->configuration['handler'][$identifier])) {
			throw new InvalidConfigurationException(sprintf('The required handler configuration for the given identifier "%s" was not found. Please configure a logger with this identifier in "Flowpack.Monolog.handler"', $identifier), 1436767040);
		}

		return $this->instanciateHandler($identifier, $this->configuration['handler'][$identifier]);
	}

	/**
	 * Home brew singleton because it is used so early.
	 *
	 * @return LoggerFactory
	 */
	public static function getInstance() {
		if (static::$instance === NULL) {
			static::$instance = new static();
		}
		return static::$instance;
	}

	/**
	 * @param string $identifier
	 * @param array $handlerConfiguration
	 * @return HandlerInterface
	 * @throws InvalidConfigurationException
	 */
	protected function instanciateHandler($identifier, $handlerConfiguration) {
		if (!isset($this->handlerInstances[$identifier])) {
			$handlerClass = isset($handlerConfiguration['className']) ? $handlerConfiguration['className'] : NULL;

			if (!class_exists($handlerClass)) {
				throw new InvalidConfigurationException(sprintf('The given handler class "%s" does not exist, please check configuration for handler "%s".', $handlerClass, $identifier), 1436767219);
			}

			$arguments = (isset($handlerConfiguration['arguments']) && is_array($handlerConfiguration['arguments'])) ? $handlerConfiguration['arguments'] : [];
			$this->handlerInstances[$identifier] = $this->instantiateClass($handlerClass, $arguments);
		}

		return $this->handlerInstances[$identifier];
	}

	/**
	 * Speed optimized alternative to ReflectionClass::newInstanceArgs()
	 *
	 * Duplicated from Neos\Flow\ObjectManagement\ObjectManager to avoid dependency in order to be able to use for SystemLogging.
	 *
	 * @param string $className Name of the class to instantiate
	 * @param array $arguments Arguments to pass to the constructor
	 * @return object The object
	 */
	protected function instantiateClass($className, array $arguments) {
		switch (count($arguments)) {
			case 0:
				$object = new $className();
				break;
			case 1:
				$object = new $className($arguments[0]);
				break;
			case 2:
				$object = new $className($arguments[0], $arguments[1]);
				break;
			case 3:
				$object = new $className($arguments[0], $arguments[1], $arguments[2]);
				break;
			case 4:
				$object = new $className($arguments[0], $arguments[1], $arguments[2], $arguments[3]);
				break;
			case 5:
				$object = new $className($arguments[0], $arguments[1], $arguments[2], $arguments[3], $arguments[4]);
				break;
			case 6:
				$object = new $className($arguments[0], $arguments[1], $arguments[2], $arguments[3], $arguments[4], $arguments[5]);
				break;
			default:
				$class = new \ReflectionClass($className);
				$object = $class->newInstanceArgs($arguments);
		}
		return $object;
	}
}