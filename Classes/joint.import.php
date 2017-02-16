<?php
require_once('PHPExcel.php');
require_once('model.php');
require_once('chunkReadFilter.php');

class Import extends model {

    protected $errors = array();
    /**
     * @param $data
     * @param $files
     */


    public function index($data){
        ini_set('error_reporting', E_ALL);
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        $this->readWriteBd($data);
    }

    public function createCategory($data)
    {
        $this->addParentCategory($data['create_category'], $data['language'], $data['category_type']);

        header('Location:'. $_SERVER['HTTP_REFERER']);
    }


    public function upload($files)
    {

        if ( $this->fileValidator($files) ) {

            $file_info = $this->getFileInfo($files);
            $upload_dir = 'upload/';
            $json = array('error'=> '','file' => '');
            $json['upload_error'] = $files['file']['error'];
            $json['file_info'] = $files['file'];
           if (is_dir($upload_dir) && is_writable($upload_dir)) {
                $move = move_uploaded_file($files['file']['tmp_name'], 'upload/' . $file_info['file']);
            }else{
                $json['error'] = 'папка несуществует или нехватает прав для записи';
                return json_encode($json);
            }
            if($move){
                $json['file'] =$file_info['file'];
            }else{
                $json['error'] = 'Файл не загружен в папку';
            }
        }else{
            $json['error'] = 'Неверный формат файла';
        }
        return json_encode($json);
    }


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
        $pathinfo = pathinfo($files['file']['name']);
        $file = $pathinfo['basename'];
        $ext = $pathinfo['extension'];
        $filename = substr($file, 0, -strlen($ext)-1);

        return compact('file', 'ext', 'filename');
    }


    public static function exelMimeType($ext) {

        $mime_type = array(
            "xls" => "application/vnd.ms-excel",
            "xlt"    =>  "application/vnd.ms-excel",
            "xlsx"    =>  "application/vnd.openxmlformats-offedocument.spreadsheetml.sheet",
            "xla"   =>   "application/vnd.ms-excel"
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
    public function fileValidator($files)
    {
        if( !$files ){
            return false;
        }

        $file_info = $this->getFileInfo($files);

        if( $files['file']['name'] == "" ){
            array_push($this->errors, "The file name is empty");
        }

        if( !in_array( $file_info['ext'], array('xls', 'xlt', 'xlsx', 'xla') ) || !self::exelMimeType($file_info['ext']) ){
            array_push($this->errors, "The file extension is not matching");
        }

        return $this->errors ? false : true;
    }


    /**
     * get file errors
     * @return array
     */
    public function getErrors()
    {
        if(!empty($_SESSION['emptyCategory'])){
            array_push($this->errors, "Были загружены товары с пустой категорией");
            unset($_SESSION['emptyCategory']);
        }
        return $this->errors;
    }


    /**
     * read excel file and add product to bd
     * @param $filename
     * @param $lang
     * @throws PHPExcel_Reader_Exception
     */
    public function readWriteBd($data)
    {

        $json = array();
        $json['error'] = '';
        $file = 'upload/'.$data['file'];
        if(!file_exists($file)){
            $json['error'] = 'Файл не существует';
            echo json_encode($json);die;
        }
        $lang = $data['lang'];
        $root_category = !empty($data['root_category']) ? $data['root_category'] : false;


        $pathinfo = pathinfo($file);
        $fullfilename = $pathinfo['basename'];
        $ext = $pathinfo['extension'];
        $filename = substr($fullfilename, 0, -strlen($ext)-1);

        $startRow = $data['start'];
        if($startRow == 1){
            $this->clearTemp();
        }
        $chunkSize = 1000;
        $chunkFilter = new chunkReadFilter();

        $inputFileType = PHPExcel_IOFactory::identify($file);

        $objReader = PHPExcel_IOFactory::createReader($inputFileType);
        /*   $objReader->setReadDataOnly(true);
           $reader = $objReader->load($file);
           $excelSheet = $reader->getActiveSheet()->toArray();
           $cnt = count($excelSheet);*/


        $json ['end'] = 'more';
        $json ['start_row'] = $startRow;
        if($root_category){
            $json['baseCategory'] = $baseCategory = $root_category;
        }else{
            $json['baseCategory'] = $baseCategory = $this->addParentCategory($filename,$lang);

        }

            $emptyValue = 0;

            $chunkFilter->setRows($startRow,$chunkSize);
            $objReader->setReadFilter($chunkFilter);
            $objReader->setReadDataOnly(true);
            $objPHPExcel = $objReader->load($file);
            $excelSheet = $objPHPExcel->getActiveSheet()->toArray();
            $cnt = count($excelSheet);
            for($i = $startRow;$i<$startRow+$chunkSize; $i++){
                if($i == $startRow+$chunkSize - 2){
                    $json['sku'] =  $excelSheet[$i][4];
                }

                $json['empty'] = $emptyValue;
                $product = array();
                if($emptyValue > 5){
                    $this->transferProduct($lang);
                    $json ['end'] = 'end';
                    unlink($file);
                    echo json_encode($json);
                    die;
                }

                if(empty($excelSheet[$i][4]) && empty($excelSheet[$i][1]) && empty($excelSheet[$i][5])){
                    $emptyValue++;

                    continue;
                }
                $donor_category = $excelSheet[$i][1];
                $categories = explode('>',$donor_category);
                $category_id = $baseCategory;
                if(empty($categories[0])){
                    $_SESSION['emptyCategory'] = 'Пустая категория';
                }

                foreach($categories as $category){
                    $result  = $this->checkCategory($category,$json['baseCategory']);
                    if(count($result)){
                        $category_id = $result[0]['category_id'];
                    }elseif(!empty($category)){
                        $category_id = $this->addCategory($category_id,$category,$lang,$json['baseCategory']);
                    }
                }

                $product['category_id'] = $category_id;
                $product['donor_url'] = $excelSheet[$i][2];
                $product['vendor']  = $excelSheet[$i][3];
                $product['sku']  = $excelSheet[$i][4];
                $product['name']  = $excelSheet[$i][5];
                $product['image']  = $excelSheet[$i][6];
                $product['image_gallery']  = $excelSheet[$i][7];
                $product['price']  = $excelSheet[$i][8];
                $product['measure']  = $excelSheet[$i][9];
                $product['attributes']  = $excelSheet[$i][10];
                $product['description']  = $excelSheet[$i][11];
                $product['currency_name']  = $excelSheet[$i][12];
                $product['additional_materials'] = $excelSheet[$i][13];
                $product['certificates']  = $excelSheet[$i][14];

                $this->addTempProduct($product);
                /*$result  = $this->checkProduct( $product['sku']);
                if(count($result) == 0 && count($product)>0){
                    $this->addProduct($product,$lang);
                }*/
            }

            unset($objReader);

            unset($objPHPExcel);

            echo json_encode($json);





      //  unlink($file);

    }
    
    public function importDataAxiomplus($price){
      
      $lang = 'ru';
      $this->deleteDataByPriceId($price['id'], $lang);
      
 
      
//      if (file_exists(__DIR__.'/axiomplus_example.xml')) {
//          $doc = simplexml_load_file(__DIR__.'/axiomplus_example.xml');
//
//          if($doc){
//
//            $categories = array();
//            $id_root = $this->addCategoryFromXML(0, 0, $price['name'] , 'ru', $price['id'], $price['id']);
//            foreach ($doc->shop->categories->category as $category) {
//                $id = $this->addCategoryFromXML($id_root, $id_root, $category , 'ru', $category->attributes()->id, $price['id']);
//                $categories[$category->attributes()->id->__toString()] = $id;
//            }
//            
//          foreach ($doc->shop->offers->offer as $offer) {
//              $data = array();
//              $data['category_id'] = $categories[$offer->categoryId->__toString()];
//              $data['vendor_link'] = $offer->url;
//              $data['vendor_category'] = 10;
//              $data['sku'] = $offer->attributes()->id;
//              $data['vendor_photo_link'] = '';
//              $data['local_photo_link'] = $offer->picture;
//              $data['quantity'] = '';
//              $data['attributes'] = '';
//              $data['additional_materials'] = '';
//              $data['name'] = $offer->name;
//              $data['vendor'] = $offer->vendor . " : " . $offer->vendorCode;
//              $data['description'] = $offer->description;
//              $data['price'] = $offer->price;
//              $data['currency'] = $offer->currencyId;
//
//              $this->addProductFromXML($data, $lang, $categories);     
//          } 
//            
//          return true;
//          }
//      } else {
//        exit(__DIR__);
//          exit('Не удалось открыть файл axiomplus_example.xml.');
//      }
     
      
      
      
      
      if( $curl = curl_init() ) {
        curl_setopt($curl, CURLOPT_URL, $price['link']);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($_POST));
        $out = curl_exec($curl);
        curl_close($curl);
        

       
        if($out){
          $doc = new SimpleXMLElement($out);
          $categories = array();
            $id_root = $this->addCategoryFromXML(0, 0, $price['name'] , 'ru', $price['id'], $price['id']);
            foreach ($doc->shop->categories->category as $category) {
                $id = $this->addCategoryFromXML($id_root, $id_root, $category , 'ru', $category->attributes()->id, $price['id']);
                $categories[$category->attributes()->id->__toString()] = $id;
            }
          
          foreach ($doc->shop->offers->offer as $offer) {
              $data = array();
              $data['category_id'] = $categories[$offer->categoryId->__toString()];
              $data['vendor'] = $offer->vendor;
              $data['vendor_link'] = $offer->url;
              $data['vendor_category'] = 10;
              $data['sku'] = $offer->attributes()->id;
              $data['vendor_photo_link'] = $offer->picture;
              $data['local_photo_link'] = $offer->picture;
              $data['quantity'] = '';
              $data['attributes'] = '';
              $data['additional_materials'] = '';
              $data['name'] = $offer->name;
              $data['vendor'] = $offer->vendor . " : " . $offer->vendorCode;
              $data['description'] = $offer->description;
              $data['price'] = $offer->price;
              $data['currency'] = $offer->currencyId;

              $this->addProductFromXML($data, $lang, $categories);     
          }          
          
          return true;
        } else {
          return $out;
        }
        
      }
    }
    public function importDataXML($price_id){
      
      $price = $this->getPrice($price_id);
      
      switch ( $price['name'] ) {
        case 'Axiomplus':
          return  $this->importDataAxiomplus($price);

          break;

        default:
        break;
      }
      

    }
    
    
    
    
    
        /**
     * @param $file
     * @param $title
     * @return array
     */
    public function readCustomData($file, $lang, $user_id, $root_folder){

      $json = array();
      $json['error'] = '';
      $file = 'upload/'.$file;
      if(!file_exists($file)){
          $json['error'] = 'Файл не существует';
          echo json_encode($json);die;
      }     
        
      $dictionary = $this->getDictionary($user_id);
      $fields = $this->getFieldsAll();

      require_once 'PHPExcel/IOFactory.php';
      $objPHPExcel = PHPExcel_IOFactory::load($file);
      foreach ($objPHPExcel->getWorksheetIterator() as $worksheet)
      {
          $worksheetTitle     = $worksheet->getTitle();
          $highestRow         = $worksheet->getHighestRow(); // например, 10
          $highestColumn      = $worksheet->getHighestColumn(); // например, 'F'
          $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
          $nrColumns = ord($highestColumn) - 64;

          //Добавляем категорию
          $category = $worksheetTitle;


          $excelSheet = $worksheet->toArray();


          $conformity = array();
          for ($col = 0; $col < $highestColumnIndex; ++ $col) {
            $val = $excelSheet[0][$col];
            if($dictionary[$val]){
              $conformity[$fields[$dictionary[$val]]] = $col;
            }
            if($fields[$val]){
              $conformity[$fields[$val]] = $col;
            }
          }

          for ($row = 1; $row <= $highestRow; ++ $row)
          {
              $product = array();
              //индекс $conformity соответствует id таблицы fields
              $product['name']  = isset($conformity[1]) ? $excelSheet[$row][$conformity[1]] : '';
              $product['sku']  =  isset($conformity[2]) ? $excelSheet[$row][$conformity[2]] : '';
              $product['vendor']  = isset($conformity[3]) ? $excelSheet[$row][$conformity[3]] : '';
              $product['price_opt']  = isset($conformity[4]) ? (float)$worksheet->getCellByColumnAndRow($conformity[4], $row+1)->getValue() : '';
              $product['price']  = isset($conformity[5]) ? (float)$worksheet->getCellByColumnAndRow($conformity[5], $row+1)->getValue() : '';
              $product['currency']  = isset($conformity[6]) ? $excelSheet[$row][$conformity[6]] : '';

              if(isset($conformity[8]) &&  $excelSheet[$row][$conformity[8]] != ''){
                $category = $excelSheet[$row][$conformity[8]];
              } 
              
              if(isset($root_folder) && $root_folder != ''){
                $parent_id = $this->addParentCategory($root_folder, $lang, 1 , $user_id);
                $category_id = $this->addCategory($parent_id, $category,$lang, $parent_id, 1, $user_id);
              } else {
                $category_id = $this->addParentCategory($category, $lang, 1 , $user_id);
              }
              
              $product['category_id'] = is_array($category_id) ? $category_id[0]['category_id'] : $category_id;

              $product['user_id'] = $user_id;
              $product['lang'] = $lang;

              if ( isset($product['name']) && $product['name'] != ''){
                $product_id = $this->checkProductAuto($product);
                if($product_id){
                  $product['product_id'] = $product_id[0]['product_id'];
                  $this->updateCustomProduct($product);
                } else {
                  $this->addCustomProduct($product);
                } 
              }

          }



      }   
      unset($objPHPExcel);

      return json_encode($json);      
    }
    
    
    /**
     * @param $file
     * @param $title
     * @return array
    */
    public function saveCustomData($file, $lang, $user_id, $root_folder){
      $data = $this->readCustomData($file, $lang, $user_id, $root_folder);
      
      
      // TODO
      
    }

}
