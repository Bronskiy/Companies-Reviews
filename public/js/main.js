var googleMap = null;
var ratingUpdated = false;

/**
 * Search form
 */
function SearchForm()
{
    this.init = function () {
        $('#search').blur(function (e) {
            if (this.value == '')
                this.value = 'Search Businesses';
        });

        $('#search').focus(function (e) {
            if (this.value == 'Search Businesses')
                this.value = '';
        });
    };

    this.submit = function () {
        $('#search-form').submit();
    };
}

var searchForm = new SearchForm();

/**
 * Check if device is handheld
 */
function isHandheld() {
    return /Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.userAgent);
}

/**
 * Review video player
 */
function Player(id) {
    var _self = this;

    this.id = id;
    this.player = $('#review-video-player-' + this.id).data('flowplayer');

    this.play = function () {
        if (!isHandheld()) {
            this.player.load();
        }
    };

    this.stop = function () {
        if (!isHandheld()) {
            this.player.unload();
        }
    };
}

/**
 * Update rating description
 */
function updateRatingDescription(score) {
    var desc = '';

    if (score >= 0 && score < 1.5) {
        desc = 'awful';
    } else if (score >= 1.5 && score <= 2) {
        desc = 'very bad';
    } else if (score > 2 && score <= 3) {
        desc = 'average';
    } else if (score > 3 && score < 4.5) {
        desc = 'good';
    } else if (score >= 4.5) {
        desc = 'awesome';
    }

    desc += ' (' + Math.round(score * 100 / 5) + '%)';

    $('.leave-review-star-description').html(desc);

    ratingUpdated = true;
}

/**
 * Check if rating is updated
 */
function checkRatingUpdated() {
    if (!ratingUpdated) {
        alert("You must select stars for rating the business!");
        return false;
    }

    return true;
}


/**
 * Intialize Google Map
 * @param lat
 * @param lng
 * @param title
 * @param icon
 */
function initMap(lat, lng, title, icon) {
    var Map = function () {
        var self = this;

        this.posChanged = false;

        this.Latlng = new google.maps.LatLng(lat, lng);

        this.mapOptions = {
            center    : this.Latlng,
            zoom      : 8,
            mapTypeId : google.maps.MapTypeId.ROADMAP
        };

        this.map = new google.maps.Map(document.getElementById("company-map"), this.mapOptions);

        this.marker = new google.maps.Marker({
            position : this.Latlng,
            title    : title,
            icon     : icon
        });

        this.geocoder = new google.maps.Geocoder();

        // To add the marker to the map, call setMap();
        this.marker.setMap(this.map);

        this.changePosition = function(event) {
            $('input#latitude').val(event.latLng.lat());
            $('input#longitude').val(event.latLng.lng());

            self.marker.setPosition(event.latLng);
            self.posChanged = true;
        };

        this.geocode = function() {
            var address = $('#state').val() + ', ' + $('#city').val() + ', ' + $('#address').val() + ', ' + $('#zip').val() ;

            this.geocoder.geocode({ 'address' : address }, function(results, status) {
                if (status == google.maps.GeocoderStatus.OK) {
                    var latLng = results[0].geometry.location;

                    $('input#latitude').val(latLng.lat());
                    $('input#longitude').val(latLng.lng());

                    self.marker.setPosition(latLng);
                    self.map.setCenter(latLng);
                    self.map.setZoom(16);
                    self.posChanged = true;
                } else {
                    alert('Sorry, we could not determine the location properly. Please set your location by dragging the marker.');
                }

                return true;
            });
        }
    };

    googleMap = new Map();
    googleMap.marker.setDraggable(true);

    // marker dragend listner
    google.maps.event.addListener(googleMap.marker, 'dragend', function(event) {
        googleMap.changePosition(event);
    });

    google.maps.event.addListener(googleMap.map, 'dblclick', function(event) {
        googleMap.changePosition(event);
    });
}

/**
 * Local business & show address checkboxes
 */
function onLocalBusinessCheckboxChange() {
    if ($("#local_business").is(":checked")) {
        $("#show_address").prop("checked", true);
        $("#show_address").prop("disabled", true);
    } else {
        $("#show_address").prop("disabled", false);
    }
}

/**
 * Update banner code
 */
function updateBannerCode() {
    var style = $("#style-selector").val();
    $("pre.banner-code").hide();
    $("#banner-style-" + style).show();
}

/**
 * Check map on business profile
 */
function checkMap() {
    var longitude, latitude;

    longitude = $("#longitude").val();
    latitude = $("#latitude").val();

    if (longitude && latitude) {
        return true;
    } else {
        alert("Please set your business location on the map.");
        return false;
    }
}

/**
 * Select specified employee and close the employee selector form
 * @param id
 */
function selectEmployee(id) {
    $("#employee_id").val(id);

    if (id == 0) {
        $(".employee-selector > .employee-photo > img").attr("src", "/images/employee.png");
        $(".employee-selector > .employee-info").html("Not Specified");
    } else {
        var employeeData = $(".company-employee[data-id=" + id + "]");

        if (employeeData) {
            var name, position, photo;

            name = employeeData.data("name");
            position = employeeData.data("position");
            photo = employeeData.data("photo");

            $(".employee-selector > .employee-photo > img").attr("src", photo);

            var info = "<b>" + name + "</b>";

            if (position) {
                info += "<br><i>" + position + "</i>";
            }

            $(".employee-selector > .employee-info").html(info);
        }
    }

    $("#employee-selector").modal("hide");
}

/**
 * Set review status
 */
function reviewStatus(status) {
    $.cookie("review_status", status, {
        path:"/"
    });

    location.reload();
}

/**
 * Set company status
 */
function companyStatus(status) {
    $.cookie("company_status", status, {
        path:"/"
    });

    location.reload();
}

/**
 * Set payments month
 */
function paymentsMonth(month) {
    $.cookie("payments_month", month, {
        path:"/"
    });

    location.reload();
}

/**
 * Init select 2 on category
 */

function initSignupForm(){

        $('#category_id').select2({
            tags: "true",
            placeholder: "Uncategorized",
            //initSelection: function(element, callback) {
            //},
            //allowClear: true
        });
        if($('#sign_up_form').length > 0){
            $('#category_id').val(null).trigger("change");
        }
}

$(document).ready(function(){
    searchForm.init();

    initSignupForm();

});
