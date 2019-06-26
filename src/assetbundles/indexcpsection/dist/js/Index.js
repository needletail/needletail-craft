/**
 * Needletail plugin for Craft CMS
 *
 * Index Field JS
 *
 * @author    Needletail
 * @copyright Copyright (c) 2019 Needletail
 * @link      https://needletail.io
 * @package   Needletail
 * @since     1.0.0
 */

$(document).on('click', 'input[data-redirect]', function (e) {
    var _form = $(this).parents('form');
    _form.find('input[name="redirect"]').val($(this).data('redirect'));
    _form.submit();
});


// Toggle various field when changing element type
$(document).on('change', '#elementType', function() {
    $('.element-select').hide();

    var value = $(this).val().replace(/\\/g, '-');
    $('.element-select[data-type="' + value + '"]').show();
});

$('#elementType').trigger('change');

// Toggle the Entry Type field when changing the section select
$(document).on('change', '.element-parent-group select', function() {
    var sections = $(this).parents('.element-sub-group').data('items');
    var entryType = 'item_' + $(this).val();
    var entryTypes = sections[entryType];

    var currentValue = $('.element-child-group select').val();

    var newOptions = '<option value="">' + Craft.t('needletail', 'None') + '</option>';
    $.each(entryTypes, function(index, value) {
        if (index) {
            newOptions += '<option value="' + index + '">' + value + '</option>';
        }
    });

    $('.element-child-group select').html(newOptions);

    // Select the first non-empty, or pre-selected
    if (currentValue) {
        $('.element-child-group select').val(currentValue);
    } else {
        $($('.element-child-group select').children()[1]).attr('selected', true);
    }
});

$('.element-parent-group select').trigger('change');


// Show initially hidden element sub-fields. A little tricky because they're in a table, and all equal siblings
$('tr:not(.element-sub-field) .col-enable .lightswitch').on('change', function(e) {
    var $lightswitch = $(this).data('lightswitch');
    var $tr = $(this).parents('tr');
    var $directSiblings = $tr.nextUntil(':not(.element-sub-field)');
    $directSiblings.toggle($(this).hasClass('on'));
}).trigger('change');


