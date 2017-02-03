<?php
namespace Flowpack\Monolog;

use Monolog\Logger;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Log\Backend\BackendInterface;

/**
 *
 */
class FlowBackendFacade implements BackendInterface {

	/**
	 * @var array
	 */
	protected $options;

	/**
	 * @var Logger
	 */
	protected $logger;

	/**
	 * @param array $options
	 */
	public function __construct($options) {
		$this->options = $options;
	}

	/**
	 * Carries out all actions necessary to prepare the logging backend, such as opening
	 * the log file or opening a database connection.
	 *
	 * @return void
	 * @api
	 */
	public function open() {
		$loggerFactory = LoggerFactory::getInstance();

		if (isset($this->options['configuration'])) {
			$this->logger = $loggerFactory->createFromConfiguation($this->options['identifier'], $this->options['configuration']);
		} else {
			$this->logger = $loggerFactory->create($this->options['identifier']);
		}
	}

	/**
	 * Appends the given message along with the additional information into the log.
	 *
	 * @param string $message The message to log
	 * @param integer $severity One of the LOG_* constants
	 * @param mixed $additionalData A variable containing more information about the event to be logged
	 * @param string $packageKey Key of the package triggering the log (determined automatically if not specified)
	 * @param string $className Name of the class triggering the log (determined automatically if not specified)
	 * @param string $methodName Name of the method triggering the log (determined automatically if not specified)
	 * @return void
	 * @api
	 */
	public function append($message, $severity = LOG_INFO, $additionalData = NULL, $packageKey = NULL, $className = NULL, $methodName = NULL) {
		switch ($severity) {
			case LOG_EMERG:
				$monologLevel = Logger::EMERGENCY;
				break;
			case LOG_ALERT:
				$monologLevel = Logger::ALERT;
				break;
			case LOG_CRIT:
				$monologLevel = Logger::CRITICAL;
				break;
			case LOG_ERR:
				$monologLevel = Logger::ERROR;
				break;
			case LOG_WARNING:
				$monologLevel = Logger::WARNING;
				break;
			case LOG_NOTICE:
				$monologLevel = Logger::NOTICE;
				break;
			case LOG_DEBUG:
				$monologLevel = Logger::DEBUG;
				break;
			default:
				$monologLevel = Logger::INFO;
				break;
		}

		$this->logger->log($monologLevel, $message, [
			'packageKey' => $packageKey,
			'className' => $className,
			'methodName' => $methodName,
			'additionalData' => $additionalData
		]);
	}

	/**
	 * Carries out all actions necessary to cleanly close the logging backend, such as
	 * closing the log file or disconnecting from a database.
	 *
	 * @return void
	 * @api
	 */
	public function close() {
	}

}

