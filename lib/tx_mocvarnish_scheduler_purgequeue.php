<?php
class tx_mocvarnish_scheduler_purgequeue extends tx_scheduler_Task {

	/**
	 * Limit of purge requests per run
	 *
	 * @var integer
	 */
	public $limit;

	/**
	 * @return boolean
	 */
	public function execute() {
		$table = 'tx_mocvarnish_purge_queue';
		$varnishCacheMgm = new Varnish_CacheManager_CURLHTTP();
		$purgeRequests = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('uid, url, domain', $table, '', '', 'uid ASC', $this->limit, 'uid');
		foreach ($purgeRequests as $purgeRequest) {
			$varnishCacheMgm->clearCacheForUrl($purgeRequest['url'], $purgeRequest['domain']);
		}
		$GLOBALS['TYPO3_DB']->exec_DELETEquery($table, sprintf('uid IN (%s)', implode(',', array_keys($purgeRequests))));
		return TRUE;
	}

}