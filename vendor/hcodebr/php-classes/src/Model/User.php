<?php

namespace Hcode\Model;
use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;

class User extends Model{

		const SESSION = "User";
		const SECRET = "HcodePhp7_Secret";	  // chave para criptografia minimo com 16 digitos
        const ERROR = "UserError";
        const ERROR_REGISTER = "UserErrorRegister";
        const SUCCESS = "UserSuccess";

		public static function getFromSession(){

			$user = new User();
			if(isset($_SESSION[User::SESSION]) && (int)$_SESSION[User::SESSION]['iduser'] > 0){

				$user->setData($_SESSION[User::SESSION]);
				
			}

		return  $user;
		}


		public static function checkLogin($inadmin = true){

			if(
				!isset($_SESSION[User::SESSION])
				||
				!$_SESSION[User::SESSION]   
				||
				!(int)$_SESSION[User::SESSION]["iduser"] > 0 
			){ 
				// não esta logado
				return false;
			
			}else {
				if ($inadmin === true && (bool)$_SESSION[User::SESSION]['inadmin'] === true){
					// usuario esta logado e é um administrador
					return true;
				
				} else if ($inadmin == false) {
				    // usuario é um administrador
				  return  true;
				
				} else{
					// não esta logado nem é um administrador
					return false;
				}
			}
		}
	
		public static function login($login, $password)
		{
				
				$sql = new  Sql();
				$results = $sql->select("SELECT * FROM tb_users a
					 INNER JOIN tb_persons b
					 ON a.idperson = b.idperson
					 WHERE a.deslogin = :LOGIN", array(
						":LOGIN"=>$login		
					));
				//var_dump($results);
				//exit;
				if (count($results) === 0){


					throw new \Exception("Usuário inexistente ou senha inválida."); // contra barra para achar a exception prncipal
				}

				$data = $results[0];

				if (password_verify($password, $data["despassword"])=== true){
					
					$user = new User();

					$data['desperson'] = utf8_encode($data['desperson']);
					$user->setData($data);
					$_SESSION[User::SESSION] = $user->getValues();

					return $user;
					//$user->setData($data["iduser"]);
					//var_dump($user);
					//exit;

				} else {
					throw new \Exception("Usuário inexistente ou senha inválida.");
				}
                
		}

public static function verifyLogin($inadmin = true){
    

	if( !User::checkLogin($inadmin))
	{ 
			if ($inadmin){
				header("Location: /admin/login");
			} else {
					header("Location: /login");
			}
            exit;
	}

}
	//*************************************
	public static function verifyEmail($email,$login){
		
		$sql = new Sql();

				$results = $sql->select("
				SELECT a.desemail, b.deslogin 
	    		FROM tb_persons a
	    		INNER JOIN tb_users b USING(idperson)
	    		WHERE a.desemail = :desemail || b.deslogin = :deslogin;
	    		", array(
		    			":desemail"=>$email,
		    			":deslogin"=>$login
		    		));
		    	
		        var_dump(count($results));
		    	
		    	if (count($results) > 0){

		    		header("Location: /admin/users/create");
		    	}
		    	

	}
	// **************************************
	public static function logout(){
		$_SESSION[User::SESSION] = NULL;
	}


	public static function listAll(){

       $sql = new sql();

       return $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) ORDER BY 	desperson");


	}


	public function save(){


		$sql = new Sql();
		

		$results = $sql->select("CALL sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
			":desperson"=>utf8_decode($this->getdesperson()),
			":deslogin"=>$this->getdeslogin(),
			":despassword"=>User::getPasswordHash($this->getdespassword()),
			":desemail"=>$this->getdesemail(),
			":nrphone"=>$this->getnrphone(),
			":inadmin"=>$this->getinadmin()
		));

			
			$data = $results[0];
		    $data['desperson'] = utf8_encode($data['desperson']);
			$this->setData($data);

	}
	public function get($iduser){

		$sql = new Sql();
		$results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser = :iduser", array(
			":iduser"=>$iduser
		));

		$data = $results[0];
		$data['desperson'] = utf8_encode($data['desperson']);
		
		$this->setData($data);

	}

	public function update(){

	$sql = new Sql();
	
	$results = $sql->select("CALL sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
			"iduser"=>$this->getiduser(),
			":desperson"=>utf8_decode($this->getdesperson()),
			":deslogin"=>$this->getdeslogin(),
			":despassword"=>User::getPasswordHash($this->getdespassword()),
			":desemail"=>$this->getdesemail(),
			":nrphone"=>$this->getnrphone(),
			":inadmin"=>$this->getinadmin()
		));
      
		$data = $results[0];
		$data['desperson'] = utf8_encode($data['desperson']);
		$this->setData($data);

	}

	public function delete(){
		$sql = new Sql();
		$sql->query("CALL sp_users_delete(:iduser)",array(
         ":iduser"=>$this->getiduser()
		));
		
    }

    public static function getForgot($email, $inadmin = true){
        
    	$sql = new Sql();

    	$results = $sql->select("
    		SELECT * 
    		FROM tb_persons a
    		INNER JOIN tb_users b USING(idperson)
    		WHERE a.desemail = :email; 
    		", array(
    			":email"=>$email
    		));
    	if (count($results)===0){

    		throw new \Eception("Não foi possível recuperar a senha.");
    	}
    	else {
  				
	  			$data = $results[0];

		    	$results2 = $sql->select("CALL sp_userspasswordsrecoveries_create(:iduser, :desip)", array(
		    		":iduser"=>$data["iduser"],
		    		":desip"=>$_SERVER["REMOTE_ADDR"]
		    	));	 

		    	IF (count($results2)===0){

		    		throw new \Eception("Não foi possível recuperar a senha.");
		    	} 
		    	else{
		    		$dataRecovery = $results2[0];
		    		//encripta idrecovery com 128 bits no mode ECB
		    		$code = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128,User::SECRET,$dataRecovery["idrecovery"],MCRYPT_MODE_ECB));

		    		// cria link para ser enviado por e-mail para
		    		//recuperação da senha
		    		if ($inadmin === true){  // rota para redefinir a senha do administrador

		    			$link = "http://www.hcodecommerce.com.br/admin/forgot/reset?code=$code";
		     		
		     		} else{                 // rota para redefinir a senha para o usuario comum
		     		
		     			$link = "http://www.hcodecommerce.com.br/forgot/reset?code=$code";
		     		
		     		}
		    		$mailer = new Mailer($data["desemail"],$data["desperson"],"Redefinir Senha do Digibusca ","forgot", array(
		    			"name"=>$data["desperson"],
		    			"link"=>$link
		    		));
                    //var_dump($mailer);
		    		$mailer->send();
		    		return $data;
	    	}

    	}


    }

    public static function validForgotDecrypt($code){

    	   
    	  	$idrecovery = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, User::SECRET, base64_decode($code), MCRYPT_MODE_ECB);
            $idrecovery = (int)$idrecovery;
            //var_dump($code);
            //var_dump($idrecovery);
            
    	  	$sql = new Sql();

    	  	$results = $sql->select("
    	  		SELECT *
				FROM tb_userspasswordsrecoveries a
				INNER JOIN tb_users b USING(iduser)
				INNER JOIN tb_persons c USING(idperson)
				WHERE
				    a.idrecovery = :idrecovery
				    AND 
				    a.dtrecovery IS NULL
				    AND
				    DATE_ADD(a.dtregister, INTERVAL 1 HOUR) >= NOW();
				    ", array(
				    	":idrecovery"=>$idrecovery
				    ));

    	  	    //echo " count= ".count($results);
    	  		if (count($results)===0){

    	  			throw new \Eception("Não foi possível recuperar a senha.");

    	  		}else{

    	  			return $results[0];

    	  		}


    }
    public static function setForgotUsed($idrecovery){
        //echo "---> ".$idrecovery;
    	$sql = new Sql();
    	$sql->query("UPDATE tb_userspasswordsrecoveries SET dtrecovery = NOW() WHERE idrecovery = :idrecovery", array(
    		":idrecovery"=>$idrecovery
    	));
    }
    public function setPassword($password){
        //echo "---> ".$password;
    	$sql = new Sql();
    	$sql->query("UPDATE tb_users SET despassword = :password WHERE iduser = :iduser", array(
    		":password"=>$password,
    		":iduser"=>$this->getiduser()
    	));
    }

   

		public static function setError($msg){

			$_SESSION[User::ERROR] = $msg;

		}

		public static function getError(){

			$msg =  (isset($_SESSION[User::ERROR]) &&  $_SESSION[User::ERROR]) ? $_SESSION[User::ERROR] : "";
			User::clearError();
			return $msg;

		}

		public static function clearError(){

			$_SESSION[User::ERROR] = NULL;
		}

       
		// mensagens de erro sucess
		public static function setSuccess($msg){

			$_SESSION[User::SUCCESS] = $msg;

		}

		public static function getSuccess(){

			$msg =  (isset($_SESSION[User::SUCCESS]) &&  $_SESSION[User::SUCCESS]) ? $_SESSION[User::SUCCESS] : "";
			User::clearSuccess();
			return $msg;

		}

		public static function clearSuccess(){

			$_SESSION[User::SUCCESS] = NULL;
		}


		public static function setErrorRegister($msg){

			
			$_SESSION[User::ERROR_REGISTER] = $msg;
			//var_dump($_SESSION[User::ERROR_REGISTER]);
			//exit;

		}

		public static function getErrorRegister(){

			$msg =  (isset($_SESSION[User::ERROR_REGISTER]) &&  $_SESSION[User::ERROR_REGISTER]) ? $_SESSION[User::ERROR_REGISTER] : "";
			User::clearErrorRegister();
			return $msg;

		}

		public static function clearErrorRegister(){

			$_SESSION[User::ERROR_REGISTER] = NULL;
		}

		public static function checkLoginExists($login){

			$sql = new Sql();
			$results = $sql->select("SELECT deslogin from tb_users 	where deslogin = :deslogin",[
				'deslogin'=>$login
			]);
			return (count($results)> 0);    // se = 0 login = false

		}


		public static function getPasswordHash($password){

			return password_hash($password, PASSWORD_DEFAULT, [
				'cost'=>12
			]);
		}

		public function getOrders(){

			$sql = new Sql();

			$results = $sql->select("
				SELECT *
				FROM tb_orders a
				INNER JOIN tb_ordersstatus b USING(idstatus)
				INNER JOIN tb_carts c USING(idcart)
				INNER JOIN tb_users d ON d.iduser = a.iduser
				INNER JOIN tb_addresses e USING(idaddress)
				INNER JOIN tb_persons f ON f.idperson = d.idperson
				WHERE a.iduser = :iduser", [
				':iduser'=>$this->getiduser()				
				]);
				
			return $results;
		}


}	// end da classe	

?>