var HREF = '';
var QUANTITY_ELEM;

function hsInit(storeUrl = window.location.origin, href) {
  hs.graphicsDir = storeUrl + '/wp-content/plugins/enpagos/lib/highslide/img/';
  hs.wrapperClassName = 'draggable-header';
  hs.lang = {
    closeText: 'Cerrar',
    closeTitle: 'Cerrar (esc)',
    loadingText: 'Cargando',
    resizeTitle: 'Tamaño',
  }
  hs.showCredits = false;
  hs.marginBottom = 0;
  hs.marginLeft = 0;
  hs.marginRight = 0;
  hs.marginTop = 0;

  HREF = href;
  QUANTITY_ELEM = document.querySelector("input[id^='quantity_'][name='quantity']");

  QUANTITY_ELEM.addEventListener('change', function () {
    hs.close();
  });
}

function showProductTerms(elem) {
  return hs.htmlExpand(elem, {
    allowSizeReduction: false,
    height: 480,
    objectType: 'iframe',
    preserveContent: false,
    resize: false,
    src: HREF.replace(
      "numero_de_equipos=1",
      "numero_de_equipos=" + QUANTITY_ELEM.value,
    ),
    transitions: ['fade'],
  });
}
