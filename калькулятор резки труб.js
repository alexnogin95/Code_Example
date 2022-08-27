$(document).ready(function(){

    $(".filter-wrap .n-btn").on("mouseenter",function(){
      $(this).find(".f-icon").css({"background-image":"url(/local/components/olof_v4/catalog/templates/tile/images/sort_icon_red.png)"});
    })
    $(".filter-wrap .n-btn").on("mouseleave",function(){
      $(this).find(".f-icon").removeAttr("style");
    })
    $(".filter-wrap .n-btn").on("click",function(){
      var url = document.location.pathname;
      if(!$(this).hasClass("down") && !$(this).hasClass("up")){
        location.href = url + "?sort=name_down";
      }
      if($(this).hasClass("down")){
        location.href = url + "?sort=name_up";
      }
      if($(this).hasClass("up")){
        location.href = url + "?sort=name_down";
      }
    });

    $(".filter-wrap .f-btn").on("mouseenter",function(){
      $(this).find(".f-icon").css({"background-image":"url(/local/components/olof_v4/catalog/templates/tile/images/sort_icon_red.png)"});
    })
    $(".filter-wrap .f-btn").on("mouseleave",function(){
      $(this).find(".f-icon").removeAttr("style");
    })
    $(".filter-wrap .f-btn").on("click",function(){
      var url = document.location.pathname;
      if(!$(this).hasClass("down") && !$(this).hasClass("up")){
        location.href = url + "?sort=price_down";
      }
      if($(this).hasClass("down")){
        location.href = url + "?sort=price_up";
      }
      if($(this).hasClass("up")){
        location.href = url + "?sort=price_down";
      }
    });
});

jQuery(function($) {
	function calcPrice(row, total_price, flag = 0) {
		var price = row.find(total_price);
		var price2 = row.find(".total-price2");
		var basePrice = row.find(".catalog-table-price-id .base");
		var maxCount = +basePrice.data('max');
		if (isNaN(maxCount)) {
			maxCount = +row.find(".catalog-table-count").data('max');
		}
		var count = parseFloat(row.find(".catalog-table-count").val());

		var basket_count = row.find(".cart-cnt").text();
		var baseScale = row.find(".catalog-table-count-visible").data('base_scale');
		var additionalScale = row.find(".catalog-table-count-visible").data('additional_scale');

		var additPrice = row.find(".catalog-table-price-id .coeff");
		var addit_coeff = +additPrice.data('coeff');
		var addit_length = +additPrice.data('length');

		if (isNaN(addit_length)) addit_length = base_length;
		if (isNaN(addit_coeff)) addit_coeff = base_coeff;
		row.find(".coeff .val").html(addit_coeff * addit_length * count);

		var base_coeff = +basePrice.data('coeff');
		var base_length = +basePrice.data('length');


		var itog_mesure = row.find('.unit-sel').val();

		count2 = count;
		if (baseScale == "кг" && itog_mesure == "796"){
			count2 = count * base_coeff * base_length;
		}

		if (baseScale == "кг" && itog_mesure == "6"){
			count2 = count / (addit_coeff * addit_length)
			count2 = count2 * base_coeff * base_length;
		}

		if (baseScale == "кг" && itog_mesure == "166"){
			count2 = count;
		}

		if (baseScale == "м" && itog_mesure == "796"){
			count2 = count * base_coeff * base_length;
		}

		if (baseScale == "м" && itog_mesure == "166"){
			count2 = count * addit_coeff * addit_length;
		}

		if (baseScale == "м" && itog_mesure == "6"){
			count2 = count;
		}

		if (baseScale == "шт"){
			count2 = count;
		}


		if (maxCount < count2) {
			count = maxCount;

			if (baseScale == "кг" && itog_mesure == "796"){
				count = maxCount / (base_coeff * base_length);
			}

			if (baseScale == "кг" && itog_mesure == "6"){
				var count_shtuki = maxCount / (base_coeff * base_length);
				count = addit_coeff * addit_length * count_shtuki;
			}


			if (baseScale == "кг" && itog_mesure == "166"){
				count = maxCount;
			}

			if (baseScale == "м" && itog_mesure == "796"){
				count = maxCount / (base_coeff * base_length);
			}

			if (baseScale == "м" && itog_mesure == "166"){
				count = maxCount / (addit_coeff * addit_length);
			}

			if (baseScale == "м" && itog_mesure == "6"){
				count = maxCount;
			}

		}

		row.find(".catalog-table-count").val(count);


		var base_val = base_coeff * base_length * count;
		if(typeof base_val === 'number'){
			if(! base_val % 1 === 0){
				base_val = +base_val.toFixed(3);
			}
		}
		row.find(".base .val").html(base_val);

		switch (itog_mesure) {
			case "796":
				if (flag === 1) {
					row.find(".catalog-table-count-visible").val(count.toFixed(2));
				}
				else {
					row.find(".catalog-table-count-visible").val(count);
				}
				break;
			case "6":
				if (flag === 1) {
					count = count / (addit_coeff * addit_length);
				}
				else {
					row.find(".catalog-table-count-visible").val((addit_coeff * addit_length * count).toFixed(2));
				}
				break;

			case "166":
				if (flag === 1) {
					count = count / (base_coeff * base_length);
				}
				else {
					row.find(".catalog-table-count-visible").val((base_coeff * base_length * count).toFixed(2));
				}
				break;
		}


		if (flag === 1) {
			row.find(".itog-items").text(count.toLocaleString("ru-RU", {maximumFractionDigits: 2, minimumFractionDigits: 2}));
			row.find(".catalog-table-count").val(count.toFixed(2));
		}
		else {
			row.find(".itog-items").text(count.toLocaleString("ru-RU", {maximumFractionDigits: 2, minimumFractionDigits: 2}));
			row.find(".catalog-table-count").val(count);
		}


		var base_val = base_coeff * base_length * count;
		var addit_val = addit_coeff * addit_length * count;
		if(typeof base_val === 'number'){
	   	if(! base_val % 1 === 0){
		      base_val = +base_val.toFixed(3);
		   }
		}

		if (flag === 1 && maxCount < count2) {

			if (baseScale == "кг" && itog_mesure == "6"){
				row.find(".catalog-table-count-visible").val((addit_val).toFixed(2));
			}

			if (baseScale == "кг" && itog_mesure == "166"){
				row.find(".catalog-table-count-visible").val((base_val).toFixed(2));
			}

			if (baseScale == "м" && itog_mesure == "166"){
				row.find(".catalog-table-count-visible").val((addit_val).toFixed(2));
			}

			if (baseScale == "м" && itog_mesure == "6"){
				row.find(".catalog-table-count-visible").val((base_val).toFixed(2));
			}

		}

		row.find(".base .val").html(base_val.toLocaleString("ru-RU", {maximumFractionDigits: 2, minimumFractionDigits: 2}));

		row.find(".coeff .val").html(addit_val.toLocaleString("ru-RU", {maximumFractionDigits: 2, minimumFractionDigits: 2}));

		var price = +price.data('tprice');
		var price2 = +price2.data('tprice');

		var totalPrice = (count * price).toLocaleString("ru-RU", {maximumFractionDigits: 2, minimumFractionDigits: 2}).replace(',', '.');
		var totalPrice2 = (count * price2).toLocaleString("ru-RU", {maximumFractionDigits: 2, minimumFractionDigits: 2}).replace(',', '.');

		row.find(".total-price").html(totalPrice + '&nbsp;р.').attr("data-tsum",totalPrice);
		row.find(".total-price2").html(totalPrice2 + '&nbsp;р.').attr("data-tsum",totalPrice2);
		row.find(total_price).html(totalPrice + '&nbsp;р.');
		row.find(total_price + '_sum_place').html(totalPrice);


		if (total_price == ".calc_sum") {
			 count = $(".catalog-detail .count-wrap").find(".catalog-table-count").val();

			if (row.find("#block").css("display") !== "none") {
				var p_sum = $(".calc").find(".sum_price").attr('data-tprice');

			}
			else {
				var p_sum = $(".catalog-detail .count-wrap").find(".total-price").attr('data-tprice');
			}

		}
		else {
			var p_sum = totalPrice + '&nbsp;р.';
		}

			var p_num = count + '&nbsp;шт.';
			$("#quickOrder").find(".product-sum").html(p_sum);
			$("#quickOrder").find(".product-num").html(p_num);
			$("#quickOrder").find(".product-quant").val(count);
			$("#quickOrder").find("input[name='product_num']").val(p_num);
			$("#quickOrder").find("input[name='product_sum']").val(p_sum);
	}


  calcPrice($(".catalog-detail .count-wrap"), ".total-price",1); //расчет при загрузке страницы
	
	
	var element=document.getElementById('total-price_sum_place');
	
	if(element){
		var price_goods = +parseFloat($(".count-wrap").find(".total-price_sum_place").html().replace('&nbsp;', ''));
		var price_calc = +parseFloat($(".calc").find(".calc_sum_sum_place").html().replace('&nbsp;', ''));

		var summa = (price_goods + price_calc).toFixed(2);

		$(".calc").find(".sum_price").html(summa + '&nbsp;р.');
		$(".calc").find(".sum_price").attr('data-tprice',summa);
	}
	

	$(".catalog-table-count").add(".catalog-table-price-id").on("change", function(){
		var row = $(this).parents(".count-wrap");
		calcPrice(row, ".total-price",  1);

		var price_goods = +parseFloat($(".count-wrap").find(".total-price_sum_place").html().replace('&nbsp;', ''));
		var price_calc = +parseFloat($(".calc").find(".calc_sum_sum_place").html().replace('&nbsp;', ''));

		var summa = (price_goods + price_calc).toFixed(2);

		$(".calc").find(".sum_price").html(summa + '&nbsp;р.');
		$(".calc").find(".sum_price").attr('data-tprice',summa);
	});

	$(".catalog-table-count-visible").on("change", function(){
		var row = $(this).parents(".count-wrap");
		var count = row.find(".catalog-table-count-visible").val();

		row.find(".catalog-table-count").val(count);
		calcPrice(row,".total-price", 1);

		var price_goods = +parseFloat($(".count-wrap").find(".total-price_sum_place").html());
		var price_calc = +parseFloat($(".calc").find(".calc_sum_sum_place").html());

		var summa = (price_goods + price_calc).toFixed(2);

		$(".calc").find(".sum_price").html(summa + '&nbsp;р.');
		$(".calc").find(".sum_price").attr('data-tprice',summa);
	});

	$('.catalog-table-count-visible').on('keyup', function(){

		this.value = this.value.replace(/[^.\d]/g, '');
	});

	$(".unit-sel").on("change", function(){
		var row = $(this).parents(".count-wrap");
		row.find(".catalog-table-count").val(1);
		row.find(".catalog-table-count-visible").val(1);
		calcPrice(row,".total-price", 1);
		row.find(".catalog-table-count-visible").val(1);
	});

	$(".catalog-detail .up").on("click",function(){
		var row = $(this).parents(".count-wrap");
		var count = +row.find(".catalog-table-count-visible").val();
		count ++;
		row.find(".catalog-table-count").val(count);
		row.find(".catalog-table-count-visible").val(count);
		calcPrice(row, ".total-price",1);



		var price_goods = +parseFloat($(".count-wrap").find(".total-price_sum_place").html().replace('&nbsp;', ''));
		var price_calc = +parseFloat($(".calc").find(".calc_sum_sum_place").html().replace('&nbsp;', ''));

		var summa = (price_goods + price_calc).toFixed(2);

		$(".calc").find(".sum_price").html(summa + '&nbsp;р.');
		$(".calc").find(".sum_price").attr('data-tprice',summa);
	});
	$(".catalog-detail .down").on("click",function(){
		var row = $(this).parents(".count-wrap");
		var count = +row.find(".catalog-table-count-visible").val();
		count --;
		if(count < 0) {
			count = 0;
		}
		row.find(".catalog-table-count").val(count);
		calcPrice(row, ".total-price",1);

		row.find(".catalog-table-count-visible").val(count);

		var price_goods = +parseFloat($(".count-wrap").find(".total-price_sum_place").html().replace('&nbsp;', ''));
		var price_calc = +parseFloat($(".calc").find(".calc_sum_sum_place").html().replace('&nbsp;', ''));

		var summa = (price_goods + price_calc).toFixed(2);

		$(".calc").find(".sum_price").html(summa + '&nbsp;р.');
		$(".calc").find(".sum_price").attr('data-tprice',summa);
	});



//---------------------------------------------Калькулятор---------------------------------------------------------//

	$(".calc .catalog-table-count").on("change", function(){
		var row_calc = $(this).parents(".calc");
		calcPrice(row_calc, ".calc_sum");

		var price_goods = +parseFloat($(".count-wrap").find(".total-price_sum_place").html().replace('&nbsp;', ''));
		var price_calc = +parseFloat($(".calc").find(".calc_sum_sum_place").html().replace('&nbsp;', ''));

		var summa = (price_goods + price_calc).toFixed(2);

		$(".calc").find(".sum_price").html(summa + '&nbsp;р.');
		$(".calc").find(".sum_price").attr('data-tprice',summa);
	});

	$(".calc .up").on("click",function(){

		var row = $(this).parents(".count-wrap");
		var price = row.find(".total-price");
		var price = +price.data('tprice');

		var row_calc = $(this).parents(".calc");

		var count = +row_calc.find(".catalog-table-count").val();

		if (count < 500){
			count ++;
		}

		row_calc.find(".catalog-table-count").val(count);
		calcPrice(row_calc, ".calc_sum");

		var price_goods = +parseFloat($(".count-wrap").find(".total-price_sum_place").html().replace('&nbsp;', ''));
		var price_calc = +parseFloat($(".calc").find(".calc_sum_sum_place").html().replace('&nbsp;', ''));

		var summa = (price_goods + price_calc).toFixed(2);

		$(".calc").find(".sum_price").html(summa + '&nbsp;р.');
		$(".calc").find(".sum_price").attr('data-tprice',summa);
	});
	$(".calc .down").on("click",function(){
		var row_calc = $(this).parents(".calc");
		var count = +row_calc.find(".catalog-table-count").val();

		if(count > 0) {
			count --;
		}
		row_calc.find(".catalog-table-count").val(count);
		calcPrice(row_calc, ".calc_sum");

		var price_goods = +parseFloat($(".count-wrap").find(".total-price_sum_place").html().replace('&nbsp;', ''));
		var price_calc = +parseFloat($(".calc").find(".calc_sum_sum_place").html().replace('&nbsp;', ''));

		var summa = (price_goods + price_calc).toFixed(2);

		$(".calc").find(".sum_price").html(summa + '&nbsp;р.');
		$(".calc").find(".sum_price").attr('data-tprice',summa);
	});

	$(".catalog-detail .cutting").on("click",function(){
		if ($("#block").css("display") == "none") {
			$("#block").fadeIn(400);
			$("#block").css("display","block");
			calcPrice($(".calc"), ".calc_sum");

			var price_goods = +parseFloat($(".count-wrap").find(".total-price_sum_place").html().replace('&nbsp;', ''));
			var price_calc = +parseFloat($(".calc").find(".calc_sum_sum_place").html().replace('&nbsp;', ''));

			var summa = (price_goods + price_calc).toFixed(2);

			$(".calc").find(".sum_price").html(summa + '&nbsp;р.');
			$(".calc").find(".sum_price").attr('data-tprice',summa);
		}
		else {
			$("#block").fadeOut(400);
			$("#block").css("display","none");
			calcPrice($(".catalog-detail .count-wrap"), ".total-price");

		}
	});

	$(".catalog-detail .quick-order").on("click",function(){
		if ($("#block").css("display") !== "none") {
			calcPrice($(".calc"), ".calc_sum");
		}
		else {
			calcPrice($(".catalog-detail .count-wrap"), ".total-price");
		}
	});

	$('#count_rayze').on('keyup', function(){

		this.value = this.value.replace(/\D/g, '');
	});

	$('#count_rayze').on('change', function(){

		if (this.value == ""){
			this.value = 0;
		}

		if (this.value > 500){
			this.value = 500;
		}
	});

});
