$("#loginForm").on("submit", function(e){
    e.preventDefault();

    $.ajax({
        url: "../api/login.php",
        type: "POST",
        data: $(this).serialize(),
        dataType: "json",
        success: function(response){
        if(response.success){
            $("#message").html("<p style='color: green;'>" + response.success + "</p>");

            if (response.role === "Admin") {
                window.location.href = "dashboard_admin.php";
            }
            else if (response.role === "Teacher") {
                window.location.href = "dashboard_teacher.php";
            }
            else if (response.role === "Student") {
                window.location.href = "dashboard_student.php";
            }
        }
        else {
          $("#message").html("<p style='color: red;'>" + response.error + "</p>");
        }
        },
        error: function(){
          $("#message").html("<p style='color: red;'>AJAX error</p>");
        }
    });
});
