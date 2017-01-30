<script id="addProductTmpl" type="text/x-handlebars-template">

    {{#if job}}
    <h2 data-i18n="AddWorkItem">Добавить работу</h2>
    {{else}}
    <h2 data-i18n="AddProductToTree" class="AddProduct">Добавить товар</h2>
    {{/if}}

    <form class="product-add" data-type="{{job}}">

        <div>

            <div class="form-group">
                <label for="inputSku" data-i18n="sku">SKU:</label>
                <input type="text" name="sku" class="form-control" id="inputSku"  data-i18n="[placeholder]EnterSKU" placeholder="Введите артикул">
            </div>

            <div class="form-group">
                <label for="inputName" data-i18n="Item">Наименование:</label>
                <input type="text" name="name" class="form-control" id="inputName"  data-i18n="[placeholder]EnterProduct" placeholder="Введите название">
            </div>

            <div class="form-group">
                <label for="inputUnits" data-i18n="units_ed">Units:</label>
                <input type="text" name="units" class="form-control" id="inputUnits"  placeholder="">
            </div>

            <div class="form-group">
                <label for="inputVendor" data-i18n="vendor_ed">Units:</label>
                <input type="text" name="vendor" class="form-control" id="inputVendor"  placeholder="">
            </div>

            <div class="form-group">
                <label for="inputPrice" data-i18n="ItemPrice">Цена:</label>
                <input type="text" name="price" class="form-control" id="inputPrice" data-i18n="[placeholder]EnterPrice" placeholder="Введите цену">
            </div>

         <!--   
            <div class="form-group">
                <label for="inputQty">Количество:</label>
                <input type="number" min=0 name="qty" value="1" class="form-control" id="inputQty">
            </div>
          -->
        </div>

        {{#if job}}

        <div>
            <div class="form-group">
                <label for="workTime">Work time in min:</label>
                <input type="number" name="work_time" class="form-control" id="workTime" min="0">
                <label for="workTypeTime">Work time:</label>
                <select name="work_type_time" id="workTypeTime">
                    {{#select work_type_time}}
                    {{#each work_type_time}}
                    <option value="{{@key}}">
                        {{this}}
                    </option>
                    {{/each}}
                    {{/select}}
                </select>
            </div>
            <div class="form-group">
                <label for="workType">Work type:</label>
                <select name="work_type" id="workType">
                    {{#select type}}
                    {{#each work_types}}
                    <option value="{{@key}}">
                        {{this}}
                    </option>
                    {{/each}}
                    {{/select}}
                </select>
            </div>
            <div class="form-group">
                <label for="workCalcType">Calc type:</label>
                <select name="work_calc_type" id="workCalcType">
                    {{#select type}}
                    {{#each work_calc_types}}
                    <option value="{{@key}}">
                        {{this}}
                    </option>
                    {{/each}}
                    {{/select}}
                </select>
            </div>
        </div>

        {{/if}}

        <div>
            <div class="form-group">
                
                  <div class="ui-widget">
                      <label for="inputCategory" data-i18n="AddFolder">Доб. кат.:</label>
                      <input type="text" name="category" class="form-control fill-category" data-id="0" id="inputCategory">
                  </div>
                        
            </div>
        </div>

        <div>
            <div class="form-group">
                <img src="img/no-image.jpg" class="no-image">
                <label class="link" for="inputImage" data-i18n="AddFoto">Добавить фото:</label>
                <input type="file" name="image" class="form-control img_upload_btn" id="inputImage">

            </div>
        </div>

        <div class="clearfix"></div>
        <table>

        </table>
        <input type="hidden" value="0" id="open_edit_product">
        <div class="form-group right-corner">
            <input id="addAgain" name="add_again" type="checkbox"><label for="addAgain"> create new</label>
            <br>
            <!--<a class="btn button save_and_edit">Подробнее</a>-->
            <button class="btn button save" data-i18n="save">Сохранить</button>
            <button class="cancel" data-i18n="cancel">Отмена</button>
        </div>
    </form>

</script>

<script id="editProductTmpl" type="text/x-handlebars-template">

    {{#if job}}
    <h2>Edit work</h2>
    {{else}}
    <h2>Edit product</h2>
    {{/if}}

    <form class="product-edit" data-id="{{product_id}}" data-type="{{job}}">

        <div>

            <div class="form-group">
                <label for="inputName" data-i18n="sku">SKU:</label>
                <input type="text" name="sku" value="{{sku}}" class="form-control" id="inputSku"  data-i18n="[placeholder]EnterSKU" placeholder="Введите артикул">
            </div>

            <div class="form-group">
                <label for="editName" data-i18n="Item">Наименование:</label>
                <input type="text" name="name" value="{{name}}" class="form-control" id="editName" data-i18n="[placeholder]EnterProduct" placeholder="Введите название">
            </div>

            <div class="form-group">
                <label for="inputUnits" data-i18n="units_ed">Units:</label>
                <input type="text" name="units" value="{{units}}" class="form-control" id="inputUnits"  placeholder="">
            </div>

            <div class="form-group">
                <label for="inputVendor" data-i18n="vendor_ed">Units:</label>
                <input type="text" name="vendor" value="{{vendor}}" class="form-control" id="inputVendor"  placeholder="">
            </div>

            <div class="form-group">
                <label for="editPrice" data-i18n="ItemPrice">Цена:</label>
                <input type="text" name="price" value="{{price}}" class="form-control" id="editPrice" data-i18n="[placeholder]EnterPrice" placeholder="Введите цену">
            </div>
            <!--
            <div class="form-group">
                <label for="editQty">Count:</label>
                <input type="number" min=0 name="qty" value="{{quantity}}" class="form-control" id="editQty">
            </div>
            -->
        </div>

        {{#if job}}

        <div>
            <div class="form-group">
                <label for="workTime">Work time in min:</label>
                <input type="number" name="work_time" value="{{work_time}}" class="form-control" id="workTime" min="0">
                <label for="workTypeTime">Work time:</label>
                <select name="work_type_time" id="workTypeTime">
                    {{#select work_type_time}}
                    {{#each work_type_time}}
                    <option value="{{@key}}">
                        {{this}}
                    </option>
                    {{/each}}
                    {{/select}}
                </select>

            </div>

            <div class="form-group">
                <label for="workType">Work type:</label>
                <select name="work_type" id="workType">
                    {{#select work_type}}
                    {{#each work_types}}
                    <option value="{{@key}}">
                        {{this}}
                    </option>
                    {{/each}}
                    {{/select}}
                </select>
            </div>

            <div class="form-group">
                <label for="workCalcType">Calc type:</label>
                <select name="work_calc_type" id="workCalcType">
                    {{#select work_calc_type}}
                    {{#each work_calc_types}}
                    <option value="{{@key}}">
                        {{this}}
                    </option>
                    {{/each}}
                    {{/select}}
                </select>
            </div>
        </div>

        {{/if}}

        <div>
            <!--
            <div class="form-group">
                <div class="ui-widget">
                    <label for="editCategory" data-i18n="AddFolder">Добавить категорию:</label>
                    <input type="text" name="category" class="form-control fill-category" data-id=0 id="editCategory">
                </div>
            </div>
            -->
            <!--
            <div class="form-group">
                    <a href="{{vendor_link}}" target="_blank">Vendor link</a>
            </div>
            -->
            <!--<div class="form-group">
                <div class="ui-widget">
                    <label for="editProduct">Добавить компонент:</label>
                    <input type="text" name="product" class="form-control fill-product" data-id=0 id="editProduct">
                </div>
            </div>-->
        </div>
        <div>
            <div class="form-group">
                <img src="{{img}}" onerror="this.src='img/no-image.jpg'" class="no-image thumbEditImage">
                <br>
                <label class="link" for="inputImage" data-i18n="AddFoto">Добавить фото:</label>
                <input type="file" name="image" class="form-control img_upload_btn" id="editImage">

            </div>
        </div>

        <!--
        <div class="clearfix"></div>
        <table>
            <thead>

            <tr>
                <th>Articul</th>
                <th>Name</th>
                <th>Photo</th>
                <th>Vendor</th>
                {{#if job}}
                <th>Exps.</th>
                {{/if}}
                <th>Type</th>
                <th>Meas.</th>
                <th>Count</th>
                <th>Price.</th>
                <th>Sum</th>
                <th>Action</th>
            </tr>

            </thead>
            <tbody class="component-list">
            </tbody>
        </table>
        <div class="form-group">
            <div class="ui-widget">
             <!--   <label for="editProduct">Добавить компонент:</label> 
                <input type="text" name="product" class="form-control fill-product" data-id=0 id="editProduct">
            </div>
        </div>
        <p> Sum: <span class="allTotal"></span></p>
        -->

        <div class="form-group right-corner">
            <button class="btn button save" data-i18n="save">Сохранить</button>
            <button class="cancel" data-i18n="cancel">Отмена</button>
        </div>
    </form>
</script>

<script id="componentTmpl" type="text/x-handlebars-template">
    <tr data-id="{{product_id}}">
        <td><input type="text" size="8" name="code" value="{{sku}}"></td>
        <td><input type="text" size="25" name="name" value="{{name}}"></td>
        <td> <img src="{{vendor_photo_link}}" onerror="this.src='img/no-image.jpg'" width="100px" class="inline"></td>
        <td><input type="text" size="15" name="vendor" value="{{vendor_category}}"></td>
        {{#if job}}
        <td><input type="text" size="8" name="rate" value="{{rate}}"></td>
        {{/if}}
        <td>
            <select name="type">
                {{#select type_product}}
                {{#each product_types}}
                <option value="{{@index}}">
                    {{this}}
                </option>
                {{/each}}
                {{/select}}
            </select>
        </td>
        <td><input type="text" size="8" name="units" value="{{units}}"></td>
        <td><input type="text" size="8" class="changeValue" name="quantity" value="{{quantity}}"></td>
        <td><input type="text" size="8" class="changeValue" name="price" value="{{price}}">{{currency}}</td>
        <td class="totalPrice">{{total}}</td>
        <td><span title="Click to delete item." class="deleteComponent ui-icon ui-icon-closethick"></td>
    </tr>
</script>

<script type="text/javascript">

    function pagination(id, type) {
        var that = $('#menuDiv_'+id);
        var page = parseInt(document.getElementById('pag_cur_page_'+id).innerHTML);
        var pages = parseInt(document.getElementById('pag_total_pages_'+id).innerHTML);
        if (type == 1) {
            var new_page = 1;
        } else if (type == 2) {
            var new_page = page - 1;
        } else if (type == 3) {
            var new_page = page + 1;
        } else if (type == 4) {
            var new_page = pages;
        }
        if ((new_page >= 1) && (new_page <= pages) && (new_page != page))
            loadProduct(id, that, new_page);
    }

    function loadProduct(category_id, that, page){
        doAction('loadProducts', {'id' : category_id, 'lang' : lang, 'limit': products_limit, 'page': page }, 'An error occurred while loading', {dataType: "json"}, function(data){

            //alert('loadProducts');

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
				if(no_category){
					$(that).next().after( template(data) );
				}else{
					$(that).after( template(data) );
				}

                // такая же конструкция в tree_no_drag.js
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
                        }, 200);
                    }   
                    else {
                        //alert('not found', G_LOCATE_product_id); 
                        console.log('not found', G_LOCATE_product_id);
                    }

                }   


                if($('#showForm').val() == 0){
					$('ul.products li').attr({draggable: "true"});
					$('ul.products li').bind("dragstart", onstartdrag);
                    //console.log($(document).find('.editProduct,.deleteProduct').length);
                    $(document).find('.editProduct,.deleteProduct').remove();
                }
					
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
                        //$('.products').append(paginator);
                        $('#menuItem_'+category_id).find('.products').append(paginator);
                    }
                });

                if($('#showForm').val() != 0) {
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
            }

        });
    }


</script>

<script id="importCustomPrice" type="text/x-handlebars-template">


    <h2  class="AddProduct">Список соответствия значений</h2>
 
     
     
  <div class="panel panel-default">
  
    {{#each this}}
  
      <div class="panel-heading">
        <h4 class="panel-title">
          <a data-toggle="collapse" data-parent="#accordion"   href="#field_{{this.id}}">
          {{this.name}}
          </a>
        </h4>
      </div>
      <div id="field_{{this.id}}" class="panel-collapse collapse">
        <div class="panel-body">
          <ul>
          
          {{#each this.dict}}
            <li class="list-group-item">{{this.title}}</li>
          {{/each}} 
              
          </ul> 
          <button type="button" class = "addItem" class="btn btn-primary">Добавить</button>      
          <input type="text" class="editItem"></span><span title="Click to save item." data-field="{{this.id}}" class="saveItem ui-icon ui-icon-check"></span>
        </div>
      </div>

    {{/each}}
     
  </div>

  <form  id="formDictionary" role="form">
    
    <div class="form-group">
      <label class="button" for="file-upload">Выберите файл</label>
      <input type="file" id="file-upload" style="display: none">
      <p class="help-block" id="file-selected">Файл не выбран</p>
      <div class="clearfix"></div>
      <label for="root_folder">Поместить в:</label>
      <input type="text" id="root_folder" placeholder="Новый каталог">  
    </div>
    <button type="submit" class="btn btn-default">Импорт</button>
    <button type="button" class="btn btn-default pull-right closeCustomPrice">Закрыть</button>
  </form>

</div>
   

</script>
