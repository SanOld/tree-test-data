<?php
require_once('db.php');
class model extends db_pdo{


    private $ch_categories;
    private $cnt_temp_product = 0;
    private $componentList = array();
    private $components_id = array();

    /**
     * @param $result
     * @return null
     */
    private function getResultOrNull( $result, $get_one = false ){

        if ( !$get_one ){
            $fetch_type = $result->fetchAll(PDO::FETCH_ASSOC);
            

        } else{
            $fetch_type = $result->fetch(PDO::FETCH_ASSOC);
        }

        return !empty($result) ? $fetch_type : null;
    }

    /**
     * @param $name
     * @return string
     */
    public function addParentCategory ($name, $lang, $type = 0, $user_id){
      $category_id = $this->checkCategory($name, 0, $user_id);
      if( ! $category_id){
        $this->db->query("INSERT INTO category SET parent_id ='0', user_id = ".$user_id.",`type`= '".$type."', name =".$this->db->quote($name).",`language` = '".$lang."' ");
        $category_id = $this->db->lastInsertId();
//        $this->db->query("UPDATE `category` SET `root_category`= '".(int)$category_id."', `type`= '".$type."' WHERE category_id = '".$category_id."'");
      } else {
        return $category_id[0]['category_id'];
      }
        
        return $category_id;
    }

    /**
     * @param $name
     * @return array
     */
    public function checkCategory($name,$root_category, $user_id = false)
    {
      if(!$user_id){
        $result = $this->db->query("SELECT category_id, name FROM category WHERE name = '".$name."' AND root_category ='".$root_category."'");
      } else {
        $result = $this->db->query("SELECT category_id, name FROM category WHERE name = '".$name."'  AND user_id ='".$user_id."' AND root_category ='".$root_category."'");
      }
        
        if($result){
          return $this->getResultOrNull($result);
        } else {
          return $result;
        }
        
    }


    public function getCategory($id)
    {
        $result = $this->db->query("SELECT category_id, name,parent_id FROM category WHERE category_id = ".$id);
        return $this->getResultOrNull($result, true);
    }
    
    public function getPricesAll(){
        $result = $this->db->query("SELECT * FROM prices");
        return $this->getResultOrNull($result);
    }
    public function getPrice($price_id){
      $result = $this->db->query("SELECT * FROM prices WHERE id= '".$price_id."'");
        return $this->getResultOrNull($result, true);
    }

    /**
     * @param $data
     */
    public function addCategoryImage($data)
    {
        $result = $this->db->query("SELECT image FROM category WHERE  category_id = '".$data['category_id']."'")->fetch(PDO::FETCH_ASSOC);
        if(file_exists('upload/vendor/'.$result['image'])){
            unlink('upload/vendor/'.$result['image']);
        }
        $this->db->query("UPDATE  category SET image = '".$data['image']."' WHERE category_id = '".$data['category_id']."'");

    }

    /**
     * @param $category_id
     * @param $name
     * @return string
     */
    public function addCategory($parent_id, $name,$lang,$root_category, $type = 0, $user_id = ''){
      
      $category_id = $this->checkCategory($name, $root_category, $user_id);
      if( ! $category_id){
        $this->db->query("INSERT INTO category SET parent_id = '".$parent_id."', name = ".$this->db->quote($name)." ,`language` = '".$lang."', user_id = '".$user_id."', type = '".$type."',root_category ='".$root_category."'");
        // $db->query("UPDATE `category` SET `order`= '".(int)$category_id."' WHERE category_id = '".$category_id."'");
        $category_id = $this->db->lastInsertId();
      }
        
      return $category_id;
    }
    
    public function addCategoryFromXML($root_category, $parent_id, $name,$lang,$sku, $price_id){

        $result = $this->db->query("INSERT INTO category SET root_category ='".$root_category."', parent_id ='".$parent_id."', name ='".$name."' ,`language` = '".$lang."',sku ='".$sku."',price_id ='".$price_id."'");
        if($result){
          return $this->db->lastInsertId();
        } else {
          return false;
        }
    }    

    public function deleteDataByPriceId($price_id, $lang){
      
      $categories = $this->getCategoriesByPriceId($price_id);
      $products = array();
      
      $this->db->query("DELETE FROM `category` WHERE `price_id` = " . $price_id);
      foreach($categories as $item ){
        
        $products_local = $this->getProductsByCategoryId($item['category_id']);
        if($products_local){
          foreach($products_local as $item2){
            $products[] = $item2;
          }
        }
        
        $this->db->query("DELETE FROM `products` WHERE `category_id` = " . $item['category_id']);
      }
      
      foreach($products as $item ){
        $this->db->query("DELETE FROM products_".$lang."  WHERE `product_id` = " . $item['product_id']);
      }
//      
    }
    
    /**
     * @param $sku
     * @return array
     */
    
    public function checkProduct($sku, $type = 0)
    {
      switch ( $type ) {
        case 1:
          return $this->db->query("SELECT product_id,sku FROM products WHERE sku = '".$sku."'")->fetchAll(PDO::FETCH_ASSOC);
          break;

        default:
          return $this->db->query("SELECT product_id,sku FROM products WHERE sku = '".$sku."'")->fetchAll(PDO::FETCH_ASSOC);
          break;
      }
        
    }
    
    public function checkProductAuto($product){
      $check1 = "SELECT product_id 
                              FROM products 
                              WHERE user_id = '".$product['user_id']."'
                              AND category_id = '".$product['category_id']."' 
                              AND sku = '".$product['sku']."'"   
                        ;
      
      $check2 = false;
      $check2 = $this->db->query("SELECT product_id 
                              FROM products_".$product['lang']."
                              WHERE name = '".$product['name']."'  
                              AND product_id IN (".$check1.")"   
                        )->fetchAll(PDO::FETCH_ASSOC);
      
      return $check2;
    }


    public function transferProduct($lang = 'ru')
    {
        $start = microtime(true);
        $result = $this->db->query("SELECT * FROM temp_products");
        while( $row = $result->fetch(PDO::FETCH_ASSOC) ){
            $this->addProduct($row,$lang);
        }
        $this->clearTemp();

    }

    public function clearTemp()
    {
        $this->db->query("TRUNCATE TABLE temp_products");
    }


    public function addTempProduct($data){

         $this->db->query("INSERT INTO temp_products SET
                        category_id = '".$data['category_id']."', vendor_link = ".$this->db->quote($data['donor_url']).", vendor_category = ".$this->db->quote($data['vendor']).",
                        sku = '".$data['sku']."', vendor_photo_link  = '".$data['image']."', local_photo_link = ".$this->db->quote($data['image_gallery']).",
                        quantity = '".$data['measure']."', additional_materials = ".$this->db->quote($data['additional_materials']).",
                        certificates = ".$this->db->quote($data['certificates']).",price = '".$data['price']."',currency = ".$this->db->quote($data['currency_name']).",
                        name = ".$this->db->quote($data['name']).", attributes = ".$this->db->quote($data['attributes']).",
                        description = ".$this->db->quote($data['description']));

        /*if($data['sku'] == '89762-1584'){
            $this->transferProduct('ru');
         }*/
    }

    /**
     * @param $data
     * @param $lang
     */
    public function addProduct($data, $lang)
    {
        $this->db->query("INSERT INTO products SET 
          category_id =         '".$data['category_id']."', 
          vendor_link =         '".$data['vendor_link']."', 
          vendor_category =     '".$data['vendor_category']."',
          sku =                 '".$data['sku']."',
          vendor_photo_link  =  '".$data['vendor_photo_link']."',
          local_photo_link =    '".$data['local_photo_link']."',
          quantity =            '".$data['quantity']."',
          additional_materials = ".$this->db->quote($data['additional_materials']).",
          certificates =        ".$this->db->quote($data['certificates']).",
          created_at =          '".time()."'");
        
        $product_id = $this->db->lastInsertId();


        $this->db->query("INSERT INTO products_".$lang." SET price = '".$data['price']."', currency = '".$data['currency']."', product_id = '".$product_id."', name = ".$this->db->quote($data['name']).", attributes = ".$this->db->quote($data['attributes']).", description = ".$this->db->quote($data['description']));
        /*if($data['sku'] == '89762-1584'){
            die;
        }*/

    }
    
    public function addProductFromXML($data, $lang, $categories)
    {
          $this->db->query("INSERT INTO products SET 
            category_id =         '".$data['category_id']."', 
            vendor =              '".$data['vendor']."',  
            vendor_link =         '".$data['vendor_link']."', 
            vendor_category =     '".$data['vendor_category']."',
            sku =                 '".$data['sku']."',
            vendor_photo_link  =  '".$data['vendor_photo_link']."',
            local_photo_link =    '".$data['local_photo_link']."',
            quantity =            '".$data['quantity']."',
            created_at =          '".time()."'");

          $product_id = $this->db->lastInsertId();

          $this->db->query("INSERT INTO products_".$lang." SET
            price =         '".$data['price']."',
            currency =      '".$data['currency']."',
            product_id =    '".$product_id."',
            name =          '".$data['name']."',
            attributes =    '".$data['attributes']."',
            description =   '".$data['description']."'");
          
          return $this->db->lastInsertId();

    }

    /**
     * @param $data
     * @param $lang
     */
    public function addCustomProduct($data, $type = 1)
    {
        $queryWork = '';

        if( isset($data['work_time']) ){
            $queryWork = ", work_time = ".(int)$data['work_time'].", work_type = ".$this->db->quote($data['work_type']).", work_type_time = ".$this->db->quote($data['work_type_time']).", work_calc_type = ".$this->db->quote($data['work_calc_type']);
        }

        $querySKU = '';
        if( $data['sku'] ){
            $querySKU = " , sku  = '".$data['sku']."' ";
        }
        if( $data['vendor'] ){
            $querySKU .= " , vendor  = '".$data['vendor']."' ";
        }
        if( $data['units'] ){
            $queryUnits = " , units  = '".$data['units']."' ";
        }

        
        $this->db->query("INSERT INTO products SET  vendor_photo_link  = '".$data['vendor_photo_link']."', `type` = '".$type."', user_id = '".$data['user_id']."', quantity = ".(int)$data['quantity'].", category_id = ".(int)$data['category_id'].$queryWork.$querySKU);
        $product_id = $this->db->lastInsertId();

        $this->db->query("INSERT INTO products_".$data['lang']." SET price = '".$data['price']."'" . $queryUnits . ", currency = '".$data['currency']."', product_id = '".$product_id."', name = ".$this->db->quote($data['name']));
        return $product_id;

    }
    
        /**
     * @param $data
     * @param $lang
     */
    public function updateCustomProduct($data, $type = 1)
    {
        $this->db->query("UPDATE products SET  
          vendor_photo_link  = '".$data['vendor_photo_link']."',
          type = '".$type."',
          user_id = '".$data['user_id']."',
          category_id = ".(int)$data['category_id']."'
          WHERE product_id = ". $data['product_id']);
        
        $this->db->query("UPDATE products_".$data['lang']." SET 
          price = '".$data['price']."',
           currency = '".$data['currency']."',
           name = ".$this->db->quote($data['name'])."
          WHERE product_id = ". $data['product_id']);
        
        return $product_id;

    }

    /**
     * @param $data
     * @param $lang
     */
    public function editCustomProduct($data, $type = 1, $user_id = 0)
    {
        $queryImg = '';
        $queryWork = '';

        if( $data['vendor_photo_link'] ){
            $queryImg = ", vendor_photo_link  = '".$data['vendor_photo_link']."'";
        }

        $querySKU = '';
        if( $data['sku'] ){
            $querySKU = ", sku  = '".$data['sku']."'";
        }
        if( $data['vendor'] ){
            $querySKU .= " , vendor  = '".$data['vendor']."' ";
        }
        if( $data['units'] ){
            $queryUnits = " , units  = '".$data['units']."' ";
        }

        if( isset($data['work_time']) ){
            $queryWork = ", work_time = ".(int)$data['work_time'].", work_type = ".$this->db->quote($data['work_type']).", work_calc_type = ".$this->db->quote($data['work_calc_type']);
        }

        //$this->db->query("UPDATE products SET `type` = 1, user_id = '".$user_id."', quantity = ".(int)$data['quantity'].", category_id = ".(int)$data['category_id']. $queryWork . $querySKU . $queryImg." WHERE product_id = " . (int)$data['product_id']);
        // no  ", category_id = ".(int)$data['category_id']
        // no   user_id = '".$user_id."
        // no `type` = 1
        $this->db->query("UPDATE products SET quantity = ".(int)$data['quantity']. $queryWork . $querySKU . $queryImg." WHERE product_id = " . (int)$data['product_id']);

        $this->db->query("UPDATE products_".$data['lang']." SET price = '".$data['price']."'".$queryUnits.", currency = '".$data['currency']."', name = ".$this->db->quote($data['name'])." WHERE product_id =" . $data['product_id']);
    }


    public function get_components($product_id, $lang)
    {
        $result = $this->db->query('SELECT * FROM components WHERE product_id= '.$product_id);
        $components = $this->getResultOrNull($result);
        $componentList = array();
        foreach ($components as $item) {

            $data['id'] = $item['component_id'];
            $componentList[] = $this->getProduct($data, $lang);

        }

        return $componentList;
    }

    public function get_components_tree($product_id, $lang)
    {
        $components = $this->product_components($product_id);
        $componentList = array();
        foreach ($components as $item) {
            $data['id'] = $item['component_id'];
            $componentList[$item['component_id']] = $this->getProduct($data, $lang);

            if($components2 = $this->product_components($item['component_id'])){
                foreach ($components2 as $item2) {
                    $data['id'] = $item2['component_id'];
                    $componentList[$item['component_id']]['components'][$item2['component_id']] = $this->getProduct($data, $lang);

                    if($components3 = $this->product_components($item2['component_id'])) {
                        foreach ($components3 as $item3) {
                            $data['id'] = $item3['component_id'];
                            $componentList[$item['component_id']]['components'][$item2['component_id']]['components'][$item3['component_id']] = $this->getProduct($data, $lang);
                        }
                    }
                }
            }
        }

        return $componentList;
    }

    public function product_components($product_id)
    {
        $result = $this->db->query('SELECT * FROM components WHERE product_id= '.$product_id);
        return $this->getResultOrNull($result);
    }


    public function getComponent($component_id, $product_id)
    {
       $result = $this->db->query('SELECT * FROM components WHERE component_id= '.$component_id.' AND product_id= '.$product_id);

        return $this->getResultOrNull($result, true);
    }


    public function updateComponent($data, $product_id, $lang)
    {
        $this->db->query("UPDATE products SET  quantity = ".$data['quantity'].", code = ".$this->db->quote($data['code']).", vendor = ".$this->db->quote($data['vendor'])." WHERE product_id =" . $product_id);

        $this->db->query("UPDATE products_".$lang." SET `type_product` = ".$data['type'].", price = '".$data['price']."', units = '".$data['units']."', name = ".$this->db->quote($data['name'])." WHERE product_id =" . $product_id);
    }


    public function addComponent($component, $component_id, $product_id, $lang)
    {
        $result = $this->db->query('INSERT INTO components SET component_id= '.$component_id.', product_id= '.$product_id);

        $this->updateComponent($component, $component_id, $lang);

        return $this->getResultOrNull($result, true);
    }


    public function remove_component($component_id, $product_id)
    {
        $this->db->query("DELETE FROM `components` WHERE `component_id` = ".$component_id." AND `product_id` = " . $product_id);

        return $component_id;
    }



    /**
     *
     * @return array
     */
    public function getCategories($lang = 'ru', $type = 0, $user_id = 1){
      
        $cats = array();
		
		if ($type == 0) {	
		    if ($_SERVER['HTTP_HOST'] == 'cad5d.com.ua' || $_SERVER['HTTP_HOST'] == 'widgets.online.cableproject.net' || 
		         $_SERVER['HTTP_HOST'] == 'widgets.online.cad5d.com.ua' ) {
		            $sql = 'SELECT ct.category_id, ct.parent_id, ct.name, ct.image,ct.count_products FROM category as ct WHERE type = "'.$type.'" AND language = "'.$lang.'"  ORDER BY `order`';
		        } else {
		            $name = 'axiomplus';
		            $sql = 'SELECT ct.category_id, ct.parent_id, ct.name, ct.image,ct.count_products FROM category as ct WHERE type = "'.$type.'" AND language = "'.$lang.'" AND name <> "'.$name.'" ORDER BY `order`';
		        }
		}
		else
		{
			$sql = 'SELECT ct.category_id, ct.parent_id, ct.name, ct.image,ct.count_products FROM category as ct WHERE type = "'.$type.'" AND language = "'.$lang.'" and user_id = '.$user_id.' ORDER BY `order`';
		}
        if ( $result = $this->db->query($sql) ) {

            while( $row = $result->fetch(PDO::FETCH_ASSOC) ){

                $row['has_product'] = true;

                $cats[$row['parent_id']][] = $row;

            }
        }

        return $cats;
    }


    public function getCategoriesByPriceId($price_id)
    {
   
			$result = $this->db->query("SELECT * FROM category WHERE price_id = '".$price_id."'");
      return $this->getResultOrNull($result);
    }
    public function getProductsByCategoryId($category_id)
    {
   
			$result = $this->db->query("SELECT * FROM products WHERE category_id = '".$category_id."'");
      return $this->getResultOrNull($result);
    }
    
    public function getCategoriesAll($data)
    {
        if ((int)$data['category_type'] == 0) {
			$result = $this->db->query("SELECT category_id, `name` as label FROM category WHERE type = '".(int)$data['category_type']."'");
		}
		else
		{
			$result = $this->db->query("SELECT category_id, `name` as label FROM category WHERE user_id = ".(int)$data['user_id']." and type = '".(int)$data['category_type']."'");
		}
        return $this->getResultOrNull($result);
    }

    public function getProductsAll($lang = 'ru')
    {
        $result = $this->db->query('SELECT *, pl.name as label FROM products_'.$lang.' as pl LEFT JOIN products as p ON p.product_id = pl.product_id');

        return $this->getResultOrNull($result);
    }
    public function getSearchProducts($data)
    {
        $search = trim($data['search']['term']);
        $implode = '';
        $words = explode(' ', trim(preg_replace('/\s+/', ' ', $search)));

        foreach ($words as $word) {
            $implode  .= "+".$word;
        }
        $implode = $search;
        $sql = "SELECT *,p.product_id as id, pl.name as label,MATCH (pl.name) AGAINST ('".$implode." IN BOOLEAN MODE') as relev,
                      MATCH (vendor_category) AGAINST ('".$implode." IN BOOLEAN MODE') as score
                      FROM products_".$data['lang']." as pl
                      LEFT JOIN products as p ON p.product_id = pl.product_id";

        if(!empty($search)){
            $sql .= " WHERE MATCH (pl.name) AGAINST ('".$implode." IN BOOLEAN MODE')>0
            OR  MATCH (vendor_category) AGAINST ('".$implode." IN BOOLEAN MODE')>0
            OR pl.name LIKE '%" . $search . "%'
            OR p.sku LIKE '%" . $search . "%'";

        }

        if(!empty($data['category']) && empty($search)){
            $sql .= " WHERE p.vendor_category LIKE '%" . $data['category'] . "%'";
        }elseif(!empty($data['category'])){
            $sql .= " AND p.vendor_category LIKE '%" . $data['category'] . "%'";
        }
        if(isset($data['type']) && $data['type'] == '0'){
            $sql .= " AND p.type = '0' ";
        }
        if(isset($data['type']) && $data['type'] == '1'){
            $sql .= " AND p.type != '0' ";
        }
        $sql .= " ORDER BY relev DESC,score DESC LIMIT 15 ";

       // var_dump($sql);die;
       // var_dump($sql);
        $result = $this->db->query($sql);

        return $this->getResultOrNull($result);
    }

    public function getSearchCategories($data)
    {
        $search = trim($data['search']['term']);
        $sql = "SELECT *,name as label,category_id as id FROM category WHERE name LIKE '%" . $search . "%'";

        if(isset($data['type']) && $data['type'] == '0'){
            $sql .= " AND type = '0' ";
        }
        if(isset($data['type']) && $data['type'] == '1'){
            $sql .= " AND type != '0' ";
        }
        $sql .= " LIMIT 5 ";
        $result = $this->db->query($sql);
        return $this->getResultOrNull($result);
    }

    public function deleteProduct($data)
    {
		if (!isset($data['user_id']) || $data['user_id'] == 1) {
			$this->db->query("DELETE FROM products WHERE product_id = ".$data['id']);
			$this->db->query("DELETE FROM products_ru WHERE product_id = ".$data['id']);
			$this->db->query("DELETE FROM products_en WHERE product_id = ".$data['id']);
			$this->db->query("DELETE FROM view_products WHERE product_id = ".$data['id']);
		}	
		else
		{
			$sql = "SELECT product_id FROM products WHERE user_id = ".$data['user_id']." and product_id = ".$data['id'];
			$result = $this->db->query($sql);
			$res = $this->getResultOrNull($result);
			if (count($res) > 0) {
				$this->db->query("DELETE FROM products WHERE user_id = ".$data['user_id']." and product_id = ".$data['id']);
				$this->db->query("DELETE FROM products_ru WHERE product_id = ".$data['id']);
				$this->db->query("DELETE FROM products_en WHERE product_id = ".$data['id']);
			}	
			$this->db->query("DELETE FROM view_products WHERE user_id = ".$data['user_id']." and product_id = ".$data['id']);
		}
        $json['true'] = 'true';
        return json_encode($json);
    }
    public function addViewProduct($data)
    {
        $checkProduct = $this->db->query("SELECT * FROM view_products WHERE user_id = '".(int)$data['user_id'] . "' AND product_id = '".$data['product_id']."'")->fetch(PDO::FETCH_ASSOC);

        if($checkProduct){
            $this->db->query("UPDATE view_products SET created_at = '".time()."' WHERE view_id = '".(int)$checkProduct['view_id'] . "'");
        } else {
            $results = $this->db->query("SELECT * FROM view_products WHERE user_id = '" . (int)$data['user_id'] . "' ORDER BY created_at ASC")->fetchAll(PDO::FETCH_ASSOC);

            if (count($results) >= 20) {
                $this->db->query("DELETE FROM view_products WHERE view_id = '".(int)$results[0]['view_id'] . "'");
            }

            $product_name = $this->db->query("SELECT * FROM products_".$data['lang']." WHERE product_id = '" . (int)$data['product_id'] . "'")->fetch(PDO::FETCH_ASSOC);
            $this->db->query("INSERT INTO view_products SET user_id = '" . (int)$data['user_id'] . "', product_id = '" . (int)$data['product_id'] . "',name =".$this->db->quote($product_name['name']).", created_at ='" . time() . "'");
        }
    }

    public function getViewProducts($data)
    {
        $sql = "SELECT * FROM view_products as vp  
                LEFT JOIN products as p ON (p.product_id = vp.product_id) where vp.user_id = ".(int)$data['user_id']." ORDER BY vp.created_at DESC";

        $result = $this->db->query($sql);
        return $this->getResultOrNull($result);
    }

    public function getUserTypeProduct($data){


        $sql = "SELECT * FROM products_".$data['lang']." as pl
        LEFT JOIN products as p ON p.product_id = pl.product_id
        WHERE user_id = '".(int)$data['user_id']."' AND p.type = '".(int)$data['type']."' AND category_id='0'";
        $result = $this->db->query($sql);
        return $this->getResultOrNull($result);
    }
    public function hasChild($category_id)
    {

        $result = $this->db->query('SELECT category_id, parent_id FROM category WHERE parent_id = '.(int)$category_id);

        while ( $row = $result->fetchAll(PDO::FETCH_ASSOC) )
        {

            foreach ($row as $item) {

                if( in_array($item['category_id'], $this->ch_categories) ){
                   return true;
                }

                return $this->hasChild($item['category_id']);
            }
        }

        return false;
    }



    /**
     * @param $list
     */
    public function saveMenu($list){

        $sort = array();

        foreach ($list as $category_id => $parentId) {

            $parentId = ($parentId === "null") ? 0 : $parentId;

            if (!array_key_exists($parentId, $sort)) {
                $sort[$parentId] = 1;
            }

//            var_dump($sort);
//            var_dump($category_id);
//            var_dump($parentId); die;

            $this->db->query("UPDATE `category` SET `parent_id` = ".$parentId.", `order` = ".$sort[$parentId]." WHERE `category_id` = ".$category_id);

            // increment the sort order for this level
            $sort[$parentId]++;
        }

        return true;
    }

    /**
     * @param $id
     * @param $title
     */
    public function editMenuItem($id, $title){
        $this->db->query("UPDATE `category` SET `name` = '".$title."' WHERE `category_id` = ".$id);
    }

    /**
     * @param $id
     * @param $title
     */
    public function addMenuItem($id, $title, $lang, $type = 0){
        $root_category = $this->db->query("SELECT root_category FROM `category` WHERE `category_id` = ".$id." LIMIT 1")->fetch(PDO::FETCH_ASSOC);
        $this->db->query("INSERT INTO `category` SET `name` = '".$title."',`language` = '".$lang."', root_category ='".$root_category['root_category']."', type ='".$type."', `parent_id` = ".$id);
    }


    /**
     * @param $id
     */
    public function removeMenuItem($category_id){
        $this->db->query("DELETE FROM `category` WHERE `category_id` = ".$category_id);
    }

    /**
     * @param $id
     * @return array
     */
    public function getProducts($category_id, $lang = 'ru', $limit, $page){
        $start = ($page - 1) * $limit;
        //echo 'SELECT * FROM products_'.$lang.' as pl LEFT JOIN products as p ON p.product_id = pl.product_id WHERE category_id = '.$category_id.' LIMIT '.$start.','.$limit;
        $result = $this->db->query('SELECT * FROM products_'.$lang.' as pl LEFT JOIN products as p ON p.product_id = pl.product_id WHERE category_id = '.$category_id.' LIMIT '.$start.','.$limit);
        return $this->getResultOrNull($result);
    }

    /**
     * @param $id
     * @return array
     */
    public function getProduct($data, $lang = 'ru'){

       /* var_dump('SELECT * FROM products_'.$lang.' as pl LEFT JOIN products as p ON p.product_id = pl.product_id WHERE p.product_id = '.$data['id']);die;
       */ $result = $this->db->query('SELECT * FROM products_'.$lang.' as pl LEFT JOIN products as p ON p.product_id = pl.product_id WHERE p.product_id = '.$data['id']);

        return $this->getResultOrNull($result, true);
    }

    /**
     * @param $field
     * @param $title
     * @return array
     */
    public function addItemToDictionary($field_id, $title, $user_id){

        $result  = $this->db->query("INSERT INTO `dictionary` SET `title` = '".$title."',`field_id` = '".$field_id."', user_id ='".$user_id."'");

      return $result ? true : false;
    }
    
    
    public function getFieldsAll(){
      $result = array();
      $fields= $this->db->query('SELECT name, id FROM fields')->fetchAll(PDO::FETCH_KEY_PAIR    );
//      foreach($fields as $item){
//        $result[$item['id']] = $item['name'];
//      }
      return $fields;
    }
    
    /**
     * @param $G_USER_ID
     * @return array
     */
    public function getDictionaryData($G_USER_ID){
      $result = array();
      $fields= $this->db->query('SELECT * FROM fields')->fetchAll(PDO::FETCH_ASSOC);;
      foreach($fields as $row){
        $result[$row[id]]['id'] = $row[id];
        $result[$row[id]]['name'] = $row[name];
        $result[$row[id]]['dict'] = $this->db->query('SELECT title FROM dictionary WHERE field_id='.$row[id]." AND user_id=" . $G_USER_ID)->fetchAll(PDO::FETCH_ASSOC);
      }
      return $result;
    }
    
    /**
     * @param $G_USER_ID
     * @return array
     */
    public function getDictionary($G_USER_ID){
      $result = array();
      $data = $this->getDictionaryData($G_USER_ID);
      
      foreach($data as $item){
        $result[$item['name']] = $item['name'];
        foreach($item['dict'] as $word){
          $result[$word['title']] = $item['name'];
        }
      }
      
      return $result;  
    }
    
    /**
     * @param $category_id
     * @return array
     */
    public function getProductsCount($category_id, $lang){
      if($this->db->query("SHOW TABLES LIKE'products_".$lang."'")->fetch() != ''){
        $result = $this->db->query('SELECT COUNT(*) as product_cnt FROM products_'.$lang.' as pl LEFT JOIN products as p ON p.product_id = pl.id WHERE category_id = '.$category_id)->fetchAll(PDO::FETCH_ASSOC);
      }
        return $result[0]['product_cnt'];
    }

    public function getProductsCountByCategory($category_id, $lang){
        $result = $this->db->query('SELECT COUNT(*) as product_cnt FROM products_'.$lang.' as pl LEFT JOIN products as p ON p.product_id = pl.product_id WHERE category_id = '.$category_id)->fetchAll(PDO::FETCH_ASSOC);
        return $result[0]['product_cnt'];
    }
    /**
     * @param $id
     * @return array
     */
    public function getChildrenProductsCount($category_id, $lang){

        $this->ch_categories = array();
        $this->ch_categories[] = $category_id;

        $this->tree($category_id, $lang); // run a recursion

        // convert recursion result to string
        //$ch_categories = implode(',', $this->ch_categories);

        $cnt = count($this->ch_categories);
        $countResult[0]['product_cnt'] = 0;

        if($cnt){
            $str = '';

            for ($i = 0; $i<$cnt; $i++){
                $str .=  ' category_id = '.$this->ch_categories[$i]  .' OR' ;
            }

            $str = substr($str, 0, -2);

            if($this->db->query("SHOW TABLES LIKE'products_".$lang."'")->fetch() != ''){
          //  $countResult = $this->db->query("SELECT COUNT(*) as product_cnt FROM products WHERE category_id IN ('.$ch_categories.')")->fetchAll(PDO::FETCH_ASSOC);
            $countResult = $this->db->query("SELECT COUNT(*) as product_cnt FROM products_".$lang." as pl LEFT JOIN products as p ON p.product_id = pl.product_id WHERE ".$str)->fetchAll(PDO::FETCH_ASSOC);
            }

        }

        return $countResult[0]['product_cnt'];
    }

    public function getRootName($category_id)
    {
        $result = $this->db->query("SELECT *  FROM category WHERE category_id = ".$category_id);
        return $this->getResultOrNull($result,true);
    }
    public function getParentsCategory()
    {
        $result = $this->db->query("SELECT DISTINCT(root_category),language FROM category");
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }
    public function updateCount($category_id,$count)
    {
         $this->db->query("UPDATE category SET count_products = '".$count."' WHERE category_id = '".$category_id."'");
    }

    /**
     * add children categories to global array by recursion
     * @param $id
     */
    private function tree($category_id, $lang)
    {
        $result = $this->db->query('SELECT category_id, parent_id FROM category WHERE language = "'.$lang.'" AND parent_id = '.(int)$category_id);

        while ( $row = $result->fetchAll(PDO::FETCH_ASSOC) )
        {
            foreach ($row as $item) {
                $this->ch_categories[] = $item['category_id'];
                $this->tree($item['category_id'], $lang);
            }
        }
    }

    public function getSettings()
    {
        $result = $this->db->query("SELECT * FROM settings WHERE setting_id = '1'");
        $res = $this->getResultOrNull($result,true);
		//session_start();
		if (!isset($_SESSION['user'])) {
			//echo '$_SESSION[user] empty';
		}
		if (isset($_SESSION['user'])) {
			if ($res != null && ($_SESSION['user'] != 'import@vendors.com' && $_SESSION['user'] != 'sshevaiviviviv@gmail.com') ) 
				$res['show_form'] = 0;
		}	
		else
		{
			//echo '$_COOKIE[user] empty';
		}
		return $res;
    }
    public function changeSettings($data)
    {
        $json = array('setting'=>'true');
        $this->db->query("UPDATE settings SET show_form ='".$data['show_form']."' WHERE setting_id = '1'");
        $err = $this->db->errorInfo();
		if($err[0] != '00000'){
            $json = array('setting'=>$err[2]);
        }
        return $json;

    }

    public function getCategoryChildren($category_id){
        $cats = array();
        if ( $result = $this->db->query('SELECT ct.category_id, ct.parent_id, ct.name, ct.type, ct.image, ct.count_products FROM category as ct WHERE parent_id = "'.$category_id.'" ORDER BY `order`') ) {
            while( $row = $result->fetch(PDO::FETCH_ASSOC) ){
                $row['has_product'] = true;
                $cats[] = $row;
            }
        }

        $settings = $this->getSettings();
        if($settings['show_form'] == 1) {
            $editMenu = 'editMenu ui-icon ui-icon-pencil';
            $deleteMenu = 'deleteMenu ui-icon ui-icon-closethick';
        }else{
            $editMenu = '';
            $deleteMenu = '';
        }

        $tree = '';
        if (count($cats) > 0){
            $tree = '<ol>';
            $products_count = '';

            foreach ($cats as $cat) {
                $type = $cat['type'];
                if(!empty($cat['image'])){
                    $catImg = '<img src="upload/vendor/'.$cat['image'].'" alt="" class="folder_img  ">';
                    //$productText = '<span >' .$products_count . '</span>';
                    $productText = '<span data-id="' . $cat['category_id'] .'" data-find_category_id="' . $cat['category_id'] .'" class="itemTitle">' . $cat['name'] . ' ' . $products_count . '</span>';
                } else {
                    $catImg = '';
                    $productText = '<span data-id="' . $cat['category_id'] .'" data-find_category_id="' . $cat['category_id'] .'" class="itemTitle">' . $cat['name'] . ' ' . $products_count . '</span>';
                }

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
                $tree .= '</li>';
            }

            $tree .= '</ol>';
            if($type == 1 || $type == 2) {
                $tree .= '<div class="user_product'.$type.'"> </div>';
            };

        }
        return json_encode($tree);
    }

	public function getSphinxProducts($ids, $lang)
    {
        $sql = "SELECT
                *,
                p.product_id as id,
                pl.name as label,
                pl.name as relev,
                p.vendor_category as score
            FROM
                products_{$lang} as pl
            LEFT JOIN
                products as p USING(product_id)
            WHERE
                p.product_id IN ({$ids})";

        $result = $this->db->query($sql);

        return $this->getResultOrNull($result);
    }
	
	public function getSphinxCategories($data)
    {
        $sql = "SELECT
                *,
                name as label,
                category_id as id
            FROM
                category
             WHERE
                category_id IN ({$data})";

        $result = $this->db->query($sql);

        return $this->getResultOrNull($result);
    }	

} ?>