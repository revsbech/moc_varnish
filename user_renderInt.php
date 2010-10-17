<?php


function user_renderint($content,$conf) {
	global $TSFE;
	$key = t3lib_div::_GP('key');
	$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','cache_pages','page_id='.intval($GLOBALS['TSFE']->id));
	if($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
		
		$data = unserialize($row['cache_data']);		
			//Copied from tslib_fe
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
					$incContent = $INTiS_cObj->callUserFunction($data['INTincScript'][$key]['postUserFunc'], $INTiS_config[$INTiS_key]['conf'], $INTiS_config[$INTiS_key]['content']);
				break;
			}
			header("X-Typo3-NoCache: true");
			
			
			$conf = $data['INTincScript'][$key]['conf'];
			if($conf['max_age']) {
				header("Cache-control: max-age=".intval($conf['max_age']));
			}
			
			//header("Cache-control: max-age=".intval(5));
			$incContent .= t3lib_div::view_array($data['INTincScript'][$key]['conf']); 
			return $incContent;
		}		
	}
	//@TODO: Somehow tell VArnish, that this content is not available, or somehow render it...
	
	return "Unrendered, not in Cache..";
	//t3lib_div::debug($incContent,"Content");
	//t3lib_div::debug($GLOBALS['TSFE']->config['INTincScript'],"Gen");
	
	//exit();
	//return $GLOBALS['TSFE']->INTincScript_process();
}