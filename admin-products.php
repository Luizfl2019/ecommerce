<?php

use \Hcode\Pageadmin;
use \Hcode\Model\User;
use \Hcode\Model\Product;


// lista os produtos existentes
$app->get("/admin/products", function(){

	$linhas = 10;
	User::verifyLogin(); // verifica se o usuario esta logado our tem permissao 					 //administrativa
	
	$search = (isset($_GET['search'])) ? $_GET['search'] : "";  //testa variavel 
	$page = (isset($_GET['page'])) ? $_GET['page'] : 1;	  // seta page para 1 caso não exista

	if($search != ''){

			$pagination = Product::getPageSearch($search,$page,$linhas);  // $linhas numero de usuarios por pagina

	}else {

		$pagination = Product::getPage($page, $linhas);  // $linha = numero de usuarios por linha
	}

	$pages = [];

	for ($x = 0; $x < $pagination['pages']; $x++){
			array_push($pages, [
			'href'=>'/adminproducts?'.http_build_query([
					'page'=>$x+1,
					'search'=>$search
					]),
					'text'=>$x+1
			]);			

	}
	$page = new PageAdmin();
	$page->setTpl("products",[
	   "products"=>$pagination['data'],
		"search"=>$search,
		"pages"=>$pages	

	]);

});

// executa a pagina para cadastro
$app->get("/admin/products/create", function(){

	User::verifyLogin();
	$page = new PageAdmin();
	$page->setTpl("products-create");

});

// salva o produto
$app->post("/admin/products/create", function(){

	User::verifyLogin();
	
	$product = new Product();
	$product->setData($_POST);
	$product->save();
	header("Location: /admin/products");
	exit;

});
// editar produto
$app->get("/admin/products/:idproduct", function($idproduct){

	User::verifyLogin();
	$product = new Product();
	$product->get((int)$idproduct);
	$page = new PageAdmin();
	$page->setTpl("products-update", [
		'product'=>$product->getValues()
	]);

});
// fazendo upload das photos
$app->post("/admin/products/:idproduct", function($idproduct){

	User::verifyLogin();
	$product = new Product();
	$product->get((int)$idproduct);
	$product->setData($_POST);
	$product->save();
	$product->setPhoto($_FILES['file']);
	header("Location: /admin/products");
	exit;
});
$app->get("/admin/products/:idproduct/delete", function($idproduct){

	User::verifyLogin();
	$product = new Product();
	$product->get((int)$idproduct);
	$product->delete();
	header("Location: /admin/products");
	exit;

});


?>
