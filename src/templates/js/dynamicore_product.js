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

  $("input[id^='quantity_'][name='quantity']")
    .change(function () {
      hs.close();

      $("a[id='dynamicore_after_product_price']")
        .attr("href", href.replace(
          "numero_de_equipos=1",
          "numero_de_equipos=" + $(this).val(),
        ))
    });
}

function showProductTerms(elem) {
  return hs.htmlExpand(elem, {
    allowSizeReduction: false,
    height: 480,
    objectType: 'iframe',
    preserveContent: false,
    resize: false,
    transitions: ['fade'],
  });
}
