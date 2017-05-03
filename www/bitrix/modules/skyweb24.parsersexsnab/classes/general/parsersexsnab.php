<?
\Bitrix\Main\Loader::IncludeModule('iblock');
\Bitrix\Main\Loader::IncludeModule('sale');
\Bitrix\Main\Loader::IncludeModule('catalog');

IncludeModuleLangFile(__FILE__);
class parsersexsnab{
	private $uploadArr;
	public $currentIBlock;
	private $currentMargin;
	private $module_id;
	function __construct(){
		$this->module_id="skyweb24.parsersexsnab";
		$this->currentIBlock=COption::GetOptionString($this->module_id, 'select_idblock');
		$this->currentMargin=COption::GetOptionString($this->module_id, 'base_margin');
		$this->updateUploadArr();
	}
	
	private function checkProp($listProp){
		foreach($listProp as $keyProp=>$nextProp){
			$ibp = new CIBlockProperty;
			$properties = CIBlockProperty::GetList(Array(), Array("IBLOCK_ID"=>$this->currentIBlock, 'CODE'=>$keyProp));
			if(!$prop_fields = $properties->GetNext()){
				$arFields = array(
					"NAME" => $nextProp,
					"ACTIVE" => "Y",
					"SORT" => "100",
					"CODE" => $keyProp,
					"PROPERTY_TYPE" => "S",
					"IBLOCK_ID" => $this->currentIBlock
				);
				$ibp->Add($arFields);
			}
		}
	}
	
	private function updateUploadArr(){
		$res = CFile::GetList(array(""), array("MODULE_ID"=>$this->module_id));
		while($res_arr = $res->GetNext()){
			$this->uploadArr[$res_arr['DESCRIPTION']]=$res_arr;
		}
	}
	
	private function createTreeSection(){
		$currentCountMarginField=$this->getMarginField();
		$tmpSection=array();
		$db_list = CIBlockSection::GetList(Array($by=>$order), array('IBLOCK_ID'=>$this->currentIBlock), false, array('ID', 'XML_ID', 'ACTIVE', $currentCountMarginField));
		while($ar_result = $db_list->GetNext()){
			$tmpSection[$ar_result['XML_ID']]=$ar_result;
		}
		
		$RootXml = new SimpleXMLElement('<form><ul class="root_section"></ul></form>');
		$rootUl=$RootXml->ul;
		$cFile=file($_SERVER["DOCUMENT_ROOT"].CFile::GetPath($this->uploadArr['rubric']['ID']));
		for($i=1; $i<count($cFile); $i++){
			$currentRow=explode(';', $cFile[$i]);
			//if(LANG_CHARSET=='windows-1251'){
				//$currentRow[1]=mb_convert_encoding($currentRow[1], "UTF-8");
				$currentRow[1]=iconv('windows-1251//TRANSLIT', 'utf-8', $currentRow[1]);
			//}
			if($currentRow[2]==0){
				$currentNode=$rootUl->addChild('li', '');
			}else{
				$findNode = $RootXml->xpath("//li[@data_id='".intval($currentRow[2])."']");
				if(!empty($findNode[0])){
					$findNode=$findNode[0];
					$findUL=$findNode->ul;
					if(empty($findUL)){
						$findUL=$findNode->addChild('ul', '');
					}
					$currentNode=$findUL->addChild('li', '');
					$findNode->addAttribute('class', 'parent');
				}
			}
			if(is_object($currentNode)){
				$currentNode->addAttribute('data_id', $currentRow[0]);
				//label
				$divLabel=$currentNode->addChild('div', '');
				$divLabel->addAttribute('class', 'label');
				$labelSpan=$divLabel->addChild('span', $currentRow[1]);
				$labelScheckbox=$divLabel->addChild('input', '');
				$labelScheckbox->addAttribute('value', $currentRow[0]);
				$labelScheckbox->addAttribute('name', 'activeSection[]');
				$labelScheckbox->addAttribute('type', 'checkbox');
				if(!empty($tmpSection[$currentRow[0]]) && $tmpSection[$currentRow[0]]['ACTIVE']=='Y'){
					$labelScheckbox->addAttribute('checked', 'checked');
				}
				//margin
				$currentMargin=$this->currentMargin;
				if(!empty($tmpSection[$currentRow[0]])){
					$currentMargin=$tmpSection[$currentRow[0]][$currentCountMarginField];
				}
				$marginCheck=$currentNode->addChild('input', '');
				$marginCheck->addAttribute('type', 'number');
				$marginCheck->addAttribute('min', '0');
				$marginCheck->addAttribute('value', $currentMargin);
				$marginCheck->addAttribute('name', 'marginSection['.$currentRow[0].']');
			}
		}
		$totalXML=$RootXml->asXML();
		$totalXML=str_replace('data_id', 'data-id', $totalXML);
		if(LANG_CHARSET=='windows-1251'){
			return iconv('utf-8','windows-1251//TRANSLIT', $totalXML);
		}
		return $totalXML;
	}
	
	private function getMarginField(){
		//search or add margin field for sections
		$currentCountMarginField=1;
		$issetField=true;
		$currentIdMargin=0;
		while($issetField==true){
			$rField= CUserTypeEntity::GetList(array($by=>$order), array('FIELD_NAME'=>'UF_SSHOP_MARGIN_'.$currentCountMarginField));
			if($arRes = $rField->Fetch()){
				if($arRes['ENTITY_ID']=='IBLOCK_'.$this->currentIBlock.'_SECTION'){
					$issetField=false;
					$currentIdMargin=$arRes['ID'];
				}else{
					$currentCountMarginField++;
				}
			}else{
				$issetField=false;
			}
		}
		if($currentIdMargin==0){
			$ob = new CUserTypeEntity();
			$arFields = array(
				'ENTITY_ID' => 'IBLOCK_'.$this->currentIBlock.'_SECTION',
				'FIELD_NAME' => 'UF_SSHOP_MARGIN_'.$currentCountMarginField,
				'USER_TYPE_ID' => 'double',
				'XML_ID' => 'UF_SSHOP_MARGIN_'.$currentCountMarginField,
				'SORT' => 100,
				'MULTIPLE' => 'N',
				'MANDATORY' => 'N',
				'SHOW_FILTER' => 'I',
				'SHOW_IN_LIST' => 'Y',
				'EDIT_IN_LIST' => 'Y',
				'IS_SEARCHABLE' => 'N'
			);
			$currentIdMargin = $ob->Add($arFields);
		}
		return 'UF_SSHOP_MARGIN_'.$currentCountMarginField;
	}
	
	private function getSectionByXML_ID($xmlId){
		$retN=0;
		$db_list = CIBlockSection::GetList(Array($by=>$order), array('IBLOCK_ID'=>$this->currentIBlock, 'XML_ID'=>$xmlId), array('ID'));
		if($ar_result = $db_list->GetNext()){
			$retN=$ar_result['ID'];
		}
		return $retN;
	}
	
	public function sectionFile(){
		if(empty($this->uploadArr['rubric'])){
			$tmpMess=new CAdminMessage(GetMessage("SKWB24_SSHOP_SECTION_FILE_NOT_UPLOAD"));
			$tmpBodyRubric='<tbody class="rubricInfoArea">
			<tr>
				<td class="adm-detail-content-cell-l _algo" colspan="2" style="text-align: left;">'.$tmpMess->Show().'</td>
			</tr>
		</tbody>';
		}else{
			$tmpBodyRubric='<tbody class="rubricInfoArea">';
			if(!empty($_REQUEST['AJAX']) && $_REQUEST['AJAX']=='Y' && $_REQUEST['TYPE']=='rubric'){
				$textSucces=new CAdminMessage(array('MESSAGE'=>GetMessage("SKWB24_SSHOP_UPLOAD_FILE_SECTION_SUCCESS"), 'TYPE'=>'OK'));
				$tmpBodyRubric.='<tr class="info">
					<td class="adm-detail-content-cell-l _algo" colspan="2" style="text-align: left;">'.$textSucces->Show().'</td>
			</tr>';}
			$tmpBodyRubric.='<tr>
				<td width="40%" class="adm-detail-content-cell-l">'.GetMessage("SKWB24_SSHOP_SECTION_FILE_UPLOADED").':</td>
				<td class="adm-detail-content-cell-r"><b>'.$this->uploadArr['rubric']['FILE_NAME'].'</b></td>
			</tr>
			<tr>
				<td width="40%" class="adm-detail-content-cell-l">'.GetMessage("SKWB24_SSHOP_SECTION_FILE_SIZE").':</td>
				<td class="adm-detail-content-cell-r"><b>'.$this->uploadArr['rubric']['FILE_SIZE'].' b</b></td>
			</tr>
			<tr>
				<td width="40%" class="adm-detail-content-cell-l">'.GetMessage("SKWB24_SSHOP_SECTION_FILE_TIME").':</td>
				<td class="adm-detail-content-cell-r"><b>'.$this->uploadArr['rubric']['TIMESTAMP_X'].'</b></td>
			</tr>
			<tr>
				<td class="adm-detail-content-cell-l _algo" colspan="2" style="text-align: center;">'.$this->createTreeSection().'</td>
			</tr>
			<tr>
				
				<td class="adm-detail-content-cell-r" colspan="2" style="text-align: center;">';
				if($this->currentIBlock==0){
					$tmpBodyRubric.=CAdminMessage::ShowMessage(GetMessage("SKWB24_SSHOP_SECTION_UPDATE_EMPTY_IBLOCK"));
				}else{
					$tmpBodyRubric.='<button class="update_structure" type="button">'.GetMessage("SKWB24_SSHOP_SECTION_UPDATE_STRUCTURE_SHORT").'</button>';
				}
				$tmpBodyRubric.='</td>
			</tr>
			</tbody>';
		}
		return $tmpBodyRubric;
	}
	
	public function productFile(){
		if(empty($this->uploadArr['product'])){
			$tmpMess=new CAdminMessage(GetMessage("SKWB24_SSHOP_PRODUCT_FILE_NOT_UPLOAD"));
			$tmpBodyRubric='
			<tr>
				<td class="adm-detail-content-cell-l _algo" colspan="2" style="text-align: left;">'.$tmpMess->Show().'</td>
			</tr>';
		}else{
			$tmpBodyRubric='';
			if(!empty($_REQUEST['AJAX']) && $_REQUEST['AJAX']=='Y' && $_REQUEST['TYPE']=='product'){
				$textSucces=new CAdminMessage(array('MESSAGE'=>GetMessage("SKWB24_SSHOP_UPLOAD_FILE_PRODUCT_SUCCESS"), 'TYPE'=>'OK'));
				$tmpBodyRubric.='<tr class="info">
					<td class="adm-detail-content-cell-l _algo" colspan="2" style="text-align: left;">'.$textSucces->Show().'</td>
			</tr>';}
			$tmpBodyRubric.='<tr>
				<td width="40%" class="adm-detail-content-cell-l">'.GetMessage("SKWB24_SSHOP_SECTION_FILE_UPLOADED").':</td>
				<td class="adm-detail-content-cell-r"><b>'.$this->uploadArr['product']['FILE_NAME'].'</b></td>
			</tr>
			<tr>
				<td width="40%" class="adm-detail-content-cell-l">'.GetMessage("SKWB24_SSHOP_SECTION_FILE_SIZE").':</td>
				<td class="adm-detail-content-cell-r"><b>'.$this->uploadArr['product']['FILE_SIZE'].' b</b></td>
			</tr>
			<tr>
				<td width="40%" class="adm-detail-content-cell-l">'.GetMessage("SKWB24_SSHOP_SECTION_FILE_TIME").':</td>
				<td class="adm-detail-content-cell-r"><b>'.$this->uploadArr['product']['TIMESTAMP_X'].'</b></td>
			</tr>
			<tr>
				<td class="adm-detail-content-cell-r" colspan="2" style="text-align: center;">';
				if($this->currentIBlock==0){
					$tmpBodyRubric.=CAdminMessage::ShowMessage(GetMessage("SKWB24_SSHOP_SECTION_UPDATE_EMPTY_IBLOCK"));
				}else{
					$tmpBodyRubric.='<button class="update_products" type="button">'.GetMessage("SKWB24_SSHOP_SECTION_UPDATE_PRODUCTS_SHORT").'</button>';
				}
				$tmpBodyRubric.='</td>
			</tr>';
		}
		return $tmpBodyRubric;
	}
	
	public function uploadSection(){
		$fileValidate=false;
		$text='';
		$cFile=file($_SERVER["DOCUMENT_ROOT"].CFile::GetPath($_REQUEST['ID']));
		if(count($cFile)>0){
			$headerStr=$cFile[0];
			if(strpos($headerStr, 'id')===false || strpos($headerStr, 'name')===false || strpos($headerStr, 'parent_id')===false){
				$ForText=new CAdminMessage(GetMessage("SKWB24_SSHOP_UPLOAD_FILE_WRONG_FORMAT"));
				$text=$ForText->Show();
				CFile::Delete($_REQUEST['ID']);
			}else{
				$fileValidate=true;
				CFile::UpdateDesc(intval($_REQUEST['ID']), 'rubric');
				$res = CFile::GetList(array(""), array("MODULE_ID"=>htmlspecialchars($this->module_id)));
				while($res_arr = $res->GetNext()){
					if($res_arr['ID']!=$_REQUEST['ID'] && $res_arr['DESCRIPTION']=='rubric'){
						CFile::Delete($res_arr["ID"]);
					}
				}
				$this->updateUploadArr();
				$text=$this->sectionFile();
			}
		}
		return array('status'=>$fileValidate, 'text'=>$text);
	}
	
	public function uploadProduct(){
		$fileValidate=false;
		$text='';
		$cFile=file($_SERVER["DOCUMENT_ROOT"].CFile::GetPath($_REQUEST['ID']));
		if(count($cFile)>0){
			$headerStr=$cFile[0];
			if(strpos($headerStr, 'photo')===false || strpos($headerStr, 'section')===false){
				$ForText=new CAdminMessage(GetMessage("SKWB24_SSHOP_UPLOAD_FILE_WRONG_FORMAT"));
				$text=$ForText->Show();
				CFile::Delete($_REQUEST['ID']);
			}else{
				$fileValidate=true;
				CFile::UpdateDesc(intval($_REQUEST['ID']), 'product');
				$res = CFile::GetList(array(""), array("MODULE_ID"=>htmlspecialchars($this->module_id)));
				while($res_arr = $res->GetNext()){
					if($res_arr['ID']!=$_REQUEST['ID'] && $res_arr['DESCRIPTION']=='product'){
						CFile::Delete($res_arr["ID"]);
					}
				}
				$this->updateUploadArr();
				$text=$this->productFile();
			}
		}
		return array('status'=>$fileValidate, 'text'=>$text);
	}

	public function updateSection(){
		$text='';
		//echo json_encode($_REQUEST['SECTIONS']);
		
		//deactivate all section
		$db_list = CIBlockSection::GetList(Array($by=>$order), array('IBLOCK_ID'=>$this->currentIBlock), array('ID', 'XML_ID', 'UF_*'));
		$bs = new CIBlockSection;
		$arFields = Array("ACTIVE" => 'N');
		$currentSectionArr=array();
		while($ar_result = $db_list->GetNext()){
			$bs->Update($ar_result['ID'], $arFields);
			$currentSectionArr[]=$ar_result;
		}
		if(count($_REQUEST['SECTIONS']['active'])>0){
			$currentCountMarginField=$this->getMarginField();
			$tmpCurrentSectionsArr=array();
			foreach($currentSectionArr as $nextSection){
				if(in_array($nextSection['XML_ID'], $_REQUEST['SECTIONS']['active'])){
					$arFields = Array("ACTIVE" => 'Y', $currentCountMarginField=>$_REQUEST['SECTIONS']['margin'][$nextSection['ID']]);
					$bs->Update($nextSection['ID'], $arFields);
				}
				$tmpCurrentSectionsArr[]=$nextSection['XML_ID'];
			}
			foreach($_REQUEST['SECTIONS']['active'] as $nextSection){
				if(!in_array($nextSection, $tmpCurrentSectionsArr)){
					$tmpName=$_REQUEST['SECTIONS']['name'][$nextSection];
					if(LANG_CHARSET=='windows-1251'){
						$tmpName=iconv('utf-8','windows-1251//TRANSLIT', $_REQUEST['SECTIONS']['name'][$nextSection]);
					}
					$arFields = Array("ACTIVE" => 'Y', 'XML_ID'=>$nextSection, 'IBLOCK_ID'=>$this->currentIBlock, $currentCountMarginField=>$_REQUEST['SECTIONS']['margin'][$nextSection], 'NAME'=>$tmpName);
					$arFields['CODE']=\Cutil::translit($tmpName, "ru", array());
					if($_REQUEST['SECTIONS']['tree'][$nextSection]>0){
						$arFields['IBLOCK_SECTION_ID']=$this->getSectionByXML_ID($_REQUEST['SECTIONS']['tree'][$nextSection]);
					}
					$bs->Add($arFields);
				}else{
					$arFields = Array("ACTIVE" => 'Y', $currentCountMarginField=>$_REQUEST['SECTIONS']['margin'][$nextSection]);
					$currentId=$this->getSectionByXML_ID($nextSection);
					$bs->Update($currentId, $arFields);
				}
			}
			$ForText=new CAdminMessage(array("MESSAGE"=>GetMessage("SKWB24_SSHOP_UPDATE_ALL_SECTIONS"), 'TYPE'=>'OK'));
		}else{
			$ForText=new CAdminMessage(GetMessage("SKWB24_SSHOP_DEACTIVATE_ALL_SECTIONS"));
		}
		return $ForText->Show();
	}
	
	public function updateProduct(){
		global $USER;
		$status='error';
		$currentMarginField=$this->getMarginField();
		//all active sections
		$db_list = CIBlockSection::GetList(Array($by=>$order), array('IBLOCK_ID'=>$this->currentIBlock, 'ACTIVE'=>'Y'), false, array('ID', 'XML_ID', $currentMarginField));
		$currentSectionArr=array();
		$currentSectionActiveList=array();
		while($ar_result = $db_list->GetNext()){
			$currentSectionArr[$ar_result['XML_ID']]=$ar_result;
			$currentSectionActiveList[]=$ar_result['XML_ID'];
		}
		if(count($currentSectionActiveList)>0){
			if($_REQUEST['STEP']==1){
				$this->checkProp(array(
					'MANUFACTURER'=>GetMessage('SKWB24_SSHOP_PROPS_MANUFACTURER'),//производитель
					'SIZE'=>GetMessage('SKWB24_SSHOP_PROPS_SIZE'),//размер
					'COLOR'=>GetMessage('SKWB24_SSHOP_PROPS_COLOR'),//цвет
					'MATERIAL'=>GetMessage('SKWB24_SSHOP_PROPS_MATERIAL'),//материал
					'COUNTRY'=>GetMessage('SKWB24_SSHOP_PROPS_COUNTRY'),//страна
					'PACKING'=>GetMessage('SKWB24_SSHOP_PROPS_PACKING'),//упаковка
					'ARTICLE'=>GetMessage('SKWB24_SSHOP_PROPS_ARTICLE')//артикул
				));
				
				//if deactivate products
				$deactiveStatus=COption::GetOptionString($this->module_id, 'deactive_products');
				if($deactiveStatus=='Y'){
					$el = new CIBlockElement;
					$arLoadProductArray = Array("ACTIVE"=>"N");
					$res = CIBlockElement::GetList(Array(), array('IBLOCK_ID'=>$this->currentIBlock, 'ACTIVE'=>'Y'), false, false, array('ID'));
					while($ob = $res->GetNext()){
						$el->Update($ob['ID'], $arLoadProductArray);
					}
				}
				
			}
			$cFile=file($_SERVER["DOCUMENT_ROOT"].CFile::GetPath($this->uploadArr['product']['ID']));
			$minPrice=COption::GetOptionString($this->module_id, 'min_price');
			if(!empty($cFile) && count($cFile)>1){
				if($_REQUEST['STEP']!='end'){
					$countRow=count($cFile)-1;
					$startRow=$_REQUEST['STEP'];
					$endRow=$startRow+5;
					$status=$endRow;
					if($endRow>$countRow){
						$endRow=$countRow;
						$status='end_upload';
					}
					
					$elArr=array();
					$forExistingEl=array();
					for($i=$startRow; $i<=$endRow; $i++){
						if(LANG_CHARSET!='windows-1251' && preg_match('#.#u', $cFile[$i])!=1){
							$cFile[$i]=iconv('windows-1251//TRANSLIT', 'utf-8', $cFile[$i]);
						}elseif(LANG_CHARSET=='windows-1251' && preg_match('#.#u', $cFile[$i])==1){
							$cFile[$i]=iconv('utf-8', 'windows-1251//TRANSLIT', $cFile[$i]);
						}
						$tmpElArr=explode(';', $cFile[$i]);
						foreach($tmpElArr as $key=>&$nextEl){
							$nextEl=trim($nextEl, '"\'');
							if($key==2){
								$forExistingEl[]=$nextEl;
							}
						}
						$elArr[]=$tmpElArr;
						
					}
					if(count($elArr)>0){
						$el = new CIBlockElement;
						$existingEl=array();
						$res = CIBlockElement::GetList(Array(), array('IBLOCK_ID'=>$this->currentIBlock, 'XML_ID'=>$forExistingEl), false, array('nTopCount'=>10), array('ID', 'XML_ID'));
						while($ob = $res->GetNext()){
							$existingEl[$ob['XML_ID']]=$ob['ID'];
						}
						
						foreach($elArr as $nextEl){
							$cName=$nextEl[0];
							if($nextEl[1]!=$nextEl[0] && strlen($nextEl[1])<9){
								$cName=str_ireplace(array($nextEl[1], ' / '),'', $nextEl[0]);
							}
							if((
								($minPrice>0 && $nextEl[9]>$minPrice) || $minPrice==0)
								&& !empty($currentSectionArr[$nextEl[12]]['ID'])
							){
								$arLoadProductArray = Array(
									"MODIFIED_BY"    => $USER->GetID(),
									"IBLOCK_ID"      => $this->currentIBlock,
									"IBLOCK_SECTION_ID"      => $currentSectionArr[$nextEl[12]]['ID'],
									"PROPERTY_VALUES"=> array(
										'MANUFACTURER'=>$nextEl[7],
										'SIZE'=>$nextEl[14],
										'COLOR'=>$nextEl[15],
										'MATERIAL'=>$nextEl[16],
										'COUNTRY'=>$nextEl[17],
										'PACKING'=>$nextEl[18],
										'ARTICLE'=>$nextEl[1]
									),
									"NAME"           => $cName,
									"CODE"      => Cutil::translit($cName,"ru"),
									"ACTIVE"         => "Y",
									"PREVIEW_TEXT"   => $nextEl[8],
									"PREVIEW_TEXT_TYPE"=>"html",
									"XML_ID"   => $nextEl[2]
								);
								if(empty($existingEl[$nextEl[2]])){
									$arLoadProductArray["DETAIL_PICTURE"]= CFile::MakeFileArray($nextEl[10]);
									$PRODUCT_ID = $el->Add($arLoadProductArray, false, true, true);
									CCatalogProduct::Add(array('ID'=>$PRODUCT_ID));
								}else{
									$PRODUCT_ID=$existingEl[$nextEl[2]];
									$el->Update($PRODUCT_ID, $arLoadProductArray);
								}
								CCatalogProduct::Update($PRODUCT_ID, array('PURCHASING_PRICE'=> floatval($nextEl[3]), 'QUANTITY'=>intval($nextEl[13])));
								$currentPrice=(!empty($currentSectionArr[$nextEl[12]][$currentMarginField]))?round($nextEl[9]*$currentSectionArr[$nextEl[12]][$currentMarginField]/100)+$nextEl[9]:floatval($nextEl[9]);
								CPrice::SetBasePrice($PRODUCT_ID, $currentPrice, 'RUB');
							}
						}
						
						$ForText=new CAdminMessage(array(
							"MESSAGE"=>GetMessage("SKWB24_SSHOP_PRODUCT_TITLE"),
							"DETAILS"=> "#PROGRESS_BAR#",
							"HTML"=>true,
							"TYPE"=>"PROGRESS",
							"PROGRESS_TOTAL" => $countRow,
							"PROGRESS_VALUE" => $endRow
						));
					}
				}else{
					//deactive sections without active products
					$deactiveSectionStatus=COption::GetOptionString($this->module_id, 'deactive_sections');
					if($deactiveSectionStatus=='Y'){
						$bs = new CIBlockSection;
						$arFields = Array("ACTIVE" => 'N');
						foreach($currentSectionActiveList as $nextSection){
							$res = CIBlockElement::GetList(Array(), array('IBLOCK_ID'=>$this->currentIBlock, 'IBLOCK_SECTION_ID'=>$nextSection, 'ACTIVE'=>'Y'), array(), false, array('ID'));
							if($res==0){
								$bs->Update($nextSection, $arFields);
							}
						}
					}
					$status='end';
					$ForText=new CAdminMessage(array("MESSAGE"=>GetMessage("SKWB24_SSHOP_UPLOAD_END"), 'TYPE'=>'OK'));
				}
			}else{
				$ForText=new CAdminMessage(GetMessage("SKWB24_SSHOP_ERROR_OPEN_PRODUCT_FILE"));
			}
		}else{
			$ForText=new CAdminMessage(GetMessage("SKWB24_SSHOP_DEACTIVATE_ALL_SECTIONS"));
		}
		return array('status'=>$status, 'text'=>$ForText->Show());
	}
}
?>