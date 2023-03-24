jQuery(document).ready(function($) {
    'use strict';
    //main tabs
    $(".accf7-settings-tab .tab-wrap li").click(function(e) {
        e.preventDefault();
        var self = $(this);
        var dispstyle = self.attr("data-id");

        $(".tab-pane").hide();
        $(".accf7-settings-tab .tab-wrap li").removeClass('active');
        self.closest('li').addClass('active');
        $("." + dispstyle + "").fadeIn();
    });

});