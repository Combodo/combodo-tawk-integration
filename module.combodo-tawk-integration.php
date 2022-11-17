<?php
/**
 * Copyright (C) 2013-2020 Combodo SARL
 *
 * This file is part of iTop.
 *
 * iTop is free software; you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * iTop is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 */

/** @noinspection PhpUnhandledExceptionInspection */
SetupWebPage::AddModule(
	__FILE__, // Path to the current file, all other file names are relative to the directory containing this file
	'combodo-tawk-integration/1.1.1',
	array(
		// Identification
		//
		'label' => 'Chat integration with tawk.to',
		'category' => 'integration',

		// Setup
		//
		'dependencies' => array(
			// Dependency on request management must remain optional as we might want the chat widget only and not the ticket creation feature.
			// That's why we put a module that is always present (itop-config-mgmt for iTop 2.7, itop-structure for iTop 3.0+) in the expression, to keep the itop-request-mgmt|-itil] optional
			'itop-config-mgmt/2.7.0||itop-structure/3.0.0||itop-request-mgmt/2.7.0||itop-request-mgmt-itil/2.7.0',
		),
		'mandatory' => false,
		'visible' => true,

		// Components
		//
		'datamodel' => array(
			// Module's autoloader
			'vendor/autoload.php',
			// Explicitly load APIs classes
			'src/Hook/ConsoleUIExtension.php',
			'src/Hook/PortalUIExtension.php',
		),
		'webservice' => array(),
		'dictionary' => array(
		),
		'data.struct' => array(),
		'data.sample' => array(),

		// Documentation
		//
		'doc.manual_setup' => '',
		'doc.more_information' => '',

		// Default settings
		//
		'settings' => array(),
	)
);
