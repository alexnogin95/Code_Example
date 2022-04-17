<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();


\Bitrix\Main\Loader::includeModule('iblock');
\Bitrix\Main\Loader::includeModule("catalog");

function Update_Music()
{
    $crm = new Upd_music();
    $crm->Upd_mus_cat();
    return "Update_Music();";
}

class Upd_music
{

    const MAX_EXECUTION_TIME = 400;
    const AGENT_TIME_INTERVAL = 2; //минут
    const PATH_TO_DIRECTORY = '/var/www/111.ru/data/www/111.ru/bitrix/php_interface/include/updater_catalog/';

// категории
    public static function getSectionList($filter, $select)
    {
        $dbSection = CIBlockSection::GetList(
            Array(
                'LEFT_MARGIN' => 'ASC',
            ),
            is_array($filter) ? $filter : Array(),
            false,
            array_merge(
                Array(
                    'ID',
                    'IBLOCK_SECTION_ID'
                ),
                is_array($select) ? $select : Array()
            )
        );
        $i = 0;
        while ($arSection = $dbSection->GetNext(true, false)) {

            $SID = $arSection['ID'];
            $PSID = (int)$arSection['IBLOCK_SECTION_ID'];

            $arLincs[$PSID]['CHILDS'][$SID] = $arSection;
            $arLincs[$SID] = &$arLincs[$PSID]['CHILDS'][$SID];

            $arLincs2[$i] = $arSection;
            $i++;
        }
        return $arLincs2;
    }


// транслит для символьных кодов
    public static function translit_sef($value)
    {
        $converter = array(
            'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd',
            'е' => 'e', 'ё' => 'e', 'ж' => 'zh', 'з' => 'z', 'и' => 'i',
            'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n',
            'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't',
            'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c', 'ч' => 'ch',
            'ш' => 'sh', 'щ' => 'sch', 'ь' => '', 'ы' => 'y', 'ъ' => '',
            'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
        );

        $value = mb_strtolower($value);
        $value = strtr($value, $converter);
        $value = mb_ereg_replace('[^-0-9a-z]', '-', $value);
        $value = mb_ereg_replace('[-]+', '-', $value);
        $value = trim($value, '_');

        return $value;
    }


    protected static function addOneMoreStepAgent()
    {
        CAgent::AddAgent(
            get_called_class() . "::Upd_mus_cat();", // имя функции
            "",                          // идентификатор модуля
            "Y",                                  // агент критичен к кол-ву запусков
            86400,                                // интервал запуска - 1 сутки
            date("d.m.Y H:i:s", strtotime("+" . self::AGENT_TIME_INTERVAL . " minute")),// дата первой проверки на запуск
            "Y",                                  // агент активен
            date("d.m.Y H:i:s", strtotime("+" . self::AGENT_TIME_INTERVAL . " minute")),// дата первого запуска
            30);

        return false;

    }

    protected static function RemoveStepAgent()
    {
        CAgent::RemoveAgent(get_called_class() . "::Upd_mus_cat();", "");
        return false;

    }


    public static function Upd_mus_cat()
    {

        $startAgentTimestamp = time();

        $rsParentSection = CIBlockSection::GetList(
            Array('name' => 'asc'),
            Array('IBLOCK_ID' => 2, 'ACTIVE' => 'Y', 'SECTION_ID' => 7175)
        );
        $arr_muz_sects = array();
        while ($arParentSection = $rsParentSection->GetNext()) {
            $arFilter = array('IBLOCK_ID' => $arParentSection['IBLOCK_ID'], '>LEFT_MARGIN' => $arParentSection['LEFT_MARGIN'], '<RIGHT_MARGIN' => $arParentSection['RIGHT_MARGIN'], '>DEPTH_LEVEL' => $arParentSection['DEPTH_LEVEL']); // выберет потомков без учета активности
            $rsSect = CIBlockSection::GetList(array('left_margin' => 'asc'), $arFilter, false, Array('UF_CAT_MUZ_ID'));
            while ($arSect = $rsSect->GetNext()) {
                $arr_muz_sects[] = $arSect['ID'];
            }
        }

        $arSections = self::getSectionList(
            Array(
                'IBLOCK_ID' => 2, 'SECTION_ID' => $arr_muz_sects
            ),
            Array(
                'NAME',
                'IBLOCK_SECTION_ID',
                'UF_CAT_MUZ_ID'
            )
        );
        ini_set('memory_limit', '-1');
// объект с ценами товаров
        if (!file_exists(self::PATH_TO_DIRECTORY . 'iterator_muz.txt')) {
            $obj_offers = simplexml_load_file(self::PATH_TO_DIRECTORY . 'dynatoneProductsWithOptions.xml');
            $rate = $obj_offers->shop->currencies->currency[0]->attributes()->rate;
            file_put_contents(self::PATH_TO_DIRECTORY . 'rate_music.txt', $rate);
            $offers_price = $obj_offers->shop;
            $iterator = 0;
            $max_iterator = count($offers_price->offers->offer);
            $iterator_now = $max_iterator;

            $res = CIBlockElement::GetList(Array(), Array("IBLOCK_ID" => 2, 'SECTION_ID' => $arr_muz_sects), false, Array(), Array("ID", "PROPERTY_UF_CAT_ID_ELEM"));
            while ($ob = $res->GetNextElement()) {
                $arFields = $ob->GetFields();
                $t[$arFields['ID']] = intval($arFields['PROPERTY_UF_CAT_ID_ELEM_VALUE']);
            }
        } else {
            $obj_offers = simplexml_load_file(self::PATH_TO_DIRECTORY . 'dynatoneProductsWithOptions.xml');
            $offers_price = $obj_offers->shop;
            $rate = file_get_contents(self::PATH_TO_DIRECTORY . 'rate_music.txt');
            $iterator = intval(file_get_contents(self::PATH_TO_DIRECTORY . 'iterator_muz.txt'));
            $max_iterator = count($offers_price->offers->offer);
            $iterator_now = $max_iterator;

            $res = CIBlockElement::GetList(Array(), Array("IBLOCK_ID" => 2, 'SECTION_ID' => $arr_muz_sects), false, Array(), Array("ID", "PROPERTY_UF_CAT_ID_ELEM"));
            while ($ob = $res->GetNextElement()) {
                $arFields = $ob->GetFields();
                $t[$arFields['ID']] = intval($arFields['PROPERTY_UF_CAT_ID_ELEM_VALUE']);
            }
        }


        while ($iterator < $iterator_now) {

            if ($iterator < $max_iterator) {

                $offer = $offers_price->offers->offer[$iterator];

                    $iterator_prop = 0;
                    $max_iterator_prop = count($offer->param);
                    $arr_proper = array();

                    while ($iterator_prop < $max_iterator_prop) {
                        $param_offer = $offer->param[$iterator_prop];
                        $param_name = strval($param_offer->attributes()->name);
                        $IBLOCK_ID = 2;
                        $properties = CIBlockProperty::GetList(Array("sort" => "asc", "name" => "asc"), Array("ACTIVE" => "Y", "IBLOCK_ID" => $IBLOCK_ID, "NAME" => $param_name));
                        while ($prop_fields = $properties->GetNext()) {
                            if (!in_array($prop_fields["NAME"], $arr_proper)) {
                                $arr_proper[] = $prop_fields["NAME"];
                            }
                        }

                        $iterator_prop++;
                    }

                    $iterator_prop = 0;
                    $arr_name_prop = array();
                    while ($iterator_prop < $max_iterator_prop) {
                        $param_offer = $offer->param[$iterator_prop];
                        $param_name = strval($param_offer->attributes()->name);
                        $arr_name_prop[] = $param_name;
                        if (!in_array($param_name, $arr_proper)) {

                            $arFields = Array(
                                "NAME" => $param_name,
                                "ACTIVE" => "Y",
                                "SORT" => "500",
                                "CODE" => self::translit_sef($param_name),
                                "PROPERTY_TYPE" => "S",
                                "SMART_FILTER" => "Y",
                                "IBLOCK_ID" => 2,
                            );

                            $iblockproperty = new CIBlockProperty;
                            if (!$PropertyID = $iblockproperty->Add($arFields)) {
                                AddMessage2Log(strval($offer->typePrefix) . " error " . print_r($iblockproperty->LAST_ERROR, true));
                            }

                        } else {

                            $PROPERTY_CODE = self::translit_sef($param_name);  // код свойства
                            $PROPERTY_VALUE = strval($param_offer);  // значение свойства

                            foreach ($t as $key => $value) {
                                if ($value == $offer->attributes()->id) {
                                    $id_element_ib = $key;
                                    break;
                                }
                            }

                        // Установим новое значение для этого свойства данного элемента
                            CIBlockElement::SetPropertyValuesEx($id_element_ib, false, array($PROPERTY_CODE => $PROPERTY_VALUE));
                            CIBlock::clearIblockTagCache(2);
                        }

                        $iterator_prop++;
                    }

                    $elem_id = "";

                    if (!in_array($offer->attributes()->id, $t)) {
                        // нет в базе(create)
                        $PROP["10"] = strval($offer->vendor);
                        $PROP["5"] = strval($offer->vendor);
                        $PROP["25"] = strval($offer->attributes()->id);

                        $stac_name = str_replace("<![CDATA[", "", strval($offer->typePrefix) . " " . strval($offer->vendor) . " " . strval($offer->model));
                        $stac_name = str_replace("]]", "", $stac_name);

                        $description_el = str_replace("<![CDATA[", "", strval($offer->description));
                        $description_el = str_replace("]]", "", $description_el);

                        $name_elem_tr = self::translit_sef($stac_name . " n" . strval($offer->attributes()->id));
                        $Picture = strval($offer->picture);

                        $saveTo = self::PATH_TO_DIRECTORY . strval($offer->attributes()->id) . ".jpg";
                        $real_path = $saveTo;

                        $fp = fopen($saveTo, 'w+');

                        if ($fp === false) {
                            AddMessage2Log('Could not open: ' . $saveTo);
                        }

                        $ch = curl_init($Picture);
                        curl_setopt($ch, CURLOPT_FILE, $fp);
                        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
                        curl_exec($ch);
                        if (curl_errno($ch)) {
                            $pic = strval($offer->attributes()->id);
                            unlink(self::PATH_TO_DIRECTORY . $pic . '.jpg');
                            $iterator++;
                            continue;
                        }

                        curl_close($ch);
                        fclose($fp);

                        $offer_price = $offer->price;

                        $flag = false;

                        foreach ($arSections as $kay => $value) {
                            if (strval($value["UF_CAT_MUZ_ID"]) == strval($offer->categoryId) && (in_array($value["ID"], $arr_muz_sects))) {
                                $cat_id = $value["ID"];
                                $flag = true;
                                break;
                            }
                        }

                        // Не нашёл у раздела родительский на сайте. Пропускаем этот товар.
                        if ($flag === false) {
                            $iterator++;
                            $pic = strval($offer->attributes()->id);
                            unlink(self::PATH_TO_DIRECTORY . $pic . '.jpg');
                            continue;
                        }

                        $el = new CIBlockElement;

                            $fields = array(
                                'IBLOCK_ID' => 2,
                                'IBLOCK_SECTION_ID' => strval($cat_id),
                                'NAME' => $stac_name,
                                'ACTIVE' => "Y",
                                'DETAIL_TEXT' => $description_el,
                                "DETAIL_TEXT_TYPE" => 'html',
                                'CREATED_BY' => '1',
                                'CODE' => $name_elem_tr,
                                'PROPERTY_VALUES' => $PROP,
                                'PREVIEW_PICTURE' => CFile::MakeFileArray($real_path),
                                'DETAIL_PICTURE' => CFile::MakeFileArray($real_path)
                            );

                        if ($PRODUCT_ID = $el->Add($fields)) {
                            $productFileds = array(
                                "ID" => $PRODUCT_ID, //ID добавленного элемента инфоблока
                                "VAT_ID" => 1, //выставляем тип ндс (задается в админке)
                                "VAT_INCLUDED" => "Y", //НДС входит в стоимость
                                "TYPE " => \Bitrix\Catalog\ProductTable::TYPE_PRODUCT //Тип товара
                            );

                            if (CCatalogProduct::Add($productFileds)) {
                                $arFieldsPrice = Array(
                                    "PRODUCT_ID" => $PRODUCT_ID,                 //ID добавленного товара
                                    // "QUANTITY" => "10",
                                    "CATALOG_GROUP_ID" => 1,                        //ID типа цены
                                    "PRICE" => floatval($offer_price),                        //значение цены
                                    "CURRENCY" => "RUB"    // валюта
                                );


//////////////////////  Удаляем лишние цены  //////////////////////////////

                                $dbPrice1 = \Bitrix\Catalog\Model\Price::getList([
                                    "filter" => array(
                                        "PRODUCT_ID" => $PRODUCT_ID,
                                        "CATALOG_GROUP_ID" => 1
                                    )]);

                                $count_price = 0;
                                while ($arPrice = $dbPrice1->fetch()) {
                                    $count_price++;
                                }

                                if ($count_price > 1) {
                                    $del_all_price_Result = CPrice::DeleteByProduct($PRODUCT_ID);
                                }

//////////////////////  Добавляем/Обновляем цены  //////////////////////////////

                                $dbPrice = \Bitrix\Catalog\Model\Price::getList([
                                    "filter" => array(
                                        "PRODUCT_ID" => $PRODUCT_ID,
                                        "CATALOG_GROUP_ID" => 1
                                    )]);

                                if ($arPrice = $dbPrice->fetch()) {
                                    $result = \Bitrix\Catalog\Model\Price::update($arPrice["ID"], $arFieldsPrice);
                                } else {
                                    $result = \Bitrix\Catalog\Model\Price::add($arFieldsPrice);
                                }

////////////////////////////////////////////////////////////////////////////

                            }
                            $pic = strval($offer->attributes()->id);
                            unlink(self::PATH_TO_DIRECTORY . $pic . '.jpg');
                            $elem_id = $PRODUCT_ID;
                        } else {
                            $pic = strval($offer->attributes()->id);
                            unlink(self::PATH_TO_DIRECTORY . $pic . '.jpg');
                        }

                    } else {

                        foreach ($t as $key => $value) {
                            if ($value == $offer->attributes()->id) {
                                $id_element_ib = $key;
                                break;
                            }
                        }

                        if (isset($offer->picture) && strval($offer->picture) != ""){
                            $offer_price = $offer->price;
                        $offer_price = floatval($offer_price) * floatval($rate);
                        $arFieldsPrice = Array(
                            "PRODUCT_ID" => $id_element_ib,
                            "CATALOG_GROUP_ID" => 1,                        //ID типа цены
                            "PRICE" => $offer_price,                        //значение цены
                            "CURRENCY" => "RUB"    // валюта
                        );

//////////////////////  Удаляем лишние цены  //////////////////////////////

                        $dbPrice1 = \Bitrix\Catalog\Model\Price::getList([
                            "filter" => array(
                                "PRODUCT_ID" => $id_element_ib,
                                "CATALOG_GROUP_ID" => 1
                            )]);

                        $count_price = 0;
                        while ($arPrice = $dbPrice1->fetch()) {
                            $count_price++;
                        }

                        if ($count_price > 1) {
                            $del_all_price_Result = CPrice::DeleteByProduct($id_element_ib);
                        }

//////////////////////  Добавляем/Обновляем цены  //////////////////////////////
                        $dbPrice = \Bitrix\Catalog\Model\Price::getList([
                            "filter" => array(
                                "PRODUCT_ID" => $id_element_ib,
                                "CATALOG_GROUP_ID" => 1
                            )]);

                        if ($arPrice = $dbPrice->fetch()) {
                            $result = \Bitrix\Catalog\Model\Price::update($arPrice["ID"], $arFieldsPrice);
                        } else {
                            $result = \Bitrix\Catalog\Model\Price::add($arFieldsPrice);
                        }
////////////////////////////////////////////////////////////////////////////

                        $ELEMENT_ID = $id_element_ib;  // код элемента
                        $PROPERTY_CODE = "BRAND_REF";  // код свойства
                        $stac_brand = str_replace("<![CDATA[", "", strval($offer->vendor));
                        $stac_brand = str_replace("]]", "", $stac_brand);
                        $PROPERTY_VALUE = $stac_brand;  // значение свойства

                        CIBlockElement::SetPropertyValuesEx($ELEMENT_ID, false, array($PROPERTY_CODE => $PROPERTY_VALUE));
                        $elem_id = $ELEMENT_ID;
                        CIBlock::clearIblockTagCache(2);
                        }

                    }

                    if ($elem_id != "") {
                        $ell = new CIBlockElement;

                        $detail_text = "";

                        $arSelect = Array("ID", "IBLOCK_ID", "PROPERTY_*");
                        $arFilter = Array("IBLOCK_ID" => 2, "ID" => intval($elem_id), "ACTIVE" => "Y");
                        $res11 = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
                        while ($ob = $res11->GetNextElement()) {
                            $arProps = $ob->GetProperties();

                            foreach ($arProps as $key => $value) {
                                if ($value["~VALUE"] != "") {

                                    //$PROP[$key] = $value["~VALUE"];
                                    if ($value["NAME"] != "Бренд" && $value["NAME"] != "id каталог") {
                                        $detail_text = $detail_text . '
              <li class="specification__item">';
                                        $detail_text = $detail_text . '
                  <span class="specification__item--name">' . $value["NAME"] . '</span>
                  <span class="specification__item--value">' . $value["~VALUE"] . '</span>';

                                        $detail_text = $detail_text . '
              </li>';
                                    }
                                }
                            }

                        }

                        $arLoadProductArray1 = Array(
                            "MODIFIED_BY" => 1, // элемент изменен текущим пользователем
                            "DETAIL_TEXT_TYPE" => 'html',
                            "DETAIL_TEXT" => html_entity_decode($detail_text),
                        );
                        $res22 = $ell->Update($elem_id, $arLoadProductArray1);
                    }

                $iterator++;

            } else {

                unlink(self::PATH_TO_DIRECTORY . 'iterator_muz.txt');

                $obRes = CAgent::GetList(Array("ID" => "DESC"), array("NAME" => "%Upd_mus_cat%"));
                while ($obj = $obRes->GetNext()) {
                    if ($obj["NAME"] == get_called_class() . "::Upd_mus_cat();") {
                        CAgent::RemoveAgent($obj["NAME"], "");
                    }
                }
                CIBlock::clearIblockTagCache(2);
                return false;
            }

            if ((time() - $startAgentTimestamp) > self::MAX_EXECUTION_TIME) {
                file_put_contents(self::PATH_TO_DIRECTORY . 'iterator_muz.txt', $iterator);

                //Удаление старого агента
                $obRes = CAgent::GetList(Array("ID" => "DESC"), array("NAME" => "%Upd_mus_cat%"));
                while ($obj = $obRes->GetNext()) {
                    if ($obj["NAME"] == get_called_class() . "::Upd_mus_cat();") {
                        CAgent::RemoveAgent($obj["NAME"], "");
                    }
                }

                //Добавляем новый агент через n минут
                self::addOneMoreStepAgent();

                return false;
                break;
            }
        }
        unlink(self::PATH_TO_DIRECTORY . 'iterator_muz.txt');
        $obRes = CAgent::GetList(Array("ID" => "DESC"), array("NAME" => "%Upd_mus_cat%"));
        while ($obj = $obRes->GetNext()) {
            if ($obj["NAME"] == get_called_class() . "::Upd_mus_cat();") {
                CAgent::RemoveAgent($obj["NAME"], "");
            }
        }
        return false;
    }

}
