<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
IncludeModuleLangFile(__FILE__);
?><?
IncludeModuleLangFile(__FILE__, LANGUAGE_ID);
  $aMenu = array(
				"parent_menu" => "global_menu_store", // �������� � ������ "������"
				"sort"        => 100,                    // ��� ������ ����
				"url"         => "skyweb24_parsersexsnab.php?lang=".LANGUAGE_ID,  // ������ �� ������ ����
				"text"        => GetMessage("skyweb24.parsersexsnab_MENU_MAIN"),       // ����� ������ ����
				"title"       => GetMessage("skyweb24.parsersexsnab_MENU_MAIN_TITLE"), // ����� ����������� ���������
				"icon"        => "skwb24_parsersexsnab_menu_icon", // ����� ������
				"page_icon"   => "", // ������� ������
				"items_id"    => "skyweb24_parsersexsnab",  // ������������� �����
				"items"       => array(),          // ��������� ������ ���� ���������� ����.
			);

return $aMenu;
?>