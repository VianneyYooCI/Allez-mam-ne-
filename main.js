(function ($) {

    $.fn.verifActivation = function(options) {
        var settings = $.extend({
            compare: $("#logincf"),
            entity: "Login",
            prenoms: ["matthieu", "marina"],
            nomsCommun: ["vélo", "voiture"],
            user: ["matthieu", "mota"]
        }, options );
 
        var validated = true;
        var validatedWithoutRepeat = true;

        if(settings.entity=="Password"){
            var prenoms = settings.prenoms;
            var nomsCommun = settings.nomsCommun;
            var user = settings.user;
            var str = this.val().toLowerCase();
            if (user.some(function(v) { return str.indexOf(v) >= 0; })) {
                validated = false;
                $('.error'+settings.entity+' .leastFirstLastName').css('color', 'red');
            }else{
                $('.error'+settings.entity+' .leastFirstLastName').css('color', 'green');
            }
            if (nomsCommun.some(function(v) { return str.indexOf(v) >= 0; })) {
                validated = false;
                $('.error'+settings.entity+' .leastCommonName').css('color', 'red');
            }else{
                $('.error'+settings.entity+' .leastCommonName').css('color', 'green');
            }
            if (prenoms.some(function(v) { return str.indexOf(v) >= 0; })) {
                validated = false;
                $('.error'+settings.entity+' .leastNiceName').css('color', 'red');
            }else{
                $('.error'+settings.entity+' .leastNiceName').css('color', 'green');
            }
        }
        if(this.val().length < 8){
            validated = false;
            $('.error'+settings.entity+' .least8').css('color', 'red');
        }else{
            $('.error'+settings.entity+' .least8').css('color', 'green');
        }
        if(!/\d/.test(this.val())){
            validated = false;
            $('.error'+settings.entity+' .leastNum').css('color', 'red');
        }else{
            $('.error'+settings.entity+' .leastNum').css('color', 'green');
        }
        if(!/[a-z]/.test(this.val())){
            validated = false;
            $('.error'+settings.entity+' .leastMin').css('color', 'red');
        }else{
            $('.error'+settings.entity+' .leastMin').css('color', 'green');
        }
        if(!/[A-Z]/.test(this.val())){
            validated = false;
            $('.error'+settings.entity+' .leastMaj').css('color', 'red');
        }else{
            $('.error'+settings.entity+' .leastMaj').css('color', 'green');
        }
        if(/[^0-9a-zA-Z]/.test(this.val())){
        //    validated = false;
        }
        validatedWithoutRepeat = validated;
        if(this.val()!=settings.compare.val()){
            validated = false;
            $('.error'+settings.entity+' .leastCor').css('color', 'red');
        }else{
            $('.error'+settings.entity+' .leastCor').css('color', 'green');
        }
        if(window.location.pathname === "/mon-compte/" || window.location.pathname === "/mon-compte" ||
            window.location.pathname === "/activation/" || window.location.pathname === "/activation" ||
            window.location.pathname === "/forget/" || window.location.pathname === "/forget"){
            var firstName = $("#first_name").val().toLowerCase();
            var lastName = $("#last_name").val().toLowerCase();
            if(str.indexOf(firstName) > -1 || str.indexOf(lastName) > -1){
                validated = false;
                $('.error'+settings.entity+' .leastFirstLastName').css('color', 'red');
            }
            else{
                $('.error'+settings.entity+' .leastFirstLastName').css('color', 'green');
            }
            if(!validated && !validatedWithoutRepeat){
                $(this)[0].setCustomValidity("Enter correct value for new password");
            }
            else if(!validated && validatedWithoutRepeat){
                $(this)[0].setCustomValidity("");
                settings.compare[0].setCustomValidity("Enter correct value for new password");
            }
            else if(validated && validatedWithoutRepeat){
                $(this)[0].setCustomValidity("");
                settings.compare[0].setCustomValidity("");
            }
        }
        if(validated){
            $('.error'+settings.entity+' li').css('color', 'green');
            this.parent().removeClass("has-error");
            this.parent().addClass("has-success");
            settings.compare.parent().removeClass("has-error");
            settings.compare.parent().addClass("has-success");
        }else{
            this.parent().removeClass("has-success");
            this.parent().addClass("has-error");
            settings.compare.parent().removeClass("has-success");
            settings.compare.parent().addClass("has-error");
        }
        if(this.val()==""){
            this.parent().removeClass("has-error");
            this.parent().removeClass("has-success");
            settings.compare.parent().removeClass("has-error");
            settings.compare.parent().removeClass("has-success");
            $('.error'+settings.entity).hide();
        }else{
            $('.error'+settings.entity).show();
        }
        if($('#login').val() == $('#password').val()){
            $('.errorLogin .leastPas').css('color', 'red');
            $('.errorPassword .leastLog').css('color', 'red');
        }else{
            $('.errorLogin .leastPas').css('color', 'green');
            $('.errorPassword .leastLog').css('color', 'green');
        }
    };

 var owl = $("#carousel");
                 owl.owlCarousel({
                     itemsCustom : [
                       [0, 1],
                       [480, 1],
                       [600, 2],
                       [767, 2],
                       [980, 3],
                       [1200, 4],
                       [1400, 4],
                       [3000, 4]
                     ],
                     navigation : true
                 });
            

}(jQuery));


jQuery( function( $ ) {
	$( document ).delegate( '[name="society_logo"]', 'change', function() {
		$( '.fileinput-button:not(.btn-success)' ).children( 'div' ).html( this.value );
	} );
} );

jQuery( function( $ ) {
    $( document ).delegate( '[name="file_fichier"]', 'change', function() {
        $( '.fileinput-button:not(.btn-success)' ).children( 'span' ).html( this.value );
    } );
} );

jQuery( function( $ ) {
    $( document ).delegate( '[name="user_avatar"]', 'change', function() {
        $( '.fileinput-button:not(.btn-success)' ).children( 'div' ).html( this.value );
    } );
} );

jQuery( function( $ ) {
    $( document ).delegate( '[name="group_image"]', 'change', function() {
        $( '.fileinput-button:not(.btn-success)' ).children( 'div' ).html( this.value );
    } );
} );