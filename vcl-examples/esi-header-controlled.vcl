# This is the standard test VCL configuration used in the examples and documentation of
# moc_varnish. Use it at own risk, but feel free to modify it and use it for any purpose.
# If you have good suggestions for improvement, please send me an email on janerik@mocsystems.com
#
#Backend definitions
#

#default is localhost, on my computer Apache is running on port 8080. Change to you specific needs. See the Varnish website for exmamples of 
#how to configures multiple backends and load-balancing etc.
backend default {
	.host = "127.0.0.1";
	.port = "8080";
}


sub vcl_recv {
if (req.request == "PURGE") {
	//Use this for varnish 3.0, the host part is not supported in Varnish 2.1	
	//purge("req.url ~ " req.url " && req.http.host == " req.http.host);
	purge("req.url ~ " req.url);
	error 200 "Purged.";
}

    if(req.backend.healthy) {
		set req.grace = 10m;
    } else {
		set req.grace = 24h;
    }


    if (req.request != "GET" &&
      req.request != "HEAD" &&
      req.request != "PUT" &&
      req.request != "POST" &&
      req.request != "TRACE" &&
      req.request != "OPTIONS" &&
      req.request != "DELETE") {
        /* Non-RFC2616 or CONNECT which is weird. */
        return (pipe);
    }
    if (req.request != "GET" && req.request != "HEAD") {
        /* We only deal with GET and HEAD by default */
        return (pass);
    }

    #do not cache awstats subfolder.
    if (req.url ~ "/awstats") {
        return (pass);
    }

    #logins need to go via pipe, so it dosnt break when there a multiple backends
    if (req.url ~ "/typo3/index.php$") {        
        return (pipe);
    }

    #Respect force-reload, and clear cache accordingly. This means that a ctrl-reload will acutally purge 
    # the cache for this URL.
    #if (req.http.Cache-Control ~ "no-cache") {
    #   purge_url(req.url);
    #   return (pass);
    #}

    #Always cache all images
    if (req.url ~ "\.(png|gif|jpg|swf)$") {
      return(lookup);
    }

	

    ##Do not cache if either be_typo_user
	#Disabled for testing purposes
    #if (req.http.Authorization || req.http.Cookie ~ ".*be_typo_user=.*") {
    #    return (pass); 
    #}


    return (lookup);
}

sub vcl_fetch {

	#Respect force-reload, and clear cache accordingly. This means that a ctrl-reload will acutally purge 
	# the cache for this URL.
	if (req.http.Cache-Control ~ "no-cache") {
		set obj.ttl = 0s;
		#Make sure ESI includes are processed!
		esi;
	  	return (deliver);
	}


    if (req.url ~ "\.(png|gif|jpg|swf)$") {
       unset obj.http.set-cookie;
       set obj.http.X-Cacheable = "YES:jpg,gif,jpg ans swf are always cached";
       return (deliver);
    }



    #Allow 34 hour stale content, before an error 500/404 is thrown. When a backend server is not responding
	# allow varnish to server stale content for 24 hours afters its expirery.
    set obj.grace = 24h;

	#Allow edgeside includes
	esi;


	#Since we rely on TYPO to send the correct Cache-control headers, we do nothing except for removing the cache-control headers before output
	
	#Make sure that We remove alle cache headers, so the Browser does not cache it for us!
	remove obj.http.Cache-Control;
	remove obj.http.Expires;
	remove obj.http.Last-Modified;
	remove obj.http.ETag;
	remove obj.http.Pragma;
	
	
	
	return (deliver);
}

sub vcl_pipe {
    # Note that only the first request to the backend will have
    # X-Forwarded-For set.  If you use X-Forwarded-For and want to
    # have it set for all requests, make sure to have:
    # set req.http.connection = "close";
    # here.  It is not set by default as it might break some broken web
    # applications, like IIS with NTLM authentication. 
    return (pipe);
}


sub vcl_error {
    set obj.http.Content-Type = "text/html; charset=utf-8";

    synthetic {"
    <?xml version="1.0" encoding="utf-8"?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
     <html>
      <head>
      <title>"} obj.status " " obj.response {"</title>
      <meta content="text/html; charset=utf-8" http-equiv="Content-Type"/>
     </head>
    <body>   
     <script type="text/javascript">
       function show_moreinfo(var1){
         document.getElementById(var1).style.display="block";
         document.getElementById(var1+"_button").style.display="none";
       }
      </script>
    
      <div style="color:#A5C642;">
       Der er desv&aelig;rre et problem med at tilg&aring; den &oslash;nskede side.
       <br/>
       Pr&oslash;v venligst igen senere.
      </div>
      <br />
      <div style="color:#949494;">
       The requested page is not available.
       <br/>
       Please try again later.
      </div>    
      <br />
     
      <span id="moreinfo" style="display:none;border:2px #a5c642 solid; width: 550px;">
       <span style="color:#949494;">
        <h2>More information: </h2>
        <h3>Error "} obj.status " " obj.response {"</h3>
       <p>"} obj.response {"</p>
       <p>XID: "} req.xid {"</p>
       </span>
      </span>
      <br />
      <input id="moreinfo_button" type="button" value="More information" onclick="show_moreinfo('moreinfo')"/>
    
      <br /><br />
      <div id="logo">
       <img src="data:image/gif;base64,R0lGODlhaQApAPcAABgYGCkpKTExMTk5OUJCQkpKSlJSUlpaWmNjY2tra3Nzc3t7e4SEhIyMjJSUlJycnKWlpaXGQqXGSqXOSq2tra3OSq3OUq3OWrW1tbXOWrXOY7XWY7XWa729vb3Wa73Wc73We73ee8bGxsbWe8bee8behMbejM7Ozs7ejM7elM7nlM7nnNbW1tbnnNbnpdbnrd7e3t7nrd7ntd7vtd7vvefn5+fvvefvxufvzuf3zu/vzu/v7+/3zu/31u/33vf33vf35/f39/f/7///7///9////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////ywAAAAAaQApAAAI/gCLFCESpMcNGS9cpDBhYgQIEBw+aOCgoaKGCxgvWKzIIeKHhyVMpFjhQgaNHkCCCFzJsqXLlzBjymSJQ0MECTcj5Nyps6dPnD6D5gRKVKeFFERmKl3KtCURDjyLDt2JEyjVqT91Ws3po6nXry+JSI0adKvVsVV7ntUaQUZSsHC9+rjKlu1atGqz1t2aIq5fpjf25hU8eK3duldxgvjLWKYMnokLS8Z6F7FODo0zu3RxGLLhz3o958WZQbNpgSnMdgaN2DBWuhZUns5s4rVqsqClstb6djbjEqHTEm6td7dV2b7/giA7HG9nuqyBJGf8QbDxyHRFC5Y+3S+Hscy1/hMP77pr97hQs7sWTn4ye5w9zse1SVi48/WTLeOQD9fmbuj1qbdaTvvx99UF47WXn4CDaXWDgQfepyB2AH72IIRNIQhgg0W5xtxtQBWI4VIXsHbdhPgR2NuIMmnooXMfPgejiCy2iJduMg4XY1001giTi5bhyGF+JuqEw4o+uuRfVi8yWGGAPSbZEn1ONmmbeFJFKeVKUEl45Y5Dthbfli+l52VzQ8JolXlktlTdbVgm6KVUbLa50nKU9XQBCBqEZMIHElgwAkOAaiXBBwyZAIKGN3HgZwmMSsCdnSsBRxxOJQQBxA004BDEDUDgQMMNO+CAEwdA+EDDqp6mcBMN/p+a5CmeEgjBGAYizFabhxGMEASVGhRhAlAXELGcDDjUZUIQEXBAxJIRuBDYTbLV4MACCzDQQRAQINdtBwxg68AOIhhwgAMNdFAEBeEugEERO2Bw7QIdsBBuAzUEscMD2DLwbmrW6VSCEETF1uVNQowgwQ0v5OVBEBaAEERRFmhowUoHQMDCxgTsoAAERQQhwgEwDLAxCw8wAEMCC4ggAgwPIHByAhDAAAAGMJxQgAEnsIDBAEEo0MDJBdTAmXgl7GDVxekhrDDDPH0AscRnWeCBThespEACDnStbg0D7FBEASzUEEDXDjxQQxENPKB1AQnEbUACMBSwUtcrCRAE/gMIoP3uYzmOQHBOFRRxcK0jtGUqWykIEYEGT1mVwlwSBCtQBy530AEBJxSB7shF1IA55g8YUAQD3RaxwMc11LADDLDbLRDeAoXtsgiaG9DBDbtlqpYEkWslBHAa+BAqDsgHsSusyOPQQxCuRrCYQAc8oPnmLIQ8AAHZs0DA9Q4csK7uDyRAQQEYaJ5AA3Xf7cBKYXN9fQEiUD6kBd+xVWhOHFigFQcgKMFD/JeTPgVwUVrpy0B2AIGuPeAEsnmA+EImggd0jQJiC0IHHAABsVUwbdsSmWxgJxANwquBacsVs8JTHNwc5kaDeYFMPEYAGLDoKZdi4VYgs6C1jAkmPEHIl49uAKQW5qkyKaKKCSgFEyKUigYJWYgAQSARilwEIxbIYhYvoEWMZIAjH/hISEbyAmT5AElMLEJAAAA7"/>
     </div>
    </body>
   </html>
     "};
    deliver;
}

#custom error page
