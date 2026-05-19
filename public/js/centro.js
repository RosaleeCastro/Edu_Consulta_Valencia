/**
 * Logica de la pagina de detalle de un centro.
 *
 * Esta pagina recibe un codigo desde centro.php y consulta el endpoint interno
 * detalle-centro.php para pintar la ficha completa.
 */

// Codigo del centro enviado por PHP desde la URL.
const codigoCentro = window.CENTRO_CODIGO || "";

// Elementos principales de la ficha.
const detalleEstado = document.getElementById("detalle-estado");
const detalleContenido = document.getElementById("detalle-contenido");
const detalleMapaEmbebido = document.getElementById("detalle-mapa-embebido");
const detalleMapaIframe = document.getElementById("detalle-mapa-iframe");

// Si no hay codigo no se puede consultar ningun centro.
if (!codigoCentro) {
  mostrarError("No se ha recibido el codigo del centro.");
} else {
  cargarDetalleCentro(codigoCentro);
}

/**
 * Consulta el endpoint interno que devuelve la ficha del centro.
 */
async function cargarDetalleCentro(codigo) {
  try {
    const respuesta = await fetch(
      `../api/detalle-centro.php?codigo=${encodeURIComponent(codigo)}`
    );
    const datos = await respuesta.json();

    if (!respuesta.ok || !datos.ok || !datos.centro) {
      throw new Error(datos.mensaje || "No se pudo cargar el detalle del centro.");
    }

    pintarDetalleCentro(datos.centro);
  } catch (error) {
    console.error(error);
    mostrarError(error.message || "No se pudo cargar el detalle del centro.");
  }
}

/**
 * Rellena todos los campos visibles de la ficha con los datos recibidos.
 */
function pintarDetalleCentro(centro) {
  document.title = `${centro.nombre} | EduConsulta Valencia`;

  // Cabecera.
  document.getElementById("detalle-nombre").textContent = centro.nombre;
  document.getElementById("detalle-subtitulo").textContent =
    `${centro.tipo} en ${centro.localidad}, ${centro.provincia}.`;

  // Datos generales.
  document.getElementById("detalle-regimen").textContent = centro.regimen;
  document.getElementById("detalle-codigo").textContent = `Codigo ${centro.codigo}`;
  document.getElementById("detalle-tipo").textContent = centro.tipo;
  document.getElementById("detalle-localidad").textContent = centro.localidad;
  document.getElementById("detalle-provincia").textContent = centro.provincia;
  document.getElementById("detalle-cp").textContent = centro.codigo_postal;
  document.getElementById("detalle-telefono").textContent = centro.telefono;
  document.getElementById("detalle-fax").textContent = centro.fax;
  document.getElementById("detalle-direccion").textContent = centro.direccion;

  // Datos institucionales.
  document.getElementById("detalle-titular").textContent = centro.titular;
  document.getElementById("detalle-cif").textContent = centro.cif;
  document.getElementById("detalle-comarca").textContent = centro.comarca;
  document.getElementById("detalle-constitucion").textContent =
    centro.fecha_constitucion;

  // Coordenadas.
  document.getElementById("detalle-latitud").textContent =
    centro.latitud || "No disponible";
  document.getElementById("detalle-longitud").textContent =
    centro.longitud || "No disponible";

  // Enlace a la ficha oficial de la Generalitat.
  const enlaceOficial = document.getElementById("detalle-ficha-oficial");
  enlaceOficial.href = centro.url && centro.url !== "#" ? centro.url : "#";
  enlaceOficial.classList.toggle("deshabilitado", !centro.url || centro.url === "#");

  // Enlace y mapa embebido de OpenStreetMap.
  const enlaceMapa = document.getElementById("detalle-mapa");
  if (centro.coordenadas_disponibles) {
    enlaceMapa.href = construirUrlMapa(centro.latitud, centro.longitud);
    enlaceMapa.classList.remove("deshabilitado");
    detalleMapaIframe.src = construirUrlMapaEmbebido(centro.latitud, centro.longitud);
    detalleMapaEmbebido.classList.remove("oculto");
  } else {
    enlaceMapa.href = "#";
    enlaceMapa.classList.add("deshabilitado");
    detalleMapaIframe.removeAttribute("src");
    detalleMapaEmbebido.classList.add("oculto");
  }

  // Se oculta el mensaje de carga y se muestra la ficha.
  detalleEstado.classList.add("oculto");
  detalleContenido.classList.remove("oculto");
}

/**
 * Muestra un error y mantiene la ficha oculta.
 */
function mostrarError(mensaje) {
  detalleEstado.textContent = mensaje;
  detalleEstado.classList.add("estado-error");
  detalleContenido.classList.add("oculto");
}

/**
 * URL normal de OpenStreetMap para abrir la ubicacion en otra pestana.
 */
function construirUrlMapa(latitud, longitud) {
  return `https://www.openstreetmap.org/?mlat=${encodeURIComponent(
    latitud
  )}&mlon=${encodeURIComponent(longitud)}#map=16/${encodeURIComponent(
    latitud
  )}/${encodeURIComponent(longitud)}`;
}

/**
 * URL embebida de OpenStreetMap para el iframe de la ficha.
 */
function construirUrlMapaEmbebido(latitud, longitud) {
  const lat = Number(latitud);
  const lon = Number(longitud);

  // Pequeno margen alrededor del centro para construir el area visible.
  const offsetLat = 0.01;
  const offsetLon = 0.015;
  const bbox = [
    lon - offsetLon,
    lat - offsetLat,
    lon + offsetLon,
    lat + offsetLat,
  ].join("%2C");

  return `https://www.openstreetmap.org/export/embed.html?bbox=${bbox}&layer=mapnik&marker=${encodeURIComponent(
    latitud
  )}%2C${encodeURIComponent(longitud)}`;
}
