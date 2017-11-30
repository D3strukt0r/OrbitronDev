var ContactForm = function () {
    return {
        
        //Contact Form
        initContactForm: function () {
	        // Validation
	        $("#contact-form").validate({
	            // Rules for form validation
	            rules:
	            {
                    'form[name]':
	                {
	                    required: true
	                },
	                'form[email]':
	                {
	                    required: true,
	                    email: true
	                },
					'form[subject]':
                    {
                        required: true,
                        minlength: 10
                    },
	                'form[message]':
	                {
	                    required: true,
	                    minlength: 10
	                },
	                captcha:
	                {
	                    required: true,
	                    remote: '/contact?verify_captcha'
	                }
	            },
	                                
	            // Messages for form validation
	            messages:
	            {
                    'form[name]':
	                {
	                    required: 'Please enter your name'
	                },
                    'form[email]':
	                {
	                    required: 'Please enter your email address',
	                    email: 'Please enter a VALID email address'
	                },
                    'form[subject]':
                    {
                        required: 'Please enter your subject'
                    },
                    'form[message]':
	                {
	                    required: 'Please enter your message'
	                },
	                captcha:
	                {
	                    required: 'Please enter characters',
	                    remote: 'Correct captcha is required'
	                }
	            },
	                                
	            // Ajax form submition                  
	            submitHandler: function(form)
	            {
	                $(form).ajaxSubmit(
	                {
	                    beforeSend: function()
	                    {
	                        $("#contact-form").find('button[type="submit"]').attr('disabled', true);
	                    },
	                    success: function()
	                    {
	                        $("#contact-form").addClass('submited');
	                    }
	                });
	            },
	            
	            // Do not change code below
	            errorPlacement: function(error, element)
	            {
	                error.insertAfter(element.parent());
	            }
	        });
        }

    };
}();