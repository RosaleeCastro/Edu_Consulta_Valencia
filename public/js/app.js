/**
 * Conexión inicial entre JavaScript y PHP.
 * El formulario enviará los filtros a nuestro endpoint interno.
 */

const formularioBusqueda = document.getElementById("formulario-busqueda");
const contenedorResultados = document.getElementById("contenedor-resultados");
const mensajeEstado = document.getElementById("mensaje-estado");
const contadorResultados = document.getElementById("contador-resultados");

formularioBusqueda.addEventListener("submit", async function (evento) {
  evento.preventDefault();

  const localidad = document.getElementById("localidad").value.trim();
  const provincia = document.getElementById("provincia").value;
  const regimen = document.getElementById("regimen").value;

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
      }),
    });

    const datos = await respuesta.json();

    if (!respuesta.ok || !datos.ok) {
      mostrarMensaje(datos.mensaje || "Ha ocurrido un error.", true);
      return;
    }

    pintarResultados(datos.centros);
    contadorResultados.textContent = `${datos.centros.length} resultado encontrado`;
  } catch (error) {
    console.error(error);
    mostrarMensaje("No se pudo conectar con el servidor PHP.", true);
  }
});

function limpiarResultados() {
  contenedorResultados.innerHTML = "";
  contadorResultados.textContent = "Buscando...";
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
  mensajeEstado.textContent = "Resultados recibidos correctamente desde PHP.";

  centros.forEach(function (centro) {
    const tarjeta = document.createElement("article");
    tarjeta.classList.add("tarjeta-centro");

    tarjeta.innerHTML = `
            <h3>${centro.nombre}</h3>

            <span class="etiqueta-regimen">${centro.regimen}</span>

            <p><strong>Tipo:</strong> ${centro.tipo}</p>
            <p><strong>Dirección:</strong> ${centro.direccion}</p>
            <p><strong>Localidad:</strong> ${centro.localidad}</p>
            <p><strong>Provincia:</strong> ${centro.provincia}</p>
            <p><strong>Teléfono:</strong> ${centro.telefono}</p>

            <a href="${centro.url}" class="enlace-centro">
                Ver ficha oficial
            </a>
        `;

    contenedorResultados.appendChild(tarjeta);
  });
}
