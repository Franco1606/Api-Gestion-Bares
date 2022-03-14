<?php 
require_once "clases/respuestas.class.php";
require_once "clases/imagenes.class.php";

$imagenes = new imagenes;
$_respuestas = new respuestas;

if($_SERVER["REQUEST_METHOD"] == "GET") {
    if(isset($_GET["usuarioID"]) && isset($_GET["nombre"])) {
        $usuarioID = $_GET["usuarioID"];
        $nombre = $_GET["nombre"];        
        $datosimagenes = $imagenes->obtenerImagenPorNombre($usuarioID, $nombre);
    } else if(isset($_GET["usuarioID"])) {
        $usuarioID = $_GET["usuarioID"];
        $datosimagenes = $imagenes->obtenerImagenes($usuarioID);
    }
    header("Content-Type: application/json");
    echo json_encode($datosimagenes);
    http_response_code(200);

} else if($_SERVER["REQUEST_METHOD"] == "POST") {
    //Recibir de datos
    $postBody = file_get_contents("php://input");    
    //Envio de datos al manejador    
    $respuesta = $imagenes->post($postBody);
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
    $respuesta = $imagenes->put($postBody);
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
    $respuesta = $imagenes->delete($postBody);
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