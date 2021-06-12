<?php
/**
 * Returns an authorized API client.
 * @return Google_Client the authorized client object
 */
function gmail_api_connect(&$accessToken='',&$authCode='')
{
    $client = new Google_Client();
    $client->setApplicationName('TodoList');
    $client->setScopes([Google_Service_Gmail::GMAIL_READONLY,Google_Service_Drive::DRIVE,'https://www.googleapis.com/auth/userinfo.email']);
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

function decodeBody($body) {
    $rawData = $body;
    $sanitizedData = strtr($rawData,'-_', '+/');
    $decodedMessage = base64_decode($sanitizedData);
    if(!$decodedMessage){
        $decodedMessage = FALSE;
    }
    return $decodedMessage;
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

        $payloadArray = json_decode(json_encode($messagePayload),true);
        $html =  $payloadArray['parts'][1]['body']['data'] ?? '';
        $html = decodeBody($html);

        
        
        //$html = urldecode($html);
       
        $id = $payloadArray['headers'][2]['value'] ;
        $description = $payloadArray['headers'][3]['value'] ;

        $description .= !empty($html) ? ' '.mb_substr(strip_tags($html),0,75) : '';
        $description = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $description);

        $extra = [ 'body'=>$messagePayload, 'headers'=>$headers,'parts'=>$parts];

        
        $out[] = ['id'=>base64_encode($id), 'text'=>$description, 'message'=>$html, 'created'=>$timestamp];
    }
    return $out;
}