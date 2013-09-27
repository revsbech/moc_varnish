<?php
namespace MOC\MocVarnish\Scheduler;

use MOC\MocVarnish\Event\EventBroker;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Scheduler task for executing the event processing scheduler task.
 *
 * @package MOC\MocVarnish
 */
class HandlePurgeEventsTask extends \TYPO3\CMS\Scheduler\Task\AbstractTask {

	/**
	 * Limit of purge events handled per run
	 *
	 * @var integer
	 */
	public $limit = 0;

	/**
	 * @return boolean Returns TRUE on successful execution, FALSE on error
	 */
	public function execute() {
		$objectManager = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');

			// All this is done to make sure that Dependency injection works properly when the scheduler task is run
		$configurationManager = $objectManager->get('TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface');
		$configuration = $configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManager::CONFIGURATION_TYPE_FRAMEWORK);
		$configuration['extensionName'] = 'moc_varnish';
		$configuration['pluginName'] = 'cache_clear';
		$bootstrap = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Core\Bootstrap');
		$bootstrap->initialize($configuration);

		/** @var $eventBroker \MOC\MocVarnish\Event\EventBroker */
		$eventBroker = GeneralUtility::makeInstance('MOC\MocVarnish\Event\EventBroker');
		$eventBroker->handleAsyncEvents($this->limit);

		return TRUE;
	}


}