<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Redirecting...</title>
<script type="text/javascript">
function redirect() {
	 var currentUrl = window.location.href;
	 currentUrl += "&zs_hasNewSession=1"
     var newUrl = "wp-login.php?redirect_to=" + encodeURIComponent(currentUrl);
	 window.location = newUrl;
}
</script>
</head>
<body onload="redirect()">
</body>
