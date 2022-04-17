<?php
// Без изысков. Надо было сделать быстро, поэтому просто. Надо запустить скрипт несколько раз для построения дерева каталога.

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

\Bitrix\Main\Loader::includeModule('iblock');
\Bitrix\Main\Loader::includeModule("catalog");

function Bild_struct_muz(){
    $crm = new Structer_muz();
    $crm->Struct_muz();

    return "Bild_struct_muz();";
}

class Structer_muz {

    const MAX_EXECUTION_TIME = 400;
    const AGENT_TIME_INTERVAL = 2; //минут
    const PATH_TO_DIRECTORY = '/var/www/1111.ru/data/www/1111.ru/bitrix/php_interface/include/updater_catalog/';

    protected static function addOneMoreStepAgent()
    {
        CAgent::AddAgent(
            get_called_class()."::Struct_muz();", // имя функции
            "",                          // идентификатор модуля
            "Y",                                  // агент критичен к кол-ву запусков
            86400,                                // интервал запуска - 1 сутки
            date("d.m.Y H:i:s",strtotime("+".self::AGENT_TIME_INTERVAL." minute")),// дата первой проверки на запуск
            "Y",                                  // агент активен
            date("d.m.Y H:i:s",strtotime("+".self::AGENT_TIME_INTERVAL." minute")),// дата первого запуска
            30);

        return false;

    }

    public static function Struct_muz()
    {
        $startAgentTimestamp = time();

        ini_set('memory_limit','-1');
        set_time_limit(2400);
        ini_set('session.gc_maxlifetime', 2400);

        $obj_offers=simplexml_load_file(self::PATH_TO_DIRECTORY.'dynatoneProductsWithOptions.xml');
        $offers_price=$obj_offers->shop;

        if (!file_exists(self::PATH_TO_DIRECTORY.'struct_muz_iter.txt')) {
            $iterator1 = 0;
        }
        else{
            $iterator1=intval(file_get_contents(self::PATH_TO_DIRECTORY.'struct_muz_iter.txt'));
        }

        $iterator1_max = count($offers_price->categories->category);

        while ($iterator1 < $iterator1_max) {
            $category1 = $offers_price->categories->category[$iterator1];

                $arFilter = Array('IBLOCK_ID' => 2, "UF_CAT_MUZ_ID" => strval($category1['id']));
                $db_list = CIBlockSection::GetList(Array("SORT" => "ASC"), $arFilter, true);

                if (!$ar_result = $db_list->GetNext()) {

                    if ($category1['parentId']) {
                        $arFilter1 = Array('IBLOCK_ID' => 2, "UF_CAT_MUZ_ID" => strval($category1['parentId']));
                        $db_list1 = CIBlockSection::GetList(Array("SORT" => "ASC"), $arFilter1, true);
                        if ($ar_result1 = $db_list1->GetNext()) {

                            $converter = array(
                                'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd',
                                'е' => 'e', 'ё' => 'e', 'ж' => 'zh', 'з' => 'z', 'и' => 'i',
                                'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n',
                                'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't',
                                'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c', 'ч' => 'ch',
                                'ш' => 'sh', 'щ' => 'sch', 'ь' => '', 'ы' => 'y', 'ъ' => '',
                                'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
                            );

                            $value = mb_strtolower(strval($category1));
                            $value = strtr($value, $converter);
                            $value = mb_ereg_replace('[^-0-9a-z]', '_', $value);
                            $value = mb_ereg_replace('[-]+', '_', $value);
                            $value = trim($value, '_');

                            $bs = new CIBlockSection;
                            $arFields = Array(
                                "ACTIVE" => 'Y',
                                "IBLOCK_SECTION_ID" => $ar_result1['ID'],
                                "IBLOCK_ID" => "2",
                                "NAME" => strval($category1),
                                "CODE" => $value ."_muz_". strval($category1['id']),
                                "SORT" => 500,
                                "DESCRIPTION_TYPE" => "text",
                                "UF_CAT_MUZ_ID" => $category1['id'],
                            );

                            $idd = $bs->Add($arFields);

                            if ($idd <= 0) {
                                AddMessage2Log("$bs->LAST_ERROR;" . date("H:i:s"));
                            }
                        }
                    }
                }

            if ((time() - $startAgentTimestamp) > self::MAX_EXECUTION_TIME) {
                file_put_contents(self::PATH_TO_DIRECTORY . 'struct_muz_iter.txt', $iterator1);

                //Удаление старого агента
                $obRes = CAgent::GetList(Array("ID" => "DESC"), array("NAME" => "%Struct_muz%"));
                while ($obj = $obRes->GetNext()) {
                    if ($obj["NAME"] == get_called_class() . "::Struct_muz();") {
                        CAgent::RemoveAgent($obj["NAME"], "");
                    }
                }

                //Добавляем новый агент через n минут
                self::addOneMoreStepAgent();

                return false;
                break;
            }

            $iterator1++;
        }

        $obRes = CAgent::GetList(Array("ID" => "DESC"), array("NAME"=>"%Struct_muz%"));
        while ($obj = $obRes->GetNext())
        {
            if ($obj["NAME"] == get_called_class()."::Struct_muz();"){
                CAgent::RemoveAgent($obj["NAME"], "");
            }
        }
        return false;
    }

}
