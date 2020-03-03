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
		$sSiteId = static::GetModuleSetting('site_id');

		// Retrieve current user information
		$sUserNameAsJson = 'Unidentified visitor';
		$sUserEmailAsJson = '';
		$oUser = UserRights::GetContactObject();
		if($oUser !== null)
		{
			$sUserNameAsJson = json_encode($oUser->GetName());
			$sUserEmailAsJson = json_encode($oUser->Get('email'));
		}

		// Nothing
		$sJS =
			<<<JS
/* Start of Tawk.to Script */
var Tawk_API=Tawk_API||{};
Tawk_API.visitor = {
	name : {$sUserNameAsJson},
	email : {$sUserEmailAsJson}
};
var Tawk_LoadStart=new Date();
(function(){
var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
s1.async=true;
s1.src='https://embed.tawk.to/{$sSiteId}/default';
s1.charset='UTF-8';
s1.setAttribute('crossorigin','*');
s0.parentNode.insertBefore(s1,s0);
})();
/* End of Tawk.to Script */
JS
		;

		return $sJS;
	}
}