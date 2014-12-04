<?php
namespace MOC\MocVarnish\Slots;

use MOC\MocMessageQueue\Message\MessageInterface;
use MOC\MocVarnish\Message\PurgePageUidMessage;
use MOC\MocVarnish\Message\PurgeUrlMessage;
use MOC\MocVarnish\Message\PurgeAllMessage;

/**
 * Extbase slot (as in SignalSlots) that listens for signals emitted by cache-clearing events.
 *
 * @package MOC\MocVarnish
 */
class MessageQueue {

	/**
	 * @var \MOC\MocVarnish\VarnishPurgeServiceInterface
	 * @inject
	 */
	protected $varnishPurgeService;

	/**
	 * @var \MOC\MocVarnish\DomainLocatorService
	 * @inject
	 */
	protected $domainLocatorService;

	/**
	 * @param MessageInterface $message
	 * @return void
	 */
	public function handleAsynchronousMessage(MessageInterface $message) {

		if ($message instanceof PurgePageUidMessage) {
			$domains = $this->domainLocatorService->getDomainsForPageUid($message->pageUid);
			if (count($domains) === 0) {
				return;
			}

			foreach ($domains as $domain) {
				print "In MessagEQueoe with domain " . $domain . ' and page id ' . $message->pageUid . PHP_EOL;
				$this->varnishPurgeService->clearCacheForTypo3PageId($message->pageUid, $domain);
			}
		}

		if ($message instanceof PurgeUrlMessage) {
			if ($message->domain !== '') {
				$this->varnishPurgeService->clearCacheForUrl($message->url, $message->domain);
			} else {
				foreach ($this->domainLocatorService->getAllDomains() as $domain) {
					$this->varnishPurgeService->clearCacheForUrl($message->url, $domain);
				}
			}
		}

		if ($message instanceof PurgeAllMessage) {
			if ($message->domain !== '') {
				$this->varnishPurgeService->clearAllCache($message->domain);
			} else {
				foreach ($this->domainLocatorService->getAllDomains() as $domain) {
					$this->varnishPurgeService->clearAllCache($domain);
				}
			}
		}

	}

}