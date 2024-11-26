// Theme Management
const themeToggle = document.getElementById("themeToggle")
const htmlElement = document.documentElement
const body = document.body

// Initialize theme from localStorage
function initializeTheme() {
  const savedTheme = localStorage.getItem("theme") || "light"
  applyTheme(savedTheme)
}

function applyTheme(theme) {
  if (theme === "dark") {
    body.classList.add("dark-mode")
    themeToggle.innerHTML = '<i class="bi bi-sun"></i>'
    localStorage.setItem("theme", "dark")
  } else {
    body.classList.remove("dark-mode")
    themeToggle.innerHTML = '<i class="bi bi-moon"></i>'
    localStorage.setItem("theme", "light")
  }
}

// Toggle theme on button click
if (themeToggle) {
  themeToggle.addEventListener("click", () => {
    const currentTheme = localStorage.getItem("theme") || "light"
    const newTheme = currentTheme === "light" ? "dark" : "light"
    applyTheme(newTheme)
  })
}

// Initialize on page load
document.addEventListener("DOMContentLoaded", initializeTheme)
