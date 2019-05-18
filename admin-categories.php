<?php

use \Hcode\Pageadmin;
use \Hcode\Model\User;
use \Hcode\Model\Category;
use \Hcode\Model\Product;



$app->get("/admin/categories", function(){

	$linhas = 10;
	User::verifyLogin(); // verifica se o usuario esta logado our tem permissao 					 //administrativa
	
	$search = (isset($_GET['search'])) ? $_GET['search'] : "";  //testa variavel 
	$page = (isset($_GET['page'])) ? $_GET['page'] : 1;	  // seta page para 1 caso não exista

	if($search != ''){

			$pagination = Category::getPageSearch($search,$page,$linhas);  // $linhas numero de usuarios por pagina

	}else {

		$pagination = Category::getPage($page, $linhas);  // $linha = numero de usuarios por linha
	}

	$pages = [];

	for ($x = 0; $x < $pagination['pages']; $x++){
			array_push($pages, [
			'href'=>'/admin/categories?'.http_build_query([
					'page'=>$x+1,
					'search'=>$search
					]),
					'text'=>$x+1
			]);			

	}

	$page = new PageAdmin();

	$page->setTpl("categories", [
	    "categories"=>$pagination['data'],
		"search"=>$search,
		"pages"=>$pages	
	]);

});

$app->get("/admin/categories/create", function(){
    
    User::verifyLogin();

	$page = new PageAdmin();

	$page->settpl("categories-create");

});
// enviando dados por post
$app->post("/admin/categories/create", function(){

	User::verifyLogin();
	
	$category = new Category();

	$category->setData($_POST);

	$category->save();

	header("Location: /admin/categories");
	exit;

});

$app->get("/admin/categories/:idcategory/delete", function($idcategory){

	User::verifyLogin();

	 $category = new Category();

	 //$category->get((int)$idcategory);

	 $category->delete($idcategory);
	// $category->delete((int)$idcategory);

	header("Location: /admin/categories");
	exit;

});
 // alteração categoria carrega as informaçoes da categria
$app->get("/admin/categories/:idcategory", function($idcategory){

	User::verifyLogin();
	$category = new Category();
	
	$category->get((int)$idcategory);

	$page = new PageAdmin();

	$page->setTpl("categories-update", [
		'category'=>$category->getValues()
	]);
	

});
//   salva os dados da categorria alterados
$app->post("/admin/categories/:idcategory", function($idcategory){

	User::verifyLogin();

	$category = new Category();
	$category->get((int)$idcategory);

	$category->setData($_POST);
	$category->save();

	header("Location: /admin/categories");
	exit;
});

// lista produtos por categorias
$app->get("/admin/categories/:idcategory/products", function($idcategory){

   User::verifyLogin();

   $category = new Category();
   $category->get((int)$idcategory);
   $page = new Pageadmin();
   $page->setTpl("categories-products", [
		'category'=>$category->getValues(),
		'productsRelated'=>$category->getProducts(),
		'productsNotRelated'=>$category->getProducts(false)
	]);

});

// relaciona categoria ao produto
$app->get("/admin/categories/:idcategory/products/:idproduct/add", function($idcategory, $idproduct){

   User::verifyLogin();

   $category = new Category();
   $category->get((int)$idcategory);
   $product = new Product();
   $product->get((int)$idproduct);
   $category->addProduct($product);
   header("Location: /admin/categories/".$idcategory."/products");
   exit;

});
// remove categoria do produto
$app->get("/admin/categories/:idcategory/products/:idproduct/remove", function($idcategory, $idproduct){

   User::verifyLogin();

   $category = new Category();
   $category->get((int)$idcategory);
   $product = new Product();
   $product->get((int)$idproduct);
   $category->removeProduct($product);
   header("Location: /admin/categories/".$idcategory."/products");
   exit;

});






?>