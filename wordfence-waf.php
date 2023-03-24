<?php
// Before removing this file, please verify the PHP ini setting `auto_prepend_file` does not point to this.

// This file was the current value of auto_prepend_file during the Wordfence WAF installation (Fri, 16 Apr 2021 18:46:39 +0000)
if (file_exists('/opt/nas/www/common/production/auto_prepends.php')) {
	include_once '/opt/nas/www/common/production/auto_prepends.php';
}
if (file_exists('/nas/content/live/coobomedia/wp-content/plugins/wordfence/waf/bootstrap.php')) {
	define("WFWAF_LOG_PATH", '/nas/content/live/coobomedia/wp-content/wflogs/');
	include_once '/nas/content/live/coobomedia/wp-content/plugins/wordfence/waf/bootstrap.php';
}
?>