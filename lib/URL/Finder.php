<?php
interface URL_Finder_Interface {

	/**
	 * @param integer $uid
	 * @return array An array of all found URL for this page id.
	 */
	public function getURLFromPageID($uid);

}


class URL_Finder_ServiceLocator {

	/**
	 * @var array
	 */
	protected $URLFinders = array();

	/**
	 * @param URL_Finder_Interface $finder
	 * @return void
	 */
	public function injectURLFinder(URL_Finder_Interface $finder) {
		array_push($this->URLFinders, $finder);
	}

	/**
	 * Return URL from pageID.
	 * Will look through all registered services, and use the first one that finds the URL.
	 * It returns an array where each entry is an associative array with domain and pagepath.
	 * If the service that locates the URL is unable to determine from which domain this URL is found from, the
	 * domain key is not set.
	 *
	 * @param integer $uid
	 * @return array An array of all found URL for this page id.
	 */
	public function getUrlFromPageID($uid) {
		foreach ($this->URLFinders as $finder) {
			if ($urls = $finder->getURLFromPageID($uid)) {
				return $urls;
			}
		}
		t3lib_div::devLog('Unable to determine pageURL for page with uid ' . $uid, 'moc_varnish', 2);
		return array();
	}

}

class URL_Finder_RealURL_PathCache implements URL_Finder_Interface, t3lib_Singleton {

	/**
	 * @var array
	 */
	protected $conf;

	public function __construct() {
		$this->conf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['moc_varnish']);
	}

	/**
	 * Will search Realurls path cache for pages with this page id and return an entry for every
	 * domain registered in the Realurl configuration matching the rootpage id.
	 *
	 * @param integer $uid
	 * @return array An array of all found URL for this page id.
	 */
	public function getURLFromPageID($uid) {
		$urls = array();
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_realurl_pathcache', sprintf('page_id = "%u" AND ( expire > %u OR expire=0)', $uid, time()));
		if ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			foreach ($this->getDomainsFromRootpageId($row['rootpage_id']) as $domain) {
				$url = array();
				$url['pagepath'] = $row['pagepath'] . '/';
				if ($domain !== '_DEFAULT') {
					$url['domain'] = $domain;
				}
				array_push($urls, $url);
			}
		}

			// If no entries are found in the RealUrl path cache, check if the page is a root-page and clear cache for that page.
		if (empty($urls)) {
			$isRootPage = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('count(pages.uid)', 'pages, sys_template', sprintf('(is_siteroot = 1 OR (sys_template.root = 1 AND pages.uid = sys_template.pid AND NOT sys_template.deleted AND NOt sys_template.hidden)) AND NOT pages.hidden AND NOT pages.deleted AND pages.uid = %u', $uid));
			if ($isRootPage > 0) {
				foreach ($this->getDomainsFromRootpageId($uid) as $domain) {
					$url = array();
					$url['pagepath'] = '/';
					if ($domain !== '_DEFAULT') {
						$url['domain'] = $domain;
					}
					array_push($urls, $url);
				}
			}
		}
		return $urls;
	}

	/**
	 * Given a certain page id, looks through RealURL conf to find all domains with this page as root id.
	 *
	 * The method takes the override_domains Extconf option into account!
	 *
	 * @param integer $uid
	 * @return array
	 */
	public function getDomainsFromRootpageId($uid) {
		$domains = array();
		if ($this->conf['override_domains']) {
			return t3lib_div::trimExplode(',', $this->conf['override_domains']);
		}

		$pointers = array();
		foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['realurl'] as $domain => $conf) {
			if (is_string($conf)) {
				$pointers[$conf][] = $domain;
			} elseif (isset($conf['pagePath']['rootpage_id']) && intval($conf['pagePath']['rootpage_id']) === intval($uid)) {
				array_push($domains, $domain);
			}
		}

		foreach ($pointers as $target => $mappedDomains) {
			if (in_array($target, $domains)) {
				$domains = array_merge($domains, $mappedDomains);
			}
		}

		return $domains;
	}

}
?>