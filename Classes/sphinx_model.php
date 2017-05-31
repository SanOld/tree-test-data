<?php
require_once('sphinx.php');

use Foolz\SphinxQL\SphinxQL;
use Foolz\SphinxQL\Helper;
use Foolz\SphinxQL\Exception\DatabaseException;

class SphinxModel extends db_sphinx{

    public function getSphinxSearchProducts($data)
    {
        $search = trim($data['search']['term']);
        $select = '*';
        //$limit = 15;
		$limit = 5000;

        if (!empty($select)) {
            try {
                $products = SphinxQL::create($this->sphinx)->select($select)
                    ->from(["product_{$data['lang']}"])
                    ->match(['name','description', 'vendor_category','sku'], $search);
                if(isset($data['type']) && $data['type'] == '0'){
                    $products->where('type','=', 0);
                }
                if(isset($data['type']) && $data['type'] == '1'){
                    $products->where('type', '!=', 0);
                }

                $products->limit($limit);
				$products->option('max_matches', $limit);

                $result = $products->execute();
				//var_dump($this->getSphinxResultOrNull($result));

                return $this->getSphinxResultOrNull($result);
            } catch (DatabaseException $e) {
                echo $e->getMessage();
            }
        }

        return null;
    }

    public function getSphinxSearchCategories($data)
    {
        $search = trim($data['search']['term']);
        $select = '*';
        //$limit = 5;
		$limit = 500;

        try {
            $categories = SphinxQL::create($this->sphinx)->select($select)
                ->from(['category'])
                ->match(['name'], $search)
                ->where('language', $data['lang']);

            if(isset($data['type']) && $data['type'] == '0'){
                $categories->where('type', 0);
            }
            if(isset($data['type']) && $data['type'] == '1'){
                $categories->where('type', '!=', 0);
            }
            $categories->limit($limit);
			$categories->option('max_matches', $limit);


            $result = $categories->execute();
//var_dump($this->getSphinxResultOrNull($result));
            return $this->getSphinxResultOrNull($result);
        } catch (DatabaseException $e) {
            echo $e->getMessage();
        }
    }

    /**
     * @param $result
     * @return array|null
     */
    private function getSphinxResultOrNull($result){
        return !empty($result) ? $result->fetchAllAssoc() : array();
    }
}