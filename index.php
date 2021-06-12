<?php
if( preg_match('/^\/assets/m',$_SERVER['REQUEST_URI'])  && !preg_match('/(.*?\.php|.*?\.json|\.\.\/.*|\.\/.*|\.\..*)/m',$_SERVER['REQUEST_URI'])){
    readfile($_SERVER['REQUEST_URI']);
    exit;
}
/*
if(empty($_SESSION['_id']) && empty($_POST['username'])){
    readfile('template/login.html');
    exit;
}*/
require('inc/setup.php');

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

    if(!empty($_GET['refresh'])){
        $_SESSION['tasks'] = [];
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

    if(empty($_SESSION['user_info']['email'])){
        $oauth2 = new \Google_Service_Oauth2($client);
        $userInfo = (array)$oauth2->userinfo->get();
        $_SESSION['user_info'] = [];
        $_SESSION['user_info']['email']   = $userInfo['email'] ?? '';
        $_SESSION['user_info']['id']      = $userInfo['id'] ?? '';
        $_SESSION['user_info']['picture'] = $userInfo['picture'] ?? '';
    }

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
        readfile('template/select.html');
        exit;
    }

    if(empty($_SESSION['tasks'])){
        $_SESSION['tasks'] = gmail_api_read_mail($user,$service,$label_id);
    }
    loadTaskSort();
    loadFromCookies();

    $task_string = str_replace('drag-signature\r\n                ?We run on ','',str_replace('\ufffd','\n',json_encode($_SESSION['tasks'],JSON_INVALID_UTF8_SUBSTITUTE)));
    $_SESSION['tasks'] = json_decode($task_string,true);
    echo "<script>var user=".json_encode($_SESSION['user_info'])."</script>";
    echo "<script>var tasks=".$task_string."</script>";
    echo "<script>var todolist_template=".json_encode(file_get_contents('template/mini-todolist.html'))."</script>";
    readfile('template/todo.html');
}else if(is_string($client)){
    header('Location: '.$client);
    exit;
}


function loadFromDB($email){

}


function loadFromCookies(){
    $modified = false;
    //print_r($_COOKIE);
    foreach($_COOKIE as $kk =>$vv){
        if(preg_match('/^tasks_/m', $kk)){
            //println($kk);
            $kk = explode('tasks_',$kk);
            //println($kk);
            $kk = $kk[1];
            //println($kk);
            $kk = explode('_',$kk);
            //println($kk);
            $id = urldecode($kk[0]);
            //println($id);
            $kk = array_slice($kk, 1,count($kk)-1);
            //println($kk);
            $kk = implode('',$kk);
            $name = urldecode($kk);
            $vv = urldecode($vv);
            $vv = json_decode($vv,true);
            $value = $vv;
            $sort =  $_SESSION['tasks_sort'][$id];
            //print_r($_SESSION['tasks_sort']);
            /*print_r('modifico');
            print_r($_SESSION['tasks'][$sort]);
            print_r(explode(':',$name));
            print_r($value);*/
            //print_r([$_SESSION['tasks'][$sort],explode(':',$name),$value]);
            arraySetVar($value,$_SESSION['tasks'][$sort], ...explode(':',$name));
            if(!empty($_SESSION['tasks'][$sort]['timer']['time'])){
                $_SESSION['tasks'][$sort]['time'] = (int)$_SESSION['tasks'][$sort]['timer']['time'];
            }
            
            $modified = true;
        }
    }

    /*if($modified){
        print_r($_SESSION['tasks']);
        exit;
    }*/
   
}

function loadTaskSort(){
    $_SESSION['tasks_sort'] = [];
    foreach($_SESSION['tasks'] as $kk => $task){
        $_SESSION['tasks_sort'][$task['id']] = $kk;
    }
}


function saveToDB(){
    global $db;
    $user = $_SESSION;
    $id = $user['_id'] ?? new \MongoDB\BSON\ObjectId();
    unset($user['tasks']);
    unset($user['_id']);

    $tasks = $_SESSION['tasks'];
    foreach($tasks as  $kk=>$vv){
        $vv['user']      = $id; 
        $vv['user_info'] = $user['user_info'];
        unset($vv['id']);
        $db->tasks->updateOne(['id'=>$tasks[$kk]['id']],['$set'=>$vv],['upsert'=>true]);
    }

    $db->users->updateOne(['_id'=>$id],['$set'=>$user],['upsert'=>true]);
}