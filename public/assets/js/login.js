document.addEventListener('DOMContentLoaded', () => {
  const toggleBtn = document.getElementById('togglePassword')
  const passwordInput = document.getElementById('password')

  if (toggleBtn && passwordInput) {
    toggleBtn.addEventListener('click', () => {
      const isHidden = passwordInput.type === 'password'
      passwordInput.type = isHidden ? 'text' : 'password'

      const icon = toggleBtn.querySelector('i')
      icon.classList.toggle('fa-eye')
      icon.classList.toggle('fa-eye-slash')
    })
  }
})