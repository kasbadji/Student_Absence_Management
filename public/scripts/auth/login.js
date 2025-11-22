$(document).ready(function() {
$("#loginForm").on("submit", function(e){
    e.preventDefault();

    $.ajax({

      url: "/Student_Absence_Management/api/auth/login.php",


        type: "POST",
        data: $(this).serialize(),
        dataType: "json",
        success: function(response){
        if(response.success){
            $("#message").html("<p style='color: green;'>" + response.success + "</p>");


              if (response.role === "Admin") {
                    window.location.href = "/Student_Absence_Management/public/admin/dashboard.php";
              } else if (response.role === "Teacher") {
                    window.location.href = "/Student_Absence_Management/public/teacher/dashboard.php";
               } else if (response.role === "Student") {
                    window.location.href = "/Student_Absence_Management/public/student/dashboard.php";
              }
        }
        else {
          $("#message").html("<p style='color: red;'>" + response.error + "</p>");
        }
        },
        error: function(jqXHR, textStatus, errorThrown){

          console.error("AJAX error:", textStatus, errorThrown, jqXHR.responseText);
          $("#message").html("<p style='color: red;'>AJAX error: " + textStatus + "</p>");
        }
    });
});
});
