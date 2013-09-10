<?php
namespace MOC\MocVarnish\Event;

use MOC\MocVarnish\Event\Handler\PurgeEventHandlerInterface;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * EventBroker for MOC Varnish event handling
 *
 * This class is responsible for all Purge event handling.
 *
 * @package MOC\MocVarnish
 */
class EventBroker implements SingletonInterface {

	/**
	 * @var array<\MOC\MocVarnish\Event\Handler\PurgeEventHandlerInterface>
	 */
	protected static $synchronousEventhandlers = array();

	/**
	 * @var array<\MOC\MocVarnish\Event\Handler\PurgeEventHandlerInterface>
	 */
	protected static $aSynchronousEventhandlers = array();

	/**
	 * @var bool
	 */
	protected static $isInitialized = FALSE;

	/**
	 * @var boolean
	 */
	public static $useBeanstalk = FALSE;

	/**
	 * @var string
	 */
	public static $beanstalkServer = '127.0.0.1';

	/**
	 * @var string
	 */
	public static $beanstalkTube = 'purgeevent';

	/**
	 * Initialize the registered eventhandlers found in $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['moc_varnish']['synchronousEventHandlers']  and
	 * $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['moc_varnish']['aSynchronousEventHandlers']
	 *
	 * These global arrays must hold classnames of classes that implement the PurgeEventHandlerInterface
	 *
	 * @return void
	 */
	public static function initialize() {
		if (static::$isInitialized === FALSE) {
			$objectManager = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
			foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['moc_varnish']['synchronousEventHandlers'] as $eventHandler) {
				static::registerSynchronousEventHandler($objectManager->get($eventHandler));
			}
			foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['moc_varnish']['aSynchronousEventHandlers'] as $eventHandler) {
				static::registerAsynchronousEventHandler($objectManager->get($eventHandler));
			}
			static::$isInitialized = TRUE;
		}
	}

	/**
	 * Publish a new purge event. Will store events for asynchronous eventhandlers if any are registered, and call
	 * all registered synchronous eventhandlers.
	 *
	 * @param PurgeEventInterface $event
	 * @return void
	 */
	public function publish(\MOC\MocVarnish\Event\PurgeEventInterface $event) {
		$this->initialize();

		if (count(static::$aSynchronousEventhandlers) > 0) {
			if (static::$useBeanstalk) {
				$pheanstalk = new \Pheanstalk_Pheanstalk(static::$beanstalkServer);
				$pheanstalk
					->useTube(static::$beanstalkTube)
					->put(serialize($event));
				GeneralUtility::devLog('Publishing event ' . get_class($event) . ' for asynchronous handling via beanstalk.', 'moc_varnish');
			} else {
				$crUserIdentifier = 0;
				if (is_object($GLOBALS['BE_USER']) && is_array($GLOBALS['BE_USER']->user)) {
					$crUserIdentifier = $GLOBALS['BE_USER']->user['uid'];
				}
				$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_mocvarnish_purgeevent_queue', array(
					'data' => serialize($event),
					'tstamp' => time(),
					'crdate' => time(),
					'cruser_id' => $crUserIdentifier
				));
				GeneralUtility::devLog('Publishing event ' . get_class($event) . ' for asynchronous handling via scheduler queue.', 'moc_varnish');
			}

		}

		foreach (static::$synchronousEventhandlers as $eventHandler) {
			if ($eventHandler->canHandleEvent($event)) {
				$eventHandler->handleEvent($event);
			}
		}
	}

	/**
	 * Find all stored unprocessed events and handle them. The events will be deleted afterwards
	 *
	 * @param integer $limit The maximum number of events to handle. 0 (default) means unlimited
	 * @return void
	 */
	public function handleAsyncEvents($limit = 0) {
		$this->initialize();

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_mocvarnish_purgeevent_queue', 'handled = 0', '', 'crdate ASC', $limit > 0 ? $limit : '');

		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {

			/** @var $event \MOC\MocVarnish\Event\PurgeEventInterface */
			$event = unserialize($row['data']);

			if ($event instanceof \MOC\MocVarnish\Event\PurgeEventInterface) {
				static::processAsynchronousEvent($event);
			}
			$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_mocvarnish_purgeevent_queue', 'uid=' . $row['uid']);
		}
	}

	/**
	 * Delegate handling to all registered asynchronous eventhandlers
	 *
	 * @param \MOC\MocVarnish\Event\PurgeEventInterface $event
	 * @return void
	 */
	public static function processAsynchronousEvent(\MOC\MocVarnish\Event\PurgeEventInterface $event) {
		foreach (static::$aSynchronousEventhandlers as $eventHandler) {
			if ($eventHandler->canHandleEvent($event)) {
				$eventHandler->handleEvent($event);
			}
		}
	}

	/**
	 * Register a synchronous event handler that is called immediately when the event is published
	 *
	 * @param PurgeEventHandlerInterface $eventHandler
	 * @return void
	 */
	public static function registerSynchronousEventHandler(PurgeEventHandlerInterface $eventHandler) {
		array_push(static::$synchronousEventhandlers, $eventHandler);
	}

	/**
	 * Register an asynchronous event handler that is called when the Event queue is processed
	 *
	 * @param PurgeEventHandlerInterface $eventHandler
	 * @return void
	 */
	public static function registerAsynchronousEventHandler(PurgeEventHandlerInterface $eventHandler) {
		array_push(static::$aSynchronousEventhandlers, $eventHandler);
	}

}