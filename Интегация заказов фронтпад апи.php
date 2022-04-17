<?php
use Bd\Deliverysushi;
\Bitrix\Main\Loader::includeModule('iblock');
\Bitrix\Main\Loader::includeModule("catalog");
\Bitrix\Main\Loader::includeModule('bd.deliverysushi');

$GLOBALS['CATALOG_SECTION_PIZZA'] = 19;
$headers = apache_request_headers();
if ($headers['Host'] == "hobbit72.ru") {
    $GLOBALS['CATALOG_SECTION_PIZZA'] = 36;
}

function TovarAgent(){
    $crm= new IntegrationFrontpad();

    $arr_catalog_ids = array(
        "catalog_iblock" => 3,
        "catalog_section_set" => 10,
        "catalog_section_pizza" => 19,
        "catalog_section_miny" => 25,
        "catalog_section_akcia" => 22,
        "catalog_section_zapech" => 28,
        "catalog_section_filadel" => 26,
        "catalog_section_tempur" => 27,
        "catalog_section_roll" => 24,
        "bd_props_1" => 11,
    );

    $crm->addProducts($arr_catalog_ids,1);
    return "TovarAgent();";
}

function TovarAgentTymen(){
    $crm= new IntegrationFrontpad();

    $arr_catalog_ids = array(
        "catalog_iblock" => 23,
        "catalog_section_set" => 29,
        "catalog_section_pizza" => 36,
        "catalog_section_miny" => 32,
        "catalog_section_akcia" => 37,
        "catalog_section_zapech" => 35,
        "catalog_section_filadel" => 33,
        "catalog_section_tempur" => 34,
        "catalog_section_roll" => 31,
        "bd_props_1" => 94,
    );

    $crm->addProducts($arr_catalog_ids,1);
    return "TovarAgentTymen();";
}

function Get_sertificates(){
    $crm= new IntegrationFrontpad();
    return $crm->get_Sertificate();
}

class IntegrationFrontpad {
    public $city;

    function __construct() {
        $this->city = "Курган";
        $headers = apache_request_headers();
        if ($headers['Host'] == "hobbit72.ru") {
            $this->city = "Тюмень";
        }
    }

    public function getkey()
    {
            $dbKey = \CIBlockElement::GetList(array("ID" => "ASC"), array("IBLOCK_ID" => 22, "NAME" => $this->city), false, false, array("IBLOCK_ID", "ID", "PROPERTY_APIKEY"));

        if($arKey=$dbKey->GetNext()){
            $api_key=$arKey["PROPERTY_APIKEY_VALUE"];
        }
        return $api_key;
    }

    public function curl($param, $method, $product = null, $product_kol = null, $addition = array()){
        $data = ""; $coltags = 0;

        $predzakaz_otmetka_id_api = 1010;
        $samovivoz_otmetka_id_api = 537;
        $headers = apache_request_headers();

        if ($headers['Host'] == "hobbit72.ru") {
            $predzakaz_otmetka_id_api = 1709;
            $samovivoz_otmetka_id_api = 1710;
        }

        foreach ($param as $key => $value) {

            $data .= "&".$key."=".$value;

            if ($key == "datetime") {
                $data .= "&tags[" . $coltags . "]=".$predzakaz_otmetka_id_api;
                $coltags++;
            }

        }

        if($_POST["ORDER"]["DELIVERY_TYPE"] == 2) {
            $data .= "&tags[" . $coltags . "]=".$samovivoz_otmetka_id_api;
        }

        if($product != null && $product_kol != null) {


            //--- Удалить из заказа промо товар --- ///

            foreach ($product as $key => $value)
            {
                if(isset($_SESSION['ID_PRODUCT_DISCOUNT_FRONTPAD']) && $_SESSION['ID_PRODUCT_DISCOUNT_FRONTPAD'] == $value){
                    unset($product[$key]);
                    unset($_SESSION['ID_PRODUCT_DISCOUNT_FRONTPAD']);
                }
            }

            $full = false;
            foreach ($product as $key => $value)
            {
                if ($value != "") {
                    $full = true;
                }
            }

            if($full != true){$product[0] = "00000001"; }

            // содержимое заказа
            foreach ($product as $key => $value)
            {
                if ($value != "") {
                    $value = str_replace(".", "", $value);
                    $data .= "&product[" . $key . "]=" . $value . "";
                    $data .= "&product_kol[" . $key . "]=" . $product_kol[$key] . "";
                    if (isset($product_mod[$key])) {
                        $data .= "&product_mod[" . $key . "]=" . $product_mod[$key] . "";
                    }
                }
            }

            if ($addition){
                $ii = $key;
                foreach ($addition as $k => $v)
                {   if (!isset($v["PRICE"]) || ($v["PRICE"] == "")) $v["PRICE"] = 0;
                    $data .= "&product[" . $ii . "]=" . $v["XML_ID"] . "";
                    $data .= "&product_kol[" . $ii . "]=" . $v["AMOUNT"] . "";
                    $data .= "&product_price[" . $ii . "]=" . $v["PRICE"] . "";
                    $ii++;
                }
            }

            $hook_status = array(3,12,4,10,11);

            foreach ($hook_status as $keyh => $valueh){
                $data .= "&hook_status[".$keyh."]=".$valueh."";
            }
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://app.frontpad.ru/api/index.php?".$method);
        curl_setopt($ch, CURLOPT_FAILONERROR, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $result = curl_exec($ch);

        curl_close($ch);
        return $result;
    }

    /*
    get_products
    */
    function addProducts($arr_catalog_ids,$it_add_product = 0){


        $obElement = new CIBlockElement();

        $param['secret'] = $this->getkey();

        $result = $this->curl($param, "get_products");
        $result = json_decode(explode("\r\n\r\n",$result)[1]);
        $allProduct = [];
        $artikuls=[];

        foreach($result->product_id as $key => $value){
            $allProduct[$key] = array('id' => $value);
            $artikuls[]=$value;
        }
        /*poisk po id*/

        $res = CIBlockElement::GetList(
            Array("ID"=>"ASC"),
            Array("IBLOCK_ID"=> $arr_catalog_ids["catalog_iblock"]),
            false,
            false,
            Array("IBLOCK_ID","ID", "XML_ID", "PROPERTY_BD_PROPS_1"));
        $productSite=[];
        $oldprops=[];
        while($arProduct= $res->GetNext()){

            if(!in_array($arProduct["XML_ID"],$artikuls)){
//				AddMessage2Log("товар не найден в выгрузке, удаляю ".$arProduct["ID"]);
//				CIBlockElement::Delete($arProduct["ID"]);
            }else{
                $productSite[$arProduct["XML_ID"]] = $arProduct["ID"];
                $oldprops[$arProduct["XML_ID"]] =$arProduct["PROPERTY_BD_PROPS_1_VALUE_ID"];
            }
        }
        foreach($result->name as $key => $value){
            if (strpos($value, ':') !== false) {
                $names[$key] = explode(":", $value);
            } else {
                $allProduct[$key]['name'] = $value;
            }
        }
        foreach($result->price as $key => $value){
            $allProduct[$key]['price'] = $value;
        }
        $namePizza = [];
        foreach($names as $key => $item){
            $items[$key] = $item[0];
            $props[$key]=trim($item[1]);

            if (isset($item[2])){
                $props[$key]=trim($item[1])." / ".trim($item[2]);
            }
        }
//        AddMessage2Log(print_r($props,true));
        $arrItemsTemp = [];
        foreach($items as $key => $name){
            $namePizza[$name][] = $key;
        }
        $arrPizza = [];
        foreach($namePizza as $key => $element){
            $arrPizza[$element[0]]['name'] = $key;
            for ($i = 0; $i < count($element); $i++){
                $arrPizza[$element[0]]['id'][] = $allProduct[$element[$i]]['id'];
                $arrPizza[$element[0]]['price'][] = $allProduct[$element[$i]]['price'];
                $arrPizza[$element[0]]['prop'][] =$props[$element[$i]];
            }
        }
        $allProduct = array_replace($allProduct, $arrPizza);
//        AddMessage2Log(print_r($allProduct,true));
        foreach ($allProduct as $key => $value){
            if (strpos($value["name"], "Бургер") === false){
                if(is_array($value["price"])){
                    $bdProp=[];
                    foreach($value["price"] as $k=>$val){
                        $bdProp[]=array(
                            "PROP"=>"Размер",
                            "VALUE" => $value["prop"][$k],
                            "PRICE" => $value["price"][$k],
                            "OLD_PRICE"=> "",
                            "WEIGHT"=>  $value['id'][$k],
                        );
                    }
                    $arFields= Array(
                        "IBLOCK_ID" => $arr_catalog_ids["catalog_iblock"],
                        "IBLOCK_SECTION_ID" => $arr_catalog_ids["catalog_section_pizza"],
                        "XML_ID" => $value['id'][0],
                        "NAME" => $value["name"],
                        "CODE" => $this->translit($value["name"]),
//					"ACTIVE" => "Y",
                        "PROPERTY_VALUES"=> Array(
                            $arr_catalog_ids['bd_props_1'] =>  array(
                                'n0' =>
                                    array (
                                        'VALUE' =>$bdProp
                                    )
                            ),
                            "PRICE"  => $value['price'][0],
                            "UNITS"=>"шт",
                        ),
                    );
                    $artukul=$value['id'][0];
                }else{

                    $arFields = Array(
                        "IBLOCK_ID" => $arr_catalog_ids["catalog_iblock"],
                        "XML_ID" => $value['id'],
                        "NAME" => $value["name"],
                        "CODE" => $this->translit($value["name"]),
//       	"ACTIVE" => "Y",
                        "PROPERTY_VALUES"=> Array(
                            "PRICE"  => $value['price'],
                            "UNITS"=>"шт"
                        ),
                    );
                    $artukul=$value['id'];

                    if((strpos($value["name"],"Акция") !== false) || (strpos($value["name"],"Акция.") !== false)){
                        $arFields["IBLOCK_SECTION_ID"]=$arr_catalog_ids["catalog_section_akcia"];
                    }
                    elseif(strpos($value["name"],"Сет") !== false){
                        $arFields["IBLOCK_SECTION_ID"]=$arr_catalog_ids["catalog_section_set"];
                    }
                    elseif(strpos($value["name"],"Мини") !== false){
                        $arFields["IBLOCK_SECTION_ID"]=$arr_catalog_ids["catalog_section_miny"];
                    }
                    elseif(strpos($value["name"],"Запеченный") !== false){
                        $arFields["IBLOCK_SECTION_ID"]=$arr_catalog_ids["catalog_section_zapech"];
                    }
                    elseif(strpos($value["name"],"Филадельфия") !== false){
                        $arFields["IBLOCK_SECTION_ID"]=$arr_catalog_ids["catalog_section_filadel"];
                    }
                    elseif(strpos($value["name"],"Темпурный") !== false){
                        $arFields["IBLOCK_SECTION_ID"]=$arr_catalog_ids["catalog_section_tempur"];
                    }
                    elseif(strpos($value["name"],"Ролл") !== false){
                        $arFields["IBLOCK_SECTION_ID"]=$arr_catalog_ids["catalog_section_roll"];
                    }
                    elseif(strpos($value["name"],"Пицца") !== false){
                        $arFields["IBLOCK_SECTION_ID"]=$arr_catalog_ids["catalog_section_pizza"];
                    }
                }

                if(isset($productSite[$artukul])){

                    $new=["NAME"=>$arFields["NAME"],"CODE"=>$arFields["CODE"]];

                    $new["PROPERTY_VALUES"]=$arFields["PROPERTY_VALUES"];
                    $new["PROPERTY_VALUES"][$arr_catalog_ids["bd_props_1"]]=array($oldprops[$artukul]=>array('VALUE' =>$arFields["PROPERTY_VALUES"][$arr_catalog_ids["bd_props_1"]]["n0"]['VALUE'],));

                    CIBlockElement::SetPropertyValuesEx($productSite[$artukul],false,array("PRICE"  => $value['price']));

                    if ($arFields["IBLOCK_SECTION_ID"]==$arr_catalog_ids["catalog_section_pizza"]){
                        CIBlockElement::SetPropertyValuesEx($productSite[$artukul],false,array("BD_PROPS_1" => $arFields["PROPERTY_VALUES"][$arr_catalog_ids["bd_props_1"]]));
                    }

                    CIBlock::clearIblockTagCache($arr_catalog_ids["catalog_iblock"]);
                }else{
                    $ID = $obElement->Add($arFields);
                }
            }
        }
    }


    /*new_order*/
    function orderFrontpad($addition = array(), $count_person = 1, $sertificate = "")
    {
        global $DB;

        $strProduct = $_POST["ArrayOrder"];
        $explodeProduct = explode(':', $strProduct);
        $product = explode(';', $explodeProduct[0]);
        $product_kol = explode(';', $explodeProduct[1]);

        $name = $_POST["ORDER"]["USER_NAME"];
        $phone = $_POST["ORDER"]["USER_PHONE"];
        $street = $_POST["ORDER"]["STREET"];
        $aparment = $_POST["ORDER"]["APARTMENT"];
        $home = $_POST["ORDER"]["HOUSE"];
        $comment = "";
        if ($_POST["ORDER"]["ODD_MONEY"]) {
            if ($_POST["ORDER"]["COMMENT"]) {
                $comment = "Сдача с: ".$_POST["ORDER"]["ODD_MONEY"]." Комментарий к заказу: ".$_POST["ORDER"]["COMMENT"];
            }
            else $comment = "Сдача с: ".$_POST["ORDER"]["ODD_MONEY"];
        }
        elseif($_POST["ORDER"]["COMMENT"]) $comment = "Комментарий к заказу: ".$_POST["ORDER"]["COMMENT"];

        if ($addition){
            foreach ($addition as $k => $v)
            {   if (isset($v["PROMO_NAME"]) && ($v["PROMO_NAME"] != ""))
            {
                $comment .= " Товар: ".$v["PROMO_NAME_PRODUCT"].". По промокоду на сайте: ".$v["PROMO_NAME"];
            }
            }
        }

        $discontBonuses = $_POST["ORDER"]["DISCOUNT_BONUSES"];
        $pod = $_POST["ORDER"]["PROCH"];
        $et = $_POST["ORDER"]["FLOOR"];

        $phoneCard = intval(preg_replace("/[^0-9]/", '', $phone));

        //детали заказа в кодировке utf-8
        $param['secret'] = $this->getkey();				//ключ api
        $param['street']  = urlencode($street);		//улица
        $param['home']	= $home; 				//дом
        $param['apart']	= $aparment;	 			//квартира
        $param['phone'] = $phone;		//телефон
        $param['descr']	= urlencode($comment); 	//комментарий
        $param['name']	= urlencode($name); 		//имя клиента
        $param['certificate']	= urlencode($sertificate); 		//номер сертификата
        $param['person'] = $count_person;
        $param['score'] = $discontBonuses;
        $param['pod'] = $pod;
        $param['et'] = $et;
        if($_POST["ORDER"]["PAYMENT_TYPE"] <> 203) $param['pay'] = 2;

        if($_POST["ORDER"]["DELIVERY_DATE"]) {

            $mnNames = ['', 'января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря'];
            $date_order = explode(" ", $_POST["ORDER"]["DELIVERY_DATE"]);

            if ((int)$date_order[0] < 10) $date_order[0] = "0".(int)$date_order[0];

            $date_order[1] = array_search($date_order[1], $mnNames);
            if ((int)$date_order[1] < 10) $date_order[1] = "0".(int)$date_order[1];

            if($_POST["ORDER"]["HOUR"] && $_POST["ORDER"]["MINUTE"]) {
                $param['datetime'] = date ( 'Y' )."-".$date_order[1]."-".$date_order[0]." ".$_POST["ORDER"]["HOUR"].":".$_POST["ORDER"]["MINUTE"].":00";
            }
        }

        //отправка

        $result = $this->curl($param, "new_order", $product, $product_kol, $addition);

        $arrayOrder = json_decode(explode("\r\n\r\n",$result)[1]);

        $phone = preg_replace("/[^,.0-9]/", '', $phone);

        $orders =  Deliverysushi\Entity\OrderTable::getList(array('filter' => array('=USER_PHONE' => $phone)))->fetchAll();

        foreach ($orders as $value) {
            $id = $value['ID'];
        }

        if ($id) {
            $idNumber = $arrayOrder->order_id;
            $order_update['ORDER_ID_FRONTPAD'] = $idNumber;
            Deliverysushi\Entity\OrderTable::update($id, $order_update);
        }
    }

    /*get_client*/
    function updateSkidka()
    {

        global $USER;

        if ($USER->IsAuthorized()) {

            if (!empty($_SESSION['SALE'])){
                return $_SESSION['SALE'];
            }

            $userId = $USER->GetID();
            $rsUser = CUser::GetByID($userId);

            if ($arUser = $rsUser->Fetch()) {
                $phone =  $arUser["PERSONAL_PHONE"];
            }

            $arUserLoginPhone = \Bitrix\Main\UserPhoneAuthTable::getList([
                'select' => array('PHONE_NUMBER'),
                'filter' => array('=USER_ID' => $userId),
            ])->fetch();

            $param['secret'] = $this->getkey($this->city);

            if ($arUserLoginPhone) {
                $param['client_phone'] =
                    substr($arUserLoginPhone["PHONE_NUMBER"], 1, 1) . " (" .
                    substr($arUserLoginPhone["PHONE_NUMBER"], 2, 3) . ") " .
                    substr($arUserLoginPhone["PHONE_NUMBER"], 5, 3) . "-" .
                    substr($arUserLoginPhone["PHONE_NUMBER"], 8, 2) . "-" .
                    substr($arUserLoginPhone["PHONE_NUMBER"], 10, 2);
            }
            else $param['client_phone'] = $phone;

            $skidka = 0;
            $_SESSION['SALE'] = $skidka;
            return $skidka;
        }
        return false;
    }

    function getlklient()
    {
        global $USER;

        if ($USER->IsAuthorized()) {

            $userId = $USER->GetID();
            $rsUser = CUser::GetByID($userId);

            if ($arUser = $rsUser->Fetch()) {
                $phone =  $arUser["PERSONAL_PHONE"];
            }

            $arUserLoginPhone = \Bitrix\Main\UserPhoneAuthTable::getList([
                'select' => array('PHONE_NUMBER'),
                'filter' => array('=USER_ID' => $userId),
            ])->fetch();

            $param['secret'] = $this->getkey($this->city);

            if ($arUserLoginPhone) {
                $param['client_phone'] =
                    substr($arUserLoginPhone["PHONE_NUMBER"], 1, 1) . " (" .
                    substr($arUserLoginPhone["PHONE_NUMBER"], 2, 3) . ") " .
                    substr($arUserLoginPhone["PHONE_NUMBER"], 5, 3) . "-" .
                    substr($arUserLoginPhone["PHONE_NUMBER"], 8, 2) . "-" .
                    substr($arUserLoginPhone["PHONE_NUMBER"], 10, 2);
            }
            else $param['client_phone'] = $phone;

            $result = $this->curl($param, "get_client");

            $result = json_decode(explode("\r\n\r\n",$result)[1]);

            $_SESSION['CLIENT_INFO'] = $result;

            return $result;
        }
        return false;
    }



    function get_Sertificate($code_sertificate = "")
    {
        $param['secret'] = $this->getkey($this->city);
        $param['certificate'] = $code_sertificate;

        $result = $this->curl($param, "get_certificate");

        $result = json_decode(explode("\r\n\r\n",$result)[1], true);

        return $result;
    }



    /*get_status*/
    function statusOrder($phone)
    {
        // Получаем таблицу заказов
        $ordersStatus =  Deliverysushi\Entity\OrderTable::getList(array('filter' => array('=USER_PHONE' => $phone)))->fetchAll();

        foreach ($ordersStatus as $key => $orderStatus) {
            // Перебераем таблицу заказов по статусу
            if ($orderStatus['STATUS'] == 0 || 6 || 8) {
                // Если Статус не равен 7 и поле idFrontpad не равна 0 делаем запрос в crm
                if ($orderStatus['ORDER_ID_FRONTPAD'] != 0 && $orderStatus['STATUS'] != 7 || 2 ) {
                    $param['secret'] = $this->getkey($this->city);
                    $param['order_id'] = $orderStatus['ORDER_ID_FRONTPAD'];
                }
            }
        }
    }

    public function idProductSite($arrProductSite, $arrProductCrm) //получаем массивы с сайта и получаем массив с CRM
    {
        $id = [];

        foreach ($arrProductSite as $key => $value)
        {
            $id[] = array('xmlIdSite' => $value["XML_ID"], 'idElementSite' => $value["ID"]);
        }

        foreach ($id as $key => $value)
        {
            if($value["xmlIdSite"] == $arrProductCrm) {
                return $value['idElementSite'];
            }
        }
        return true;
    }

    public function translit($str)
    {
        $tr = array(
            "А"=>"A","Б"=>"B","В"=>"V","Г"=>"G",
            "Д"=>"D","Е"=>"E","Ж"=>"J","З"=>"Z","И"=>"I",
            "Й"=>"Y","К"=>"K","Л"=>"L","М"=>"M","Н"=>"N",
            "О"=>"O","П"=>"P","Р"=>"R","С"=>"S","Т"=>"T",
            "У"=>"U","Ф"=>"F","Х"=>"H","Ц"=>"TS","Ч"=>"CH",
            "Ш"=>"SH","Щ"=>"SCH","Ъ"=>"","Ы"=>"YI","Ь"=>"",
            "Э"=>"E","Ю"=>"YU","Я"=>"YA","а"=>"a","б"=>"b",
            "в"=>"v","г"=>"g","д"=>"d","е"=>"e","ж"=>"j",
            "з"=>"z","и"=>"i","й"=>"y","к"=>"k","л"=>"l",
            "м"=>"m","н"=>"n","о"=>"o","п"=>"p","р"=>"r",
            "с"=>"s","т"=>"t","у"=>"u","ф"=>"f","х"=>"h",
            "ц"=>"ts","ч"=>"ch","ш"=>"sh","щ"=>"sch","ъ"=>"y",
            "ы"=>"yi","ь"=>"","э"=>"e","ю"=>"yu","я"=>"ya",
            "."=>"_"," "=>"_","?"=>"_","/"=>"_","\\"=>"_",
            "*"=>"_",":"=>"_","*"=>"_","\""=>"_","<"=>"_",
            ">"=>"_","|"=>"_"
        );

        return strtr($str,$tr);
    }
}

?>
