document.addEventListener("DOMContentLoaded", () => {
  const tbody = document.querySelector(".table tbody");

  // 1. Load saved students from localStorage
  const savedStudents = JSON.parse(localStorage.getItem("students")) || [];

  // 2. Load saved attendance (checkbox states)
  const savedAttendance = JSON.parse(localStorage.getItem("attendance")) || {};

  savedStudents.forEach(s => {
    const row = document.createElement("tr");

    // create 6 periods of P + Pa checkboxes
    let checkboxesHtml = "";
    for (let i = 0; i < 6; i++) {
      checkboxesHtml += `<td class="check"><input type="checkbox" data-student="${s.last}-${s.first}" data-index="${i * 2}"></td>`;
      checkboxesHtml += `<td class="check"><input type="checkbox" data-student="${s.last}-${s.first}" data-index="${i * 2 + 1}"></td>`;
    }

    row.innerHTML = `
      <td>${s.last}</td>
      <td>${s.first}</td>
      ${checkboxesHtml}
      <td></td>  <!-- Absences -->
      <td></td>  <!-- Participation -->
      <td></td>  <!-- Message -->
    `;

    tbody.appendChild(row);
  });

  // 3. Apply logic to all rows
  const rows = tbody.querySelectorAll("tr");
  rows.forEach(row => {
    const checkboxes = row.querySelectorAll('input[type="checkbox"]');
    const absenceCell = row.children[row.children.length - 3];
    const participationCell = row.children[row.children.length - 2];
    const messageCell = row.children[row.children.length - 1];

    const studentId = `${row.children[0].textContent}-${row.children[1].textContent}`;

    // restore saved state
    if (savedAttendance[studentId]) {
      const states = savedAttendance[studentId];
      checkboxes.forEach((cb, idx) => {
        cb.checked = !!states[idx];
      });
    }

    function updateCounts() {
      const totalP = 6;
      let presentCount = 0;
      let participationCount = 0;

      for (let i = 0; i < checkboxes.length; i += 2) {
        if (checkboxes[i].checked) presentCount++;
        if (checkboxes[i + 1].checked) participationCount++;
      }

      const absences = totalP - presentCount;
      absenceCell.textContent = absences;
      participationCell.textContent = participationCount;

      if (absences < 3) {
        row.style.backgroundColor = "#b8f2b2";
        messageCell.textContent = "Good attendance – Excellent participation!";
      } else if (absences <= 4) {
        row.style.backgroundColor = "#fff4a3";
        messageCell.textContent = "Warning – attendance low – You need to participate more";
      } else {
        row.style.backgroundColor = "#ff7373";
        messageCell.textContent = "Excluded – too many absences – You need to participate more";
      }

      // Save updated checkbox states
      const states = Array.from(checkboxes).map(cb => cb.checked);
      savedAttendance[studentId] = states;
      localStorage.setItem("attendance", JSON.stringify(savedAttendance));
    }

    checkboxes.forEach(cb => cb.addEventListener("change", updateCounts));
    updateCounts(); // initialize with restored values
  });
});
