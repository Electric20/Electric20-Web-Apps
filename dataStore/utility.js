function logAction (action) {
	$.ajax({
  		url: "../dataStore/jsLog.php",
  		data: {"action": action, "agent": navigator.userAgent}
	});
}
