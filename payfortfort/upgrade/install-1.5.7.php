<?php

if (!defined('_PS_VERSION_'))
	exit;

// object module ($this) available
function upgrade_module_1_5_7($object)
{
	$upgrade_version = '1.5.7';

	$object->upgrade_detail[$upgrade_version] = array();

	Configuration::updateValue('PAYFORT_FORT_INTEGRATION_TYPE', 'redirection');

	Configuration::updateValue('PAYFORT_FORT', $upgrade_version);
	return true;
}
