// Populate form if editing
window.onload = function() {
    const urlParams = new URLSearchParams(window.location.search);
    const editId = urlParams.get('editId');
    if (editId) {
      // Mock data retrieval, replace with real data lookup
      document.getElementById('formTitle').innerText = 'Edit Q&A';
      document.getElementById('faqId').value = editId;
      document.getElementById('question').value = 'Sample Question ' + editId; // Example
      document.getElementById('answer').value = 'Sample Answer for question ' + editId; // Example
    }
  };

  function saveFaq(event) {
    event.preventDefault();
    // Logic to save the FAQ (either add or update based on faqId)
    alert('FAQ saved successfully!');
    window.location.href = 'index.html'; // Redirect to main page (adjust as needed)
  }