<?php 
require_once "clases/respuestas.class.php";
require_once "clases/sesiones.class.php";

$_sesiones = new sesiones;
$_respuestas = new respuestas;

if($_SERVER["REQUEST_METHOD"] == "GET") {
    if(isset($_GET["usuarioID"]) && isset($_GET["activas"])) {
        $usuarioID = $_GET["usuarioID"];
        $datossesiones = $_sesiones->obtenerSesionesActivas($usuarioID);
    } else if(isset($_GET["usuarioID"]) && isset($_GET["cerradas"])) {
        $usuarioID = $_GET["usuarioID"];
        $datossesiones = $_sesiones->obtenerSesionesCerradas($usuarioID);
    } else if(isset($_GET["sesionID"])) {
        $sesionID = $_GET["sesionID"];
        $datossesiones = $_sesiones->obtenerSesion($sesionID);
    }
    header("Content-Type: application/json");
    echo json_encode($datossesiones);
    http_response_code(200);

} else if($_SERVER["REQUEST_METHOD"] == "POST") {
    //Recibir de datos
    $postBody = file_get_contents("php://input");   
    //Envio de datos al manejador
    $respuesta = $_sesiones->post($postBody);
    //Respuesta 
    header('Content-Type: application/json');
    if(isset($respuesta["result"]["error_id"])){
        $responseCode = $respuesta["result"]["error_id"];
        http_response_code($responseCode);
    }else {
        http_response_code(200);
    }
    echo json_encode($respuesta);  

} else if($_SERVER['REQUEST_METHOD'] == "PUT"){
    //Recibir de datos
    $postBody = file_get_contents("php://input");
    //Envio de datos al manejador
    $respuesta = $_sesiones->put($postBody);
    //Respuesta 
    header('Content-Type: application/json');
    if(isset($respuesta["result"]["error_id"])){
        $responseCode = $respuesta["result"]["error_id"];
        http_response_code($responseCode);
    }else{
        http_response_code(200);
    }
    echo json_encode($respuesta);

} else if($_SERVER["REQUEST_METHOD"] == "DELETE") {
    //Recibir de datos
    $postBody = file_get_contents("php://input");
    //Envio de datos al manejador
    $respuesta = $_sesiones->delete($postBody);
    //Respuesta
    header('Content-Type: application/json');
    if(isset($respuesta["result"]["error_id"])){
        $responseCode = $respuesta["result"]["error_id"];
        http_response_code($responseCode);
    }else {
        http_response_code(200);
    }
    echo json_encode($respuesta);    
}

else {    
    header("Content-Type: application/json");
    $respuesta = $_respuestas->error_405();
    echo json_encode($respuesta);
} 

?>