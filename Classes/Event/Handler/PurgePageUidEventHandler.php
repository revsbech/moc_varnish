<?php
namespace MOC\MocVarnish\Event\Handler;

use MOC\MocVarnish\Event\PurgeEventInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Eventhandler for purging varnish cache for TYPO3 pageUid. Each event will store which page
 * should be cleared for, and the registered UrlLocatorService will be asked to provide all URL to
 * this page.
 * The Domain for that page is fetched via the DomainLocatorService. If no domain is found, no page
 * is cleared!
 *
 * @package MOC\MocVarnish
 */
class PurgePageUidEventHandler implements PurgeEventHandlerInterface {

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
	 * Will ask the DomainLocatorService for domains for this page. If no domains are found, nothing is cleared.
	 * Will use registered UrlLocatorService for fetching the URL to the page.
	 *
	 * @param PurgeEventInterface $event
	 * @return void
	 */
	public function handleEvent(PurgeEventInterface $event) {
		$domains = $this->domainLocatorService->getDomainsForPageUid($event->pageUid);
		if (count($domains) === 0) {
			return;
		}
		foreach ($this->urlLocatorService->getURLFromPageID($event->pageUid) as $url) {
			foreach ($domains as $domain) {
				$this->varnishPurgeService->clearCacheForUrl($url, $domain);
			}
		}
	}

	/**
	 * Determine if the event handler can handle this event.
	 *
	 * This eventhandler can only handle events of type PurgePageUidEvent
	 *
	 * @param PurgeEventInterface $event
	 * @return boolean
	 */
	public function canHandleEvent(PurgeEventInterface $event) {
		if (get_class($event) == 'MOC\MocVarnish\Event\PurgePageUidEvent') {
			return TRUE;
		}
		return FALSE;
	}

}