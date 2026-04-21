
(function (global) {
  "use strict";

  /* estado interno */
  var _cart = [];

  var _popupData = [
    {
      cod: "MED-001",
      nom: "PARACETAMOL 500 MG x 100 TAB",
      stock: "220",
      precio: 14.0,
    },
    {
      cod: "MED-002",
      nom: "IBUPROFENO 400 MG x 50 CAP",
      stock: "8",
      precio: 22.5,
    },
    {
      cod: "MED-003",
      nom: "AMOXICILINA 500 MG x 21 CAP",
      stock: "45",
      precio: 38.0,
    },
    {
      cod: "MED-004",
      nom: "OMEPRAZOL 20 MG x 28 CAP",
      stock: "132",
      precio: 18.5,
    },
    {
      cod: "MED-005",
      nom: "LORATADINA 10 MG x 10 TAB",
      stock: "90",
      precio: 9.0,
    },
    {
      cod: "MED-006",
      nom: "METFORMINA 850 MG x 60 TAB",
      stock: "54",
      precio: 28.0,
    },
    {
      cod: "MED-007",
      nom: "AZITROMICINA 500 MG x 3 TAB",
      stock: "18",
      precio: 42.0,
    },
  ];

  var _toastIcons = {
    suc: "checkmark-circle-outline",
    dan: "alert-circle-outline",
    war: "warning-outline",
    inf: "information-circle-outline",
  };

  /* tema */
  function toggleTheme() {
    var cur = document.documentElement.getAttribute("data-theme");
    var next = cur === "light" ? "dark" : "light";
    document.documentElement.setAttribute("data-theme", next);
    localStorage.setItem("sf-theme", next);
    var ic = document.getElementById("thico");
    if (ic)
      ic.setAttribute(
        "name",
        next === "dark" ? "sunny-outline" : "moon-outline",
      );
  }

  /* sidebar */
  let sidebarOpenSubmenus = [];
  
  function toggleSidebar() {
    if (global.innerWidth <= 768) {
      var cur = document.documentElement.getAttribute("data-sidebar");
      document.documentElement.setAttribute(
        "data-sidebar",
        cur === "mobile-open" ? "expanded" : "mobile-open",
      );
      return;
    }
    var cur = document.documentElement.getAttribute("data-sidebar");
    var next = cur === "collapsed" ? "expanded" : "collapsed";
    document.documentElement.setAttribute("data-sidebar", next);
    localStorage.setItem("sf-sidebar", next);
    
    if (next === "collapsed") {
      // Guardar cuales submenus estaban abiertos antes de contraer
      sidebarOpenSubmenus = [];
      document.querySelectorAll(".ni.open").forEach(function (item) {
        // Obtener el id del submenu desde el onclick
        const onclickAttr = item.getAttribute('onclick');
        if (onclickAttr) {
          const match = onclickAttr.match(/toggleSub\('([^']+)'\s*,\s*this\)/);
          if (match && match[1]) {
            sidebarOpenSubmenus.push(match[1]);
          }
        }
      });
      
      // Cerrar todos los submenus
      document.querySelectorAll(".sub.open").forEach(function (s) {
        s.classList.remove("open");
      });
      document.querySelectorAll(".ni.open").forEach(function (i) {
        i.classList.remove("open");
      });
    } else {
      // Al expandir, restaurar los submenus que estaban abiertos
      setTimeout(() => {
        sidebarOpenSubmenus.forEach(subId => {
          const sub = document.getElementById(subId);
          if (sub) {
            sub.classList.add('open');
            // Buscar el item padre correspondiente
            const onclickCall = `toggleSub('${subId}', this)`;
            const parentItem = document.querySelector(`[onclick="${onclickCall}"]`);
            if (parentItem) {
              parentItem.classList.add('open');
            }
          }
        });
      }, 100);
    }
  }

  function closeMobile() {
    document.documentElement.setAttribute("data-sidebar", "expanded");
    localStorage.setItem("sf-sidebar", "expanded");
  }

  function toggleSub(id, item) {
    var sm = document.getElementById(id);
    if (!sm) return;
    
    // Si el sidebar esta colapsado, primero expandirlo
    if (document.documentElement.getAttribute("data-sidebar") === "collapsed") {
      document.documentElement.setAttribute("data-sidebar", "expanded");
      localStorage.setItem("sf-sidebar", "expanded");
      
      // Abrir este submenu despues de la transicion
      setTimeout(() => {
        sm.classList.add("open");
        item.classList.add("open");
      }, 100);
      return;
    }
    
    var open = sm.classList.contains("open");
    sm.classList.toggle("open", !open);
    item.classList.toggle("open", !open);
  }

  /* topbar scroll */
  function _initScroll() {
    var tb = document.getElementById("topbar");
    if (!tb) return;
    var lastY = 0;
    var THRESH = 60;
    function handler() {
      var y = global.pageYOffset || document.documentElement.scrollTop || 0;
      if (y <= 0) {
        tb.classList.remove("hidden", "visible");
      } else if (y > lastY && y > THRESH) {
        tb.classList.add("hidden");
        tb.classList.remove("visible");
      } else if (y < lastY) {
        tb.classList.remove("hidden");
        tb.classList.add("visible");
      }
      lastY = y;
    }
    global.addEventListener("scroll", handler, { passive: true });
    document.addEventListener("scroll", handler, { passive: true });
  }

  /* dropdowns */
  function _closeAllDd() {
    document.querySelectorAll(".ddm.open").forEach(function (m) {
      m.classList.remove("open");
    });
  }

  function toggleDd(e, id) {
    e.stopPropagation();
    var btn = e.currentTarget;
    var menu = document.getElementById("ddm" + id.replace("dd", ""));
    if (!menu) return;
    var wasOpen = menu.classList.contains("open");
    _closeAllDd();
    if (wasOpen) return;
    menu.style.top = "-9999px";
    menu.style.left = "-9999px";
    menu.classList.add("open");
    var M = 8;
    var vw = global.innerWidth;
    var vh = global.innerHeight;
    var bR = btn.getBoundingClientRect();
    var mW = menu.offsetWidth || 180;
    var mH = menu.offsetHeight || 160;
    var top = bR.bottom + 4;
    if (top + mH > vh - M && bR.top - mH - 4 >= M) top = bR.top - mH - 4;
    top = Math.max(M, Math.min(top, vh - mH - M));
    var left;
    if (vw - bR.left - mW - M >= 0) left = bR.left;
    else if (bR.right - mW - M >= 0) left = bR.right - mW;
    else left = bR.left + bR.width / 2 - mW / 2;
    left = Math.max(M, Math.min(left, vw - mW - M));
    menu.style.top = top + "px";
    menu.style.left = left + "px";
    menu.style.minWidth = Math.max(mW, bR.width) + "px";
  }

  /* modales */
  function showM(id) {
    var el = document.getElementById(id);
    if (el) {
      el.classList.add("open");
      document.body.style.overflow = "hidden";
    }
  }

  function closeM(id) {
    var el = document.getElementById(id);
    if (el) {
      el.classList.remove("open");
      document.body.style.overflow = "";
    }
  }

  /* toasts */
  function showToast(msg, type) {
    type = type || "inf";
    var c = document.getElementById("tctx");
    if (!c) return;
    var el = document.createElement("div");
    el.className = "toast t" + type;
    el.innerHTML =
      '<ion-icon name="' +
      (_toastIcons[type] || _toastIcons.inf) +
      '"></ion-icon>' +
      '<span class="tmsg">' +
      msg +
      "</span>" +
      '<ion-icon name="close-outline" class="tclz"></ion-icon>';
    el.querySelector(".tclz").addEventListener("click", function () {
      el.remove();
    });
    c.appendChild(el);
    setTimeout(function () {
      if (el.parentNode) el.remove();
    }, 4000);
  }

  /* detalle fila - tabla 1 */
  function showRowDetail(d) {
    function set(id, val) {
      var e = document.getElementById(id);
      if (e) e.textContent = val;
    }
    set("mrd-title", "Proveedor #" + d.id);
    set("mrd-id", "Estado: " + d.estado);
    set("mrd-name", d.name);
    set("mrd-nit", d.nit);
    set("mrd-email", d.email);
    set("mrd-tel", d.tel);
    set("mrd-dir", d.dir);
    showM("m-row-detail");
  }
  /* detalle lote - tabla 2 */
  function showLoteDetail(d) {
    function set(id, val) {
      var e = document.getElementById(id);
      if (e) e.textContent = val;
    }
    set("mld-cod", "Codigo: " + d.cod);
    set("mld-nom", d.nom);
    set("mld-lote", d.lote);
    set("mld-vence", d.vence);
    set("mld-prov", d.prov);
    set("mld-precio", d.precio);
    set("mld-stock", d.stock);
    set("mld-suc", d.suc);
    var b = document.getElementById("mld-badges");
    if (b)
      b.innerHTML =
        '<span class="badge bdef">' +
        d.cat +
        "</span> " +
        '<span class="badge bgry">' +
        d.suc +
        "</span>";
    showM("m-lote-detail");
  }

  /* carrito */
  function _renderCart() {
    var count = _cart.reduce(function (a, i) {
      return a + i.qty;
    }, 0);
    var total = _cart.reduce(function (a, i) {
      return a + i.precio * i.qty;
    }, 0);
    var badge = document.getElementById("cart-badge");
    var cc = document.getElementById("cart-count");
    var ct = document.getElementById("cart-total");
    var empty = document.getElementById("cart-empty");
    var wrap = document.getElementById("cart-table-wrap");
    var body = document.getElementById("cart-body");
    if (badge) badge.textContent = count;
    if (cc)
      cc.textContent =
        count +
        " producto" +
        (count !== 1 ? "s" : "") +
        " agregado" +
        (count !== 1 ? "s" : "");
    if (ct) ct.textContent = "Bs. " + total.toFixed(2);
    if (!_cart.length) {
      if (empty) empty.style.display = "flex";
      if (wrap) wrap.style.display = "none";
      return;
    }
    if (empty) empty.style.display = "none";
    if (wrap) wrap.style.display = "block";
    if (!body) return;
    var html = "";
    for (var k = 0; k < _cart.length; k++) {
      var item = _cart[k];
      var sub = (item.precio * item.qty).toFixed(2);
      html +=
        "<tr>" +
        "<td class='tdm'>" +
        item.cod +
        "</td>" +
        "<td class='tdp'>" +
        item.nom +
        "</td>" +
        "<td>Bs. " +
        item.precio.toFixed(2) +
        "</td>" +
        "<td><div class='flx g4 flxc'>" +
        "<button class='btn btn-gho btn-ico btn-xs' data-cod='" +
        item.cod +
        "' data-delta='-1'><ion-icon name='remove-outline'></ion-icon></button>" +
        "<span style='min-width:22px;text-align:center;font-weight:700'>" +
        item.qty +
        "</span>" +
        "<button class='btn btn-gho btn-ico btn-xs' data-cod='" +
        item.cod +
        "' data-delta='1'><ion-icon name='add-outline'></ion-icon></button>" +
        "</div></td>" +
        "<td style='font-weight:600;color:var(--btn-success)'>Bs. " +
        sub +
        "</td>" +
        "<td><button class='btn btn-war btn-ico btn-xs' data-remove='" +
        item.cod +
        "'><ion-icon name='trash-outline'></ion-icon></button></td>" +
        "</tr>";
    }
    body.innerHTML = html;
    // attach events via delegation instead of inline onclick
    body.querySelectorAll("[data-delta]").forEach(function (btn) {
      btn.addEventListener("click", function () {
        _changeQty(
          btn.getAttribute("data-cod"),
          parseInt(btn.getAttribute("data-delta")),
        );
      });
    });
    body.querySelectorAll("[data-remove]").forEach(function (btn) {
      btn.addEventListener("click", function () {
        _removeFromCart(btn.getAttribute("data-remove"));
      });
    });
  }

  function addToCart(cod, nom, precio) {
    var existing = null;
    for (var k = 0; k < _cart.length; k++) {
      if (_cart[k].cod === cod) {
        existing = _cart[k];
        break;
      }
    }
    if (existing) {
      existing.qty++;
    } else {
      _cart.push({
        cod: cod,
        nom: nom,
        precio: parseFloat(precio),
        qty: 1,
      });
    }
    _renderCart();
    closePopup();
    var inp = document.getElementById("popupSearch");
    if (inp) inp.value = "";
    showToast(nom.substring(0, 28) + "... agregado", "suc");
  }

  function _removeFromCart(cod) {
    _cart = _cart.filter(function (i) {
      return i.cod !== cod;
    });
    _renderCart();
  }

  function _changeQty(cod, delta) {
    for (var k = 0; k < _cart.length; k++) {
      if (_cart[k].cod === cod) {
        _cart[k].qty += delta;
        if (_cart[k].qty <= 0) {
          _removeFromCart(cod);
          return;
        }
        _renderCart();
        return;
      }
    }
  }

  function openCart() {
    showM("m-cart");
  }

  /* popup table */
  function openPopup() {
    var input = document.getElementById("popupSearch");
    var popup = document.getElementById("tablePopup");
    if (!input || !popup) return;
    _renderPopupRows(_popupData);
    _positionPopup(input, popup);
    popup.classList.add("open");
  }

  function closePopup() {
    var popup = document.getElementById("tablePopup");
    if (popup) popup.classList.remove("open");
  }

  function filterPopup(q) {
    var filtered = _popupData.filter(function (r) {
      return (
        r.nom.toLowerCase().indexOf(q.toLowerCase()) > -1 ||
        r.cod.toLowerCase().indexOf(q.toLowerCase()) > -1
      );
    });
    _renderPopupRows(filtered);
  }

  function _positionPopup(input, popup) {
    var rect = input.getBoundingClientRect();
    var pw = Math.min(520, global.innerWidth - 16);
    var left = rect.left;
    if (left + pw > global.innerWidth - 8) left = global.innerWidth - pw - 8;
    if (left < 8) left = 8;
    var top = rect.bottom + 4;
    if (top + 320 > global.innerHeight - 8) top = rect.top - 320 - 4;
    if (top < 8) top = 8;
    popup.style.left = left + "px";
    popup.style.top = top + "px";
    popup.style.width = pw + "px";
  }

  function _renderPopupRows(data) {
    var body = document.getElementById("popupBody");
    if (!body) return;
    if (!data.length) {
      body.innerHTML =
        '<tr><td colspan="5" class="tp-empty">Sin resultados</td></tr>';
      return;
    }
    var html = "";
    for (var k = 0; k < data.length; k++) {
      var r = data[k];
      html +=
        "<tr class='tr-cart' data-popup-cod='" +
        r.cod +
        "' data-popup-nom='" +
        r.nom +
        "' data-popup-precio='" +
        r.precio +
        "'>" +
        "<td class='tdm'>" +
        r.cod +
        "</td>" +
        "<td class='tdp'>" +
        r.nom +
        "</td>" +
        "<td>" +
        r.stock +
        " u.</td>" +
        "<td>Bs. " +
        r.precio.toFixed(2) +
        "</td>" +
        "<td><button class='btn btn-def btn-xs'>Agregar</button></td>" +
        "</tr>";
    }
    body.innerHTML = html;
    body.querySelectorAll("tr[data-popup-cod]").forEach(function (tr) {
      tr.addEventListener("click", function () {
        addToCart(
          tr.getAttribute("data-popup-cod"),
          tr.getAttribute("data-popup-nom"),
          tr.getAttribute("data-popup-precio"),
        );
      });
    });
  }

  /* tabs */
  function _initTabs() {
    document.querySelectorAll(".tabs").forEach(function (tabs) {
      tabs.querySelectorAll(".tab").forEach(function (tab) {
        tab.addEventListener("click", function () {
          tabs.querySelectorAll(".tab").forEach(function (t) {
            t.classList.remove("ac");
          });
          tab.classList.add("ac");
        });
      });
    });
    document.querySelectorAll(".tabsp").forEach(function (tabs) {
      tabs.querySelectorAll(".tabp").forEach(function (tab) {
        tab.addEventListener("click", function () {
          tabs.querySelectorAll(".tabp").forEach(function (t) {
            t.classList.remove("ac");
          });
          tab.classList.add("ac");
        });
      });
    });
  }

  /* table row click */
  function _initTableRows() {
    document.querySelectorAll("tr[data-row-json]").forEach(function (tr) {
      tr.style.cursor = "pointer";
      tr.addEventListener("click", function (e) {
        if (e.target.closest("button") || e.target.closest(".td-actions"))
          return;
        try {
          var d = JSON.parse(tr.getAttribute("data-row-json"));
          var type = tr.getAttribute("data-row-type");
          if (type === "lote") showLoteDetail(d);
          else showRowDetail(d);
        } catch (err) {
          console.warn("Row parse error:", err);
        }
      });
    });
  }

  /* alertes: cerrar */
  function _initAlerts() {
    document.querySelectorAll(".altc").forEach(function (btn) {
      btn.addEventListener("click", function () {
        var alert = btn.closest(".alert");
        if (alert) alert.remove();
      });
    });
  }

  /* init (se llama solo cuando dom listo) */
  function init() {
    // sincronizar icono de tema
    var t = localStorage.getItem("sf-theme") || "light";
    var ic = document.getElementById("thico");
    if (ic)
      ic.setAttribute("name", t === "dark" ? "sunny-outline" : "moon-outline");

    _initScroll();
    _initTabs();
    _initTableRows();
    _initAlerts();

    // cerrar dropdowns al click fuera
    document.addEventListener("click", function (e) {
      if (!e.target.closest(".dd")) _closeAllDd();
      if (!e.target.closest(".table-popup-wrap")) closePopup();
    });
    global.addEventListener("scroll", _closeAllDd, { passive: true });
  }

  /* api publica */
  global.App = {
    toggleTheme: toggleTheme,
    toggleSidebar: toggleSidebar,
    closeMobile: closeMobile,
    toggleSub: toggleSub,
    toggleDd: toggleDd,
    showM: showM,
    closeM: closeM,
    showToast: showToast,
    showRowDetail: showRowDetail,
    showLoteDetail: showLoteDetail,
    addToCart: addToCart,
    openCart: openCart,
    openPopup: openPopup,
    closePopup: closePopup,
    filterPopup: filterPopup,
    init: init,
  };

  /* make closeM global for compatibility */
  global.closeM = closeM;
})(window);

document.addEventListener("DOMContentLoaded", function () {
  App.init();
});
