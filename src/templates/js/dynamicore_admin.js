var plugin_tabs_control_id = 'dynamicore_tabs';

function dynamicoreTabs(id) {
    $('#' + id + ' > a').click(function () {
        var tabName = '#' + id + '_' + $(this).data('name');

        $('#' + id + ' a').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');

        $('div[id^=' + id + '_]').css('display', 'none');
        $(tabName).css('display', 'block');
    });
}

function setFirstTab() {
    $('#' + plugin_tabs_control_id + ' > a:first-child').click();

    $('#dynamicore_people_form_key').focus();
}

$(document).ready(function () {
    dynamicoreTabs(plugin_tabs_control_id);

    var inputColor = [
        'primary_color',
        'secondary_color',
        'text_primary_color',
        'text_secondary_color',
    ];
    for (var i = 0; i < inputColor.length; i++) {
        $('#dynamicore_' + inputColor[i]).ColorPicker({
            onBeforeShow: function () {
                $(this).ColorPickerSetColor(this.value);
            },
            onChange: function (hsb, hex, rgb) {
                console.log(hsb, ' - ', hex, ' - ', rgb);

                $(this).val(hex);
                $(this).css('backgroundColor', '#' + hex);
            },
            onSubmit: function (hsb, hex, rgb, el) {
                $(el).val(hex);
                $(el).ColorPickerHide();
            },
        });
    }

    var $input = $('#dynamicore_allow_categories').tagify({
        whitelist: []
    })
        .on('add', function (e, tagName) {
            console.log('JQEURY EVENT: ', 'added', tagName)
        })
        .on("invalid", function (e, tagName) {
            console.log('JQEURY EVENT: ', "invalid", e, ' ', tagName);
        });

    $input.data('tagify');
});
