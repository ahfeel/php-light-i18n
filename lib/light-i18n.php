<?php

// Warning: If the session is not started, preferred language will not be saved.

define('LANG_FILE_DIRECTORY', dirname(__FILE__).'/../lang');

if (!defined('DETECT_LANGUAGE'))
  define('DETECT_LANGUAGE', true);

if (!defined('DEFAULT_LANG'))
  define('DEFAULT_LANG', 'en');

if (!isset($_SESSION['lang']) || isset($_GET['lang'])) {
  $langs = Array();

  if (DETECT_LANGUAGE === true) {
	if (isset($_GET['lang']))
	  $langs[] = $_GET['lang'];

	if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
	  foreach (explode(',',$_SERVER['HTTP_ACCEPT_LANGUAGE']) as $part) {
		$langs[] = strtolower(substr($part,0,2));
	  }
	}
  }

  $langs[] = DEFAULT_LANG;

  foreach ($langs as $lang) {
	if (file_exists(LANG_FILE_DIRECTORY .'/'. $lang . '.ini')) {
	  $_SESSION['lang'] = $lang;
	  break;
	}
  }
}

$langfile = LANG_FILE_DIRECTORY.'/'.$_SESSION['lang'].'.ini';

$cacheFilePath = sys_get_temp_dir() . '/php_i18n_' . md5(__FILE__) . '_' . $_SESSION['lang'] . '.cache';
if (file_exists($cacheFilePath) == false || filemtime($cacheFilePath) < filemtime($langfile)) {

  if (!file_exists($langfile)) {
	die('Missing internationalisation file: '.$langfile);
  }

  $ini = parse_ini_file($langfile, true);
  if ($ini == null)
	die('Cannot parse ini file: '.$langfile);

  function compile_ini_section($section, $prefix = '') {
	$tmp = '';
	foreach ($section as $key => $value) {
	  if (is_array($value)) {
		$tmp .= compile_ini_section($value, $key.'_');
	  } else {
		$tmp .= 'const '.$prefix.$key.' = \''.str_replace('\'', '\\\'', $value)."';\n";
	  }
	}
	return $tmp;
  }

  $compiled = "<?php class L {\n";
  $compiled .= compile_ini_section($ini);
  $compiled .= '}';

  file_put_contents($cacheFilePath, $compiled);
  chmod($cacheFilePath, 0777);
}

require_once $cacheFilePath;
