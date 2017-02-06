<?php
namespace Flowpack\Monolog;

use Neos\Flow\Configuration\ConfigurationManager;
use Neos\Flow\Core\Bootstrap;
use Neos\Flow\Package\Package as BasePackage;
use Neos\Utility\Files;

/**
 *
 */
class Package extends BasePackage {
	/**
	 * Invokes custom PHP code directly after the package manager has been initialized.
	 *
	 * @param Bootstrap $bootstrap The current bootstrap
	 * @return void
	 */
	public function boot(Bootstrap $bootstrap) {
		if (!file_exists(FLOW_PATH_DATA . 'Logs')) {
			Files::createDirectoryRecursively(FLOW_PATH_DATA . 'Logs');
		}


		$monologFactory = LoggerFactory::getInstance();
		$bootstrap->setEarlyInstance(LoggerFactory::class, $monologFactory);

		$dispatcher = $bootstrap->getSignalSlotDispatcher();

		$dispatcher->connect('Neos\Flow\Core\Booting\Sequence', 'afterInvokeStep', function ($step) use ($bootstrap, $dispatcher) {
			if ($step->getIdentifier() === 'neos.flow:configuration') {
				/** @var ConfigurationManager $configurationManager */
				$configurationManager = $bootstrap->getEarlyInstance(ConfigurationManager::class);
				$monologFactory = LoggerFactory::getInstance();

				$loggerConfigurations = $configurationManager->getConfiguration(ConfigurationManager::CONFIGURATION_TYPE_SETTINGS, 'Flowpack.Monolog');
				$monologFactory->injectConfiguration($loggerConfigurations);
			}
		});
	}

}