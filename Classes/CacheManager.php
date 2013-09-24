<?php
namespace MOC\MocVarnish;

use MOC\MocVarnish\Message\PurgePageUidMessage;
use MOC\MocVarnish\Message\PurgeUrlMessage;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * CacheManger for MOC Varnish. Use this class for request clearing of Varnish cache
 * for either a specific URL or a page uid.
 *
 * The cache manager will either (depending on configuration) emit a signal for synchrouns handling, or will publish
 * a message using the message queue delegating the actual work making the backend much faster.
 *
 * @package MOC\MocVarnish
 */
class CacheManager implements SingletonInterface {

	/**
	 * @var \MOC\MocMessageQueue\Queue\QueueInterface
	 */
	protected $queue;

	/**
	 * @var array
	 */
	protected $extensionConfiguration = array();

	/**
	 * @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher
	 * @inject
	 */
	protected $signalSlotDispatcher;

	/**
	 * @var bool
	 */
	protected $emitAsynchronously = FALSE;

	public function __construct() {
		$this->extensionConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['moc_varnish']);

		$objectManager = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
		if ($this->extensionConfiguration['enable_async_purge'] && ExtensionManagementUtility::isLoaded('moc_message_queue')) {
			$this->emitAsynchronously = TRUE;
				// It is on purpose that this is not injected as we only want it if the enable_async_puge is set
			$this->queue = $objectManager->get('MOC\MocMessageQueue\Queue\QueueInterface');
		}
	}

	/**
	 * This method will schedule a cache clearing signal. Either as a extbase Signal, or as a message queue message.
	 *
	 * Call this to trigger clearing cache for a specific URL. You can optionally specify for which domain this should
	 * work.
	 *
	 * @param string $url The URL (relative) to clear cache for
	 * @param string $domain Specific domain to clear for. If left empty (default) all known domains will be cleared
	 * @return boolean
	 */
	public function clearCacheForUrl($url, $domain = '') {
		if ($this->emitAsynchronously) {
			GeneralUtility::devLog('Publishing clear cache message for url ' . $url . ' on domain ' . $domain, 'moc_varnish');
			$this->queue->publish(new PurgeUrlMessage($url, $domain));
		} else {
			GeneralUtility::devLog('Emitting clear cache message for url ' . $url . ' on domain ' . $domain, 'moc_varnish');
			$this->signalSlotDispatcher->dispatch(__CLASS__, 'clearCacheForUrl', array(
				'url' => $url,
				'domain' => $domain
			));
		}
		return TRUE;
	}

	/**
	 * This method will schedule a cache clearing signal. Either as a extbase Signal, or as a message queue message.
	 *
	 * Call this to trigger clearing cache for a specific page uid.
	 *
	 * @param integer $pageUid
	 * @return boolean
	 */
	public function clearCacheForPageUid($pageUid) {
		if ($this->emitAsynchronously) {
			GeneralUtility::devLog('Publishing clear cache message for page id ' . $pageUid, 'moc_varnish');
			$this->queue->publish(new PurgePageUidMessage($pageUid));
		} else {
			GeneralUtility::devLog('Emitting clear cache signal for page id ' . $pageUid, 'moc_varnish');
			$this->signalSlotDispatcher->dispatch(__CLASS__, 'clearCacheForPageUid', array(
				'pageUid' => $pageUid
			));
		}
		return TRUE;
	}

}