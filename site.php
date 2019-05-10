<?php

use \Hcode\Page;
use \Hcode\Model\Product; 
use \Hcode\Model\Category; 
use \Hcode\Model\Cart; 
use \Hcode\Model\Address;
use \Hcode\Model\User;

$app->get('/', function() {   // rotas
    
 	$products = Product::listAll();
 	$page = new Page();
  	$page->setTpl("index", [
  		'products'=>Product::checkList($products)
  	]);

});
$app->get("/categories/:idcategory", function($idcategory){

	$page = (isset($_GET['page'])) ? (int)$_GET['page'] :1;

	$category = new Category();
	$category->get((int)$idcategory);
	
	$pagination = $category->getProductsPage($page);
   // var_dump($pagination);
    //exit;
	$pages = [];

	for ($i=1; $i <= $pagination['pages'];$i++){
		 array_push($pages, [
		 	'link'=>'/categories/'.$category->getidcategory().'?page='.$i,
		 	'page'=>$i
		 ]);
		 //var_dump($pages);

	}

	$page = new Page();
	$page->setTpl("category", [
		'category'=>$category->getValues(),
		'products'=>$pagination['data'],
		'pages'=>$pages
	]);

});
// rota para detalhes do produto
$app->get("/products/:desurl", function($desurl){

	$product = new Product();
	$product->getFromURL($desurl);
	$page = new Page();
	$page->setTpl("product-detail", [
		'product'=>$product->getValues(),
		'categories'=>$product->getCategories()
	]);


});
// rota carrinho de compras
$app->get("/cart", function(){

	$cart = Cart::getFromSession(); // verifica se usuario esta logado
   //var_dump($cart->getProducts());
   // exit;
	$page = new Page();
	$page->setTpl("cart", [
		'cart'=>$cart->getValues(),
		'products'=>$cart->getProducts(),
		'error'=>Cart::getMsgError()
	]);

});
// adiciona produto ao carrinho de compras
$app->get("/cart/:idproduct/add", function($idproduct){
    $product = new Product();
	$product->get((int)$idproduct);
	$cart = Cart::getFromSession(); // recupera o carrinho da sessão caso não exista cria um novo
	$cart->addProduct($product);
	header("Location: /cart");
	exit;
});
// remove um produto do carrinho
$app->get("/cart/:idproduct/minus", function($idproduct){
    
	//var_dump($idproduct);

	$product = new Product();
	$product->get((int)$idproduct);
	$cart = Cart::getFromSession(); // recupera o carrinho da sessão caso não exista cria um novo
	$cart->removeProduct($product);  // opcão default false
	header("Location: /cart");
	exit;
});
// remove todos os prodtos do carrinho
$app->get("/cart/:dproduct/remove", function($idproduct){

	$product = new Product();
	$product->get((int)$idproduct);
	$cart = Cart::getFromSession(); // recupera o carrinho da sessão caso não exista cria um novo
	$cart->removeProduct($product, true);  // opção para remoção de todos os produtos
	header("Location: /cart");
	exit;
});

// enviando cep para calculo do cep
$app->post("/cart/freight", function(){

	//var_dump($_POST['zipcode']);
	
	$cart = new Cart();
	$cart = Cart::getFromSession();
	$cart->setFreight($_POST['zipcode']);
	header("Location: /cart");
	exit;

});

// login chekout
$app->get("/checkout", function(){

User::verifyLogin(false); // verifica se o usuario esta logado não seja admin

$cart = Cart::getFromSession();  // pega o carrinho que esta na sessão

$address = new Address();   // nova classe contendo endereço

$page = new Page();

	$page->setTpl("checkout", [
		'cart'=>$cart->getValues(),
		'address'=>$address->getValues()
	]);	
});

// login do site
$app->get("/login", function(){

$page = new Page();

	$page->setTpl("login",[
		'error'=>User::getError()
	]);	
});

// verifica o login digitado
$app->post("/login", function(){

try {

	User::login($_POST['login'],$_POST['password']);
}
 catch(exception $e){

	User::setError($e->getMessage());

   }

header("Location: /checkout");
exit;

});

// Sair
$app->get("/logout", function(){

	User::logout();
	header("location: /login");
	exit;

});


?>
