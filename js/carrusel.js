

/*
    Autor: Richard Roque Del Rio – UO302698
*/

class Carrusel {

    #busqueda;
    #fotos = [];
    #actual = 0;
    #maximo = 4;
    #contenedor;
    #interval = null;

    constructor(busqueda = "Chang International Circuit") {
        this.#busqueda = busqueda;



        this.#contenedor = $("<section>");

        // Insertar como PRIMERA sección del main
        $("main").prepend(this.#contenedor);

        // Obtener imágenes
        this.#getFotografias();
    }


    // Obtiene las imágenes desde el servicio público de Flickr

    #getFotografias() {
        $.ajax({
            url: "https://api.flickr.com/services/feeds/photos_public.gne",
            data: {
                tags: this.#busqueda,
                format: "json"
            },
            dataType: "jsonp",
            jsonpCallback: "jsonFlickrFeed",
            success: datos => this.#procesarJSONFotografias(datos),
            error: () => console.error("No se pudieron cargar las imágenes del carrusel.")
        });
    }


    // Procesa la respuesta de Flickr

    #procesarJSONFotografias(datos) {

        this.#fotos = datos.items
            .slice(0, this.#maximo + 1)
            .map(item => item.media.m.replace("_m.jpg", "_z.jpg"));

        if (this.#fotos.length === 0) return;

        this.#actual = 0;
        this.#mostrarFotografia();

        // Intervalo
        this.#interval = setInterval(
            () => this.#cambiarFotografia(),
            3000
        );
    }


    // Muestra la imagen actual

    #mostrarFotografia() {

        this.#contenedor.empty();

        // Título de sección 
        const h2 = $("<h2>").text("Fotos MotoGP");

        // Contenido
        const article = $("<article>");
        const h3 = $("<h3>").text(`Imágenes del circuito de ${this.#busqueda}`);
        const img = $("<img>")
            .attr("src", this.#fotos[this.#actual])
            .attr("alt", `Imagen del circuito ${this.#busqueda}`);

        article.append(h3, img);
        this.#contenedor.append(h2, article);
    }


    // Cambia la imagen cada 3 segundos

    #cambiarFotografia() {

        this.#actual = (this.#actual < this.#maximo)
            ? this.#actual + 1
            : 0;

        const img = this.#contenedor.find("article h3 + img");
        img.attr("src", this.#fotos[this.#actual]);
    }
}

// Inicialización cuando el documento está listo
$(document).ready(() => {
    new Carrusel();
});
