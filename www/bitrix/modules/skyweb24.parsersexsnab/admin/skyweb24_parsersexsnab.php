<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
IncludeModuleLangFile(__FILE__);
$module_id="skyweb24.parsersexsnab";
$APPLICATION->SetTitle(GetMessage("SKWB24_SSHOP_MAIN_TITLE"));
CModule::IncludeModule('iblock');
if(CModule::IncludeModule($module_id)){
	$parsersexsnabO=new parsersexsnab;

	//ajax mode
	if(!empty($_REQUEST['AJAX']) && $_REQUEST['AJAX']=='Y'){
		if(!empty($_REQUEST['TYPE'])){
			if($_REQUEST['TYPE']=='rubric'){
				$retArr=$parsersexsnabO->uploadSection();
			}elseif($_REQUEST['TYPE']=='product'){
				$retArr=$parsersexsnabO->uploadProduct();
			}
			$retArr['tab']=$_REQUEST['TYPE'];
			echo Cutil::PhpToJSObject($retArr);
		}elseif(!empty($_REQUEST['COMMAND']) && $_REQUEST['COMMAND']=='UPDATE_SECTION'){
			$retArr=$parsersexsnabO->updateSection();
			echo Cutil::PhpToJSObject($retArr);
		}elseif(!empty($_REQUEST['COMMAND']) && $_REQUEST['COMMAND']=='UPDATE_PRODUCTS'){
			$retArr=$parsersexsnabO->updateProduct();
			echo Cutil::PhpToJSObject($retArr);
		}
		die();
	}
}
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
CJSCore::Init(array("jquery"));

$APPLICATION->IncludeFile("/bitrix/modules/skyweb24.parsersexsnab/include/headerInfo.php", Array());

if(CModule::IncludeModule($module_id)){
	$parsersexsnabO=new parsersexsnab;
	if($parsersexsnabO->currentIBlock==0){
		echo CAdminMessage::ShowMessage(
			array(
				"MESSAGE"=>GetMessage("SKWB24_SSHOP_EMPTY_CATALOG").'<br />'.GetMessage("SKWB24_SSHOP_GO_EDIT_START").' <a href="/bitrix/admin/settings.php?mid='.$module_id.'">'.GetMessage("SKWB24_SSHOP_GO_EDIT_LINK").'</a> '.GetMessage("SKWB24_SSHOP_GO_EDIT_FINISH"),
				"HTML"=>true,
				"TYPE"=>"ERROR"
			)
		);
	}
	$aTabs = array(
		array("DIV" => "parsersexsnab_section", "TAB" => GetMessage("SKWB24_SSHOP_TAB_SECTION_NAME"), "ICON" => "", "TITLE" => GetMessage("SKWB24_SSHOP_TAB_SECTION_TITLE"), 'ONSELECT'=>'selectTab("rubric")'),	
		array("DIV" => "parsersexsnab_products", "TAB" => GetMessage("SKWB24_SSHOP_TAB_PRODUCT_NAME"), "ICON" => "", "TITLE" => GetMessage("SKWB24_SSHOP_TAB_PRODUCT_TITLE"), 'ONSELECT'=>'selectTab("product")'),	
	);
	$tabControl = new CAdminTabControl("tabControl", $aTabs);
	$tabControl->Begin();
	$tabControl->BeginNextTab();
	echo $parsersexsnabO->sectionFile();?>
<tr>
	<td width="50%" class="adm-detail-content-cell-l">
		<?=GetMessage("SKWB24_SSHOP_SECTION_FILE_UPLOAD")?>
	</td>
	<td class="adm-detail-content-cell-r">
		<?$APPLICATION->IncludeComponent("bitrix:main.file.input", "drag_n_drop",
		   array(
			  "INPUT_NAME"=>"shop_file",
			  "MULTIPLE"=>"N",
			  "MODULE_ID"=>$module_id,
			  "MAX_FILE_SIZE"=>"10000000",
			  "ALLOW_UPLOAD"=>"F",
			  "ALLOW_UPLOAD_EXT"=>'csv'
		   ),
		   false
		);?>
	</td>
</tr>
	<?$tabControl->BeginNextTab();?>
	<tr id="upload_product_error">
		<td class="adm-detail-content-cell-l _algo" colspan="2" style="text-align: left;">
			<?CAdminMessage::ShowMessage(array(
				"MESSAGE"=>GetMessage("SKWB24_SSHOP_UPLOAD_PRODUCT_ERROR")
			));?>
		</td>
	</tr>
	<tbody class="productInfoArea">
		<?echo $parsersexsnabO->productFile();?>
	</tbody>
<tr>
	<td width="50%" class="adm-detail-content-cell-l">
		<?=GetMessage("SKWB24_SSHOP_PRODUCT_FILE_UPLOAD")?>
	</td>
	<td class="adm-detail-content-cell-r">
		<?$APPLICATION->IncludeComponent("bitrix:main.file.input", "drag_n_drop",
		   array(
			  "INPUT_NAME"=>"product_file",
			  "MULTIPLE"=>"N",
			  "MODULE_ID"=>$module_id,
			  "MAX_FILE_SIZE"=>"10000000",
			  "ALLOW_UPLOAD"=>"F",
			  "ALLOW_UPLOAD_EXT"=>'csv'
		   ),
		   false
		);?>
	</td>
</tr>
	<?$tabControl->End();?>
<script>

	var currentTab='rubric',
		sectionArea=$('#parsersexsnab_section_edit_table'),
		productArea=$('#parsersexsnab_products_edit_table');
	function selectTab(type){
		if(type){currentTab=type;}
	}
	
	BX.addCustomEvent('uploadFinish', function(result){
		if(result.error!='undefined'){
			BX.ajax({
				url: '<?=$APPLICATION->GetCurPage()?>',
				data: {ID:result.element_id, AJAX:'Y', TYPE:currentTab},
				method: 'POST',
				dataType: 'json',
				timeout: 200,
				async: true,
				onsuccess: function(data){
					if(data.tab && data.tab=='rubric'){
						sectionArea.find('tr.info').remove();
						if(!data.status){
							sectionArea.prepend('<tr class="info"><td class="adm-detail-content-cell-l _algo" colspan="2" style="text-align: left;">'+data.text+'</td></tr>');
						}else if(data.status){
							sectionArea.find('.rubricInfoArea').html(data.text);
						}
					}else if(data.tab && data.tab=='product'){
						productArea.find('tr.info').remove();
						if(!data.status){
							productArea.prepend('<tr class="info"><td class="adm-detail-content-cell-l _algo" colspan="2" style="text-align: left;">'+data.text+'</td></tr>');
						}else if(data.status){
							productArea.find('.productInfoArea').html(data.text);
						}
					}
				},
				onfailure: function(erdata){
					console.log(erdata);
				}
			});
		}
	});
	
	$(document).ready(function(){
		sectionArea=$('#parsersexsnab_section_edit_table');
		productArea=$('#parsersexsnab_products_edit_table');
		
		//rubric manage
		$('#parsersexsnab_section').on('click', '.root_section li.parent .label span', function(){
			var childUl=$(this).closest('li').find('>ul');
			if(childUl.length>0){
				var currentView='block';
				if(childUl.css('display')=='block'){
					currentView='none';
				}
				childUl.css('display', currentView);
			}
		});
		
		$('#parsersexsnab_section').on('change keyup', 'input[type=number]', function(){
			if($(this).val()=='' || $(this).val()<0){
				$(this).val('0');
			}
			$(this).closest('li').find('ul input[type=number]').val($(this).val());
		});
		$('#parsersexsnab_section').on('change', '.root_section input[type=checkbox]', function(){
			$(this).closest('li').find('ul input[type=checkbox]').prop('checked', $(this).prop('checked'));
			if($(this).prop('checked')){
				var firstUl=$(this).closest('ul');
				firstUl.closest('li').find('>.label input[type=checkbox]').prop('checked', true);
				firstUl.closest('li').parent().closest('li').find('>.label input[type=checkbox]').prop('checked', true);
			}
		});
		
		$('#parsersexsnab_section').on('click', '.update_structure', function(){
			var sectionBlock=$('#parsersexsnab_section .root_section').parent(),
				sectionBlockArr=sectionBlock.serializeArray(),
				newSectionArr={margin:[], active:[], tree:[], name:[]},
				treeSections=[];
			for(var i=0; i<sectionBlockArr.length; i++){
				if(sectionBlockArr[i].name.indexOf('activeSection')>-1){
					newSectionArr.active.push(sectionBlockArr[i].value);
				}else if(sectionBlockArr[i].name.indexOf('marginSection')>-1){
					tmpIndex=sectionBlockArr[i].name.split('[');
					tmpIndex=parseInt(tmpIndex[1]);
					newSectionArr.margin[tmpIndex]=sectionBlockArr[i].value;
				}
			}
			sectionBlock.find('li').each(function(){
				newSectionArr.name[$(this).data('id')]=$(this).find('span').eq(0).text();
			});
			
			sectionBlock.find('.root_section li').each(function(){
				var tmpParent=0;
				if($(this).data('id')){
					if($(this).parent().closest('li')!='' && $(this).parent().closest('li').data('id')){
						tmpParent=$(this).parent().closest('ul li').data('id');
					}
					newSectionArr.tree[$(this).data('id')]=tmpParent;
				}
			});
			
			if(sectionBlock.length>0){
				BX.ajax({
					url: '<?=$APPLICATION->GetCurPage()?>',
					data: {SECTIONS:newSectionArr, AJAX:'Y', COMMAND:'UPDATE_SECTION'},
					method: 'POST',
					dataType: 'json',
					timeout: 200,
					async: true,
					onsuccess: function(data){
						sectionArea.find('tr.info').remove();
						sectionArea.prepend('<tr class="info"><td class="adm-detail-content-cell-l _algo" colspan="2" style="text-align: left;">'+data+'</td></tr>');
					},
					onfailure: function(erdata){
						console.log(erdata);
					}
				});
			}
		});
		
		var stepProduct=1;
		$('#parsersexsnab_products').on('click', '.update_products', function(){
			var this_=$(this);
			this_.prop('disabled', true);
			BX.ajax({
				url: '<?=$APPLICATION->GetCurPage()?>',
				data: {AJAX:'Y', COMMAND:'UPDATE_PRODUCTS', STEP:stepProduct},
				method: 'POST',
				dataType: 'json',
				timeout: 200,
				async: true,
				onsuccess: function(data){
					this_.prop('disabled', false);
					productArea.find('tr.info').remove();
					productArea.prepend('<tr class="info"><td class="adm-detail-content-cell-l _algo" colspan="2" style="text-align: left;">'+data.text+'</td></tr>');
					if(data.status!='end'){
						stepProduct=(data.status=='end_upload')?'end':(parseInt(data.status)+1);
						this_.trigger('click');
					}else{
						stepProduct=1;
					}
				},
				onfailure: function(erdata){
					console.log(erdata);
					this_.prop('disabled', false);
					$('#upload_product_error').css('display', 'table-row');
				}
			});
		});
	});
	
</script>
<?}else{
	echo 'no';
}
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>