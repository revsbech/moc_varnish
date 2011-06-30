<?php

class ux_tslib_fe extends tslib_fe {
	/**
	 * We override the default implementation, in order to tell TYPO3 to send Cache control headers even though we have USER INT's on the page!
	 * 
	 */
	function isStaticCacheble()	{
		$doCache = !$this->no_cache
				&& !$this->isEXTincScript()
				&& !$this->isUserOrGroupSet();
		return $doCache;
	}	
	
}
?>