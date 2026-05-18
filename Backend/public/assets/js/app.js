document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('form[data-confirm]').forEach(function (form) {
    form.addEventListener('submit', function (event) {
      if (!confirm(form.getAttribute('data-confirm'))) {
        event.preventDefault();
      }
    });
  });

  document.querySelectorAll('[data-toggle-target]').forEach(function (button) {
    button.addEventListener('click', function () {
      var target = document.querySelector(button.getAttribute('data-toggle-target'));
      if (!target) return;
      target.classList.toggle('hidden');
    });
  });

  // Live validation: required, email, url, password confirmation
  function showError(input, message) {
    let err = input.parentNode.querySelector('.input-error');
    if (!err) {
      err = document.createElement('div');
      err.className = 'input-error';
      input.parentNode.appendChild(err);
    }
    err.textContent = message;
    input.classList.add('invalid');
  }

  function clearError(input) {
    let err = input.parentNode.querySelector('.input-error');
    if (err) err.textContent = '';
    input.classList.remove('invalid');
  }

  function validateInput(input) {
    const val = input.value.trim();
    if (input.hasAttribute('required') && val === '') {
      showError(input, 'This field is required');
      return false;
    }
    if (input.type === 'email' && val !== '' && !/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(val)) {
      showError(input, 'Please enter a valid email');
      return false;
    }
    if (input.type === 'url' && val !== '' && !/^https?:\/\/.+/.test(val)) {
      showError(input, 'Please enter a valid URL (https://...)');
      return false;
    }
    if ((input.name === 'password' || input.type === 'password') && input.name !== 'password_confirm') {
      if (val !== '' && val.length < 8) {
        showError(input, 'Password must be at least 8 characters');
        return false;
      }
    }
    // password confirmation
    if (input.name === 'password_confirm') {
      const form = input.form;
      if (form) {
        const pw = form.querySelector('input[name="password"]');
        if (pw && pw.value !== input.value) {
          showError(input, 'Passwords do not match');
          return false;
        }
      }
    }

    clearError(input);
    return true;
  }

  document.querySelectorAll('form').forEach(function (form) {
    form.querySelectorAll('input,textarea,select').forEach(function (input) {
      // attach live validation
      input.addEventListener('input', function () {
        validateInput(input);
      });
      // validate on blur as well
      input.addEventListener('blur', function () {
        validateInput(input);
      });
    });

    // prevent submit if client-side invalid
    form.addEventListener('submit', function (e) {
      let valid = true;
      form.querySelectorAll('input,textarea,select').forEach(function (input) {
        if (!validateInput(input)) valid = false;
      });
      if (!valid) {
        e.preventDefault();
      }
    });
  });
});
