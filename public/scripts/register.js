 $("#registerForm").on("submit", function(e){
    e.preventDefault(); // Stop Page Refresh

    $.ajax({
         url:"../api/register.php",
         type:"POST",
         data: $(this).serialize(), // Jquery method to take all form data
         dataType: "json",
         success:function(response){
            if(response.success){
             $("#message").html("<p style='color:green'>" + response.success + "</p>");
            }
            else {
             $("#message").html("<p style='color:red'>" + response.error + "</p>");
             }
        },
        error:function(){
         $("#message").html("<p style='color:red'>AJAX Error</p>");
        }
     });
  });
