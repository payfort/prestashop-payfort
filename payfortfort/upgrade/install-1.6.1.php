<?php

if (!defined('_PS_VERSION_'))
	exit;

// object module ($this) available
function upgrade_module_1_6_1($object)
{
	$upgrade_version = '1.6.1';

	$object->upgrade_detail[$upgrade_version] = array();

//	Configuration::updateValue('PAYFORT_FORT_ORDER_PLACEMENT', 'all');
	Configuration::updateValue('PAYFORT_FORT_DEBUG_MODE', 0);
	Configuration::updateValue('PAYFORT_FORT_GATEWAY_CURRENCY', 'base');

	Configuration::updateValue('PAYFORT_FORT', $upgrade_version);
	return true;
}
