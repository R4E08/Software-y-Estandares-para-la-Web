
/*
  Autor: Richard Roque Del Río – UO302698
*/

class Noticias {

    #busqueda;
    #url;
    #apiKey;
    #contenedor;

    constructor(busqueda = "MotoGP") {
        this.#busqueda = busqueda;
        this.#url = "https://api.thenewsapi.com/v1/news/all";
        this.#apiKey = "opy8AZtmWDVR0ETYugUSaQZUVVPfzeoLIwiO8Zy9";

        // ==================================================
        // CREACIÓN DINÁMICA DE LA SECCIÓN (OBLIGATORIO)

        this.#contenedor = $("<section>");
        this.#contenedor.append($("<h2>").text("Noticias MotoGP"));

        $("main").append(this.#contenedor);

        this.#buscar();
    }


    // Llamada al servicio web

    #buscar() {
        const urlCompleta =
            `${this.#url}?api_token=${this.#apiKey}&search=${this.#busqueda}&language=es`;

        fetch(urlCompleta)
            .then(response => {
                if (!response.ok) {
                    throw new Error("Error en la llamada a TheNewsApi");
                }
                return response.json();
            })
            .then(json => this.#procesarInformacion(json))
            .catch(error => console.error("Error en fetch:", error));
    }


    // Procesamiento del JSON

    #procesarInformacion(json) {

        if (!json || !json.data) return;

        json.data.forEach(item => {

            const article = $("<article>");

            article.append(
                $("<h3>").text(item.title)
            );

            if (item.description) {
                article.append(
                    $("<p>").text(item.description)
                );
            }

            article.append(
                $("<small>").text("Fuente: " + item.source)
            );

            article.append(
                $("<a>")
                    .attr("href", item.url)
                    .attr("target", "_blank")
                    .text("Leer más")
            );

            this.#contenedor.append(article);
        });
    }
}

// Inicialización automática
$(document).ready(() => {
    new Noticias();
});
