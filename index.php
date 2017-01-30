<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: X-Requested-With, Content-Type");
//header("X-Frame-Options: ALLOW-FROM http://local.online.cableproject.net");
//header("X-Frame-Options: ALLOW-FROM local.online.cableproject.net:8084");
//header("X-Frame-Options: ALLOW-FROM *");
$start = microtime(true);
//ini_set('error_reporting', E_ALL);
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);


require_once 'Classes/tree.php';
require_once('Classes/joint.import.php');
require_once('Classes/route.php');
require_once('Classes/popup.php');

if (isset($_GET['user_id'])) {
	$G_USER_ID = intval($_GET['user_id']);
}
else
{
	$G_USER_ID = 1;
}

$lang = (isset($_GET['lang'])) ? $_GET['lang'] : 'ru';
$tree_html_0 = $tree_html->build_tree($lang, 0, $G_USER_ID);
$tree_html_1 = $tree_html->build_tree($lang, 1, $G_USER_ID);
$tree_html_2 = $tree_html->build_tree($lang, 2, $G_USER_ID);

$price_option = $tree_html->build_price_option();
$dictionary_data = $tree_html->get_dictionary_data($G_USER_ID);
$time = microtime(true) - $start;
//printf('Скрипт выполнялся %.4F сек.', $time);
//var_dump(memory_get_usage());die;
$x1 = explode('.', $_SERVER['HTTP_HOST']);
$xdomain = '';

for ($i = 1; $i < count($x1); $i++)
{
	if ($i == (count($x1) - 1))
	{
		$xdomain = $xdomain.$x1[$i];
	}	
	else	
	{
		$xdomain = $xdomain.$x1[$i].'.';
	}
}
?>
<html lang="en">
<head>

<script type="text/javascript">
   var G_USER_ID = <?php echo($G_USER_ID); ?>;
   var G_LOCATE_product_id = 0;
   var dictionary_data = <?php echo $dictionary_data; ?>;
</script>   

    <meta charset="utf-8">
    <title>Sortable categories</title>
    <meta content="" name="description">
    <input type="hidden" value="<?=$lang?>" id="current_language">
    <input type="hidden" value="<?=$settings['show_form']?>" id="showForm">


    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <link rel="stylesheet" href="//code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css"/>
    <link rel="stylesheet" href="css/bootstrap.min.css"/>
    <link rel="stylesheet" href="css/alertify.min.css"/>
    <link rel="stylesheet" href="css/default.min.css"/>
    
	<?php if ($settings['show_form'] != 0) {
	?>
		<link rel="stylesheet" href="css/style.css?<?php echo filemtime('css/style.css');?>"/>
	<?php
	}
	else
	{
	?>
		<link rel="stylesheet" href="css/style_no_drag.css?<?php echo filemtime('css/style_no_drag.css');?>"/>
	<?php
	}
	?>
    <script type="text/javascript" src="js/bootstrap.min.js"></script>
    <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.0/jquery-ui.js"></script>
    <script type="text/javascript" src="js/handlebars-v4.0.5.js"></script>
    

	<?php if ($settings['show_form'] != 0) {
		echo '<script type="text/javascript" src="js/jquery.mjs.nestedSortable.js"></script>';
	}
	?>	

    <script type="text/javascript" src="js/jstree.min.js"></script>
   <!-- <script type="text/javascript" src="js/alertify.min.js"></script>-->

	<?php if ($settings['show_form'] != 0) {
	?>
		<script type="text/javascript" src="js/tree.js?<?php echo filemtime('js/tree.js');?>"></script>
	<?php
	}
	else
	{
	?>
		<script type="text/javascript" src="js/tree_no_drag.js?<?php echo filemtime('js/tree_no_drag.js');?>"></script>
	<?php
	}
	?>

 	<?php if ($settings['show_form'] != 0) {
		echo '<script type="text/javascript" src="js/m_drag_master.js"></script>';
	}
	?>	

    <script src="locale/i18next.min.js"></script>
    <script src="locale/i18next-jquery.min.js"></script>
    <script src="locale/i18nextXHRBackend.min.js"></script>

    <script id="errorsTmpl" type="text/x-handlebars-template">
        <ul class="errors">
            {{#each errors}}
            <li class="error">
                {{this}}
            </li>
            {{/each}}
        </ul>
    </script>

    <script id="productsTmpl" type="text/x-handlebars-template">
        <ul class="products">
            {{#each products}}
            <li class="product drag-element mjs-nestedSortable-no-nesting mjs-nestedSortable-disabled">
               <img src="{{vendor_photo_link}}" onerror="this.src='img/no-image.jpg'" width="50px" class="inline">

                <span class="inline productTitle mjs-nestedSortable-no-nesting mjs-nestedSortable-disabled"
                      data-id="{{product_id}}" data-find_id="{{product_id}}"> {{name}} </span>
                <span title="Click to edit item." data-id="{{product_id}}"
                      class="editProduct ui-icon ui-icon-pencil"></span>
                <span title="Click to delete item." data-id="{{product_id}}"
                      class="deleteProduct ui-icon ui-icon-closethick"></span>
              
            </li>
            {{/each}}
        </ul>
    </script>

    <script id="optsTableTmpl" type="text/x-handlebars-template">
        <table class="optstable">
            <thead>
            <tr>
                <th data-i18n="treeAttributesData"></th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            {{#rowlist attributes}}{{/rowlist}}
            </tbody>
        </table>
    </script>

    <script id="priceTableTmpl" type="text/x-handlebars-template">
        Цена - 200$
        <br>
        Дата обновления цены - {{price}}
    </script>

<script type="text/javascript">
   var xdomain = "<?php echo($xdomain); ?>";
   //xdomain = xdomain;
   //alert(xdomain);
   //xdomain = "local.online.cableproject.net";
   
   xdomain = 'tree-test-data';
   document.domain = xdomain;
   
   
   //document.addEventListener("dragend", function( event ) {
   //   // reset the transparency
   //   console.log('dragend');
   //   parent.window.postMessage({message: {cmd: 'drag_data', data: ''}}, '*');
   //}, false);
</script>   

    
	<script type="text/javascript">
	
    </script>	
	  
</head>

<body crossOrigin="anonymous">
<div>
    <input id="job_product_search" type="search" data-i18n="[placeholder]searchProducts"
           placeholder="">
    <a href="#" class="gear"><img src="img/gear.png" alt=""/></a>
</div>
<?php if($settings['show_form'] == 1) { ?>

    <form action="<?= $_SERVER['REQUEST_URI'] ?>" method="post"
          class="setting_form ">
        <input type="hidden" name="count_quantity">
        <input type="submit" class="button" value="Пересчитать количество товаров">
    </form>
    <form action="" method="post" class="setting_form " id="formImportXML">
        <label for="">Прайс</label>
        <select name="price_id" id="price_id">
          <?php echo $price_option; ?>
        </select>
        <input type="submit" class="button" value="Обновить данные">
    </form>
    <div><label for="">Переключение языка</label>
    <select name="" id="change_language">
        <option value="ru">Русский</option>
        <option value="en">Английский</option>
    </select>

    <form action="<?= $_SERVER['REQUEST_URI'] ?>" method="post"
          class="setting_form ">
        <label for="">Новая категория</label>
        <input type="text" name="create_category" id="create_category_name" placeholder="Название категории">
        <select name="category_type" id="category-type">
            <option value="0">]</option>
            <option value="1"></option>
            <option value="2"></option>
        </select>
        <input type="hidden" name="language" id="lang_caregory" value="ru">
        <input type="submit" class="button">
    </form>
    <form action="index.php" method="post" id="formImport" enctype="multipart/form-data"
          class="setting_form ">
        <label class="button" for="xml-file-upload">Выбрать файл</label>
        <input type="file" id="xml-file-upload" name="file" style="display: none">
        <input type="hidden" id="max_file_size" value="<?= ini_get('post_max_size') ?>"/>
        <input type="hidden" id="root-category" name="root_category">
        <select name="language" id="importLanguage">
            <option value="ru">Русский</option>
            <option value="en">Английский</option>
        </select>
        <input type="submit" class="button">
    </form>

    <?php
    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo '<h2 class="importError" >' . $error . '</h2></br>';
        }
    }
    ?>
    <input type="text" id="search_category" placeholder="Вендор">
    <input type="text" id="search_product" placeholder="Название или Артикул">
    <span class="resetSearch">x</span>
    <select name="" id="search_type">
        <option value="all">Везде</option>
        <option value="0">В общем каталоге</option>
        <option value="1">В моем каталоге</option>
    </select>

<?php } ?>
<div class="loader"></div></div>

<section id="tree">
    <?php if ($tree_html) { ?>
        <div id="tabs1" class="tabs">
            <ul id="tabs-headers-1">
                <li data-i18n="catalog"></li>
                <li data-i18n="myProducts"></li>
                <li data-i18n="MyJobCost"></li>
                <li data-i18n="History"></li>
            </ul>
            <div id="tabs-content-1">
                <div class="tab-piece">
                    <!--<img class="collapse-all" src="img/toggle-collapse_blue.png" alt="">-->
                    <button class="collapse-all" id="collapse"></button>
                    <?php echo $tree_html_0; ?>
                </div>
                <div class="tab-piece">
                    <img class="collapse-all" src="img/toggle-collapse_blue.png" alt="">
                    <button class="create-good button" data-i18n="AddProductToTree"></button>
                    <button class="create-category button" data-type="1" data-i18n="AddFolder"></button>
                    <button class="import-price button" >Импорт</button>
                    <?php echo $tree_html_1; ?>
                    
                </div>
                <div class="tab-piece">
                    <img class="collapse-all" src="img/toggle-collapse_blue.png" alt="">
                    <button class="create-job button" data-i18n="AddWorkItem"></button>
                    <button class="create-category button" data-type="2" data-i18n="AddFolder"></button>
                    <?php echo $tree_html_2; ?>
                </div>
                <div class="tab-piece view_product">

                </div>
            </div>
        </div>
    <?php } ?>
</section>

<input type="file" value="test" class="vendor_img divHide" id="vendor_img">
<input type="hidden" value="" class="category_img" id="category_img">
<input type="hidden" value="001" id="category_add_product">



<?php if ($tree_html) { ?>
    <div id="tabs2" class="tabs">
        <ul id="tabs-headers">
            <li class="underImg"><img src="img/1.png" alt="" data-i18n="[title]Photo" title=""/><span data-i18n="Photo"></span></li>
            <li class="underImg"><img src="img/2.png" alt="" data-i18n="[title]treeAttributes" title=""/><span data-i18n="Attr"></span></li>
            <li class="underImg"><img src="img/3.png" alt="" data-i18n="[title]Prices" title=""/><span data-i18n="Prices"></span></li>
            <li class="underImg parts_block"><img src="img/4.png" alt="" data-i18n="[title]Complected" title=""/><span data-i18n="Comp"></span></li>
            <li class="underImg"><img src="img/5.png" alt="" data-i18n="[title]Information" title=""/><span data-i18n="Info"></span></li>
        </ul>
        <div id="tabs-content">
            <div></div>
            <div></div>
            <div id="product_price_block"></div>
            <div id="product_components" class="parts_block"></div>
            <div id="product_info_block"></div>

        </div>
    </div>
<?php } ?>
<div class="tests" style="
    display: none;
    height: calc(95% - 330px);
    position: absolute;
    width: 328px;
    top: 353px;
    left: 0;
    border: 1px solid #D8D8D8;
    background-color: white;    "></div>
<p class="divHide1">Drag the product into the rectangle:</p>

<div id="dropzone-container" class="divHide1">

</div>

</body>
</html>
<script type="text/javascript">
    function getParameterByName(name) {
        //console.log(location.search);
        name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
        var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
            results = regex.exec(location.search);
        return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
    }
	
	var loadfrom = getParameterByName('parent');

    var lang = getParameterByName('lang');
	if (lang == '' || lang == null || lang == undefined ) {
		lang = 'ru';
	}

	function listener(event) {
		var msg = event.originalEvent.data.message;
		if (msg.cmd == 'localization_data' || msg.cmd == 'localization_data_change') {
			//alert(msg.cmd);
			//console.log(msg);
			if (msg.cmd == 'localization_data') {
				window.location.hash = event.originalEvent.origin;
				var res_data = msg.data;
			}
			else {
				lang = msg.data.lang;
				var res_data = msg.data.res_data;
			}	
			if (msg.cmd == 'localization_data_change') {
				$('#change_language').val(lang);
				rebuild_tree(lang,0, G_USER_ID);
				rebuild_tree(lang,1, G_USER_ID);
				rebuild_tree(lang,2, G_USER_ID);

				//console.log('res_data', res_data.translation);
				//i18next.addResources(lang, 'translation', res_data.translation);
				//$('[data-i18n]').localize();
				//i18next.language = lang;
				//console.log(i18next.t(['12Ports']));
				//console.log(i18next);


			}	
			
			//console.log(res_data);
			
			i18next.
				init({
					lng: lang,
					fallbackLng: lang,
					resources: 
					{
						[lang]: res_data
					},
/*					resources: 
					{
						ru: {
						  translation: {
							"catalog": "1"
						  }
						},
						en: {
						  translation: {
							"catalog": "2"
						  }
						}
					},
*/
					debug: false
				});

			i18nextJquery.init(i18next, $, {
				tName: 't', // --> appends $.t = i18next.t
				i18nName: 'i18n', // --> appends $.i18n = i18next
				handleName: 'localize', // --> appends $(selector).localize(opts);
				selectorAttr: 'data-i18n', // selector for translating elements
				targetAttr: 'data-i18n-target', // element attribute to grab target element to translate (if diffrent then itself)
				optionsAttr: 'data-i18n-options', // element attribute that contains options, will load/set if useOptionsAttr = true
				useOptionsAttr: false, // see optionsAttr
				parseDefaultValueFromContent: true // parses default values from content ele.val or ele.text
			});
			i18next.on('loaded', function (loaded) {

				$('[data-i18n]').localize();
				$('#accordion-js h2').each(function () {
					$(this).append('<img src="img/vrsaquo.png" alt=""/>');
				});
			});
			
			$('[data-i18n]').localize();
			//console.log(i18next.t(['12Ports']));
			
			$('#accordion-js h2').each(function () {
			   $(this).append('<img src="img/vrsaquo.png" alt=""/>');
			});

		}
	}
	if (loadfrom == 'ocad')
	{
		$(window).on("message onmessage", listener);
	}
	else
	{
		i18next.
			use(i18nextXHRBackend).
			init({
				lng: 'ru',
				fallbackLng: "en",
				debug: true,
				backend: {
					loadPath: 'http://widgets.cableproject.net/release/locale/get_locale.php?lang={{lng}}'
				}
			});
		i18nextJquery.init(i18next, $, {
			tName: 't', // --> appends $.t = i18next.t
			i18nName: 'i18n', // --> appends $.i18n = i18next
			handleName: 'localize', // --> appends $(selector).localize(opts);
			selectorAttr: 'data-i18n', // selector for translating elements
			targetAttr: 'data-i18n-target', // element attribute to grab target element to translate (if diffrent then itself)
			optionsAttr: 'data-i18n-options', // element attribute that contains options, will load/set if useOptionsAttr = true
			useOptionsAttr: false, // see optionsAttr
			parseDefaultValueFromContent: true // parses default values from content ele.val or ele.text
		});
		i18next.on('loaded', function (loaded) {
			$('[data-i18n]').localize();
			$('#accordion-js h2').each(function () {
				$(this).append('<img src="img/vrsaquo.png" alt=""/>');
			});
		});
	}	

</script>

