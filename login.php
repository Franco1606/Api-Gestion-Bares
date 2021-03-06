<?php 
require_once 'clases/login.class.php';
require_once 'clases/respuestas.class.php';

$_login = new login;
$_respuestas = new respuestas;

if($_SERVER['REQUEST_METHOD'] == "POST"){

    //Recibir datos
    $postBody = file_get_contents("php://input");    
           
    //Envio de datos al manejador
    $response = $_login->login($postBody);
    
    //Respuesta
    header('Content-Type: application/json');
    if(isset($response["result"]["error_id"])){
        $responseCode = $response["result"]["error_id"];
        http_response_code($responseCode);
    }else{
        http_response_code(200);
    }
    echo json_encode($response);

}else{
    header('Content-Type: application/json');
    $response = $_respuestas->error_405();
    echo json_encode($response);
}
?>