//  JavaScript to Apply Layout Changes 
  
    document.getElementById('layoutForm').addEventListener('submit', function(event) {
      event.preventDefault();
      applyLayoutSettings();
    });
  
    function applyLayoutSettings() {
      const layoutType = document.getElementById('layoutType').value;
      const theme = document.getElementById('theme').value;
      const sidebarVisible = document.getElementById('sidebarVisibility').checked;
      const headerLayout = document.getElementById('headerLayout').value;
  
      // Save settings (mock logic for now, replace with backend call if necessary)
      alert('Layout Settings Applied!');
      previewLayout(layoutType, theme, sidebarVisible, headerLayout);
    }
  
    function previewLayout(layoutType, theme, sidebarVisible, headerLayout) {
      // Preview layout based on selected options
      const previewDiv = document.getElementById('layoutPreview');
      previewDiv.innerHTML = `
        <h4>Selected Layout: ${layoutType}</h4>
        <p>Theme: ${theme}</p>
        <p>Sidebar: ${sidebarVisible ? 'Visible' : 'Hidden'}</p>
        <p>Header Layout: ${headerLayout}</p>
      `;
    }
  
    function resetLayout() {
      // Reset all form fields to their default values
      document.getElementById('layoutForm').reset();
      document.getElementById('layoutPreview').innerHTML = `
        <h4>Layout Preview</h4>
        <p>Select the layout options to see the changes.</p>
      `;
    }
  