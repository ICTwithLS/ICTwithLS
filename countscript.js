let timer;
let totalTime;
let remainingTime;
let elapsedTime = 0; // Track time after reaching zero
let isRunning = false;
let soundPlaying = false;

// Load the audio file (ensure it's in your project directory)
const alarmSound = new Audio('Sleep Away.mp3'); // Replace 'Sleep Away.mp3' with your audio file's path
alarmSound.loop = true; // Set the sound to loop

// Start the timer
function startTimer() {
  if (isRunning) return; // Prevent multiple intervals

  const hours = parseInt(document.getElementById("hours").value) || 0;
  const minutes = parseInt(document.getElementById("minutes").value) || 0;
  const seconds = parseInt(document.getElementById("seconds").value) || 0;

  totalTime = hours * 3600 + minutes * 60 + seconds;
  
  // Initialize remaining time only if timer has not started yet or was reset
  if (!remainingTime || remainingTime <= 0) {
    remainingTime = totalTime;
    elapsedTime = 0;
  }

  if (remainingTime <= 0) {
    alert("Please enter a valid time!");
    return;
  }

  // Disable inputs and buttons during countdown
  document.querySelectorAll('input').forEach(input => input.disabled = true);
  document.getElementById("start").disabled = true;
  document.getElementById("pause").disabled = false;
  document.getElementById("reset").disabled = false;

  isRunning = true;
  updateTimer();

  // Start the countdown
  timer = setInterval(() => {
    if (remainingTime > 0) {
      remainingTime--;
    } else {
      if (!soundPlaying) {
        alarmSound.play(); // Play alarm sound when timer reaches zero
        soundPlaying = true;
      }
      elapsedTime++;
    }
    updateTimer();
  }, 1000);
}

// Update the timer display
function updateTimer() {
  let displayTime;
  if (remainingTime > 0) {
    displayTime = formatTime(remainingTime);
  } else {
    displayTime = '-' + formatTime(elapsedTime);
  }
  document.getElementById("time-text").innerText = displayTime;
}

// Format time to always show 2 digits
function formatTime(timeInSeconds) {
  const absTime = Math.abs(timeInSeconds);
  const hours = Math.floor(absTime / 3600);
  const minutes = Math.floor((absTime % 3600) / 60);
  const seconds = absTime % 60;
  return `${padZero(hours)}:${padZero(minutes)}:${padZero(seconds)}`;
}

// Add leading zero to single-digit numbers
function padZero(time) {
  return time < 10 ? `0${time}` : time;
}

// Pause the timer (sound continues if timer has reached zero)
function pauseTimer() {
  clearInterval(timer);
  isRunning = false;
  document.getElementById("start").disabled = false;
  document.getElementById("pause").disabled = true;
}

// Reset the timer and stop sound
function resetTimer() {
  clearInterval(timer);
  isRunning = false;
  remainingTime = 0; // Reset remaining time
  elapsedTime = 0;
  document.getElementById("time-text").innerText = "00:00:00";

  // Stop and reset the alarm sound
  alarmSound.pause();
  alarmSound.currentTime = 0;
  soundPlaying = false;

  // Enable inputs and reset button states
  document.querySelectorAll('input').forEach(input => input.disabled = false);
  document.getElementById("start").disabled = false;
  document.getElementById("pause").disabled = true;
  document.getElementById("reset").disabled = true;
}

// Toggle fullscreen mode
function toggleFullscreen() {
  if (!document.fullscreenElement) {
    document.documentElement.requestFullscreen();
  } else if (document.exitFullscreen) {
    document.exitFullscreen();
  }
}
