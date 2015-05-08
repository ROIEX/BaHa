$(document).ready(function() 
{

    $('#buy-form').submit(function(event)
    {
        // immediately disable the submit button to prevent double submits
        
        var emailFilter = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/; 
        var fName = $('#first-name').val();
        var lName = $('#last-name').val();
        var email = $('#email').val();
        var cardNumber = $('#card-number').val();
        var cardCVC = $('#card-security-code').val();
         
        // First and last name fields: make sure they're not blank
        if (fName === "") {
            alert("Please enter your first name.");
			return false;
        } else
        if (lName === "") {
            alert("Please enter your last name.");
			return false;
        } else 
        if (email === "") {
            alert("Please enter your email address.");
			return false;
        } else if (!emailFilter.test(email)) {
            alert("Your email address is not valid.");
			return false;
        }
          
        // Stripe will validate the card number and CVC for us, so just make sure they're not blank
         else if (cardNumber === "") {
            alert("Please enter your card number.");
			return false;
        } else 
        if (cardCVC === "") {
            alert("Please enter your card security code.");
			return false;
        }
		else {
		return true;
		}

        // Boom! We passed the basic validation, so we're ready to send the info to 
        // Stripe to create a token! (We'll add this code soon.)
         
    });
	//
	$('.stripe-recurring').change(function() {
		if($(this).val() == 'yes') {
			$('#stripe-plans-up').slideDown();
			$('#stripe-plans-down').slideUp();
		} else {
			$('#stripe-plans-up').slideUp();
			$('#stripe-plans-down').slideDown();
		}
	});
});