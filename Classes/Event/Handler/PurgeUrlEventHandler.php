<?php
namespace MOC\MocVarnish\Event\Handler;

use MOC\MocVarnish\Event\PurgeEventInterface;

/**
 * Eventhandler for purging specific URLs. Each event will have a URL and possibly a domain to clear for.
 * If no domain is specified, all known domains are cleared (using the DomainLocatorService).
 *
 * @package MOC\MocVarnish\Event\Handler
 */
class PurgeUrlEventHandler implements PurgeEventHandlerInterface {

	/**
	 * @var \MOC\MocVarnish\VarnishPurgeServiceInterface
	 * @inject
	 */
	protected $varnishPurgeService;

	/**
	 * @var \MOC\MocVarnish\UrlLocatorServiceInterface
	 * @inject
	 */
	protected $urlLocatorService;

	/**
	 * @var \MOC\MocVarnish\DomainLocatorService
	 * @inject
	 */
	protected $domainLocatorService;

	/**
	 * Handle a purge event
	 *
	 * @param PurgeEventInterface $event
	 * @return void
	 */
	public function handleEvent(PurgeEventInterface $event) {
		if ($event->domain !== '') {
			$this->varnishPurgeService->clearCacheForUrl($event->url, $event->domain);
		} else {
			foreach ($this->domainLocatorService->getAllDomains() as $domain) {
				$this->varnishPurgeService->clearCacheForUrl($event->url, $domain);
			}
		}
	}

	/**
	 * Should determine if this event handler can handle this type of events
	 *
	 * @param PurgeEventInterface $event
	 * @return boolean
	 */
	public function canHandleEvent(PurgeEventInterface $event) {
		if (get_class($event) == 'MOC\MocVarnish\Event\PurgeUrlEvent') {
			return TRUE;
		}
		return FALSE;
	}

}