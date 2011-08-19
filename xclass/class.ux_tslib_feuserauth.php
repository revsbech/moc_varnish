<?php
/**
 *
 * The class ocerrides the default dontSetCookie value of tslib_feuserAuth. This way a new cookie is never
 * set unless explicityly asked for (by setting dontSetCookie to FALSE.
 * This is done when creating new FE user sessions, and when writing stuff in the sessionData for
 * GLOBALS['TSFE']->fe_user.
 * 
 */
class ux_tslib_feuserAuth extends tslib_feUserAuth {
	public $dontSetCookie = TRUE;

	function storeSessionData() {
		if ($this->sesData_change) {
			$this->dontSetCookie = FALSE;
		}
		parent::storeSessionData();
	}

	function createUserSession($tempuser) {
		$this->dontSetCookie = FALSE;
		parent::createUserSession($tempuser);
	}
}
