$(document).ready(function() {
    $("#registerForm").on("submit", function(e){
        e.preventDefault();

        var $form = $(this);
        var $btn = $("#registerBtn");
        var $msg = $("#message");

        // basic client-side check: password confirm matches
        var pass = $form.find('input[name="password"]').val();
        var pass2 = $form.find('input[name="password_confirm"]').val();
        if(pass !== pass2){
            $msg.text('Passwords do not match').addClass('error');
            return;
        }

        $btn.prop('disabled', true).attr('aria-busy', 'true');
        $msg.removeClass('error success').text('');

        $.ajax({
            url: "/Student_Absence_Management/api/auth/register.php",
            type: "POST",
            data: $form.serialize(),
            dataType: "json",
            success: function(response){
                if(response && response.success){
                    $msg.text(response.success).addClass('success');
                    setTimeout(function(){
                        if (response.role === "Admin") {
                            window.location.href = "/Student_Absence_Management/public/admin/dashboard.php";
                        } else if (response.role === "Teacher") {
                            window.location.href = "/Student_Absence_Management/public/teacher/dashboard.php";
                        } else if (response.role === "Student") {
                            window.location.href = "/Student_Absence_Management/public/student/dashboard.php";
                        } else {
                            window.location.reload();
                        }
                    }, 450);
                } else {
                    var err = (response && response.error) ? response.error : 'Registration failed';
                    $msg.text(err).addClass('error');
                    $btn.prop('disabled', false).removeAttr('aria-busy');
                }
            },
            error: function(jqXHR, textStatus, errorThrown){
                console.error('Register AJAX error:', textStatus, errorThrown, jqXHR && jqXHR.responseText);
                $msg.text('AJAX error: ' + textStatus).addClass('error');
                $btn.prop('disabled', false).removeAttr('aria-busy');
            }
        });
    });

    // password toggle (re-usable)
    $(document).on('click', '.toggle-password', function(e){
        e.preventDefault();
        var $btn = $(this);
        var $container = $btn.closest('.input-group, .form-group, .password-field');
        var $input = $container.find('input[type="password"], input[type="text"]').first();
        if(!$input || !$input.length){
            $input = $btn.siblings('input[type="password"], input[type="text"]').first();
        }
        if(!$input || !$input.length){
            $input = $btn.prevAll('input[type="password"], input[type="text"]').first();
        }
        if(!$input || !$input.length) return;

        var wasFocused = (document.activeElement === $input[0]);
        var selectionStart = null, selectionEnd = null;
        try{ selectionStart = $input[0].selectionStart; selectionEnd = $input[0].selectionEnd; }catch(ignore){}

        var isPassword = $input.attr('type') === 'password';
        try{ $input.attr('type', isPassword ? 'text' : 'password'); }catch(err){
            var $clone = $input.clone(); $clone.attr('type', isPassword ? 'text' : 'password'); $input.replaceWith($clone); $input = $clone;
        }
        try{ if(wasFocused){ $input[0].focus(); if(typeof selectionStart === 'number' && typeof selectionEnd === 'number'){ $input[0].setSelectionRange(selectionStart, selectionEnd); } } }catch(ignore){}

        if(isPassword){
            $btn.attr('aria-label','Hide password').attr('title','Hide password').attr('aria-pressed','true').addClass('is-shown');
            $btn.text('🙈');
        } else {
            $btn.attr('aria-label','Show password').attr('title','Show password').attr('aria-pressed','false').removeClass('is-shown');
            $btn.text('👁️');
        }
    });

});
