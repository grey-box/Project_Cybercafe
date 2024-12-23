//  JavaScript for Adding/Editing Instructions 
    // Populate form if editing
    window.onload = function() {
      const urlParams = new URLSearchParams(window.location.search);
      const editId = urlParams.get('editId');
      if (editId) {
        // Mock data retrieval, replace with real data lookup
        document.getElementById('formTitle').innerText = 'Edit Instruction';
        document.getElementById('instructionId').value = editId;
        document.getElementById('description').value = 'Sample description for instruction ' + editId; // Example
        // Load existing image if available
      }
    };
  
    function saveInstruction(event) {
      event.preventDefault();
      // Logic to save the instruction (either add or update based on instructionId)
      alert('Instruction saved successfully!');
      window.location.href = 'index.html'; // Redirect to main page (adjust as needed)
    }