<?php
function stampa($val){
    echo '<pre>';
    echo "\n\n\n\n-----------------------------------------";
    echo "\n-------------$val----------\n";
    echo "-----------------------------------------------\n";
    print_r(eval("return $val;"));
    echo "\n\n---------------------\n\n\n\n";
    echo '</pre>';
}

function println($roba){
    print_r($roba);
    echo "\n<br>";
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

function arraySetVar($value,&$array,...$indexes)
{  
    $tmp = '';
    $pointer = &$array;
    $prevObject   = null;
    $prevPath     = null;
    foreach ($indexes as $index) 
    {
        $prec = &$pointer;
        if(is_numeric($index)){
            $index = (int)$index;
        }
        if(isset($pointer[$index]))
        {
            $pointer = &$pointer[$index];
        }else{
            $pointer[$index] = [];
            $pointer = &$pointer[$index];
        }
    }
    $pointer = $value;
}


/*
function objVar(obj,path,value=null){
    if (!path) return obj;  //non ho nulla da fare
    if (!obj)  return null; //non trovato 
    //console.log(obj);
    //console.log('cerco '+path);
    var props = path.split(":");
    var currentObject = obj;
    var prevObject    = null;
    var prevPath      = null;
    for (var i = 0; i < props.length; ++i) {
        prevObject = currentObject;
        prevPath   = props[i];
        if(typeof currentObject[props[i]]!="undefined"){
            currentObject = currentObject[props[i]];
        }   
        else if(!isNaN(props[i]) && typeof currentObject[parseInt(props[i])]!="undefined"){
            currentObject = currentObject[parseInt(props[i])];
        }else if(value!==null){
            currentObject[props[i]] = {};
        }            
        else return false;
    }
    if(prevObject && prevPath && value!==null){
        prevObject[prevPath] = value;
        currentObject = prevObject[prevPath];
    }
    // Se siamo riusciti ad arrivare alla fine allora restituisco l'oggetto che ho trovato
    //console.log(currentObject);
    return currentObject;
}
*/