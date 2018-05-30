require([
    'jquery',
    'jquery/ui',
    'mage/adminhtml/events'
], function($) {
    $(function () {
        $('ul#sort-fbt').sortable({
            connectWith: "ul",
            receive: function(event, ui) {
                ui.item.attr('id', ui.item.attr('id').replace(ui.sender.data('list'), $(this).data('list')));
            },
            update: function(event, ui) {
                var sortable = [
                    $('#sort-fbt').sortable('serialize'),
                ];
                $('#fbt_general_sort_item_type').val( sortable.join('&') );
            }
        })
        .disableSelection();
        $('#row_fbt_general_sort_item_type > .value').hide();
    });
});