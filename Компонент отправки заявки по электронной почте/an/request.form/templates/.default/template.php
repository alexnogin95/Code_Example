<? if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$this->setFrameMode(true);
$this->addExternalCss("/bitrix/css/main/bootstrap.css");
?>

<p class="header_p"> Новая заявка </p>

<form id="request_form" enctype="multipart/form-data" method="post">
    <?=bitrix_sessid_post();?>

    <label for="request_header" class="text_label"> Заголовок заявки </label>
    <input type="text" class="inputtext" required name="request_header" value="" aria-required="true" aria-invalid="false">

    <p class="header_p"> Категория </p>
    <div class="radio_form">
    <input type="radio" value="Масла, автохимия, фильтры, аксессуары, обогреватели, запчасти, сопутствующие товары" required checked name="request_category"/>
        <span>Масла, автохимия, фильтры, аксессуары, обогреватели, запчасти, сопутствующие товары</span>
    </div>
    <div class="radio_form">
        <input type="radio" value="Шины, диски" name="request_category"/><span>Шины, диски</span>
    </div>

    <p class="header_p" class="header_label"> Вид заявки </p>
    <div class="radio_form">
        <input type="radio" value="Запрос цены и сроков поставки" required checked name="request_type"/><span>Запрос цены и сроков поставки</span>
    </div>
    <div class="radio_form">
        <input type="radio" value="Пополнение складов" name="request_type"/><span>Пополнение складов</span>
    </div>
    <div class="radio_form">
        <input type="radio" value="Спецзаказ" name="request_type"/><span>Спецзаказ</span>
    </div>

    <p class="header_p"> Склад поставки </p>
    <select class="select_storage" name="request_storage">
        <option value="Не выбран" selected>(Выберите склад поставки)</option>
        <option value="Склад №1">Склад №1</option>
        <option value="Склад №2">Склад №2</option>
        <option value="Склад №3">Склад №3</option>
    </select>

    <p class="header_p" style="margin-bottom: unset"> Состав заявки </>
    <div class="form_composition">
        <div class="row_composition col-md-12 no-padding-l">
            <div class="col-lg-2 col-md-4 col-sm-6 col-xs-12 no-padding-l padding-top-md" style="padding-right: 3px">
                <p class="composition_label"> Бренд </p>
                <select class="select_brand" name="request_composition[0][brand]">
                    <option label="Бренд не выбран" value="Не выбран" selected>Выберите бренд</option>
                    <option value="Бренд №1">Бренд №1</option>
                    <option value="Бренд №2">Бренд №2</option>
                    <option value="Бренд №2">Бренд №3</option>
                </select>
            </div>
            <div class="col-lg-2 col-md-4 col-sm-6 col-xs-12 padding-border padding-top-md">
                <p class="composition_label"> Наименование </p>
                <input type="text" name="request_composition[0][name]" class="item">
            </div>
            <div class="col-lg-2 col-md-4 col-sm-6 col-xs-12 padding-border padding-top-md">
                <p class="composition_label"> Количество </p>
                <input type="text" name="request_composition[0][count]" class="item">
            </div>
            <div class="col-lg-2 col-md-4 col-sm-6 col-xs-12 padding-border padding-top-md">
                <p class="composition_label"> Фасовка </p>
                <input type="text" name="request_composition[0][packing]" class="item">
            </div>
            <div class="col-lg-2 col-md-4 col-sm-6 col-xs-12 padding-border padding-top-md">
                <p class="composition_label"> Клиент </p>
                <input type="text" name="request_composition[0][client]" class="item">
            </div>
            <div class="col-lg-1 col-md-4 col-sm-6 col-xs-12 padding-border padding-top-md">
                <button type="button" class="plus" onclick="plus(this);">+</button>
                <button type="button" class="minus" onclick="minus(this);">-</button>
            </div>
        </div>
    </div>

    <input class="files_form" type="file" name="request_file[]" multiple>

    <label for="request_message" class="text_label"> Комментарий </label>
    <textarea name="request_message" class="text_comment"></textarea>

    <button type="submit"> Отправить </button>

</form>
