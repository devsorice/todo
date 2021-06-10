<?php
require('config.php');
$_SESSION['scope']  = $_SESSION['scope'] ?? [];

if(!empty($_GET)){
    if(isset($_GET['code'])){
        $_SESSION['auth_code'] = $_GET['code'];
    }
    if(!empty($_GET['scope'])){
        $_SESSION['scope'][$_GET['scope']] = true;
    }

    if(!empty($_GET['label_id'])){
        $_SESSION['label_id'] = $_GET['label_id'];
    }
}
$label_id     = $_SESSION['label_id'] ?? '';
$auth_code    = $_SESSION['auth_code'] ?? '';
$access_token = $_SESSION['access_token'] ?? '';
$client = gmail_api_connect($access_token,$auth_code);
$_SESSION['auth_code']    = $auth_code;
$_SESSION['access_token'] = $access_token;



if($client instanceof Google_Client){
    $user = 'me';
    $service = new Google_Service_Gmail($client);

    if(empty($label_id)){
        $results = $service->users_labels->listUsersLabels($user);
        $labels = [];
        
        foreach ($results->getLabels() as $l) {
            $label = [];
            $label['id']   = $l->getId();
            $label['name'] = $l->getName(); 
            $labels[] = $label;
          }
        echo "<script>var options=".json_encode($labels)."</script>";
        readfile('select.html');
        exit;
    }

    if(empty($_SESSION['tasks'])){
        $_SESSION['tasks'] = gmail_api_read_mail($user,$service,$label_id);
    }
    echo "<script>var tasks=".json_encode($_SESSION['tasks'])."</script>";
    readfile('todo.html');
}else if(is_string($client)){
    header('Location: '.$client);
    exit;
}


function stampa($val){
    echo '<pre>';
    echo "\n\n\n\n-----------------------------------------";
    echo "\n-------------$val----------\n";
    echo "-----------------------------------------------\n";
    print_r(eval("return $val;"));
    echo "\n\n---------------------\n\n\n\n";
    echo '</pre>';
}

function debug(){
    stampa('$_SERVER');
    stampa('$_COOKIE');
    stampa('$_SESSION');
    stampa('$_GET');
    stampa('$_POST');
    stampa('session_id()');
    stampa("file_get_contents('php://input')");
    exit;
}