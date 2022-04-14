<?php

/*
 * @copyright   Copyright (C) 2010-2022 Combodo SARL
 * @license     http://opensource.org/licenses/AGPL-3.0
 */

/**
 * Endpoint to process webhook calls from tawk.to to create a ticket from the chat conversation
 *
 * @link https://developer.tawk.to/webhooks/
 */

use Combodo\iTop\Extension\TawkIntegration\Service\IncomingWebhooksHandler;

$sAppRootFilePath = 'approot.inc.php';
// Depending on where the module is executed (env-xxx, datamodels with symlinks, extensions with symlinks, ...), approot file path can be different. So we try to find it.
for ($iDepth = 0; $iDepth <= 5; $iDepth++) {
	if (file_exists($sAppRootFilePath)) {
		require_once $sAppRootFilePath;
		break;
	}

	$sAppRootFilePath = '../'.$sAppRootFilePath;
}
// Necessary for autoloaders to be enabled
require_once APPROOT.'/application/startup.inc.php';

// Processing
try {
	$oWebhooksHandler = new IncomingWebhooksHandler();
	$sResponse = $oWebhooksHandler->HandleOperation();

	echo $sResponse;
} catch (Exception $oException) {
	echo $oException->getMessage();
}
