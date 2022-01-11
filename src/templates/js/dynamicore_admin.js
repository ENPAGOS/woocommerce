var plugin_tabs_control_id = 'dynamicore_tabs';

function dynamicoreTabs(id) {
    $('#' + id + ' > a').click(function() {
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

$(document).ready(function() {
    dynamicoreTabs(plugin_tabs_control_id);
});
