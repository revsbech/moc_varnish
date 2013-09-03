<?php
namespace MOC\MocVarnish\Event\Handler;

use MOC\MocVarnish\Event\PurgeEventInterface;

/**
 * Intercace for MCO Vanrish event queue eventhandlers
 *
 * @package MOC\MocVarnish
 */
interface PurgeEventHandlerInterface {

	/**
	 * Handle a purge event
	 *
	 * @param PurgeEventInterface $event
	 * @return void
	 */
	public function handleEvent(PurgeEventInterface $event);

	/**
	 * Determine if the event handler can handle the given event.
	 *
	 * @param PurgeEventInterface $event
	 * @return boolean
	 */
	public function canHandleEvent(PurgeEventInterface $event);
}