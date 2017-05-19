/**
 * Языковые данные
 */
var product_types = {
	'ru' : ['Точечный', 'Линейный', 'Периметр', 'Площадь', 'Объем'],
	'en' : ['Point', 'Linear', 'Perimeter', 'Area', 'Volume']
};

var work_types = {
	'ru' : {
		'linear_work' : 'линейная',
		'point_work' : 'точечная'
	},
	'en' : {
		'linear_work' : 'Linear work',
		'point_work' : 'Point work'
	}
};
var work_type_time = {
	'ru' : {
		'min' : 'минуты',
		'hour' : 'часы'
	},
	'en' : {
		'min' : 'минуты',
		'hour' : 'часов'
	}
};
var work_calc_types = {
	'ru' : {
		'length_calc' : 'от длины',
		'area_calc' : 'от площади',
		'volume_calc' : 'от объема'
	},
	'en' : {
		'length_calc' : 'Length calc',
		'area_calc' : 'Area calc',
		'volume_calc' : 'Volume calc'
	}
};


var lang = $('#current_language').val();
var user_id = G_USER_ID; // 1
var products_limit = 10;
var products_page = 1;
var current_category = '*';
// for no_drag
/*
var drag_el;
var drag_data;
*/

// for now drag
	function onstartdrag(e) {
	   /*e.preventDefault(); e.stopPropagation();*/ // если раскоментить не будет начала драга

	   document.domain = xdomain; //"online.cableproject.net";

		//$('.products').attr({draggable: "true"});
		//$('.products').bind("dragstart", onstartdrag);

		//console.log('start drag on');

		if (e.originalEvent)
		{
			e.dataTransfer = e.originalEvent.dataTransfer;
		}
		//console.log(e);
		//e.dataTransfer.effectAllowed = 'move'; //'all';

		////e.dataTransfer.setData("Text", e.target.outerHTML);
		drag_el = e.target;
		//console.log(drag_el);
		if (drag_el.tagName == 'IMG') {
			//var id = $(drag_el.nextSibling.nextSibling).attr('data-id');
			drag_el = drag_el.parentNode;
		}
		var id = $($(drag_el).find('span')[0]).attr('data-id');
		doAction_load('loadProductData', {'id' : id, 'lang' : window.lang}, 'An error occurred while loading', {dataType: "json"}, function(data){

			e.dataTransfer.effectAllowed = 'move'; //'all';

			drag_data = JSON.stringify(data);
			e.dataTransfer.setData("Text", drag_data);
			//console.log(JSON.stringify(data));

		});

		//parent.window.postMessage({message: {cmd: 'drag_data', data: e.target.outerHTML}}, '*');

		//$.event.props.push('dataTransfer');
		//$("#dragover").html('<font color=#FF0000><b>WORK</b></font><br />event: <b>drop</b>');
     }


$(document).ready(function(){

$('.category_id_search').val( current_category );

var splitter = $('#outerContainer').split({
    orientation: 'horizontal',
    limit: 10,
    position: '50%', // if there is no percentage it interpret it as pixels
    onDrag: function(event) {
//        console.log(splitter.position());
    }
});
// for no_drag
/*
$('ul.products li').attr({draggable: "true"});
$('ul.products li').bind("dragstart", onstartdrag);
*/
	$('#change_language').on('change',function(){
		lang = $('#change_language').val();
		rebuild_tree(lang,0, G_USER_ID);
		rebuild_tree(lang,1, G_USER_ID);
		rebuild_tree(lang,2, G_USER_ID);
	});

	/*$('#search_product').on('focus',function(){
		$(this).autocomplete("search");
	});*/
	$('#search_category').on('focus',function(){
		$(this).autocomplete("search");
	});

	$('#search_category').autocomplete({
		'source': function(request, response) {
			$.ajax({
				url: 'index.php?searchCategory=true',
				type: 'post',
				data: { search : request,lang :  window.lang , type : $('#search_type').val() },
				dataType: 'json',
				success: function(json) {
					response($.map(json.categories, function(item) {
						return {
							label: item['label'],
							value: item['label'],
							category_id: item['id']
						}
					}));
				}
			});
		},
		minLength: 1,
		select: function (event, ui) {
			/*var category = $('span [data-find_category_id =' +ui.item.category_id + ']');
			 category.addClass('find_product');
			 var destination = category.offset().top;
			 $('#tree').animate( { scrollTop: destination - 300 }, 800 );*/
			categoryClick(ui.item.category_id);

			}
	});

	var default_img_i = "img/no-image.jpg";
	var default_img = "this.src='" + default_img_i + "'";
	$('#job_product_search').autocomplete({
		'source': function(request, response) {
			$.ajax({
				url: 'index.php?searchProduct=true',
				type: 'post',
				data: { search : request, category: $('#search_category').val(), category_id: $('.category_id_search').val() ,lang :  window.lang , type : $('#search_type').val() },
				dataType: 'json',
				success: function(json) {
					response($.map(json, function(item) {
						if (item['vendor_category'] == null) {
							var i_vendor_category = 'NO VENDOR';
						} else {
							var i_vendor_category = item['vendor_category'];
						}
						if (item['image'] == undefined) {
							var i_image = default_img_i;
						}
						else {
							var i_image = item['image']
						}
						return {
							label: item['label'],
							value: item['label'],
							id: item['id'],
							sku: item['sku'],
							image: i_image,
							quantity: item['quantity'],
							category_name: i_vendor_category
						}
					}));
				}
			});
		},
		minLength: 0,
		select: function (event, ui) {
			if(ui.item.quantity == 'no'){
				categoryClick(ui.item.id);
			}else{
				attributeForm(ui.item.id,'true');
			}

		}

	}).data("ui-autocomplete")._renderItem = function(ul, item) {
		if(item.quantity == 'no'){
			return $("<li>").data("ui-autocomplete-item", item).
			append('<a><img src="'+ item.image +'" 	onerror='+default_img+' alt="" width="50px"><span class="category_auto">' + item.category_name + "</span>" + ' - ' + item.label + '</a>').
			appendTo(ul);
			} else {
			return $("<li>").data("ui-autocomplete-item", item).
			append('<a><img src="'+ item.image +'" 	onerror='+default_img+' alt="" width="50px"><span class="category_auto">' + item.category_name + "</span>" + ' - ' + item.label + ' - ' + item.sku + '</a>').
			appendTo(ul);
		}

	};

	$('.resetSearch').on('click',function(){
		$('.collapse-all').click();
		$('#search_product').val('');
		$('#search_category').val('');
	});
	$(document).on('click','.product',function(){
		$('.productTitle.find_product').removeClass('find_product');
		$(this).find('.productTitle').addClass('find_product');
	//	$('.productTitle.find_product').eq(0).trigger('click');
	});
	$(document).on('click','.create-category',function(){
    
		var type = $(this).data('type');

		var text_p = i18next.t('tree_addfolder');
		var value = prompt(text_p, "");
        if(value != null && value.trim()!=""){
            doAction('addParentCategory', {'category_name' : value ,lang :  window.lang , type : type, user_id: G_USER_ID}, 'An error occurred while loading', {dataType: "json"}, function(data) {
                rebuild_tree(lang,type, G_USER_ID);
                //alertify.success('Категория создана: ' + value);

            });
        }
		/*alertify.prompt("Введите название категории", "",
			function(evt, value ){
				doAction('addParentCategory', {'category_name' : value ,lang :  window.lang , type : type}, 'An error occurred while loading', {dataType: "json"}, function(data) {
					rebuild_tree(lang,type, G_USER_ID);
					alertify.success('Категория создана: ' + value);

				});

					},
			function(){
				alertify.error('Отмена');
			})
		;*/
	});

	/**
	 * инициализируем 3 дерева
	 */

	if($('#showForm').val() == 1){
		treeInit(0);
		treeInit(1);
		treeInit(2);
	}

// for no_drag
/*
	$('ul.products li').attr({draggable: "true"});
	$('ul.products li').bind("dragstart", onstartdrag);

    document.domain = xdomain; //"online.cableproject.net";
*/


	/**
	 * инициализируем handlebars хелперы
	 */
	handlebarsHelpers();

	/**
	 * инициализируем 3 таба
	 */
	$("#tabs1").lightTabs();
	$("#tabs2").lightTabs();

	$('.expandEditor').attr('title','Click to show/hide item editor');
	$('.disclose').attr('title','Click to show/hide children');
	$('.deleteMenu').attr('title', 'Click to delete item.');

  $(document).on('change', '.category_id_search', function() {
    
    if ( $(this).prop('checked') ){

      $(this).val( current_category );

    } else {

      $(this).val( "*" );
      
    }

  })
	/**
	 * обработчик сворачивания/разворачивания дерева
	 */
	$(document).on('click', '.disclose', function() {
        var element = this;
        var id = this.id;

        $(this).closest('li').toggleClass('mjs-nestedSortable-collapsed').toggleClass('mjs-nestedSortable-expanded');
        $(this).toggleClass('ui-icon-plusthick').toggleClass('ui-icon-minusthick');

        
        $('body').find('div.selected').removeClass('selected');

        //для поиска по каталогу записываем id раскрытой папки
        if( $(this).hasClass('ui-icon-minusthick') ){
          current_category = id ;
          $('.category_id_search').prop('checked') ? $('.category_id_search').val( id ) : $('.category_id_search').val( '*' );
          $(this).closest('div').addClass('selected');
        } else {
          current_category = '*' ;
          $('.category_id_search').val( '*' );
          $(this).closest('div').removeClass('selected');
        }

        var that = $('#menuDiv_'+id);
        if (!this.hasAttribute("childLoaded")) {
            doAction('getCategoryChildren', {'category_id' : id}, 'An error occurred while loading - getCategoryChildren', {dataType: "json"}, function(data) {
                if (data != null) {
                    //var menuItem = document.getElementById("menuItem_"+id);
                    //menuItem.innerHTML += data;
                    $(that).after(data);
                    document.getElementById(id).setAttribute('childLoaded', true);
                }
            });
        }

        var category_name = $(that).find('.expandEditor').data('category_name');

        if( !$(that).find('.ui-icon-minusthick').length ){
            $('.products, .product-count').not('.users').empty();
            $(that).find('.find_product').removeClass('find_product');
            return false;
        }
        if($('#tabs-headers-1').find('li.active').data('page') == 1){
            $('#category_add_product').val(id);
            $('#category_add_product').data('category_name',category_name);
        }

        $(document).find('span.find_product').removeClass('find_product');
        $('span [data-find_category_id =' +id + ']').addClass('find_product');

        loadProduct(id, that, 1);

	});

	/**
	 * раскрыть просмотр категории
	 */
	$(document).on('click', '.expandEditor, .itemTitle', function(){
		/*var id = $(this).attr('data-id');
		 $('#menuEdit'+id).toggle();
		 $(this).toggleClass('ui-icon-triangle-1-n').toggleClass('ui-icon-triangle-1-s');*/
	});

	/**
	 * редактирование заголовка
	 */
	$(document).on('click', '.editMenu', function(){
		var $menuItem = $(this).closest('.menuDiv');

		$menuItem.find('.itemTitle').hide();
		$menuItem.find('.editTitle').show();
		$menuItem.find('.saveTitle').css('display', 'inline-block');
	});

	$(document).on('click', '.addItem', function(){
		var $menuItem = $(this).closest('div');

    $(this).hide();
		$menuItem.find('.editItem').show();
    $menuItem.find('.saveItem').show();
		$menuItem.find('.saveItem').css('display', 'inline-block');
	});

	/**
	 * запустить редактирование изображения
	 */
	$(document).on('click', '.editImg', function(){
		$('.category_img').val($(this).data('id'));
		$('.vendor_img').trigger('click');
	});

	/**
	 * обработчик редактирование изображения
	 */
	$(document).on('change','#vendor_img',function(){
		var formData = new FormData;
		var category_id = $('.category_img').val();
		formData.append('image', $("#vendor_img").prop('files')[0]);
		formData.append('category_id', category_id);
		$('#vendor_img').val('');
		$.ajax({
			url: 'index.php?vendor_img=true',
			data: formData,
			processData: false,
			contentType: false,
			type: 'post',
			dataType: 'json',
			success: function (data) {

				if(data.error){
					 errors(data.error);
				 }else{
					$('[data-category_id='+data.category_id+']').prev().attr('src',data.filename);
				}
				//	alertify.notify('Товар успешно добавлен', 'success', 5, function(){  console.log('dismissed'); });
			},
			error: function(){
				alert('Unknown upload error');
			}
		});
	});

	/**
	 * добавить категорию
	 */
	$(document).on('click', '.addCategory', function(){
		var id = $(this).attr('data-id');
		var type = $(this).attr('data-type');

		$(this).after('<div class="add-new-category"><input type="text" placeholder="имя категории..."><span title="Click to create category." data-id="' + id + '" data-type="' + type + '" class="saveNewCategory ui-icon ui-icon-check"></span></div>');
	});

	/**
	 * обработчик редактирования заголовка продукта
	 */
	$(document).on('click', '.productTitle', function(){
		var id = $(this).attr('data-id');
		attributeForm(id);
	});

	function waitcategory(data, i, product_id){
		if ($('[data-category_id= ' + data['categories'][i] +']').length == 0) {
			setTimeout(function(data, i, product_id){
				waitcategory(data, i, product_id);
			}, 200, data, i, product_id);
		}
		else
		{
			var cnt = data['categories'].length;

			if(data['categories'][cnt-1] == data['categories'][i]){
				//console.log('waitcategory', product_id);
				//alert(product_id);
				if (product_id != undefined) {
					G_LOCATE_product_id = product_id;
				}
			}

			if($('[data-category_id= ' + data['categories'][i] +']').prev('img').length){
				$('[data-category_id= ' + data['categories'][i] +']').prev().prev().click();
			}else{
				$('[data-category_id= ' + data['categories'][i] +']').prev().click();
			}
			if(data['categories'][cnt-1] == data['categories'][i]){
				var category = $('span [data-find_category_id =' +data['categories'][i] + ']');
				category.addClass('find_product');
				var destination = category.offset().top;
				//$('#tree').animate( { scrollTop: destination - 300 }, 800 );
				//console.log('category item',  category);
				//console.log('category.offset().top',  category.offset().top);
				//console.log('scrollTop', destination - 200);
				if (product_id == undefined) {
					$('#tabs-content-1').children(0).animate( { scrollTop: destination - 200 }, 200 );
				}
				//if (product_id != undefined) {
				//	setTimeout(function(data, i){
				//		waitproduct(data, i, product_id);
				//	}, 200, data, i);
				//
				//}

			}
		}
	}

	function categoryClick(category_id, product_id){
		doAction('getCategoryParent', {'category_id' : category_id}, 'An error occurred while loading', {dataType: "json"}, function(data) {
			$(document).find('span.find_product').removeClass('find_product');
			$('.collapse-all').click();
			var cnt = data['categories'].length;
			//console.log(data);
			for(var i = 0; i < cnt; i++){

				if ($('[data-category_id= ' + data['categories'][i] +']').length == 0) {
					setTimeout(function(data, i){
						waitcategory(data, i, product_id);
					}, 200, data, i, product_id);
				}
				else {
					//console.log('exists', data['categories'][i]);
					//if (i > 0)
					//	return;
					setTimeout(function(data, i, product_id){
						waitcategory(data, i, product_id);
					}, 200, data, i, product_id);
				}
			}
		});

	}

	function attributeForm(id, showProduct){
		//alert(id);
//console.log(id);
		showProduct = showProduct || '';
		addViewProduct(id);

		doAction('loadProductData', {'id' : id, 'lang' : window.lang , 'showProduct' : showProduct}, 'An error occurred while loading', {dataType: "json"}, function(data){
			//$('.ui-autocomplete').show();

			// только если в результате на продукт кликнули
			console.log('loadProductData', data);

			if(data['categories']){
				$('#search_product').trigger('blur');
				$('#tabs-headers-1').find('li').eq(data['product'].type).click();
				$('.collapse-all').click();
				var cnt = data['categories'].length;

				if (cnt > 0) {
					//alert('from attributeForm ' + data['product'].product_id);
					if (data['product']) {
						categoryClick(data['categories'][cnt-1], data['product'].product_id);
					} else
					{
						categoryClick(data['categories'][cnt-1]);
					}
				}

				/*
				for(var i = 0;i<cnt ; i++){
					// orig
					//$('[data-category_id= ' + data['categories'][i] +']').prev().click();

					//tests
					$('#menuDiv_' + data['categories'][i] + '>span').eq(0).click();

					//$('#menuDiv_' + data['categories'][i] + '>span').eq(0).click();
					//console.log($('#menuDiv_' + data['categories'][i] + '>span').eq(0).trigger('click'));
					//console.log($('#menuDiv_' + data['categories'][i] + '>span').eq(0).click());


					if(data['categories'][cnt-1] == data['categories'][i]){
						//console.log(123);
						//
						setTimeout(function(){
							$('#menuItem_' + data['categories'][cnt-1]).find('[data-find_id='+id+']').addClass('find_product');
							var destination = $('#menuItem_' + data['categories'][cnt-1]).find('[data-find_id='+id+']').offset().top;
							$('#tree').animate( { scrollTop: destination - 300 }, 800 );
						}, 1500);
					}
				}
				*/
			}

			var default_img = "this.src='img/no-image.jpg'";
			var description = data.product.description;
			var additional_materials = '<span>'+ i18next.t('tree_addinfo') +'</span><p>'+data.product.additional_materials+'</p>';
			additional_materials += '<span>'+ i18next.t('tree_sertificats') + '</span><p>'+data.product.certificates+'</p>';
			var img = '<img width="200px" src="'+data.product.vendor_photo_link+'" onerror='+default_img+' ><p>'+ description + '</p>';
			var rowData = {
				'attributes' : data.product.attributes.split('::')
			};
      var price_block = '<span>Цена: </span><p><a href="'+data.product.vendor_link+'" target="_blank">' + data.product.price + " " + data.product.currency +'</a></p>';

			var components = '<p>' + i18next.t('tree_complect') + '</p><ul>';
			for (var key in data.product.components) {
				var product_name = data.product.components[key]['name'];
				if(data.product.components[key]['components'] === undefined){

					components += '<li>'+ product_name +'</li>';
				}else{
					components +=  product_name + '<li><ul>';
					for (var i in data.product.components[key]['components']) {

						var product_name = data.product.components[key]['components'][i]['name'];
						components += '<li>'+ product_name +'</li>';
					}
					components += '</li></ul>';
				}


			}

			components += '</ul>';

			var source   = $("#optsTableTmpl").html();
			var template = Handlebars.compile(source);
			//console.log(template);

			var $tab = $('#tabs-content').find('div');

			$tab.eq(0).html(img);
			$tab.eq(1).html(rowData.attributes != '' ? template(rowData) : '');
			$tab.eq(3).html(components);
			$('#product_info_block').html(additional_materials);
      $('#product_price_block').html(price_block);
			$('[data-i18n]').localize();

			$("table.optstable").sortTable();
		});
	}

	function addViewProduct(product_id) {
		doAction('addViewProduct', {
			'product_id': product_id,
			//'user_id':1,
			'user_id':G_USER_ID,
			'lang':window.lang,
		}, 'An error occurred while loading', {dataType: "html"}, function (data) {
		});
	}

	/**
	 * обработчик добавления категории
	 */
	$(document).on('click', '.saveNewCategory', function(){
		var id = $(this).attr('data-id');
		var type = $(this).attr('data-type');
		var title = $(this).prev().val();

		if( !title ){
			alert('Пожалуйста укажите имя категории');

			return false;
		}

		doAction('add', {'id' : id, 'title' : title, 'lang' : window.lang, 'type' : type}, 'An error occurred while creating a category', null, function(){
			$('.add-new-category').remove();

			rebuild_tree(lang, type, G_USER_ID);
		});


	});

	/**
	 * обработчик сохранения заголовка
	 */
	$(document).on('click', '.saveTitle', function(){
		var id = $(this).attr('data-id');
		var title = $(this).closest('.menuDiv').find('.editTitle').val();
		var $menuItem = $(this).closest('.menuDiv');

		$menuItem.find('.itemTitle').text(title);
		$menuItem.find('.itemTitle').show();
		$menuItem.find('.editTitle').hide();
		$(this).css('display', 'none');

		doAction('edit', {'id' : id, 'title' : title}, 'An error occurred while saving');
	});

  	/**
	 * обработчик сохранения
	 */
	$(document).on('click', '.saveItem', function(){
		var field_id = $(this).attr('data-field');
		var $list = $(this).closest('div');

    var title = $list.find('.editItem').val();
    var $item = $('<li class="list-group-item">' + title + '</li>');
    $list.find('ul').append( $item );

    $list.find('.addItem').show();
		$list.find('.editItem').hide();
    $list.find('.saveItem').hide();
		$(this).css('display', 'none');

		doAction('addItemToDictionary', {'field_id' : field_id, 'title': title, 'user_id': user_id }, 'An error occurred while saving',  null, function(data){
      if(data){
        $list.find('ul').append( $item );
      } else {
        alert('Соответствие не добавлено'); //TODO  MSG
      }
    });
	});

	/**
	 * обработчик удаления заголовка категории
	 */
	$(document).on('click', '.deleteMenu', function(){
		var id = $(this).attr('data-id');
		var result = confirm('DELETE ?');
		if (result) {
			doAction('delete', {'id' : id}, 'An error occurred while deleting');
			$('#menuItem_'+id).remove();
		}
	});

	/**
	 * открыть загрузчик xml файла для импорта
	 */
	$(document).on('click', '.loadXMLMenu', function(){
		var id = $(this).attr('data-id');
		//doAction('loadXml', {'id' : id}, 'An error occurred while uploading');
		$('#root-category').val(id);
		$('#xml-file-upload').trigger('click');
	});

	/**
	 * начать импорт файла
	 */
	$(document).on('click','file_load',function(){
		$('#xml-file-upload').click();
	});

	/**
	 * свернуть все дерево
	 */
	$(document).on('click', '.collapse-all', function(){
		var $disclose = $(this).parent().find('.sortable').find('.disclose');
		$disclose.closest('li').removeClass('mjs-nestedSortable-expanded').addClass('mjs-nestedSortable-collapsed');
		$disclose.removeClass('ui-icon-minusthick').addClass('ui-icon-plusthick');
		var category_type = $('#tabs1').find('.active').data('page');
		$('#tabs-content-'+category_type).find('.products').not('.users').remove();
	});

	/**
	 * создать компонент
	 */
	$(document).on('click', '.create-good', function(){
		var source   = $("#addProductTmpl").html();
		var template = Handlebars.compile(source);
		var data = {};
		data.job = false;

		$('.tests').empty().css('display','block').append(template(data) );
		/*alertify.alert()
			.setting({
				'label':'Сохранить',
				'message': template() ,
				'frameless': true

			}).setHeader('<em> Добавить продукт </em> ').set('resizable', true).resizeTo('60%', '60%').show();*/

		$('[data-i18n]').localize();

		if($('#category_add_product').val() != '001'){
			$('#inputCategory').val($('#category_add_product').data('category_name'));
		}
		$(function() {
			if ($('#inputCategory').attr('name') != undefined) {
				var category_type = $('#tabs1').find('.active').data('page');
				$.ajax({
					url: 'index.php?get_categories=true&category_type='+category_type + '&user_id='+G_USER_ID,
					type: 'get',
					dataType: 'json',
					success: function (data) {
						$('#inputCategory').autocomplete({
							source: data.categories,
							select: function (event, ui) {
								$('#inputCategory').data('id', ui.item.category_id); // set category_id
							}
						}).data("ui-autocomplete")._renderItem = function(ul, item) {
							return $("<li>").data("ui-autocomplete-item", item).append("<a>" + item.label + "</a>").appendTo(ul);
						};
					}
				});
			}
		});

	});

	/**
	 * импортировать прайс пользователя
	 */
	$(document).on('click', '.import-price', function(){
		var source   = $("#importCustomPrice").html();
		var template = Handlebars.compile(source);
    var data = dictionary_data;

    $('.tests').empty().css('display','block').append(template(data) );
	});








	/*$(document).on('click', '.save', function(){
		$('.tests').css('display','none');
	});*/


	/**
	 * создать работу
	 */
	$(document).on('click', '.create-job', function(){
		var source   = $("#addProductTmpl").html();
		var template = Handlebars.compile(source);
        //$('.tests').append(template);
		var data = {};
		data.job = true;
		$('.tests').empty().css('display','block').append(template(data));
        var tplData = {
			'job' : true,
			'work_types' : work_types[window.lang],
			'work_calc_types' : work_calc_types[window.lang],
			'work_type_time' : work_type_time[window.lang]
		};

		$('[data-i18n]').localize();

		/*alertify.alert()
			.setting({
				'label':'Сохранить',
				'message': template(tplData) ,
				'frameless': true

			}).setHeader('<em> Добавить работу </em> ').set('resizable', true).resizeTo('60%', '60%').show();
*/
		$(function() {
			var category_type = $('#tabs1').find('.active').data('page');
			if ($('#inputCategory').attr('name') != undefined) {
				$.ajax({
					url: 'index.php?get_categories=true&category_type='+category_type + '&user_id='+G_USER_ID,
					type: 'get',
					dataType: 'json',
					success: function (data) {
						$('#inputCategory').autocomplete({
							source: data.categories,
							select: function (event, ui) {
								$('#inputCategory').data('id', ui.item.category_id); // set categoru_id
							}
						}).data("ui-autocomplete")._renderItem = function(ul, item) {
							return $("<li>").data("ui-autocomplete-item", item).append("<a>" + item.label + "</a>").appendTo(ul);
						};

					}
				});
			}

		});

	});


	/**
	 * обработчик редактирования товара
	 */
	$(document).on('click', '.editProduct', function(){
		var id = $(this).data('id');
		var type = $(this).closest('.products').prev().data('type');

		$('.component-list').empty();

		doAction('loadProductData', {'id' : id, 'lang' : window.lang}, 'An error occurred while loading', {dataType: "json"}, function(data){
			loadProductsByJSON(data, type);
		});

		loadComponentList(id, type);
	});

	/**
	 * обработчик удаления товара
	 */
	$(document).on('click', '.deleteProduct', function(){
		var id = $(this).data('id');

		$(this).parent().remove();

		doAction('deleteProduct', {'id' : id, 'user_id': G_USER_ID}, 'An error occurred while loading', {dataType: "json"}, function(data){

		});
	});


	/**
	 * открыть загрузчик фотографии
	 */
	$(document).on('click', '.no-image, .link', function(){
		$('.img_upload_btn').trigger('click');
	});



	$(document).on('click','.save_and_edit',function(){
		$('#open_edit_product').val('1');
		$('.product-add').submit();
	});
	/**
	 * обработчик добавления нового товара
	 */
	$(document).on('submit', '.product-add', function(e){
		var errors = [];

		e.preventDefault();

		$('.errors').remove();

		if( isValid() ){

			var $form = $(this);

			var name = $form.find('input[name=name]').val();
			var price = $form.find('input[name=price]').val();
			var qty = $form.find('input[name=qty]').val();
			var category_id = $form.find('input[name=category]').data('id');

			var sku = $form.find('input[name=sku]').val();
			var vendor = $form.find('input[name=vendor]').val();
			var units = $form.find('input[name=units]').val();

			if($('#category_add_product').val() != '001'){
				category_id = $('#category_add_product').val();
			}
			var type = 1;
			var formData = new FormData;

			formData.append('image', $("#inputImage").prop('files')[0]);
			formData.append('name', name);
			formData.append('sku', sku);
			formData.append('vendor', vendor);
			formData.append('units', units);
			formData.append('user_id', user_id);
			formData.append('price', price);
			formData.append('quantity', qty);
			formData.append('category_id', category_id);
			formData.append('lang', window.lang);

			if($form.data('type')){
				//alert('job');
				type = 2;
				formData.append('job', true);
				formData.append('work_time', $form.find('input[name=work_time]').val());
				formData.append('work_type', $form.find('select[name=work_type]').val());
				formData.append('work_type_time', $form.find('select[name=work_type_time]').val());
				formData.append('work_calc_type', $form.find('select[name=work_calc_type]').val());
			}

			$.ajax({
				url: 'index.php?product_add=true',
				data: formData,
				processData: false,
				contentType: false,
				type: 'post',
				dataType: 'json',
				success: function (data) {
					if($('[data-category_id = '+category_id+']').prev().hasClass('ui-icon-plusthick')){
						$('[data-category_id = '+category_id+']').prev().click();
					}
					//$('[data-category_id = '+category_id+']').prev().click();
					loadUserProduct(type);
					if(data.error){
						alert(error);
					}
					if( !$('#addAgain').prop('checked') ){
						$('.tests').css('display', 'none');
					}
				//	alertify.notify('Товар успешно добавлен', 'success', 5, function(){  console.log('dismissed'); });
					if($('#open_edit_product').val() == 1){
						$('#open_edit_product').val('');
						setTimeout(function(){
							$('.editProduct[data-id =' +data.product_id +  ']').trigger('click');
						}, 1000);
					}
					clearFields();

				},
				error: function(){
					alert('Unknown upload error');
				}
			});

		} else {
			var source   = $("#errorsTmpl").html();
			var template = Handlebars.compile(source);

			$(this).after( template({'errors' : errors}) );
		}

		/**
		 * валидация полей формы
		 */
		function isValid(){
			var name = $('input[name=name]').val();
			var category = $('input[name=category]').data('id');

			if( name === '' || name === undefined ){
				//errors.push('Имя не должно быть пустым.');
				errors.push(i18next.t('not_empty'));
			}

			if( category == 0 ){
				//errors.push('Выбирите существующую категорию или создайте новую.');
			}

			return (errors.length) ? false : true;
		}

		/**
		 * очистка полей формы
		 */
		function clearFields(){
			$form.find('input').each(function(){

				if( $(this).attr('id') === 'inputQty' ){
					return true;
				}

				$(this).val('');
			});
		}

	});

	/**
	 * обработчик редактирования товара и добавления компонентов
	 */
	$(document).on('submit', '.product-edit', function(e) {
		var errors = [];

		e.preventDefault();

		$('.errors').remove();

		var $form = $(this);

		var product_id = $form.data('id');
		var name = $form.find('input[name=name]').val();
		var sku = $form.find('input[name=sku]').val();
		var vendor = $form.find('input[name=vendor]').val();
		var units = $form.find('input[name=units]').val();
		var price = $form.find('input[name=price]').val();
		var qty = $form.find('input[name=qty]').val();
		var category_id = $form.find('input[name=category]').data('id');

		if (isValid()) {

			var formData = new FormData;

			formData.append('image', $("#editImage").prop('files')[0]);
			formData.append('product_id', product_id);
			formData.append('name', name);
			formData.append('sku', sku);
			formData.append('vendor', vendor);
			formData.append('units', units);
			formData.append('price', price);
			formData.append('quantity', qty);
			formData.append('category_id', category_id);
			formData.append('lang', window.lang);

			if($form.data('type')){
				formData.append('job', true);
				formData.append('work_time', $form.find('input[name=work_time]').val());
				formData.append('work_type', $form.find('select[name=work_type]').val());
				formData.append('work_calc_type', $form.find('select[name=work_calc_type]').val());
			}

			$.ajax({
				url: 'index.php?product_edit=true',
				data: formData,
				processData: false,
				contentType: false,
				type: 'post',
				dataType: 'json',
				success: function (data) {

					if (data.error) {
						alert(error);
					}

					clearFields();
					$('.tests').css('display', 'none');

					/*alertify.notify('Товар успешно изменен', 'success', 5, function () {
					});
*/
				},
				error: function () {
					alert('Unknown upload error');
				}
			});

			var components = {};

			// добавить компоненты
			$('.component-list').find('tr').each(function(item, value){

				$td = $(value).find('td');
				lang = window.lang;

				components[ $(value).data('id') ] = {
						'code' : $td.find('input[name=code]').val(),
						'name' : $td.find('input[name=name]').val(),
						'vendor' : $td.find('input[name=vendor]').val(),
						'type' : $td.find('select[name=type]').val(),
						'units' : $td.find('input[name=units]').val(),
						'quantity' : $td.find('input[name=quantity]').val(),
						'price' : $td.find('input[name=price]').val()
				}
			});

			$.ajax({
				url: 'index.php?components_edit=true',
				data: {
					'components': components,
					'product_id': product_id,
					'lang': lang
				},
				type: 'post',
				dataType: 'json',
				success: function (data) {
					$('.component-list').empty();
					//alertify.alert().close();
				},
				error: function () {
					alert('Unknown upload error');
				}
			});


		} else {
			var source = $("#errorsTmpl").html();
			var template = Handlebars.compile(source);

			$(this).after(template({'errors': errors}));
		}

		/**
		 * валидация полей формы
		 */
		function isValid(){
			var name = $('input[name=name]').val();
			var category = $('input[name=category]').data('id');

			if( name === '' || name === undefined ){
				//errors.push('Имя не должно быть пустым.');
				errors.push(i18next.t('not_empty'));
			}

			if( category == 0 ){
				//errors.push('Выбирите существующую категорию или создайте новую.');
				errors.push('Select an existing category or create a new one.');
			}

			return (errors.length) ? false : true;
		}

		/**
		 * очистка полей формы
		 */
		function clearFields(){
			$form.find('input').each(function(){

				if( $(this).attr('id') === 'inputQty' ){
					return true;
				}

				$(this).val('');
			});
		}
	});

	$(document).on('input','.changeValue',function(){
		var $this = $(this);
		var quant = $this.closest('tr').find('input[name=quantity]').val();
		var price = $this.closest('tr').find('input[name=price]').val();
		$this.closest('tr').find('.totalPrice').text((price*quant).toFixed(2));
		totalSumComponent();

	});



	/**
	 * загрузка продуктов выбранной категории
	 */
	$(document).on('click', '.menuDiv', function(){
        /*
		var that = this;
		var id = $(this).find('.expandEditor').attr('data-id');

		var category_name = $(this).find('.expandEditor').data('category_name');

		if( !$(that).find('.ui-icon-minusthick').length ){
			$('.products, .product-count').not('.users').empty();
			$(that).find('.find_product').removeClass('find_product');
			return false;
		}
		if($('#tabs-headers-1').find('li.active').data('page') == 1){
			$('#category_add_product').val(id);
			$('#category_add_product').data('category_name',category_name);
		}

		$(document).find('span.find_product').removeClass('find_product');
		$('span [data-find_category_id =' +id + ']').addClass('find_product');

		loadProduct(id, that, 1);
		*/
	});


	function loadProduct(category_id, that, page){
		doAction('loadProducts', {'id' : category_id, 'lang' : lang, 'limit': products_limit, 'page': page }, 'An error occurred while loading - loadProducts', {dataType: "json"}, function(data){

			$('.products, .product-count').not('.users').remove();
			var no_category = $(that).next('ol').length;

            if( data.products.length > 0 ){

				if( !$(that).parent().parent().hasClass('sortable') ){
					$(that).find('.editTitle').after('<span class="product-count">('+ data.products.length +')</span>');
				}

				var source   = $("#productsTmpl").html();
				var template = Handlebars.compile(source);

				if($('#showForm').val() == 0){
					data.showForm = 1;
				}

                //$(that).after( template(data) );

                //alert(no_category);
				if(no_category){
                    // на втором открытии
					$(that).next().after( template(data) );
				}else{
                    // на первом открытии
					$(that).after( template(data) );
				}
// for no_drag
/*
				$('ul.products li').attr({draggable: "true"});
				$('ul.products li').bind("dragstart", onstartdrag);
*/

				// такая же конструкция в popup.php
				if (G_LOCATE_product_id != 0)
				{
					console.log(G_LOCATE_product_id);
					if ($('span[data-id=' + G_LOCATE_product_id + ']').length > 0)
					//if ($('span[data-find_id=' + G_LOCATE_product_id + ']').length > 0)
					{
                        $('#tabs-content-1').children(0).animate( { scrollTop: 0 }, 1 );
                        setTimeout(function(){
                            var category = $('span[data-id=' + G_LOCATE_product_id + ']');
                            //var category = $('span[data-find_id=' + G_LOCATE_product_id + ']');
                            category.addClass('find_product');
                            var destination = category.offset().top;
                            $('#tabs-content-1').children(0).animate( { scrollTop: destination - 200 }, 100 );
                            G_LOCATE_product_id = 0;
                        }, 100);
					}
					else {
                        //alert('not found', G_LOCATE_product_id);
                        console.log('not found', G_LOCATE_product_id);
                        $('#tabs-content-1').children(0).animate( { scrollTop: 0 }, 1 );
                        setTimeout(function(){
							var category = $('span [data-find_category_id =' + category_id + ']');
							category.addClass('find_product');
							var destination = category.offset().top;
							$('#tabs-content-1').children(0).animate( { scrollTop: destination - 200 }, 100 );
                        }, 100);
					}
				}


				if($('#showForm').val() == 0){
					//console.log($(document).find('.editProduct,.deleteProduct').length);
					$(document).find('.editProduct,.deleteProduct').remove();
				}

				$('[data-i18n]').localize();

                doAction('getProductsCountByCategory', {'category_id' : category_id, lang: lang}, 'An error occurred while loading', {dataType: "json"}, function(cnt) {
                    if (cnt > products_limit) {
                        var pages = Math.ceil(cnt/products_limit);
                        var paginator = '<span class="paginator" data-id="'+category_id+'">';
                        paginator += '<span class="pag_option" onclick="pagination('+category_id+', 1);">&#9668;&#9668;</span>';
                        paginator += '<span class="pag_option" onclick="pagination('+category_id+', 2);">&#9668;</span>';
                        paginator += '<span class="pag_option2" id="pag_cur_page_'+category_id+'">'+page+'</span>';
                        paginator += '|';
                        paginator += '<span class="pag_option2" id="pag_total_pages_'+category_id+'">'+pages+'</span>';
                        paginator += '<span class="pag_option" onclick="pagination('+category_id+', 3);">&#9658;</span>';
                        paginator += '<span class="pag_option" onclick="pagination('+category_id+', 4);">&#9658;&#9658;</span>';
                        paginator += '</span>';
                        //alert(paginator);
                        $('#menuItem_'+category_id).find('.products').not('.users').append(paginator);
                        //$('.products').append(paginator);
                    }
                });

				$('#dropzone-container').mDropMaster({
					'onDrop' : function(dragObject){
						this.onLeave();
						$('#dropzone-container').addClass('move-completed');
						dragObject.show();
						var product_id = $(dragObject.elem).find('.productTitle').data('id')
						doAction('loadProductData', {'id' : product_id, 'lang' : window.lang , 'showProduct' : ''}, 'An error occurred while loading', {dataType: "json"}, function(data) {

						});
							},
					'onDragMove' : function(){
						$('#dropzone-container').addClass('move');
					},
					'onDragFail' : function(){
						$('#dropzone-container').removeClass('move');
					}

				});

			}

		});
	}

	/**
	 * сериализация дерева
	 */
	$(document).on('click', '#serialize', function(){
		serialized = $('ol.sortable').nestedSortable('serialize');
		$('#serializeOutput').text(serialized+'\n\n');
	});

	/**
	 * загрузка выбранной фотографии
	 */
	$(document).on('change', 'input.img_upload_btn', function(){
		readURL(this);
	});


	// jQuery убирает у объектов событий "лишние" свойства, поэтому, если мы хотим использовать HTML5
	// примочки вместе с jQuery, нужно включить для событий свойство dataTransfer.
	jQuery.event.props.push('dataTransfer');
	jQuery.event.props.push('pageX');
	jQuery.event.props.push('pageY');


	/**
	 * обработчик кнопки импорта файла
	 */
	$(document).on('change', '#xml-file-upload', function(){
		var max_file_size, file_size;

		max_file_size = $('#max_file_size').val();
		max_file_size = max_file_size.slice(0, -1);
		max_file_size *= 1000 * 1000;
		file_size = $("#xml-file-upload")[0].files[0].size;

		if( max_file_size < file_size ){
			$('#xml-file-upload').val('');
			errors('Превышен максимальный размер файла');
		}
	});

	/**
	 * загрузка файлов на сервер
	 */
	$(document).on('submit', '#formImport', function(e){
		e.preventDefault();
		$(document).find('.importError').remove('.importError');
		var $input = $("#xml-file-upload");
		var ext = $input.val().split('.');
		ext = ext[ext.length-1].toLowerCase();

		if (ext =='xls' || ext =='xlt' || ext =='xlsx' || ext =='xla'){
			var filedata = new FormData;
			filedata.append('file', $input.prop('files')[0]);

			$.ajax({
				url: 'index.php?upload=true',
				data: filedata,
				processData: false,
				contentType: false,
				type: 'post',
				dataType: 'json',
				success: function (data) {

					if(data.error.length != 0){
						errors(data.error);
					}else if(data.file == 'error'){
						errors('Ошибка при загрузке файла');
					}else{
						var lang = $('#importLanguage').val();
						$('#formImport').before('<img id="imgLoader" src="/img/loading1.gif">');
						recAjax(data.file, lang, 1);
					}
				},
				error: function(){
					alert('error');
				}
			});

		} else{
			errors('Неправильный формат файла');
		}
	});

	/**
	 * загрузка файлов на сервер
	 */
	$(document).on('submit', '#formImportXML', function(e){
		e.preventDefault();
		$(document).find('.importError').remove('.importError');
		var price_id = $("#price_id");

    if(price_id.val() != ''){

      $('#formImportXML').before('<img id="imgLoader" src="/img/loading1.gif">');

      var filedata = new FormData;
      filedata.append('price_id', price_id.val());

			$.ajax({
				url: 'index.php?importXML=true',
				data: filedata,
				processData: false,
				contentType: false,
				type: 'post',
//				dataType: 'json',
				success: function (data) {
          window.console.log(data);
					if(data){

					}else{
            errors('Ошибка при загрузке данных');
					}

          $(document).find('#imgLoader').remove();
				},
				error: function(){
          $(document).find('#imgLoader').remove();
					errors('Ошибка при загрузке данных');
				}
			});
    }

	});

  	/**
	 * загрузка файлов на сервер
	 */
	$(document).on('submit', '#formDictionary', function(e){
		e.preventDefault();

		var $input = $("#file-upload");
		var ext = $input.val().split('.');
		ext = ext[ext.length-1].toLowerCase();

    var root_folder= $("#root_folder").val();


		if (ext =='xls' || ext =='xlt' || ext =='xlsx' || ext =='xla'){
			var filedata = new FormData;
			filedata.append('file', $input.prop('files')[0]);

			$.ajax({
				url: 'index.php?upload=true',
				data: filedata,
				processData: false,
				contentType: false,
				type: 'post',
				dataType: 'json',
				success: function (data) {

					if(data.error.length != 0){
						errors(data.error);
					}else if(data.file == 'error'){
						errors('Ошибка при загрузке файла');
					}else{
						var lang = $('#importLanguage').val();
            doAction('saveCustomData', {'file' : data.file, 'lang' : lang, 'user_id': user_id, 'root_folder': root_folder}, 'An error occurred while loading', null, function(data){
                rebuild_tree(lang,1, user_id);
                $('.closeCustomPrice').trigger('click');
            });
					}
				},
				error: function(){
					alert('error');
				}
			});

		} else{
			errors('Неправильный формат файла');
		}
	});

	$(document).on('change', 'input#file-upload', function(){

		var name = $(this).val().split('\\') || '';
    name = name[name.length-1].toLowerCase();
    if(name == ''){
      name = 'Файл не выбран';
    }
    $(this).closest('div').find('#file-selected').text(name);
	});



	/**
	 * закрытие формы
	 */
	$(document).on('click', '.closeCustomPrice', function(){
     $('.tests').html('');
     $('.tests').css('display','none');
	});

	/**
	 * удаление компонента
	 */
	$(document).on('click', '.deleteComponent', function(){
		var component_id = $(this).closest('tr').data('id');
		var product_id = $('.product-edit').data('id');

		doAction('deleteComponent', {'component_id' : component_id, 'product_id' : product_id}, 'An error occurred while removing', {dataType: "json"}, function(data){
			$('.component-list').find('tr[data-id=' + data.component_id + ']').remove();
		});
	});


	$(document).on('click', '.cancel', function(){
		$('.tests').css('display', 'none');
	});


	/**
	 * загрузка списка компонентов
	 */
	function loadComponentList(product_id, type){
		doAction('loadComponents', {'product_id' : product_id, 'lang' : window.lang}, 'An error occurred while loading', {dataType: "json"}, function(list){
			list.components.forEach(function(value){
				appendProduct(event, value, type);
			});
		});
	}


	/**
	 * загрузка продуктов из JSON данных
	 */
	function loadProductsByJSON(data, type){
		type = type || data.product.type;

		var source   = $("#editProductTmpl").html();
		var template = Handlebars.compile(source);

		var tplData = {
			'img' : data.product.vendor_photo_link ? data.product.vendor_photo_link : 'no-image.jpg',
			'product_id' : data.product.product_id,
			'sku' : data.product.sku,
			'units' : data.product.units,
			'vendor' : data.product.vendor,
			'name' : data.product.name,
			'price' : data.product.price,
			'quantity' : data.product.quantity,
			'vendor_link' : data.product.vendor_link,
			'work_time' : data.product.work_time,
			'work_type' : data.product.work_type,
			'work_calc_type' : data.product.work_calc_type,
			'work_type_time' : data.product.work_type_time,
			'job' : type == 2,
			'work_types' : work_types[window.lang],
			'work_calc_types' : work_calc_types[window.lang]
		};

		//console.log(tplData);

		if (data.product.type == 2)
			tplData.job = true;
		else
			tplData.job = false;

		//console.log(data);

        //$('.tests').append(template);
		$('.tests').empty().css('display','block').append(template(tplData));

		$('[data-i18n]').localize();


		/*alertify.alert()
			.setting({
				'label':'Сохранить',
				'message': template(tplData) ,
				'frameless': true

			}).setHeader('<em> '+ (type == 1 ? "Добавить продукт" : "Добавить работу") +' </em> ').set('resizable', true).resizeTo('70%', '70%').show();
*/

		var $editCategory = $('#editCategory');
		$editCategory.data('id', data.product.category_id);

		doAction('loadCategory', {'id' : data.product.category_id, 'lang' : lang}, 'An error occurred while loading', {dataType: "json"}, function(data){
			$editCategory.val(data.category.name);
		});

		var category_type = $('#tabs1').find('.active').data('page');

		// get categories for autocomplete
		/*
		$.ajax({
			url: 'index.php?get_categories=true&category_type='+category_type + '&user_id='+G_USER_ID,
			type: 'get',
			dataType: 'json',
			success: function (data) {
				$('#editCategory').autocomplete({
					source: data.categories,
					select: function (event, ui) {
						$('#editCategory').data('id', ui.item.category_id); // set categoru_id
					}
				}).data("ui-autocomplete")._renderItem = function(ul, item) {
					return $("<li>").data("ui-autocomplete-item", item).append("<a>" + item.label + "</a>").appendTo(ul);
				};

			}
		});
		*/

		var typo = 2;

		$('#editProduct').autocomplete({
			'source': function(request, response) {
				$.ajax({
					url: 'index.php?searchComponents=true',
					type: 'post',
					data: { search : request ,lang :  window.lang},
					dataType: 'json',
					success: function(json) {
						response($.map(json.products, function(item) {
							return {
								label: item['label'],
								value: item['label'],
								product_id: item['id']
							}
						}));
					}
				});
			},
			minLength: 2,
			select: function (event, ui) {
				$('#editProduct').data('id', ui.item.product_id); // set product_id
				appendProduct(event, ui.item, type);
				$('#editProduct').val('');
			}
		});
	}



	/**
	 * добавления компонента в таблицу
	 */
	function appendProduct(event, data, type){
		var source   = $("#componentTmpl").html();
		var template = Handlebars.compile(source);
		doAction('loadProductData', {'id' : data.product_id, 'lang' : window.lang}, 'An error occurred while loading', {dataType: "json"}, function(data){

			data.product['total'] = data.product.quantity * data.product.price;
			data.product['product_types'] = product_types[window.lang];
			data.product['job'] = (type == 2);



			$('.component-list').append( template(data.product) );
			totalSumComponent();
		});
		$('[data-i18n]').localize();

	}

	function totalSumComponent(){
		var sum = $('#editPrice').val() * $('#editQty').val();
		$('.totalPrice').each(function(){
			sum += +$(this).text();
		});
		$('.allTotal').text(sum.toFixed(2));
	}


	/**
	 * Рекурсивный аякс для построчного чтения файла
	 * @param file
	 * @param lang
	 * @param start
	 * @param root
	 */
	function recAjax(file, lang, start, root){
		root = root || '';

		var $root = $('#root-category');

		if( $root.val() != '' ){
			root = $root.val();
			$root.val('');
		}
		var startTo = parseInt(start) + 999;
		errors('Загрузка товаров с ' + start + ' до ' + startTo);
		$.ajax({
			url: 'index.php?readfile=true',
			data: {
				file : file,
				lang : lang,
				start : start,
				root_category : root
			},
			type: 'post',
			dataType: 'json',
			success: function (data) {
				if(data.error.length != 0){
					errors(data.error);
					$(document).find('#imgLoader').remove();
				}else if (data.end == 'more'){
					recAjax(file, lang, startTo, data.baseCategory);
				} else if(data.end == 'end'){
					$(document).find('#imgLoader').remove();
					location.reload();
				}else{

					errors('Произошла ошибка');
					$(document).find('#imgLoader').remove();
				}
			},error: function(e,a,b){
				errors('Произошла ошибка');
				$(document).find('#imgLoader').remove();
			}
		});
	}

	/**
	 * смена языка импортируемого файла
	 */
	$(document).on('change', '#importLanguage', function(){
		lang = $(this).val();
		$('#lang_caregory').val(lang);
	});


});

// functions


function displayForm(show_form){
	$(document).find('.importError').remove('.importError');
	doAction('show_form', {'show_form' : show_form}, 'An error occurred while loading', {dataType: "json"}, function(data) {
		if(data.setting == 'true'){
			/*if(show_form == 0){
				$('.setting_form').hide();
				$('.editMenu,.deleteMenu,.editProduct,.deleteProduct').hide()
			}else if(show_form == 1){
				$('.setting_form').show();
			}*/
			location.reload();
		}else{
			errors(data.setting)
		}
	});

}


/**
 * Handlebars регистрация хелперов
 */
function handlebarsHelpers(){

	Handlebars.registerHelper('rowlist', function(items) {
		var out = "";
		var row;

		items.map(function(item) {
			row = item.split(':');

			out += '<tr><td>' + row[0] + '</td><td>' + row[1] + '</td></tr>';
		});

		return out;
	});

	Handlebars.registerHelper("select", function(value, options) {
		return options.fn(this)
			.split('\n')
			.map(function(v) {
				var t = 'value="' + value + '"'
				return ! RegExp(t).test(v) ? v : v.replace(t, t + ' selected="selected"')
			})
			.join('\n')
	});

}


/**
 * инициализация дерева
 */
function treeInit(type){
	var ns = $('.tab-piece').eq(type).find('ol.sortable').nestedSortable({
		forcePlaceholderSize: true,
		handle: 'div',
		helper:	'clone',
		items: 'li',
		opacity: .6,
		placeholder: 'placeholder',
		revert: 250,
		tabSize: 25,
		tolerance: 'pointer',
		toleranceElement: '> div',
		maxLevels: 10,
		isTree: true,
		expandOnHover: 70,
		startCollapsed: false,
		update: saveOrder
	});
}



/**
 * сохранения порядка и глубины категорий дерева
 */
function saveOrder(){
	var serialized = $('.tab-piece').find($(this)).nestedSortable('serialize');

	doAction('update', serialized, 'An error occurred while saving');
}

/**
 * перестройка всего дерева
 */
function rebuild_tree(lang, type, _G_USER_ID){
	window.lang = lang;
	doAction('build', {'lang' : lang, 'type' : type, 'user_id': _G_USER_ID}, 'An error occurred while building of tree', {dataType: "json"}, function(data){
		var $treeContent = $('#tabs-content-1').find('.tab-piece');

		loadUserProduct(type);
		$treeContent.eq(type).html(data.tree);
		if (type == 0){
			$treeContent.eq(0).prepend('<img class="collapse-all" src="img/toggle-collapse_blue.png" alt="">');
		} else if(type == 1) {
			//$treeContent.eq(1).prepend('<button class="create-good button">Добавить товар</button>');
			//$treeContent.eq(1).prepend('<input type="text" placeholder="Название категории"><button class="create-category button" data-type="1">Добавить Категорию</button>');
			$treeContent.eq(1).prepend('<img class="collapse-all" src="img/toggle-collapse_blue.png" alt="">');
      $treeContent.eq(1).prepend('<button class="import-price button" >Импорт</button>');
            $treeContent.eq(1).prepend('<button class="create-category button" data-type="1" data-i18n="AddFolder"></button>');
			$treeContent.eq(1).prepend('<button class="create-good button" data-i18n="AddProductToTree"></button>');

		} else {
			//$treeContent.eq(2).prepend('<button class="create-job button" data-i18n="AddWorkItem">Добавить работу</button>');
			//$treeContent.eq(2).prepend('<input type="text" placeholder="Название категории"><button class="create-category button" data-type="2">Добавить Категорию</button>');
			$treeContent.eq(2).prepend('<img class="collapse-all" src="img/toggle-collapse_blue.png" alt="">');
            $treeContent.eq(2).prepend('<button class="create-category button" data-type="2" data-i18n="AddFolder"></button>');
            $treeContent.eq(2).prepend('<button class="create-job button" data-i18n="AddWorkItem"></button>');

		}

		$('[data-i18n]').localize();
		$('#accordion-js h2').each(function () {
			$(this).append('<img src="img/vrsaquo.png" alt=""/>');
		});

		if($('#showForm').val() == 1){
			treeInit(0);
			treeInit(1);
			treeInit(2);
		}
	});
}



/**
 * вывод ошибок в импорт
 */
function errors(text){
	$(document).find('.importError').remove('.importError');
	$('#formImport').after('<h2 class="importError" style="color:red">' + text +'</h2>');
}


/**
 * send ajax with different action
 *
 * @param action
 * @param data
 * @param msg
 * @param options
 * @param callback
 */
function doAction(action, data, msg, options, callback){

	var dataType = 'html', isLoading;

	if( (options !== undefined) && options && typeof options === "object" ){
		dataType = (Object.prototype.hasOwnProperty.call(options, 'dataType')) ? options.dataType : 'html';
	}

	$.ajax({
		url: 'index.php?' + action + '=true',
		type: 'post',
		async: true,
		data: data,
		dataType: dataType,
		beforeSend: function(){

			isLoading = true;

			//$('.loader').show(300);

			document.onclick = function(event) {
				// Ловим событие для Interner Explorer
				var event = event || window.event;

				/*if(event !== undefined && isLoading){
					$('.loader').css({
						'left': event.pageX,
						'top': event.pageY
					});
				}*/

			};

		},
		complete: function(){
			//$('.loader').hide(300);
			isLoading = false;
		},
		success: callback,
		error: function(){
			alert(msg);
		}
	})
}

/**
 * send ajax with different action
 *
 * @param action
 * @param data
 * @param msg
 * @param options
 * @param callback
 */
function doAction_load(action, data, msg, options, callback){

	var dataType = 'html', isLoading;

	if( (options !== undefined) && options && typeof options === "object" ){
		dataType = (Object.prototype.hasOwnProperty.call(options, 'dataType')) ? options.dataType : 'html';
	}

	$.ajax({
		url: 'index.php?' + action + '=true',
		type: 'post',
		data: data,
		async: false,
		dataType: dataType,
		complete: function(){
			isLoading = false;
		},
		success: callback,
		error: function(){
			alert(msg);
		}
	});
}

/**
 * отладчик дерева
 */
function dump(arr,level) {
	var dumped_text = "";
	if(!level) level = 0;

	//The padding given at the beginning of the line.
	var level_padding = "";
	for(var j=0;j<level+1;j++) level_padding += "    ";

	if(typeof(arr) == 'object') { //Array/Hashes/Objects
		for(var item in arr) {
			var value = arr[item];

			if(typeof(value) == 'object') { //If it is an array,
				dumped_text += level_padding + "'" + item + "' ...\n";
				dumped_text += dump(value,level+1);
			} else {
				dumped_text += level_padding + "'" + item + "' => \"" + value + "\"\n";
			}
		}
	} else { //Strings/Chars/Numbers etc.
		dumped_text = "===>"+arr+"<===("+typeof(arr)+")";
	}
	return dumped_text;
}

/**
 * чтение url изображения из type="file"
 */
function readURL(input) {
	if (input.files && input.files[0]) {
		var reader = new FileReader();

		reader.onload = function (e) {
			$(input).siblings('img').attr('src', e.target.result);
		};

		reader.readAsDataURL(input.files[0]);
	}
}

function loadViewProduct(){
	doAction('loadViewProduct', {'user_id' : user_id, 'lang' : window.lang}, 'An error occurred while loading', {dataType: "json"}, function(data){

		var source   = $("#productsTmpl").html();
		var template = Handlebars.compile(source);

		$('.view_product').html( template(data) );
// for no_drag
/*
		$('ul.products li').attr({draggable: "true"});
		$('ul.products li').bind("dragstart", onstartdrag);
*/
		$('[data-i18n]').localize();

	})
};
function loadUserProduct(type){
	doAction('loadUserProduct', {'user_id' : user_id, 'lang' : window.lang, 'type' : type}, 'An error occurred while loading', {dataType: "json"}, function(data){

		var source   = $("#productsTmpl").html();
		var template = Handlebars.compile(source);
		$('.user_product'+type).html( template(data) );
		$('.user_product'+type).find('.products').addClass('users');

		//alert('loadUserProduct');
// for no_drag
/*
		$('ul.products li').attr({draggable: "true"});
		$('ul.products li').bind("dragstart", onstartdrag);
*/

		$('[data-i18n]').localize();

	})
};


/**
 * плагин табов
 */
(function($){

	jQuery.fn.lightTabs = function(options){

		var createTabs = function(){
			tabs = this;
			i = 0;

			$('.parts_block').hide();
			showPage = function(i){
				$(tabs).children("div").children("div").hide();
				$(tabs).children("div").children("div").eq(i).show();
				$(tabs).children("ul").children("li").removeClass("active");
				$(tabs).children("ul").children("li").eq(i).addClass("active");
				if(i == 1 || i == 2){
					loadUserProduct(i);
				}
				if(i == 3){
					loadViewProduct();
				}
			};

			showPage(0);

			$(tabs).children("ul").children("li").each(function(index, element){

				$(element).attr("data-page", i);
				i++;
			});

			$(tabs).children("ul").children("li").click(function(){

				window.tabs = $(this).closest('.tabs');
				if($(this).closest('.tabs').attr('id') == 'tabs1' && (parseInt($(this).attr("data-page")) == 0 || parseInt($(this).attr("data-page")) == 3)){
					$('.parts_block').hide();
				}else if ($(this).closest('.tabs').attr('id') == 'tabs1'){
					$('.parts_block').show();
				}
				showPage(parseInt($(this).attr("data-page")));
			});
		};
		return this.each(createTabs);
	};
})(jQuery);

/**
 * плагин сортировки таблицы
 */
(function($){
	"use strict";
	$.fn.sortChildren=function(options){
		var  settings=$.isPlainObject(options)
			?$.extend({ // Object parameter, new style
			cmp:function(){return $(this).text()},
			ignoreFirst:0, // ignore some of the first few children
			ignoreLast:0, // ignore some of the last few children
			reverse:false  // sort in reverse
		},options)
			:{ // cmp,ignoreFirst,ignoreLast,reverse // old parameters
			cmp:arguments[0],
			ignoreFirst:arguments[1],
			ignoreLast:arguments[2],
			reverse:arguments[3]
		};
		return this.each(function(){
			var self=$(this),
				children=$.makeArray(self.children()),
				first=children.splice(0,settings.ignoreFirst||0),
				last=children.splice(-(settings.ignoreLast||0),settings.ignoreLast);
			$.each(children.sort(settings.cmp), function(i, child){
				if (settings.reverse)
					self.prepend(child);
				else
					self.append(child);
			});
			self.prepend(first).append(last);
		})
	};
	$.fn.sortTable=function(){ // an example solution  for simple table sorting
		return this.each(function(){
			var sorts = $("thead th",this).map(function(i){
				return $.sortKeys.call(0,$(this).data("sort")||[{"childAlpha":i}])
			}).get();
			$("thead th",this).click(function() {
				var tbody = $(this).closest("table").children("tbody"),
					column = $(this).index(),
					alreadySorted = $(this).hasClass("sorted");
				if(alreadySorted) $(this).toggleClass("reversed").siblings().removeClass("sorted reversed");
				$(this).addClass("sorted").siblings().removeClass("sorted reversed");
				tbody.sortChildren({
					cmp: sorts[column],
					reverse: $(this).hasClass("reversed")
				})
			}).attr("title", "sortable").first().click()
		})
	};
	$.sortKeys=function(a){ // easier way to build sort compare functions.
		if ($.isArray(a))
			return $.sortFunc($.map(a,function(key,i){
				return $.map(key,function(childNo,sort){
					return $.sortFunc[sort](childNo)
				})
			}));
		else
			return $.sortFunc($.map(arguments,function(argument,i){
				return argument?$.sortFunc[argument](i):undefined
			}))
	};
	$.sortFunc=$.extend(function (sorts,options){
		var options= options||[];
		var funcs=sorts.map(function(v){
			if ($.isFunction(v)){
				options.push('');
				return v
			} else {
				var vS=v.split("::"),
					option={},
					o=vS.length==1?[]:vS[1].toLowerCase().split(" ");
				o.map(function(v){option[v]=true});
				options.push(option);
				return new Function("return "+vS[0])
			}
		});
		return function(a,b){
			for (var i=0;i<funcs.length;i++){
				var func=funcs[i]
					,aV= func.call(a)
					,bV= func.call(b);
				if (options[i].numeric){
					aV=+aV;
					bV=+bV
				}
//             console.log("sort",aV,bV,reverse)
				var reverse=options[i].reverse?-1:+1;
				if (aV < bV)
					return -1*reverse;
				else if (aV > bV)
					return 1*reverse;
			}
			return 0
		}
	},{
		numeric: function() {
			return +$(this).text();
		},
		alpha: function() {
			return $(this).text();
		},
		reverseNumeric: function() {
			return - +$(this).text();
		},
		reverseAlpha: "$(this).text()::reverse",

		childNumeric: function(i) {
			return function(){ return  parseInt($.trim($(this).children().eq(i).text()))}
		},
		childReverseNumeric: function(i) {
			return function(){ return -parseInt($.trim($(this).children().eq(i).text()))}
		},
		childAlpha: function(i) {
			return function(){ return           $.trim($(this).children().eq(i).text())}
		}
	})
})(jQuery);




