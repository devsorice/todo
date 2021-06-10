<?php
require('config.php');

if (php_sapi_name() != 'cli') {
    throw new Exception('This application must be run on the command line.');
}

/**
 * Returns an authorized API client.
 * @return Google_Client the authorized client object
 */
function getClient()
{
    $client = new Google_Client();
    $client->setApplicationName('Gmail API PHP Quickstart');
    $client->setScopes(Google_Service_Gmail::GMAIL_READONLY);
    $client->setAuthConfig('credentials.json');
    $client->setAccessType('offline');
    $client->setPrompt('select_account consent');

    // Load previously authorized token from a file, if it exists.
    // The file token.json stores the user's access and refresh tokens, and is
    // created automatically when the authorization flow completes for the first
    // time.
    $tokenPath = 'token.json';
    if (file_exists($tokenPath)) {
        $accessToken = json_decode(file_get_contents($tokenPath), true);
        $client->setAccessToken($accessToken);
    }

    // If there is no previous token or it's expired.
    if ($client->isAccessTokenExpired()) {
        // Refresh the token if possible, else fetch a new one.
        if ($client->getRefreshToken()) {
            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
        } else {
            // Request authorization from the user.
            $authUrl = $client->createAuthUrl();
            printf("Open the following link in your browser:\n%s\n", $authUrl);
            print 'Enter verification code: ';
            $authCode = trim(fgets(STDIN));

            // Exchange authorization code for an access token.
            $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
            $client->setAccessToken($accessToken);

            // Check to see if there was an error.
            if (array_key_exists('error', $accessToken)) {
                throw new Exception(join(', ', $accessToken));
            }
        }
        // Save the token to a file.
        if (!file_exists(dirname($tokenPath))) {
            mkdir(dirname($tokenPath), 0700, true);
        }
        file_put_contents($tokenPath, json_encode($client->getAccessToken()));
    }
    return $client;
}


// Get the API client and construct the service object.
$client = getClient();
$service = new Google_Service_Gmail($client);

// Print the labels in the user's account.
$user = 'me';
$results = $service->users_labels->listUsersLabels($user);

if (count($results->getLabels()) == 0) {
  print "No labels found.\n";
} else {
  print "Labels:\n";
  foreach ($results->getLabels() as $label) {
    printf("- %s ----- %s\n", $label->getName(),$label->getId());
  }
}

$optParams = [];
// $optParams['maxResults'] = 5; // Return Only 5 Messages
//$optParams['labelIds'] = 'INBOX';
$optParams['labelIds'] = 'Label_1233896604111842094';
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

    $out = ['body'=>$messagePayload, 'headers'=>$headers,'parts'=>$parts,'timestamp'=>$timestamp];
    $db->raw_messages->insertOne($out);
    $out = json_encode($out);
    /*
        $messagePayload = $message->getPayload();
        $headers = $message->getPayload()->getHeaders();
        $parts = $message->getPayload()->getParts();

        $timestamp = ($message->internalDate) / 1000;

        $date = date('Y-m-d H-i-s', $timestamp);

        foreach ($parts as $part) {
        if($part->mimeType == 'application/pdf'){
            $attachmentId = $part['body']['attachmentId'];
        }
        }

        $data = $service->users_messages_attachments->get($user, $messageId, $attachmentId);
        $data = $data->data;
        $data = strtr($data, array('-' => '+', '_' => '/'));

        $filename = "Car2Go " . $date . ".pdf";

        if(!file_exists($filename)){
        $fh = fopen($filename, "w+");
        fwrite($fh, base64_decode($data));
        fclose($fh);
        }
        else{
        'File ' . $filename . 'already exists!';
        }

        echo  "\n";

    */   
    file_put_contents('messages/'.$messageId .'.txt', $out);
}