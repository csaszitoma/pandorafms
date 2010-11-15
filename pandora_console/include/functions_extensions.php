<?php

// Pandora FMS - http://pandorafms.com
// ==================================================
// Copyright (c) 2005-2010 Artica Soluciones Tecnologicas
// Please see http://pandorafms.org for full contribution list

// This program is free software; you can redistribute it and/or
// modify it under the terms of the  GNU Lesser General Public License
// as published by the Free Software Foundation; version 2

// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

/**
 * @package Include
 * @subpackage Extensions
 */

$extension_file = '';


/**
 * TODO: Document extensions
 *
 * @param string $filename
 */
function extension_call_main_function ($filename) {
	global $config;
	
	$extension = &$config['extensions'][$filename];
	if ($extension['main_function'] != '') {
		$params = array ();
		call_user_func_array ($extension['main_function'], $params);
	}
}

/**
 * TODO: Document extensions
 *
 * @param string $filename
 */
function extension_call_godmode_function ($filename) {
	global $config;
	
	$extension = &$config['extensions'][$filename];
	if ($extension['godmode_function'] != '') {
		$params = array ();
		call_user_func_array ($extension['godmode_function'], $params);
	}
}

/**
 * TODO: Document extensions
 */
function extensions_call_login_function () {
	global $config;
	
	$params = array ();
	foreach ($config['extensions'] as $extension) {
		if ($extension['login_function'] == '')
			continue;
		call_user_func_array ($extension['login_function'], $params);
	}
}

/**
 * TODO: Document extensions
 *
 * @param string $page
 */
function is_extension ($page) {
	global $config;
	
	$filename = basename ($page);
	return isset ($config['extensions'][$filename]);
}

/**
 * Scan the EXTENSIONS_DIR or ENTERPRISE_DIR.'/'.EXTENSIONS_DIR for search
 * the files extensions.
 *
 * @param bool $enterprise
 */
function get_extensions ($enterprise = false) {
	$dir = EXTENSIONS_DIR;
	$handle = false;
	if ($enterprise)
		$dir = ENTERPRISE_DIR.'/'.EXTENSIONS_DIR;

	if (file_exists ($dir))
		$handle = @opendir ($dir);	
	
	if (empty ($handle))
		return;
		
	$file = readdir ($handle);
	$extensions = array ();
	$ignores = array ('.', '..');
	while ($file !== false) {
		if (in_array ($file, $ignores)) {
			$file = readdir ($handle);
			continue;
		}
		$filepath = realpath ($dir."/".$file);
		if (! is_readable ($filepath) || is_dir ($filepath) || ! preg_match ("/.*\.php$/", $filepath)) {
			$file = readdir ($handle);
			continue;
		}
		$extension['file'] = $file;
		$extension['operation_menu'] = '';
		$extension['godmode_menu'] = '';
		$extension['main_function'] = '';
		$extension['godmode_function'] = '';
		$extension['login_function'] = '';
		$extension['enterprise'] = $enterprise;
		$extension['dir'] = $dir;
		$extensions[$file] = $extension;
		$file = readdir ($handle);
	}
	
	/* Load extensions in enterprise directory */
	if (! $enterprise && file_exists (ENTERPRISE_DIR.'/'.EXTENSIONS_DIR))
		return array_merge ($extensions, get_extensions (true));
	
	return $extensions;
}

/**
 * TODO: Document extensions
 *
 * @param array $extensions
 */
function load_extensions ($extensions) {
	global $config;
	global $extension_file;
	
	foreach ($extensions as $extension) {
		$extension_file = $extension['file'];
		require_once (realpath ($extension['dir']."/".$extension_file));
	}
}

/**
 * TODO: Document extensions
 *
 * @param string name
 * @param string fatherId
 * @param string icon
 */
function add_operation_menu_option ($name, $fatherId = null, $icon = null) {
	global $config;
	global $extension_file;
	
	/* $config['extension_file'] is set in load_extensions(), since that function must
	   be called before any function the extension call, we are sure it will 
	   be set. */
	$option_menu['name'] = mb_substr ($name, 0, 15);
	$extension = &$config['extensions'][$extension_file];
	$option_menu['sec2'] = $extension['dir'].'/'.mb_substr ($extension_file, 0, -4);
	$option_menu['fatherId'] = $fatherId;
	$option_menu['icon'] = $icon;
	$extension['operation_menu'] = $option_menu;
}

/**
 * TODO: Document extensions
 *
 * @param string name
 * @param string acl
 * @param string fatherId
 * @param string icon
 */
function add_godmode_menu_option ($name, $acl, $fatherId = null, $icon = null) {
	global $config;
	global $extension_file;
	
	/* $config['extension_file'] is set in load_extensions(), since that function must
	   be called before any function the extension call, we are sure it will 
	   be set. */
	$option_menu['acl'] = $acl;
	$option_menu['name'] = mb_substr ($name, 0, 15);
	$extension = &$config['extensions'][$extension_file];
	$option_menu['sec2'] = $extension['dir'].'/'.mb_substr ($extension_file, 0, -4);
	$option_menu['fatherId'] = $fatherId;
	$option_menu['icon'] = $icon;
	$extension['godmode_menu'] = $option_menu;
}

/**
 * Add in the header tabs in godmode agent the extension tab.
 * 
 * @param $tabId
 * @param $tabName
 * @param $tabIcon
 * @param $tabFunction
 */
function add_extension_godmode_tab_agent($tabId, $tabName, $tabIcon, $tabFunction) {
	global $config;
	global $extension_file;
	
	$extension = &$config['extensions'][$extension_file];
	$extension['extension_god_tab'] = array();
	$extension['extension_god_tab']['id'] = $tabId;
	$extension['extension_god_tab']['name'] = $tabName;
	$extension['extension_god_tab']['icon'] = $tabIcon;
	$extension['extension_god_tab']['function'] = $tabFunction;
}

/**
 * Add in the header tabs in operation agent the extension tab.
 * 
 * @param unknown_type $tabId
 * @param unknown_type $tabName
 * @param unknown_type $tabIcon
 * @param unknown_type $tabFunction
 */
function add_extension_opemode_tab_agent($tabId, $tabName, $tabIcon, $tabFunction) {
	global $config;
	global $extension_file;
	
	$extension = &$config['extensions'][$extension_file];
	$extension['extension_ope_tab'] = array();
	$extension['extension_ope_tab']['id'] = $tabId;
	$extension['extension_ope_tab']['name'] = $tabName;
	$extension['extension_ope_tab']['icon'] = $tabIcon;
	$extension['extension_ope_tab']['function'] = $tabFunction;
}

/**
 * TODO: Document extensions
 *
 * @param string $function_name
 */
function add_extension_main_function ($function_name) {
	global $config;
	global $extension_file;
	
	$extension = &$config['extensions'][$extension_file];
	$extension['main_function'] = $function_name;
}

/**
 * TODO: Document extensions
 *
 * @param string $function_name
 */
function add_extension_godmode_function ($function_name) {
	global $config;
	global $extension_file;
	
	$extension = &$config['extensions'][$extension_file];
	$extension['godmode_function'] = $function_name;
}

/**
 * TODO: Document extensions
 *
 * @param string $function_name
 */
function add_extension_login_function ($function_name) {
	global $config;
	global $extension_file;
	
	$extension = &$config['extensions'][$extension_file];
	$extension['login_function'] = $function_name;
}
?>
