<?php
namespace MOC\MocVarnish\Event\Handler;

use MOC\MocVarnish\Event\PurgeEventInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Debug event handler for MOC Vanrish purge event queue system
 *
 * Will simply debug the type of the event handled.
 *
 * @package MOC\MocVarnish
 */
class DebugPurgeEventHandler implements PurgeEventHandlerInterface {

	/**
	 * Handle a purge event
	 *
	 * @param PurgeEventInterface $event
	 * @return void
	 */
	public function handleEvent(PurgeEventInterface $event) {
		debug('Handling event of type ' . get_class($event), 'moc_varnish');
	}

	/**
	 * This event handler can handle all kinds of events.
	 *
	 * @param PurgeEventInterface $event
	 * @return boolean
	 */
	public function canHandleEvent(PurgeEventInterface $event) {
		return TRUE;
	}

}