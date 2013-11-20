<?php
global $USER, $APPLICATION;

if ( (bool) $USER->IsAdmin() !== true) {
	$APPLICATION->AuthForm();
}

$APPLICATION->SetTitle('Генератор меню для статичных файлов');

define('MENU_CREATE_GLOBAL','global');
define('MENU_CREATE_LOCAL','local');

$arResult['ERROR'] = false;
$arResult['MENUS'] = array();

$menu = COption::GetOptionString('fileman','menutypes');
$menu = unserialize(stripslashes($menu));

if (is_array($menu) && count($menu)) {
	$arResult['MENUS'] = $menu;
}

$arResult['PARAMS'] = array(
	'folder' => '/',
	'filter' => '*.php',
	'ignore' => '',
	'menu_create' => MENU_CREATE_GLOBAL,
	'menu_type' => 'left',
	'subfolders' => true,
);

$arResult['ITEMS'] = array();

// Сканируем папки
if (isset($_POST['scan']) && isset($_POST['s']) && is_array($_POST['s'])) {

    $scanFolder = $_SERVER['DOCUMENT_ROOT'] . '/' . slash($_POST['s']['folder']);
    $scanFilter = trim($_POST['s']['filter']);
    $scanIgnore = trim($_POST['s']['ignore']);
    $scanMenuCreate = trim($_POST['s']['menu_create']);
    $scanMenuType = trim($_POST['s']['menu_type']);
    $scanSubfolders = isset($_POST['s']['subfolders']) && trim($_POST['s']['subfolders'] == 'on') ? true : false;

    if (substr_count($scanIgnore, ',') > 0) {
    	$scanIgnore = explode(',', $scanIgnore);
    } elseif ($scanIgnore != '') {
    	$scanIgnore = array($scanIgnore);
    } else {
    	$scanIgnore = array();
    }

    $arResult['PARAMS'] = array(
    	'folder' => slash($_POST['s']['folder']),
    	'filter' => $scanFilter,
    	'ignore' => slash($_POST['s']['ignore']),
    	'menu_create' => $scanMenuCreate,
    	'menu_type' => $scanMenuType,
    	'subfolders' => $scanSubfolders,
    );

	if (!is_dir($scanFolder)) {
		$arResult['ERROR'] = 'Folder not found';
		$this->IncludeComponentTemplate();
		return ;
	}

	if (getDir($scanFolder, $scanFilter, $scanSubfolders, $arResult['ITEMS'], $scanIgnore) === false) {
		$arResult['ERROR'] = 'Error scan dir. Big truble (';
		$this->IncludeComponentTemplate();
		return ;
	}

	$arResult['INFO'] = array(
		'MENU_FILE' => '.'.$arResult['PARAMS']['menu_type'].'.menu.php',
		'MENU_TYPE' => $scanMenuType,
		'SUBFOLDERS' => $scanSubfolders,
		'FOLDER'    => $arResult['PARAMS']['folder'],
		'FOLDER_FILTER'    => $arResult['PARAMS']['folder'] . $arResult['PARAMS']['filter'],
		'IGNORE' => $scanIgnore
	);

	$arResult['INFO']['MENU_ROOT'] = $arResult['PARAMS']['folder'] . $arResult['INFO']['MENU_FILE'];
}

if (isset($_POST['menu']) && is_array($_POST['menu']) && isset($_POST['link']) && is_array($_POST['link'])) {
	// We must create menu. Let's go )
	$menus = $_POST['menu'];
	$links = $_POST['link'];
	$isCreated = $_POST['created'];

	CModule::IncludeModule('fileman');

	foreach ($menus as $menuIndex => $file) {

		$aMenuLinks = array();
		
		if (isset($isCreated[$menuIndex])) {
			foreach ($isCreated[$menuIndex] as $linkIndex => $on) {
				$url = $links[$menuIndex][$linkIndex];
				$res = CFileman::ParseFileContent( $APPLICATION->GetFileContent($_SERVER['DOCUMENT_ROOT'].$url) );
				$aMenuLinks[] = array(
					$res['TITLE'],
					$url,
					array(),
					array(),
					""
				);
			}
		}

		// CFileman::SaveMenu($_SERVER['DOCUMENT_ROOT'].$file, $aMenuLinks);
		createMenu($file, $aMenuLinks);
	}

	$arResult['CREATED_OK'] = true;
}

function getDir($path, $filter, $recursive, &$result, $ignore)
{
	foreach (glob($path.'/*', GLOB_ONLYDIR) as $dir) {
		
		if ($dir == '.' | $dir == '..' | ignored($dir, $ignore)) continue;

		foreach (glob($dir.'/'.$filter) as $file) {
			if (ignored($file, $ignore)) continue;

			$f[] = slash(str_replace($_SERVER['DOCUMENT_ROOT'], '/', $file), false);
		}

		if (count($f) > 0) {
			$result[] = array(
				'DIR' => slash(str_replace($_SERVER['DOCUMENT_ROOT'], '/', $dir)),
				'FILES' =>  $f,
			);	
		}
		
		if ($recursive) {
			if (getDir($dir, $filter, $recursive, $result, $ignore) === false) {
				return false;
			}
		}
	} 

	return true;
}

function createMenu($file,$items = array())
{
	global $APPLICATION;
	$content = '<?php $aMenuLinks = ' . var_export($items, true).'; ?>';
	$APPLICATION->SaveFileContent($_SERVER['DOCUMENT_ROOT'] . $file, $content);
}


function ignored($value, $list){
	$value = str_replace($_SERVER['DOCUMENT_ROOT'], '', $value);
	foreach ($list as $path) {
		if (substr_count($value, trim($path) ) > 0) {
			return true;
		}
	}
	return false;
}

function slash($path,$addSlash = true)
{
	$index = 0;
	while ( substr_count($path, '//') > 0 or $index < 10 ) {
		$path = str_replace('//', '/', $path);
		$index ++;
	}
	$path = trim($path);
	if ($addSlash && $path[strlen($path)-1] != '/') {
		$path = $path.'/';
	}
	return $path;
}

$this->IncludeComponentTemplate();
?>