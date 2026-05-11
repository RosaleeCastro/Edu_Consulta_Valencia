/**
 * Conexión inicial entre JavaScript y PHP.
 * El formulario enviará los filtros a nuestro endpoint interno.
 */

const formularioBusqueda = document.getElementById("formulario-busqueda");
const contenedorResultados = document.getElementById("contenedor-resultados");
const mensajeEstado = document.getElementById("mensaje-estado");
const contadorResultados = document.getElementById("contador-resultados");
const mensajeFiltros = document.getElementById("mensaje-filtros");
const selectProvincia = document.getElementById("provincia");
const selectLocalidad = document.getElementById("localidad");
const panelResultados = document.getElementById("panel-resultados");
const barraVistas = document.getElementById("barra-vistas");
const contadorMapa = document.getElementById("contador-mapa");
const mensajeMapa = document.getElementById("mensaje-mapa");
const botonesVista = document.querySelectorAll(".boton-vista");
let ubicacionesPorProvincia = {};
let mapaResultados;
let capaMarcadores;
let tarjetasPorCodigo = new Map();

cargarUbicaciones();
inicializarMapaResultados();
selectProvincia.addEventListener("change", manejarCambioProvincia);
botonesVista.forEach((boton) => {
  boton.addEventListener("click", () => cambiarVistaResultados(boton.dataset.view));
});

formularioBusqueda.addEventListener("submit", async function (evento) {
  evento.preventDefault();

  const localidad = selectLocalidad.value.trim();
  const provincia = selectProvincia.value;
  const regimen = document.getElementById("regimen").value;
  const tipoCentro = document.getElementById("tipo-centro").value;

  limpiarResultados();
  mostrarMensaje("Consultando con PHP...");

  try {
    const respuesta = await fetch("../api/buscar-centros.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        localidad,
        provincia,
        regimen,
        tipo_centro: tipoCentro,
      }),
    });

    const textoRespuesta = await respuesta.text();
    let datos;

    try {
      datos = JSON.parse(textoRespuesta);
    } catch (errorJson) {
      console.error("Respuesta no JSON del servidor:", textoRespuesta);
      mostrarMensaje(
        `El servidor respondió con un formato no válido (HTTP ${respuesta.status}).`,
        true
      );
      return;
    }

    if (!respuesta.ok || !datos.ok) {
      mostrarMensaje(datos.mensaje || "Ha ocurrido un error.", true);
      return;
    }

    const centros = Array.isArray(datos.centros) ? datos.centros : [];
    const totalResultados =
      typeof datos.total === "number" ? datos.total : centros.length;

    pintarResultados(centros);
    actualizarContadorResultados(totalResultados);
  } catch (error) {
    console.error(error);
    mostrarMensaje("No se pudo conectar con el servidor PHP.", true);
  }
});

function limpiarResultados() {
  contenedorResultados.innerHTML = "";
  contadorResultados.textContent = "Buscando...";
  contadorMapa.textContent = "Preparando vista geográfica...";
  mensajeMapa.textContent = "Actualizando centros en el mapa.";
  mensajeFiltros.hidden = true;
  mensajeFiltros.textContent = "";
  tarjetasPorCodigo = new Map();
  panelResultados.classList.add("oculto");
  barraVistas.classList.add("oculto");

  if (capaMarcadores) {
    capaMarcadores.clearLayers();
  }
}

function mostrarMensaje(texto, esError = false) {
  mensajeEstado.textContent = texto;

  if (esError) {
    mensajeEstado.classList.add("estado-error");
  } else {
    mensajeEstado.classList.remove("estado-error");
  }
}

function pintarResultados(centros) {
  if (centros.length === 0) {
    mensajeEstado.textContent =
      "La consulta se realizó correctamente, pero no se encontraron centros con esos filtros.";
    mensajeFiltros.hidden = true;
    contadorMapa.textContent = "0 centros geolocalizados";
    mensajeMapa.textContent =
      "No hay coordenadas que mostrar para la búsqueda actual.";
    centrarMapaPorDefecto();
    return;
  }

  mensajeEstado.textContent = "Resultados recibidos correctamente desde PHP.";
  mostrarResumenFiltros();
  panelResultados.classList.remove("oculto");
  barraVistas.classList.remove("oculto");

  centros.forEach(function (centro) {
    const tarjeta = document.createElement("article");
    tarjeta.classList.add("tarjeta-centro");
    const enlaceDetalle = centro.codigo
      ? `centro.php?codigo=${encodeURIComponent(centro.codigo)}`
      : "#";
    const enlaceMapa = centro.coordenadas_disponibles
      ? construirUrlMapa(centro.latitud, centro.longitud)
      : "#";
    const descripcionCodigo = centro.codigo
      ? `<p class="dato-centro"><strong>Código:</strong> ${escaparHtml(
          centro.codigo
        )}</p>`
      : "";
    const botonMapaClase = centro.coordenadas_disponibles
      ? "enlace-centro enlace-centro-secundario"
      : "enlace-centro enlace-centro-secundario deshabilitado";
    const enlaceOficial = centro.url && centro.url !== "#" ? centro.url : "#";
    const botonFichaClase =
      centro.url && centro.url !== "#"
        ? "enlace-centro"
        : "enlace-centro deshabilitado";

    tarjeta.innerHTML = `
            <h3>${escaparHtml(centro.nombre)}</h3>

            <div class="cabecera-tarjeta-centro">
              <span class="etiqueta-regimen">${escaparHtml(centro.regimen)}</span>
              <span class="etiqueta-secundaria">${escaparHtml(
                centro.codigo_postal || "Sin CP"
              )}</span>
            </div>

            <p class="dato-centro"><strong>Tipo:</strong> ${escaparHtml(
              centro.tipo
            )}</p>
            ${descripcionCodigo}
            <p class="dato-centro"><strong>Dirección:</strong> ${escaparHtml(
              centro.direccion
            )}</p>
            <p class="dato-centro"><strong>Localidad:</strong> ${escaparHtml(
              centro.localidad
            )}</p>
            <p class="dato-centro"><strong>Provincia:</strong> ${escaparHtml(
              centro.provincia
            )}</p>
            <p class="dato-centro"><strong>Teléfono:</strong> ${escaparHtml(
              centro.telefono
            )}</p>
            <p class="dato-centro"><strong>Titular:</strong> ${escaparHtml(
              centro.titular
            )}</p>
            <p class="dato-centro"><strong>Comarca:</strong> ${escaparHtml(
              centro.comarca
            )}</p>

            <div class="acciones-tarjeta">
              <a href="${enlaceDetalle}" class="enlace-centro enlace-centro-secundario">
                  Ver detalle
              </a>
              <a href="${enlaceMapa}" class="${botonMapaClase}" target="_blank" rel="noopener noreferrer">
                  Ver mapa
              </a>
              <a href="${enlaceOficial}" class="${botonFichaClase}" target="_blank" rel="noopener noreferrer">
                  Ficha oficial
              </a>
            </div>
        `;

    contenedorResultados.appendChild(tarjeta);

    if (centro.codigo) {
      tarjetasPorCodigo.set(String(centro.codigo), tarjeta);
    }
  });

  actualizarMapaResultados(centros);
}

async function cargarUbicaciones() {
  try {
    const respuesta = await fetch("../api/opciones-ubicacion.php");
    const datos = await respuesta.json();

    if (!respuesta.ok || !datos.ok || !Array.isArray(datos.ubicaciones)) {
      throw new Error(datos.mensaje || "No se pudieron cargar las ubicaciones.");
    }

    ubicacionesPorProvincia = Object.fromEntries(
      datos.ubicaciones.map((item) => [item.provincia, item.localidades])
    );

    poblarSelectProvincia(datos.ubicaciones);
  } catch (error) {
    console.error(error);
    mensajeEstado.textContent =
      "No se pudieron cargar las provincias y localidades. Recarga la página.";
    mensajeEstado.classList.add("estado-error");
  }
}

function poblarSelectProvincia(ubicaciones) {
  selectProvincia.innerHTML =
    '<option value="">Selecciona una provincia</option>';

  ubicaciones.forEach(function (item) {
    const opcion = document.createElement("option");
    opcion.value = item.provincia;
    opcion.textContent = item.provincia;
    selectProvincia.appendChild(opcion);
  });
}

function manejarCambioProvincia() {
  const provincia = selectProvincia.value;
  const localidades = ubicacionesPorProvincia[provincia] || [];

  selectLocalidad.innerHTML = "";

  if (!provincia) {
    selectLocalidad.disabled = true;
    selectLocalidad.innerHTML =
      '<option value="">Selecciona primero una provincia</option>';
    return;
  }

  selectLocalidad.disabled = false;

  const opcionInicial = document.createElement("option");
  opcionInicial.value = "";
  opcionInicial.textContent = "Selecciona una localidad";
  selectLocalidad.appendChild(opcionInicial);

  localidades.forEach(function (localidad) {
    const opcion = document.createElement("option");
    opcion.value = localidad;
    opcion.textContent = localidad;
    selectLocalidad.appendChild(opcion);
  });
}

function mostrarResumenFiltros() {
  const partes = [
    `Provincia: ${selectProvincia.value || "Todas"}`,
    `Localidad: ${selectLocalidad.value || "Todas"}`,
  ];
  const regimen = document.getElementById("regimen").value;
  const tipoCentro = document.getElementById("tipo-centro");

  if (regimen) {
    partes.push(`Régimen: ${regimen}`);
  }

  if (tipoCentro.value) {
    partes.push(`Filtro: ${tipoCentro.options[tipoCentro.selectedIndex].text}`);
  }

  mensajeFiltros.textContent = partes.join(" | ");
  mensajeFiltros.hidden = false;
}

function actualizarContadorResultados(total) {
  if (total === 1) {
    contadorResultados.textContent = "1 resultado encontrado";
    return;
  }

  contadorResultados.textContent = `${total} resultados encontrados`;
}

function inicializarMapaResultados() {
  mapaResultados = L.map("mapa-resultados", {
    zoomControl: true,
    scrollWheelZoom: false,
  }).setView([39.4699, -0.3763], 9);

  L.tileLayer("https://tile.openstreetmap.org/{z}/{x}/{y}.png", {
    maxZoom: 19,
    attribution:
      '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
  }).addTo(mapaResultados);

  capaMarcadores = L.layerGroup().addTo(mapaResultados);
}

function actualizarMapaResultados(centros) {
  if (!mapaResultados || !capaMarcadores) {
    return;
  }

  capaMarcadores.clearLayers();

  const bounds = [];
  let centrosGeolocalizados = 0;

  centros.forEach((centro) => {
    if (!centro.coordenadas_disponibles) {
      return;
    }

    const lat = Number(centro.latitud);
    const lon = Number(centro.longitud);

    if (Number.isNaN(lat) || Number.isNaN(lon)) {
      return;
    }

    centrosGeolocalizados += 1;
    bounds.push([lat, lon]);

    const marcador = L.marker([lat, lon], {
      title: centro.nombre,
    }).addTo(capaMarcadores);

    marcador.bindPopup(crearPopupMapa(centro));
    marcador.on("click", () => destacarTarjetaCentro(centro.codigo));
  });

  contadorMapa.textContent =
    centrosGeolocalizados === 1
      ? "1 centro geolocalizado"
      : `${centrosGeolocalizados} centros geolocalizados`;

  if (centrosGeolocalizados === 0) {
    mensajeMapa.textContent =
      "Los resultados no incluyen coordenadas válidas para dibujarse en el mapa.";
    centrarMapaPorDefecto();
    return;
  }

  mensajeMapa.textContent =
    "Pulsa un marcador para ver un resumen rápido y resaltar su tarjeta.";
  mapaResultados.fitBounds(bounds, {
    padding: [36, 36],
    maxZoom: 14,
  });
}

function crearPopupMapa(centro) {
  const enlaceDetalle = centro.codigo
    ? `centro.php?codigo=${encodeURIComponent(centro.codigo)}`
    : "#";

  return `
    <div class="popup-centro">
      <strong>${escaparHtml(centro.nombre)}</strong>
      <span>${escaparHtml(centro.tipo)}</span>
      <span>${escaparHtml(centro.localidad)} · ${escaparHtml(centro.provincia)}</span>
      <a href="${enlaceDetalle}">Ver detalle</a>
    </div>
  `;
}

function destacarTarjetaCentro(codigo) {
  if (!codigo) {
    return;
  }

  document.querySelectorAll(".tarjeta-centro.destacada").forEach((tarjeta) => {
    tarjeta.classList.remove("destacada");
  });

  const tarjeta = tarjetasPorCodigo.get(String(codigo));

  if (!tarjeta) {
    return;
  }

  tarjeta.classList.add("destacada");
  tarjeta.scrollIntoView({
    behavior: "smooth",
    block: "center",
  });
}

function centrarMapaPorDefecto() {
  if (!mapaResultados) {
    return;
  }

  mapaResultados.setView([39.4699, -0.3763], 9);
}

function cambiarVistaResultados(vista) {
  panelResultados.classList.remove("vista-mixta", "vista-lista", "vista-mapa");
  panelResultados.classList.add(`vista-${vista}`);

  botonesVista.forEach((boton) => {
    boton.classList.toggle("activo", boton.dataset.view === vista);
  });

  if (mapaResultados) {
    window.setTimeout(() => mapaResultados.invalidateSize(), 180);
  }
}

function construirUrlMapa(latitud, longitud) {
  return `https://www.openstreetmap.org/?mlat=${encodeURIComponent(
    latitud
  )}&mlon=${encodeURIComponent(longitud)}#map=16/${encodeURIComponent(
    latitud
  )}/${encodeURIComponent(longitud)}`;
}

function escaparHtml(valor) {
  return String(valor ?? "")
    .replaceAll("&", "&amp;")
    .replaceAll("<", "&lt;")
    .replaceAll(">", "&gt;")
    .replaceAll('"', "&quot;")
    .replaceAll("'", "&#39;");
}
