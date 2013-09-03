<?php
namespace MOC\MocVarnish\UrlLocatorService;

use MOC\MocVarnish\UrlLocatorServiceInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * RealURL pathcache implementation of MOC Varnish UrlLocatorService
 *
 * Will use RealURL path cache to look up URL's for specific page ID's
 *
 * @package MOC\MocVarnish
 */
class RealURLLocatorService implements UrlLocatorServiceInterface {

	/**
	 * Will search RealURLs path cache for pages with this page id and return an entry all URL's found.
	 *
	 * @param integer $uid
	 * @return array An array of all found URL for this page id.
	 */
	public function getURLFromPageID($uid) {
		$urls = array();
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'tx_realurl_pathcache', sprintf('page_id = "%u" AND ( expire > %u OR expire=0)', $uid, time()));
		if ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$url = $row['pagepath'] . '/';
			array_push($urls, $url);
		}

			// If no entries are found in the RealUrl path cache, check if the page is a root-page and clear cache for that page.
		if (empty($urls)) {
			$isRootPage = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('count(pages.uid)', 'pages, sys_template', sprintf('(is_siteroot = 1 OR (sys_template.root = 1 AND pages.uid = sys_template.pid AND NOT sys_template.deleted AND NOT sys_template.hidden)) AND NOT pages.hidden AND NOT pages.deleted AND pages.uid = %u', $uid));
			if ($isRootPage > 0) {
				$url = '/';
				array_push($urls, $url);
			}
		}
		return $urls;
	}

}