<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();


\Bitrix\Main\Loader::includeModule('iblock');

class Pay_Query_Sber
{

    protected $userName = 'P1111111111-api';
    protected $password = '1111111111';

    public function QueryRegistrSberPay($num_contract, $fio = "", $fio_child = "", $tel = ""){

        $arSelect = Array("ID", "IBLOCK_ID", "NAME", "PROPERTY_*");
        $arFilter = Array("IBLOCK_ID"=>37, "ACTIVE"=>"Y", "PROPERTY_NUMBER_CONTRACT"=>$num_contract);
        $res = CIBlockElement::GetList(Array(), $arFilter, false, Array("nPageSize"=>50), $arSelect);
        while($ob = $res->GetNextElement())
        {
            $arFields = $ob->GetFields();
            $arProps = $ob->GetProperties();
            
            if ($arProps["SUMM"]["VALUE"] > 0){

                $vars = array();
                $vars['userName'] = $this->userName;
                $vars['password'] = $this->password;

                /* Описание заказа, не более 24 символов, запрещены % + \r \n */
                if ($fio != "") $vars['description'] = 'Путевка '.$fio;

                /* ID заказа в магазине */
                $vars['orderNumber'] = $arFields["ID"];
                /* Сумма заказа в копейках */
                $vars['amount'] = $arProps["SUMM"]["VALUE"] * 100;
                /* URL куда клиент вернется в случае успешной оплаты */
                $vars['returnUrl'] = 'https://1111111.ru/include/success_paymant_sber.php';
                /* URL куда клиент вернется в случае ошибки */
                $vars['failUrl'] = 'https://1111111.ru/include/error_paymant_sber.php';
                $vars['description'] = 'Оплата путевки';

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'https://securepayments.sberbank.ru/payment/rest/register.do?' . http_build_query($vars));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_HEADER, false);

                $res = curl_exec($ch);

                if($res === false){
                    $err = curl_error($ch).". Попробуйте ещё раз.";
                    curl_close($ch);
                    return "Ошибка ".$err;
                }else{
                    curl_close($ch);
                    $res = json_decode($res, JSON_OBJECT_AS_ARRAY);

                    if (empty($res['orderId'])){
                        /* Возникла ошибка: */
                        return $res['errorMessage'];
                    } else {
                        /* Успех: */

                        // Установим новое значение для свойств данного договора
                        CIBlockElement::SetPropertyValuesEx($arFields["ID"], 37, array(
                                "FIO_PLAT" => $fio,
                                "FIO_CHILD" => $fio_child,
                                "PHONE" => $tel,
                            )
                        );

                        /* Перенаправление клиента на страницу оплаты */
                        return '<script>document.location.href = "' . $res['formUrl'] . '"</script>';
                    }
                }
            }
            else{
                return "Сумма путёвки не прописана в договоре";
            }
        }
        return "Номер договора не найден";
    }
}

?>