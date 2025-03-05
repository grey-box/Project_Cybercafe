//  JavaScript

  function removeRow(button) {
    const row = button.parentElement.parentElement;
    row.remove();
  }

  function editInstruction(id) {
    window.location.href = `addEditInstruction.html?editId=${id}`;
  }
