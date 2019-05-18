<?php


use\Hcode\PageAdmin;
use\Hcode\Model\User;
use\Hcode\Model\Order;
use\Hcode\Model\OrderStatus;


// mostra status
$app->get("/admin/orders/:idorder/status", function($idorder){

	User::verifyLogin(); // verifica se o usuário é administrador

	$order = new Order();

	$order->get((int)$idorder);
	
	$page = new PageAdmin();
	
	$page->setTpl("order-status", [
		'order'=>$order->getValues(),
		'status'=>OrderStatus::listAll(),
		'msgError'=>Order::getError(),
		'msgSuccess'=>Order::getSuccess()

	]);

});

// altera status
$app->post("/admin/orders/:idorder/status", function($idorder){

	User::verifyLogin(); // verifica se o usuário é administrador

	if (!isset($_POST['idstatus']) || !(int)$_POST['idstatus'] > 0){  // testa se idstatus existe 
			// se não existir
		Order::setError("Informe o status atual.");
		header("Location: /admin/orders/".$idorder."/status");
		exit;

	}
	$order = new Order();

	$order->get((int)$idorder);
	
	$order->setidstatus((int)$_POST['idstatus']);

	$order->save();

	Order::setSuccess("Status atualizado.");

	header("Location: /admin/orders/".$idorder."/status");
		exit;
});

// excluir pedido
$app->get("/admin/orders/:idorder/delete", function($idorder){

	User::verifyLogin(); // verifica se o usuário é administrador

	$order = new Order();

	$order->get((int)$idorder);
	//var_dump($order);
	//exit;

	$order->delete();

	Header("Location: /admin/orders");
	exit;

});
// detalhes do pedido
$app->get("/admin/orders/:idorder", function($idorder){

	User::verifyLogin(); // verifica se o usuário é administrador

	$order = new Order();

	$order->get((int)$idorder);
	
	$cart = $order->getCart();

	$page = new PageAdmin();
	

	$page->setTpl("order", [
		'order'=>$order->getValues(),
		'cart'=>$cart->getValues(),
		'products'=>$cart->getProducts()
	]);

});



// aula 125 lista pedidos no adm
$app->get("/admin/orders", function (){

	User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl("orders", [
		'orders'=>Order::listAll()
	]);

});




?>
