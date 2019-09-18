# basic-office365-oauth-login

A very basic Office 365 OAuth 2.0 Login Script

## How to run

This assumes you already have an OAuth enabled app in your Azure Active Directory. 

1. Create a Office365Auth instance with the tennant URL (eg `darrenstest.onmicrosoft.com`), your client id and secret, and the URL that the callback should be made to.

```php
new Office365Auth($office365_tenant, $office365_client, $office365_secret, $office365_redirect)
```

2. Generate a Login URL, passing in the OAuth scopes that you wish to use

```php
$url = $o365->redirect($scopes);
```

Normally at this stage you would redirect the user to this url, for example

```
https://login.microsoftonline.com/darrenstest.onmicrosoft.com/oauth2/v2.0/authorize?state=5d827fdf51035&scope=openid+profile+User.Read+Directory.Read.All&response_type=code&approval_prompt=auto&redirect_uri=http%3A%2F%2Flocalhost%2Foauth.php&client_id=7269a50b-778f-4ac8-8f45-ff3e17c80ad3
```

The user will then be taken to the standard O365 login page

![image](https://user-images.githubusercontent.com/9951843/65177996-c0987800-da4f-11e9-9cbf-e90d7d0bee32.png)

They login with their normal username and password, and complete any two factor prompts

![image](https://user-images.githubusercontent.com/9951843/65178109-f9d0e800-da4f-11e9-84f7-ef59270e3bfe.png)

The user is then redirected to the callback url, with a `code` query string

This is then converted to an Office365User instance, which is done by exchanging the code for an OAuth access token. 

```php
$user = $o365->user($_GET['code']);
```

You can now obtain details about the user using the following: 

```php
$user->profile()
$user->groups()
$user->manager()
$user->directReports()
```

You can also query anything from the microsoft graph using

```php
$user->graph($query)
```

An example of the data you can get is as follows: 

### Profile (/me)
```php
$user->profile();
```
```php
stdClass Object
(
    [@odata.context] => https://graph.microsoft.com/v1.0/$metadata#users/$entity
    [businessPhones] => Array
        (
        )

    [displayName] => Darren Coutts
    [givenName] => Darren
    [jobTitle] => Test Human
    [mail] => darren@darrenstest.onmicrosoft.com
    [mobilePhone] => 
    [officeLocation] => Office 1
    [preferredLanguage] => en-US
    [surname] => Coutts
    [userPrincipalName] => darren@darrenstest.onmicrosoft.com
    [id] => 9c44d5e6-36b4-4a03-b1ea-0106d286fa53
)
```
### Groups (/me/memberOf)
```php
$user->groups();
```
```php
stdClass Object
(
    [@odata.context] => https://graph.microsoft.com/v1.0/$metadata#directoryObjects
    [value] => Array
        (
            [0] => stdClass Object
                (
                    [@odata.type] => #microsoft.graph.directoryRole
                    [id] => 50fdea7c-043c-4536-ac68-f7eb84d306ad
                    [deletedDateTime] => 
                    [description] => Can manage all aspects of Azure AD and Microsoft services that use Azure AD identities.
                    [displayName] => Company Administrator
                    [roleTemplateId] => 62e90394-69f5-4237-9190-012177145e10
                )

            [1] => stdClass Object
                (
                    [@odata.type] => #microsoft.graph.group
                    [id] => c971ba87-45fd-428a-90fc-5bde5c181211
                    [deletedDateTime] => 
                    [classification] => 
                    [createdDateTime] => 2019-09-18T17:59:57Z
                    [creationOptions] => Array
                        (
                        )

                    [description] => 
                    [displayName] => staff_group
                    [groupTypes] => Array
                        (
                        )

                    [isAssignableToRole] => 
                    [mail] => 
                    [mailEnabled] => 
                    [mailNickname] => 517b38e0-2
                    [onPremisesLastSyncDateTime] => 
                    [onPremisesSecurityIdentifier] => 
                    [onPremisesSyncEnabled] => 
                    [preferredDataLocation] => 
                    [proxyAddresses] => Array
                        (
                        )

                    [renewedDateTime] => 2019-09-18T17:59:57Z
                    [resourceBehaviorOptions] => Array
                        (
                        )

                    [resourceProvisioningOptions] => Array
                        (
                        )

                    [securityEnabled] => 1
                    [visibility] => 
                    [onPremisesProvisioningErrors] => Array
                        (
                        )

                )

        )

)
```
### Manager (/me/manager)
```php
$user->manager();
```
```php
stdClass Object
(
    [@odata.context] => https://graph.microsoft.com/v1.0/$metadata#directoryObjects/$entity
    [@odata.type] => #microsoft.graph.user
    [id] => 49ef913a-71ed-4416-99bb-365d599643ee
    [businessPhones] => Array
        (
        )

    [displayName] => Boss Manager
    [givenName] => Manager
    [jobTitle] => 
    [mail] => 
    [mobilePhone] => 
    [officeLocation] => 
    [preferredLanguage] => 
    [surname] => Manager
    [userPrincipalName] => manager@darrenstest.onmicrosoft.com
)
```
### Direct Reports (/me/directReports)
```php
$user->directReports();
```
```php
stdClass Object
(
    [@odata.context] => https://graph.microsoft.com/v1.0/$metadata#directoryObjects
    [value] => Array
        (
            [0] => stdClass Object
                (
                    [@odata.type] => #microsoft.graph.user
                    [id] => f0fe4a26-4781-482c-941f-64549cd544f9
                    [businessPhones] => Array
                        (
                        )

                    [displayName] => Another User
                    [givenName] => Another
                    [jobTitle] => 
                    [mail] => 
                    [mobilePhone] => 
                    [officeLocation] => 
                    [preferredLanguage] => 
                    [surname] => User
                    [userPrincipalName] => another@darrenstest.onmicrosoft.com
                )

        )

)
```
