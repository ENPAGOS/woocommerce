hs.graphicsDir = 'http://localhost:8080/wp-content/plugins/enpagos/lib/highslide/img/';
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

function showProductTerms(elem) {

  return hs.htmlExpand(elem, {
    allowSizeReduction: false,
    height: 480,
    objectType: 'iframe',
    resize: false,
    transitions: ['fade'],
  });
}
