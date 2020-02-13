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

namespace Combodo\iTop\Extension\TawkIntegration\Extension;

use AbstractPortalUIExtension;
use Combodo\iTop\Extension\TawkIntegration\Helper\ConfigHelper;
use Symfony\Component\DependencyInjection\Container;

/**
 * Class PortalUIExtension
 *
 * @package Combodo\iTop\Extension\TawkIntegration\Extension
 * @author Guillaume Lajarige <guillaume.lajarige@combodo.com>
 */
class PortalUIExtension extends AbstractPortalUIExtension
{
	/**
	 * @inheritDoc
	 */
	public function GetJSInline(Container $oContainer)
	{
		$sJS = '';

		// Check if chat should be loaded
		if (!ConfigHelper::IsAllowed($_ENV['PORTAL_ID']))
		{
			return $sJS;
		}

		// Add JS widget
		$sJS .= ConfigHelper::GetWidgetJSSnippet();

		return $sJS;
	}
}
