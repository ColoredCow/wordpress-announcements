jQuery(document).ready(function($) {
    if($('#cc_start_date').length) {
        $(function() {
            var pickerOpts = {
                dateFormat: "yy-mm-dd"
            };
            jQuery("#cc_start_date").datepicker(pickerOpts);
            jQuery("#cc_end_date").datepicker(pickerOpts);
        });
 
    }
    
});