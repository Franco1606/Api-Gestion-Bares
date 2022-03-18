<?php
require_once 'clases/respuestas.class.php';
require_once 'clases/mozos.class.php';

$_respuestas = new respuestas;
$_mozos = new mozos;

if($_SERVER['REQUEST_METHOD'] == "GET"){
    if(isset($_GET['usuarioID'])) { 
        $usuarioID = $_GET['usuarioID'];
    }            
    $datosMozos = $_mozos->obtenerMozos($usuarioID);
    header("Content-Type: application/json");
    echo json_encode($datosMozos);
    http_response_code(200);
       
} else if ($_SERVER["REQUEST_METHOD"] == "POST") {
    //Recibir de datos
    $postBody = file_get_contents("php://input");   
    //Envio de datos al manejador
    $respuesta = $_mozos->post($postBody);
    //Respuesta 
    header('Content-Type: application/json');
    if(isset($respuesta["result"]["error_id"])){
        $responseCode = $respuesta["result"]["error_id"];
        http_response_code($responseCode);
    }else {
        http_response_code(200);
    }
    echo json_encode($respuesta);

} else {
    header('Content-Type: application/json');
    $respuesta = $_respuestas->error_405();
    echo json_encode($respuesta);
}

?>