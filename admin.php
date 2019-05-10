<?php

use \Hcode\Pageadmin;
use \Hcode\Model\User;


$app->get('/admin', function() {   // rotas
    
  User::verifyLogin();  // verifica se o usuario está logado

  $page = new PageAdmin();
  $page->setTpl("index");



});
// rota para abrir a tela de login
$app->get('/admin/login', function() {
   
	$page = new PageAdmin([      
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("login");

});
// rota para teste de login de senha
$app->post('/admin/login', function(){

		User::login($_POST["login"], $_POST["password"]);
		header('location: /admin');
		exit;

});
// rota para sair da pagina admin
$app->get('/admin/logout', function() {

	User::logout();
	header("Location: /admin/login");
	exit;
});

// rotas para recuperação de senha
$app->get("/admin/forgot", function(){

$page = new PageAdmin([      
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("forgot");


});


// rotas pega o valor do email e consulta na tabela se existe
$app->post("/admin/forgot", function(){

 
 $user =  User::getForgot($_POST["email"]);

 header("Location: /admin/forgot/sent");
 exit;

});

$app->get("/admin/forgot/sent", function (){

$page = new PageAdmin([      
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("forgot-sent");

});

$app->get("/admin/forgot/reset", function(){

$user = User::validForgotDecrypt($_GET["code"]);

$page = new PageAdmin([      
		"header"=>false,
		"footer"=>false
	]);

$page->setTpl("forgot-reset", array(
	"name"=>$user["desperson"],
	"code"=>$_GET["code"]
));

});

$app->post("/admin/forgot/reset", function(){  // inserção da nova senha
 //echo "Senha = ".$_POST["password"];
	$forgot = User::validForgotDecrypt($_POST["code"]);

	User::setForgotUsed($forgot["idrecovery"]);   // verifica se a senha ja foi utilizada e se esta no periodo de 1h.
	$user = new User();

	$user->get((int)$forgot["iduser"]);  // dados do usuario

	$password = password_hash($_POST["password"], PASSWORD_DEFAULT,[
		"cost"=>12
		]);

	$user->setPassword($password);

	$page = new PageAdmin([      
		"header"=>false,
		"footer"=>false
	]);

	$page->setTpl("forgot-reset-success");


});

?>