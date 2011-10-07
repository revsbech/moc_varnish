<?php

class ux_tslib_content_UserInternal extends tslib_content_UserInternal {

	/**
	 * Render the USER_INT cObject
	 *
	 * @param	array		Array of TypoScript properties
	 * @return	string		Output
	 */
	public function render($conf = array()) {
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