<?php

namespace Hcode\Model;
use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;

class Product extends Model{

	
	public static function listAll(){

       $sql = new sql();

       return $sql->select("SELECT * FROM tb_products ORDER BY desproduct");


	}
    //  busca photos quem não são encontradas no listaAll (apoio)
	public static function checkList($list){
	   
		foreach ($list as &$row) {
			$p = new Product();
			$p->setData($row);
			$row = $p->getValues();
		}
			return $list;
	}



	public function save(){

        
		$sql = new Sql();
       
		$results = $sql->select("CALL sp_products_save(:idproduct, :desproduct, :vlprice, :vlwidth, :vlheight, :vllength, :vlweight, :desurl)", array(
			":idproduct"=>$this->getidproduct(),
			":desproduct"=>$this->getdesproduct(),
			":vlprice"=>$this->getvlprice(),
			":vlwidth"=>$this->getvlwidth(),
			":vlheight"=>$this->getvlheight(),
			":vllength"=>$this->getvllength(),
			":vlweight"=>$this->getvlweight(),
			":desurl"=>$this->getdesurl()
		));

			if (count($results) > 0){

		    		$this->setData($results[0]);
		    	} 	else {
					throw new \Exception("erro de inserção.");
				}
		    	
	}

	public function get($idproduct){

		$sql = new Sql();

		$results = $sql->select("SELECT * from tb_products WHERE idproduct = :idproduct", [":idproduct"=>$idproduct
	     ]);
			
		$this->setData($results[0]);
	}

	
	public function delete(){

		$sql = new Sql();
		$sql->query("DELETE FROM tb_products WHERE idproduct = :idproduct", [
					":idproduct"=>$this->getidproduct()
				]);
		
	}

	public function checkPhoto(){
		if (file_exists($_SERVER['DOCUMENT_ROOT']. DIRECTORY_SEPARATOR.
		"res". DIRECTORY_SEPARATOR.
		"site". DIRECTORY_SEPARATOR.
		"img". DIRECTORY_SEPARATOR.
		"products". DIRECTORY_SEPARATOR.
		$this->getidproduct().".jpg" 
	)) {
			$url =  "/res/site/img/products/".$this->getidproduct(). ".jpg";
	   }else{

	   		$url = "/res/site/img/product.jpg";  // imagem padrao cinza
	   }	

	   return $this->setdesphoto($url);


	}
	public function getValues(){

		$this->checkPhoto();
		$values = parent::getValues();
		return $values;
	}

	public function setPhoto($file){

		$extension = explode('.',$file['name']);   // explode a variavel para pegar somente a extenssão.
		$extension = end($extension);  // pega somente a estenssão

		switch($extension){

			case "jpg":
			case "jpeg":
				$image = imagecreatefromjpeg($file['tmp_name']);
			break;
			case "gif":
				$image = imagecreatefromgif($file['tmp_name']);
			break;
			case "png":
				$image = imagecreatefrompng($file['tmp_name']);
			break;
		}

		$dist = $_SERVER['DOCUMENT_ROOT']. DIRECTORY_SEPARATOR.
		"res". DIRECTORY_SEPARATOR.
		"site". DIRECTORY_SEPARATOR.
		"img". DIRECTORY_SEPARATOR.
		"products". DIRECTORY_SEPARATOR.
		$this->getidproduct().".jpg"; 	

		imagejpeg($image, $dist); // manda foto convertida para o destino
        imagedestroy($image);	  // destroy imagem temporaria
        $this->checkPhoto();      // carreg photo par memoria
	}

	public function getFromURL($desurl){

		$sql = new Sql();
       
		$rows = $sql->select("SELECT * FROM tb_products WHERE desurl = :desurl", [
			':desurl'=>$desurl
			]);
		$this->setData($rows[0]);

	}

	public function getCategories(){

		$sql = new Sql();
		return $sql->select("
			SELECT * FROM tb_categories a
			INNER JOIN tb_productscategories b 
			ON a.idcategory = b.idcategory 
			WHERE b.idproduct = :idproduct", [
				':idproduct'=>$this->getidproduct()
			]);
	}    


// consulta todos os produtos
		public static function getPage($page = 1 , $itensPerPage = 10){

    	$start = ($page - 1) * $itensPerPage;   // começa no 0
    	$sql = new Sql();

    	$results = $sql->select("
			SELECT  SQL_CALC_FOUND_ROWS *
			FROM tb_products
			ORDER BY desproduct
			LIMIT $start,$itensPerPage;
		");
		
    		$resultTotal = $sql->select(" SELECT FOUND_ROWS() AS nrtotal");

    		return[
    			'data'=>$results,
    			'total'=>(int)$resultTotal[0]["nrtotal"],
    			'pages'=>ceil($resultTotal[0]["nrtotal"] / $itensPerPage) // ceil arredonda para cima numero toral de paginas

    		];


    }
       // consulta produtos via search
    public static function getPageSearch($search, $page = 1 , $itensPerPage = 10){

    	$start = ($page - 1) * $itensPerPage;   // começa no 0
    	$sql = new Sql();

    	$results = $sql->select("
			SELECT  SQL_CALC_FOUND_ROWS *
			FROM tb_products 
			WHERE desproduct LIKE :search
			ORDER BY desproduct
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
}	// final da classe

?>