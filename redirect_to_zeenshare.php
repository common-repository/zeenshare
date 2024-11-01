<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Redirecting...</title>
<script type="text/javascript">
function redirect() {
	 var params = extractUrlParams();
	 var newUrl = params['continueUrl'];
	 newUrl = decodeURIComponent(newUrl);
	 if(newUrl.indexOf("?") != -1) {
		 newUrl += '&sid=<?php echo $_REQUEST["zs_sid"]?>';
	 } else {
		 newUrl += '?sid=<?php echo $_REQUEST['zs_sid']?>';
	 }
	 window.location = newUrl;
}


function extractUrlParams() {
    var f = [];
    if(window.location.search && window.location.search.length >1) {
        var t = window.location.search.substring(1).split('&');
        for ( var i = 0; i < t.length; i++) {
            var x = t[i].split('=');
            f[x[0]] = x[1];
        }
    }
    return f;
}
</script>
</head>
<body onload="redirect()">
</body>
