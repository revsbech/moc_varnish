<?php
$TCA['tx_mocvarnish_purge_queue'] = array(
	'ctrl' => $TCA['tx_mocvarnish_purge_queue']['ctrl'],
	'interface' => array('showRecordFieldList' => 'url, domain'),
	'columns' => array(
		'url' => array(
			'exclude' => 0,
			'label' => 'URL',
			'config' => array(
				'type' => 'input',
				'eval' => 'trim'
			),
		),
		'domain' => array(
			'exclude' => 0,
			'label' => 'Domain',
			'config' => array(
				'type' => 'input',
				'eval' => 'trim'
			),
		)
	),
	'types' => array(
		'0' => array('showitem' => 'url, domain')
	)
);
