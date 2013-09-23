<?php
namespace MOC\MocVarnish\Slots;

use MOC\MocMessageQueue\Message\MessageInterface;
use MOC\MocVarnish\Message\PurgePageUidMessage;
use MOC\MocVarnish\Message\PurgeUrlMessage;

class MessageQueue {

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
	 * @param MessageInterface $message
	 * @return void
	 */
	public function handleAsynchronousMessage(MessageInterface $message) {
		if ($message instanceof PurgePageUidMessage) {
			$domains = $this->domainLocatorService->getDomainsForPageUid($message->pageUid);

			if (count($domains) === 0) {
				return;
			}
			foreach ($this->urlLocatorService->getURLFromPageID($message->pageUid) as $url) {
				foreach ($domains as $domain) {
					$this->varnishPurgeService->clearCacheForUrl($url, $domain);
				}
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
	}

}
