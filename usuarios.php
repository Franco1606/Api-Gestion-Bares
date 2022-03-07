<?php
require_once 'clases/respuestas.class.php';
require_once 'clases/usuarios.class.php';

$_respuestas = new respuestas;
$_usuarios = new usuarios;

if($_SERVER['REQUEST_METHOD'] == "GET"){
    if(isset($_GET['tokenAdmin'])) { 
        $tokenUsuario = "tokenAdmin";
        $token = $_GET['tokenAdmin'];
    } else if(isset($_GET['tokenMozo'])) {
        $tokenUsuario = "tokenMozo";
        $token = $_GET['tokenMozo'];
    } else if(isset($_GET['tokenCocina'])) {
        $tokenUsuario = "tokenCocina";
        $token = $_GET['tokenCocina'];
    }           
    $datosUsuario = $_usuarios->obtenerUsuarioPorToken($tokenUsuario, $token);
    header("Content-Type: application/json");
    echo json_encode($datosUsuario);
    http_response_code(200);
       
} else{
    header('Content-Type: application/json');
    $respuesta = $_respuestas->error_405();
    echo json_encode($respuesta);
}

?>