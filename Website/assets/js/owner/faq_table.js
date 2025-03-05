// FAQ Table js script
  function removeRow(button) {
    const row = button.parentElement.parentElement;
    row.remove();
  }

  function editFaq(id) {
    window.location.href = `addEditForm.html?editId=${id}`;
  }
