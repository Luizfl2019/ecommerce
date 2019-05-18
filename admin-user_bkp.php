<?php

use \Hcode\Pageadmin;
use \Hcode\Model\User;

// altera senha do usuario
$app->get('/admin/users/:iduser/password', function($iduser) {
   
	User::verifyLogin(); // verifica se o usuario esta logado our tem permissao 					 //administrativa
	$user = new User();

	$suer->get((inte)$iduser);

	
	$page = new PageAdmin();
	$page->setTpl("users-password", [
		'user'=>$user->getValues(),
		'msgError'=>User::getError(),
		'msgSuccess'=>User::getSuccess()
	]);
		
});


$app->get('/admin/users', function() {
   
	User::verifyLogin(); // verifica se o usuario esta logado our tem permissao 					 //administrativa
	
	$users = User::listAll();

	$page = new PageAdmin();
	$page->setTpl("users", array (
		"users"=>$users));     // atualiza o template
});
// rota para criar um novo usuario adm
$app->get('/admin/users/create', function() {
   
	User::verifyLogin(); // verifica se o usuario esta logado our tem permissao 					 //administrativa
	$page = new PageAdmin();
	$page->setTpl("users-create");

});
// rota para excluir o usuario delete
$app->get('/admin/users/:iduser/delete', function($iduser) {
   
	User::verifyLogin();

	$user = new User();
    $user->get((int)$iduser);
	$user->delete();
	header("Location: /admin/users");
	exit;



});	

// rota para alterar o usuario adm passando o id do usuario

$app->get('/admin/users/:iduser', function($iduser) {
   
	User::verifyLogin(); // verifica se o usuario esta logado our tem permissao 					 //administrativa
	$user = new User();
	$user->get((int)$iduser);

	$page = new PageAdmin();
	$page->setTpl("users-update",array(
		"user"=>$user->getvalues()
	));
});

// rota para salvar um novo usuario metodo post
$app->post('/admin/users/create', function() {
   
	User::verifyLogin();
//************************************
	User::verifyEmail($_POST["desemail"],($_POST["deslogin"]));
	//*************************************
	$user = new User();
	$_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0; // verifica se foi definido = 1 se não 0
	$user->setData($_POST);
	$user->save();
	//var_dump($user);
	header("Location: /admin/users");
	exit;
});	
// rota para salvar a edição 
$app->post("/admin/users/:iduser", function($iduser) {
   
	User::verifyLogin();
	//var_dump($_POST);

	$user = new User();
	$_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0; // verifica se foi definido = 1 se não 0
	$user->get((int)$iduser);

	$user->setData($_POST);

	$user->update();

	header("Location: /admin/users");
	exit;
});	


?>
