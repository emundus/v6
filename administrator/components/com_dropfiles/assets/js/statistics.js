jQuery(document).ready(function($){


    Calendar.setup({
        // Id of the input field
        inputField: "fdate",
        // Format of the input field
        ifFormat: "%Y-%m-%d",
        // Trigger for the calendar (button ID)
        button: "fdate_img",
        // Alignment (defaults to "Bl")
        align: "Tl",
        singleClick: true
    });

    Calendar.setup({
        // Id of the input field
        inputField: "tdate",
        // Format of the input field
        ifFormat: "%Y-%m-%d",
        // Trigger for the calendar (button ID)
        button: "tdate_img",
        // Alignment (defaults to "Bl")
        align: "Tl",
        singleClick: true
    });
    $(".btn-reset").click(function(e){
        e.preventDefault();
        $('select').val("").trigger('change').trigger("liszt:updated").trigger("chosen:updated");
        $('#fdate').val("");
        $('#tdate').val("");
        $('#query').val("");
        $('#adminForm').submit();
    });
    $('#selection').on('change', function () {
        $('#selection_value').val("").trigger('change').trigger("liszt:updated").trigger("chosen:updated");
        $('#adminForm').submit();
    })
});