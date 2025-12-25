/*
  Autor: Richard Roque Del Río – UO302698
*/

class Ciudad {

    #nombre;
    #pais;
    #gentilicio;
    #poblacion;
    #coordenadas;

    constructor(nombre, pais, gentilicio) {
        this.#nombre = nombre;
        this.#pais = pais;
        this.#gentilicio = gentilicio;
        this.#poblacion = 0;
        this.#coordenadas = { latitud: 0.0, longitud: 0.0 };
    }

    /* 
       Datos básicos
     */

    rellenarDatos(poblacion, latitud, longitud) {
        this.#poblacion = poblacion;
        this.#coordenadas.latitud = latitud;
        this.#coordenadas.longitud = longitud;
    }

    obtenerNombre() { return this.#nombre; }
    obtenerPais() { return this.#pais; }

    /* 
       Construcción DOM
 */

    #asegurarSecciones() {
        const main = $("main");

        if (main.children("section").length === 0) {

            const secInfo = $("<section>");
            secInfo.append($("<h3>").text("Información de la ciudad"));
            main.append(secInfo);

            const secMeteo = $("<section>");
            secMeteo.append($("<h3>").text("Meteorología"));
            main.append(secMeteo);
        }
    }

    mostrarInformacionCiudad() {
        this.#asegurarSecciones();

        const seccion = $("main > section").eq(0);
        const article = $("<article>");

        article.append(
            $("<h4>").text(`${this.#nombre}, ${this.#pais}`)
        );

        const ul = $("<ul>");
        ul.append($("<li>").text(`Gentilicio: ${this.#gentilicio}`));
        ul.append(
            $("<li>").text(
                `Población: ${this.#poblacion.toLocaleString("es-ES")} habitantes`
            )
        );

        article.append(ul);

        article.append(
            $("<p>").text(
                `Coordenadas: (${this.#coordenadas.latitud}, ${this.#coordenadas.longitud})`
            )
        );

        seccion.append(article);
    }

    /* 
       METEOROLOGÍA CARRERA
     */

    getMeteorologiaCarrera() {
        this.#asegurarSecciones();

        const { latitud, longitud } = this.#coordenadas;
        const fecha = "2025-03-02";

        $.ajax({
            url: "https://archive-api.open-meteo.com/v1/archive",
            data: {
                latitude: latitud,
                longitude: longitud,
                start_date: fecha,
                end_date: fecha,
                hourly:
                    "temperature_2m,apparent_temperature,precipitation," +
                    "relative_humidity_2m,windspeed_10m,winddirection_10m",
                daily: "sunrise,sunset",
                timezone: "auto"
            },
            dataType: "json",
            success: datos => this.#procesarCarrera(datos, fecha),
            error: () => console.error("Error meteorología carrera")
        });
    }

    #procesarCarrera(datos, fecha) {
        const seccion = $("main > section").eq(1);
        const art = $("<article>");

        art.append(
            $("<h4>").text(`Carrera (${fecha}, 15:00 )`)
        );

        const i = 15; // 15:00

        const ul = $("<ul>");
        ul.append($("<li>").text(`Temperatura: ${datos.hourly.temperature_2m[i]} ºC`));
        ul.append($("<li>").text(`Sensación térmica: ${datos.hourly.apparent_temperature[i]} ºC`));
        ul.append($("<li>").text(`Humedad: ${datos.hourly.relative_humidity_2m[i]} %`));
        ul.append($("<li>").text(`Lluvia: ${datos.hourly.precipitation[i]} mm`));
        ul.append($("<li>").text(`Viento: ${datos.hourly.windspeed_10m[i]} km/h`));
        ul.append($("<li>").text(`Dirección viento: ${datos.hourly.winddirection_10m[i]} º`));
        ul.append($("<li>").text(`Amanecer: ${datos.daily.sunrise[0].split("T")[1]}`));
        ul.append($("<li>").text(`Atardecer: ${datos.daily.sunset[0].split("T")[1]}`));

        art.append(ul);
        seccion.append(art);
    }

    /* =====================================================
       METEOROLOGÍA ENTRENOS
    ===================================================== */

    getMeteorologiaEntrenos() {
        this.#asegurarSecciones();

        const { latitud, longitud } = this.#coordenadas;
        const fechas = ["2025-02-27", "2025-02-28", "2025-03-01"];

        $.ajax({
            url: "https://archive-api.open-meteo.com/v1/archive",
            data: {
                latitude: latitud,
                longitude: longitud,
                start_date: fechas[0],
                end_date: fechas[2],
                hourly:
                    "temperature_2m,precipitation,windspeed_10m,relative_humidity_2m",
                timezone: "auto"
            },
            dataType: "json",
            success: datos => this.#procesarEntrenos(datos, fechas),
            error: () => console.error("Error meteorología entrenos")
        });
    }

    #procesarEntrenos(datos, fechas) {
        const seccion = $("main > section").eq(1);

        const t = datos.hourly.temperature_2m;
        const p = datos.hourly.precipitation;
        const v = datos.hourly.windspeed_10m;
        const h = datos.hourly.relative_humidity_2m;

        for (let d = 0; d < 3; d++) {
            const ini = d * 24;
            const fin = ini + 24;

            const art = $("<article>");
            art.append($("<h4>").text(`Entrenos ${fechas[d]}`));

            const ul = $("<ul>");
            ul.append($("<li>").text(`Temp media: ${this.#media(t.slice(ini, fin))} ºC`));
            ul.append($("<li>").text(`Humedad media: ${this.#media(h.slice(ini, fin))} %`));
            ul.append($("<li>").text(`Lluvia media: ${this.#media(p.slice(ini, fin))} mm`));
            ul.append($("<li>").text(`Viento medio: ${this.#media(v.slice(ini, fin))} km/h`));

            art.append(ul);
            seccion.append(art);
        }
    }

    #media(arr) {
        return (arr.reduce((a, b) => a + b, 0) / arr.length).toFixed(2);
    }
}


$(document).ready(() => {

    const ciudad = new Ciudad(
        "Buriram",
        "Tailandia",
        "Buriramense"
    );

    ciudad.rellenarDatos(
        300000,
        14.9941,
        103.1039
    );

    ciudad.mostrarInformacionCiudad();
    ciudad.getMeteorologiaCarrera();
    ciudad.getMeteorologiaEntrenos();
});
