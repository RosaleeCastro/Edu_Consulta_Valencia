/**
 * Archivo principal de JavaScript.
 *
 * En este paso solo dejamos preparada la conexión con el formulario.
 * En el siguiente paso añadiremos la petición fetch() real hacia PHP.
 */

// Captura de elementos del DOM
const formularioBusqueda = document.getElementById("formulario-busqueda");
const contenedorResultado = document.getElementById("contenedor-resultados");
const mensajeEstado = document.getElementById("mensaje-estado");
const contadorResultados = document.getElementById("contador-resultados");

formularioBusqueda.addEventListener("submit", function (e) {
  e.preventDefault;

  //capturamos los valores que sube el formularios
  const localidad = document.getElementById("localidad").value.trim();
  const provincia = document.getElementById("provincia").value.trim();
  const regimen = document.getElementById("regimen").value;

  mensajeEstado.textContent = `Búsqueda preparada para: ${localidad}`;
  contadorResultados.textContent = `Preparando consulta...`;

  console.log({
    localidad,
    provincia,
    regimen,
  });
});
