<?php
require_once __DIR__ . '/cronometroClase.php';
session_start();

/* CONFIGURACIÓN BD */
$conexion = new mysqli("localhost", "DBUSER2025", "DBPSWD2025", "UO302698_DB");
if ($conexion->connect_error) {
    die("Error de conexión");
}
$conexion->set_charset("utf8mb4");

/* CRONÓMETRO */
if (
    isset($_SESSION["cronometro_usabilidad"]) &&
    $_SESSION["cronometro_usabilidad"] instanceof Cronometro
) {
    $cronometro = $_SESSION["cronometro_usabilidad"];
} else {
    $cronometro = new Cronometro();
    $_SESSION["cronometro_usabilidad"] = $cronometro;
}

/* VARIABLES */
$accion = $_POST["accion"] ?? "";
$mensaje = "";
$mostrarObservador = false;

/* INICIAR PRUEBA */
if ($accion === "iniciar") {

    $_SESSION["datos_usuario"] = [
        "profesion" => $_POST["profesion"] ?? "",
        "edad" => $_POST["edad"] ?? "",
        "genero" => $_POST["genero"] ?? "",
        "pericia" => $_POST["pericia"] ?? "",
        "dispositivo" => $_POST["dispositivo"] ?? ""
    ];

    $cronometro = new Cronometro();
    $cronometro->reiniciar();
    $cronometro->arrancar();

    $_SESSION["cronometro_usabilidad"] = $cronometro;
    $_SESSION["prueba_iniciada"] = true;
}

/* FINALIZAR PRUEBA */
if ($accion === "terminar") {

    if (!isset($_SESSION["prueba_iniciada"])) {
        $mensaje = "Primero hay que iniciar la prueba.";
    } else {

        for ($i = 1; $i <= 10; $i++) {
            if (trim($_POST["p$i"] ?? "") === "") {
                $mensaje = "Todas las preguntas deben responderse.";
                break;
            }
        }

        if ($mensaje === "") {

            $conexion->begin_transaction();

            try {
                $cronometro->parar();
                $tiempo = $cronometro->getTiempo();
                unset($_SESSION["prueba_iniciada"]);

                $du = $_SESSION["datos_usuario"];

                /* USUARIO */
                $stmt = $conexion->prepare(
                    "INSERT INTO Usuarios (profesion, edad, genero, pericia_informatica)
                     VALUES (?, ?, ?, ?)"
                );
                $stmt->bind_param("sisi", $du["profesion"], $du["edad"], $du["genero"], $du["pericia"]);
                $stmt->execute();
                $idUsuario = $stmt->insert_id;
                $stmt->close();

                /* DISPOSITIVO */
                $stmt = $conexion->prepare(
                    "SELECT id_dispositivo FROM Dispositivos WHERE nombre = ?"
                );
                $stmt->bind_param("s", $du["dispositivo"]);
                $stmt->execute();
                $stmt->bind_result($idDisp);

                if (!$stmt->fetch()) {
                    $stmt->close();
                    $stmt = $conexion->prepare(
                        "INSERT INTO Dispositivos (nombre) VALUES (?)"
                    );
                    $stmt->bind_param("s", $du["dispositivo"]);
                    $stmt->execute();
                    $idDisp = $stmt->insert_id;
                }
                $stmt->close();

                /* RESULTADO */
                $stmt = $conexion->prepare(
                    "INSERT INTO Resultados
                    (id_usuario, id_dispositivo, tiempo_segundos, completado,
                     comentarios, propuestas_mejora, valoracion)
                    VALUES (?, ?, ?, 1, ?, ?, ?)"
                );
                $stmt->bind_param(
                    "iiissi",
                    $idUsuario,
                    $idDisp,
                    $tiempo,
                    $_POST["comentariosUsuario"],
                    $_POST["mejorasUsuario"],
                    $_POST["valoracion"]
                );
                $stmt->execute();
                $idResultado = $stmt->insert_id;
                $stmt->close();

                /* RESPUESTAS */
                $stmtResp = $conexion->prepare(
                    "INSERT INTO Respuestas (id_resultado, num_pregunta, respuesta)
                     VALUES (?, ?, ?)"
                );
                for ($i = 1; $i <= 10; $i++) {
                    $stmtResp->bind_param("iis", $idResultado, $i, $_POST["p$i"]);
                    $stmtResp->execute();
                }
                $stmtResp->close();

                $conexion->commit();
                $_SESSION["ultimo_usuario"] = $idUsuario;
                $mostrarObservador = true;
                $mensaje = "Prueba guardada correctamente.";

            } catch (Throwable $e) {
                $conexion->rollback();
                $mensaje = "Error al guardar la prueba.";
            }
        }
    }
}

/* OBSERVADOR */
if ($accion === "guardar_observador") {

    $idUsuario = $_SESSION["ultimo_usuario"] ?? null;
    $comentario = trim($_POST["comentarioObservador"] ?? "");

    if ($idUsuario && $comentario !== "") {
        $stmt = $conexion->prepare(
            "INSERT INTO Observaciones (id_usuario, comentario)
             VALUES (?, ?)"
        );
        $stmt->bind_param("is", $idUsuario, $comentario);
        $stmt->execute();
        $stmt->close();
    }

    session_unset();
    session_regenerate_id(true);
    $mensaje = "Prueba finalizada. Gracias.";
}

$pruebaIniciada = isset($_SESSION["prueba_iniciada"]);
$disabled = $pruebaIniciada ? "" : "disabled";
$du = $_SESSION["datos_usuario"] ?? [];

$preguntas = [
    "¿Cuál es el nombre del piloto?",
    "¿Qué fecha de nacimiento aparece para el piloto?",
    "¿Cuál es el lugar de nacimiento del piloto?",
    "¿Qué equipo se indica para el piloto?",
    "¿Cuál es el nombre del circuito?",
    "¿En qué país se encuentra el circuito?",
    "¿Qué longitud tiene el circuito?",
    "¿Qué temperatura se muestra el día de la carrera?",
    "¿Cuál es el número de habitantes?",
    "¿Cuál es la primera noticia?"
];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Test de Usabilidad</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
    <main>

        <?php if ($mensaje): ?>
            <p role="alert"><?= htmlspecialchars($mensaje) ?></p>
        <?php endif; ?>

        <?php if ($mostrarObservador): ?>

            <form method="post">
                <section>
                    <h2>Observador</h2>
                    <label for="obs">Comentario del observador</label><br>
                    <textarea id="obs" name="comentarioObservador" required></textarea><br>
                    <button type="submit" name="accion" value="guardar_observador">Guardar</button>
                </section>
            </form>

        <?php else: ?>

            <form method="post" autocomplete="off">

                <section>
                    <h2>Datos del usuario</h2>

                    <label for="profesion">Profesión</label><br>
                    <input id="profesion" name="profesion" type="text" required
                        value="<?= htmlspecialchars($du["profesion"] ?? "") ?>"><br><br>

                    <label for="edad">Edad</label><br>
                    <input id="edad" name="edad" type="number" min="10" max="120" required
                        value="<?= htmlspecialchars($du["edad"] ?? "") ?>"><br><br>

                    <fieldset>
                        <legend>Género</legend>
                        <label><input type="radio" name="genero" value="Masculino" <?= ($du["genero"] ?? "") === "Masculino" ? "checked" : "" ?> required> Masculino</label><br>
                        <label><input type="radio" name="genero" value="Femenino" <?= ($du["genero"] ?? "") === "Femenino" ? "checked" : "" ?>> Femenino</label><br>
                        <label><input type="radio" name="genero" value="Otro" <?= ($du["genero"] ?? "") === "Otro" ? "checked" : "" ?>> Otro</label>
                    </fieldset><br>

                    <label for="pericia">Pericia informática (0–10)</label><br>
                    <input id="pericia" name="pericia" type="number" min="0" max="10" required
                        value="<?= htmlspecialchars($du["pericia"] ?? "") ?>"><br><br>

                    <fieldset>
                        <legend>Dispositivo</legend>
                        <label><input type="radio" name="dispositivo" value="Ordenador" <?= ($du["dispositivo"] ?? "") === "Ordenador" ? "checked" : "" ?> required> Ordenador</label><br>
                        <label><input type="radio" name="dispositivo" value="Tableta" <?= ($du["dispositivo"] ?? "") === "Tableta" ? "checked" : "" ?>> Tableta</label><br>
                        <label><input type="radio" name="dispositivo" value="Telefono" <?= ($du["dispositivo"] ?? "") === "Telefono" ? "checked" : "" ?>> Teléfono</label>
                    </fieldset><br>

                    <button type="submit" name="accion" value="iniciar">Iniciar prueba</button>
                </section>

                <section>
                    <h2>Cuestionario</h2>

                    <?php foreach ($preguntas as $i => $texto): ?>
                        <label for="p<?= $i + 1 ?>"><?= ($i + 1) ?>. <?= htmlspecialchars($texto) ?></label><br>
                        <input id="p<?= $i + 1 ?>" name="p<?= $i + 1 ?>" type="text" <?= $disabled ?> required><br><br>
                    <?php endforeach; ?>

                    <label for="comentarios">Comentarios</label><br>
                    <textarea id="comentarios" name="comentariosUsuario" <?= $disabled ?>></textarea><br><br>

                    <label for="mejoras">Propuestas de mejora</label><br>
                    <textarea id="mejoras" name="mejorasUsuario" <?= $disabled ?>></textarea><br><br>

                    <label for="valoracion">Valoración (0–10)</label><br>
                    <input id="valoracion" name="valoracion" type="number" min="0" max="10" <?= $disabled ?>><br><br>

                    <button type="submit" name="accion" value="terminar" <?= $disabled ?>>Finalizar prueba</button>
                </section>

            </form>

        <?php endif; ?>

    </main>
</body>

</html>