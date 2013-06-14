<?php

class ux_tslib_cObj extends tslib_cObj {

	function stdWrap_postUserFuncInt($content = '', $conf = array()) {
		$content = parent::render($conf);

		if ($conf['no_esi'] == FALSE && t3lib_div::_GP('from_varnish') == FALSE) {
			$substKey = str_replace(array('<!--','-->'), '', $content);
			$url = t3lib_div::getIndpEnv('TYPO3_SITE_PATH') .
				'?id='.$GLOBALS['TSFE']->id .
				'&type=978&key='.$substKey .
				'&identifier='.$GLOBALS['TSFE']->newHash .
				'&from_varnish=1';
			$content = '<esi:include src="'.$url.'" />';
		}

		return $content;
	}
}