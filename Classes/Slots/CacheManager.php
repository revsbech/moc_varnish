<?php
namespace MOC\MocVarnish\Slots;

/**
 * Slot for listening to clear cache signals.
 *
 * When the CacheManager emits signal about clearing cache, this slot will pick them up and do the actual clearning.
 *
 * @package MOC\MocVarnish
 */
class CacheManager {

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
	 * @param integer $pageUid
	 * @return void
	 */
	public function clearCacheForPageUid($pageUid) {
		$domains = $this->domainLocatorService->getDomainsForPageUid($pageUid);
		if (count($domains) === 0) {
			return;
		}
		foreach ($this->urlLocatorService->getURLFromPageID($pageUid) as $url) {
			foreach ($domains as $domain) {
				$this->varnishPurgeService->clearCacheForUrl($url, $domain);
			}
		}
	}

	/**
	 * @param string $url
	 * @param string $domain
	 * @return void
	 */
	public function clearCacheForUrl($url, $domain = '') {
		if ($domain !== '') {
			$this->varnishPurgeService->clearCacheForUrl($url, $domain);
		} else {
			foreach ($this->domainLocatorService->getAllDomains() as $domain) {
				$this->varnishPurgeService->clearCacheForUrl($url, $domain);
			}
		}
	}

}