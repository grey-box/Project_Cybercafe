//  JavaScript to Update Slider Value

  // Function to update slider value
  function updateSliderValue(value) {
    document.getElementById('sliderValue').textContent = value + '%';
  }

  // Function to generate a random access code
  function generateCode() {
    const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    let randomCode = '';
    for (let i = 0; i < 10; i++) {
      randomCode += characters.charAt(Math.floor(Math.random() * characters.length));
    }
    document.getElementById('accessCode').value = randomCode;
  }

  // Automatically generate a default random code on page load
  window.onload = function() {
    generateCode();
  };