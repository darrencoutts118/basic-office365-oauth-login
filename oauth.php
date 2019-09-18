<?php

require __DIR__ . '/secrets.php';

class Office365Auth
{

    private $tenant;
    private $client_id;
    private $client_secret;
    private $redirect_uri;

    public function __construct($tenant, $client_id, $client_secret, $redirect_uri)
    {
        $this->tenant        = $tenant;
        $this->client_id     = $client_id;
        $this->client_secret = $client_secret;
        $this->redirect_uri  = $redirect_uri;
    }

    public function redirect($scopes)
    {
        $query                    = [];
        $query['state']           = uniqid();
        $query['scope']           = $scopes;
        $query['response_type']   = 'code';
        $query['approval_prompt'] = 'auto';
        $query['redirect_uri']    = $this->redirect_uri;
        $query['client_id']       = $this->client_id;

        return 'https://login.microsoftonline.com/' . $this->tenant . '/oauth2/v2.0/authorize?' . http_build_query($query);
    }

    public function user($code)
    {
        $params                  = [];
        $params['code']          = $code;
        $params['client_id']     = $this->client_id;
        $params['client_secret'] = $this->client_secret;
        $params['redirect_uri']  = $this->redirect_uri;
        $params['grant_type']    = 'authorization_code';

        $url = 'https://login.microsoftonline.com/' . $this->tenant . '/oauth2/v2.0/token';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = json_decode(curl_exec($ch));
        curl_close($ch);

        return new Office365User($response->access_token);
    }
}

class Office365User
{
    private $access_token;

    public function __construct($token)
    {
        $this->access_token = $token;
    }

    public function graph($query)
    {
        $headers   = [];
        $headers[] = 'Authorization: Bearer ' . $this->access_token;
        $headers[] = 'Content-Type: application/json';

        $url = 'https://graph.microsoft.com/v1.0' . $query;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = json_decode(curl_exec($ch));
        curl_close($ch);

        return $response;
    }

    public function profile()
    {
        return $this->graph('/me');
    }

    public function groups()
    {
        return $this->graph('/me/memberOf');
    }

    public function manager()
    {
        return $this->graph('/me/manager');
    }

    public function directReports()
    {
        return $this->graph('/me/directReports');
    }
}

$o365 = new Office365Auth($office365_tenant, $office365_client, $office365_secret, $office365_redirect);

$scopes = 'openid profile User.Read Directory.Read.All';

if (!isset($_GET['code'])) {
    $url = $o365->redirect($scopes);
    echo 'Click here to login - <a href="' . $url . '">' . $url . '</a>';
} else {
    echo '<pre>';
    // get the user
    $user = $o365->user($_GET['code']);

    echo '<h1>Profile (/me)</h1>';
    print_r($user->profile());

    echo '<h1>Groups (/me/memberOf)</h1>';
    print_r($user->groups());

    echo '<h1>Groups (/me/manager)</h1>';
    print_r($user->manager());

    echo '<h1>Groups (/me/directReports)</h1>';
    print_r($user->directReports());

    echo '<br /><hr /><a href="?">Try again...</a>';
}
