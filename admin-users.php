<?php

use \Hcode\Pageadmin;
use \Hcode\Model\User;


$app->get('/admin/users', function() {
    $linhas = 10;
	User::verifyLogin(); // verifica se o usuario esta logado our tem permissao 					 //administrativa
	
	$search = (isset($_GET['search'])) ? $_GET['search'] : "";  //testa variavel 
	$page = (isset($_GET['page'])) ? $_GET['page'] : 1;	  // seta page para 1 caso não exista

	if($search != ''){

			$pagination = User::getPageSearch($search,$page,$linhas);  // $linhas numero de usuarios por pagina

	}else {

		$pagination = User::getPage($page, $linhas);  // $linha = numero de usuarios por linha
	}

	$pages = [];

	for ($x = 0; $x < $pagination['pages']; $x++){
			array_push($pages, [
			'href'=>'/admin/users?'.http_build_query([
					'page'=>$x+1,
					'search'=>$search
					]),
					'text'=>$x+1
			]);			

	}

	$page = new PageAdmin();
	$page->setTpl("users", array (
		"users"=>$pagination['data'],
		"search"=>$search,
		"pages"=>$pages	
		));     // atualiza o template
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
