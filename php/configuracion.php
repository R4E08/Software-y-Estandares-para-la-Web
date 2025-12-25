<?php
/* 
   Clase Configuracion
   Gestión de la BD del Test de Usabilidad
 */
class Configuracion {

    private $servidor = "localhost";
    private $usuario  = "DBUSER2025";
    private $password = "DBPSWD2025";
    private $bd       = "UO302698_DB";
    private $conexion;

    /* 
       Constructor
     */
    public function __construct() {
        $this->conexion = new mysqli(
            $this->servidor,
            $this->usuario,
            $this->password
        );

        if ($this->conexion->connect_error) {
            die("Error de conexión");
        }
    }

    /* 
       Inicializar BD
       → DROP previo + ejecutar estructura_bd.sql
     */
    public function inicializarBD() {

        // Evita errores de BD duplicada
        $this->conexion->query("DROP DATABASE IF EXISTS " . $this->bd);

        $rutaSql = "estructura_bd.sql";

        if (!file_exists($rutaSql)) {
            return "No existe el fichero estructura_bd.sql.";
        }

        $sql = file_get_contents($rutaSql);
        if ($sql === false) {
            return "No se pudo leer el fichero SQL.";
        }

        if ($this->conexion->multi_query($sql)) {
            do {
                if ($res = $this->conexion->store_result()) {
                    $res->free();
                }
            } while ($this->conexion->more_results() && $this->conexion->next_result());

            if ($this->conexion->errno) {
                return "Error al inicializar BD: " . $this->conexion->error;
            }

            return "Base de datos inicializada correctamente.";
        }

        return "Error al ejecutar el script SQL: " . $this->conexion->error;
    }

    /* 
       Reiniciar BD
       → Vacía las tablas
     */
    public function reiniciarBD() {

        $this->conexion->select_db($this->bd);

        $tablas = [
            "Observaciones",
            "Resultados",
            "Usuarios",
            "Dispositivos"
        ];

        $this->conexion->query("SET FOREIGN_KEY_CHECKS = 0;");
        foreach ($tablas as $tabla) {
            $this->conexion->query("TRUNCATE TABLE $tabla;");
        }
        $this->conexion->query("SET FOREIGN_KEY_CHECKS = 1;");

        return "Base de datos reiniciada correctamente.";
    }

    /* 
       Eliminar BD
       → DROP DATABASE
     */
    public function eliminarBD() {

        if ($this->conexion->query("DROP DATABASE IF EXISTS " . $this->bd)) {
            return "Base de datos eliminada correctamente.";
        }

        return "Error al eliminar la base de datos.";
    }

    /* 
       Exportar BD a CSV
     */
    public function exportarCSV() {

        $this->conexion->select_db($this->bd);

        $tablas = [
            "Usuarios",
            "Resultados",
            "Observaciones"
        ];

        $archivo = "exportacion_test_" . date("Y-m-d_H-i-s") . ".csv";

        header("Content-Type: text/csv; charset=utf-8");
        header("Content-Disposition: attachment; filename=$archivo");

        $salida = fopen("php://output", "w");

        foreach ($tablas as $tabla) {

            fputcsv($salida, ["TABLA: $tabla"]);

            $resultado = $this->conexion->query("SELECT * FROM $tabla");

            if ($resultado && $resultado->num_rows > 0) {

                $columnas = [];
                while ($col = $resultado->fetch_field()) {
                    $columnas[] = $col->name;
                }
                fputcsv($salida, $columnas);

                while ($fila = $resultado->fetch_assoc()) {
                    fputcsv($salida, $fila);
                }
            }

            fputcsv($salida, []);
        }

        fclose($salida);
        exit;
    }
}

/* 
   CONTROL DE BOTONES (MISMO ARCHIVO)
 */
$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $config = new Configuracion();

    if (isset($_POST["botonInicializar"])) {
        $mensaje = $config->inicializarBD();
    }

    if (isset($_POST["botonReiniciar"])) {
        $mensaje = $config->reiniciarBD();
    }

    if (isset($_POST["botonEliminar"])) {
        $mensaje = $config->eliminarBD();
    }

    if (isset($_POST["botonExportar"])) {
        $config->exportarCSV(); // finaliza con exit
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>MotoGP – Configuración</title>
    <link rel="icon" href="multimedia/favicon48px.ico">

    <meta name="author" content="Richard Robin Roque Del Rio" />
    <meta name="description" content="Configuracion BD" />
    <meta name="keywords" content="BD, configuracion" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />


    <link rel="stylesheet" href="../estilo/estilo.css">
    <link rel="stylesheet" href="../estilo/layout.css">
</head>

<body>
<main>

<h2>Configuración</h2>

<?php if ($mensaje !== ""): ?>
<p><?= htmlspecialchars($mensaje) ?></p>
<?php endif; ?>

<form method="post">
    <input type="submit" name="botonInicializar" value="Inicializar BD">
    <input type="submit" name="botonReiniciar" value="Reiniciar BD">
    <input type="submit" name="botonEliminar" value="Eliminar BD">
    <input type="submit" name="botonExportar" value="Exportar BD">
</form>

</main>
</body>
</html>
