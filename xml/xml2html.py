# -*- coding: utf-8 -*-
"""
Autor: Richard Roque
"""

import xml.etree.ElementTree as ET

NS = {"ns": "http://www.uniovi.es"}


def formatear_tiempo_iso8601(tiempo):
    """
    Convierte un tiempo ISO-8601 a formato mm:ss.mmm
    """
    if not tiempo or not tiempo.startswith("PT"):
        return tiempo

    t = tiempo[2:]  # quitar "PT"
    minutos = 0
    segundos = 0.0

    if "M" in t:
        partes = t.split("M")
        minutos = int(partes[0])
        t = partes[1]

    if "S" in t:
        segundos = float(t.replace("S", ""))

    total = minutos * 60 + segundos
    mm = int(total // 60)
    ss = total % 60

    return f"{mm:02d}:{ss:06.3f}"


class Html:
    def __init__(self):
        self.html = ET.Element("html", lang="es")

        head = ET.SubElement(self.html, "head")
        ET.SubElement(head, "meta", charset="UTF-8")
        ET.SubElement(head, "meta", name="author",
                      content="Richard Robin Roque Del Rio")
        ET.SubElement(head, "meta", name="description",
                      content="Información del circuito generada desde XML")
        ET.SubElement(head, "meta", name="viewport",
                      content="width=device-width, initial-scale=1.0")
        ET.SubElement(head, "title").text = "InfoCircuito"
        ET.SubElement(head, "link",
                      rel="stylesheet",
                      href="../estilo/estilo.css",
                      type="text/css")

        body = ET.SubElement(self.html, "body")
        self.main = ET.SubElement(body, "main")

    def section(self, titulo):
        sec = ET.SubElement(self.main, "section")
        ET.SubElement(sec, "h2").text = titulo
        return sec

    def write(self, filename):
        ET.indent(self.html, space="  ")
        with open(filename, "w", encoding="utf-8") as f:
            f.write("<!DOCTYPE HTML>\n")
            f.write(ET.tostring(self.html, encoding="unicode"))


def main():
    xml_file = input("Introduzca el archivo XML: ").strip()
    tree = ET.parse(xml_file)
    root = tree.getroot()

    html = Html()

    # ==================================================
    # DATOS DEL CIRCUITO
    # ==================================================
    sec = html.section("Datos del circuito")
    art = ET.SubElement(sec, "article")

    ET.SubElement(art, "h3").text = root.findtext(
        "ns:nombreCircuito", namespaces=NS)

    ul = ET.SubElement(art, "ul")

    def li(texto):
        ET.SubElement(ul, "li").text = texto

    li(f"Longitud: {root.findtext('ns:longitudCircuito', namespaces=NS)} metro")
    li(f"Anchura media: {root.findtext('ns:anchuraMedia', namespaces=NS)} metro")
    li(f"Fecha: {root.findtext('ns:fechaCarrera', namespaces=NS)}")
    li(f"Hora de inicio: {root.findtext('ns:horaEsp', namespaces=NS)}")
    li(f"Vueltas: {root.findtext('ns:numeroVueltas', namespaces=NS)}")
    li(f"Localidad: {root.findtext('ns:localidadProxima', namespaces=NS)}")
    li(f"País: {root.findtext('ns:paisDelCircuito', namespaces=NS)}")
    li(f"Patrocinador principal: {root.findtext('ns:nombrePatrocinadorP', namespaces=NS)}")

    # ==================================================
    # REFERENCIAS
    # ==================================================
    sec = html.section("Referencias")
    ul = ET.SubElement(sec, "ul")

    for ref in root.findall("ns:referencias/ns:referencia", NS):
        li_ref = ET.SubElement(ul, "li")
        a = ET.SubElement(
            li_ref, "a",
            href=ref.attrib.get("url"),
            target="_blank",
            rel="noopener"
        )
        texto = ref.text.strip() if ref.text else ref.attrib.get("url")
        a.text = texto

    # ==================================================
    # GALERÍA DE FOTOS
    # ==================================================
    sec = html.section("Galería de fotos")

    for foto in root.findall("ns:galeriaFotos/ns:fotos", NS):
        fig = ET.SubElement(sec, "figure")
        ET.SubElement(
            fig, "img",
            src=foto.attrib.get("nombre"),
            alt="Foto del circuito"
        )

    # ==================================================
    # VÍDEOS
    # ==================================================
    sec = html.section("Vídeos")

    for vid in root.findall("ns:galeriaVideos/ns:videos", NS):
        video = ET.SubElement(
            sec, "video",
            controls="controls",
            preload="auto"
        )
        ET.SubElement(
            video, "source",
            src= vid.attrib.get("nombre"),
            type="video/mp4"
        )

    # ==================================================
    # RESULTADO
    # ==================================================
    sec = html.section("Resultado")

    # Ganador
    art = ET.SubElement(sec, "article")
    ET.SubElement(art, "h3").text = "Ganador"

    ganador = root.findtext(
        "ns:vencedorCarrera/ns:nombreVencedor", namespaces=NS)

    tiempo_iso = root.findtext(
        "ns:vencedorCarrera/ns:tiempoVencedor", namespaces=NS)

    tiempo_legible = formatear_tiempo_iso8601(tiempo_iso)

    ET.SubElement(art, "p").text = f"{ganador} — Tiempo: {tiempo_legible}"

    # Clasificación
    art = ET.SubElement(sec, "article")
    ET.SubElement(art, "h3").text = "Clasificación mundial"
    ol = ET.SubElement(art, "ol")

    for c in root.findall("ns:clasificados/ns:clasificado", NS):
        ET.SubElement(
            ol, "li"
        ).text = f"Puesto {c.attrib.get('posicion')}: {c.text.strip()}"

    # ==================================================
    # ESCRITURA FINAL
    # ==================================================
    html.write("InfoCircuito.html")
    print("InfoCircuito.html generado correctamente.")


if __name__ == "__main__":
    main()
