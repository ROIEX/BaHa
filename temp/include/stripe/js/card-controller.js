$(document).ready(function() 
{

    $('#buy-form').submit(function(event)
    {
        // immediately disable the submit button to prevent double submits
        
        var cardNumber = $('#card-number').val();
        var cardCVC = $('#card-security-code').val();
         
        // First and last name fields: make sure they're not blank
        if (cardNumber === "") {
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
});