String.prototype.interpolate = function(params) {
    const names = Object.keys(params);
    const vals = Object.values(params);
    return new Function(...names, `return \`${this}\`;`)(...vals);
}

var buildquery = function(){
  var esc = function(param) {
    return encodeURIComponent(param)
      .replace(/[!'()*]/g, escape)
      .replace(/%20/g, '+');
  };
  
  var isNumeric = function(n) {
    return !isNaN(parseFloat(n)) && isFinite(n);
  };  
  var cleanArray = function(actual) {
    var newArray = new Array();
    for (var i = 0; i < actual.length; i++) {
      if (actual[i]) {
        newArray.push(actual[i]);
      }
    }
    return newArray;
  };  
  var httpBuildQuery = function(queryData, numericPrefix, argSeparator, tempKey) {
    numericPrefix = numericPrefix || null;
    argSeparator = argSeparator || '&';
    tempKey = tempKey || null;
  
    if (!queryData) {
      return '';
    }
  
    var query = Object.keys(queryData).map(function(k) {
      var res;
      var key = k;
  
      if (tempKey) {
        key = tempKey + '[' + key + ']';
      }
  
      if (typeof queryData[k] === 'object' && queryData[k] !== null) {
        res = httpBuildQuery(queryData[k], null, argSeparator, key);
      } else {
        if (numericPrefix) {
          key = isNumeric(key) ? numericPrefix + Number(key) : key;
        }
  
        var val = queryData[k];
  
        val = val === true ? '1' : val;
        val = val === false ? '0' : val;
        val = val === 0 ? '0' : val;
        val = val || '';
  
        res = esc(key) + '=' + esc(val);
      }
  
      return res;
    });
  
    return cleanArray(query)
      .join(argSeparator)
      .replace(/[!'()*]/g, '');
  };

  return httpBuildQuery(...arguments);
}

function saveTodolist(el){
    var close = el.closest('.uk-modal-dialog').querySelector('.uk-close');
    var i   = parseInt(el.getAttribute('data-i'));
    var id  = el.getAttribute('data-id');
    var elements = formToObject(el);
    if(elements['elements'] && typeof (elements['elements'])=='object'){
        elements = elements['elements'];
    }else if(elements['elements'] && typeof (elements['elements'])=='string'){
        elements = [elements['elements']];
    }else{
        elements = [];
    }
    

    var elDom = document.querySelector('.splide [data-i="'+i+'"]');
    var subtask_div = elDom.querySelector('.subtask-data');
    var subtask_count = elDom.querySelector('.subtask-count');
    var html = '';


    list[i]['todo'] = [];
    for(var s=0; s<elements.length;s++){
        list[i]['todo'].push({'text':elements[s]});
        html += `<input type="hidden" class="task-subtask" name="tasks:${id}:todo:${s}:text" data-name="todo:${s}:text"     value="${elements[s]}">`;
    }
    subtask_div.innerHTML =    html;
    subtask_count.innerHTML = elements.length;


    updateBackend();
    //

    if(close){
        close.click();
    }
}

var executeScripts = function(parentSelector) {
    var elm = document.getElementById(parentSelector).parentElement;
    Array.from(elm.querySelectorAll("script")).forEach( oldScript => {
      const newScript = document.createElement("script");
      Array.from(oldScript.attributes)
        .forEach( attr => newScript.setAttribute(attr.name, attr.value) );
      newScript.appendChild(document.createTextNode(oldScript.innerHTML));
      oldScript.parentNode.replaceChild(newScript, oldScript);
    });
  }

function formToObject( elem ) {
    var current, entries, item, key, output, value;
    output = {};
    entries = new FormData( elem ).entries();
    // Iterate over values, and assign to item.
    while ( item = entries.next().value )
      {
        // assign to variables to make the code more readable.
        key = item[0];
        value = item[1];
        // Check if key already exist
        if (Object.prototype.hasOwnProperty.call( output, key)) {
          current = output[ key ];
          if ( !Array.isArray( current ) ) {
            // If it's not an array, convert it to an array.
            current = output[ key ] = [ current ];
          }
          current.push( value ); // Add the new value to the array.
        } else {
          output[ key ] = value;
        }
      }
      return output;
    }

function setCookie(name,value="",days=false) {
    var expires = "";
    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days*24*60*60*1000));
        expires = "; expires=" + date.toUTCString();
    }
    document.cookie = name + "=" + (value || "")  + expires + "; path=/";
}

function getCookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for(var i=0;i < ca.length;i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1,c.length);
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
    }
    return null;
}

function eraseCookie(name) {   
    document.cookie = name +'=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
}

function objVar(obj,path,value=null,_default=false){
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
    if(prevObject && prevPath && value!==null  && !_default){
        prevObject[prevPath] = value;
        currentObject = prevObject[prevPath];
    }

    if(_default && prevObject && prevPath && (!currentObject && currentObject!==0)){
        prevObject[prevPath] = value;
        currentObject = prevObject[prevPath];
    }
    // Se siamo riusciti ad arrivare alla fine allora restituisco l'oggetto che ho trovato
    //console.log(currentObject);
    return currentObject;
}
function getNested(def, obj, ...args) {
    var value = args.reduce((obj, level) => obj && obj[level], obj);
    if(typeof value=='undefined'){
        return def;
    }
    return value;
  }



function blankTodolist(id,i) {
    return todolist_template.interpolate({id,i}); 
}
  
  
  // Create a new list item when clicking on the "Add" button
function newTodoElement(elid=false,text=false) {
    if(typeof elid!='string'){
        console.log(elid);
        elid = elid.closest('.todolist');
        console.log(elid);
        elid = elid.id;
        console.log(elid);
    }
    var container = document.getElementById(elid);
    var real_li = document.createElement("li");
    var li = document.createElement("div");
    li.setAttribute('class','tagelement');
    real_li.appendChild(li);
    var myInput = container.querySelector(".myInput");
    var inputValue = myInput.value;
    if(text)
        inputValue = text;
    var t = document.createElement("input");
    t.setAttribute('type','text');
    t.setAttribute('name','elements');
    t.setAttribute('class','uk-input');
    t.value = inputValue;
    li.appendChild(t);
    if (inputValue === '') {
        alert("Devi scrivere qualcosa");
    } else {
        container.querySelector(".myUL").appendChild(real_li);
    }
    myInput.value = "";

    var span = document.createElement("SPAN");
    span.setAttribute('uk-icon','icon: close');
    span.className = "close uk-padding-small";
    span.onclick = function() {
        var div = this.parentElement.parentElement;
        div.remove();
    }
    //span.appendChild(txt);
    li.appendChild(span);
}


function saveCountDownStart(i){
    var el = list[i];
    var elDom = document.querySelector('.splide [data-i="'+i+'"]');
    var timer = elDom.querySelector('.timer');
    var id  = elDom.getAttribute('data-id');


    var time = el.time;
    var start =  new Date().getTime();
    var timetxt = msToTime(el.time);
    var stop = '';
    var running = 'true';
    setCookie('playing_i', i);
    setCookie('playing_id', id);
    saveFrontend(i,{time,stop,start,i,timetxt,running,stop});
}


function saveCountDownStop(i){
    var el = list[i];
    var elDom = document.querySelector('.splide [data-i="'+i+'"]');
    var timer = elDom.querySelector('.timer');
    var id  = elDom.getAttribute('data-id');
    var now = new Date().getTime();
    var then = parseInt(timer.getAttribute('data-start'));
    list[i].time +=   now - then;
    var new_time = list[i].time; 


    var time = new_time;
    var start =  '';
    var timetxt = msToTime(new_time);
    var stop = new Date().getTime();
    var running = 'false';
    saveFrontend(i,{time,stop,start,i,timetxt,running,stop});
}



function saveFrontend(i,vars){
    var el = list[i];
    var elDom = document.querySelector('.splide [data-i="'+i+'"]');
    var timer = elDom.querySelector('.timer');
    var id = elDom.getAttribute('data-id');
    var cookie_name = `tasks_${id}_`;

    for (const [key, value] of Object.entries(vars)) {
         var input = elDom.querySelector('.task-'+key);
         var name  = input.getAttribute('data-name');

         //Salvataggio Zero
         objVar(el,name,value);

         //Salvataggio Uno  (ok)
         timer.setAttribute('data-'+key, value);

         //Salvataggio due
         input.value  = value;

         //Salvataggio tre
         setCookie(encodeURIComponent(cookie_name+name), encodeURIComponent(JSON.stringify(value)));
    }
}



function debug(){
    console.log('COOKIE');
    console.log(document.cookie);

    console.log('FORM');
    var form     = document.getElementById('main-form');
    var formData = new FormData(form); 
    var object = {};
    formData.forEach((value, key) => object[key] = value);
    console.log(object);

    console.log('DATI INLINE');
    console.log(document.querySelectorAll('.timer'));

    console.log('VARIABILE GLOBALE');
    console.log(list);

    console.log(JSON.stringify(object));
}


function stopCountdown(){
    var i = parseInt(playing.element.getAttribute('data-i'));
    saveCountDownStop(i);
    clearInterval(playing.interval);
  }

  function startCountdown(i){
          var elParent = document.querySelector('.splide [data-i="'+i+'"]');
          var el = elParent.querySelector('.timer');
          // Set the date we're counting down to
          var countDownDate = parseInt(el.getAttribute('data-start'));
          var already       = parseInt(el.getAttribute('data-time'));
        
          playing.element =  el;
          // Update the count down every 1 second
          playing.interval = setInterval(function() {
            // Get today's date and time
            var now = new Date().getTime();
            

            // Find the distance between now and the count down date
            var distance =  now - countDownDate;

            // Display the result in the element with id="demo"
            el.innerHTML = msToTime(distance+already);

            // If the count down is finished, write some text
            
          }, 1000);
  }

function updateBackend(){
    var form     = document.getElementById('main-form');
    var formData = new FormData(form); 
    var object = {};
    formData.forEach((value, key) => object[key] = value);
    var str = buildquery({'update':object});
    console.log(str);

    function reqListener() {
        console.log(this.responseText);
    }
      
    var oReq = new XMLHttpRequest();
    
    oReq.addEventListener("load", reqListener);
    oReq.open("POST", "/");
    oReq.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    oReq.send(str);
}


// stop erase animations from firing on load
document.addEventListener("DOMContentLoaded",function(){
	document.querySelector("form").addEventListener("click",e => {
		let checkboxCL = e.target.classList,
			pState = "pristine";

		if (checkboxCL.contains(pState)){
      checkboxCL.remove(pState);
      e.target.removeAttribute('checked');
    }
		
	});


    document.querySelector("form").addEventListener("click", e => {
        let checkboxCL = e.target.classList;
        if(checkboxCL.contains('task-checkbox'))
            updateBackend();
        if(checkboxCL.contains('task-checkbox') && e.target.checked){
            
            setTimeout(() => {
                splide.go('+1');
            }, 600);               
        }
    });
});