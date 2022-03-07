<?php
require_once "respuestas.class.php";
require_once "conexion/conexion.php";

class token extends conexion {

    private $tiempoSesion = 3600;

    public function verificarToken($datos) {                 
        $_respuestas = new respuestas;         
        if(!(isset($datos['tokenAdmin']) || isset($datos['tokenMozo'])) || isset($datos['tokenCocina'])){            
            return $_respuestas->error_401();

        } else{            
            if(isset($datos['tokenAdmin'])) {
                $token = $datos['tokenAdmin'];
                $tokenUsuario = "tokenAdmin";
            } else if(isset($datos['tokenMozo'])) {
                $token = $datos['tokenMozo'];
                $tokenUsuario = 'tokenMozo';
            } else if(isset($datos['tokenCocina'])) {
                $token = $datos['tokenCocina'];
                $tokenUsuario = 'tokenCocina';
            }            
            $datosToken = $this->buscarToken($datos);                                                
            if($datosToken){                               
                $_respuestas = new respuestas;
                $timpoActual = time();
                $timpoToken = $datosToken["tiempo"];                                
                if($timpoActual-$timpoToken > $this->tiempoSesion) {
                    $this->eliminarToken($datos);                  
                    return $_respuestas->error_401("El Token que envio es invalido o ha caducado");                
                }
                else {
                    $this->actualizarToken($datos);
                    $verificacion = 1;                    
                    return $verificacion;
                }
            }else {
                return $_respuestas->error_401("El Token que envio es invalido o ha caducado");
            }
        }
    }

    private function buscarToken($datos){
        if(isset($datos['tokenAdmin'])) {
            $token = $datos['tokenAdmin'];
            $tabla = "usuarios_token";            
        } else if(isset($datos['tokenMozo'])) {
            $token = $datos['tokenMozo'];
            $tabla = 'mozos_token';            
        } else if(isset($datos['tokenCocina'])) {
            $token = $datos['tokenCocina'];
            $tabla = 'cocineros_token';            
        }
        $query = "SELECT * from " . $tabla . " WHERE token = '" . $token . "' AND estado = 1";                
        $datosToken = parent::obtenerDatos($query);
        if($datosToken){
            return $datosToken[0];
        }else{
            return 0;
        }
    }

    private function actualizarToken($datos){
        if(isset($datos['tokenAdmin'])) {
            $token = $datos['tokenAdmin'];
            $tabla = "usuarios_token";
        } else if(isset($datos['tokenMozo'])) {
            $token = $datos['tokenMozo'];
            $tabla = 'mozos_token';
        } else if(isset($datos['tokenCocina'])) {
            $token = $datos['tokenCocina'];
            $tabla = 'cocineros_token';
        }        
        $tiempoActual = time();
        $query = "UPDATE " . $tabla . " SET tiempo = '$tiempoActual' WHERE token = '$token' ";
        $resp = parent::nonQuery($query);
        if($resp >= 1){            
            return $resp;
        }else{
            return 0;
        }
    }
    
    private function eliminarToken($datos) {
        if(isset($datos['tokenAdmin'])) {
            $token = $datos['tokenAdmin'];
            $tabla = "usuarios_token";
        } else if(isset($datos['tokenMozo'])) {
            $token = $datos['tokenMozo'];
            $tabla = 'mozos_token';
        } else if(isset($datos['tokenCocina'])) {
            $token = $datos['tokenCocina'];
            $tabla = 'cocineros_token';
        }
        $query = "DELETE FROM " . $tabla . " WHERE token= '" . $token . "'";
        parent::nonQuery($query);
    }
}

?>