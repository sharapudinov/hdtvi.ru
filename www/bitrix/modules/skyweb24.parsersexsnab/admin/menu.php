<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
IncludeModuleLangFile(__FILE__);
?><?
IncludeModuleLangFile(__FILE__, LANGUAGE_ID);
  $aMenu = array(
				"parent_menu" => "global_menu_store", // поместим в раздел "Сервис"
				"sort"        => 100,                    // вес пункта меню
				"url"         => "skyweb24_parsersexsnab.php?lang=".LANGUAGE_ID,  // ссылка на пункте меню
				"text"        => GetMessage("skyweb24.parsersexsnab_MENU_MAIN"),       // текст пункта меню
				"title"       => GetMessage("skyweb24.parsersexsnab_MENU_MAIN_TITLE"), // текст всплывающей подсказки
				"icon"        => "skwb24_parsersexsnab_menu_icon", // малая иконка
				"page_icon"   => "", // большая иконка
				"items_id"    => "skyweb24_parsersexsnab",  // идентификатор ветви
				"items"       => array(),          // остальные уровни меню сформируем ниже.
			);

return $aMenu;
?>