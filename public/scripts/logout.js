$("#logoutBtn").on("click", function(){
    $.ajax({
         url: "../api/logout.php",
         method: "POST",
        success: function(){
         window.location.href = "../public/login.html";
         },
        error: function(){
         alert("Logout failed. Please try again.");
        }
     });
});
