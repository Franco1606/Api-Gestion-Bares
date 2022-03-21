<?php 
require_once "conexion/conexion.php";
require_once "respuestas.class.php";
require_once "token.class.php";

class cocineros extends conexion {    

    private $tabla = "cocineros";
    private $cocineroID;
    private $usuarioID;
    private $usuario;
    private $nombre;
    private $password;
    private $estado;   

    public function obtenerCocineros($usuarioID) {
        $query = "SELECT * FROM " . $this->tabla . " WHERE usuarioID = '" . $usuarioID . "'";        
        $datosCocineros = parent::obtenerDatos($query);
        if($datosCocineros) {
            return $datosCocineros;
        } else {
            return 0;
        }
    }

    public function obtenerCocinero($cocineroID) {
        $query = "SELECT * FROM " . $this->tabla . " WHERE cocineroID = '" . $cocineroID . "'";        
        $datosCocineros = parent::obtenerDatos($query);
        if($datosCocineros) {
            return $datosCocineros[0];
        } else {
            return 0;
        }
    }

    private function verificarExistencia(){
        $query = "SELECT * FROM " . $this->tabla . " WHERE usuario = '" . $this->usuario . "'";        
        $datosCocinero = parent::obtenerDatos($query);        
        if($datosCocinero) {
            return $datosCocinero[0];
        } else {
            return 0;
        }
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
                        $resp = $this->insertarCocinero($this->usuario, $this->nombre, $this->password, $this->estado);                                                
                        if($resp){                            
                            // si se guardo
                            $result = $_respuestas->response;
                            $result["result"] = array(
                            "cocineroID" => $resp
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
                    return $_respuestas->error_200("El usuario ". $this->usuario . " ya existe ");
                }
            }
        }
        else {
            return $verificarToken; 
        }
    }

    private function insertarCocinero(){
        $query = "INSERT INTO " . $this->tabla . " (usuario, nombre, password, estado, usuarioID) values ('" . $this->usuario . "','" . $this->nombre . "','" . $this->password . "','" . $this->estado . "','" . $this->usuarioID . "')";        
        $resp = parent::nonQueryId($query);
        if($resp){
             return $resp;
        }else{
            return 0;
        }
    }

    public function put($postBody) {
        $_respuestas = new respuestas;
        $_token = new token;        
        $datos = json_decode($postBody, true);
        $verificarToken = $_token->verificarToken($datos);
        if($verificarToken == 1){
            if(!isset($datos["cocineroID"]) || !isset($datos["usuario"]) || !isset($datos["nombre"]) || !isset($datos["estado"]) || !isset($datos["password"]) || !isset($datos["newPassword"])){
                return $_respuestas->error_400();
            } else {
                $this->cocineroID = $datos["cocineroID"];
                $this->usuario = $datos["usuario"];
                $datosUsuario = $this->verificarExistencia();                
                if(!($datosUsuario != 0 && $datosUsuario["cocineroID"] != $this->cocineroID)) {
                    $verifPass = 1;
                    if($datos["newPassword"] == null || $datos["newPassword"] == "") {
                        $this->password = $datos["password"];
                    } else {
                        $this->password = $datos["newPassword"];
                        if(strlen($this->password) < 6 || strlen($this->password) > 16) {
                            $verifPass = 0;
                        }
                    }                    
                    if($verifPass) {
                        if($datos["newPassword"] == null || $datos["newPassword"] == "") {
                            $this->password = $datos["password"];
                        } else {
                            $this->password = parent::encriptar($datos["newPassword"]);
                        }                        
                        $this->nombre = $datos["nombre"];
                        $this->estado = $datos["estado"];                
                        $resp = $this->modificarCocinero();                               
                        if($resp) {                    
                            $respuesta = $_respuestas->response;
                            $respuesta["result"] = array(
                                "status" => "ok",                         
                                "cocineroID" => $this->cocineroID
                            );
                            return $respuesta;
                        } else {
                            return $_respuestas->error_500("Error interno del servidor, el cambio no se guardo o no hubo modificaciones en el registro");
                        }
                    } else {
                        return $_respuestas->error_200("La contraseña debe tener entre 6 y 16 caracteres");
                    }
                } else {
                    //existe el usuario                
                    return $_respuestas->error_200("El usuario ". $this->usuario . " ya existe ");
                }
            }

        } else {
            return $verificarToken;
        }
    }

    private function modificarCocinero(){
        $query = "UPDATE " . $this->tabla . " SET usuario ='" . $this->usuario . "', nombre = '" . $this->nombre . "', estado = '" . $this->estado . "', password = '" . $this->password . "' WHERE cocineroID = '" . $this->cocineroID . "'";
        $resp = parent::nonQuery($query);       
        if($resp >= 1){
             return $resp;
        }else{
            return 0;
        }
    }
    
    public function delete($postBody){
        $_respuestas = new respuestas;
        $_token = new token;        
        $datos = json_decode($postBody, true);
        $verificarToken = $_token->verificarToken($datos);
        if($verificarToken == 1) {
            if(!isset($datos['cocineroID'])){
                return $_respuestas->error_400();
            }else{
                $this->cocineroID = $datos['cocineroID'];
                $resp = $this->eliminarCocinero();
                if($resp){                    
                    $respuesta = $_respuestas->response;
                    $respuesta["result"] = array(
                        "cocineroID" => $this->cocineroID
                    );                    
                    return $respuesta;
                }else{
                    return $_respuestas->error_500("Error interno del servidor, no se pudo borrar el registro o el registro no existia");
                }
            }
        }
    }

    private function eliminarCocinero(){
        $query = "DELETE FROM " . $this->tabla . " WHERE cocineroID = '" . $this->cocineroID . "'";               
        $resp = parent::nonQuery($query);
        if($resp >= 1 ){
            return $resp;
        }else{
            return 0;
        }
    }
}

?>