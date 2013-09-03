<?php
namespace MOC\MocVarnish;

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class for finding domains to clear cache for.
 *
 * Is just a small wrapper for searching for domain records for a given page (or all pages). It will take into
 * account the optional ExtManager setting override_domain
 *
 * @package MOC\MocVarnish
 */
class DomainLocatorService implements SingletonInterface {

	/**
	 * @var array
	 */
	protected $extConf = array();

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['moc_varnish']);
	}

	/**
	 * Find all domains that a page is available for (not including redirect domains)
	 *
	 * For a given pageuid, it will find all domains for the site that page is located on
	 * Will traverse the rootline and look for domain records in the siteroot.
	 * If no siteroot is found, it will return all domains.
	 *
	 * Respects ExtManager setting override_domains
	 *
	 * @param integer $pageUid
	 * @return array
	 */
	public function getDomainsForPageUid($pageUid) {
		if ($this->extConf['override_domains']) {
			return GeneralUtility::trimExplode(',', $this->extConf['override_domains']);
		}
		$rootpageUid = 0;
		$domains = array();
		$rootLineStruct = \TYPO3\CMS\Backend\Utility\BackendUtility::BEgetRootLine($pageUid);
		foreach ($rootLineStruct as $page) {
			if ($page['is_siteroot']) {
				$rootpageUid = $page['uid'];
				break;
			}
		}
		if ($rootpageUid === 0) {
			return $this->getAllDomains();
		}
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('domainName', 'sys_domain', 'redirectTo="" AND pid=' . $rootpageUid);
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			array_push($domains, $row['domainName']);
		}
		return $domains;
	}

	/**
	 * Find all domains. Will simply find all domains records that are not redirect domain records.
	 *
	 * Respects ExtManager setting override_domains
	 *
	 * @return array
	 */
	public function getAllDomains() {
		if ($this->extConf['override_domains']) {
			return GeneralUtility::trimExplode(',', $this->extConf['override_domains']);
		}
		$domains = array();
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('domainName', 'sys_domain', 'redirectTo=""');
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			array_push($domains, $row['domainName']);
		}
		return $domains;
	}

}

