<?php
/**
 * Returns an authorized API client.
 * @return Google_Client the authorized client object
 */
function gmail_api_connect(&$accessToken='',&$authCode='')
{
    $client = new Google_Client();
    $client->setApplicationName('TodoList');
    $client->setScopes(Google_Service_Gmail::GMAIL_READONLY);
    $client->setAuthConfig('credentials.json');
    $client->setAccessType('offline');
    $client->setPrompt('select_account consent');

    // Load previously authorized token from a file, if it exists.
    // The file token.json stores the user's access and refresh tokens, and is
    // created automatically when the authorization flow completes for the first
    // time.
    if (!empty($accessToken)) {
        $client->setAccessToken($accessToken);
    }

    // If there is no previous token or it's expired.
    if ($client->isAccessTokenExpired()) {
        // Refresh the token if possible, else fetch a new one.
        if ($client->getRefreshToken()) {
            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
        } else if(!empty($authCode)){
            // Exchange authorization code for an access token.
             $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
             $client->setAccessToken($accessToken);
            // Check to see if there was an error.
            if (array_key_exists('error', $accessToken)) {
                throw new Exception(join(', ', $accessToken));
            }
        }else {
            // Request authorization from the user.
            $authUrl = $client->createAuthUrl();
            return $authUrl;
        }
        
        $accessToken = $client->getAccessToken();
    }
    return $client;
}

function gmail_api_read_mail($user,$service,$label_id){
    $out = [];
    $optParams = [];
    $optParams['labelIds'] = $label_id;
    $messages = $service->users_messages->listUsersMessages($user,$optParams);

    foreach($messages as $message){
        $msg       = $message;
        $messageId = $message->getId();

        $optParamsGet = [];
        $optParamsGet['format'] = 'full'; // Display message in payload
        $message = $service->users_messages->get($user,$messageId,$optParamsGet);

        $messagePayload = $message->getPayload();
        $headers = $message->getPayload()->getHeaders();
        $parts = $message->getPayload()->getParts();
        $timestamp = ($message->internalDate) / 1000;

        $out[] = ['body'=>$messagePayload, 'headers'=>$headers,'parts'=>$parts,'timestamp'=>$timestamp];
    }

    return $out;
}