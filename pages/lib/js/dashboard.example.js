
document.addEventListener("DOMContentLoaded", function(event) {

    //Create new instance of App:
    let App = new FrigateApp({
        //Settings:
    });


    //Dashboard methods and events:
    var dashboard = {
        init : function() {
            //Init the dashboard:
            this.refreshStats();
        },
        refreshStats: function($btn = $()) {
            $btn.prop("disabled", true);
            App.apiRequest("GET", "totals", null,
                { 'parts': 'all' }, 
                {
                    error: function(jqXhr, textStatus, errorMessage) {
                        console.log(jqXhr, textStatus, errorMessage);
                    },
                    success: function(res) {
                        // console.log(res);
                        //check that res has totals key:
                        if (res.hasOwnProperty('totals')) {
                            //iterate over totals:
                            for (const key in res.totals) {
                                if (res.totals.hasOwnProperty(key)) {
                                    const value = res.totals[key];
                                    //Update the value:
                                    $(`[data-card-name='${key}'] .current-value`).text(value);
                                }
                            }
                        }
                    },
                    complete: function() {
                        $btn.prop("disabled", false);
                    }
                }
            );
        }
    };

    /************* Set user actions **********/
    $.extend(window.globalActions, {
        "click refresh-stats" : function(e) {
            // console.log("refresh stats");
            dashboard.refreshStats($(this));
        }
    });

    //Attachuser actions:
    App.onActions("click change", "data-action", window.globalActions);



    /************* Initialize **********/
    dashboard.init();

});