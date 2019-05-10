<?php

use \Hcode\Pageadmin;
use \Hcode\Model\User;
use \Hcode\Model\Product;


// lista os produtos existentes
$app->get("/admin/products", function(){

	User::verifyLogin();
	$products = Product::listAll();
	$page = new PageAdmin();
	$page->setTpl("products",[
	   'products'=>$products
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
