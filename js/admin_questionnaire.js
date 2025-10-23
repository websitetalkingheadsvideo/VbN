/**
 * Admin Questionnaire JavaScript - Valley by Night
 * Handles questionnaire admin functionality
 */

function toggleEdit(id) {
    const editForm = document.getElementById("edit-" + id);
    if (editForm.style.display === "none" || editForm.style.display === "") {
        editForm.style.display = "table-row";
    } else {
        editForm.style.display = "none";
    }
}
