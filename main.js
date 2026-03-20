/* ========================= */
/* MODALES LOGIN / SIGNUP */
/* ========================= */
const API_URL = "https://TU-NGROK.ngrok-free.app";
const modalLogin = document.getElementById("modalLogin");
const modalSignup = document.getElementById("modalSignup");
const navRight = document.getElementById("navRight");
const datosBtn = document.getElementById("datosMeteoBtn");
const datosSensoresBtn = document.getElementById("datosSensoresBtn");
const datosDiv = document.getElementById("datosMeteorologicos");
const contenidoPrincipal = document.getElementById("contenido-principal"); // NUEVO: contenedor principal
const main = document.querySelector("#contenido-principal");

function actualizarNavbar() {
  const userId = localStorage.getItem("user_id");
  const nombre = localStorage.getItem("user_nombre");

  if (userId) {
    navRight.innerHTML = `
      <span>Hola, ${nombre}</span>
      <div class="menu-toggle" id="menuToggle">☰</div>
    `;
    datosSensoresBtn.style.visibility = "visible";

// 👇 AÑADIR ESTO
if (!document.getElementById("cerrarSesionMenu")) {
  const logout = document.createElement("a");
  logout.id = "cerrarSesionMenu";
  logout.textContent = "Cerrar sesión";
  document.querySelector(".nav-left").appendChild(logout);

  logout.addEventListener("click", () => {
    localStorage.removeItem("user_id");
    localStorage.removeItem("user_nombre");
    location.reload();
  });
}
  } else {
    navRight.innerHTML = `
      <a id="botonIniciarSesion">Iniciar sesión</a>
      <a id="botonRegistrarse">Registrarse</a>
    `;
    document.getElementById("botonIniciarSesion").addEventListener("click", () => modalLogin.classList.add("show"));
    document.getElementById("botonRegistrarse").addEventListener("click", () => modalSignup.classList.add("show"));
  }
}

actualizarNavbar();

const closes = document.querySelectorAll(".close");
closes.forEach((span) => {
  span.onclick = () => span.closest(".modal").classList.remove("show");
});

window.onclick = function (event) {
  if (event.target.classList.contains("modal")) {
    event.target.classList.remove("show");
  }
};

/* ========================= */
/* MOSTRAR CONTRASEÑAS */
/* ========================= */
const loginOjo = document.getElementById("loginOjo");
const loginPassword = document.getElementById("loginPassword");
loginOjo.addEventListener("mousedown", () => { loginPassword.type = "text"; loginOjo.src = "img/ojo2.png"; });
loginOjo.addEventListener("mouseup", () => { loginPassword.type = "password"; loginOjo.src = "img/ojo.png"; });
loginOjo.addEventListener("mouseout", () => { loginPassword.type = "password"; loginOjo.src = "img/ojo.png"; });

const signupOjo = document.getElementById("signupOjo");
const signupPassword = document.getElementById("signupPassword");
signupOjo.addEventListener("mousedown", () => { signupPassword.type = "text"; signupOjo.src = "img/ojo2.png"; });
signupOjo.addEventListener("mouseup", () => { signupPassword.type = "password"; signupOjo.src = "img/ojo.png"; });
signupOjo.addEventListener("mouseout", () => { signupPassword.type = "password"; signupOjo.src = "img/ojo.png"; });

const signupOjo2 = document.getElementById("signupOjo2");
const signupPasswordRepetir = document.getElementById("signupPasswordRepetir");
signupOjo2.addEventListener("mousedown", () => { signupPasswordRepetir.type = "text"; signupOjo2.src = "img/ojo2.png"; });
signupOjo2.addEventListener("mouseup", () => { signupPasswordRepetir.type = "password"; signupOjo2.src = "img/ojo.png"; });
signupOjo2.addEventListener("mouseout", () => { signupPasswordRepetir.type = "password"; signupOjo2.src = "img/ojo.png"; });

/* ========================= */
/* MOSTRAR / OCULTAR DATOS METEO */
/* ========================= */
datosBtn.addEventListener("click", () => {
  datosDiv.style.display = datosDiv.style.display === "none" ? "block" : "none";
  main.style.display = datosDiv.style.display === "block" ? "none" : "block";
});

/* ========================= */
/* CARGA ESTACIONES Y GRAFICO */
/* ========================= */
const selector = document.getElementById("selector-estacion");
const contenedor = document.getElementById("datos-meteo");
const titulo = document.getElementById("titulo-estacion");
const graficoCont = document.getElementById("grafico-contenedor");
const canvas = document.getElementById("grafico");
let grafico = null;

fetch("obtenerEstaciones.php")
  .then(res => res.json())
  .then(estaciones => {
    estaciones.forEach(e => {
      const option = document.createElement("option");
      option.value = e.ubicacion;
      option.textContent = e.nombre;
      selector.appendChild(option);
    });
    const nerja = estaciones.find(e => e.nombre.toUpperCase().includes("NERJA"));
    if (nerja) { selector.value = nerja.ubicacion; cargarDatos(); }
  }).catch(() => { contenedor.innerHTML = "Error cargando estaciones"; });

selector.addEventListener("change", cargarDatos);

function cargarDatos() {
  const estacion = selector.value;
  const nombreEstacion = selector.options[selector.selectedIndex].text;
  if (!estacion) {
    titulo.textContent = "Observación Meteorológica";
    contenedor.innerHTML = "Selecciona una estación";
    graficoCont.style.display = "none";
    if (grafico) grafico.destroy();
    return;
  }

  titulo.innerHTML = `Observación Meteorológica<div class="subtitulo">${nombreEstacion}</div>`;
  contenedor.innerHTML = "<div class='loading'>Cargando datos...</div>";
  graficoCont.style.display = "none";
  if (grafico) grafico.destroy();

  fetch("datosMeteooBD.php?estacion=" + estacion)
    .then(res => res.json())
    .then(data => {
      if (data.error) { contenedor.innerHTML = data.error; return; }
      if (data.length === 0) { contenedor.innerHTML = "No hay datos disponibles"; return; }

      const fechas = data.map(d => new Date(d.fecha_hora));
      const ultimaFecha = new Date(Math.max.apply(null, fechas));
      const fechaFormateada = ultimaFecha.toLocaleString("es-ES", { day: "2-digit", month: "2-digit", year: "numeric", hour: "2-digit", minute: "2-digit" });

      function ultimoValor(tipo) {
        const filtrado = data.filter(d => d.tipo_medida_id == tipo);
        if (filtrado.length === 0) return '';
        return parseFloat(filtrado[filtrado.length - 1].valor).toFixed(1);
      }

      const temp = ultimoValor(1);
      const hum = ultimoValor(2);
      const viento = ultimoValor(3);
      const lluvia = ultimoValor(4);

      contenedor.innerHTML = `
        <div class="datos">
          <div class="dato" id="dato-fecha">
            <img src="img/calendario.png" class="imagenes" alt="Fecha">
            <div class="dato-label">Fecha</div>
            <div class="dato-valor">${fechaFormateada}</div>
          </div>
          <div class="dato" id="dato-temperatura">
            <img src="img/alta-temperatura.png" class="imagenes" alt="Temperatura">
            <div class="dato-label">Temperatura</div>
            <div class="dato-valor">${temp} °C</div>
          </div>
          <div class="dato" id="dato-humedad">
            <img src="img/humedad.png" class="imagenes" alt="Humedad">
            <div class="dato-label">Humedad</div>
            <div class="dato-valor">${hum} %</div>
          </div>
          <div class="dato" id="dato-viento">
            <img src="img/nube.png" class="imagenes" alt="Viento">
            <div class="dato-label">Viento</div>
            <div class="dato-valor">${viento} m/s</div>
          </div>
          <div class="dato" id="dato-lluvia">
            <img src="img/clima.png" class="imagenes" alt="Lluvia">
            <div class="dato-label">Lluvia</div>
            <div class="dato-valor">${lluvia} mm</div>
          </div>
        </div>
      `;

      document.getElementById("dato-temperatura").onclick = () => mostrarGrafico(data, 1, "Temperatura °C", "red");
      document.getElementById("dato-humedad").onclick = () => mostrarGrafico(data, 2, "Humedad %", "blue");
      document.getElementById("dato-viento").onclick = () => mostrarGrafico(data, 3, "Viento m/s", "green");
      document.getElementById("dato-lluvia").onclick = () => mostrarGrafico(data, 4, "Lluvia mm", "purple");
    }).catch(() => { contenedor.innerHTML = "Error al cargar datos"; });
}

function mostrarGrafico(data, tipo, label, color) {
  graficoCont.style.display = "block";
  const dataset = data.filter(d => d.tipo_medida_id == tipo);
  const labels = dataset.map(d => d.fecha_hora.slice(11, 16));
  const valores = dataset.map(d => d.valor);
  if (grafico) grafico.destroy();

  grafico = new Chart(canvas, {
    type: "line",
    data: {
      labels,
      datasets: [{
        label,
        data: valores,
        borderColor: color,
        backgroundColor: color + "33",
        tension: 0.3,
        pointRadius: 8,     // puntos grandes visibles
        pointHoverRadius: 10,  // puntos aún más grandes al tocar
        pointHitRadius: 10   // área táctil más amplia en móvil
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false, // usa altura CSS
      interaction: {
        mode: 'nearest',        // detectar el punto más cercano
        axis: 'x',
        intersect: true         // tooltip solo al tocar el punto exacto
      },
      plugins: {
        legend: {
          display: true,
          labels: { font: { size: 18 } } // leyenda más grande
        },
        tooltip: {
          enabled: true,
          mode: 'nearest',
          intersect: true,
          bodyFont: { size: 18 },  // texto tooltip grande
          titleFont: { size: 18 },
          padding: 12
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: { font: { size: 18 } } // números eje Y más grandes
        },
        x: {
          ticks: { font: { size: 18 } } // números eje X más grandes
        }
      }
    }
  });
}

/* ========================= */
/* LOGIN */
/* ========================= */
const loginBtn = document.querySelector("#loginForm button");
const loginError = document.getElementById("loginError");

loginBtn.addEventListener("click", function () {
  const email = document.getElementById("loginEmail").value.trim();
  const password = document.getElementById("loginPassword").value.trim();
  loginError.textContent = "";

  if (!email && !password) { loginError.textContent = "Debes introducir email y contraseña"; return; }
  if (!email) { loginError.textContent = "Debes introducir tu email"; return; }
  if (!password) { loginError.textContent = "Debes introducir tu contraseña"; return; }

  fetch("login.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}`
  }).then(res => res.json())
    .then(data => {
      if (data.success) {
        localStorage.setItem("user_id", data.user_id);
        localStorage.setItem("user_nombre", data.nombre);
        loginError.classList.remove("error-msg");
        loginError.classList.add("success-msg");
        loginError.textContent = "Inicio de sesión correcto";
        setTimeout(() => location.reload(), 1000);
      } else {
        loginError.classList.remove("success-msg");
        loginError.classList.add("error-msg");
        loginError.textContent = data.mensaje;
      }
    }).catch(() => { loginError.textContent = "Error del servidor"; });
});

/* ========================= */
/* REGISTRO */
/* ========================= */
const signupBtn = document.querySelector("#signupForm button");
const signupError = document.getElementById("signupError");

signupBtn.addEventListener("click", function () {
  const nombre = document.getElementById("signupNombre").value.trim();
  const apellidos = document.getElementById("signupApellidos").value.trim();
  const email = document.getElementById("signupEmail").value.trim();
  const password = document.getElementById("signupPassword").value.trim();
  const repetir = document.getElementById("signupPasswordRepetir").value.trim();
  const telefono = document.getElementById("signupTelefono").value.trim();

  signupError.textContent = "";
  if (!nombre || !apellidos || !email || !password || !repetir) {
    signupError.textContent = "Todos los campos obligatorios deben completarse";
    return;
  }
  if (password !== repetir) {
    signupError.textContent = "Las contraseñas no coinciden";
    return;
  }
  if (password.length < 6) {
    signupError.textContent = "La contraseña debe tener al menos 6 caracteres";
    return;
  }

  fetch("registro.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `nombre=${encodeURIComponent(nombre)}&apellidos=${encodeURIComponent(apellidos)}&email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}&telefono=${encodeURIComponent(telefono)}`
  }).then(res => res.json())
    .then(data => {
      if (data.success) {
        localStorage.setItem("user_id", data.user_id);
        localStorage.setItem("user_nombre", nombre);

        signupError.classList.remove("error-msg");
        signupError.classList.add("success-msg");
        signupError.textContent = "Usuario registrado correctamente";

        setTimeout(() => {
          modalSignup.classList.remove("show");
          location.reload();
        }, 1000);

      } else {
        signupError.classList.remove("success-msg");
        signupError.classList.add("error-msg");
        signupError.textContent = data.mensaje;
      }
    }).catch(() => { signupError.textContent = "Error del servidor"; });
});

/* ========================= */
/* ACTUALIZAR UI SEGUN SESIÓN */
/* ========================= */
function actualizarUI() {
  function actualizarUI() {
    const userNombre = localStorage.getItem("user_nombre");
    const navRight = document.querySelector(".nav-right");

    if (contenidoPrincipal) contenidoPrincipal.style.display = "block";

    if (userNombre) {
      navRight.innerHTML = `
      <span>Hola, ${userNombre}</span> |
      <a href="#" id="cerrarSesion">Cerrar sesión</a>
    `;

      datosBtn.style.display = "inline-block";
      datosSensoresBtn.style.visibility = "visible"; // ✅ FIX

      document.getElementById("cerrarSesion").addEventListener("click", (e) => {
        e.preventDefault();
        localStorage.removeItem("user_id");
        localStorage.removeItem("user_nombre");
        location.reload();
      });

      datosDiv.style.display = "none";

    } else {
      navRight.innerHTML = `
      <a id="botonIniciarSesion">Iniciar sesión</a> |
      <a id="botonRegistrarse">Registrarse</a>
    `;

      datosBtn.style.display = "none";
      datosSensoresBtn.style.visibility = "hidden";
      datosDiv.style.display = "none";

      document.getElementById("botonIniciarSesion").onclick = () => modalLogin.classList.add("show");
      document.getElementById("botonRegistrarse").onclick = () => modalSignup.classList.add("show");
    }
  }
}

actualizarUI();

/* ========================== */
/* CARGAR PROVINCIAS Y ESTACIONES */
/* ========================== */
const selectorProvincia = document.getElementById("selector-provincia");
const selectorEstacion = document.getElementById("selector-estacion");

fetch("obtenerProvincias.php")
  .then(res => res.json())
  .then(provincias => {
    provincias.forEach(p => {
      const option = document.createElement("option");
      option.value = p.id;
      option.textContent = p.nombre;
      selectorProvincia.appendChild(option);
    });
  });

selectorProvincia.addEventListener("change", function () {
  const provinciaId = this.value;
  selectorEstacion.innerHTML = '<option value="">Selecciona una estación</option>';
  selectorEstacion.disabled = true;
  if (!provinciaId) return;

  fetch("obtenerEstaciones.php?provincia_id=" + provinciaId)
    .then(res => res.json())
    .then(estaciones => {
      estaciones.forEach(e => {
        const option = document.createElement("option");
        option.value = e.ubicacion;
        option.textContent = e.nombre;
        selectorEstacion.appendChild(option);
      });
      selectorEstacion.disabled = false;
    });
});

/*=======================*/
/*    CONVERTIR EN PWA   */
/*=======================*/
if ('serviceWorker' in navigator) {
  navigator.serviceWorker.register('./sw.js')
    .then(() => console.log('SW registrado'))
    .catch(err => console.error(err));
}

/* ========================= */
/* MENÚ HAMBURGUESA */
/* ========================= */

function activarMenu() {
  const menuToggle = document.getElementById("menuToggle");
  const navLeft = document.querySelector(".nav-left");

  if (menuToggle) {
    menuToggle.addEventListener("click", () => {
      navLeft.classList.toggle("active");
    });
  }
}

activarMenu();