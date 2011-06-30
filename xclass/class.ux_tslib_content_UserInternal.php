<?php

class ux_tslib_content_UserInternal extends tslib_content_UserInternal {
	function render($conf = array()) {
		$content = '';
		if($conf['no_esi']) {
			return parent::render($conf);
		}
		
		if(!t3lib_div::_GP('from_varnish')) {
			$substKey = str_replace(array('<!--','-->'),'',parent::render($conf));
			$url = '?id='.$GLOBALS['TSFE']->id.'&type=978&key='.$substKey.'&from_varnish=1';
			$content .= '<esi:include src="'.$url.'" />';			
			return $content;
		}
		return parent::render($conf);
	}
	
}