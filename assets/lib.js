String.prototype.interpolate = function(params) {
    const names = Object.keys(params);
    const vals = Object.values(params);
    return new Function(...names, `return \`${this}\`;`)(...vals);
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



function blankTodolist(id) {
    return todolist_template.interpolate({id}); 
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
    t.setAttribute('name','tags[]');
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
    var txt = document.createTextNode("\u00D7");
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

function updateBackend(keys){

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
        if(checkboxCL.contains('task-checkbox') && e.target.checked){
            setTimeout(() => {
                splide.go('+1');
            }, 600);               
        }
    });
});