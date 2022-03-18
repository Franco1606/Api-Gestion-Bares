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

    public function obtenerMozo($mozoID) {
        $query = "SELECT * FROM " . $this->tabla . " WHERE mozoID = '" . $mozoID . "'";        
        $datosMozos = parent::obtenerDatos($query);
        if($datosMozos) {
            return $datosMozos[0];
        } else {
            return 0;
        }
    }

    private function verificarExistencia(){
        $query = "SELECT * FROM " . $this->tabla . " WHERE usuario = '" . $this->usuario . "'";        
        $datosMozo = parent::obtenerDatos($query);        
        if($datosMozo) {
            return $datosMozo[0];
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
                    return $_respuestas->error_200("El usuario ". $this->usuario . " ya existe ");
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

    public function put($postBody) {
        $_respuestas = new respuestas;
        $_token = new token;        
        $datos = json_decode($postBody, true);
        $verificarToken = $_token->verificarToken($datos);
        if($verificarToken == 1){
            if(!isset($datos["mozoID"]) || !isset($datos["usuario"]) || !isset($datos["nombre"]) || !isset($datos["estado"]) || !isset($datos["password"]) || !isset($datos["newPassword"])){
                return $_respuestas->error_400();
            } else {
                $this->mozoID = $datos["mozoID"];
                $this->usuario = $datos["usuario"];
                $datosUsuario = $this->verificarExistencia();                
                if(!($datosUsuario != 0 && $datosUsuario["mozoID"] != $this->mozoID)) {
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
                        $resp = $this->modificarMozo();                               
                        if($resp) {                    
                            $respuesta = $_respuestas->response;
                            $respuesta["result"] = array(
                                "status" => "ok",                         
                                "mozoID" => $this->mozoID
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

    private function modificarMozo(){
        $query = "UPDATE " . $this->tabla . " SET usuario ='" . $this->usuario . "', nombre = '" . $this->nombre . "', estado = '" . $this->estado . "', password = '" . $this->password . "' WHERE mozoID = '" . $this->mozoID . "'";
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
            if(!isset($datos['mozoID'])){
                return $_respuestas->error_400();
            }else{
                $this->mozoID = $datos['mozoID'];
                $resp = $this->eliminarMozo();
                if($resp){                    
                    $respuesta = $_respuestas->response;
                    $respuesta["result"] = array(
                        "mozoID" => $this->mozoID
                    );                    
                    return $respuesta;
                }else{
                    return $_respuestas->error_500("Error interno del servidor, no se pudo borrar el registro o el registro no existia");
                }
            }
        }
    }

    private function eliminarMozo(){
        $query = "DELETE FROM " . $this->tabla . " WHERE mozoID = '" . $this->mozoID . "'";               
        $resp = parent::nonQuery($query);
        if($resp >= 1 ){
            return $resp;
        }else{
            return 0;
        }
    }
}

?>