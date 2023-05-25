<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (check_bitrix_sessid() && $_SERVER['REQUEST_METHOD'] == "POST")
{
    $arMailFields = Array();
    $arMailFields["REQUEST_HEADER"] = trim ($_REQUEST["request_header"]);
    $arMailFields["REQUEST_CATEGORY"] = $_REQUEST["request_category"];
    $arMailFields["REQUEST_TYPE"] = $_REQUEST["request_type"];
    $arMailFields["REQUEST_STORAGE"] = $_REQUEST["request_storage"];
    $arMailFields["REQUEST_COMPOSITION"] = $_REQUEST["request_composition"];
    $arMailFields["REQUEST_MESSAGE"] = trim ($_REQUEST["request_message"]);

    $files = array();
    foreach ($_FILES as $file){
        if (!empty($file['tmp_name'])) {
            $files[]=CFile::SaveFile($file,'form');
        }
    }

    CEvent::Send("TEST_REQUEST_MESSAGE_FORM", SITE_ID, $arMailFields, "N", "", $files);

    foreach ($files as $i){
        CFile::Delete($i);
    }
}

$this->IncludeComponentTemplate();