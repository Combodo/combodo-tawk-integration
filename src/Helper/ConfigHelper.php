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

namespace Combodo\iTop\Extension\TawkIntegration\Helper;

use MetaModel;
use UserRights;

/**
 * Class ConfigHelper
 *
 * @package Combodo\iTop\Extension\TawkIntegration\Helper
 * @author Guillaume Lajarige <guillaume.lajarige@combodo.com>
 */
class ConfigHelper
{
	const MODULE_CODE = 'combodo-tawk-integration';

	/**
	 * Return the module code so it can be used widely (module setting, URLs, ...)
	 *
	 * @return string
	 */
	public static function GetModuleCode()
	{
		return static::MODULE_CODE;
	}

	/**
	 * @param string $sProperty
	 *
	 * @return mixed
	 */
	public static function GetModuleSetting($sProperty)
	{
		return MetaModel::GetModuleSetting(static::GetModuleCode(), $sProperty);
	}

	/**
	 * Return if the module should be allowed based on:
	 * - The defined GUI
	 * - The current user profiles
	 *
	 * @param string $sGUI
	 *
	 * @return bool
	 */
	public static function IsAllowed($sGUI)
	{
		// Check if enabled in $sGUI
		$aEnabledGUIs = MetaModel::GetModuleSetting(static::GetModuleCode(), 'enabled_portals');
		if (is_array($aEnabledGUIs) && !in_array($sGUI, $aEnabledGUIs))
		{
			return false;
		}

		// Check if user has profile to access chat
		$aUserProfiles = UserRights::ListProfiles();
		$aAllowedProfiles = MetaModel::GetModuleSetting(static::GetModuleCode(), 'allowed_profiles');
		// No allowed profile defined = Allowed for everyone
		if (!empty($aAllowedProfiles))
		{
			$bAllowed = false;
			foreach ($aAllowedProfiles as $sAllowedProfile)
			{
				if (in_array($sAllowedProfile, $aUserProfiles))
				{
					$bAllowed = true;
					break;
				}
			}

			if (!$bAllowed)
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Return the JS snippet for the widget
	 *
	 * @return string
	 */
	public static function GetWidgetJSSnippet()
	{
		// Retrieve widget parameters
		$sPropertyId = static::GetModuleSetting('property_id');
		$sWidgetId = static::GetModuleSetting('widget_id');
		$sAPIKey = static::GetModuleSetting('api_key');

		// Prepare default user data
		$aUserData = array(
			'name' => 'Unidentified visitor',
			'email' => '',
		);

		// Retrieve current user information
		$oUser = UserRights::GetContactObject();
		if($oUser !== null)
		{
			$aUserData['name'] = $oUser->GetName();
			$aUserData['email'] = $oUser->Get('email');

			if(false === empty($sAPIKey)){
				$aUserData['hash'] = hash_hmac("sha256", $aUserData['email'], $sAPIKey);
			}
		}
		$sUserDataAsJson = json_encode($aUserData);

		// Nothing
		$sJS =
			<<<JS
/* Start of Tawk.to Script */
// Note: Unlike the official snippet, we use `window.Tawk_API` instead of `var Tawk_API` to ensure that the variable is global,
// otherwise we encounter variable scope issues and the visitor data are not passed to the tawk.to server.
window.Tawk_API = window.Tawk_API||{};
window.Tawk_API.visitor = {$sUserDataAsJson};
var Tawk_LoadStart = new Date();
(function(){
var s1 = document.createElement("script"), s0 = document.getElementsByTagName("script")[0];
s1.async = true;
s1.src = 'https://embed.tawk.to/{$sPropertyId}/{$sWidgetId}';
s1.charset = 'UTF-8';
s1.setAttribute('crossorigin','*');
s0.parentNode.insertBefore(s1,s0);
})();
/* End of Tawk.to Script */
JS
		;

		return $sJS;
	}
}