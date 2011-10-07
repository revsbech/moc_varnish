<?php


function user_renderint($content,$conf) {
	$key = t3lib_div::_GET('key');
	$identifier = t3lib_div::_GET('identifier');

	if (empty($key) || empty($identifier)) {
		header('X-TYPO3-DISABLE-VARNISHCACHE: true');
		echo 'Missing GET parameters, can\'t proceed.';
		exit();
	}

	if (TYPO3_UseCachingFramework) {
		$pageCache = $GLOBALS['typo3CacheManager']->getCache('cache_pages');
		$row = $pageCache->get($identifier);
		$data = unserialize($row['cache_data']);
	} else {
		$rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*', 'cache_pages', 'hash=' . $GLOBALS['TYPO3_DB']->fullQuoteStr($identifier, 'cache_pages'));
		if (count($rows) === 1) {
			$row = $rows[0];
			$data = unserialize($row['cache_data']);
		}
	}

		//Copied from tslib_fe
	/**
	 * @todo bring to sync with 4.5 code
	 */
	if($data['INTincScript'][$key]) {
		$INTiS_cObj = unserialize($data['INTincScript'][$key]['cObj']);
		/* @var $INTiS_cObj tslib_cObj */
		$INTiS_cObj->INT_include=1;
		switch($data['INTincScript'][$key]['type'])	{
			case 'SCRIPT':
				$incContent = $INTiS_cObj->PHP_SCRIPT($data['INTincScript'][$key]['conf']);
			break;
			case 'COA':
				$incContent = $INTiS_cObj->COBJ_ARRAY($data['INTincScript'][$key]['conf']);
			break;
			case 'FUNC':
				$incContent = $INTiS_cObj->USER($data['INTincScript'][$key]['conf']);
			break;
			case 'POSTUSERFUNC':
				$incContent = $INTiS_cObj->callUserFunction($data['INTincScript'][$key]['postUserFunc'], $data['INTincScript'][$key]['conf'], $data['INTincScript'][$key]['content']);
			break;
		}

		header("X-ESI-RESPONSE: 1");
		$EXTconfArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['moc_varnish']);
		$conf = $data['INTincScript'][$key]['conf'];

		$max_age = $INTiS_cObj->stdWrap($conf['max_age'],$conf['max_age.']);
		if($max_age ) {
			header("Cache-control: max-age=".intval($max_age));
		} elseif(intval($EXTconfArr['userINT_forceTTL'])>0) {
			header("Cache-control: max-age=".intval($EXTconfArr['userINT_forceTTL']));
		}
		return $incContent;
	} else {
		header("X-TYPO3-DISABLE-VARNISHCACHE: true");
		print date("d/m-Y H:m:i").": Cache for page found, but there is no configuration for USER_INT with hash ".$key." in cache record...".t3lib_div::view_array($data);
		exit();
	}

	//@TODO: Somehow tell Varnish, that this content is not available, or somehow render it...
	header("X-TYPO3-DISABLE-VARNISHCACHE: true");
	print date("d/m-Y H:m:i").": Unable to find cache for page";
	exit();
}