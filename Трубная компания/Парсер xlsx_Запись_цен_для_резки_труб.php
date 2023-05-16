<?php
set_time_limit(0);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php" ) ;
use Bitrix\Main\Loader;

use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

Loader::includeModule('pai.phpoffice');
Loader::IncludeModule("iblock");
Loader::includeModule("catalog");
COption::SetOptionString("catalog", "DEFAULT_SKIP_SOURCE_CHECK", "Y");


$count_search = 0;

//------------- Проход по инфоблоку "Цены резки" и запись данных в массив -------------------

$arRezka = array();
$arSelect = Array("IBLOCK_ID", "ID", "NAME", "PROPERTY_*");
$res = CIBlockElement::GetList(Array("ID" => "ASC"), Array("IBLOCK_ID" => 32), false, false, $arSelect);

while($ob = $res->GetNextElement()) {
    $arFields = $ob->GetFields();
    $arProps = $ob->GetProperties();

    array_push($arRezka, [
        "ID"=>$arFields["ID"],
        "REAL_NAME" => $arFields['NAME'],
    ]);
}

//------------- Проход по xlsx файлу и запись данных в массив -------------------

$sFile = $_SERVER["DOCUMENT_ROOT"].'/cutting.xlsx';
$oReader = new Xlsx();

$oSpreadsheet = $oReader->load($sFile);
$oCells = $oSpreadsheet->getActiveSheet()->getCellCollection();

$ar_xlsx = array();
$xlsx_name = "";
$cut_name = "";

for ($iRow = 3; $iRow <= $oCells->getHighestRow(); $iRow++)
{
    for ($iCol = 'E'; $iCol <= 'F'; $iCol++)
    {
        $oCell = $oCells->get($iCol.$iRow);
        if($oCell) {
            $cut_type = explode(",", $oCell->getValue());

            if ($iCol == 'E') {
                $xlsx_name = $oCell->getValue();

                if($cut_type[0]=="Резка"){
                    $cut_type[0] = "Резка кругом";
                }

                $cut_name = str_replace("Резка, ", "", $oCell->getValue());
                $cut_name = str_replace("Резка плазмой, ", "", $cut_name);
            }
            else {
                array_push($ar_xlsx, [
                    "NAME" => $cut_name,
                    "REAL_NAME" => $xlsx_name,
                    "PRICE" => $oCell->getValue(),
                    "CUT_TYPE" => $cut_type[0],
                ]);
            }
        }
    }
}


//---------- Проход по каталогу товаров ---------------------

$el = new CIBlockElement;

$db1=CIBlockElement::GetList( Array("ID"=>"ASC"), Array("IBLOCK_ID"=>26, "ACTIVE_DATE"=>"Y"), false,  false,  $arSelect);
while($ar1=$db1->GetNextElement()) {

    $count_search++;

    $arFields2 = $ar1->GetFields();


//---------- Добавление(обновление) новой цены резки ---------------------

    foreach ($ar_xlsx as $k_file => $v_file) {
        $pos = strpos(trim($arFields2['NAME']), $v_file["NAME"]);

        if ($pos !== false) {
            $flag = 0;
            $find_rezka_id = 0;
            foreach ($arRezka as $k => $v) {
                if ($v_file["REAL_NAME"] == $v["REAL_NAME"]) {
                    $finded_rezka_id = $v["ID"];
                    $flag = 1;
                    break;
                }
            }

//---------- Добавление новой цены резки ---------------------

            if ($flag == 0) {

                $PROP = array();
                $PROP["GOODS"] = $v_file["NAME"];
                $PROP["PRODUCT"] = $arFields2['ID'];
                $PROP["CUT_TYPE"] = $v_file["CUT_TYPE"];
                $PROP["CUT_PRICE"] = $v_file["PRICE"];

                $arLoadProductArray = Array(
                    "MODIFIED_BY" => $USER->GetID(), // элемент изменен текущим пользователем
                    "IBLOCK_SECTION_ID" => false,          // элемент лежит в корне раздела
                    "IBLOCK_ID" => 32,
                    "PROPERTY_VALUES" => $PROP,
                    "NAME" => $v_file["REAL_NAME"],
                    "ACTIVE" => "Y",            // активен
                    "PREVIEW_TEXT" => "",
                    "DETAIL_TEXT" => "",
                    "DETAIL_PICTURE" => "",
                    "PREVIEW_PICTURE" => "",
                );

                if ($ADD_PRODUCT_ID = $el->Add($arLoadProductArray)) {
                    array_push($arRezka, [
                        "ID"=> $ADD_PRODUCT_ID,
                        "REAL_NAME" => $v_file["REAL_NAME"],
                    ]);
                } else echo "Error: " . $el->LAST_ERROR;
            }
            else {


//---------- Обновление цены резки ---------------------

                CIBlockElement::SetPropertyValuesEx($finded_rezka_id, 32, array(
                    "CUT_PRICE" => $v_file["PRICE"],
                    "GOODS" => $v_file["NAME"],
                    "PRODUCT" => $arFields2["ID"],
                    "CUT_TYPE" => $v_file["CUT_TYPE"]
                ));

            }

            CIBlock::clearIblockTagCache(32);
            break;
        }
    }
}
echo "Количество совпадений: ".$count_search;