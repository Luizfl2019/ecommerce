<?php

namespace Hcode\Model;
use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;

class Category extends Model{

	
	public static function listAll(){

       $sql = new sql();

       return $sql->select("SELECT * FROM tb_categories ORDER BY descategory");


	}

	public function save(){


		$sql = new Sql();

		$results = $sql->select("CALL sp_categories_save(:idcategory, :descategory)", array(
			":idcategory"=>$this->getidcategory(),
			":descategory"=>$this->getdescategory()
		));

			//var_dump($results);
			$this->setData($results[0]);

			Category::updateFile();  // faz update da tabela categories-me.html

	}

	public function get($idcategory){

		$sql = new Sql();

		$results = $sql->select("SELECT * from tb_categories WHERE idcategory = :idcategory", [":idcategory"=>$idcategory]);
			
		$this->setData($results[0]);
	}

	
	public function delete($idcategory){

		
		$sql = new Sql();
		$sql->query("DELETE FROM tb_categories WHERE idcategory = :idcategory", [
					":idcategory"=>$idcategory
				]);
		
		Category::updateFile();  // faz update da tabela categories-me.html

	}


	// atualizando categorias no site, salvando html categries-menu.html

	public static function updateFile()   // atualiando
	{

			$categories = Category::listAll();
			$html = []; // criando um array html
			foreach ($categories as $row) {   // cria um array com os dados da tabela categoria
				array_push($html,'<li><a href="/categories/'.$row['idcategory'].'">'.$row['descategory'].'</a></li>');
			}
				// sanvando o array dentro de arquivo categories-menu.html
			    // inplode converte array em uma string;
			file_put_contents($_SERVER['DOCUMENT_ROOT']. DIRECTORY_SEPARATOR . "views". DIRECTORY_SEPARATOR. "categories-menu.html", implode('',$html));


	}	
	public function getProducts($related=true){  

		$sql = new SqL();

		if ($related===true){

				return $sql->select("SELECT * FROM tb_products WHERE idproduct IN(
				SELECT a.idproduct
				FROM tb_products a
				INNER JOIN tb_productscategories b
				ON a.idproduct = b.idproduct
				WHERE b.idcategory = :idcategory);", [
					':idcategory'=>$this->getidcategory()
				]);	


		}else{
				return $sql->select("SELECT * FROM tb_products WHERE idproduct  NOT IN(
				SELECT a.idproduct
				FROM tb_products a
				INNER JOIN tb_productscategories b
				ON a.idproduct = b.idproduct
				WHERE b.idcategory = :idcategory);", [
					':idcategory'=>$this->getidcategory()
				
				]);	


		}

    }

    public function getProductsPage($page = 1 , $itensPerPage = 3){

    	$start = ($page - 1) * $itensPerPage;   // começa no 0
    	$sql = new Sql();

    	$results = $sql->select("
			SELECT  SQL_CALC_FOUND_ROWS *
			FROM tb_products a
			INNER JOIN tb_productscategories b ON a.idproduct = b.idproduct
			INNER JOIN tb_categories c ON c.idcategory = b.idcategory
			WHERE c.idcategory = :idcategory
			LIMIT $start,$itensPerPage;", [
				'idcategory'=>$this->getidcategory()
			]);

    		$resultTotal = $sql->select(" SELECT FOUND_ROWS() AS nrtotal");

    		return[
    			'data'=>Product::checkList($results),
    			'total'=>(int)$resultTotal[0]["nrtotal"],
    			'pages'=>ceil($resultTotal[0]["nrtotal"] / $itensPerPage) // ceil arredonda para cima numeto toral de paginas

    		];


    }

    public function addProduct(Product $product){  //  associa produto a categoria

    	
    	 $sql = new Sql();
    	 $sql->query("INSERT INTO tb_productscategories (idcategory,idproduct) VALUES (:idcategory, :idproduct)", [
    	 	':idcategory'=>$this->getidcategory(),
    	 	':idproduct'=>$product->getidproduct()
    	 ]);

    }
	public function removeProduct(Product $product){  // remove associação prodto a categoria

	    	 $sql = new Sql();
	    	 $sql->query("DELETE FROM tb_productscategories WHERE idcategory = :idcategory and idproduct = :idproduct", [
	    	 	':idcategory'=>$this->getidcategory(),
	    	 	':idproduct'=>$product->getidproduct()
	    	 ]);

    }

	// consulta todos os usuarios
		public static function getPage($page = 1 , $itensPerPage = 10){

    	$start = ($page - 1) * $itensPerPage;   // começa no 0
    	$sql = new Sql();

    	$results = $sql->select("
			SELECT  SQL_CALC_FOUND_ROWS *
			FROM tb_categories
			ORDER BY descategory
			LIMIT $start,$itensPerPage;
		");
		
    		$resultTotal = $sql->select(" SELECT FOUND_ROWS() AS nrtotal");

    		return[
    			'data'=>$results,
    			'total'=>(int)$resultTotal[0]["nrtotal"],
    			'pages'=>ceil($resultTotal[0]["nrtotal"] / $itensPerPage) // ceil arredonda para cima numero toral de paginas

    		];


    }
       // consulta via search
    public static function getPageSearch($search, $page = 1 , $itensPerPage = 10){

    	$start = ($page - 1) * $itensPerPage;   // começa no 0
    	$sql = new Sql();

    	$results = $sql->select("
			SELECT  SQL_CALC_FOUND_ROWS *
			FROM tb_categories
			WHERE descategory LIKE :search
			ORDER BY descategory
			LIMIT $start,$itensPerPage;", [
					'search'=>'%'.$search.'%'
			]);
		
    		$resultTotal = $sql->select(" SELECT FOUND_ROWS() AS nrtotal");

    		return[
    			'data'=>$results,
    			'total'=>(int)$resultTotal[0]["nrtotal"],
    			'pages'=>ceil($resultTotal[0]["nrtotal"] / $itensPerPage) // ceil arredonda para cima numero toral de paginas

    		];


    }
}    // end da classe

?>