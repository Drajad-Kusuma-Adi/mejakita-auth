let token = tokenGen();
drawCaptcha();

/**
 * Generates a random string of 6 characters
 * @returns {string}
 */
function tokenGen() {
  const chars = 'abcdefghijklmnopqrstuvwxyz1234567890';
  let output = '';

  for (let i = 0; i < 6; i++) {
    const rand = Math.floor(Math.random() * chars.length);
    output += chars[rand];
  }

  return output;
}

/**
 * Draws the captcha image on HTML canvas
 * @returns {void}
 */
function drawCaptcha() {
  // 1. Get the canvas element
  const canvas = document.getElementById("captcha-canvas");

  // 2. Get the 2D context of the canvas
  const ctx = canvas.getContext("2d");

  // 3. Set the width and height of the canvas
  canvas.width = 300;
  canvas.height = 100;

  // 4. Apply random transformations to distort the text
  ctx.setTransform(
    1 + Math.random() * 0.1, // scaleX
    Math.random() * 0.2,     // skewX
    Math.random() * 0.2,     // skewY
    1 + Math.random() * 0.1, // scaleY
    Math.random() * 10,      // translateX
    Math.random() * 10       // translateY
  );

  // 5. Draw the distorted captcha image
  ctx.font = "bold 20px Sans-Serif";
  ctx.textAlign = "center";
  ctx.textBaseline = "middle";
  ctx.fillStyle = "#000000";
  ctx.fillText(token, canvas.width / 2, canvas.height / 2);

  // 6. Reset transformations
  ctx.setTransform(1, 0, 0, 1, 0, 0);

  // 7. Add noise
  for (let i = 0; i < 8000; i++) {
    ctx.fillRect(Math.random() * canvas.width, Math.random() * canvas.height, 1, 1);
  }
}


/**
 * Handles the captcha input
 * 
 * @returns {void}
 */
function handleCaptcha() {
  const input = document.getElementById("captcha-input").value;
  if (input !== token) {
    document.getElementById("msg").innerHTML = "Incorrect captcha";

    // Re-generate the token
    token = tokenGen();
    drawCaptcha();
  } else {
    document.getElementById("msg").innerHTML = "Correct captcha";
  }
}