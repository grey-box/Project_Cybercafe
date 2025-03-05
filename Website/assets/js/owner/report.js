//  JavaScript to Toggle Dropdowns 

  function toggleDropdown(dropdownId) {
    const dropdown = document.getElementById(dropdownId);
    dropdown.disabled = !dropdown.disabled;
  }

  function runReport() {
    alert("Report generation initiated with selected filters.");
  }

