<?php
require_once('sphinx.php');

use Foolz\SphinxQL\SphinxQL;
use Foolz\SphinxQL\Exception\DatabaseException;

class SphinxModel extends db_sphinx{

    public function getSphinxSearchProducts($data)
    {
        $search = trim($data['search']['term']);
        $select = '*';
        $limit = 15;

        if (!empty($select)) {
            try {
                $products = SphinxQL::create($this->sphinx)->select($select)
                    ->from(["product_{$data['lang']}"])
                    ->match(['name','description', 'vendor_category'], $search);
                if(isset($data['type']) && $data['type'] == '0'){
                    $products->where('type', 0);
                }
                if(isset($data['type']) && $data['type'] == '1'){
                    $products->where('type', '!=', 0);
                }
                $products->limit($limit);

                $result = $products->execute();

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
        $limit = 5;

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

            $result = $categories->execute();

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
        return !empty($result) ? $result : array();
    }
}