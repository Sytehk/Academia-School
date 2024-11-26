// Sidebar Toggle
const sidebar = document.getElementById("sidebar")
const sidebarToggleBtn = document.getElementById("sidebarToggleBtn")
const sidebarToggle = document.getElementById("sidebarToggle")

if (sidebarToggleBtn) {
  sidebarToggleBtn.addEventListener("click", () => {
    sidebar.classList.toggle("show")
  })
}

if (sidebarToggle) {
  sidebarToggle.addEventListener("click", () => {
    sidebar.classList.remove("show")
  })
}

// Close sidebar when clicking on a link (mobile)
const navLinks = document.querySelectorAll(".sidebar .nav-link")
navLinks.forEach((link) => {
  link.addEventListener("click", () => {
    if (window.innerWidth < 992) {
      sidebar.classList.remove("show")
    }
  })
})

// Close sidebar when clicking outside (mobile)
document.addEventListener("click", (event) => {
  if (window.innerWidth < 992) {
    const isClickInsideSidebar = sidebar.contains(event.target)
    const isClickOnToggle = sidebarToggleBtn && sidebarToggleBtn.contains(event.target)

    if (!isClickInsideSidebar && !isClickOnToggle && sidebar.classList.contains("show")) {
      sidebar.classList.remove("show")
    }
  }
})
