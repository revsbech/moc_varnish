<?php
namespace MOC\MocVarnish;

use MOC\MocVarnish\Event\EventBroker;
use MOC\MocVarnish\Event\PurgePageUidEvent;
use MOC\MocVarnish\Event\PurgeUrlEvent;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;


/**
 * CacheManger for MOC Varnish. Use this class for request clearing of Varnish cache
 * for either a specific URL or a page uid.
 *
 * The cache manager will fire PurgeCache events to the registered EventBroker thereby
 * delegating the actual clearing to registered event handlers.
 *
 * The event handlers can be synchronous og a-synchronous depending on configuration (configurable in ExtManager)
 *
 * @package MOC\MocVarnish
 */
class CacheManager implements SingletonInterface {

	/**
	 * @var \MOC\MocVarnish\Event\EventBroker
	 * @inject
	 */
	protected $eventBroker;

	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManager
	 * @inject
	 */
	protected $objectManager;

	/**
	 * This method will schedule a cache clearing event via the event broker.
	 *
	 * Call this to trigger clearing cache for a specific URL. You can optionally specify for which domain this should
	 * work.
	 *
	 * @param string $url The URL (relative) to clear cache for
	 * @param string $domain Specific domain to clear for. If left empty (default) all known domains will be cleared
	 * @return boolean
	 */
	public function clearCacheForUrl($url, $domain = '') {
		GeneralUtility::devLog('Triggering clear cache event for url ' . $url . ' on domain ' . $domain, 'moc_varnish');
		$event = new PurgeUrlEvent($url, $domain);
		return $this->eventBroker->publish($event);
	}

	/**
	 * This method will schedule a cache clearing event via the event broker .
	 *
	 * Call this to trigger clearing cache for a specific page uid.
	 *
	 * @param integer $pageUid
	 * @return boolean
	 */
	public function clearCacheForPageUid($pageUid) {
		GeneralUtility::devLog('Triggering clear cache event for pageid ' . $pageUid, 'moc_varnish');
		$event = new PurgePageUidEvent($pageUid);
		return $this->eventBroker->publish($event);
	}

}