<?php

require 'light-i18n.php';

$inis = array();

$handler = opendir(LANG_FILE_DIRECTORY);

while ($file = readdir($handler)) {
  if (strcasecmp(substr($file, -4), '.ini') == 0) {
	$lang = substr($file, 0, -4);
	$inis[$lang] = parse_ini_file(LANG_FILE_DIRECTORY.'/'.$file);
  }
}

closedir($handler);

$diff = call_user_func_array('array_diff_key', $inis);

foreach (array_keys($diff) as $key) {
  foreach ($inis as $lang => $values) {
	if (!isset($values[$key])) {
	  echo 'Missing key ',$key,' in file ',$lang,".ini\n";
	}
  }
}
