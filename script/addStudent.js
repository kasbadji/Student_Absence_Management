document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("studentForm");

  const idField = document.getElementById("studentID");
  const lastField = document.getElementById("lastname");
  const firstField = document.getElementById("firstname");
  const emailField = document.getElementById("email");

  const idError = document.getElementById("idError");
  const lastError = document.getElementById("lastError");
  const firstError = document.getElementById("firstError");
  const emailError = document.getElementById("emailError");

  form.addEventListener("submit", (e) => {
    e.preventDefault();

    let valid = true;
    idError.textContent = "";
    lastError.textContent = "";
    firstError.textContent = "";
    emailError.textContent = "";

    if (idField.value.trim() === "" || !/^\d+$/.test(idField.value)) {
      idError.textContent = "Student ID must contain only numbers.";
      valid = false;
    }
    if (lastField.value.trim() === "" || !/^[a-zA-Z]+$/.test(lastField.value)) {
      lastError.textContent = "Last Name must contain only letters.";
      valid = false;
    }
    if (firstField.value.trim() === "" || !/^[a-zA-Z]+$/.test(firstField.value)) {
      firstError.textContent = "First Name must contain only letters.";
      valid = false;
    }
    if (emailField.value.trim() === "" || !/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(emailField.value)) {
      emailError.textContent = "Invalid email format (ex: name@example.com).";
      valid = false;
    }

    if (!valid) return;

    // save student to localStorage
    const student = {
      last: lastField.value.trim(),
      first: firstField.value.trim(),
      email: emailField.value.trim()
    };

    let students = JSON.parse(localStorage.getItem("students")) || [];
    students.push(student);
    localStorage.setItem("students", JSON.stringify(students));

    form.reset();
    alert("Student added!");
    window.location.href = "./studentTable.html";
  });
});
