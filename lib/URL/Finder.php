<?php

interface URL_Finder_interface {
	public function getURLFromPageID($uid);
}


class URL_Finder_ServiceLocator {
	protected $URLFinders = array();
	public function injectURLFinder(URL_Finder_interface $finder) {
		$this->URLFinders[] = $finder;
	}

	/**
	 * Return URL from pageID.
	 * Will look through all registered services, and use the first one that finds the URL.
	 * It returns an array where each entry is an associative array with domain and pagepath.
	 * If the service that locates the URL is unable to determine from which domain this URL is found from, the
	 * domain key is not set.
	 * @param int $uid
	 * @return array An array of all found URL for this page id.
	 */
	public function getUrlFromPageID($uid) {
		foreach($this->URLFinders as $finder) {
			if($urls = $finder->getURLFromPageID($uid)) {
				return $urls;
			}
			t3lib_div::devLog('Unable to determine pageURL for page with uid ' . $uid, 'moc_varnish', 2);
			return array();
		}
	}

	/**
	 * Normalizes a path, makes sure that the path always contains a trailing slash.
	 * @param unknown_type $path
	 */
	protected function normalizePath($path) {
		if(substr($path, -1,1) != "/") {
			$path .= "/";
		}
		return $path;
	}
}

class URL_Finder_RealURL_PathCache implements URL_Finder_interface, t3lib_Singleton {

	/**
	 * @var array
	 */
	protected $conf;

	public function __construct() {
		$this->conf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['moc_varnish']);
	}

	public function getURLFromPageID($uid) {
		$urls = array();
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_realurl_pathcache','page_id='.intval($uid).' AND ( expire > ' .time() . ' OR expire=0)');
		if($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			foreach($this->getDomainsFromRootpageId($row['rootpage_id']) as $domain) {
				$url = array();
				$url['pagepath'] = $row['pagepath'].'/';
				if($domain != '_DEFAULT') {
					$url['domain'] = $domain;
				}
				$urls[] = $url;
			}

		}
		return $urls;
	}


	/**
	 * Given a certain page id, looks through RealURL conf to find all domains with this page as root id.
	 *
	 * The method takes the override_domains Extconf option into account!
	 *
	 * @param unknown_type $uid
	 * @return array
	 */
	protected function getDomainsFromRootpageId($uid) {
		global $TYPO3_CONF_VARS;
		$domains = array();
		if($this->conf['override_domains']) {
			return t3lib_div::trimExplode(',',$this->conf['override_domains']);
		}

		foreach($TYPO3_CONF_VARS['EXTCONF']['realurl'] as $domain=>$conf) {
			if($conf['pagePath']['rootpage_id'] == $uid) {
				$domains[] = $domain;
			}
		}
		return $domains;
	}
}

?>