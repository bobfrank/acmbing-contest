
/*
 *  GetXmlHttpObject()
 *  
 *  This function returns an XMLHTTP object, which is the crux of all
 *  asynchronous http requests.
 */

function GetXmlHttpObject()
{ 
    var objXMLHttp = null;

    if (window.XMLHttpRequest) {
        objXMLHttp = new XMLHttpRequest();
    } else if (window.ActiveXObject) {
        objXMLHttp = new ActiveXObject("Microsoft.XMLHTTP");
    }

    return objXMLHttp;
} 

/*
 *  class AsyncHttpRequest(callback)
 *  
 *  This class allocates an XMLHTTP object and stores the specified
 *  callback. The callback function must take one string parameter.
 *
 *  Member functions
 *  ----------------
 *  get(url)          
 *      Asynchronously sends an HTTP GET request to the specified URL, 
 *      and when the response is received it is passed to callback 
 *      function. 
 *
 *  post(url,content) 
 *      Asynchronously sends an HTTP POST request to the specified URL
 *      with the specified data, and when the response is received it 
 *      is passed to callback function. 
 */

function AsyncHttpRequest(callback)
{
    // Get the XmlHttp Object

    this.xmlHttp = GetXmlHttpObject();
    if (this.xmlHttp == null) {
        alert("Browser does not support HTTP Request");
        return;
    }

    this.callback = callback;

    // Need to store value of this pointer so that
    // delegates work correctly

    var me = this;

    // Define a few functions

    this.callbackWrapper = function () { 
        if (me.xmlHttp.readyState == 4 || me.xmlHttp.readyState == "complete") { 
            me.callback(me.xmlHttp.responseText); 
        } 
    }
  
    this.get = function (url) {
        me.xmlHttp.onreadystatechange = me.callbackWrapper;
        me.xmlHttp.open("GET",url,true);
        me.xmlHttp.send(null);
    }

    this.post = function (url,content) {
        me.xmlHttp.onreadystatechange = me.callbackWrapper;
        me.xmlHttp.open("POST",url,true);
        me.xmlHttp.send(content);
    }
}

/*
 *  refresh(field,url)
 *
 *  The refresh function will asynchronously refresh the block 
 *  specified by field with the content of the specified URL
 */

function refresh(field,url)
{
    var asyncHttpReq = new AsyncHttpRequest(
        function(responseText) { 
            document.getElementById(field).innerHTML = responseText; 
        }
    );

    asyncHttpReq.get(url);
} 

/*
 *  addLoadEvent(func)
 *
 *  This function registers a callback function that gets called
 *  when the page loads. The callback functions are called in the
 *  order that they are registered.
 */

function addLoadEvent(func) 
{
    var oldonload = window.onload;
    if (typeof window.onload != 'function') {
        window.onload = func;
    } else {
        window.onload = function() {
            if (oldonload) {
                oldonload();
            }
            func();
        }
    }
}




