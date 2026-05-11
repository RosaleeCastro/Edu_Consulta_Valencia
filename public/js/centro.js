const codigoCentro = window.CENTRO_CODIGO || "";
const detalleEstado = document.getElementById("detalle-estado");
const detalleContenido = document.getElementById("detalle-contenido");
const detalleMapaEmbebido = document.getElementById("detalle-mapa-embebido");
const detalleMapaIframe = document.getElementById("detalle-mapa-iframe");

if (!codigoCentro) {
  mostrarError("No se ha recibido el código del centro.");
} else {
  cargarDetalleCentro(codigoCentro);
}

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

function pintarDetalleCentro(centro) {
  document.title = `${centro.nombre} | EduConsulta Valencia`;
  document.getElementById("detalle-nombre").textContent = centro.nombre;
  document.getElementById("detalle-subtitulo").textContent =
    `${centro.tipo} en ${centro.localidad}, ${centro.provincia}.`;
  document.getElementById("detalle-regimen").textContent = centro.regimen;
  document.getElementById("detalle-codigo").textContent = `Código ${centro.codigo}`;
  document.getElementById("detalle-tipo").textContent = centro.tipo;
  document.getElementById("detalle-localidad").textContent = centro.localidad;
  document.getElementById("detalle-provincia").textContent = centro.provincia;
  document.getElementById("detalle-cp").textContent = centro.codigo_postal;
  document.getElementById("detalle-telefono").textContent = centro.telefono;
  document.getElementById("detalle-fax").textContent = centro.fax;
  document.getElementById("detalle-direccion").textContent = centro.direccion;
  document.getElementById("detalle-titular").textContent = centro.titular;
  document.getElementById("detalle-cif").textContent = centro.cif;
  document.getElementById("detalle-comarca").textContent = centro.comarca;
  document.getElementById("detalle-constitucion").textContent =
    centro.fecha_constitucion;
  document.getElementById("detalle-latitud").textContent =
    centro.latitud || "No disponible";
  document.getElementById("detalle-longitud").textContent =
    centro.longitud || "No disponible";

  const enlaceOficial = document.getElementById("detalle-ficha-oficial");
  enlaceOficial.href = centro.url && centro.url !== "#" ? centro.url : "#";
  enlaceOficial.classList.toggle("deshabilitado", !centro.url || centro.url === "#");

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

  detalleEstado.classList.add("oculto");
  detalleContenido.classList.remove("oculto");
}

function mostrarError(mensaje) {
  detalleEstado.textContent = mensaje;
  detalleEstado.classList.add("estado-error");
  detalleContenido.classList.add("oculto");
}

function construirUrlMapa(latitud, longitud) {
  return `https://www.openstreetmap.org/?mlat=${encodeURIComponent(
    latitud
  )}&mlon=${encodeURIComponent(longitud)}#map=16/${encodeURIComponent(
    latitud
  )}/${encodeURIComponent(longitud)}`;
}

function construirUrlMapaEmbebido(latitud, longitud) {
  const lat = Number(latitud);
  const lon = Number(longitud);
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
