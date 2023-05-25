
function plus(e){
    $(e).parents(".form_composition").append(
        '<div class="row_composition col-md-12 no-padding-l">\n' +
        '            <div class="col-lg-2 col-md-4 col-sm-6 col-xs-12 no-padding-l padding-top-md" style="padding-right: 3px">\n' +
        '                <p class="composition_label"> Бренд </p>\n' +
        '                <select class="select_brand" name="request_composition[0][brand]">\n' +
        '                    <option label="Бренд не выбран" value="Не выбран" selected>Выберите бренд</option>\n' +
        '                    <option value="Бренд №1">Бренд №1</option>\n' +
        '                    <option value="Бренд №2">Бренд №2</option>\n' +
        '                    <option value="Бренд №2">Бренд №3</option>\n' +
        '                </select>\n' +
        '            </div>\n' +
        '            <div class="col-lg-2 col-md-4 col-sm-6 col-xs-12 padding-border padding-top-md">\n' +
        '                <p class="composition_label"> Наименование </p>\n' +
        '                <input type="text" name="request_composition[0][name]" class="item">\n' +
        '            </div>\n' +
        '            <div class="col-lg-2 col-md-4 col-sm-6 col-xs-12 padding-border padding-top-md">\n' +
        '                <p class="composition_label"> Количество </p>\n' +
        '                <input type="text" name="request_composition[0][count]" class="item">\n' +
        '            </div>\n' +
        '            <div class="col-lg-2 col-md-4 col-sm-6 col-xs-12 padding-border padding-top-md">\n' +
        '                <p class="composition_label"> Фасовка </p>\n' +
        '                <input type="text" name="request_composition[0][packing]" class="item">\n' +
        '            </div>\n' +
        '            <div class="col-lg-2 col-md-4 col-sm-6 col-xs-12 padding-border padding-top-md">\n' +
        '                <p class="composition_label"> Клиент </p>\n' +
        '                <input type="text" name="request_composition[0][client]" class="item">\n' +
        '            </div>\n' +
        '            <div class="col-lg-1 col-md-4 col-sm-6 col-xs-12 padding-border padding-top-md">\n' +
        '                <button type="button" class="plus" onclick="plus(this);">+</button>\n' +
        '                <button type="button" class="minus" onclick="minus(this);">-</button>\n' +
        '            </div>\n' +
        '        </div>'
    );
}
function minus(e) {
    console.log($('.row_composition').length);
    if($('.row_composition').length > 1){
        $(e).parents(".row_composition").remove();
    }
}
