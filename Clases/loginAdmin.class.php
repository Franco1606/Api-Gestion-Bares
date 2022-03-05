<?php
require_once 'conexion/conexion.php';
require_once 'respuestas.class.php';

class loginAdmin extends conexion{

    public function login($json){
        $_respustas = new respuestas;
        $datos = json_decode($json,true);        
        if(!isset($datos['usuario']) || !isset($datos["password"])){
            //Error con los campos
            return $_respustas->error_400();
        }else{
            //Datos enviados correctos  
            $usuario = $datos['usuario'];
            //Validacion de formato de mail
            if(filter_var($usuario, FILTER_VALIDATE_EMAIL)){
                $password = $datos['password'];
                //Validacion longitud password
                if(strlen($password)>=6 && strlen($password)<= 16) {
                    $password = parent::encriptar($password);
                    $datos = $this->obtenerDatosUsuario($usuario);
                    if($datos){
                        //verificar si la contraseña es igual
                            if($password == $datos['password']){
                                //verificar estado del usuario
                                    if($datos['estado'] == 1){                                       
                                        //crear el token
                                        $verificar  = $this->insertarToken($datos['usuarioID']);
                                        if($verificar){
                                                // si se guardo
                                                $result = $_respustas->response;
                                                $result["result"] = array(
                                                    "token" => $verificar
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

    private function obtenerDatosUsuario($usuario){
        $query = "SELECT * FROM usuarios WHERE usuario = '$usuario'";
        $datos = parent::obtenerDato($query);
        if($datos["usuarioID"]){
            return $datos;
        }else{
            return 0;
        }
    }


    private function insertarToken($usuarioID){
        $val = true;
        $token = bin2hex(openssl_random_pseudo_bytes(16,$val));
        $date = time();
        $estado = 1;
        $query = "INSERT INTO usuarios_token (usuarioID,token,estado,fecha)VALUES('$usuarioID','$token','$estado','$date')";
        $verifica = parent::nonQuery($query);
        if($verifica){
            return $token;
        }else{
            return 0;
        }
    }

    private function eliminarTokensAnteriores($usuarioID) {
        $query = "DELETE FROM usuarios_token WHERE usuarioID= '" . $usuarioID . "'";
        parent::nonQuery($query);
    }    

    private function actualizarToken($token){
        $tiempoActual = time();
        $query = "UPDATE usuarios_token SET fecha = '$tiempoActual' WHERE token = '$token' ";
        $resp = parent::nonQuery($query);
        if($resp >= 1){
            return $resp;
        }else{
            return 0;
        }
    }

}
?>