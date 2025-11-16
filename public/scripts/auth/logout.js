$("#logoutBtn").on("click", function(){
    $.ajax({
          url: "/Student_Absence_Management/api/auth/logout.php",
         method: "POST",
        success: function(){
         window.location.href = "../public/login.html";
         },
        error: function(){
         alert("Logout failed. Please try again.");
        }
     });
});
