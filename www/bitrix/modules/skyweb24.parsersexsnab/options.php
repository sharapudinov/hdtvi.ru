<?
IncludeModuleLangFile(__FILE__);
$module_id='skyweb24.parsersexsnab';
$changeStatus=false;
CModule::IncludeModule("iblock");
$listIBlock=array();
$res = CIBlock::GetList(Array('NAME'=>'ASC'), Array('ACTIVE'=>'Y'), false);
while($ar_res = $res->Fetch()){
	$listIBlock[]=array('ID'=>$ar_res['ID'],'NAME'=>$ar_res['NAME']);
}

if(isset($_REQUEST['shop_iblock'])){
	COption::SetOptionString($module_id, 'select_idblock', intval($_REQUEST['shop_iblock']));
	$changeStatus=true;
}
if(isset($_REQUEST['min_price'])){
	COption::SetOptionString($module_id, 'min_price', intval($_REQUEST['min_price']));
	$changeStatus=true;
}
if(isset($_REQUEST['base_margin'])){
	COption::SetOptionString($module_id, 'base_margin', intval($_REQUEST['base_margin']));
	$changeStatus=true;
	
	$tmpDeactiveSections='N';
	$tmpDeactiveProducts='N';
	if(!empty($_REQUEST['deactive_sections'])){
		$tmpDeactiveSections='Y';
	}
	if(!empty($_REQUEST['deactive_products'])){
		$tmpDeactiveProducts='Y';
	}
	COption::SetOptionString($module_id, 'deactive_sections', $tmpDeactiveSections);
	COption::SetOptionString($module_id, 'deactive_products', $tmpDeactiveProducts);
}


$currentIBlock=COption::GetOptionString($module_id, 'select_idblock');
$currentMinPrice=COption::GetOptionString($module_id, 'min_price');
$currentMargin=COption::GetOptionString($module_id, 'base_margin');
$currentDeactiveSections=COption::GetOptionString($module_id, 'deactive_sections');
$currentDeactiveProducts=COption::GetOptionString($module_id, 'deactive_products');

$aTabs = array(
	array("DIV" => "parsersexsnab_option", "TAB" => GetMessage("SKWB24_SSHOP_TAB_NAME"), "ICON" => "", "TITLE" => GetMessage("SKWB24_SSHOP_TAB_TITLE"))	
);?>
<form class="sshop_edit_block" method="post" action="<?echo $APPLICATION->GetCurPage()?>?mid=<?=urlencode($module_id)?>&amp;lang=<?=LANGUAGE_ID?>">
<?
$tabControl = new CAdminTabControl("tabControl", $aTabs);
$tabControl->Begin();
$tabControl->BeginNextTab();
if($currentIBlock==0){?>
	<tr>
		<td class="adm-detail-content-cell-l _algo" colspan="2" style="text-align: left;">
			<?echo CAdminMessage::ShowMessage(GetMessage("SKWB24_SSHOP_EMPTY_CATALOG"));?>
		</td>
	</tr>
<?}
if($changeStatus && $currentIBlock>0){?>
	<tr>
		<td class="adm-detail-content-cell-l _algo" colspan="2" style="text-align: left;">
			<?echo CAdminMessage::ShowNote(GetMessage("SKWB24_SSHOP_CHANGE_SUCCESS"));?>
		</td>
	</tr>
<?}?>
<tr>
	<td width="50%" class="adm-detail-content-cell-l">
		<?=GetMessage("SKWB24_SSHOP_SELECT_IBLOCK")?>
	</td>
	<td class="adm-detail-content-cell-r">
		<select name="shop_iblock">
			<option value="0"><?=GetMessage("SKWB24_SSHOP_SELECT_IBLOCK2")?></option>
		<?
			foreach($listIBlock as $nextBlock){
				$selected=($currentIBlock==$nextBlock['ID'])?' selected="selected"':'';
				echo '<option value="'.$nextBlock['ID'].'"'.$selected.'>'.$nextBlock['NAME'].' ['.$nextBlock['ID'].']</option>';
			}
		?>
		</select>
	</td>
</tr>
<tr>
	<td width="50%" class="adm-detail-content-cell-l">
		<?=GetMessage("SKWB24_SSHOP_MIN_PRICE")?>
	</td>
	<td class="adm-detail-content-cell-r">
		<input type="number" min="0" max="100" style="width:160px;" name="min_price" value="<?=$currentMinPrice?>" />
	</td>
</tr>
<tr>
	<td width="50%" class="adm-detail-content-cell-l">
		<?=GetMessage("SKWB24_SSHOP_BASE_MARGIN")?>
	</td>
	<td class="adm-detail-content-cell-r">
		<input type="number" min="0" max="100" style="width:160px;" name="base_margin" value="<?=$currentMargin?>" />
	</td>
</tr>
<?
$checkedSections=($currentDeactiveSections=='Y')?' checked="checked"':'';
$checkedProducts=($currentDeactiveProducts=='Y')?' checked="checked"':'';
?>
<tr>
	<td width="50%" class="adm-detail-content-cell-l">
		<?=GetMessage("SKWB24_SSHOP_DEACTIVE_SECTIONS")?>
	</td>
	<td class="adm-detail-content-cell-r">
		<input type="checkbox" name="deactive_sections" value="Y"<?=$checkedSections?> />
	</td>
</tr>
<tr>
	<td width="50%" class="adm-detail-content-cell-l">
		<?=GetMessage("SKWB24_SSHOP_DEACTIVE_PRODUCTS")?>
	</td>
	<td class="adm-detail-content-cell-r">
		<input type="checkbox" name="deactive_products" value="Y"<?=$checkedProducts?> />
	</td>
</tr>
<?$tabControl->Buttons(
	array(
		"back_url" => $_REQUEST["back_url"]
	)
);
$tabControl->End();?>
</form>