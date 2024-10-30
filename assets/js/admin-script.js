jQuery(document).ready(function($) {
    // Mostrar/ocultar campos de valor mínimo basado en el estado del checkbox
    function toggleMinimumFields() {
        if ($('#mmow_enable_minimum_order_value').is(':checked')) {
            $('#mmow_minimum_order_fields').show();
        } else {
            $('#mmow_minimum_order_fields').hide();
        }
    }

    // Mostrar/ocultar campos de valor máximo basado en el estado del checkbox
    function toggleMaximumFields() {
        if ($('#mmow_enable_maximum_order_value').is(':checked')) {
            $('#mmow_maximum_order_fields').show();
        } else {
            $('#mmow_maximum_order_fields').hide();
        }
    }

    // Ejecutar al cargar la página
    toggleMinimumFields();
    toggleMaximumFields();

    // Ejecutar cuando se cambie el estado del checkbox
    $('#mmow_enable_minimum_order_value').change(toggleMinimumFields);
    $('#mmow_enable_maximum_order_value').change(toggleMaximumFields);
});
