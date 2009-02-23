<?php
$__start__ = microtime(true);
require_once("config.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title><?php print isset($_title_)?$_title_:TITLE_BOARD; ?></title>
<style type="text/css">
@import "/css/core.css";
<?php
if(session('externalcss')) $Style->external_css(session('externalcss'));
$Style->display($Style->get_theme());
?>
</style>
<script type="text/javascript" src="/js/core.js"></script>
</head>
<body lang="en">
<!-- Start Error Display -->
<?php error_display(); ?>
<!-- End Error Display -->
<?php if(!get('ajax') && file_exists(DIR."/lang/".LANG."_header.php")) require(DIR."/lang/".LANG."_header.php"); ?>
<!-- Start Content Display -->
<div id="content">
<div class="pad">
<?php print $buffer; ?>
<div class="small clear" style="text-align:right">page generated in <?php print (abs(microtime(true)-$__start__));?> seconds</div>
<div class="small version" style="text-align:right"><a href="/main/changelog/">pgboard v<?php print VERSION;?></a></div>
</div>
</div>
<div id="bottom"></div>
<!-- End Content Display -->
<?php if(!get('ajax') && file_exists(DIR."/lang/".LANG."_footer.php")) require(DIR."/lang/".LANG."_footer.php"); ?>
</body>
</html>
