<?php 
require_once "conexion/conexion.php";
require_once "respuestas.class.php";
require_once "token.class.php";

class mozos extends conexion {    

    private $tabla = "mozos";
    private $mozoID;
    private $usuarioID;
    private $usuario;
    private $nombre;
    private $password;
    private $estado;   

    public function obtenerMozos($usuarioID) {
        $query = "SELECT * FROM " . $this->tabla . " WHERE usuarioID = '" . $usuarioID . "'";        
        $datosMozos = parent::obtenerDatos($query);
        if($datosMozos) {
            return $datosMozos;
        } else {
            return 0;
        }
    }

    private function verificarExistencia(){
        $query = "SELECT mozoID FROM " . $this->tabla . " WHERE usuario = '" . $this->usuario . "'";        
        $datos = parent::obtenerDatos($query);        
        return count($datos);
    }

    public function post($postBody) {
        $_respuestas = new respuestas;
        $_token = new token;        
        $datos = json_decode($postBody, true);
        $verificarToken = $_token->verificarToken($datos);                 
        if($verificarToken == 1) {                       
            if(!isset($datos['usuario']) || !isset($datos['nombre']) || !isset($datos['password']) || !isset($datos['estado']) || !isset($datos['usuarioID'])){
                return $_respuestas->error_400();
            } else {                
                $this->usuario = $datos['usuario'];
                $this->usuarioID = $datos['usuarioID'];                                                                                                      
                if(!$this->verificarExistencia()) {
                    $this->password = $datos['password'];                
                    if(strlen($this->password)>=6 && strlen($this->password)<= 16) {
                        $this->password = parent::encriptar($this->password);
                        $this->usuarioID = $datos['usuarioID'];
                        $this->nombre = $datos['nombre'];
                        $this->estado = $datos['estado'];                                              
                        $resp = $this->insertarMozo($this->usuario, $this->nombre, $this->password, $this->estado);                                                
                        if($resp){                            
                            // si se guardo
                            $result = $_respuestas->response;
                            $result["result"] = array(
                            "mozoID" => $resp
                            );                            
                            return $result;
                        } else {
                            //error al guardar
                            return $_respuestas->error_500("Error interno, no hemos podido guardar");
                        }  
                    } else {
                        return $_respuestas->error_200("La contraseña debe tener entre 6 y 16 caracteres");
                    }
                } else {
                    //existe el usuario                
                    return $_respuestas->error_200("El usuario ". $this->usuario . "ya existe ");
                }
            }
        }
        else {
            return $verificarToken; 
        }
    }

    private function insertarMozo(){
        $query = "INSERT INTO " . $this->tabla . " (usuario, nombre, password, estado, usuarioID) values ('" . $this->usuario . "','" . $this->nombre . "','" . $this->password . "','" . $this->estado . "','" . $this->usuarioID . "')";        
        $resp = parent::nonQueryId($query);
        if($resp){
             return $resp;
        }else{
            return 0;
        }
    }
}

?>