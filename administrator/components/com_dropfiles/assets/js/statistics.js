jQuery(document).ready(function($){

    if (typeof Calendar !== "undefined" && typeof Calendar.setup === "function") {
        // safe to use the function
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
    } else {

        function initDateRange(fromId, toId) {
            $("#" + fromId).datetimepicker({
                format: "Y-m-d",
                onShow: function (ct) {
                    this.setOptions({
                        maxDate: $("#" + toId).val() ? $("#" + toId).val() : false
                    })
                },
                timepicker: false
            });

            $("#" + toId).datetimepicker({
                format: "Y-m-d",
                onShow: function (ct) {
                    this.setOptions({
                        minDate: $("#" + fromId).val() ? $("#" + fromId).val() : false
                    })
                },
                timepicker: false
            });
        }
        initDateRange('fdate', 'tdate');
        $('.icon-date').click(function () {
            console.log("clicked");
            var txt = $(this).attr('data-id');
            $('#' + txt).datetimepicker('show');
        });

        $(".chosen").chosen();
    }


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