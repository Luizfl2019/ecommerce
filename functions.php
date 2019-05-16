<?php

use \Hcode\Model\User;
use \Hcode\Model\Cart;

function formatPrice($vlprice){

	if(!$vlprice > 0) $vlprice = 0;  
	return number_format($vlprice, 2,",",".");
}


function checkLogin($inadmin = true){

	return User::checkLogin($inadmin);
}

function getUserName(){

	$user = User::getFromSession();
	return $user->getdesperson();
}

function getCartNrQtd(){  // carrega os carrinho a quantidade de pedidos

	$cart = Cart::getFromSession();
	$totals = $cart->getProductsTotals();

	return $totals['nrqtd'];


}
function getCartVlSubtotal(){  // carrega os carrinho valor dos pedidos

	$cart = Cart::getFromSession();
	$totals = $cart->getProductsTotals();

	return FormatPrice($totals['vlprice']);


}

?>