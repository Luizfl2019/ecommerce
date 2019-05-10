<?php

namespace Hcode\Model;
use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;
use \Hcode\Model\User;
use \Hcode\Model\Product;



class Cart extends Model{

	const SESSION = "Cart";
	const SESSION_ERROR = "CartError";

	
	public static function getFromSession(){

		$cart = new Cart();
		//var_dump($_SESSION[Cart::SESSION]['idcart']) ;
                
		// verifica se carrinho ja existe
		if(isset($_SESSION[Cart::SESSION]) && (int)$_SESSION[Cart::SESSION]['idcart'] > 0){

			$cart->get((int)$_SESSION[Cart::SESSION]['idcart']); // existe então carrega o 
		   // var_dump($cart) ;
		  //  exit;
		} else {

			$cart->getFromSessionID();

			if (!(int)$cart->getidcart() > 0){   // se não existir carrinho

				$data = [
					'dessessionid'=>session_id()   // cria novo carrinho
				];
				
				if (User::checkLogin(false)){

					$user = User::getFromSession();
					 $data['iduser'] = $user->getiduser();
 				}
                //var_dump($user) ;
                //exit;
 				$cart->setData($data);
 				$cart->save();
 				$cart->setToSession();  // carrinho novo coloca na sessão

			}
		}	
		return $cart;
	}
	
	public function setToSession(){

		$_SESSION[Cart::SESSION] = $this->getValues();

	}
	public function getFromSessionID(){

		$sql = new Sql();
		$results = $sql->select("SELECT * FROM tb_carts WHERE dessessionid = :dessessionid", [
			':dessessionid'=>session_id()
		]);
			if (count($results)>0){
				$this->setData($results[0]);
			}
	}


	public function get(int $idcart){

		$sql = new Sql();
		$results = $sql->select("SELECT * FROM tb_carts WHERE idcart = :idcart", [
			':idcart'=>$idcart
		]);
			if (count($results)>0){
				$this->setData($results[0]);
			}
	}
	// adiciona um carrinho de compras
	public function save(){

		$sql = new Sql();
  
		$results = $sql->select("CALL sp_carts_save(:idcart, :dessessionid, :iduser, :deszipcode, :vlfreight, :nrdays)", [
			':idcart'=>$this->getidcart(),
			':dessessionid'=>$this->getdessessionid(),
			':iduser'=>$this->getiduser(),
			':deszipcode'=>$this->getdeszipcode(),
			':vlfreight'=>$this->getvlfreight(),
			':nrdays'=>$this->getnrdays()
		]);
			$this->setData($results[0]);
	}

	// aciciona produto ao carrinho
	public function addProduct(Product $product){

		$sql = new Sql();
		$sql->query("INSERT INTO tb_cartsproducts(idcart, idproduct) VALUES(:idcart, :idproduct)",[
			':idcart'=>$this->getidcart(),
			':idproduct'=>$product->getidproduct()
		]);
			$this->getCalculateTotal(); // atualiza os valores
	}

	public function removeProduct(Product $product,$all = false){
        

        //var_dump($this->getidcart());
        //var_dump($product->getidproduct());
        //exit;
		$sql = new Sql();
			if($all){  // todos os produtos com o mesmo id serão removidos

				$sql->query("UPDATE tb_cartsproducts SET dtremoved = NOW() WHERE idcart = :idcart AND idproduct = :idproduct", [
					':idcart'=>$this->getidcart(),
					':idproduct'=>$product->getidproduct()
				]);

			} else { // remove somente 1 produto 

			$sql->query("UPDATE tb_cartsproducts SET dtremoved = NOW() WHERE idcart = 					:idcart AND idproduct = :idproduct AND dtremoved IS NULL LIMIT 1", [
					':idcart'=>$this->getidcart(),
					':idproduct'=>$product->getidproduct()
				]);


			}
			$this->getCalculateTotal(); // atualiza valores
	}		
			// lista produtos que estão no carrinho
			public function getProducts(){

				$sql = new Sql();

				$rows = $sql->select("
					SELECT b.idproduct, b.desproduct, b.vlprice, b.vlwidth, b.vlheight, b.vllength, b.vlweight, b.desurl, COUNT(*) AS nrqtd, SUM(b.vlprice) AS vltotal
					FROM tb_cartsproducts a
					INNER JOIN tb_products b ON a.idproduct = b.idproduct
					WHERE a.idcart = :idcart AND a.dtremoved IS NULL
					GROUP BY b.idproduct, b.desproduct, b.vlprice, b.vlwidth, b.vlheight, b.vllength, b.vlweight, b.desurl
					ORDER BY b.desproduct", [
						':idcart'=>$this->getidcart()
					]);

					return Product::checkList($rows);



			}

	public function getProductsTotals(){

		$sql = new Sql();

		$results = $sql->select("SELECT SUM(vlprice) AS vlprice, SUM(vlwidth) AS vlwidth, SUM(vlheight) AS vlheight,
		SUM(vllength) AS vllength, SUM(vlweight) AS vlweight, COUNT(*) AS nrqtd
		FROM tb_products a 
		INNER JOIN tb_cartsproducts b ON a.idproduct = b.idproduct
		WHERE b.idcart = :idcart AND dtremoved IS NULL;
		", [
			':idcart'=>$this->getidcart()
		]);

		if (count($results) > 0) {
			return $results[0];
		}else {
			 return[];
		}

	}

	public function setFreight($nrzipcode){

        
		$nrzipcode = str_replace('-','',$nrzipcode);  // retira - da variavel se houver
		$totals = $this->getProductsTotals();
		
		if ($totals['nrqtd']>0){  // verifica se existe produto no carrinho

			if($totals['vlheight']<2) $totals['vlheight'] = 2;
			if($totals['vllength']<16) $totals['vllength'] = 16;

			$qs = http_build_query([
				'nCdEmpresa'=>'',
				'sDsSenha'=>'',
				'nCdServico'=>'40010',
				'sCepOrigem'=>'09853120',
				'sCepDestino'=>$nrzipcode,
				'nVlPeso'=>$totals['vlweight'],
				'nCdFormato'=>'1',
				'nVlComprimento'=>$totals['vllength'],
				'nVlAltura'=>$totals['vlheight'],
				'nVlLargura'=>$totals['vlwidth'],
				'nVlDiametro'=>'0',
				'sCdMaoPropria'=>'S',
				'nVlValorDeclarado'=>$totals['vlprice'],
				'sCdAvisoRecebimento'=>'SET',

			]);

			$xml = simplexml_load_file("http://ws.correios.com.br/calculador/CalcPrecoPrazo.asmx/CalcPrecoPrazo?".$qs);
			  // var_dump($xml);
			  
			   $result = $xml->Servicos->cServico;

			   if ($result->MsgErro != ''){    // testa se tem algum erro

			   		Cart::setMsgError($result->MsgErro);
			   } else{

			   	   Cart::clearMsgError();
			   }
               //var_dump($result->Valor);
               //exit;
                         

			   $this->setnrdays($result->PrazoEntrega);
			   $this->setvlfreight(Cart::formatValueToDecimal($result->Valor));
			   $this->setdeszipcode($nrzipcode);
		
			   $this->save();
			   return $result;

		}else {


		}
	}	
		public static function formatValueToDecimal($value){
           
			$value = str_replace('.', '', $value);
			return str_replace(',', '.', $value);

		}

		public static function setMsgError($msg){

			$_SESSION[Cart::SESSION_ERROR] = $msg;

		}

		public static function getMsgError(){

			$msg =  (isset($_SESSION[Cart::SESSION_ERROR])) ? $_SESSION[Cart::SESSION_ERROR] : "";
			Cart::clearMsgError();
			return $msg;

		}

		public static function clearMsgError(){

			$_SESSION[Cart::SESSION_ERROR] = NULL;
		}
		public function updateFreight(){

			//var_dump($this->getdeszipcode());
			
			if($this->getdeszipcode() != ''){
				$this->setFreight($this->getdeszipcode());
			}
		}
		public function getValues(){

			$this->getCalculateTotal();
			return parent::getValues();

		}
		public function getCalculateTotal(){

			
			$this->updateFreight();

			$totals = $this->getProductsTotals();

			$this->setvlsubtotal($totals['vlprice']);
			$this->setvltotal($totals['vlprice'] + $this->getvlfreight());
			//var_dump($this->getvlsubtotal());
			//exit;
		}

}    // end da classe

?>