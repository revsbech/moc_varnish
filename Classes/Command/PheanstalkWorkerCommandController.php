<?php
namespace MOC\MocVarnish\Command;

use MOC\MocVarnish\Event\EventBroker;
use MOC\MocVarnish\VarnishPurgeService\CurlVarnishPurgeService;

/**
 * Command line controller for starting the beanstalk worker process
 *
 * Will start the workingprocess in daemon mode, listening to the configured tube on the beanstalk server.
 *
 * @package MOC\MocVarnish
 */
class PheanstalkWorkerCommandController extends \TYPO3\CMS\Extbase\Mvc\Controller\CommandController {

	/**
	 * Start a new Pheanstalk worker for the MOC Varnish event queue
	 *
	 * @return void
	 */
	public function startCommand() {
		$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['moc_varnish']);
		if (intval($extConf['event.']['enable_beanstalk']) == 0) {
			$this->outputLine('Beanstalk not enabled in extconf.');
			$this->sendAndExit(0);
		}

			// Print is used, since outputLine will buffer output, and this process is in daemon mode
		print 'Starting up. Listening to tube ' . $extConf['event.']['beanstalk_tube'] . ' on server ' . $extConf['event.']['beanstalk_server'] . PHP_EOL;
		$pheanstalk = new \Pheanstalk_Pheanstalk($extConf['event.']['beanstalk_server']);
		EventBroker::initialize();

			// Make sure that the Curl Purgeservice is executed immediately when clearCacheForUrl is run
		CurlVarnishPurgeService::$executeImmediately = TRUE;

		while (TRUE) {
			$job = $pheanstalk
				->watch($extConf['event.']['beanstalk_tube'])
				->ignore('default')
				->reserve();

			/** @var $event \MOC\MocVarnish\Event\PurgeEventInterface */
			$event = unserialize($job->getData());

			if ($event instanceof \MOC\MocVarnish\Event\PurgeEventInterface) {
				print 'Processing event ' . $event . PHP_EOL;
				EventBroker::processAsynchronousEvent($event);
			}
			$pheanstalk->delete($job);
		}
	}

}