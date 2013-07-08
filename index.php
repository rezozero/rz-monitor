<html>
<head>
	<title>RZ Monitor</title>

	<script type="text/javascript" src="./js/jquery-1.10.1.min.js"></script>
	<script type="text/javascript" src="./js/rz-monitor.class.js"></script>
</head>
<body>
	<table id="responses">

	</table>
	<script type="text/javascript">
		$(window).load(function () {
			new RZMonitor(<?php echo file_get_contents(dirname(__FILE__).'/conf/sites.json'); ?>);
		});
	</script>
</body>	
</html>