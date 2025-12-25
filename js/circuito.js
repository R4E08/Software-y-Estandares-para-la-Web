
/*
  Autor: Richard Roque Del Río – UO302698
*/

let circuito;
let cargadorSVG;
let cargadorKML;
let mapaCircuito;

document.addEventListener("DOMContentLoaded", () => {
    circuito = new Circuito();
    cargadorSVG = new CargadorSVG();
    mapaCircuito = new MapaCircuito();
    cargadorKML = new CargadorKML(mapaCircuito);

    cargarGoogleMaps();
});

/*
    Carga dinámica de Google Maps
*/
function cargarGoogleMaps() {
    const script = document.createElement("script");
    script.src = "https://maps.googleapis.com/maps/api/js?key=XXX";
    script.async = true;
    document.head.appendChild(script);

    esperarGoogleMaps();
}

function esperarGoogleMaps() {
    if (window.google && window.google.maps) {
        mapaCircuito.inicializarMapa();
    } else {
        setTimeout(esperarGoogleMaps, 100);
    }
}

/*
    CIRCUITO
*/
class Circuito {

    constructor() {
        this.comprobarApiFile();
        this.cargarInfoCircuito();
    }

    comprobarApiFile() {
        const p = document.createElement("p");

        p.textContent = (window.File && window.FileReader && window.FileList && window.Blob)
            ? "El navegador soporta el API File de HTML5."
            : "El navegador NO soporta el API File de HTML5.";

        document.querySelector("main").appendChild(p);
    }

    cargarInfoCircuito() {
        fetch("xml/InfoCircuito.html")
            .then(r => r.text())
            .then(t => this.procesarHTML(t));
    }

    procesarHTML(texto) {
        const parser = new DOMParser();
        const doc = parser.parseFromString(texto, "text/html");

        const seccionInfo = document.querySelector("main").children[0];

        // Limpia la sección destino, dejando su h2 propio
        seccionInfo.querySelectorAll(":scope > :not(h2)").forEach(n => n.remove());

        doc.querySelectorAll(
            "h2,ul,article,figure,video"
        ).forEach(n => {
            seccionInfo.appendChild(n.cloneNode(true));
        });
    
    
    }
}

/*
    SVG
*/
class CargadorSVG {

    constructor() {
        const input = document.querySelectorAll("input[type='file']")[0];
        input.addEventListener("change", e => this.leerSVG(e));
    }

    leerSVG(e) {
        const archivo = e.target.files[0];
        if (!archivo) return;

        const lector = new FileReader();
        lector.onload = () => this.insertarSVG(lector.result);
        lector.readAsText(archivo);
    }

    insertarSVG(texto) {
        const svg = new DOMParser()
            .parseFromString(texto, "image/svg+xml")
            .documentElement;

        const seccionSVG = document.querySelector("main").children[1];
        seccionSVG.appendChild(svg);
    }
}

/*
    KML
*/
class CargadorKML {

    constructor(mapa) {
        this.mapa = mapa;

        const input = document.querySelectorAll("input[type='file']")[1];
        input.addEventListener("change", e => this.leerKML(e));
    }

    leerKML(e) {
        const archivo = e.target.files[0];
        if (!archivo) return;

        const lector = new FileReader();
        lector.onload = () => this.procesarKML(lector.result);
        lector.readAsText(archivo);
    }

    procesarKML(texto) {
        const xml = new DOMParser().parseFromString(texto, "application/xml");
        const coords = xml.getElementsByTagName("coordinates");

        let puntos = [];
        for (let c of coords) {
            puntos.push(...this.parsearCoordenadas(c.textContent));
        }

        if (puntos.length > 0) {
            this.mapa.insertarCapaKML(puntos[0], puntos);
        }
    }

    parsearCoordenadas(texto) {
        return texto.trim().split(/\s+/).map(p => {
            const [lng, lat] = p.split(",").map(Number);
            return { lat, lng };
        });
    }
}

/*
    MAPA
*/
class MapaCircuito {

    inicializarMapa() {
        const contenedor = document.querySelector("main")
            .children[2]
            .querySelector("div");

        this.mapa = new google.maps.Map(contenedor, {
            center: { lat: 0, lng: 0 },
            zoom: 3,
            mapTypeId: "terrain"
        });
    }

    insertarCapaKML(origen, tramos) {
        this.mapa.setCenter(origen);
        this.mapa.setZoom(15);

        new google.maps.Marker({
            map: this.mapa,
            position: origen
        });

        new google.maps.Polyline({
            map: this.mapa,
            path: tramos,
            strokeColor: "#FF0000",
            strokeWeight: 4
        });
    }
}
