<?php
if(!empty($_GET)){
    debug();
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

readfile('todo.html');