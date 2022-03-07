<?php
require_once 'conexion/conexion.php';
require_once 'respuestas.class.php';

class login extends conexion{

    public function login($json){
        $_respustas = new respuestas;
        $datos = json_decode($json,true);        
        if((!isset($datos['usuarioAdmin']) && !isset($datos['usuarioCocina']) && !isset($datos['usuarioMozo'])) || !isset($datos["password"])){
            //Error con los campos
            return $_respustas->error_400();
        }else{             
            //Datos enviados correctos
            $validacionMail = true;
            if(isset($datos['usuarioAdmin'])) { 
                if(filter_var($datos['usuarioAdmin'], FILTER_VALIDATE_EMAIL)) {
                    $usuario = $datos["usuarioAdmin"];
                } else {
                    $validacionMail = false;
                }
            }
            if(isset($datos['usuarioCocina'])) { $usuario = $datos["usuarioCocina"];}
            if(isset($datos['usuarioMozo'])) { $usuario = $datos["usuarioMozo"];}                                   
            //Validacion de formato de mail para usuarioAdmin
            if($validacionMail){
                $password = $datos['password'];
                //Validacion longitud password
                if(strlen($password)>=6 && strlen($password)<= 16) {
                    $password = parent::encriptar($password);                                       
                    $datosUsuario = $this->obtenerDatosUsuario($datos);                                    
                    if($datosUsuario){
                        //verificar si la contraseña es igual
                            if($password == $datosUsuario['password']){
                                //verificar estado del usuario
                                    if($datosUsuario['estado'] == 1){                                                                               
                                        //crear el token                                        
                                        $token  = $this->insertarToken($datosUsuario);                                         
                                        if($token){
                                                // si se guardo
                                                $result = $_respustas->response;
                                                $result["result"] = array(
                                                    "token" => $token
                                                );
                                                return $result;
                                        }else{
                                                //error al guardar
                                                return $_respustas->error_500("Error interno, No hemos podido guardar");
                                        }
                                    }else{
                                        //el usuario esta inactivo
                                        return $_respustas->error_200("El usuario esta inactivo");
                                    }
                            }else{
                                //la contraseña no es igual
                                return $_respustas->error_200("Contraseña incorrecta");
                            }
                    }else{
                        //no existe el usuario
                        return $_respustas->error_200("El usuaro $usuario  no existe ");
                    }
                } else  {
                    return $_respustas->error_200("La contraseña debe tener entre 6 y 16 caracteres");
                }
            } else {
                return $_respustas->error_200("El formato del correo es incorrecto");
            }
        }
    }

    private function obtenerDatosUsuario($datosJsn){
        if(isset($datosJsn["usuarioAdmin"])) {
            $table = "usuarios";
            $usuario = $datosJsn["usuarioAdmin"];
        } else if (isset($datosJsn["usuarioMozo"])) {
            $table = "mozos";
            $usuario = $datosJsn["usuarioMozo"];
        } else if(isset($datosJsn["usuarioCocina"])) {
            $table = "cocineros";
            $usuario = $datosJsn["usuarioCocina"];
        }
        $query = "SELECT * FROM ". $table ." WHERE usuario = '$usuario'";        
        $datos = parent::obtenerDatos($query);                       
        if($datos != null){
            return $datos[0];
        }else{
            return 0;
        }
    }

    private function insertarToken($datosUsuario){
        if(isset($datosUsuario["usuarioID"])) {
            $table = "usuarios_token";
            $ID = $datosUsuario["usuarioID"];
            $campoUsuario = "usuarioID";
        } else if (isset($datosUsuario["mozoID"])) {
            $table = "mozos_token";
            $ID = $datosUsuario["mozoID"];
            $campoUsuario = "mozoID";
        } else if(isset($datosUsuario["cocineroID"])) {
            $table = "cocineros_token";
            $ID = $datosUsuario["cocineroID"];
            $campoUsuario = "cocineroID";
        }
        $val = true;
        $token = bin2hex(openssl_random_pseudo_bytes(16,$val));
        $tiempo = time();
        $estado = 1;
        $query = "INSERT INTO " . $table . " (" . $campoUsuario . ",token,estado,tiempo)VALUES('$ID','$token','$estado','$tiempo')";                      
        $verifica = parent::nonQuery($query);
        if($verifica){
            return $token;
        }else{
            return 0;
        }
    }
}
?>