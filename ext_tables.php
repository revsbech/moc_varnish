<?php
if (!defined('TYPO3_MODE')) {
        die ('Access denied.');
}

// initialize static extension templates
t3lib_extMgm::addStaticFile($_EXTKEY, 'static/', 'MOC Varnish');
