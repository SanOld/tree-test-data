<?php

require_once('model.php');
require_once('sphinx_model.php');

class Tree extends model{

	private $categories;
	private $product_categories;
  private $prices;
  public $ent = 0;
	public $errors;
  


	public function build_tree($lang = 'ru', $type = 0, $user_id, $cats = null, $parent_id = 0){

		$settings = $this->getSettings();
		if($settings['show_form'] == 1) {
			$editMenu = 'editMenu ui-icon ui-icon-pencil';
			$deleteMenu = 'deleteMenu ui-icon ui-icon-closethick';
		}else{
			$editMenu = '';
			$deleteMenu = '';
		}

		if(is_null($cats)){
			$this->categories = $this->getCategories($lang, $type, $user_id);
		}

		if( is_array($this->categories) && !empty($this->categories[$parent_id]) ){
            $this->ent = $this->ent+1;

			$tree = '<ol>';
			$products_count = '';

			if ($parent_id == 0){
				$tree = '<ol class="sortable ui-sortable mjs-nestedSortable-branch mjs-nestedSortable-expanded" id="sort-cats-menu">';
			}

			foreach( $this->categories[$parent_id] as $cat ){
				if ( $parent_id == 0 ){
					$products_count = '['.$cat['count_products'].']';
				}

				//if(!empty($cat['image'])){
				//	$catImg = '<img src="upload/vendor/'.$cat['image'].'" alt="" class="folder_img  ">';
				//	$productText = '<span >' .$products_count . '</span>';
				//}else{
					$catImg = '';
					$productText = '<span data-id="' . $cat['category_id'] .'" data-find_category_id="' . $cat['category_id'] .'" class="itemTitle">' . $cat['name'] . ' ' . $products_count . '</span>';
				//}

				$tree .= '<li style="display: '. ( isset($cat['has_product']) ? "list-item" : "none" ) .';" class="mjs-nestedSortable-branch mjs-nestedSortable-collapsed" id="menuItem_' . $cat['category_id'] .'" data-foo="bar">';
                $tree .= '<div class="menuDiv" data-type="'.$type.'" id="menuDiv_'.$cat['category_id'].'">
						   <span title="Click to show/hide children" id="' . $cat['category_id'] .'" class="disclose ui-icon ui-icon-plusthick class_for_img '. ( !empty($products_count) ? " uniq_img" : "" ) .'">
						   <span></span>
						   </span>
						   '.$catImg.'
						   <span title="Click to show/hide item editor" data-id="' . $cat['category_id'] .'" data-category_id = "' . $cat['category_id'] .'" data-category_name = "' . $cat['name'] .'" class="expandEditor">
						   <span></span>
						   </span>
						   <span>'.$productText.'

						   <input type="text" value="' . $cat['name'] . '" class="editTitle"></span><span title="Click to save item." data-id="' . $cat['category_id'] .'" class="saveTitle ui-icon ui-icon-check"></span>
						   <span title="Click to delete item." data-id="' . $cat['category_id'] .'" class="'.$deleteMenu.'">
						   <span></span>
						   </span>
						   <label title="Click to load image." for="vendor_img" data-id="' . $cat['category_id'] .'" class="editImg ui-icon ui-icon-image"></label>
						   <span title="Click to edit item." data-id="' . $cat['category_id'] .'" class="'.$editMenu.'"></span>
						   <span title="Click to add a category." data-id="' . $cat['category_id'] .'" data-type="'.$type.'" class="addCategory ui-icon ui-icon-plus"></span>
						   <span title="Click to load xml file." data-id="' . $cat['category_id'] .'" class="loadXMLMenu ui-icon ui-icon-document"></span>

						   </span>
						   <div id="menuEdit' . $cat['category_id'] .'" class="menuEdit hidden">
							   <p>
								   Content or form, or nothing here. Whatever you want.
							   </p>
						   </div>
					   </div>';
				//$tree .=  $this->build_tree( $lang, $type, $user_id, $this->categories, $cat['category_id'] );
				$tree .= '</li>';
			}

			$tree .= '</ol>';
			if($type == 1 || $type == 2) {
				$tree .= '<div class="user_product'.$type.'"> </div>';
			};

		} else return null;

		return $tree;
	}

	public function update_tree($data){
		return $this->saveMenu($data);
	}

	public function edit_item($id, $title){
		$this->editMenuItem($id, $title);
	}

	public function add_item($id, $title, $lang, $type){
		$this->addMenuItem($id, $title, $lang, $type);
	}

	public function remove_item($id){
		$this->removeMenuItem($id);
	}

	public function load_products($id, $lang, $limit, $page){
		$json = array();
		$json['products'] = $this->getProducts($id, $lang, $limit, $page);
		//$products = $this->getProducts($id, $lang);

		/*foreach ($products as $product){

			$Headers = @get_headers($product['vendor_photo_link']);
			//var_dump($Headers);die;
			if(!strpos('200', $Headers[0]) && !file_exists($product['vendor_photo_link'])) {
				$product['vendor_photo_link'] = 'img/no-image.jpg';
			}
			$json['products'][] = $product;
		}*/


		return json_encode($json);
	}

	public function load_product_data($data, $lang){

		$json = array();

		$json['product'] = $this->getProduct($data, $lang);
		$json['product']['components'] = $this->get_components_tree($data['id'],$lang);
		//$json['product']['components'] = $this->get_components($data['id'],$lang);
		//unset($json['product']['components'][$data['id']]);
		if(!empty($data['showProduct'])){
			$json['categories'] = $this->getProductCategories($json['product']['category_id']);
		}
		return json_encode($json);
	}

	public function getProductCategories($category_id = 1,$categorySearch = false)
	{

		if($category_id != 0){
			$this->product_categories[] = $category_id;
		}
		$result = $this->getCategory($category_id);
		if($result['category_id'] != $result['parent_id']){
			$this->getProductCategories($result['parent_id']);
		}
		if($categorySearch){
			$json['categories'] = array_reverse($this->product_categories);
			return json_encode($json);
		}else{
			return array_reverse($this->product_categories);
		}

	}


	public function get_categories($data){

		$json = array();

		$json['categories'] = $this->getCategoriesAll($data);
		return json_encode($json);
	}

	public function get_products($data){

		$json = array();

		$json['products'] = $this->getProductsAll($data);
		return json_encode($json);
	}
  
  public function build_price_option(){
    $text = '';
    $prices = $this->getPricesAll();
    $text .= '<option value="">&nbsp;</option>';
    
    foreach($prices as $k1=>$v1){
      $text .= "<option value='".$v1['id']."'>".$v1['name']."</option>";
    }

		return $text;
	}
  
  public function get_dictionary_data($G_USER_ID){
    $json = array();

		$json = $this->getDictionaryData($G_USER_ID);
		return json_encode($json); 
  }

	public function search_components($data)
	{
		$json = array();
		$json['products'] = $this->getSearchProducts($data);
		return json_encode($json);
	}

	public function search_products($data){
		$json = array();
		$products = $this->getSearchProducts($data);

		$categories = $this->getSearchCategories($data);
		$cnt = count($categories);
		for ($i = 0; $i<$cnt;$i++){
			$tmp_cat = $this->getRootName($categories[$i]['root_category']);
			$categories[$i]['vendor_category'] = $tmp_cat['name'];
			$categories[$i]['quantity'] = 'no';
			$categories[$i]['image'] = 'img/normal_folder.png';
		}
		$result = array_merge($categories, $products);
		return json_encode($result);
	}
	public function search_categories($data){
		$json = array();
		$json['categories'] = $this->getSearchCategories($data);
		return json_encode($json);
	}

	public function product_add_or_edit($product_data, $files, $edit = false){

		if( !$product_data ){
			return false;
		}

		$json = array();

		if ( $this->imgValidator($files) ) {

			$file_info = $this->getFileInfo($files);

			$upload_dir = 'upload/products/';

			if(!empty($product_data['job'])){
				$upload_dir = 'upload/jobs/';
			}

			$json = array('error'=> '','file' => '');
			$json['upload_error'] = $files['image']['error'];
			$json['file_info'] = $files['image'];


			$image_path =  uniqid() . '.' . $file_info['ext'];

			//echo $upload_dir.'<br>';
			//echo $files['image']['tmp_name'].'<br>';
			//echo $upload_dir.$image_path.'<br>';

			if (is_dir($upload_dir) && is_writable($upload_dir)) {
				$move = move_uploaded_file($files['image']['tmp_name'], $upload_dir.$image_path);
			} else{
				$json['error'] = '1.папка несуществует или нехватает прав для записи';

				return json_encode($json);
			}

			if($move){
				$json['file'] = $file_info['file'];
			} else{
				$json['error'] = 'Файл не загружен в папку';
			}

		}

		$product_data['currency'] = 'USD';

		$type = !empty($product_data['job']) ?  2 : 1;

		$img_empty = 'img/no-image.jpg';

		if( $edit ){
			$img_empty = false;
		}

		$product_data['vendor_photo_link'] = !empty($image_path) ? $upload_dir.$image_path : $img_empty;

		if( $edit ){
			/* добавить обработчик удаления картинки */

			$this->editCustomProduct($product_data, $type);
		} else {
			$json['product_id'] =  $this->addCustomProduct($product_data, $type);
		}

		return json_encode($json);
	}

	public function vendor_img($vendor_data, $files){

		if( !$vendor_data ){
			return false;
		}

		$json = array();

		if ( $this->imgValidator($files) ) {

			$file_info = $this->getFileInfo($files);
			$upload_dir = 'upload/vendor/';
			$json = array('error'=> '','file' => '');
			$json['upload_error'] = $files['image']['error'];
			$json['file_info'] = $files['image'];

			$image_path =  uniqid() . '.' . $file_info['ext'];
			
			if (!is_dir($upload_dir))			
			{
				//echo $upload_dir.'<br>';
				//exit;
			}	
			//echo $files['image']['tmp_name'].'<br>';
			//echo $upload_dir.$image_path.'<br>';
			
			if (is_dir($upload_dir) && is_writable($upload_dir)) {
				$move = move_uploaded_file($files['image']['tmp_name'], $upload_dir.$image_path);
			} else{
				$json['error'] = '2.папка несуществует или нехватает прав для записи';

				return json_encode($json);
			}

			if($move){
				$json['file'] = $file_info['file'];
				$json['filename'] = $upload_dir.$image_path;
				$json['category_id'] = $vendor_data['category_id'];
			} else{
				$json['error'] = 'Файл не загружен в папку';
			}
		}
		$vendor_data['image'] = $image_path;

		$this->addCategoryImage($vendor_data);

		return json_encode($json);
	}

	public function rebuild_tree($lang, $type, $user_id)
	{
		$json = array();

		$json['tree'] = $this->build_tree($lang, $type, $user_id);
		return json_encode($json);
	}


	public function change_setting($data)
	{
		$json = $this->changeSettings($data);
		return json_encode($json);
	}
	public function count_quantity()
	{
		$categories = $this->getParentsCategory();

		foreach($categories as $category){
			if( $p_cnt = $this->getChildrenProductsCount($category['root_category'], $category['language']) ){
				$count =  $p_cnt ;

			} else if ( $p_cnt = $this->getProductsCount($category['root_category'], $category['language']) ){
				$count = $p_cnt;

			} else {
				$count = 0;
			}

			$this->updateCount($category['root_category'],$count);
		}
		header('Location:' . $_SERVER['HTTP_REFERER']);

	}

	public function viewProducts($data)
	{
		$result = $this->addViewProduct($data);
	}

	public function getViews($data)
	{
		$json = array();
		$json['products'] = $this->getViewProducts($data);
		return json_encode($json);
	}
	public function getUserProduct($data)
	{
		//return null;
		$json = array();
		$json['products'] = $this->getUserTypeProduct($data);
		return json_encode($json);
	}




	public function load_category($id)
	{
		$json = array();

		$json['category'] = $this->getCategory($id);
		return json_encode($json);
	}

	public function components_edit($data)
	{
		$json = array();
		if(isset($data['components'])){

		foreach ($data['components'] as $component_id => $item) {
			$component = $this->getComponent($component_id, $data['product_id']);

			if ($component){
				$this->updateComponent($item, $component_id, $data['lang']);
			} else {
				$this->addComponent($item, $component_id, $data['product_id'], $data['lang']);
			}
			}
		}

		return json_encode($json);
	}

	public function load_components($product_id, $lang)
	{
		$json = array();

		$json['components'] = $this->get_components($product_id, $lang);
		//unset($json['components'][$product_id]);
		return json_encode($json);
	}

	public function component_delete($id, $product_id)
	{
		$json = array();

		$json['component_id'] = $this->remove_component($id, $product_id);

		return json_encode($json);
	}
  
	public function add_item_to_dictionary($field_id, $title, $user)
	{
		$json = array();

		$json = $this->addItemToDictionary($field_id, $title, $user);

		return json_encode($json);
	}  
  
  public function save_custom_data($file, $lang, $user_id){
   $json = array();

		$json = $this->saveCustomData($field_id, $title, $user);

		return json_encode($json); 
  }



	// helpers

	/**
	 * return file info (file, ext, name)
	 * @param $files
	 * @return array
	 */
	public function getFileInfo($files)
	{
		if( !$files ){
			return false;
		}

		$pathinfo = pathinfo($files['image']['name']);
		$file = $pathinfo['basename'];
		$ext = $pathinfo['extension'];
		$filename = substr($file, 0, -strlen($ext)-1);

		return compact('file', 'ext', 'filename');
	}


	public static function imgMimeType($ext) {

		$mime_type = array(
			"jpe" => "image/jpeg",
			"jpeg" => "image/jpeg",
			"jpg" => "image/jpeg",
			"png" => "image/png"
		);

		if (isset($mime_type[$ext])) {
			return $mime_type[$ext];
		}

		return false;
	}


	/**
	 * validate and add messages
	 * @param $files
	 * @return bool
	 */
	public function imgValidator($files)
	{
		if( !$files ){
			return false;
		}

		$file_info = $this->getFileInfo($files);

		if( $files['image']['name'] == "" ){
			array_push($this->errors, "The file name is empty");
		}

		if( !in_array( $file_info['ext'], array('jpe', 'jpeg', 'jpg', 'png') ) || !self::imgMimeType($file_info['ext']) ){
			array_push($this->errors, "The file extension is not matching");
		}

		return $this->errors ? false : true;
	}

	/**
	 * @param $data
	 */
	/* 
	public function sphinx_search_products($data) {
		$json = array();

		 * не запрашивать данные,
		 * если не передан поисковый запрос
		if (empty(trim($data['search']['term']))) {
			return json_encode($json);
		}

		$products = (new SphinxModel)->getSphinxSearchProducts($data);
		$categories = (new SphinxModel)->getSphinxSearchCategories($data);

		if (!empty($categories)) {
			foreach ($categories as $key => $category) {
				$tmp_cat = $this->getRootName($category['root_category']);
				$categories[$key]['vendor_category'] = $tmp_cat['name'];
				$categories[$key]['quantity'] = 'no';
				$categories[$key]['image'] = 'img/normal_folder.png';
			}
		}

		$json = array_merge($categories, $products);

		return json_encode($json);
	}
	*/
	
	public function sphinx_search_products($data) {
		$json = array();

		/**
		 * не запрашивать данные,
		 * если не передан поисковый запрос
		 */
		if (empty(trim($data['search']['term']))) {
			return json_encode($json);
		}

		$products = (new SphinxModel)->getSphinxSearchProducts($data);
		$categories = (new SphinxModel)->getSphinxSearchCategories($data);

		if (!empty($products)) {
			$products = array_map(function ($arr){
				return $arr['id'];
			},$products);
			$products = implode(',',$products);
			$products = $this->getSphinxProducts($products, $data['lang'], $data['category_id']);
		}

		if (!empty($categories)) {
			$categories = array_map(function ($arr){
				return $arr['id'];
			},$categories);
			$categories = implode(',',$categories);
			$categories = $this->getSphinxCategories($categories);

			foreach ($categories as $key => $category) {
				$tmp_cat = $this->getRootName($category['root_category']);
				$categories[$key]['vendor_category'] = $tmp_cat['name'];
				$categories[$key]['quantity'] = 'no';
				$categories[$key]['image'] = 'img/normal_folder.png';
			}
		}
    if($categories || $products)
		$json = array_merge($categories, $products);

		return json_encode($json);
	}

	/**
	 * @param $data
	 */
	/*
	public function sphinx_search_categories($data) {
		$json = array();
		$json['categories'] = (new SphinxModel)->getSphinxSearchCategories($data);

		return json_encode($json);
	}
	*/
	
	public function sphinx_search_categories($data) {
		$json = array();
		$categories = (new SphinxModel)->getSphinxSearchCategories($data);

		if (!empty($categories)) {
			$categories = array_map(function ($arr){
				return $arr['id'];
			}, $categories);
			$categories = implode(',',$categories);
			$json['categories'] = $this->getSphinxCategories($categories);
		} else {
			// !!!проверить где будет использоваться данный код!!!
			$json['categories'] = $categories;
		}

		return json_encode($json);
	}	
}



