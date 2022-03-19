<?php

require_once "conexion/conexion.php";
require_once "respuestas.class.php";
require_once "token.class.php";

class usuarios extends conexion {       

    public function obtenerUsuarioPorToken($tokenUsuario, $token){ 
        $_token = new token;
        $array = array($tokenUsuario => $token);              
        $verificarToken = $_token->verificarToken($array);        
        if($verificarToken == 1) {
            $datosToken = $this->buscarToken($tokenUsuario, $token);               
            switch ($tokenUsuario) {
                case "tokenAdmin":
                    $campoUsuario = "usuarioID";
                    $tabla = "usuarios";
                    $ID = $datosToken["usuarioID"];
                break;
                case "tokenMozo":
                    $campoUsuario = "mozoID";
                    $tabla = "mozos";
                    $ID = $datosToken["mozoID"];  
                break;
                case "tokenCocina":
                    $campoUsuario = "cocineroID";
                    $tabla = "cocineros";
                    $ID = $datosToken["cocineroID"];
                break;
            }         
                
            $query = "SELECT * from " . $tabla . " WHERE " . $campoUsuario . " = '" . $ID . "'";        
            $datosUsuario = parent::obtenerDatos($query);
            if($datosUsuario){
                return $datosUsuario[0];
            }else{
                return 0;
            }
        } else {
            return $verificarToken;
        }              
        
    }    

    private function buscarToken($tokenUsuario, $token){
        switch ($tokenUsuario) {
            case "tokenAdmin":
                $campoUsuario = "usuarioID";
                $tabla = "usuarios_token";
                break;
            case "tokenMozo":
                $campoUsuario = "mozoID";
                $tabla = "mozos_token";  
                break;
            case "tokenCocina":
                $campoUsuario = "cocineroID";
                $tabla = "cocineros_token";
                break;
        }
        $query = "SELECT " . $campoUsuario . " from " . $tabla . " WHERE token = '" . $token . "' AND estado = 1";        
        $datosToken = parent::obtenerDatos($query);
        if($datosToken){
            return $datosToken[0];
        }else{
            return 0;
        }
    }
    
}


?>