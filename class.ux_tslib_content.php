<?php


class ux_tslib_cObj extends tslib_cObj {
	function USER($conf, $ext = '') {
		$content = '';
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
			$content = "xy: 9 Inc key: ".$substKey."<br />";
			$basehost = "";
			//$basehost = "http://edntest.local:8080/";
			$url = $basehost.'?id='.$GLOBALS['TSFE']->id.'&type=978&key='.$substKey.'&from_varnish=1';
			//$url = 'http://edntest.local:8080/?id=10&type=978&key=INT_SCRIPT.86595e68eef86a8b31e3b81df73ddc60&from_varnish=1';
			$content .= '<esi:include src="'.$url.'" />';
			
			return $content;
		}
		return parent::USER($conf,$ext);
	}
	
}