document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById("modal");
    const closeBtn = document.querySelector(".close-btn");
    const viewButtons = document.querySelectorAll(".view-btn");

    // Open modal when view button is clicked
    viewButtons.forEach(button => {
        button.addEventListener("click", function() {
            // Extract data from the row
            const row = this.closest("tr");
            const date = row.cells[0].innerText;
            const taxType = row.cells[1].innerText;
            const amount = row.cells[2].innerText;
            const status = row.cells[3].innerText;

            // Populate modal with data
            document.getElementById("date").innerText = "Date: " + date;
            document.getElementById("tax-type").innerText = "Tax Type: " + taxType;
            document.getElementById("amount").innerText = "Amount: " + amount;
            document.getElementById("status").innerText = "Status: " + status;

            modal.style.display = "block";
        });
    });

    // Close modal when close button is clicked
    closeBtn.addEventListener("click", function() {
        modal.style.display = "none";
    });

    // Close modal when clicking outside the modal content
    window.addEventListener("click", function(event) {
        if (event.target === modal) {
            modal.style.display = "none";
        }
    });
});
