jQuery(document).ready(function() {
    if (jQuery('div.modal-content').length > 0) {
        var modal = document.getElementById('payright_modal654');
        // Get the button that opens the modal
        // var opener = document.getElementById('opener');
        // var close = document.getElementById('close');
        if (payrightModuleOverride.payrightOverrideClass != null) {
            var classOverride = payrightModuleOverride.payrightOverrideClass;
        } else {
            var classOverride = '.sticky';
        }
        var array = classOverride.split(" ");
        // When the user clicks the button, open the modal 
        // opener.onclick = function() {
        //     modal.style.display = "block";
        //         jQuery(modal).css('z-index', '99999999999999');
        //     array.forEach(function(entry) {
        //         jQuery('' + entry + '').css('z-index', '1');
        //         console.log(entry);
        //     });
        // }
        jQuery('.payright_opener654').click(function(event) {
            jQuery('#payright_modal654').show();
            jQuery(modal).css('z-index', '99999999999999');
            array.forEach(function(entry) {
                jQuery('' + entry + '').css('z-index', '1');
                console.log(entry);
            });
        });
        // When the user clicks anywhere outside of the modal, close it
        window.onclick = function(event) {
            if (event.target == modal) {
                // modal.style.display = "none";
                jQuery('#payright_modal654').hide();
                array.forEach(function(entry) {
                    jQuery('' + entry + '').css('z-index', 'initial');
                });
            }
        }
        // close.onclick = function(event) {
        //     modal.style.display = "none";
        //     array.forEach(function(entry) {
        //         jQuery('' + entry + '').css('z-index', 'initial');
        //     });
        // }
        jQuery('#PayrightHowitWorksmodalPopup > #close').click(function(event) {
            jQuery('#payright_modal654').hide();
            array.forEach(function(entry) {
                jQuery('' + entry + '').css('z-index', 'initial');
            });
        });
        jQuery(document).change(function() {
            jQuery('.payright_opener654V').click(function(event) {
                console.log("pressed opener");
                jQuery('#payright_modal654').show();
                jQuery('#payright_modal654').css('z-index', '99999999999999');
                array.forEach(function(entry) {
                    jQuery('' + entry + '').css('z-index', '1');
                    console.log(entry);
                });
            });
        });
    }
    if (jQuery('footer').length > 0) {
        if (jQuery('footer:has(div#prshop)')) {
            jQuery('footer div#prshop').hide();
        }
        if (jQuery('footer:has(div.payrightProductInstalments)')) {
            jQuery('footer div.payrightProductInstalments').hide();
        }
    }
});