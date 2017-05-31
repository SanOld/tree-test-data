<?php
session_start();

if (isset($_GET['user'])){
	$_COOKIE['user'] = $_GET['user'];
	$_SESSION['user'] = $_GET['user'];
}

if (!empty($_GET['build'])){
	$tree_html = new Tree();
    echo $tree_html->rebuild_tree($_POST['lang'], $_POST['type'], $_POST['user_id']);
    exit;
}

if (isset($_GET['loadUserProduct'])){
	$tree_html = new Tree();
    echo $tree_html->getUserProduct($_POST);
    exit;
}	

if (!empty($_GET['loadProducts'])){
	$tree_html = new Tree();
    echo $tree_html->load_products($_POST['id'], $_POST['lang'], $_POST['limit'], $_POST['page']);
    exit;
}
	
$import = new Import();

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
    if(!empty($_GET['upload'])){
        echo $import->upload($_FILES);die;
    } if(!empty($_GET['readfile'])){
        echo $import->index($_POST);die;
    } if(!empty($_POST['create_category'])){
        $import->createCategory($_POST);
    } if(!empty($_GET['importXML'])){
      echo $import->importDataXML($_POST['price_id']);die;
    } if (isset($_GET['saveCustomData'])){
    echo $import->saveCustomData($_POST['file'],$_POST['lang'], $_POST['user_id'], $_POST['root_folder']);
      
    exit;
}

}
$errors = $import->getErrors();


$tree_html = new Tree();
$settings = $tree_html->getSettings();
/*$data['search']['term'] = 'организа';
$data['type'] = 'all';
$data['lang'] = 'ru';
$settings1 = $tree_html->search_products($data);*/

if (!empty($_GET['update'])){
    $tree_html->update_tree($_POST['menuItem']);
} else if (!empty($_GET['edit'])){
    $tree_html->edit_item($_POST['id'], $_POST['title']);
} else if (!empty($_GET['add'])){
    $tree_html->add_item($_POST['id'], $_POST['title'], $_POST['lang'], $_POST['type']);
} else if (!empty($_GET['delete'])){
    $tree_html->remove_item($_POST['id']);
} else if (!empty($_GET['loadProducts'])){
    echo $tree_html->load_products($_POST['id'], $_POST['lang'], $_POST['limit'], $_POST['page']);
    exit;
} else if (!empty($_GET['loadCategory'])){
    echo $tree_html->load_category($_POST['id']);
    exit;
} else if (!empty($_GET['loadProductData'])){
    echo $tree_html->load_product_data($_POST, $_POST['lang']);
    exit;
} else if (!empty($_GET['loadComponents'])){
    echo $tree_html->load_components($_POST['product_id'], $_POST['lang']);
    exit;
} else if (!empty($_GET['build'])){
    echo $tree_html->rebuild_tree($_POST['lang'], $_POST['type'], $_POST['user_id']);
    exit;
} else if (!empty($_GET['product_add'])){
    echo $tree_html->product_add_or_edit($_POST, $_FILES);
    exit;
} else if (!empty($_GET['product_edit'])){
    echo $tree_html->product_add_or_edit($_POST, $_FILES, true);
    exit;
} else if (!empty($_GET['vendor_img'])){
    echo $tree_html->vendor_img($_POST, $_FILES);
    exit;
} else if (!empty($_GET['get_categories'])){
    echo $tree_html->get_categories($_GET);
    exit;
} else if (!empty($_GET['show_form'])){
    echo $tree_html->change_setting($_POST);
    exit;
} else if (!empty($_GET['get_products'])){
    echo $tree_html->get_products($_GET['lang']);
    exit;
} else if (!empty($_GET['components_edit'])){
    echo $tree_html->components_edit($_POST);
    exit;
} else if (!empty($_GET['deleteComponent'])){
    echo $tree_html->component_delete($_POST['component_id'], $_POST['product_id']);
    exit;
} else if (!empty($_GET['searchProduct'])) {
    echo $tree_html->sphinx_search_products($_POST);
//    echo $tree_html->search_products($_POST);
    exit;
}else if (!empty($_GET['searchComponents'])) {
    echo $tree_html->search_components($_POST);
    exit;
}else if (!empty($_GET['searchCategory'])){
    echo $tree_html->sphinx_search_categories($_POST);
//    echo $tree_html->search_categories($_POST);
    exit;
} else if (isset($_POST['count_quantity'])){
    echo $tree_html->count_quantity();
    exit;
}else if (isset($_GET['addViewProduct'])){
    echo $tree_html->viewProducts($_POST);
    exit;
}else if (isset($_GET['loadViewProduct'])){
    echo $tree_html->getViews($_POST);
    exit;
}else if (isset($_GET['loadUserProduct'])){
    echo $tree_html->getUserProduct($_POST);
    exit;
}else if (isset($_GET['deleteProduct'])){
    echo $tree_html->deleteProduct($_POST);
    exit;
}else if (isset($_GET['getCategoryParent'])){
    echo $tree_html->getProductCategories($_POST['category_id'],true);
    exit;
}else if (isset($_GET['addParentCategory'])){
    echo $tree_html->addParentCategory($_POST['category_name'],$_POST['lang'],$_POST['type'], $_POST['user_id'] );
    exit;
}else if (isset($_GET['getCategoryChildren'])){
    echo $tree_html->getCategoryChildren($_POST['category_id']);
    exit;
}else if (isset($_GET['getProductsCountByCategory'])){
    echo $tree_html->getProductsCountByCategory($_POST['category_id'],$_POST['lang']);
    exit;
}else if (isset($_GET['addItemToDictionary'])){
    echo $tree_html->add_item_to_dictionary($_POST['field_id'],$_POST['title'], $_POST['user_id']);
    exit;
}else if (isset($_GET['saveHost'])){
    echo $tree_html->save_host($_POST['category_id'],$_POST['url_visible']);
    exit;
}
