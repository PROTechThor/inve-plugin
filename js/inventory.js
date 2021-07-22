jQuery(document).ready(function($) {

    $('body').on("click", ".detail-status" , function() {

        // Get the button and current status of the detail
        let detail_button = $(this);
        let detail_status  = $(this).attr("data-inve-status");

        // Get ID of the clicked detail
        let comment_id = $(this).parent().find(".hcomment").val();
        let data = {
            "comment_id":comment_id,
            "status": detail_status
        };

        // Create AJAX request to save the detail
        $.post( inveconf.ajaxURL, {
            action:"set_detail_status",
            nonce:inveconf.ajaxNonce,
            data : data,
        }, function( data ) {
            if("success" == data.status){
                if("valid" == detail_status){
                    detail_button.val("Dispprove");
                    detail_button.attr("data-inve-status","invalid");
                }else{
                    detail_button.val("Approve");
                    detail_button.attr("data-inve-status","valid");
                }
            }
        }, "json");
    });
});
