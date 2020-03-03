# Tawk.to chat integration

_Note: Prototype to show how to integrate a third-party chat(bot) in iTop through the existing APIs._

## Description
[Tawk.to](https://www.tawk.to/) is a free, hosted, third-party, live support chat that can be easily integrated within iTop to offer a new contact channel. It's a perfect way to enable live chat in iTop with a minimal effort/investment.

## Features
It brings a small bubble in the bottom-right corner of the screen. Users can click on it to start a conversation with support agents.

_Note: Chat can be enabled in any end-user portals or the backoffice through its configuration._

![](doc/portal-widget-closed.png)

![](doc/portal-widget-talking.png)

Combined with the _iFrame dashlet_, the agent dashboard can be embedded in the admin. console to answer chats directly from iTop.

![](doc/console-chats-dashboard.png)

## Compatibility
Compatible with iTop 2.7+

## Configuration
### Get tawk.to account
Go to [tawk.to](https://www.tawk.to/), create a free account and that's it!

### Set widget configuration
First, go to the tawk.to backoffice and retrieve the `Site ID`. Once you got it, fill the module settings as follow:
- `site_id` Put the site ID retrieve in the previous step.
- `enabled_portals` An array of the "portals" you want the chat to be enabled on. Can be `backoffice` for the admin. console or any end-user portal ID (eg. `itop-portal` for the standard portal), by default only the `itop-portal` is enabled.
- `allowed_profiles` An array of iTop profiles to define which users will be able to use the chat. If not defined, all users will be able t use it, by default only `Portal user` is allowed.

The extension comes with default settings in its XML datamodel (_module parameters_), you can either:
- Overload them with your XML delta (Good to propagate settings to all your iTop instances)
- Overload them in iTop's configuration file (Good to change setting only on a specific instance)

##### Default configuration in the datamodel
```
<?xml version="1.0" encoding="UTF-8"?>
<itop_design xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" version="1.6">
	<module_parameters>
		<parameters id="combodo-tawk-integration" _delta="define">
			<site_id>PUT_YOUR_SITE_ID_HERE</site_id>
			<enabled_portals type="array">
				<enabled_portal id="itop-portal">itop-portal</enabled_portal>
			</enabled_portals>
			<allowed_profiles type="array">
				<allowed_profile id="portal-user">Portal user</allowed_profile>
			</allowed_profiles>
		</parameters>
	</module_parameters>
</itop_design>
```

##### Overloading through the configuration file
Simply put the following in the configuration file and fill it with your own settings:
```
'combodo-tawk-integration' => array (
    'site_id' => 'somesiteidforyourcopany',
    'enabled_portals' => array (
      'itop-portal',
    ),
    'allowed_profiles' => array(
      'Portal user',
    ),
),
```
