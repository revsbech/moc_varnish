<?php




class ux_tslib_cObj extends tslib_cObj {
	function USER($conf, $ext = '') {
		//return parent::USER($conf,$ext);
		
		$content = '';
		if($conf['varnish.']['no_esi']) {
			return parent::USER($conf,$ext);
		}
		
		if($ext == "INT" && !t3lib_div::_GP('from_varnish')) {
			$this->userObjectType = false;
				$this->userObjectType = self::OBJECTTYPE_USER_INT;
				$substKey = $ext . '_SCRIPT.' . $GLOBALS['TSFE']->uniqueHash();
				//$content.='<!--' . $substKey . '-->';
				$GLOBALS['TSFE']->config[$ext . 'incScript'][$substKey] = array(
					'file' => $conf['includeLibs'],
					'conf' => $conf,
					'cObj' => serialize($this),
					'type' => 'FUNC'
				);
			$url = '?id='.$GLOBALS['TSFE']->id.'&type=978&key='.$substKey.'&from_varnish=1';
			$content .= '<esi:include src="'.$url.'" />';			
			return $content;
		}
		return parent::USER($conf,$ext);
	}
	
}