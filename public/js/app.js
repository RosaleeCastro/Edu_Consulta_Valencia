/**
 * Logica principal de la pagina de busqueda.
 *
 * Este archivo conecta la interfaz HTML con la API interna en PHP.
 * Tambien crea el mapa con Leaflet y sincroniza tarjetas con marcadores.
 */

// Elementos principales del formulario y la interfaz.
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

// Catalogo provincia -> localidades cargado desde el backend.
let ubicacionesPorProvincia = {};

// Variables globales relacionadas con Leaflet.
let mapaResultados;
let capaMarcadores;

// Mapas de relacion entre codigo de centro, tarjeta HTML y marcador Leaflet.
let tarjetasPorCodigo = new Map();
let marcadoresPorCodigo = new Map();
let codigoCentroActivo = null;

// Carga inicial: ubicaciones, mapa y eventos.
cargarUbicaciones();
inicializarMapaResultados();

// Cuando cambia la provincia, se rellenan sus localidades.
selectProvincia.addEventListener("change", manejarCambioProvincia);

// Botones de vista: mixta, solo lista o solo mapa.
botonesVista.forEach((boton) => {
  boton.addEventListener("click", () => cambiarVistaResultados(boton.dataset.view));
});

/**
 * Envio del formulario de busqueda.
 *
 * Se evita la recarga de pagina y se manda un JSON al endpoint PHP interno.
 */
formularioBusqueda.addEventListener("submit", async function (evento) {
  evento.preventDefault();

  // Lectura de los filtros seleccionados por el usuario.
  const localidad = selectLocalidad.value.trim();
  const provincia = selectProvincia.value;
  const regimen = document.getElementById("regimen").value;
  const tipoCentro = document.getElementById("tipo-centro").value;

  limpiarResultados();
  mostrarMensaje("Consultando con PHP...");

  try {
    // Peticion a la API interna del proyecto.
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

    // Se lee como texto primero para poder detectar respuestas que no sean JSON.
    const textoRespuesta = await respuesta.text();
    let datos;

    try {
      datos = JSON.parse(textoRespuesta);
    } catch (errorJson) {
      console.error("Respuesta no JSON del servidor:", textoRespuesta);
      mostrarMensaje(
        `El servidor respondio con un formato no valido (HTTP ${respuesta.status}).`,
        true
      );
      return;
    }

    // El backend usa datos.ok para indicar si la operacion fue correcta.
    if (!respuesta.ok || !datos.ok) {
      mostrarMensaje(datos.mensaje || "Ha ocurrido un error.", true);
      return;
    }

    const centros = Array.isArray(datos.centros) ? datos.centros : [];
    const totalResultados =
      typeof datos.total === "number" ? datos.total : centros.length;

    // Pinta tarjetas y marcadores.
    pintarResultados(centros);
    actualizarContadorResultados(totalResultados);
  } catch (error) {
    console.error(error);
    mostrarMensaje("No se pudo conectar con el servidor PHP.", true);
  }
});

/**
 * Limpia la interfaz antes de una nueva busqueda.
 */
function limpiarResultados() {
  contenedorResultados.innerHTML = "";
  contadorResultados.textContent = "Buscando...";
  contadorMapa.textContent = "Preparando vista geografica...";
  mensajeMapa.textContent = "Actualizando centros en el mapa.";
  mensajeFiltros.hidden = true;
  mensajeFiltros.textContent = "";

  // Se reinician las relaciones entre tarjetas y marcadores.
  tarjetasPorCodigo = new Map();
  marcadoresPorCodigo = new Map();
  codigoCentroActivo = null;

  panelResultados.classList.add("oculto");
  barraVistas.classList.add("oculto");

  // Se eliminan los marcadores anteriores del mapa.
  if (capaMarcadores) {
    capaMarcadores.clearLayers();
  }
}

/**
 * Muestra un mensaje de estado general.
 */
function mostrarMensaje(texto, esError = false) {
  mensajeEstado.textContent = texto;

  if (esError) {
    mensajeEstado.classList.add("estado-error");
  } else {
    mensajeEstado.classList.remove("estado-error");
  }
}

/**
 * Renderiza las tarjetas de centros y actualiza el mapa.
 */
function pintarResultados(centros) {
  if (centros.length === 0) {
    mensajeEstado.textContent =
      "La consulta se realizo correctamente, pero no se encontraron centros con esos filtros.";
    mensajeFiltros.hidden = true;
    contadorMapa.textContent = "0 centros geolocalizados";
    mensajeMapa.textContent =
      "No hay coordenadas que mostrar para la busqueda actual.";
    centrarMapaPorDefecto();
    return;
  }

  mensajeEstado.textContent = "Resultados recibidos correctamente desde PHP.";
  mostrarResumenFiltros();
  panelResultados.classList.remove("oculto");
  barraVistas.classList.remove("oculto");
  refrescarMapaResultados();

  centros.forEach(function (centro) {
    // Cada centro se convierte en una tarjeta HTML.
    const tarjeta = document.createElement("article");
    tarjeta.classList.add("tarjeta-centro");

    const enlaceDetalle = centro.codigo
      ? `centro.php?codigo=${encodeURIComponent(centro.codigo)}`
      : "#";

    const enlaceMapa = centro.coordenadas_disponibles
      ? construirUrlMapa(centro.latitud, centro.longitud)
      : "#";

    const descripcionCodigo = centro.codigo
      ? `<p class="dato-centro"><strong>Codigo:</strong> ${escaparHtml(
          centro.codigo
        )}</p>`
      : "";

    const botonMapaClase = centro.coordenadas_disponibles
      ? "enlace-centro enlace-centro-secundario"
      : "enlace-centro enlace-centro-secundario deshabilitado";

    // Se usa escaparHtml para evitar insertar HTML peligroso desde datos externos.
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
            <p class="dato-centro"><strong>Direccion:</strong> ${escaparHtml(
              centro.direccion
            )}</p>
            <p class="dato-centro"><strong>Localidad:</strong> ${escaparHtml(
              centro.localidad
            )}</p>
            <p class="dato-centro"><strong>Provincia:</strong> ${escaparHtml(
              centro.provincia
            )}</p>
            <p class="dato-centro"><strong>Telefono:</strong> ${escaparHtml(
              centro.telefono
            )}</p>
            <p class="dato-centro"><strong>Titular:</strong> ${escaparHtml(
              centro.titular
            )}</p>
            <p class="dato-centro"><strong>Comarca:</strong> ${escaparHtml(
              centro.comarca
            )}</p>

            <div class="acciones-tarjeta">
              <a href="${enlaceDetalle}" class="enlace-centro">
                  Ver centro
              </a>
              <a href="${enlaceMapa}" class="${botonMapaClase}" target="_blank" rel="noopener noreferrer">
                  Ver mapa
              </a>
            </div>
        `;

    contenedorResultados.appendChild(tarjeta);

    // Si el centro tiene codigo, se vincula la tarjeta con su marcador.
    if (centro.codigo) {
      tarjetasPorCodigo.set(String(centro.codigo), tarjeta);
      tarjeta.addEventListener("mouseenter", () => activarMarcadorCentro(centro.codigo));
      tarjeta.addEventListener("mouseleave", () => desactivarMarcadorCentro(centro.codigo));
      tarjeta.addEventListener("focusin", () => activarMarcadorCentro(centro.codigo));
      tarjeta.addEventListener("focusout", () => desactivarMarcadorCentro(centro.codigo));
    }
  });

  actualizarMapaResultados(centros);
}

/**
 * Carga provincias y localidades desde el endpoint interno.
 */
async function cargarUbicaciones() {
  try {
    const respuesta = await fetch("../api/opciones-ubicacion.php");
    const datos = await respuesta.json();

    if (!respuesta.ok || !datos.ok || !Array.isArray(datos.ubicaciones)) {
      throw new Error(datos.mensaje || "No se pudieron cargar las ubicaciones.");
    }

    // Convierte la lista recibida en un objeto provincia -> localidades.
    ubicacionesPorProvincia = Object.fromEntries(
      datos.ubicaciones.map((item) => [item.provincia, item.localidades])
    );

    poblarSelectProvincia(datos.ubicaciones);
  } catch (error) {
    console.error(error);
    mensajeEstado.textContent =
      "No se pudieron cargar las provincias y localidades. Recarga la pagina.";
    mensajeEstado.classList.add("estado-error");
  }
}

/**
 * Rellena el select de provincias.
 */
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

/**
 * Rellena el select de localidades segun la provincia seleccionada.
 */
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

/**
 * Muestra un resumen de los filtros usados en la ultima busqueda.
 */
function mostrarResumenFiltros() {
  const partes = [
    `Provincia: ${selectProvincia.value || "Todas"}`,
    `Localidad: ${selectLocalidad.value || "Todas"}`,
  ];
  const regimen = document.getElementById("regimen").value;
  const tipoCentro = document.getElementById("tipo-centro");

  if (regimen) {
    partes.push(`Regimen: ${regimen}`);
  }

  if (tipoCentro.value) {
    partes.push(`Filtro: ${tipoCentro.options[tipoCentro.selectedIndex].text}`);
  }

  mensajeFiltros.textContent = partes.join(" | ");
  mensajeFiltros.hidden = false;
}

/**
 * Actualiza el contador textual de resultados.
 */
function actualizarContadorResultados(total) {
  if (total === 1) {
    contadorResultados.textContent = "1 resultado encontrado";
    return;
  }

  contadorResultados.textContent = `${total} resultados encontrados`;
}

/**
 * Crea el mapa Leaflet inicial.
 */
function inicializarMapaResultados() {
  mapaResultados = L.map("mapa-resultados", {
    zoomControl: true,
    scrollWheelZoom: false,
  }).setView([39.4699, -0.3763], 9);

  // OpenStreetMap aporta las teselas visuales del mapa.
  L.tileLayer("https://tile.openstreetmap.org/{z}/{x}/{y}.png", {
    maxZoom: 19,
    attribution:
      '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
  }).addTo(mapaResultados);

  // Capa donde se insertan y limpian los marcadores de cada busqueda.
  capaMarcadores = L.layerGroup().addTo(mapaResultados);
}

/**
 * Dibuja en el mapa los centros que tienen coordenadas validas.
 */
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

    // Marcador Leaflet con icono personalizado.
    const marcador = L.marker([lat, lon], {
      title: centro.nombre,
      icon: crearIconoCentro(false),
    }).addTo(capaMarcadores);

    marcador.bindPopup(crearPopupMapa(centro));

    // Al hacer clic en el marcador, se destaca su tarjeta.
    marcador.on("click", () => {
      destacarTarjetaCentro(centro.codigo);
      activarMarcadorCentro(centro.codigo);
    });
    marcador.on("popupclose", () => desactivarMarcadorCentro(centro.codigo));

    if (centro.codigo) {
      marcadoresPorCodigo.set(String(centro.codigo), marcador);
    }
  });

  contadorMapa.textContent =
    centrosGeolocalizados === 1
      ? "1 centro geolocalizado"
      : `${centrosGeolocalizados} centros geolocalizados`;

  if (centrosGeolocalizados === 0) {
    mensajeMapa.textContent =
      "Los resultados no incluyen coordenadas validas para dibujarse en el mapa.";
    centrarMapaPorDefecto();
    refrescarMapaResultados();
    return;
  }

  mensajeMapa.textContent =
    "Pulsa un marcador para ver un resumen rapido y resaltar su tarjeta.";

  // Ajusta automaticamente el encuadre para mostrar todos los marcadores.
  mapaResultados.fitBounds(bounds, {
    padding: [36, 36],
    maxZoom: 14,
  });
  refrescarMapaResultados();
}

/**
 * HTML del popup que aparece al pulsar un marcador.
 */
function crearPopupMapa(centro) {
  const enlaceDetalle = centro.codigo
    ? `centro.php?codigo=${encodeURIComponent(centro.codigo)}`
    : "#";

  return `
    <div class="popup-centro">
      <strong>${escaparHtml(centro.nombre)}</strong>
      <span>${escaparHtml(centro.tipo)}</span>
      <span>${escaparHtml(centro.localidad)} - ${escaparHtml(centro.provincia)}</span>
      <a href="${enlaceDetalle}">Ver centro</a>
    </div>
  `;
}

/**
 * Resalta la tarjeta asociada a un codigo de centro.
 */
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

/**
 * Activa visualmente un marcador y abre su popup.
 */
function activarMarcadorCentro(codigo) {
  if (!codigo) {
    return;
  }

  const codigoNormalizado = String(codigo);
  const marcador = marcadoresPorCodigo.get(codigoNormalizado);
  const tarjeta = tarjetasPorCodigo.get(codigoNormalizado);

  if (!marcador) {
    return;
  }

  if (codigoCentroActivo && codigoCentroActivo !== codigoNormalizado) {
    desactivarMarcadorCentro(codigoCentroActivo);
  }

  marcador.setIcon(crearIconoCentro(true));
  marcador.openPopup();
  codigoCentroActivo = codigoNormalizado;

  if (tarjeta) {
    tarjeta.classList.add("destacada");
  }
}

/**
 * Devuelve un marcador a su estado normal.
 */
function desactivarMarcadorCentro(codigo) {
  if (!codigo) {
    return;
  }

  const codigoNormalizado = String(codigo);
  const marcador = marcadoresPorCodigo.get(codigoNormalizado);
  const tarjeta = tarjetasPorCodigo.get(codigoNormalizado);

  if (!marcador) {
    return;
  }

  marcador.setIcon(crearIconoCentro(false));

  if (tarjeta) {
    tarjeta.classList.remove("destacada");
  }

  if (codigoCentroActivo === codigoNormalizado) {
    codigoCentroActivo = null;
  }
}

/**
 * Crea un icono HTML personalizado para Leaflet.
 */
function crearIconoCentro(activo) {
  const claseActiva = activo ? " marcador-centro-activo" : "";

  return L.divIcon({
    className: "contenedor-marcador-centro",
    html: `
      <div class="marcador-centro${claseActiva}">
        <span class="marcador-centro-pulso"></span>
        <span class="marcador-centro-nucleo"></span>
      </div>
    `,
    iconSize: activo ? [34, 34] : [22, 22],
    iconAnchor: activo ? [17, 17] : [11, 11],
    popupAnchor: [0, -16],
  });
}

/**
 * Devuelve el mapa al encuadre inicial sobre Valencia.
 */
function centrarMapaPorDefecto() {
  if (!mapaResultados) {
    return;
  }

  mapaResultados.setView([39.4699, -0.3763], 9);
  refrescarMapaResultados();
}

/**
 * Cambia la clase del panel para alternar vista mixta/lista/mapa.
 */
function cambiarVistaResultados(vista) {
  panelResultados.classList.remove("vista-mixta", "vista-lista", "vista-mapa");
  panelResultados.classList.add(`vista-${vista}`);

  botonesVista.forEach((boton) => {
    boton.classList.toggle("activo", boton.dataset.view === vista);
  });

  refrescarMapaResultados();
}

/**
 * Fuerza a Leaflet a recalcular su tamano.
 *
 * Es necesario cuando el mapa estaba oculto o cambia de vista.
 */
function refrescarMapaResultados() {
  if (!mapaResultados) {
    return;
  }

  window.requestAnimationFrame(() => {
    window.setTimeout(() => {
      mapaResultados.invalidateSize(true);
    }, 220);
  });
}

/**
 * Construye una URL externa de OpenStreetMap centrada en un centro.
 */
function construirUrlMapa(latitud, longitud) {
  return `https://www.openstreetmap.org/?mlat=${encodeURIComponent(
    latitud
  )}&mlon=${encodeURIComponent(longitud)}#map=16/${encodeURIComponent(
    latitud
  )}/${encodeURIComponent(longitud)}`;
}

/**
 * Escapa texto antes de insertarlo en innerHTML.
 */
function escaparHtml(valor) {
  return String(valor ?? "")
    .replaceAll("&", "&amp;")
    .replaceAll("<", "&lt;")
    .replaceAll(">", "&gt;")
    .replaceAll('"', "&quot;")
    .replaceAll("'", "&#39;");
}
